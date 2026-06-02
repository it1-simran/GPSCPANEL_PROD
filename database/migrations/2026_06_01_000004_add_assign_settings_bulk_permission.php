<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add assign_settings_bulk permission to settings_management module.
     *
     * @return void
     */
    public function up()
    {
        // Add new settings permission for assign bulk
        DB::table('permissions')->insert([
            [
                'key' => 'settings_management.assign_bulk',
                'module' => 'settings_management',
                'action' => 'assign_bulk',
                'label' => 'Assign Settings Bulk',
                'description' => 'Allow assigning settings in bulk to devices',
                'order' => 16,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Grant this permission to all users who have settings_management.edit
        $editSettingsPermissionId = DB::table('permissions')
            ->where('key', 'settings_management.edit')
            ->value('id');

        $assignBulkPermissionId = DB::table('permissions')
            ->where('key', 'settings_management.assign_bulk')
            ->value('id');

        if ($editSettingsPermissionId && $assignBulkPermissionId) {
            // Get all users who have settings_management.edit
            $userIds = DB::table('user_permissions')
                ->where('permission_id', $editSettingsPermissionId)
                ->distinct()
                ->pluck('user_id')
                ->toArray();

            // Add assign_bulk permission to them
            foreach ($userIds as $userId) {
                DB::table('user_permissions')->insertOrIgnore([
                    'user_id' => $userId,
                    'permission_id' => $assignBulkPermissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Do the same for role_permissions
            $roleIds = DB::table('role_permissions')
                ->where('permission_id', $editSettingsPermissionId)
                ->distinct()
                ->pluck('role_id')
                ->toArray();

            foreach ($roleIds as $roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $assignBulkPermissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        \Log::info('Permission Migration: Added settings_management.assign_bulk permission');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Delete the assign_bulk permission
        DB::table('permissions')
            ->where('key', 'settings_management.assign_bulk')
            ->delete();

        \Log::info('Permission Migration Reversed: Removed settings_management.assign_bulk permission');
    }
};
