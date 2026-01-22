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
        Schema::create('firmwares', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Foreign keys
            $table->unsignedBigInteger('device_category_id')->nullable();
            $table->unsignedBigInteger('backend_id')->nullable();

            $table->json('configurations')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Optional foreign keys if you want relations enforced
            // $table->foreign('device_category_id')->references('id')->on('device_categories')->onDelete('cascade');
            // $table->foreign('backend_id')->references('id')->on('backends')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('firmwares');
    }
};
