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
        if (!Schema::hasTable('permission_changes')) {
            Schema::create('permission_changes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index()->comment('User whose permission was changed');
                $table->unsignedBigInteger('permission_id')->nullable()->comment('Permission that was changed');
                $table->unsignedBigInteger('changed_by')->comment('Admin who made the change');
                $table->timestamp('changed_at')->useCurrent()->index()->comment('When the change was made');
                $table->string('change_type', 20)->comment('Type of change: granted, revoked, updated');
                $table->string('permission_key', 100)->nullable()->comment('Permission key (e.g., certificate_management.view)');

                // Composite index for efficient queries
                $table->index(['user_id', 'changed_at']);

                // Foreign key to writers table (users)
                $table->foreign('user_id')->references('id')->on('writers')->onDelete('cascade');
                $table->foreign('changed_by')->references('id')->on('writers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_changes');
    }
};
