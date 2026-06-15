<?php

namespace App\Services;

use App\User;
use App\Permission;
use App\PermissionAuditLog;
use App\Events\PermissionChanged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

class PermissionAssignmentService
{
    private const RESELLER_DEALER_DEFAULT_EXCLUDED_KEYS = [
        'certificate_management.view',
    ];

    /**
     * Permission keys disabled by default for Manufacturer (Reseller) and Dealer (User) accounts.
     *
     * @return string[]
     */
    public function getResellerDealerDefaultExcludedKeys(): array
    {
        return self::RESELLER_DEALER_DEFAULT_EXCLUDED_KEYS;
    }

    /**
     * Remove permissions that should stay off by default for Reseller/Dealer accounts.
     */
    public function stripResellerDealerDefaultExclusions(array $permissionIds): array
    {
        $excludedIds = Permission::whereIn('key', $this->getResellerDealerDefaultExcludedKeys())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($excludedIds)) {
            return array_values(array_unique(array_map('intval', $permissionIds)));
        }

        return array_values(array_diff(
            array_map('intval', $permissionIds),
            $excludedIds
        ));
    }

    /**
     * Assign permission to a user with hierarchy validation
     *
     * @param User $targetUser - User receiving permission
     * @param Permission|int $permission - Permission to assign
     * @param User|null $assigningUser - User assigning (defaults to Auth::user())
     * @param string|null $reason - Reason for assignment
     * @return array ['success' => bool, 'message' => string]
     */
    public function assignPermission(
        User $targetUser,
        $permission,
        User $assigningUser = null,
        string $reason = null
    ): array {
        if (!$assigningUser) {
            $assigningUser = auth()->user();
        }

        // Convert permission to Permission object if ID provided
        if (is_int($permission)) {
            $permission = Permission::find($permission);
            if (!$permission) {
                return ['success' => false, 'message' => 'Permission not found'];
            }
        }

        // Validation checks
        $validation = $this->validatePermissionAssignment($targetUser, $permission, $assigningUser);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        try {
            DB::beginTransaction();

            // Check if already assigned
            $exists = DB::table('user_permissions')
                ->where('user_id', $targetUser->id)
                ->where('permission_id', $permission->id)
                ->exists();

            if (!$exists) {
                DB::table('user_permissions')->insert([
                    'user_id' => $targetUser->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Log the assignment
                PermissionAuditLog::log(
                    $targetUser,
                    $permission,
                    'assigned',
                    $assigningUser,
                    $reason
                );

                // Clear permission cache
                \App\Helpers\PermissionHelper::flushCache();

                // Fire event for real-time permission tracking
                Event::dispatch(new PermissionChanged(
                    $targetUser->id,
                    'granted',
                    $permission->key,
                    $assigningUser->id,
                    ['reason' => $reason]
                ));
            }

            DB::commit();
            return ['success' => true, 'message' => 'Permission assigned successfully'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission assignment failed', [
                'target_user_id' => $targetUser->id,
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to assign permission'];
        }
    }

    /**
     * Revoke permission from a user and cascade to all descendants
     *
     * @param User $targetUser - User losing permission
     * @param Permission|int $permission - Permission to revoke
     * @param User|null $revokingUser - User revoking (defaults to Auth::user())
     * @param string|null $reason - Reason for revocation
     * @return array ['success' => bool, 'message' => string, 'affected_users' => int]
     */
    public function revokePermission(
        User $targetUser,
        $permission,
        User $revokingUser = null,
        string $reason = null
    ): array {
        if (!$revokingUser) {
            $revokingUser = auth()->user();
        }

        // Convert permission to Permission object if ID provided
        if (is_int($permission)) {
            $permission = Permission::find($permission);
            if (!$permission) {
                return ['success' => false, 'message' => 'Permission not found'];
            }
        }

        try {
            DB::beginTransaction();

            // Get all dependent permissions (child permissions that depend on this permission)
            $dependentPermissionIds = [];
            if ($permission instanceof Permission) {
                $dependentPermissionIds = $permission->getDependentPermissionIds();
            } else {
                $perm = Permission::find($permission);
                if ($perm) {
                    $dependentPermissionIds = $perm->getDependentPermissionIds();
                }
            }

            // Revoke from target user
            DB::table('user_permissions')
                ->where('user_id', $targetUser->id)
                ->where('permission_id', $permission->id)
                ->delete();

            // Also revoke all dependent/child permissions
            if (!empty($dependentPermissionIds)) {
                DB::table('user_permissions')
                    ->where('user_id', $targetUser->id)
                    ->whereIn('permission_id', $dependentPermissionIds)
                    ->delete();
            }

            // Log the revocation
            PermissionAuditLog::log(
                $targetUser,
                $permission,
                'revoked',
                $revokingUser,
                $reason ?? 'Cascading revocation from parent'
            );

            // Cascade revocation to all descendants (child users)
            $affectedCount = $this->cascadeRevoke($targetUser, $permission, $revokingUser);

            // Also cascade dependent permissions to child users
            if (!empty($dependentPermissionIds)) {
                foreach ($dependentPermissionIds as $depPermId) {
                    $depPerm = Permission::find($depPermId);
                    if ($depPerm) {
                        $this->cascadeRevoke($targetUser, $depPerm, $revokingUser);
                    }
                }
            }

            DB::commit();

            // Clear permission cache
            \App\Helpers\PermissionHelper::flushCache();

            // Fire event for real-time permission tracking
            Event::dispatch(new PermissionChanged(
                $targetUser->id,
                'revoked',
                $permission->key,
                $revokingUser->id,
                ['reason' => $reason, 'cascade_count' => $affectedCount]
            ));

            $totalAffected = 1 + $affectedCount; // Include target user
            return [
                'success' => true,
                'message' => "Permission revoked from user and {$affectedCount} descendant(s)",
                'affected_users' => $totalAffected
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission revocation failed', [
                'target_user_id' => $targetUser->id,
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to revoke permission'];
        }
    }

    /**
     * Cascade permission removal by ID through all descendants (no audit log — called inside tx)
     */
    private function cascadeRevokeById(int $parentUserId, int $permissionId): void
    {
        $childIds = DB::table('writers')
            ->where(function ($query) use ($parentUserId) {
                $query->where('parent_user_id', $parentUserId)
                    ->orWhere('created_by', $parentUserId);
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($childIds)) return;

        // Remove permission from all direct children
        DB::table('user_permissions')
            ->whereIn('user_id', $childIds)
            ->where('permission_id', $permissionId)
            ->delete();

        // Recurse into grandchildren
        foreach ($childIds as $childId) {
            $this->cascadeRevokeById($childId, $permissionId);
        }
    }

    /**
     * Revoke permission from all descendants recursively (with audit logging — call outside tx)
     */
    private function cascadeRevoke(User $parentUser, Permission $permission, User $revokingUser): int
    {
        $affectedCount = 0;
        $children = User::where(function ($query) use ($parentUser) {
                $query->where('parent_user_id', $parentUser->id)
                    ->orWhere('created_by', $parentUser->id);
            })
            ->get();

        foreach ($children as $child) {
            DB::table('user_permissions')
                ->where('user_id', $child->id)
                ->where('permission_id', $permission->id)
                ->delete();

            try {
                PermissionAuditLog::log($child, $permission, 'revoked', $revokingUser, 'Cascaded from parent: ' . $parentUser->name);
            } catch (\Throwable $e) {
                Log::warning('Cascade audit log failed', ['error' => $e->getMessage()]);
            }

            $affectedCount++;
            $affectedCount += $this->cascadeRevoke($child, $permission, $revokingUser);
        }

        return $affectedCount;
    }

    /**
     * Assign multiple permissions at once with validation
     *
     * @param User $targetUser
     * @param array $permissionIds - Array of permission IDs
     * @param User|null $assigningUser
     * @param string|null $reason
     * @return array ['success' => bool, 'message' => string, 'assigned' => int, 'failed' => int]
     */
    public function assignMultiple(
        User $targetUser,
        array $permissionIds,
        User $assigningUser = null,
        string $reason = null
    ): array {
        if (!$assigningUser) {
            $assigningUser = auth()->user();
        }

        $assigned = 0;
        $failed = 0;
        $errors = [];

        foreach ($permissionIds as $permId) {
            $permission = Permission::find($permId);
            if (!$permission) {
                $failed++;
                $errors[] = "Permission {$permId} not found";
                continue;
            }

            $result = $this->assignPermission($targetUser, $permission, $assigningUser, $reason);
            if ($result['success']) {
                $assigned++;
            } else {
                $failed++;
                $errors[] = $result['message'];
            }
        }

        return [
            'success' => $failed === 0,
            'message' => "Assigned {$assigned} permission(s), {$failed} failed",
            'assigned' => $assigned,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Sync permissions - replace all with new set
     *
     * @param mixed $targetUser
     * @param array $permissionIds - New permission IDs
     * @param mixed|null $assigningUser
     * @return array ['success' => bool, 'message' => string, 'added' => int, 'removed' => int]
     */
    /**
     * Compute permission sync diff without persisting changes.
     *
     * @return array{success:bool,message?:string,toAdd?:int[],toRemove?:int[],toRemoveWithDependents?:int[],finalPermissionIds?:int[]}
     */
    public function prepareSyncPlan(
        $targetUser,
        array $permissionIds,
        $assigningUser = null
    ): array {
        if (!$assigningUser) {
            $assigningUser = auth()->user();
        }

        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));
        $permissionIds = $this->applyDependencies($permissionIds);
        $permissionIds = $this->applyCreateEditPairing($permissionIds);

        if ($assigningUser && $assigningUser->user_type !== 'Admin') {
            $assignablePermissionIds = $this->getEffectiveAssignedPermissionIds($assigningUser);
            $disallowedPermissionIds = array_values(array_diff($permissionIds, $assignablePermissionIds));

            if (!empty($disallowedPermissionIds)) {
                $permission = Permission::whereIn('id', $disallowedPermissionIds)->first();
                $permName = $permission ? $permission->label : 'Permission #' . $disallowedPermissionIds[0];

                return [
                    'success' => false,
                    'message' => "Cannot assign '{$permName}' - this permission is beyond your access level.\nPlease refresh the page.",
                ];
            }
        }

        if ($targetUser->user_type === 'User') {
            $accountMgmtPerms = DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->where('module', 'account_management')
                ->count();

            if ($accountMgmtPerms > 0) {
                return [
                    'success' => false,
                    'message' => 'User type cannot have Account Management permissions',
                ];
            }
        }

        $currentPermIds = DB::table('user_permissions')
            ->where('user_id', $targetUser->id)
            ->pluck('permission_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $toAdd = array_values(array_diff($permissionIds, $currentPermIds));
        $toRemove = array_values(array_diff($currentPermIds, $permissionIds));
        $toRemoveWithDependents = $this->getPermissionsWithDependents(
            $this->applyCreateEditPairing($toRemove)
        );

        return [
            'success' => true,
            'toAdd' => $toAdd,
            'toRemove' => $toRemove,
            'toRemoveWithDependents' => $toRemoveWithDependents,
            'finalPermissionIds' => $permissionIds,
        ];
    }

    public function syncPermissions(
        $targetUser,
        array $permissionIds,
        $assigningUser = null
    ): array {
        if (!$assigningUser) {
            $assigningUser = auth()->user();
        }

        $plan = $this->prepareSyncPlan($targetUser, $permissionIds, $assigningUser);
        if (!$plan['success']) {
            return [
                'success' => false,
                'message' => $plan['message'],
            ];
        }

        $toAdd = $plan['toAdd'];
        $toRemoveWithDependents = $plan['toRemoveWithDependents'];

        try {
            // --- Single atomic sync: delete removed, insert added ---
            DB::beginTransaction();

            if (!empty($toRemoveWithDependents)) {
                DB::table('user_permissions')
                    ->where('user_id', $targetUser->id)
                    ->whereIn('permission_id', $toRemoveWithDependents)
                    ->delete();

                // Cascade removal to ALL descendants for each revoked permission
                foreach ($toRemoveWithDependents as $permId) {
                    $this->cascadeRevokeById($targetUser->id, $permId);
                }
            }

            if (!empty($toAdd)) {
                $insertRows = [];
                foreach ($toAdd as $permId) {
                    $insertRows[] = [
                        'user_id'      => $targetUser->id,
                        'permission_id' => $permId,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
                DB::table('user_permissions')->insertOrIgnore($insertRows);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission sync failed', [
                'target_user_id' => $targetUser->id,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Failed to sync permissions: ' . $e->getMessage()];
        }

        // Clear permission cache
        \App\Helpers\PermissionHelper::flushCache();

        // Audit log AFTER commit — never inside the transaction
        try {
            foreach ($toAdd as $permId) {
                $perm = Permission::find($permId);
                if ($perm) PermissionAuditLog::log($targetUser, $perm, 'assigned', $assigningUser, 'Bulk sync');
            }
            foreach ($toRemoveWithDependents as $permId) {
                $perm = Permission::find($permId);
                if ($perm) PermissionAuditLog::log($targetUser, $perm, 'revoked', $assigningUser, 'Bulk sync');
            }
        } catch (\Throwable $e) {
            Log::warning('Audit log failed (permissions were saved)', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => 'Permissions synced successfully',
            'added'   => count($toAdd),
            'removed' => count($toRemoveWithDependents),
            'permissions' => DB::table('user_permissions')
                ->where('user_id', $targetUser->id)
                ->pluck('permission_id')
                ->map(fn($id) => (int) $id)
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Validate permission assignment against hierarchy rules
     *
     * @param User $targetUser - User receiving permission
     * @param Permission $permission - Permission to assign
     * @param User $assigningUser - User assigning permission
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validatePermissionAssignment(
        User $targetUser,
        Permission $permission,
        User $assigningUser
    ): array {
        // Rule 1: Only Admin and Reseller can manage other users
        if (!in_array($assigningUser->user_type, ['Admin', 'Reseller'])) {
            return [
                'valid' => false,
                'message' => 'Only Admin and Reseller can assign permissions'
            ];
        }

        // Rule 2: Reseller can only manage their children
        if ($assigningUser->user_type === 'Reseller') {
            if (!$assigningUser->canManage($targetUser)) {
                return [
                    'valid' => false,
                    'message' => 'You can only assign permissions to your child users'
                ];
            }
        }

        // Rule 3: Assigning user must have the permission
        if (!$assigningUser->hasPermission($permission->key)) {
            return [
                'valid' => false,
                'message' => "You don't have permission to assign '{$permission->label}'"
            ];
        }

        // Rule 4: User type cannot have account_management permissions
        if ($targetUser->user_type === 'User' && $permission->module === 'account_management') {
            return [
                'valid' => false,
                'message' => 'User type cannot be assigned Account Management permissions'
            ];
        }

        // Rule 5: Target user cannot be parent or ancestor of assigning user
        // (can't create a cycle in the hierarchy)
        if ($targetUser->id === $assigningUser->id) {
            return [
                'valid' => false,
                'message' => 'Cannot assign permissions to yourself'
            ];
        }

        return ['valid' => true, 'message' => 'Permission assignment is valid'];
    }

    /**
     * Default permissions for an existing Reseller or User (Dealer) account,
     * based on the role template defined in role_permissions.
     */
    public function getDefaultPermissionIdsForExistingUser($user): array
    {
        $roleSlug = match ($user->user_type ?? '') {
            'Reseller' => 'reseller',
            'User' => 'user',
            default => null,
        };

        if (!$roleSlug) {
            return [];
        }

        $permissionIds = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->where('roles.slug', $roleSlug)
            ->where('permissions.is_active', 1)
            ->pluck('permissions.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        return $this->stripResellerDealerDefaultExclusions(
            $this->applyDependencies(
                $this->applyCreateEditPairing($permissionIds)
            )
        );
    }

    /**
     * Default permissions for a newly created account.
     * Admin-created accounts use system defaults; reseller-created children inherit parent permissions.
     */
    public function getDefaultPermissionIdsForNewAccount($creator, string $targetUserType): array
    {
        if ($creator && $creator->user_type === 'Reseller') {
            $permissionIds = $this->getEffectiveAssignedPermissionIds($creator);

            if ($targetUserType === 'User') {
                $permissionIds = Permission::whereIn('id', $permissionIds)
                    ->where('module', '!=', 'account_management')
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->toArray();
            }

            return $this->stripResellerDealerDefaultExclusions(
                $this->applyDependencies(
                    $this->applyCreateEditPairing($permissionIds)
                )
            );
        }

        $permissionIds = Permission::where('is_active', 1)
            ->whereNotIn('key', $this->getResellerDealerDefaultExcludedKeys())
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        return $this->stripResellerDealerDefaultExclusions(
            $this->applyDependencies(
                $this->applyCreateEditPairing($permissionIds)
            )
        );
    }

    /**
     * Get all permissions a user can assign (their own permissions)
     *
     * @param mixed $user
     * @return Collection
     */
    public function getAssignablePermissions($user): Collection
    {
        if ($user->user_type === 'Admin') {
            return Permission::where('is_active', 1)->get();
        }

        // Non-admin can only assign their own permissions
        return Permission::whereIn('id', $this->getEffectiveAssignedPermissionIds($user))
            ->where('is_active', 1)
            ->get();
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

    /**
     * Get audit log for a user's permission changes
     *
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function getPermissionAuditLog(User $user, int $limit = 50): Collection
    {
        return PermissionAuditLog::forUser($user->id)
            ->with('permission', 'assignedBy')
            ->limit($limit)
            ->get();
    }

    /**
     * Apply permission dependencies: if a child is being added, ensure parent is also added
     * @param array $permissionIds
     * @return array Updated permission IDs with parents included
     */
    /**
     * Pair create/edit permissions per module.
     */
    private function applyCreateEditPairing(array $permissionIds): array
    {
        $pairedIds = $permissionIds;

        $permissions = Permission::whereIn('id', $permissionIds)
            ->where('is_active', 1)
            ->get();

        foreach ($permissions as $permission) {
            if (!preg_match('/^(.+)\.(create|edit)$/', $permission->key, $matches)) {
                continue;
            }

            $pairKey = $matches[1] . ($matches[2] === 'create' ? '.edit' : '.create');
            $pairId = Permission::where('key', $pairKey)->where('is_active', 1)->value('id');

            if ($pairId) {
                $pairedIds[] = (int) $pairId;
            }
        }

        return array_values(array_unique($pairedIds));
    }

    private function applyDependencies(array $permissionIds): array
    {
        $permissionsToAdd = [];

        foreach ($permissionIds as $permId) {
            $permissionsToAdd[] = $permId;

            // Get the permission and all its parents
            $permission = Permission::find($permId);
            if ($permission && $permission->hasParent()) {
                $parent = $permission->parent;
                while ($parent) {
                    if (!in_array($parent->id, $permissionsToAdd)) {
                        $permissionsToAdd[] = $parent->id;
                    }
                    $parent = $parent->parent;
                }
            }
        }

        return array_values(array_unique($permissionsToAdd));
    }

    /**
     * Get permissions with all their dependent children
     * @param array $permissionIds
     * @return array All permission IDs including dependent children
     */
    private function getPermissionsWithDependents(array $permissionIds): array
    {
        $allIds = [];

        foreach ($permissionIds as $permId) {
            $allIds[] = $permId;

            $permission = Permission::find($permId);
            if ($permission) {
                $dependentIds = $permission->getDependentPermissionIds();
                $allIds = array_merge($allIds, $dependentIds);
            }
        }

        return array_values(array_unique($allIds));
    }
}
