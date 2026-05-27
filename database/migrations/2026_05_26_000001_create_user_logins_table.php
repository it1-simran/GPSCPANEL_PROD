<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('user_logins')) {
            Schema::create('user_logins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('ip_address', 45);
                $table->text('user_agent');
                $table->timestamp('logged_at')->useCurrent();
                $table->foreign('user_id')->references('id')->on('writers')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_logins');
    }
};
