<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('writers')) {
            return;
        }

        Schema::table('writers', function (Blueprint $table) {
            if (!Schema::hasColumn('writers', 'parent_user_id')) {
                $table->unsignedBigInteger('parent_user_id')->nullable()->comment('Parent user for hierarchy');
                $table->index(['parent_user_id']);
            }
            if (!Schema::hasColumn('writers', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->comment('User role');
                $table->index(['role_id']);
            }
        });
    }

    public function down()
    {
        if (Schema::hasTable('writers')) {
            Schema::table('writers', function (Blueprint $table) {
                if (Schema::hasColumn('writers', 'parent_user_id')) {
                    $table->dropIndex(['parent_user_id']);
                    $table->dropColumn('parent_user_id');
                }
                if (Schema::hasColumn('writers', 'role_id')) {
                    $table->dropIndex(['role_id']);
                    $table->dropColumn('role_id');
                }
            });
        }
    }
};
