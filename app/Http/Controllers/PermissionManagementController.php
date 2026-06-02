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

        // Debug logging
        \Log::info('Permission Update Request', [
            'reseller_id' => $resellerId,
            'reseller_name' => $reseller->name,
            'requested_permissions' => $permissions,
            'permission_count' => count($permissions)
        ]);

        // Get current permissions before update (both user and role)
        $beforeUserPerms = DB::table('user_permissions')
            ->where('user_id', $resellerId)
            ->pluck('permission_id')
            ->toArray();
        $beforeRolePerms = [];
        if ($reseller->role_id) {
            $beforeRolePerms = DB::table('role_permissions')
                ->where('role_id', $reseller->role_id)
                ->pluck('permission_id')
                ->toArray();
        }
        $beforeAll = array_unique(array_merge($beforeUserPerms, $beforeRolePerms));

        \Log::info('Permissions Before Sync', [
            'user_permissions' => $beforeUserPerms,
            'role_permissions' => $beforeRolePerms,
            'combined' => $beforeAll
        ]);

        // Sync permissions - replaces all user permissions with the provided array
        // If reseller has role, we need to sync role permissions instead
        if ($reseller->role_id) {
            // Clear user permissions and sync role permissions
            DB::table('user_permissions')->where('user_id', $resellerId)->delete();

            // Update role permissions
            $result = DB::table('role_permissions')
                ->where('role_id', $reseller->role_id)
                ->delete();

            // Insert new role permissions
            foreach ($permissions as $permId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $reseller->role_id,
                    'permission_id' => $permId
                ]);
            }

            \Log::info('Sync Result', ['action' => 'role_sync', 'role_id' => $reseller->role_id]);
        } else {
            // No role, just sync user permissions
            $result = $reseller->permissions()->sync($permissions);

            \Log::info('Sync Result', [
                'attached' => $result['attached'] ?? [],
                'detached' => $result['detached'] ?? [],
                'updated' => $result['updated'] ?? []
            ]);
        }

        // Get updated permissions after sync
        $afterUserPerms = DB::table('user_permissions')
            ->where('user_id', $resellerId)
            ->pluck('permission_id')
            ->toArray();
        $afterRolePerms = [];
        if ($reseller->role_id) {
            $afterRolePerms = DB::table('role_permissions')
                ->where('role_id', $reseller->role_id)
                ->pluck('permission_id')
                ->toArray();
        }
        $afterAll = array_unique(array_merge($afterUserPerms, $afterRolePerms));

        \Log::info('Permissions After Sync', [
            'user_permissions' => $afterUserPerms,
            'role_permissions' => $afterRolePerms,
            'combined' => $afterAll
        ]);

        // Clear the permission cache so updated permissions take effect immediately
        \App\Helpers\PermissionHelper::flushCache();

        // Log the action
        \Log::info('Admin assigned permissions to Reseller', [
            'admin_id' => $user->id,
            'reseller_id' => $resellerId,
            'permissions_count' => count($permissions),
            'before' => count($beforeAll),
            'after' => count($afterAll),
            'has_role' => $reseller->role_id ? 'yes' : 'no'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully',
            'debug' => [
                'before_count' => count($beforeAll),
                'after_count' => count($afterAll),
                'requested_count' => count($permissions),
                'has_role' => $reseller->role_id ? 'yes' : 'no'
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
            $this->validatePermissionHierarchy($user, $childUser, $request->input('permissions', []));
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
        $beforeUserPerms = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->pluck('permission_id')
            ->toArray();
        $beforeRolePerms = [];
        if ($childUser->role_id) {
            $beforeRolePerms = DB::table('role_permissions')
                ->where('role_id', $childUser->role_id)
                ->pluck('permission_id')
                ->toArray();
        }
        $beforeAll = array_unique(array_merge($beforeUserPerms, $beforeRolePerms));

        // Sync permissions - if user has role, also update role permissions
        if ($childUser->role_id) {
            DB::table('user_permissions')->where('user_id', $userId)->delete();
            DB::table('role_permissions')->where('role_id', $childUser->role_id)->delete();

            foreach ($permissions as $permId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $childUser->role_id,
                    'permission_id' => $permId
                ]);
            }
        } else {
            $childUser->permissions()->sync($permissions);
        }

        // Get updated permissions
        $afterUserPerms = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->pluck('permission_id')
            ->toArray();
        $afterRolePerms = [];
        if ($childUser->role_id) {
            $afterRolePerms = DB::table('role_permissions')
                ->where('role_id', $childUser->role_id)
                ->pluck('permission_id')
                ->toArray();
        }
        $afterAll = array_unique(array_merge($afterUserPerms, $afterRolePerms));

        // Clear the permission cache so updated permissions take effect immediately
        \App\Helpers\PermissionHelper::flushCache();

        // Log the action
        \Log::info('Permission updated for user', [
            'updated_by' => $user->id,
            'updated_by_type' => $user->user_type,
            'user_id' => $userId,
            'permissions_count' => count($permissions),
            'before' => count($beforeAll),
            'after' => count($afterAll)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User permissions updated successfully',
            'debug' => [
                'before_count' => count($beforeAll),
                'after_count' => count($afterAll),
                'requested_count' => count($permissions)
            ]
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

        // Get current permissions before update (both user and role)
        $beforeUserPerms = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->pluck('permission_id')
            ->toArray();
        $beforeRolePerms = [];
        if ($targetUser->role_id) {
            $beforeRolePerms = DB::table('role_permissions')
                ->where('role_id', $targetUser->role_id)
                ->pluck('permission_id')
                ->toArray();
        }
        $beforeAll = array_unique(array_merge($beforeUserPerms, $beforeRolePerms));

        // Sync permissions - if user has role, also clear role permissions
        if ($targetUser->role_id) {
            DB::table('user_permissions')->where('user_id', $userId)->delete();
            DB::table('role_permissions')->where('role_id', $targetUser->role_id)->delete();

            foreach ($permissions as $permId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $targetUser->role_id,
                    'permission_id' => $permId
                ]);
            }
        } else {
            $targetUser->permissions()->sync($permissions);
        }

        // Get updated permissions
        $afterUserPerms = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->pluck('permission_id')
            ->toArray();
        $afterRolePerms = [];
        if ($targetUser->role_id) {
            $afterRolePerms = DB::table('role_permissions')
                ->where('role_id', $targetUser->role_id)
                ->pluck('permission_id')
                ->toArray();
        }
        $afterAll = array_unique(array_merge($afterUserPerms, $afterRolePerms));

        // Clear the permission cache so updated permissions take effect immediately
        \App\Helpers\PermissionHelper::flushCache();

        // Log the action
        \Log::info('Admin assigned permissions to User', [
            'admin_id' => $user->id,
            'user_id' => $userId,
            'permissions_count' => count($permissions),
            'before' => count($beforeAll),
            'after' => count($afterAll)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully',
            'debug' => [
                'before_count' => count($beforeAll),
                'after_count' => count($afterAll),
                'requested_count' => count($permissions)
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
