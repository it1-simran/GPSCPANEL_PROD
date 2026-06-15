<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove create_device and delete_device permissions.
     *
     * @return void
     */
    public function up()
    {
        // Get permission IDs for create and delete device permissions
        $createDeviceId = DB::table('permissions')
            ->where('key', 'device_management.create')
            ->value('id');

        $deleteDeviceId = DB::table('permissions')
            ->where('key', 'device_management.delete')
            ->value('id');

        // Remove from user_permissions table
        if ($createDeviceId) {
            DB::table('user_permissions')
                ->where('permission_id', $createDeviceId)
                ->delete();
        }

        if ($deleteDeviceId) {
            DB::table('user_permissions')
                ->where('permission_id', $deleteDeviceId)
                ->delete();
        }

        // Remove from role_permissions table
        if ($createDeviceId) {
            DB::table('role_permissions')
                ->where('permission_id', $createDeviceId)
                ->delete();
        }

        if ($deleteDeviceId) {
            DB::table('role_permissions')
                ->where('permission_id', $deleteDeviceId)
                ->delete();
        }

        // Delete the permissions themselves
        DB::table('permissions')
            ->whereIn('key', ['device_management.create', 'device_management.delete'])
            ->delete();

        \Log::info('Migration: Removed device_management.create and device_management.delete permissions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-add the permissions
        DB::table('permissions')->insert([
            [
                'key' => 'device_management.create',
                'module' => 'device_management',
                'action' => 'create',
                'label' => 'Create Device',
                'description' => null,
                'order' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'device_management.delete',
                'module' => 'device_management',
                'action' => 'delete',
                'label' => 'Delete Device',
                'description' => null,
                'order' => 4,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        \Log::info('Migration Reversed: Re-added device_management.create and device_management.delete permissions');
    }
};
