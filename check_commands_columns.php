<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tableName = 'imei_commands';
if (Schema::hasTable($tableName)) {
    $columns = Schema::getColumnListing($tableName);
    echo "Columns in '$tableName':\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
} else {
    echo "Table '$tableName' does NOT exist.\n";
}
