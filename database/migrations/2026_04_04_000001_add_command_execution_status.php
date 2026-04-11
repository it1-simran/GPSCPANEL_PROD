<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add execution tracking columns for commands
     */
    public function up()
    {
        Schema::table('imei_commands', function (Blueprint $table) {
            // Add new columns for better tracking
            if (!Schema::hasColumn('imei_commands', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('sent_at')->comment('When command was executed');
            }
            if (!Schema::hasColumn('imei_commands', 'device_response')) {
                $table->text('device_response')->nullable()->after('executed_at')->comment('Response from device');
            }
            if (!Schema::hasColumn('imei_commands', 'response_time')) {
                $table->integer('response_time')->nullable()->after('device_response')->comment('Time in milliseconds');
            }
            
            // Update status comment to include new statuses
            // Status: 0=Pending, 1=Sent, 2=Executed, 3=Failed
            $table->comment('Command queue with execution tracking');
        });
    }

    public function down()
    {
        Schema::table('imei_commands', function (Blueprint $table) {
            $table->dropColumnIfExists('executed_at');
            $table->dropColumnIfExists('device_response');
            $table->dropColumnIfExists('response_time');
        });
    }
};
