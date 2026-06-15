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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Permission key like account_management.view');
            $table->string('module', 50)->comment('Module name: account_management, device_management, etc');
            $table->string('action', 50)->comment('Action: view, create, edit, delete, download, print');
            $table->string('label', 100)->comment('Display label');
            $table->text('description')->nullable()->comment('Permission description');
            $table->integer('order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->index(['module']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
};
