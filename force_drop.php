<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::statement('DROP TABLE IF EXISTS imei_logs;');
    DB::statement('DROP TABLE IF EXISTS imei_devices;');
    DB::statement('DELETE FROM migrations WHERE migration LIKE "%imei_devices%" OR migration LIKE "%imei_logs%";');
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "Dropped tables and cleared migration records.\n";

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_03_25_081745_create_imei_devices_table.php',
        '--force' => true
    ]);
    echo Artisan::output();

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_03_25_081803_create_imei_logs_table.php',
        '--force' => true
    ]);
    echo Artisan::output();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
