<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tableName = 'imei_devices';
if (Schema::hasTable($tableName)) {
    $columns = Schema::getColumnListing($tableName);
    echo "Columns in '$tableName':\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    // Check enum values for status if possible
    try {
        $result = DB::select("SHOW COLUMNS FROM $tableName WHERE Field = 'status'");
        if ($result) {
            echo "Status column type: " . $result[0]->Type . "\n";
        }
    } catch (\Exception $e) {
        echo "Could not check status column type.\n";
    }
} else {
    echo "Table '$tableName' does NOT exist.\n";
}

$tableName = 'jobs';
if (Schema::hasTable($tableName)) {
    echo "\nTable '$tableName' exists.\n";
}
