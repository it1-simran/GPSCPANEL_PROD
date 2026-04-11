<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL requires modifying ENUM by re-declaring all values
        DB::statement("ALTER TABLE `imei_devices` MODIFY COLUMN `status` ENUM('active', 'inactive', 'close') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original two-value ENUM (converts 'close' rows to 'active' first)
        DB::statement("UPDATE `imei_devices` SET `status` = 'active' WHERE `status` = 'close'");
        DB::statement("ALTER TABLE `imei_devices` MODIFY COLUMN `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active'");
    }
};
