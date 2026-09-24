<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->string('sku_code')->unique();
            $table->unsignedBigInteger('writer_id'); // account the SKU is defined for
            $table->unsignedBigInteger('created_by'); // who submitted it (may be Admin, on behalf of writer_id)

            $table->unsignedBigInteger('device_category_id');
            $table->string('device_category_name')->nullable();

            $table->string('esim_make');
            $table->string('esim_profile_1');
            $table->string('esim_profile_2');
            $table->string('esim_recharge_period', 20);

            $table->unsignedBigInteger('firmware_id')->nullable();
            $table->string('firmware_name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('vendor_id')->nullable();

            $table->longText('configuration')->nullable(); // frozen config snapshot (JSON)

            $table->string('status')->default('Pending'); // Pending | Approved | Rejected
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('writer_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('skus');
    }
};
