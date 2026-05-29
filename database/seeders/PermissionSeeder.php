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

        // Define permissions
        $permissions = [
            // Account Management
            ['key' => 'account_management.view', 'module' => 'account_management', 'action' => 'view', 'label' => 'View Account Management', 'order' => 1],
            ['key' => 'account_management.create', 'module' => 'account_management', 'action' => 'create', 'label' => 'Create Account', 'order' => 2],
            ['key' => 'account_management.edit', 'module' => 'account_management', 'action' => 'edit', 'label' => 'Edit Account', 'order' => 3],
            ['key' => 'account_management.delete', 'module' => 'account_management', 'action' => 'delete', 'label' => 'Delete Account', 'order' => 4],

            // Device Management
            ['key' => 'device_management.view', 'module' => 'device_management', 'action' => 'view', 'label' => 'View Device Management', 'order' => 1],
            ['key' => 'device_management.create', 'module' => 'device_management', 'action' => 'create', 'label' => 'Create Device', 'order' => 2],
            ['key' => 'device_management.edit', 'module' => 'device_management', 'action' => 'edit', 'label' => 'Edit Device', 'order' => 3],
            ['key' => 'device_management.delete', 'module' => 'device_management', 'action' => 'delete', 'label' => 'Delete Device', 'order' => 4],

            // Certificate Management
            ['key' => 'certificate_management.view', 'module' => 'certificate_management', 'action' => 'view', 'label' => 'View Certificate Management', 'order' => 1],
            ['key' => 'certificate_management.download', 'module' => 'certificate_management', 'action' => 'download', 'label' => 'Download Certificate', 'order' => 2],
            ['key' => 'certificate_management.print', 'module' => 'certificate_management', 'action' => 'print', 'label' => 'Print Certificate', 'order' => 3],

            // Settings Management
            ['key' => 'settings_management.view', 'module' => 'settings_management', 'action' => 'view', 'label' => 'View Settings', 'order' => 1],
            ['key' => 'settings_management.edit', 'module' => 'settings_management', 'action' => 'edit', 'label' => 'Edit Settings', 'order' => 2],
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

        // Assign basic permissions to Reseller role (can be overridden later)
        $resellerPermissions = Permission::whereIn('key', [
            'account_management.view',
            'account_management.create',
            'account_management.edit',
            'device_management.view',
            'device_management.create',
            'device_management.edit',
            'certificate_management.view',
        ])->get();
        $resellerRole->permissions()->syncWithoutDetaching($resellerPermissions->pluck('id'));

        // Assign basic permissions to User role
        $userPermissions = Permission::whereIn('key', [
            'device_management.view',
            'certificate_management.view',
        ])->get();
        $userRole->permissions()->syncWithoutDetaching($userPermissions->pluck('id'));
    }
}
