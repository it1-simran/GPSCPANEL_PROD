<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $result = DB::select("DESCRIBE imei_logs");
    echo "Table 'imei_logs' exists. Structure:\n";
    print_r($result);
} catch (\Exception $e) {
    echo "Table 'imei_logs' does NOT exist or could not be described. Error: " . $e->getMessage() . "\n";
}

try {
    $result = DB::select("DESCRIBE imei_devices");
    echo "\nTable 'imei_devices' exists. Structure:\n";
    print_r($result);
} catch (\Exception $e) {
    echo "Table 'imei_devices' does NOT exist or could not be described. Error: " . $e->getMessage() . "\n";
}
