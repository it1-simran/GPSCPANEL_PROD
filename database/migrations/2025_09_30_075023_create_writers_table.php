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
        Schema::create('writers', function (Blueprint $table) {
            $table->id();
            $table->string('device_category_id')->nullable(); // storing IDs as CSV, consider pivot table if relational
            $table->longText('configurations')->nullable();
            $table->longText('can_configurations')->nullable();

            $table->string('name');
            $table->string('mobile', 15)->unique();
            $table->string('email')->unique();
            $table->string('password');

            $table->string('LoginPassword')->nullable();
            $table->string('showLoginPassword')->nullable();

            $table->integer('today_pings')->default(0);
            $table->integer('total_pings')->default(0);

            $table->string('otp')->nullable();
            $table->boolean('twoFactorAuthentication')->default(false);
            $table->string('twoFactorAuthToken')->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();

            $table->boolean('Active_Status')->default(true);
            $table->string('user_type')->default('User');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->rememberToken();
            $table->timestamps();

            // optional: add foreign key if `created_by` refers to another table
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('writers');
    }
};
