<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DefaultPermissions
{
    public static function definitions(): array
    {
        return [
            ['key' => 'account_management.view', 'module' => 'account_management', 'action' => 'view', 'label' => 'View Account Management', 'order' => 1],
            ['key' => 'account_management.create', 'module' => 'account_management', 'action' => 'create', 'label' => 'Create Account', 'order' => 2],
            ['key' => 'account_management.edit', 'module' => 'account_management', 'action' => 'edit', 'label' => 'Edit Account', 'order' => 3],
            ['key' => 'account_management.delete', 'module' => 'account_management', 'action' => 'delete', 'label' => 'Delete Account', 'order' => 4],

            ['key' => 'device_management.view', 'module' => 'device_management', 'action' => 'view', 'label' => 'View Device Management', 'order' => 1],
            ['key' => 'device_management.edit', 'module' => 'device_management', 'action' => 'edit', 'label' => 'Edit Device', 'order' => 2],

            ['key' => 'certificate_management.view', 'module' => 'certificate_management', 'action' => 'view', 'label' => 'View Certificate', 'order' => 1],

            ['key' => 'settings_management.view', 'module' => 'settings_management', 'action' => 'view', 'label' => 'View Settings', 'order' => 1],
            ['key' => 'settings_management.create', 'module' => 'settings_management', 'action' => 'create', 'label' => 'Create Settings', 'order' => 2],
            ['key' => 'settings_management.edit', 'module' => 'settings_management', 'action' => 'edit', 'label' => 'Edit Settings', 'order' => 3],
            ['key' => 'settings_management.delete', 'module' => 'settings_management', 'action' => 'delete', 'label' => 'Delete Settings', 'order' => 4],
            ['key' => 'settings_management.assign_bulk', 'module' => 'settings_management', 'action' => 'assign_bulk', 'label' => 'Assign Settings Bulk', 'order' => 5],
        ];
    }

    public static function seed(): void
    {
        $now = now();

        foreach (self::definitions() as $permission) {
            $existing = DB::table('permissions')->where('key', $permission['key'])->first();

            if ($existing) {
                DB::table('permissions')->where('key', $permission['key'])->update([
                    'module' => $permission['module'],
                    'action' => $permission['action'],
                    'label' => $permission['label'],
                    'order' => $permission['order'],
                    'is_active' => 1,
                    'updated_at' => $now,
                ]);
                continue;
            }

            DB::table('permissions')->insert([
                'key' => $permission['key'],
                'module' => $permission['module'],
                'action' => $permission['action'],
                'label' => $permission['label'],
                'description' => null,
                'order' => $permission['order'],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public static function seedDependencies(): void
    {
        if (!Schema::hasColumn('permissions', 'parent_permission_id')) {
            return;
        }

        $dependencies = [
            'account_management.create' => 'account_management.view',
            'account_management.edit' => 'account_management.view',
            'account_management.delete' => 'account_management.view',
            'device_management.edit' => 'device_management.view',
            'settings_management.create' => 'settings_management.view',
            'settings_management.edit' => 'settings_management.view',
            'settings_management.delete' => 'settings_management.view',
            'settings_management.assign_bulk' => 'settings_management.view',
        ];

        foreach ($dependencies as $childKey => $parentKey) {
            $parentId = DB::table('permissions')->where('key', $parentKey)->value('id');
            if (!$parentId) {
                continue;
            }

            DB::table('permissions')
                ->where('key', $childKey)
                ->update(['parent_permission_id' => $parentId]);
        }
    }
}
