<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('devices')) {
            if (! $this->indexExists('devices', 'devices_name_index')) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->index('name', 'devices_name_index');
                });
            }

            if (! $this->indexExists('devices', 'devices_imei_index')) {
                // imei is TEXT in production — prefix length is required for indexing.
                DB::statement('CREATE INDEX devices_imei_index ON devices (imei(15))');
            }
        }

        if (Schema::hasTable('device_logs') && ! $this->indexExists('device_logs', 'device_logs_device_action_created_index')) {
            Schema::table('device_logs', function (Blueprint $table) {
                $table->index(['device_id', 'action', 'created_at'], 'device_logs_device_action_created_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('devices')) {
            if ($this->indexExists('devices', 'devices_name_index')) {
                Schema::table('devices', function (Blueprint $table) {
                    $table->dropIndex('devices_name_index');
                });
            }

            if ($this->indexExists('devices', 'devices_imei_index')) {
                DB::statement('DROP INDEX devices_imei_index ON devices');
            }
        }

        if (Schema::hasTable('device_logs') && $this->indexExists('device_logs', 'device_logs_device_action_created_index')) {
            Schema::table('device_logs', function (Blueprint $table) {
                $table->dropIndex('device_logs_device_action_created_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return count($rows) > 0;
    }
};
