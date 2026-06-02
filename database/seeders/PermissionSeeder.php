<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator with full access', 'is_active' => 1]
        );

        $resellerRole = Role::firstOrCreate(
            ['slug' => 'reseller'],
            ['name' => 'Reseller', 'slug' => 'reseller', 'description' => 'Reseller with delegated permissions', 'is_active' => 1]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User', 'slug' => 'user', 'description' => 'End user with limited permissions', 'is_active' => 1]
        );

        // Define permissions - comprehensive list for all modules
        $permissions = [
            // Account Management
            ['key' => 'account_management.view', 'module' => 'account_management', 'action' => 'view', 'label' => 'View Account Management', 'order' => 1],
            ['key' => 'account_management.create', 'module' => 'account_management', 'action' => 'create', 'label' => 'Create Account', 'order' => 2],
            ['key' => 'account_management.edit', 'module' => 'account_management', 'action' => 'edit', 'label' => 'Edit Account', 'order' => 3],
            ['key' => 'account_management.delete', 'module' => 'account_management', 'action' => 'delete', 'label' => 'Delete Account', 'order' => 4],

            // Device Management
            ['key' => 'device_management.view', 'module' => 'device_management', 'action' => 'view', 'label' => 'View Device Management', 'order' => 1],
            ['key' => 'device_management.edit', 'module' => 'device_management', 'action' => 'edit', 'label' => 'Edit Device', 'order' => 2],

            // Certificate Management
            ['key' => 'certificate_management.view', 'module' => 'certificate_management', 'action' => 'view', 'label' => 'View Certificate', 'order' => 1],

            // Settings Management
            ['key' => 'settings_management.view', 'module' => 'settings_management', 'action' => 'view', 'label' => 'View Settings', 'order' => 1],
            ['key' => 'settings_management.create', 'module' => 'settings_management', 'action' => 'create', 'label' => 'Create Settings', 'order' => 2],
            ['key' => 'settings_management.edit', 'module' => 'settings_management', 'action' => 'edit', 'label' => 'Edit Settings', 'order' => 3],
            ['key' => 'settings_management.delete', 'module' => 'settings_management', 'action' => 'delete', 'label' => 'Delete Settings', 'order' => 4],
            ['key' => 'settings_management.assign_bulk', 'module' => 'settings_management', 'action' => 'assign_bulk', 'label' => 'Assign Settings Bulk', 'order' => 5],
        ];

        // Create permissions
        $permissionObjects = [];
        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(
                ['key' => $perm['key']],
                array_merge($perm, ['is_active' => 1])
            );
            $permissionObjects[$perm['key']] = $permission->id;
        }

        // Assign all permissions to Admin role
        $adminPermissions = Permission::where('is_active', 1)->get();
        $adminRole->permissions()->syncWithoutDetaching($adminPermissions->pluck('id'));

        // Assign default permissions to Reseller role
        // Reseller has: Account Management (all), Device Management (all), Settings Management (all)
        $resellerPermissions = Permission::whereIn('key', [
            // Account Management
            'account_management.view',
            'account_management.create',
            'account_management.edit',
            'account_management.delete',
            // Device Management
            'device_management.view',
            'device_management.edit',
            // Settings Management
            'settings_management.view',
            'settings_management.create',
            'settings_management.edit',
            'settings_management.delete',
            'settings_management.assign_bulk',
        ])->get();
        $resellerRole->permissions()->sync($resellerPermissions->pluck('id'));

        // Assign default permissions to User role
        // User has: Device Management (all), Settings Management (all), Certificate Management (view only)
        $userPermissions = Permission::whereIn('key', [
            // Device Management
            'device_management.view',
            'device_management.edit',
            // Settings Management
            'settings_management.view',
            'settings_management.create',
            'settings_management.edit',
            'settings_management.delete',
            'settings_management.assign_bulk',
            // Certificate Management (view only)
            'certificate_management.view',
        ])->get();
        $userRole->permissions()->sync($userPermissions->pluck('id'));
    }
}
