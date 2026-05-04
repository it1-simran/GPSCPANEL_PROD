<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('test_plan_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_plan_id')->constrained()->onDelete('cascade');
            $table->integer('sequence');
            $table->string('step_type'); // send_command, wait_for_response, etc.
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('test_plan_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('imei_device_id')->constrained('imei_devices')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, running, passed, failed, stopped
            $table->foreignId('current_step_id')->nullable()->constrained('test_plan_steps')->onDelete('set null');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('test_plan_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained('test_plan_executions')->onDelete('cascade');
            $table->foreignId('step_id')->constrained('test_plan_steps')->onDelete('cascade');
            $table->string('status'); // pass, fail, skipped
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_plan_execution_logs');
        Schema::dropIfExists('test_plan_executions');
        Schema::dropIfExists('test_plan_steps');
        Schema::dropIfExists('test_plans');
    }
};
