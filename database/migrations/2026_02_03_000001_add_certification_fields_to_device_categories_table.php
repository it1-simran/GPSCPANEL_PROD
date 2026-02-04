<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('device_categories', function (Blueprint $table) {
            $table->string('arai_tac_no')->nullable();
            $table->date('arai_date')->nullable();
            $table->string('certification_model_name')->nullable();
        });
    }

    public function down()
    {
        Schema::table('device_categories', function (Blueprint $table) {
            $table->dropColumn(['arai_tac_no', 'arai_date', 'certification_model_name']);
        });
    }
};

