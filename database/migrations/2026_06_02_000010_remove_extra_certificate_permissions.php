<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove certificate_management.create, .edit, .delete permissions everywhere.
     * Only certificate_management.view should remain for that module.
     */
    public function up()
    {
        $keysToRemove = [
            'certificate_management.create',
            'certificate_management.edit',
            'certificate_management.delete',
        ];

        $ids = DB::table('permissions')->whereIn('key', $keysToRemove)->pluck('id')->toArray();

        if (!empty($ids)) {
            // Remove from user_permissions (assignments to users)
            DB::table('user_permissions')->whereIn('permission_id', $ids)->delete();

            // Remove from role_permissions (role defaults)
            DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();

            // Remove from permission_audit_logs FK cleanup (cascade may already handle)
            if (\Schema::hasTable('permission_audit_logs')) {
                DB::table('permission_audit_logs')->whereIn('permission_id', $ids)->delete();
            }

            // Finally delete the permissions themselves
            DB::table('permissions')->whereIn('id', $ids)->delete();

            \Log::info('Migration: Removed ' . count($ids) . ' extra certificate permissions and their assignments');
        }
    }

    public function down()
    {
        // Re-running PermissionSeeder will recreate them if needed; no auto-restore.
        \Log::warning('Migration reversed: extra certificate permissions not auto-restored — run PermissionSeeder to recreate');
    }
};
