<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('devices') && ! $this->indexExists('devices', 'devices_listing_index')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->index(['is_deleted', 'user_id', 'device_category_id'], 'devices_listing_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('devices') && $this->indexExists('devices', 'devices_listing_index')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->dropIndex('devices_listing_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return count($rows) > 0;
    }
};
