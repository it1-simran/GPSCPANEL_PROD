<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packet_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('packet_fields', 'is_required')) {
                $table->boolean('is_required')->default(true)->after('validation_type');
            }
        });
    }

    public function down()
    {
        Schema::table('packet_fields', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
};
