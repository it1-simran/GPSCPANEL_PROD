<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Permission;

return new class extends Migration
{
    /**
     * Remove device_management.create and device_management.delete permissions.
     * Add settings_management.create and settings_management.delete permissions.
     *
     * @return void
     */
    public function up()
    {
        // Get permission IDs to remove
        $createDeviceId = DB::table('permissions')
            ->where('key', 'device_management.create')
            ->value('id');

        $deleteDeviceId = DB::table('permissions')
            ->where('key', 'device_management.delete')
            ->value('id');

        // Remove these permissions from all users
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

        // Remove from all roles
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

        // Delete the old permissions
        DB::table('permissions')
            ->whereIn('key', ['device_management.create', 'device_management.delete'])
            ->delete();

        // Add new settings permissions
        DB::table('permissions')->insert([
            [
                'key' => 'settings_management.create',
                'module' => 'settings_management',
                'action' => 'create',
                'label' => 'Create Settings',
                'description' => 'Allow creating new settings templates',
                'order' => 14,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'settings_management.delete',
                'module' => 'settings_management',
                'action' => 'delete',
                'label' => 'Delete Settings',
                'description' => 'Allow deleting settings templates',
                'order' => 15,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Log the migration
        \Log::info('Permission Migration: Removed device create/delete, added settings create/delete');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Delete new settings permissions
        DB::table('permissions')
            ->whereIn('key', ['settings_management.create', 'settings_management.delete'])
            ->delete();

        // Re-add device permissions
        DB::table('permissions')->insert([
            [
                'key' => 'device_management.create',
                'module' => 'device_management',
                'action' => 'create',
                'label' => 'Create Device',
                'description' => 'Allow creating new devices',
                'order' => 13,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'device_management.delete',
                'module' => 'device_management',
                'action' => 'delete',
                'label' => 'Delete Device',
                'description' => 'Allow deleting devices',
                'order' => 14,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        \Log::info('Permission Migration Reversed: Re-added device create/delete, removed settings create/delete');
    }
};
