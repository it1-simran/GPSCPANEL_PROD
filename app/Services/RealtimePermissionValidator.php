<?php

namespace App\Services;

use App\Writer as User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Real-Time Permission Validator Service
 *
 * Provides real-time permission validation by checking permissions
 * directly from the database on every request, bypassing any request-level caching.
 *
 * This ensures that when an admin revokes a user's permission, it takes effect
 * immediately on the next request, without waiting for session refresh.
 */
class RealtimePermissionValidator
{
    /**
     * Validate if a user has a specific permission (ALWAYS checks database)
     *
     * @param User $user The user to check
     * @param string $permissionKey Permission key (e.g., 'certificate_management.view')
     * @return bool True if user has permission, false otherwise
     */
    public static function isPermissionValid(User $user, string $permissionKey): bool
    {
        // Admin and Support users always have permission
        if (in_array($user->user_type, ['Admin', 'Support'])) {
            return true;
        }

        // For other users, check database directly (NEVER use cache)
        // First check user_permissions table (direct assignments)
        $userHasPermission = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $user->id)
            ->where('permissions.key', $permissionKey)
            ->exists();

        if ($userHasPermission) {
            return true;
        }

        // Then check role_permissions table (permissions via role)
        if ($user->role_id) {
            $roleHasPermission = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role_id', $user->role_id)
                ->where('permissions.key', $permissionKey)
                ->exists();

            if ($roleHasPermission) {
                return true;
            }
        }

        // Check parent user permissions (for hierarchical users like child resellers)
        if ($user->parent_user_id) {
            $parent = User::find($user->parent_user_id);
            if ($parent && self::isPermissionValid($parent, $permissionKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user's permissions have changed since last validation
     *
     * @param User $user The user to check
     * @param Carbon $lastCheckTime When the last permission check occurred
     * @return bool True if permissions have changed, false otherwise
     */
    public static function hasPermissionChanged(User $user, ?Carbon $lastCheckTime = null): bool
    {
        if (!$lastCheckTime) {
            return false;
        }

        // Check if any permission change occurred for this user after last check
        return DB::table('permission_changes')
            ->where('user_id', $user->id)
            ->where('changed_at', '>', $lastCheckTime)
            ->exists();
    }

    /**
     * Record a permission change in the audit log
     *
     * @param int $userId User whose permission changed
     * @param string $permissionKey Permission key that was changed
     * @param string $changeType Type of change: 'granted', 'revoked', 'updated'
     * @param int $changedBy User ID who made the change (usually admin)
     * @return void
     */
    public static function recordPermissionChange(
        int $userId,
        string $permissionKey,
        string $changeType,
        int $changedBy
    ): void {
        // Find permission ID by key
        $permission = DB::table('permissions')
            ->where('key', $permissionKey)
            ->first();

        DB::table('permission_changes')->insert([
            'user_id' => $userId,
            'permission_id' => $permission->id ?? null,
            'permission_key' => $permissionKey,
            'change_type' => $changeType,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }

    /**
     * Record a bulk permission change for a user
     *
     * @param int $userId User whose permissions changed
     * @param int $changedBy User ID who made the change (usually admin)
     * @param string $changeDescription Description of what changed
     * @return void
     */
    public static function recordBulkPermissionChange(
        int $userId,
        int $changedBy,
        string $changeDescription = 'bulk_update'
    ): void {
        DB::table('permission_changes')->insert([
            'user_id' => $userId,
            'permission_id' => null,
            'permission_key' => $changeDescription,
            'change_type' => 'updated',
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);
    }

    /**
     * Get the timestamp of the last permission change for a user
     *
     * @param User $user The user to check
     * @return Carbon|null Timestamp of last permission change
     */
    public static function getLastPermissionChangeTime(User $user): ?Carbon
    {
        $lastChange = DB::table('permission_changes')
            ->where('user_id', $user->id)
            ->orderBy('changed_at', 'desc')
            ->first();

        return $lastChange ? Carbon::parse($lastChange->changed_at) : null;
    }

    /**
     * Get all permission changes for a user
     *
     * @param User $user The user to get changes for
     * @param int $limit Number of changes to return
     * @return array Permission changes
     */
    public static function getUserPermissionHistory(User $user, int $limit = 50): array
    {
        return DB::table('permission_changes')
            ->where('user_id', $user->id)
            ->orderBy('changed_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Check if user has ANY permission in a module
     *
     * @param User $user The user to check
     * @param string $module Module name (e.g., 'certificate_management')
     * @return bool True if user has any permission in the module
     */
    public static function hasAnyModulePermission(User $user, string $module): bool
    {
        // Admin and Support always have access
        if (in_array($user->user_type, ['Admin', 'Support'])) {
            return true;
        }

        // Check if user has ANY permission starting with module name
        // e.g., 'certificate_management.view', 'certificate_management.create', etc.
        $userHasPermission = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $user->id)
            ->where('permissions.key', 'like', $module . '.%')
            ->exists();

        if ($userHasPermission) {
            return true;
        }

        // Check role permissions
        if ($user->role_id) {
            $roleHasPermission = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role_id', $user->role_id)
                ->where('permissions.key', 'like', $module . '.%')
                ->exists();

            if ($roleHasPermission) {
                return true;
            }
        }

        // Check parent permissions
        if ($user->parent_user_id) {
            $parent = User::find($user->parent_user_id);
            if ($parent && self::hasAnyModulePermission($parent, $module)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permissions for a user
     *
     * @param User $user The user to get permissions for
     * @return array Array of permission keys
     */
    public static function getUserPermissions(User $user): array
    {
        // Admin and Support have all permissions
        if (in_array($user->user_type, ['Admin', 'Support'])) {
            return ['*'];
        }

        $permissions = [];

        // Get user direct permissions
        $userPermissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $user->id)
            ->pluck('permissions.key')
            ->toArray();

        $permissions = array_merge($permissions, $userPermissions);

        // Get role permissions
        if ($user->role_id) {
            $rolePermissions = DB::table('role_permissions')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->where('role_permissions.role_id', $user->role_id)
                ->pluck('permissions.key')
                ->toArray();

            $permissions = array_merge($permissions, $rolePermissions);
        }

        // Get parent permissions
        if ($user->parent_user_id) {
            $parent = User::find($user->parent_user_id);
            if ($parent) {
                $parentPermissions = self::getUserPermissions($parent);
                $permissions = array_merge($permissions, $parentPermissions);
            }
        }

        return array_unique($permissions);
    }
}
