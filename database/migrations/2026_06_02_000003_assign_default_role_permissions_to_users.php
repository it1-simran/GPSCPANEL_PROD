<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Role;
use App\Permission;

return new class extends Migration
{
    /**
     * Assign default role permissions to all existing users based on their role
     *
     * @return void
     */
    public function up()
    {
        // Get role IDs and their permissions
        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
        $resellerRoleId = DB::table('roles')->where('slug', 'reseller')->value('id');
        $userRoleId = DB::table('roles')->where('slug', 'user')->value('id');

        // Get admin role permissions
        if ($adminRoleId) {
            $adminPermIds = DB::table('role_permissions')
                ->where('role_id', $adminRoleId)
                ->pluck('permission_id')
                ->toArray();

            $adminUsers = User::where('user_type', 'Admin')->get();
            foreach ($adminUsers as $admin) {
                foreach ($adminPermIds as $permId) {
                    DB::table('user_permissions')->insertOrIgnore([
                        'user_id' => $admin->id,
                        'permission_id' => $permId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Get reseller role permissions
        if ($resellerRoleId) {
            $resellerPermIds = DB::table('role_permissions')
                ->where('role_id', $resellerRoleId)
                ->pluck('permission_id')
                ->toArray();

            $resellerUsers = User::where('user_type', 'Reseller')->get();
            foreach ($resellerUsers as $reseller) {
                foreach ($resellerPermIds as $permId) {
                    DB::table('user_permissions')->insertOrIgnore([
                        'user_id' => $reseller->id,
                        'permission_id' => $permId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Get user role permissions
        if ($userRoleId) {
            $userPermIds = DB::table('role_permissions')
                ->where('role_id', $userRoleId)
                ->pluck('permission_id')
                ->toArray();

            $userTypeAccounts = User::where('user_type', 'User')->get();
            foreach ($userTypeAccounts as $user) {
                foreach ($userPermIds as $permId) {
                    DB::table('user_permissions')->insertOrIgnore([
                        'user_id' => $user->id,
                        'permission_id' => $permId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        \Log::info('Migration: Assigned default role permissions to all existing users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a data migration - reverse would require tracking what was added
        // For safety, we just log it
        \Log::warning('Migration Reversed: Default role permissions assigned to users - data not automatically removed');
    }
};
