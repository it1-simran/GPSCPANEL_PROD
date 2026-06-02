<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\Writer;
use App\Services\PermissionAssignmentService;
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

        // Direct database query - get reseller's permissions from user_permissions table
        $userPermissions = DB::table('user_permissions')
            ->where('user_id', $resellerId)
            ->pluck('permission_id')
            ->toArray();

        // Get role permissions if reseller has a role
        $rolePermissions = [];
        if ($reseller->role_id) {
            $rolePermissions = DB::table('role_permissions')
                ->where('role_id', $reseller->role_id)
                ->pluck('permission_id')
                ->toArray();
        }

        $allPermissions = array_values(array_unique(array_merge($rolePermissions, $userPermissions)));

        return response()->json([
            'permissions' => $allPermissions,
            'rolePermissions' => array_values($rolePermissions),
            'userPermissions' => array_values($userPermissions)
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
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

        // Use PermissionAssignmentService for validated sync
        $service = new PermissionAssignmentService();
        $result = $service->syncPermissions($reseller, $permissions, $user);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'debug' => [
                'requested_count' => count($permissions),
                'added' => $result['added'] ?? 0,
                'removed' => $result['removed'] ?? 0
            ]
        ]);
    }

    /**
     * Reseller: Manage Child User Permissions
     */
    public function resellerManageChildPermissions(Request $request)
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

        // If a specific child user is selected, filter permissions based on their type
        $selectedUser = null;
        if ($request->has('user_id')) {
            $selectedUser = Writer::find($request->input('user_id'));

            // Filter out account_management permissions for User type
            if ($selectedUser && $selectedUser->user_type === 'User') {
                $availablePermissions = $availablePermissions
                    ->filter(function($permission) {
                        return !str_starts_with($permission->key, 'account_management.');
                    });
            }
        } else {
            // When no user is selected, show all permissions from reseller
            // The filtering will happen on the frontend when user selects a User type
        }

        // Group by module
        $permissionsByModule = $availablePermissions
            ->sortBy('module')
            ->groupBy('module');

        $modules = $permissionsByModule->keys();

        return view('reseller.manage_child_permissions', [
            'childUsers' => $childUsers,
            'permissionsByModule' => $permissionsByModule,
            'modules' => $modules,
            'availablePermissions' => $availablePermissions,
            'selectedUser' => $selectedUser
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

        // Filter out account_management permissions for User type
        if ($childUser->user_type === 'User') {
            $childPermissions = DB::table('permissions')
                ->whereIn('id', $childPermissions)
                ->where('module', '!=', 'account_management')
                ->pluck('id')
                ->toArray();
        }

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
            $hierarchyError = $this->validatePermissionHierarchy($user, $childUser, $request->input('permissions', []));
            if ($hierarchyError) {
                return response()->json(['error' => $hierarchyError], 422);
            }
        } elseif ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permissions = $request->input('permissions', []);

        // Validate: User type cannot have account_management permissions
        if ($childUser->user_type === 'User') {
            // Get permission modules for the requested permissions
            $permissionModules = DB::table('permissions')
                ->whereIn('id', $permissions)
                ->pluck('module')
                ->unique()
                ->toArray();

            if (in_array('account_management', $permissionModules)) {
                return response()->json([
                    'error' => 'User type accounts cannot be assigned Account Management permissions.'
                ], 422);
            }
        }

        // Get current permissions before update
        $beforeCount = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->count();

        // Use PermissionAssignmentService for validated sync with cascading
        $service = new PermissionAssignmentService();
        $result = $service->syncPermissions($childUser, $permissions, $user);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 422);
        }

        // Get updated permissions count
        $afterCount = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->count();

        // Log the action
        \Log::info('Permission updated for user', [
            'updated_by' => $user->id,
            'updated_by_type' => $user->user_type,
            'user_id' => $userId,
            'permissions_count' => count($permissions),
            'before' => $beforeCount,
            'after' => $afterCount,
            'added' => $result['added'] ?? 0,
            'removed' => $result['removed'] ?? 0
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'debug' => [
                'before_count' => $beforeCount,
                'after_count' => $afterCount,
                'requested_count' => count($permissions),
                'added' => $result['added'] ?? 0,
                'removed' => $result['removed'] ?? 0
            ]
        ]);
    }

    /**
     * Validate that child permissions don't exceed parent permissions
     * @return string|null Error message if validation fails, null if valid
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
                $permission = DB::table('permissions')->find($permId);
                $permName = $permission ? $permission->label : "Permission #$permId";
                return "Cannot assign '$permName' - this permission is beyond your access level";
            }
        }

        return null; // Valid
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

        // Use PermissionAssignmentService for validated sync
        $service = new PermissionAssignmentService();
        $result = $service->syncPermissions($targetUser, $permissions, $user);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'debug' => [
                'requested_count' => count($permissions),
                'added' => $result['added'] ?? 0,
                'removed' => $result['removed'] ?? 0
            ]
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
