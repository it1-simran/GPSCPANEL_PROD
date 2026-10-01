<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add device_management.bulk_assign ("Bulk Assign Devices") under Device Management.
     *
     * It depends on device_management.edit, so the existing dependency logic adds Edit/View
     * when it is granted and removes it whenever Edit is revoked. Revoking it from a parent
     * cascades to all descendants through PermissionAssignmentService::syncPermissions().
     *
     * Manufacturer (Reseller) accounts that can already edit devices keep today's access to
     * the Bulk Assign page, so nothing disappears on deploy; Admin can switch it off per account.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $editId = DB::table('permissions')->where('key', 'device_management.edit')->value('id');

        $attributes = [
            'module' => 'device_management',
            'action' => 'bulk_assign',
            'label' => 'Bulk Assign Devices',
            'description' => 'Allow assigning, moving and taking back devices in bulk across own stock and child accounts',
            'order' => 3,
            'is_active' => 1,
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('permissions', 'parent_permission_id')) {
            $attributes['parent_permission_id'] = $editId;
        }

        $bulkAssignId = DB::table('permissions')->where('key', 'device_management.bulk_assign')->value('id');
        if ($bulkAssignId) {
            DB::table('permissions')->where('id', $bulkAssignId)->update($attributes);
        } else {
            $bulkAssignId = DB::table('permissions')->insertGetId(array_merge($attributes, [
                'key' => 'device_management.bulk_assign',
                'created_at' => now(),
            ]));
        }

        if (!$editId) {
            return;
        }

        // Keep current access: Manufacturer accounts that already hold Edit Device.
        $resellerIds = DB::table('user_permissions')
            ->join('writers', 'writers.id', '=', 'user_permissions.user_id')
            ->where('user_permissions.permission_id', $editId)
            ->where('writers.user_type', 'Reseller')
            ->distinct()
            ->pluck('user_permissions.user_id');

        foreach ($resellerIds as $userId) {
            DB::table('user_permissions')->insertOrIgnore([
                'user_id' => $userId,
                'permission_id' => $bulkAssignId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Role templates (defaults for new accounts). Dealer ("user") role is left out:
        // the Bulk Assign page exists only for Manufacturer accounts.
        $roleIds = DB::table('roles')->whereIn('slug', ['admin', 'reseller'])->pluck('id');
        foreach ($roleIds as $roleId) {
            if (!DB::table('role_permissions')->where('role_id', $roleId)->where('permission_id', $editId)->exists()) {
                continue;
            }
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $bulkAssignId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info('Permission Migration: Added device_management.bulk_assign permission', [
            'granted_to_resellers' => $resellerIds->count(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $id = DB::table('permissions')->where('key', 'device_management.bulk_assign')->value('id');
        if (!$id) {
            return;
        }

        DB::table('user_permissions')->where('permission_id', $id)->delete();
        DB::table('role_permissions')->where('permission_id', $id)->delete();
        DB::table('permissions')->where('id', $id)->delete();

        Log::info('Permission Migration Reversed: Removed device_management.bulk_assign permission');
    }
};
