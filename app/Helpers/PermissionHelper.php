<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Per-request permission cache: user_id => array of permission keys
     * Prevents N+1 queries when the sidebar checks many permissions in one page load.
     */
    private static array $cache = [];

    /**
     * Return the set of permission keys granted to $user (or the currently
     * authenticated user).  Admin users are granted everything.
     */
    private static function getGrantedKeys($user = null, array $visitedUserIds = []): array
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) {
            return [];
        }

        // Admin always has everything — no DB lookup needed.
        if ($user->user_type === 'Admin') {
            return ['*'];
        }

        $cacheKey = (int) $user->id;
        if (!isset(self::$cache[$cacheKey])) {
            if (in_array($cacheKey, $visitedUserIds, true)) {
                return [];
            }

            $keys = [];
            $directPermissions = $user->permissions()
                ->where('permissions.is_active', 1)
                ->get();

            foreach ($directPermissions as $p) {
                $keys[] = $p->key;
            }

            $parent = self::getParentUser($user);
            if ($parent && (int) $parent->id !== $cacheKey) {
                $parentKeys = self::getGrantedKeys($parent, array_merge($visitedUserIds, [$cacheKey]));

                if (!in_array('*', $parentKeys, true)) {
                    $keys = array_values(array_intersect($keys, $parentKeys));
                }
            }

            self::$cache[$cacheKey] = array_unique($keys);
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Resolve the account parent used for inherited permissions.
     */
    private static function getParentUser($user)
    {
        $parentId = $user->parent_user_id ?: $user->created_by;

        if (!$parentId) {
            return null;
        }

        return \App\Writer::find($parentId);
    }

    /**
     * Flush the in-memory cache (useful after permission updates in tests).
     */
    public static function flushCache(): void
    {
        self::$cache = [];
    }

    /**
     * Check if a user has a specific permission key.
     *
     * @param  string       $permissionKey  e.g. "account_management.create"
     * @param  mixed|null   $user           defaults to Auth::user()
     * @return bool
     */
    public static function hasPermission(string $permissionKey, $user = null): bool
    {
        $keys = self::getGrantedKeys($user);

        // Wildcard means admin
        if (in_array('*', $keys, true)) {
            return true;
        }

        return in_array($permissionKey, $keys, true);
    }

    /**
     * Check if the current user can view a whole module (has the *.view permission).
     *
     * @param  string  $moduleName  e.g. "account_management"
     * @return bool
     */
    public static function canViewModule(string $moduleName): bool
    {
        return self::hasPermission($moduleName . '.view');
    }

    /**
     * Check if the current user has ANY permission for a module.
     * Used to determine if a module section should be visible in sidebar.
     *
     * @param  string  $moduleName  e.g. "settings_management"
     * @return bool
     */
    public static function hasAnyModulePermission(string $moduleName): bool
    {
        $keys = self::getGrantedKeys();

        // Wildcard means admin
        if (in_array('*', $keys, true)) {
            return true;
        }

        // Check if any permission key starts with the module name
        foreach ($keys as $key) {
            if (str_starts_with($key, $moduleName . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a list of module names where the user holds a view permission.
     * (Used by the sidebar to decide which top-level sections to render.)
     *
     * @return string[]
     */
    public static function getAccessibleModules(): array
    {
        $keys = self::getGrantedKeys();

        if (in_array('*', $keys, true)) {
            return ['account_management', 'device_management', 'certificate_management', 'settings_management'];
        }

        $modules = [];
        foreach ($keys as $key) {
            if (str_ends_with($key, '.view')) {
                $modules[] = substr($key, 0, -strlen('.view'));
            }
        }

        return array_unique($modules);
    }

    /**
     * Check if a user can perform a specific action in a module
     *
     * @param  string  $module  e.g. "account_management"
     * @param  string  $action  e.g. "create", "edit", "delete"
     * @param  mixed|null   $user
     * @return bool
     */
    public static function canPerformAction(string $module, string $action, $user = null): bool
    {
        $permissionKey = "{$module}.{$action}";
        return self::hasPermission($permissionKey, $user);
    }

    /**
     * Check if current user is an Admin
     *
     * @return bool
     */
    public static function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->user_type === 'Admin';
    }

    /**
     * Check if current user is a Reseller
     *
     * @return bool
     */
    public static function isReseller(): bool
    {
        $user = Auth::user();
        return $user && $user->user_type === 'Reseller';
    }

    /**
     * Get all accessible permission keys for a user
     *
     * @param  mixed|null  $user
     * @return array
     */
    public static function getAllAccessiblePermissions($user = null): array
    {
        return self::getGrantedKeys($user);
    }

    /**
     * Check if parent can assign a permission to child
     * Child cannot have more permissions than parent
     *
     * @param  string  $permissionKey
     * @param  mixed|null  $parentUser
     * @return bool
     */
    public static function canParentAssignPermission(string $permissionKey, $parentUser = null): bool
    {
        if (!$parentUser) {
            $parentUser = Auth::user();
        }

        // Admin can assign any permission
        if ($parentUser->user_type === 'Admin') {
            return true;
        }

        // Parent must have the permission to assign it
        return self::hasPermission($permissionKey, $parentUser);
    }

    /**
     * Check if user can access account management
     * Only Admin and Reseller can access, not User/Dealer
     *
     * @param  mixed|null  $user
     * @return bool
     */
    public static function canAccessAccountManagement($user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return false;
        }

        // Only Admin and Reseller can access
        return in_array($user->user_type, ['Admin', 'Reseller']);
    }

    /**
     * Get permissions a user can assign to their children
     * Only their own permissions can be assigned
     *
     * @param  mixed|null  $user
     * @return array
     */
    public static function getAssignablePermissionKeys($user = null): array
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return [];
        }

        // Admin can assign all permissions
        if ($user->user_type === 'Admin') {
            return \App\Permission::where('is_active', 1)->pluck('key')->toArray();
        }

        // Others can only assign their own permissions
        return self::getGrantedKeys($user);
    }

    /**
     * Validate permission assignment (child cannot exceed parent)
     *
     * @param  string  $permissionKey
     * @param  mixed  $targetUser  The user getting the permission
     * @param  mixed|null  $assigningUser  The user assigning it (defaults to Auth::user())
     * @return array  ['valid' => bool, 'message' => string]
     */
    public static function validatePermissionAssignment(
        string $permissionKey,
        $targetUser,
        $assigningUser = null
    ): array {
        if (!$assigningUser) {
            $assigningUser = Auth::user();
        }

        // Check if assigning user can manage target user
        if (!$assigningUser->canManage($targetUser)) {
            return [
                'valid' => false,
                'message' => 'You cannot assign permissions to this user.'
            ];
        }

        // Check if assigning user has the permission
        if (!self::canParentAssignPermission($permissionKey, $assigningUser)) {
            return [
                'valid' => false,
                'message' => "You don't have permission to assign '{$permissionKey}'."
            ];
        }

        // All validations passed
        return [
            'valid' => true,
            'message' => 'Permission assignment is valid.'
        ];
    }
}
