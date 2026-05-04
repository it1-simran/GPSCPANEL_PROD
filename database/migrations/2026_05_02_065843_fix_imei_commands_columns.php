<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure all required columns for command tracking exist
     */
    public function up()
    {
        Schema::table('imei_commands', function (Blueprint $table) {
            if (!Schema::hasColumn('imei_commands', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('imei_commands', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('imei_commands', 'device_response')) {
                $table->text('device_response')->nullable()->after('executed_at');
            }
            if (!Schema::hasColumn('imei_commands', 'response_time')) {
                $table->integer('response_time')->nullable()->after('device_response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('imei_commands', function (Blueprint $table) {
            $table->dropColumn(['sent_at', 'executed_at', 'device_response', 'response_time']);
        });
    }
};
