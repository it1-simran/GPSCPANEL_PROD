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
        Schema::create('modals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vendorId')->nullable();

            // Foreign keys
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('firmware_id')->nullable();

            $table->timestamps();

            // Optional: add relations
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('firmware_id')->references('id')->on('firmwares')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modals');
    }
};
