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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');

            // foreign key reference to writers
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('device_category_id')->nullable();

            $table->longText('configurations')->nullable();
            $table->longText('can_configurations')->nullable();

            $table->tinyInteger('verify')->default(0); // 0 = pending, 1 = verified, 2 = rejected etc.
            $table->boolean('default_template')->default(false);

            $table->timestamps();

            // Optional: add foreign keys
            // $table->foreign('id_user')->references('id')->on('writers')->onDelete('cascade');
            // $table->foreign('device_category_id')->references('id')->on('device_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('templates');
    }
};
