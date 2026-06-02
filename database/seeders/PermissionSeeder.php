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

        // ---------------------------------------------------------------
        // Master permission list — 12 permissions across 4 modules
        // ---------------------------------------------------------------
        $permissions = [
            // Account Management (4)
            ['key' => 'account_management.view',   'module' => 'account_management',   'action' => 'view',        'label' => 'View Account Management', 'order' => 1],
            ['key' => 'account_management.create', 'module' => 'account_management',   'action' => 'create',      'label' => 'Create Account',          'order' => 2],
            ['key' => 'account_management.edit',   'module' => 'account_management',   'action' => 'edit',        'label' => 'Edit Account',            'order' => 3],
            ['key' => 'account_management.delete', 'module' => 'account_management',   'action' => 'delete',      'label' => 'Delete Account',          'order' => 4],

            // Device Management (2)
            ['key' => 'device_management.view',    'module' => 'device_management',    'action' => 'view',        'label' => 'View Device Management',  'order' => 1],
            ['key' => 'device_management.edit',    'module' => 'device_management',    'action' => 'edit',        'label' => 'Edit Device',             'order' => 2],

            // Certificate Management (1 — view only)
            ['key' => 'certificate_management.view',   'module' => 'certificate_management', 'action' => 'view',   'label' => 'View Certificate',   'order' => 1],

            // Settings Management (5)
            ['key' => 'settings_management.view',        'module' => 'settings_management', 'action' => 'view',        'label' => 'View Settings',          'order' => 1],
            ['key' => 'settings_management.create',      'module' => 'settings_management', 'action' => 'create',      'label' => 'Create Settings',        'order' => 2],
            ['key' => 'settings_management.edit',        'module' => 'settings_management', 'action' => 'edit',        'label' => 'Edit Settings',          'order' => 3],
            ['key' => 'settings_management.delete',      'module' => 'settings_management', 'action' => 'delete',      'label' => 'Delete Settings',        'order' => 4],
            ['key' => 'settings_management.assign_bulk', 'module' => 'settings_management', 'action' => 'assign_bulk', 'label' => 'Assign Settings Bulk',   'order' => 5],
        ];

        // Upsert permissions — update label/order if key already exists
        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['key' => $perm['key']],
                array_merge($perm, ['is_active' => 1])
            );
        }

        // Remove any permissions NOT in the master list (keeps DB clean)
        $validKeys = array_column($permissions, 'key');
        Permission::whereNotIn('key', $validKeys)->delete();

        // Reload fresh IDs after upsert
        $allPerms = Permission::where('is_active', 1)->pluck('id', 'key');

        // ---------------------------------------------------------------
        // Admin role — all 12 permissions
        // ---------------------------------------------------------------
        $adminRole->permissions()->sync($allPerms->values());

        // ---------------------------------------------------------------
        // Reseller role — Account + Device + Certificate (view) + Settings
        // ---------------------------------------------------------------
        $resellerKeys = [
            'account_management.view', 'account_management.create',
            'account_management.edit', 'account_management.delete',
            'device_management.view', 'device_management.edit',
            'certificate_management.view',
            'settings_management.view', 'settings_management.create',
            'settings_management.edit', 'settings_management.delete',
            'settings_management.assign_bulk',
        ];
        $resellerRole->permissions()->sync(
            $allPerms->only($resellerKeys)->values()
        );

        // ---------------------------------------------------------------
        // User role — Device + Certificate (view) + Settings (no account_management)
        // ---------------------------------------------------------------
        $userKeys = [
            'device_management.view', 'device_management.edit',
            'certificate_management.view',
            'settings_management.view', 'settings_management.create',
            'settings_management.edit', 'settings_management.delete',
            'settings_management.assign_bulk',
        ];
        $userRole->permissions()->sync(
            $allPerms->only($userKeys)->values()
        );
    }
}
