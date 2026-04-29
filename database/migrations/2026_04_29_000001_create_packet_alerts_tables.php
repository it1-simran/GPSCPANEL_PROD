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
        Schema::create('packet_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packet_type_id')->constrained('packet_types')->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('packet_alert_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packet_alert_id')->constrained('packet_alerts')->onDelete('cascade');
            $table->foreignId('packet_field_id')->constrained('packet_fields')->onDelete('cascade');
            $table->string('operator'); // ==, !=, <=, >=
            $table->string('value');
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
        Schema::dropIfExists('packet_alert_conditions');
        Schema::dropIfExists('packet_alerts');
    }
};
