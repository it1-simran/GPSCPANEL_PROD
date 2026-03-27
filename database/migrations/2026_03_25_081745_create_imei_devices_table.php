<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imei_devices', function (Blueprint $table) {
            $table->id();
            $table->string('imei')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('schedule_start')->nullable();
            $table->timestamp('schedule_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imei_devices');
    }
};
