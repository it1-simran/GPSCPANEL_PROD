<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

try {
    DB::statement('ALTER TABLE migrations ADD PRIMARY KEY (id);');
} catch (\Exception $e) {}

try {
    DB::statement('ALTER TABLE migrations MODIFY id INT UNSIGNED AUTO_INCREMENT;');
    echo "Fixed migrations table auto-increment.\n";
} catch (\Exception $e) {
    echo "Ignore ALTER TABLE error: " . $e->getMessage() . "\n";
}

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    Schema::dropIfExists('imei_logs');
    Schema::dropIfExists('imei_devices');
    DB::statement("DELETE FROM migrations WHERE migration LIKE '%imei_devices%' OR migration LIKE '%imei_logs%'");
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "Dropped tables.\n";

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
