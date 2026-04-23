<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add delimiter to packet_types
        Schema::table('packet_types', function (Blueprint $table) {
            if (!Schema::hasColumn('packet_types', 'delimiter')) {
                $table->string('delimiter')->nullable()->after('header_identifier');
            }
        });

        // Add validation fields to packet_fields
        Schema::table('packet_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('packet_fields', 'min_value')) {
                $table->string('min_value')->nullable()->after('fixed_value');
                $table->string('max_value')->nullable()->after('min_value');
                $table->text('regex_pattern')->nullable()->after('max_value');
            }
        });

        // Add protocol_id to imei_devices to link them
        Schema::table('imei_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('imei_devices', 'protocol_id')) {
                $table->unsignedBigInteger('protocol_id')->nullable()->after('imei');
            }
        });
    }

    public function down()
    {
        Schema::table('packet_types', function (Blueprint $table) {
            $table->dropColumn('delimiter');
        });
        Schema::table('packet_fields', function (Blueprint $table) {
            $table->dropColumn(['min_value', 'max_value', 'regex_pattern']);
        });
        Schema::table('imei_devices', function (Blueprint $table) {
            $table->dropColumn('protocol_id');
        });
    }
};
