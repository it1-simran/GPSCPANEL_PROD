<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\Writer;
use Illuminate\Http\Request;
use Auth;
use DB;

class PermissionManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin: Manage Reseller Permissions
     */
    public function adminManagePermissions()
    {
        $user = Auth::user();

        // Only Admin can access this
        if ($user->user_type !== 'Admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Get all Resellers
        $resellers = Writer::where('user_type', 'Reseller')
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        // Get all permissions grouped by module
        $permissionsByModule = Permission::where('is_active', 1)
            ->orderBy('module')
            ->orderBy('order')
            ->get()
            ->groupBy('module');

        $modules = $permissionsByModule->keys();

        return view('admin.manage_permissions', [
            'resellers' => $resellers,
            'permissionsByModule' => $permissionsByModule,
            'modules' => $modules
        ]);
    }

    /**
     * Get permissions for a specific reseller
     */
    public function getResellerPermissions($resellerId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reseller = Writer::find($resellerId);
        if (!$reseller || $reseller->user_type !== 'Reseller') {
            return response()->json(['error' => 'Reseller not found'], 404);
        }

        // Get reseller's permissions
        $rolePermissions = $reseller->role ?
            $reseller->role->permissions()->pluck('id')->toArray() : [];

        $userPermissions = $reseller->permissions()->pluck('permission_id')->toArray();

        $allPermissions = array_unique(array_merge($rolePermissions, $userPermissions));

        return response()->json([
            'permissions' => $allPermissions,
            'rolePermissions' => $rolePermissions,
            'userPermissions' => $userPermissions
        ]);
    }

    /**
     * Update Reseller Permissions (Admin)
     */
    public function updateResellerPermissions(Request $request, $resellerId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reseller = Writer::find($resellerId);
        if (!$reseller || $reseller->user_type !== 'Reseller') {
            return response()->json(['error' => 'Reseller not found'], 404);
        }

        $permissions = $request->input('permissions', []);

        // Sync permissions
        $reseller->permissions()->sync($permissions);

        // Log the action
        \Log::info('Admin assigned permissions to Reseller', [
            'admin_id' => $user->id,
            'reseller_id' => $resellerId,
            'permissions_count' => count($permissions)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully'
        ]);
    }

    /**
     * Reseller: Manage Child User Permissions
     */
    public function resellerManageChildPermissions()
    {
        $user = Auth::user();

        if ($user->user_type !== 'Reseller') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Get child users created by this reseller
        $childUsers = Writer::where('created_by', $user->id)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        // Get reseller's permissions (only show these to child users)
        $resellerRolePermissions = $user->role ?
            $user->role->permissions : collect([]);
        $resellerUserPermissions = $user->permissions;

        $availablePermissions = $resellerRolePermissions->merge($resellerUserPermissions)
            ->unique('id');

        // Group by module
        $permissionsByModule = $availablePermissions
            ->sortBy('module')
            ->groupBy('module');

        $modules = $permissionsByModule->keys();

        return view('reseller.manage_child_permissions', [
            'childUsers' => $childUsers,
            'permissionsByModule' => $permissionsByModule,
            'modules' => $modules,
            'availablePermissions' => $availablePermissions
        ]);
    }

    /**
     * Get permissions for a specific child user
     */
    public function getChildUserPermissions($userId)
    {
        $user = Auth::user();
        $childUser = Writer::find($userId);

        // Verify this user is created by current reseller or admin
        if ($user->user_type === 'Reseller' && $childUser->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->user_type !== 'Admin' && $user->user_type !== 'Reseller') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get child user's permissions
        $childPermissions = $childUser->permissions()->pluck('permission_id')->toArray();

        return response()->json([
            'permissions' => $childPermissions
        ]);
    }

    /**
     * Update Child User Permissions (Reseller or Admin)
     */
    public function updateChildUserPermissions(Request $request, $userId)
    {
        $user = Auth::user();
        $childUser = Writer::find($userId);

        if (!$childUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Verify authorization
        if ($user->user_type === 'Reseller') {
            if ($childUser->created_by !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            // Validate Reseller is not assigning beyond their permissions
            $this->validatePermissionHierarchy($user, $childUser, $request->input('permissions', []));
        } elseif ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permissions = $request->input('permissions', []);

        // Sync permissions
        $childUser->permissions()->sync($permissions);

        // Log the action
        \Log::info('Permission updated for user', [
            'updated_by' => $user->id,
            'updated_by_type' => $user->user_type,
            'user_id' => $userId,
            'permissions_count' => count($permissions)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User permissions updated successfully'
        ]);
    }

    /**
     * Validate that child permissions don't exceed parent permissions
     */
    private function validatePermissionHierarchy($parentUser, $childUser, $requestedPermissions)
    {
        // Get parent's accessible permissions
        $parentRolePermissions = $parentUser->role ?
            $parentUser->role->permissions()->pluck('id')->toArray() : [];
        $parentUserPermissions = $parentUser->permissions()->pluck('permission_id')->toArray();
        $parentAccessiblePermissions = array_unique(array_merge($parentRolePermissions, $parentUserPermissions));

        // Check if all requested permissions are accessible to parent
        foreach ($requestedPermissions as $permId) {
            if (!in_array($permId, $parentAccessiblePermissions)) {
                throw new \Exception("Cannot assign permission beyond your access level");
            }
        }
    }

    /**
     * Admin: Manage User Permissions
     */
    public function adminManageUserPermissions()
    {
        $user = Auth::user();

        // Only Admin can access this
        if ($user->user_type !== 'Admin') {
            return redirect()->back()->with('error', 'Unauthorized access');
        }

        // Get all Users
        $users = Writer::where('user_type', 'User')
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        // Get permissions excluding 'account_management' module (only for Resellers)
        $permissionsByModule = Permission::where('is_active', 1)
            ->where('module', '!=', 'account_management')
            ->orderBy('module')
            ->orderBy('order')
            ->get()
            ->groupBy('module');

        $modules = $permissionsByModule->keys();

        return view('admin.manage_user_permissions', [
            'users' => $users,
            'permissionsByModule' => $permissionsByModule,
            'modules' => $modules
        ]);
    }

    /**
     * Get permissions for a specific user
     */
    public function getUserPermissions($userId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $targetUser = Writer::find($userId);
        if (!$targetUser || $targetUser->user_type !== 'User') {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get user's permissions
        $userPermissions = $targetUser->permissions()->pluck('permission_id')->toArray();

        return response()->json([
            'permissions' => $userPermissions
        ]);
    }

    /**
     * Update User Permissions (Admin)
     */
    public function updateUserPermissions(Request $request, $userId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $targetUser = Writer::find($userId);
        if (!$targetUser || $targetUser->user_type !== 'User') {
            return response()->json(['error' => 'User not found'], 404);
        }

        $permissions = $request->input('permissions', []);

        // Sync permissions
        $targetUser->permissions()->sync($permissions);

        // Log the action
        \Log::info('Admin assigned permissions to User', [
            'admin_id' => $user->id,
            'user_id' => $userId,
            'permissions_count' => count($permissions)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully'
        ]);
    }

    /**
     * Get user's accessible modules
     */
    public function getUserModules()
    {
        $user = Auth::user();

        // Get user's permissions
        $rolePermissions = $user->role ?
            $user->role->permissions : collect([]);
        $userPermissions = $user->permissions;

        $allPermissions = $rolePermissions->merge($userPermissions)->unique('id');

        // Get unique modules with view permission
        $modules = $allPermissions
            ->where('action', 'view')
            ->unique('module')
            ->pluck('module')
            ->toArray();

        return response()->json([
            'modules' => $modules
        ]);
    }
}
