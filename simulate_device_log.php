<?php

/**
 * Device Traffic Simulator for UI Testing
 * Use this script while running a Test Plan in the web console.
 */

use Illuminate\Contracts\Console\Kernel;
use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Models\PacketType;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// --- CONFIGURATION ---
$imei = '490154203237518'; 
$packetTypeId = 4; // Change this to match your test plan's packet type

echo "\n📡 SIMULATING DEVICE TRAFFIC FOR UI CONSOLE...\n";

$device = ImeiDevice::where('imei', $imei)->first();
if (!$device) die("❌ Error: Device with IMEI $imei not found.\n");

$pt = PacketType::with('fields')->find($packetTypeId);
if (!$pt) die("❌ Error: Packet Type ID $packetTypeId not found.\n");

echo "✅ Using structure from Packet Type: {$pt->name}\n";

$delim = $pt->delimiter ?: ',';
$fields = $pt->fields->sortBy('sequence');
$parts = [];

foreach ($fields as $field) {
    $val = '0';
    if ($field->sequence == 1 && $pt->header_identifier) {
        $val = $pt->header_identifier;
    } elseif ($field->fixed_value) {
        $val = $field->fixed_value;
    } elseif ($field->data_type == 'Numeric') {
        $val = '1';
    } else {
        $val = 'DATA';
    }
    
    // Ensure length matches if specified
    if ($field->length) {
        $val = str_pad(substr($val, 0, $field->length), $field->length, '0');
    }
    
    $parts[] = $val;
}

$rawPacket = implode($delim, $parts);

$log = ImeiLog::create([
    'imei_id' => $device->id,
    'raw_packet' => $rawPacket,
    'parsed_data' => [], 
    'logged_at' => now(),
]);

echo "🚀 LOG INSERTED SUCCESSFULLY!\n";
echo "🆔 Log ID: #{$log->id}\n";
echo "📄 Raw: {$rawPacket}\n";
echo "📊 Field Count: " . count($parts) . "\n";
echo "\nCheck your Web Console now! Step 2 should continue.\n";
