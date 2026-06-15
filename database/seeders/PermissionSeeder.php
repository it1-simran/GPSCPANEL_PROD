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
        // Master permission list
        // ---------------------------------------------------------------
        $permissions = \Database\Seeders\Support\DefaultPermissions::definitions();

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
        // Reseller role — Account + Device + Settings (certificate view off by default)
        // ---------------------------------------------------------------
        $resellerKeys = [
            'account_management.view', 'account_management.create',
            'account_management.edit', 'account_management.delete',
            'device_management.view', 'device_management.edit',
            'settings_management.view', 'settings_management.create',
            'settings_management.edit', 'settings_management.delete',
            'settings_management.assign_bulk',
        ];
        $resellerRole->permissions()->sync(
            $allPerms->only($resellerKeys)->values()
        );

        // ---------------------------------------------------------------
        // User role — Device + Settings (no account_management, certificate view off by default)
        // ---------------------------------------------------------------
        $userKeys = [
            'device_management.view', 'device_management.edit',
            'settings_management.view', 'settings_management.create',
            'settings_management.edit', 'settings_management.delete',
            'settings_management.assign_bulk',
        ];
        $userRole->permissions()->sync(
            $allPerms->only($userKeys)->values()
        );
    }
}
