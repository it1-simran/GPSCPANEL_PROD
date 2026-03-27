<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ImeiDevice;

$imei = "123456789012345";
$device = ImeiDevice::where('imei', $imei)->first();

if ($device) {
    echo "Device found: ID: " . $device->id . ", Status: " . $device->status . "\n";
    if ($device->withinSchedule()) {
        echo "Device is WITHIN schedule.\n";
    } else {
        echo "Device is OUTSIDE schedule.\n";
    }
} else {
    echo "Device not found for IMEI: $imei\n";
}
