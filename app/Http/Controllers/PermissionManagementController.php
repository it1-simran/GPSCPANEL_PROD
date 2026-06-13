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

        // Clear permission cache to ensure fresh load from database
        \App\Helpers\PermissionHelper::flushCache();

        // Get all Resellers
        $resellers = Writer::where('user_type', 'Reseller')
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        // Get all permissions grouped by module (fresh from database)
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

        // Account permissions are managed in user_permissions. Role permissions are
        // defaults only and must not re-enable permissions the admin removed.
        $userPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $resellerId)
            ->where('permissions.is_active', 1)
            ->pluck('permissions.id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $rolePermissions = [];
        if ($reseller->role_id) {
            $rolePermissions = DB::table('role_permissions')
                ->where('role_id', $reseller->role_id)
                ->pluck('permission_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }

        return response()->json([
            'permissions' => array_values(array_unique($userPermissions)),
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
            'permissions' => $result['permissions'] ?? [],
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

        // Clear permission cache to ensure fresh load from database
        \App\Helpers\PermissionHelper::flushCache();

        // Get child users created by this reseller.
        $childUsers = Writer::where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhere('parent_user_id', $user->id);
            })
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        $selectedUser = null;
        if ($request->has('user_id')) {
            $selectedUser = Writer::find($request->input('user_id'));
            if ($selectedUser && !$this->canManageChildUser($user, $selectedUser)) {
                return redirect()->back()->with('error', 'Unauthorized access');
            }
        }

        $availablePermissions = $this->getAssignablePermissionsForUser($user, $selectedUser);

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

        // Clear permission cache to ensure fresh load from database
        \App\Helpers\PermissionHelper::flushCache();

        $childUser = Writer::find($userId);

        if (!$childUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Verify this user is created by current reseller or admin
        if ($user->user_type === 'Reseller' && !$this->canManageChildUser($user, $childUser)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->user_type !== 'Admin' && $user->user_type !== 'Reseller') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get child user's permissions (fresh from database, not cached)
        $childPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $childUser->id)
            ->where('permissions.is_active', 1)
            ->pluck('permissions.id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $allowedPermissionIds = null;
        if ($user->user_type === 'Reseller') {
            $allowedPermissionIds = $this->getAssignablePermissionIds($user, $childUser);
            $childPermissions = array_values(array_intersect($childPermissions, $allowedPermissionIds));
        }

        return response()->json([
            'permissions' => $childPermissions,
            'assignable_permissions' => $allowedPermissionIds,
            'user_type' => $childUser->user_type,
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
            if (!$this->canManageChildUser($user, $childUser)) {
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
            'permissions' => $result['permissions'] ?? [],
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
        $parentAccessiblePermissions = $this->getAssignablePermissionIds($parentUser, $childUser);
        $requestedPermissions = array_map('intval', $requestedPermissions);

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

    private function canManageChildUser($parentUser, $childUser): bool
    {
        if (!$childUser) {
            return false;
        }

        return (int) $childUser->created_by === (int) $parentUser->id
            || (int) $childUser->parent_user_id === (int) $parentUser->id;
    }

    private function getEffectiveAssignedPermissionIds($user): array
    {
        if (!$user) {
            return [];
        }

        $permissionKeys = \App\Helpers\PermissionHelper::getAllAccessiblePermissions($user);

        if (empty($permissionKeys)) {
            return [];
        }

        return Permission::whereIn('key', $permissionKeys)
            ->where('is_active', 1)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    private function getAssignablePermissionIds($user, $targetUser = null): array
    {
        if (!$user) {
            return [];
        }

        if ($user->user_type === 'Admin') {
            $query = Permission::where('is_active', 1);
        } else {
            $query = Permission::whereIn('id', $this->getEffectiveAssignedPermissionIds($user))
                ->where('is_active', 1);
        }

        if ($targetUser && $targetUser->user_type === 'User') {
            $query->where('module', '!=', 'account_management');
        }

        return $query->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();
    }

    private function getAssignablePermissionsForUser($user, $targetUser = null)
    {
        $permissionIds = $this->getAssignablePermissionIds($user, $targetUser);

        if (empty($permissionIds)) {
            return collect([]);
        }

        return Permission::whereIn('id', $permissionIds)
            ->where('is_active', 1)
            ->orderBy('module')
            ->orderBy('order')
            ->get();
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
            'permissions' => $result['permissions'] ?? [],
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
        $modules = \App\Helpers\PermissionHelper::getAccessibleModules();

        return response()->json([
            'modules' => $modules
        ]);
    }

    /**
     * Get permission dependencies
     * Returns parent-child relationships for permissions
     */
    public function getPermissionDependencies()
    {
        $permissions = Permission::where('is_active', 1)->get();

        $dependencies = [];
        foreach ($permissions as $permission) {
            $parentId = $permission->parent_permission_id;
            if ($parentId) {
                $dependencies[$permission->id] = $parentId;
            }
        }

        // Also get inverse: which permissions depend on each permission
        $dependents = [];
        foreach ($permissions as $permission) {
            if ($permission->parent_permission_id) {
                if (!isset($dependents[$permission->parent_permission_id])) {
                    $dependents[$permission->parent_permission_id] = [];
                }
                $dependents[$permission->parent_permission_id][] = $permission->id;
            }
        }

        return response()->json([
            'dependencies' => $dependencies,  // child_id => parent_id
            'dependents' => $dependents       // parent_id => [child_id, ...]
        ]);
    }
}
