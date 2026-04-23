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
        Schema::create('sms_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('imei')->unique()->nullable();
            $table->string('phone_number')->unique();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sms_command_templates', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('payload');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('sms_devices')->onDelete('cascade');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('content');
            $table->string('status')->default('sent');
            $table->string('provider_ref')->nullable();
            $table->foreignId('replied_to_id')->nullable()->constrained('sms_logs')->nullOnDelete();
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
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('sms_command_templates');
        Schema::dropIfExists('sms_devices');
    }
};
