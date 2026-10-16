<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail for account deletion (soft delete) and restore.
     *
     * One account_deactivations row per delete action. It lists every account of the
     * deactivated hierarchy (account_deactivation_accounts) and every device that was
     * moved to the deleting account's unassigned stock (account_deactivation_devices),
     * with the device's previous holder so Restore can hand it back.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('writers', function (Blueprint $table) {
            if (!Schema::hasColumn('writers', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
            if (!Schema::hasColumn('writers', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable();
            }
            if (!Schema::hasColumn('writers', 'deactivation_id')) {
                $table->unsignedBigInteger('deactivation_id')->nullable();
                $table->index(['deactivation_id']);
            }
        });

        if (!Schema::hasTable('account_deactivations')) {
            Schema::create('account_deactivations', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('root_user_id');
                $table->unsignedBigInteger('root_parent_id')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->string('deleted_by_type', 20)->nullable();
                // Account whose unassigned stock received the devices (NULL = Admin stock).
                $table->unsignedBigInteger('devices_moved_to')->nullable();
                $table->unsignedInteger('account_count')->default(0);
                $table->unsignedInteger('device_count')->default(0);
                $table->string('status', 20)->default('deactivated');
                $table->unsignedBigInteger('restored_by')->nullable();
                $table->timestamp('restored_at')->nullable();
                $table->unsignedInteger('devices_returned')->default(0);
                $table->timestamps();
                $table->index(['status']);
                $table->index(['deleted_by']);
                $table->index(['root_user_id']);
            });
        }

        if (!Schema::hasTable('account_deactivation_accounts')) {
            Schema::create('account_deactivation_accounts', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('deactivation_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedInteger('level')->default(0);
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('mobile', 15)->nullable();
                $table->string('user_type', 20)->nullable();
                $table->unsignedInteger('device_count')->default(0);
                $table->timestamps();
                $table->index(['deactivation_id']);
                $table->index(['user_id']);
            });
        }

        if (!Schema::hasTable('account_deactivation_devices')) {
            Schema::create('account_deactivation_devices', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('deactivation_id');
                $table->unsignedBigInteger('device_id');
                $table->string('imei', 20)->nullable();
                $table->unsignedBigInteger('prev_user_id')->nullable();
                $table->unsignedBigInteger('prev_master_id')->nullable();
                $table->string('prev_assign_to_ids')->nullable();
                $table->unsignedBigInteger('new_user_id')->nullable();
                $table->unsignedBigInteger('new_master_id')->nullable();
                $table->string('new_assign_to_ids')->nullable();
                $table->boolean('returned')->default(0);
                $table->timestamp('returned_at')->nullable();
                $table->timestamps();
                $table->index(['deactivation_id']);
                $table->index(['device_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_deactivation_devices');
        Schema::dropIfExists('account_deactivation_accounts');
        Schema::dropIfExists('account_deactivations');

        Schema::table('writers', function (Blueprint $table) {
            if (Schema::hasColumn('writers', 'deactivation_id')) {
                $table->dropIndex(['deactivation_id']);
                $table->dropColumn('deactivation_id');
            }
            foreach (['deleted_at', 'deleted_by'] as $column) {
                if (Schema::hasColumn('writers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
