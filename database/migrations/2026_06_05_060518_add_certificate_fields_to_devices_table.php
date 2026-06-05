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
        Schema::table('devices', function (Blueprint $table) {
            // Certificate data - stores complete certificate information in JSON format
            $table->longText('certificate_data')->nullable()->after('configurations')->comment('Complete certificate information in JSON format');

            // Certificate generation flag - tracks if certificate has been generated
            $table->boolean('is_certificate_generated')->default(false)->after('certificate_data')->comment('Flag to track if certificate has been generated (0=No, 1=Yes)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['certificate_data', 'is_certificate_generated']);
        });
    }
};
