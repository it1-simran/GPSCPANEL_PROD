<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Writer;
use App\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfills role_id and parent_user_id for existing writers.
     * Does NOT touch user_permissions — permissions are managed
     * exclusively through the Admin "Manage Permissions" UI.
     *
     * @return void
     */
    public function up()
    {
        // Backfill role_id based on user_type (for users missing it)
        $roles = Role::all()->keyBy(function ($role) {
            return strtolower($role->slug);
        });

        Writer::all()->each(function ($writer) use ($roles) {
            $changed = false;

            if (!$writer->role_id) {
                $userTypeLower = strtolower($writer->user_type);
                if (isset($roles[$userTypeLower])) {
                    $writer->role_id = $roles[$userTypeLower]->id;
                    $changed = true;
                }
            }

            if (!$writer->parent_user_id && $writer->created_by) {
                $writer->parent_user_id = $writer->created_by;
                $changed = true;
            }

            if ($changed) {
                $writer->saveQuietly();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to reverse — role_id / parent_user_id columns already exist.
    }
};
