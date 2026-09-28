<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separate "Enable eSIM in SKU" flag, independent of is_esim (which also
     * drives the CCID input, firmware and device API eSIM handling). When on,
     * the SKU create/edit wizard shows and requires the eSIM fields for this
     * Device Category. Backfilled from is_esim so existing categories keep
     * today's SKU behaviour until an admin changes it.
     */
    public function up()
    {
        if (!Schema::hasColumn('device_categories', 'is_sku_esim')) {
            Schema::table('device_categories', function (Blueprint $table) {
                $table->boolean('is_sku_esim')->default(0)->after('is_esim');
            });
            DB::table('device_categories')->update(['is_sku_esim' => DB::raw('is_esim')]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('device_categories', 'is_sku_esim')) {
            Schema::table('device_categories', function (Blueprint $table) {
                $table->dropColumn('is_sku_esim');
            });
        }
    }
};
