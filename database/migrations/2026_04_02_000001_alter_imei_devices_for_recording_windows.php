<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imei_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('imei_devices', 'start_at')) {
                if (!Schema::hasColumn('imei_devices', 'start_at')) {
                $table->timestamp('start_at')->nullable()->after('status');
            }
            }
            if (!Schema::hasColumn('imei_devices', 'end_at')) {
                if (!Schema::hasColumn('imei_devices', 'end_at')) {
                $table->timestamp('end_at')->nullable()->after('start_at');
            }
            }
            if (!Schema::hasColumn('imei_devices', 'last_log_id')) {
                if (!Schema::hasColumn('imei_devices', 'last_log_id')) {
                $table->unsignedBigInteger('last_log_id')->nullable()->after('end_at');
            }
            }
        });

        DB::table('imei_devices')->whereNull('start_at')->update([
            'start_at' => DB::raw('schedule_start'),
        ]);
        DB::table('imei_devices')->whereNull('end_at')->update([
            'end_at' => DB::raw('schedule_end'),
        ]);
    }

    public function down()
    {
        Schema::table('imei_devices', function (Blueprint $table) {
            if (Schema::hasColumn('imei_devices', 'last_log_id')) {
                $table->dropColumn('last_log_id');
            }
            if (Schema::hasColumn('imei_devices', 'end_at')) {
                $table->dropColumn('end_at');
            }
            if (Schema::hasColumn('imei_devices', 'start_at')) {
                $table->dropColumn('start_at');
            }
        });
    }
};
