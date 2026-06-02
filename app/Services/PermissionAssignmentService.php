<?php

namespace App\Services;

use App\User;
use App\Permission;
use App\PermissionAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionAssignmentService
{
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

            // Revoke from target user
            DB::table('user_permissions')
                ->where('user_id', $targetUser->id)
                ->where('permission_id', $permission->id)
                ->delete();

            // Log the revocation
            PermissionAuditLog::log(
                $targetUser,
                $permission,
                'revoked',
                $revokingUser,
                $reason ?? 'Cascading revocation from parent'
            );

            // Cascade revocation to all descendants
            $affectedCount = $this->cascadeRevoke($targetUser, $permission, $revokingUser);

            DB::commit();

            // Clear permission cache
            \App\Helpers\PermissionHelper::flushCache();

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
     * Revoke permission from all descendants recursively
     *
     * @param User $parentUser
     * @param Permission $permission
     * @param User $revokingUser
     * @return int - Number of users affected
     */
    private function cascadeRevoke(User $parentUser, Permission $permission, User $revokingUser): int
    {
        $affectedCount = 0;

        // Get all direct children
        $children = User::where('parent_user_id', $parentUser->id)->get();

        foreach ($children as $child) {
            // Remove permission from child
            DB::table('user_permissions')
                ->where('user_id', $child->id)
                ->where('permission_id', $permission->id)
                ->delete();

            // Log the cascading revocation
            PermissionAuditLog::log(
                $child,
                $permission,
                'revoked',
                $revokingUser,
                'Cascaded from parent: ' . $parentUser->name
            );

            $affectedCount++;

            // Recursively cascade to grandchildren
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
     * @param User $targetUser
     * @param array $permissionIds - New permission IDs
     * @param User|null $assigningUser
     * @return array ['success' => bool, 'message' => string, 'added' => int, 'removed' => int]
     */
    public function syncPermissions(
        User $targetUser,
        array $permissionIds,
        User $assigningUser = null
    ): array {
        if (!$assigningUser) {
            $assigningUser = auth()->user();
        }

        // Validation: User type cannot have account_management
        if ($targetUser->user_type === 'User') {
            $accountMgmtPerms = DB::table('permissions')
                ->whereIn('id', $permissionIds)
                ->where('module', 'account_management')
                ->count();

            if ($accountMgmtPerms > 0) {
                return [
                    'success' => false,
                    'message' => 'User type cannot have Account Management permissions'
                ];
            }
        }

        try {
            DB::beginTransaction();

            // Get current permissions
            $currentPermIds = DB::table('user_permissions')
                ->where('user_id', $targetUser->id)
                ->pluck('permission_id')
                ->toArray();

            // Permissions to add
            $toAdd = array_diff($permissionIds, $currentPermIds);

            // Permissions to remove
            $toRemove = array_diff($currentPermIds, $permissionIds);

            // Add new permissions
            foreach ($toAdd as $permId) {
                $permission = Permission::find($permId);
                if ($permission) {
                    $this->assignPermission($targetUser, $permission, $assigningUser, 'Bulk sync');
                }
            }

            // Remove old permissions
            foreach ($toRemove as $permId) {
                $permission = Permission::find($permId);
                if ($permission) {
                    $this->revokePermission($targetUser, $permission, $assigningUser, 'Bulk sync');
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Permissions synced successfully',
                'added' => count($toAdd),
                'removed' => count($toRemove)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission sync failed', [
                'target_user_id' => $targetUser->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to sync permissions'];
        }
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
     * Get all permissions a user can assign (their own permissions)
     *
     * @param User $user
     * @return Collection
     */
    public function getAssignablePermissions(User $user): Collection
    {
        if ($user->user_type === 'Admin') {
            return Permission::where('is_active', 1)->get();
        }

        // Non-admin can only assign their own permissions
        return $user->permissions()
            ->where('is_active', 1)
            ->get();
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
}
