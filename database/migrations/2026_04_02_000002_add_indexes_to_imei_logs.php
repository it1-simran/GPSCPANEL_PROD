<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('imei_logs', function (Blueprint $table) {
            $table->index(['imei_id', 'id']);
            $table->index('logged_at');
        });
    }

    public function down()
    {
        Schema::table('imei_logs', function (Blueprint $table) {
            $table->dropIndex(['imei_id', 'id']);
            $table->dropIndex(['logged_at']);
        });
    }
};
