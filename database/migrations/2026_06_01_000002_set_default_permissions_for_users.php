<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Permission;
use App\Writer;

return new class extends Migration
{
    /**
     * Set default permissions for all existing users (Resellers and Users).
     * All permissions are enabled by default except certificate.view.
     *
     * @return void
     */
    public function up()
    {
        // Get all active permissions except certificate_management.view
        $permissions = Permission::where('is_active', 1)
            ->where('key', '!=', 'certificate_management.view')
            ->pluck('id')
            ->toArray();

        // Get certificate.view permission ID (to explicitly exclude it)
        $certViewPermission = Permission::where('key', 'certificate_management.view')
            ->first();

        // Iterate through all non-admin users and assign default permissions
        Writer::where('user_type', '!=', 'Admin')
            ->where('is_deleted', 0)
            ->get()
            ->each(function ($writer) use ($permissions) {
                // Get existing permissions for this user
                $existingPermIds = DB::table('user_permissions')
                    ->where('user_id', $writer->id)
                    ->pluck('permission_id')
                    ->toArray();

                // Merge with default permissions (don't remove manually configured ones)
                $allPermIds = array_unique(array_merge($permissions, $existingPermIds));

                // Sync the permissions (keeping existing + adding defaults)
                $writer->permissions()->sync($allPermIds);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a data migration. To reverse, you would need to know
        // which permissions were manually set vs. default. We don't remove
        // permissions on rollback to prevent accidental data loss.
    }
};
