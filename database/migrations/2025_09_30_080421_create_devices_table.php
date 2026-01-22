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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('master_id')->nullable();
            $table->string('assign_to_ids')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('device_category_id');
            $table->json('configurations')->nullable();
            $table->json('can_configurations')->nullable();
            $table->json('errors')->nullable();
            $table->string('imei', 15)->unique();
            $table->string('ip')->nullable();
            $table->string('port')->nullable();
            $table->integer('logs_interval')->nullable();
            $table->string('password')->nullable();
            $table->integer('sleep_interval')->nullable();
            $table->integer('trans_interval')->nullable();
            $table->boolean('fota')->default(0);
            $table->boolean('is_editable')->default(0);
            $table->integer('ping_interval')->nullable();
            $table->boolean('active_status')->default(1);
            $table->string('deviceStatus')->nullable();
            $table->string('firmware_version')->nullable();
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
        Schema::dropIfExists('devices');
    }
};
