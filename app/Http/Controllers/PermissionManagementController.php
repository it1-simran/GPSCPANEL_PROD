<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\Writer;
use App\Services\PermissionAssignmentService;
use App\Services\PermissionSyncImpactService;
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
     * Admin: Manage Manufacturer (Reseller) and Dealer (User) permissions
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

        $accounts = $this->adminOwnedAccountsQuery()
            ->orderBy('user_type')
            ->orderBy('name')
            ->get();

        $selectedAccountId = request()->query('account_id')
            ?? request()->query('reseller_id')
            ?? request()->query('user_id');
        // A link to an account the Admin did not create (e.g. a Manufacturer's child) opens nothing.
        if ($selectedAccountId && !$accounts->contains('id', (int) $selectedAccountId)) {
            $selectedAccountId = null;
        }

        // Get all permissions grouped by module (fresh from database)
        $permissionsByModule = Permission::where('is_active', 1)
            ->orderBy('module')
            ->orderBy('order')
            ->get()
            ->groupBy('module');

        $modules = $permissionsByModule->keys();

        return view('admin.manage_permissions', [
            'accounts' => $accounts,
            'selectedAccountId' => $selectedAccountId,
            'permissionsByModule' => $permissionsByModule,
            'modules' => $modules
        ]);
    }

    /**
     * Get permissions for a Manufacturer or Dealer account
     */
    public function getResellerPermissions($resellerId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $this->findManageableAccount($resellerId);
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        // Account permissions are managed in user_permissions. Role permissions are
        // defaults only and must not re-enable permissions the admin removed.
        $userPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $account->id)
            ->where('permissions.is_active', 1)
            ->pluck('permissions.id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $rolePermissions = [];
        if ($account->role_id) {
            $rolePermissions = DB::table('role_permissions')
                ->where('role_id', $account->role_id)
                ->pluck('permission_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }

        return response()->json([
            'permissions' => array_values(array_unique($userPermissions)),
            'rolePermissions' => array_values($rolePermissions),
            'userPermissions' => array_values($userPermissions),
            'user_type' => $account->user_type,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    /**
     * Update Manufacturer or Dealer permissions (Admin)
     */
    public function updateResellerPermissions(Request $request, $resellerId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $this->findManageableAccount($resellerId);
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $permissions = $request->input('permissions', []);

        // Use PermissionAssignmentService for validated sync
        $service = new PermissionAssignmentService();
        $result = $service->syncPermissions($account, $permissions, $user);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 422);
        }

        app(PermissionSyncImpactService::class)->applyImpact($account, $permissions, $user);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'permissions' => $result['permissions'] ?? [],
            'user_type' => $account->user_type,
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

        // Always render the reseller's full assignable matrix; JS hides modules per child user type.
        $availablePermissions = $this->getAssignablePermissionsForUser($user, null);

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
        $permissionsByModule = collect([]);
        $availableCount = 0;

        if ($user->user_type === 'Reseller') {
            $assignablePermissions = $this->getAssignablePermissionsForUser($user, $childUser);
            $allowedPermissionIds = $assignablePermissions
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();
            $permissionsByModule = $assignablePermissions->groupBy('module');
            $availableCount = $assignablePermissions->count();
            $childPermissions = array_values(array_intersect($childPermissions, $allowedPermissionIds));
        }

        return response()->json([
            'permissions' => $childPermissions,
            'assignable_permissions' => $allowedPermissionIds,
            'permissions_by_module' => $permissionsByModule->map(function ($permissions) {
                return $permissions->map(function ($permission) {
                    return [
                        'id' => (int) $permission->id,
                        'label' => $permission->label,
                        'key' => $permission->key,
                        'module' => $permission->module,
                    ];
                })->values();
            }),
            'modules' => $permissionsByModule->keys()->values(),
            'available_count' => $availableCount,
            'user_type' => $childUser->user_type,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
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

        app(PermissionSyncImpactService::class)->applyImpact($childUser, $permissions, $user);

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
                return "Cannot assign '$permName' - this permission is beyond your access level.\nPlease refresh the page.";
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
        if ($targetUser && $targetUser->user_type !== 'Reseller') {
            $query->whereNotIn('key', (new PermissionAssignmentService())->getManufacturerOnlyKeys());
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
     * Legacy route — redirects to the unified permissions page.
     */
    public function adminManageUserPermissions(Request $request)
    {
        $query = [];
        if ($request->filled('user_id')) {
            $query['account_id'] = $request->query('user_id');
        }

        return redirect()->route('admin.manage-permissions', $query);
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

        app(PermissionSyncImpactService::class)->applyImpact($targetUser, $permissions, $user);

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
     * Preview permission sync impact for a Manufacturer or Dealer (Admin)
     */
    public function previewResellerPermissionImpact(Request $request, $resellerId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $account = $this->findManageableAccount($resellerId);
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        return $this->buildPermissionImpactResponse($account, $request->input('permissions', []), $user);
    }

    /**
     * Preview permission sync impact for a user (Admin)
     */
    public function previewUserPermissionImpact(Request $request, $userId)
    {
        $user = Auth::user();

        if ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $targetUser = Writer::find($userId);
        if (!$targetUser || $targetUser->user_type !== 'User') {
            return response()->json(['error' => 'User not found'], 404);
        }

        return $this->buildPermissionImpactResponse($targetUser, $request->input('permissions', []), $user);
    }

    /**
     * Preview permission sync impact for a child user (Reseller or Admin)
     */
    public function previewChildUserPermissionImpact(Request $request, $userId)
    {
        $user = Auth::user();
        $childUser = Writer::find($userId);

        if (!$childUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->user_type === 'Reseller') {
            if (!$this->canManageChildUser($user, $childUser)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $hierarchyError = $this->validatePermissionHierarchy($user, $childUser, $request->input('permissions', []));
            if ($hierarchyError) {
                return response()->json(['error' => $hierarchyError], 422);
            }
        } elseif ($user->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->buildPermissionImpactResponse($childUser, $request->input('permissions', []), $user);
    }

    private function buildPermissionImpactResponse(Writer $targetUser, array $permissions, $assigningUser)
    {
        $impactService = app(PermissionSyncImpactService::class);
        $result = $impactService->previewImpact($targetUser, $permissions, $assigningUser);

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'hasImpact' => $result['hasImpact'],
            'childUsers' => $result['childUsers'],
            'removingSettingsView' => $result['removingSettingsView'] ?? false,
            'removingDeviceView' => $result['removingDeviceView'] ?? false,
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

    private function findManageableAccount($accountId): ?Writer
    {
        return $this->adminOwnedAccountsQuery()
            ->where('id', $accountId)
            ->first();
    }

    /**
     * Read-only account chain below an Admin-created account, for the "Hierarchy Summary" on
     * Manage Permissions. Parent is parent_user_id, else created_by (same rule the permission
     * cascade uses). Does not change any permission or hierarchy data.
     */
    public function getAccountHierarchy($accountId)
    {
        if (Auth::user()->user_type !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $root = $this->findManageableAccount($accountId);
        if (!$root) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $accounts = Writer::where('is_deleted', 0)
            ->whereNotIn('user_type', ['Admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'user_type', 'created_by', 'parent_user_id']);

        $childrenOf = [];
        foreach ($accounts as $account) {
            $parentId = (int) ($account->parent_user_id ?: $account->created_by);
            if ($parentId && $parentId !== (int) $account->id) {
                $childrenOf[$parentId][] = $account;
            }
        }

        // Collect the whole chain first (cycle-safe), then count devices for those accounts only.
        $chainIds = [(int) $root->id];
        $queue = [(int) $root->id];
        $seen = [(int) $root->id => true];
        while ($queue) {
            $id = array_shift($queue);
            foreach ($childrenOf[$id] ?? [] as $child) {
                if (!isset($seen[$child->id])) {
                    $seen[$child->id] = true;
                    $chainIds[] = (int) $child->id;
                    $queue[] = (int) $child->id;
                }
            }
        }
        $devicesBy = DB::table('devices')
            ->where('is_deleted', '0')
            ->whereIn('user_id', $chainIds)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'user_id');

        $counts = ['manufacturers' => 0, 'dealers' => 0, 'others' => 0, 'total' => 0, 'devices' => (int) ($devicesBy[$root->id] ?? 0), 'levels' => 0];
        $visited = [(int) $root->id => true];
        $build = function ($account, int $depth) use (&$build, &$counts, &$visited, $childrenOf, $devicesBy) {
            $node = [
                'id' => (int) $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'type' => $account->user_type,
                'type_label' => $this->accountTypeLabel($account->user_type),
                'devices' => (int) ($devicesBy[$account->id] ?? 0),
                'children' => [],
            ];
            foreach ($childrenOf[$account->id] ?? [] as $child) {
                if (isset($visited[$child->id])) {
                    continue;
                }
                $visited[$child->id] = true;
                $counts['total']++;
                $counts['levels'] = max($counts['levels'], $depth + 1);
                $counts['devices'] += (int) ($devicesBy[$child->id] ?? 0);
                if ($child->user_type === 'Reseller') {
                    $counts['manufacturers']++;
                } elseif ($child->user_type === 'User') {
                    $counts['dealers']++;
                } else {
                    $counts['others']++;
                }
                $node['children'][] = $build($child, $depth + 1);
            }
            return $node;
        };

        return response()->json([
            'root' => $build($root, 0),
            'counts' => $counts,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Manufacturer / Dealer accounts created by an Admin. Child accounts created by a
     * Manufacturer are managed by that Manufacturer (Manage Child Permissions), not here.
     */
    private function adminOwnedAccountsQuery()
    {
        return Writer::whereIn('user_type', ['Reseller', 'User'])
            ->where('is_deleted', 0)
            ->whereIn('created_by', Writer::where('user_type', 'Admin')->pluck('id'));
    }

    private function accountTypeLabel(string $userType): string
    {
        return match ($userType) {
            'Reseller' => 'Manufacturer',
            'User' => 'Dealer',
            default => $userType,
        };
    }
}
