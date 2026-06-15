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
        Schema::create('permission_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User being assigned/revoked permission');
            $table->unsignedBigInteger('permission_id')->comment('Permission being assigned/revoked');
            $table->unsignedBigInteger('assigned_by')->comment('Admin/Reseller who made the change');
            $table->enum('action', ['assigned', 'revoked'])->comment('Action performed');
            $table->text('reason')->nullable()->comment('Reason for the change');
            $table->json('metadata')->nullable()->comment('Additional context (IP, user agent, etc)');
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('writers')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('writers')->onDelete('cascade');

            // Indexes for quick lookups
            $table->index(['user_id', 'created_at']);
            $table->index(['assigned_by', 'created_at']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_audit_logs');
    }
};
