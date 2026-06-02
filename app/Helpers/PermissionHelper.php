<?php

namespace App\Helpers;

use App\Permission;
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
    private static function getGrantedKeys($user = null): array
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

        $cacheKey = $user->id;
        if (!isset(self::$cache[$cacheKey])) {
            // Merge role permissions + direct user permissions
            $keys = [];

            if ($user->role) {
                foreach ($user->role->permissions as $p) {
                    $keys[] = $p->key;
                }
            }

            foreach ($user->permissions as $p) {
                $keys[] = $p->key;
            }

            self::$cache[$cacheKey] = array_unique($keys);
        }

        return self::$cache[$cacheKey];
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
}
