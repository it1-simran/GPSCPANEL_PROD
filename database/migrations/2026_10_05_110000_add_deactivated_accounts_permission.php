<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add account_management.deactivated_accounts ("Deactivated Accounts") under Account Management.
     *
     * Manufacturer-only (PermissionAssignmentService::MANUFACTURER_ONLY_KEYS). It depends on
     * account_management.delete, so granting it adds Delete/View and revoking Delete removes it.
     *
     * Manufacturer accounts that can already delete accounts get it, so the Deactivated Accounts
     * page stays available on deploy; Admin can switch it off per account.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $deleteId = DB::table('permissions')->where('key', 'account_management.delete')->value('id');

        $attributes = [
            'module' => 'account_management',
            'action' => 'deactivated_accounts',
            'label' => 'Deactivated Accounts',
            'description' => 'Allow viewing deactivated accounts and restoring them with their devices',
            'order' => 5,
            'is_active' => 1,
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('permissions', 'parent_permission_id')) {
            $attributes['parent_permission_id'] = $deleteId;
        }

        $permissionId = DB::table('permissions')->where('key', 'account_management.deactivated_accounts')->value('id');
        if ($permissionId) {
            DB::table('permissions')->where('id', $permissionId)->update($attributes);
        } else {
            $permissionId = DB::table('permissions')->insertGetId(array_merge($attributes, [
                'key' => 'account_management.deactivated_accounts',
                'created_at' => now(),
            ]));
        }

        if (!$deleteId) {
            return;
        }

        // Keep current access: Manufacturer accounts that already hold Delete Account.
        $resellerIds = DB::table('user_permissions')
            ->join('writers', 'writers.id', '=', 'user_permissions.user_id')
            ->where('user_permissions.permission_id', $deleteId)
            ->where('writers.user_type', 'Reseller')
            ->distinct()
            ->pluck('user_permissions.user_id');

        foreach ($resellerIds as $userId) {
            DB::table('user_permissions')->insertOrIgnore([
                'user_id' => $userId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Role templates (defaults for new accounts). Dealer ("user") role is left out:
        // the permission is for Manufacturer accounts only.
        $roleIds = DB::table('roles')->whereIn('slug', ['admin', 'reseller'])->pluck('id');
        foreach ($roleIds as $roleId) {
            if (!DB::table('role_permissions')->where('role_id', $roleId)->where('permission_id', $deleteId)->exists()) {
                continue;
            }
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info('Permission Migration: Added account_management.deactivated_accounts permission', [
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
        $id = DB::table('permissions')->where('key', 'account_management.deactivated_accounts')->value('id');
        if (!$id) {
            return;
        }

        DB::table('user_permissions')->where('permission_id', $id)->delete();
        DB::table('role_permissions')->where('permission_id', $id)->delete();
        DB::table('permissions')->where('id', $id)->delete();

        Log::info('Permission Migration Reversed: Removed account_management.deactivated_accounts permission');
    }
};
