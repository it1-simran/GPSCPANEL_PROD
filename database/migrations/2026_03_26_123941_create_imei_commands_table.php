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
        Schema::create('imei_commands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imei_id');
            $table->string('command');
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Sent');
            $table->timestamps();

            $table->foreign('imei_id')->references('id')->on('imei_devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imei_commands');
    }
};
