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
        Schema::create('imei_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imei_id')->constrained('imei_devices')->onDelete('cascade');
            $table->text('raw_packet');
            $table->string('source_ip')->nullable();
            $table->timestamp('logged_at')->useCurrent();
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
        Schema::dropIfExists('imei_logs');
    }
};
