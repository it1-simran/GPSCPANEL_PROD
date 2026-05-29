<?php

namespace App\Helpers;

use App\Permission;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if user has permission to view a module
     */
    public static function canViewModule($moduleName)
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Admin can view all modules
        if ($user->user_type === 'Admin') {
            return true;
        }

        // Check if user has view permission for this module
        $permissionKey = $moduleName . '.view';

        // Get user's role permissions
        $rolePermissions = $user->role ?
            $user->role->permissions()->where('key', $permissionKey)->exists() : false;

        // Get user's direct permissions
        $userPermissions = $user->permissions()
            ->where('permissions.key', $permissionKey)
            ->exists();

        return $rolePermissions || $userPermissions;
    }

    /**
     * Get all accessible modules for current user
     */
    public static function getAccessibleModules()
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        // Admin can access all modules
        if ($user->user_type === 'Admin') {
            return ['account_management', 'device_management', 'certificate_management', 'settings_management'];
        }

        // Get modules where user has view permission
        $modules = [];

        if ($user->role) {
            $roleModules = $user->role->permissions()
                ->where('action', 'view')
                ->pluck('module')
                ->unique()
                ->toArray();
            $modules = array_merge($modules, $roleModules);
        }

        $userModules = $user->permissions()
            ->where('permissions.action', 'view')
            ->pluck('permissions.module')
            ->unique()
            ->toArray();
        $modules = array_merge($modules, $userModules);

        return array_unique($modules);
    }
}
