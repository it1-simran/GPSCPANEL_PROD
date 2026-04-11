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
    if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');

            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('device_category_id')->nullable();

            $table->longText('configurations')->nullable();
            $table->longText('can_configurations')->nullable();

            $table->tinyInteger('verify')->default(0);
            $table->boolean('default_template')->default(false);

            $table->timestamps();
        });
        }
    }
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
