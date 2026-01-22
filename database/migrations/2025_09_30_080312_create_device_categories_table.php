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
         Schema::create('device_categories', function (Blueprint $table) {
            $table->id();
            $table->string('device_category_name');
            $table->text('inputs')->nullable();
            $table->text('parameters')->nullable();
            $table->boolean('is_esim')->default(0);
            $table->boolean('is_can_protocol')->default(0);
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
        Schema::dropIfExists('device_categories');
    }
};
