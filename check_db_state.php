<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'users',
    'password_resets',
    'failed_jobs',
    'personal_access_tokens',
    'writers',
    'templates',
    'notifications',
    'modals',
    'firmwares',
    'esims',
    'device_logs',
    'device_categories',
    'devices',
    'data_fields',
    'ccids',
    'backends',
    'imei_devices',
    'imei_logs',
    'jobs'
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "Table '$table' exists.\n";
    } else {
        echo "Table '$table' does NOT exist.\n";
    }
}

if (Schema::hasTable('migrations')) {
    echo "\nMigrations recorded:\n";
    $migrations = DB::table('migrations')->pluck('migration');
    foreach ($migrations as $m) {
        echo "- $m\n";
    }
} else {
    echo "\nMigrations table does NOT exist.\n";
}
