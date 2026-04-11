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
        if (!Schema::hasTable('writers')) {
            Schema::create('writers', function (Blueprint $table) {
                $table->id();
                $table->string('device_category_id')->nullable();
                $table->longText('configurations')->nullable();
                $table->longText('can_configurations')->nullable();
                $table->string('name');
                $table->string('mobile', 15);
                $table->string('email');
                $table->string('password');
                $table->string('LoginPassword')->nullable();
                $table->string('showLoginPassword')->nullable();
                $table->integer('today_pings')->default(0);
                $table->integer('total_pings')->default(0);
                $table->string('otp')->nullable();
                $table->boolean('twoFactorAuthentication')->default(0);
                $table->string('twoFactorAuthToken')->nullable();
                $table->timestamp('two_factor_expires_at')->nullable();
                $table->boolean('Active_Status')->default(1);
                $table->string('user_type')->default('User');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->rememberToken();
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
        Schema::dropIfExists('writers');
    }
};
