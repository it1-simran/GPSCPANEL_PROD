<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Permission;

class SetupPermissionDependencies extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Sets up permission hierarchy where disabling a parent permission
     * automatically disables all child permissions
     */
    public function run(): void
    {
        // Define permission dependencies
        // Format: 'child_key' => 'parent_key'
        $dependencies = [
            // Account Management - View Account is the parent
            'account_management.create' => 'account_management.view',
            'account_management.edit' => 'account_management.view',
            'account_management.delete' => 'account_management.view',

            // Device Management - View Device is the parent
            'device_management.edit' => 'device_management.view',

            // Settings Management - View Settings is the parent
            'settings_management.create' => 'settings_management.view',
            'settings_management.edit' => 'settings_management.view',
            'settings_management.delete' => 'settings_management.view',
            'settings_management.assign_bulk' => 'settings_management.view',
        ];

        foreach ($dependencies as $childKey => $parentKey) {
            $childPermission = Permission::where('key', $childKey)->first();
            $parentPermission = Permission::where('key', $parentKey)->first();

            if ($childPermission && $parentPermission) {
                $childPermission->update([
                    'parent_permission_id' => $parentPermission->id
                ]);
                $this->command->info("Set parent: {$parentKey} -> {$childKey}");
            } else {
                if (!$childPermission) {
                    $this->command->warn("Child permission not found: {$childKey}");
                }
                if (!$parentPermission) {
                    $this->command->warn("Parent permission not found: {$parentKey}");
                }
            }
        }
    }
}
