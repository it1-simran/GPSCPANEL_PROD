<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Set up permission dependencies:
        // View permissions are parents of Create/Edit/Delete permissions

        // Account Management dependencies
        $viewAccountId = DB::table('permissions')
            ->where('key', 'account_management.view')
            ->value('id');

        if ($viewAccountId) {
            DB::table('permissions')
                ->where('module', 'account_management')
                ->where('action', '!=', 'view')
                ->update(['parent_permission_id' => $viewAccountId]);
        }

        // Device Management dependencies
        $viewDeviceId = DB::table('permissions')
            ->where('key', 'device_management.view')
            ->value('id');

        if ($viewDeviceId) {
            DB::table('permissions')
                ->where('module', 'device_management')
                ->where('action', '!=', 'view')
                ->update(['parent_permission_id' => $viewDeviceId]);
        }

        // Certificate Management dependencies
        $viewCertificateId = DB::table('permissions')
            ->where('key', 'certificate_management.view')
            ->value('id');

        if ($viewCertificateId) {
            DB::table('permissions')
                ->where('module', 'certificate_management')
                ->where('action', '!=', 'view')
                ->update(['parent_permission_id' => $viewCertificateId]);
        }

        // Settings Management dependencies
        $viewSettingsId = DB::table('permissions')
            ->where('key', 'settings_management.view')
            ->value('id');

        if ($viewSettingsId) {
            DB::table('permissions')
                ->where('module', 'settings_management')
                ->where('action', '!=', 'view')
                ->update(['parent_permission_id' => $viewSettingsId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset all parent_permission_id to NULL
        DB::table('permissions')->update(['parent_permission_id' => null]);
    }
};
