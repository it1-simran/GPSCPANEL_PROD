<?php
// php fake_logs.php 004400981955188

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

$imeiStr = isset($argv[1]) ? $argv[1] : "004400981955188";
$count = rand(25000, 30000);

echo "Finding device with IMEI: $imeiStr\n";
$device = ImeiDevice::where('imei', $imeiStr)->first();

if (!$device) {
    echo "Device not found!\n";
    exit(1);
}

echo "Found device ID: {$device->id}. Generating $count logs...\n";

$logs = [];
$now = Carbon::now();

DB::disableQueryLog();

$batchSize = 2000;
$totalInserted = 0;

for ($i = 0; $i < $count; $i++) {
    $lat = 28.61 + (rand(-5000, 5000) / 100000);
    $lon = 77.20 + (rand(-5000, 5000) / 100000);
    $speed = rand(15, 85);
    $time = $now->copy()->subSeconds($count - $i)->format('Y-m-d H:i:s');
    $timeOnly = $now->copy()->subSeconds($count - $i)->format('H:i:s');
    
    $payload = json_encode([
        "imei" => $imeiStr,
        "latitude" => $lat,
        "longitude" => $lon,
        "speed" => $speed,
        "bearing" => rand(0, 359),
        "altitude" => rand(150, 250),
        "accuracy" => rand(5, 15),
        "timestamp" => $time,
        "data" => "START,LAT:$lat,LON:$lon,SPEED:$speed,TIME:$timeOnly,END"
    ]);

    $logs[] = [
        'imei_id' => $device->id,
        'raw_packet' => $payload,
        'source_ip' => '127.0.0.1',
        'logged_at' => $time,
        'created_at' => $time,
        'updated_at' => $time,
    ];

    if (count($logs) >= $batchSize) {
        ImeiLog::insert($logs);
        $totalInserted += count($logs);
        $logs = [];
        echo "Inserted $totalInserted logs...\n";
    }
}

if (count($logs) > 0) {
    ImeiLog::insert($logs);
    $totalInserted += count($logs);
}

// Update the last_log_id if necessary
$lastLog = ImeiLog::where('imei_id', $device->id)->orderBy('id', 'desc')->first();
if ($lastLog) {
    $device->last_log_id = $lastLog->id;
    $device->save();
}

echo "Successfully completed generating $totalInserted logs for IMEI $imeiStr.\n";