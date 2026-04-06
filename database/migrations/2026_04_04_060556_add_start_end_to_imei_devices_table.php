<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('imei_devices', function (Blueprint $table) {
        if (!Schema::hasColumn('imei_devices', 'start_at')) {
            $table->timestamp('start_at')->nullable();
        }
        if (!Schema::hasColumn('imei_devices', 'end_at')) {
            $table->timestamp('end_at')->nullable();
        }
    });
}

    public function down()
    {
        Schema::table('imei_devices', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'end_at']);
        });
    }
};