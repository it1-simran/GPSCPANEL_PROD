<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\Artisan;
Artisan::call('migrate', [
    '--path' => 'database/migrations/2026_03_25_081803_create_imei_logs_table.php',
    '--force' => true
]);
echo Artisan::output();
