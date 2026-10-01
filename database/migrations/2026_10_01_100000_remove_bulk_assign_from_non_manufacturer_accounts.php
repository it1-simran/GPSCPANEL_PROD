<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Bulk Assign Devices" is a Manufacturer-only permission. Accounts created before that rule
     * (new-account defaults granted every permission) may hold it as Dealer accounts; remove it.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('user_permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')->where('key', 'device_management.bulk_assign')->value('id');
        if (!$permissionId) {
            return;
        }

        $userIds = DB::table('user_permissions')
            ->join('writers', 'writers.id', '=', 'user_permissions.user_id')
            ->where('user_permissions.permission_id', $permissionId)
            ->where('writers.user_type', '!=', 'Reseller')
            ->pluck('user_permissions.user_id');

        if ($userIds->isEmpty()) {
            return;
        }

        DB::table('user_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('user_id', $userIds)
            ->delete();

        DB::table('role_permissions')
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->where('roles.slug', 'user')
            ->where('role_permissions.permission_id', $permissionId)
            ->delete();

        Log::info('Permission Migration: removed device_management.bulk_assign from non-Manufacturer accounts', [
            'user_ids' => $userIds->values()->all(),
        ]);
    }

    /**
     * Nothing to restore: Dealer accounts must not hold this permission.
     *
     * @return void
     */
    public function down()
    {
    }
};
