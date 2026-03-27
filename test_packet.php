<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Jobs\ProcessDevicePacket;

echo "1. Creating test device...\n";
$device = ImeiDevice::firstOrCreate(
    ['imei' => '123456789012345'],
    ['status' => 'active']
);
echo "- Device ID: {$device->id}, IMEI: {$device->imei}\n";

echo "2. Simulating packet dispatch...\n";
$payload = "START,123456789012345,LAT:12.34,LON:56.78,END";
ProcessDevicePacket::dispatchSync($payload, '127.0.0.1');

echo "3. Verifying log insertion...\n";
$log = ImeiLog::where('imei_id', $device->id)->latest('id')->first();
if ($log) {
    echo "- Log found! Raw Packet: {$log->raw_packet}\n";
    echo "- Source IP: {$log->source_ip}\n";
    echo "- Success!\n";
} else {
    echo "- X Failed to find log.\n";
}
