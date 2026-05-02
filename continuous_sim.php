<?php

/**
 * Continuous Device Traffic Simulator
 * Run this script BEFORE starting your Test Plan in the browser.
 */

use Illuminate\Contracts\Console\Kernel;
use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Models\PacketType;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$imei = '490154203237518'; 
$packetTypeId = 1; // You might need to change this based on your plan

echo "\n🔄 CONTINUOUS SIMULATOR STARTED (Ctrl+C to stop)...\n";
echo "📡 Monitoring IMEI: $imei\n";

$device = ImeiDevice::where('imei', $imei)->first();
if (!$device) die("❌ Error: Device not found.\n");

// Try to auto-detect the packet type if ID 1 isn't correct
$pt = PacketType::with('fields')->find($packetTypeId);
if (!$pt) {
    $pt = PacketType::with('fields')->first();
    echo "⚠️ Packet Type $packetTypeId not found, falling back to: " . ($pt ? $pt->name : 'NONE') . "\n";
}

if (!$pt) die("❌ Error: No packet types found in system.\n");

echo "✅ Using structure: {$pt->name} (Header: '{$pt->header_identifier}')\n";

while (true) {
    $fields = $pt->fields->sortBy('sequence');
    $maxSeq = $fields->max('sequence') ?: 1;
    
    // Initialize parts array with empty strings up to max sequence
    $parts = array_fill(0, $maxSeq, '');
    
    foreach ($fields as $field) {
        $idx = $field->sequence - 1;
        $val = '0';
        
        if ($field->sequence == 1 && $pt->header_identifier) {
            $val = $pt->header_identifier;
        } elseif ($field->fixed_value) {
            $val = $field->fixed_value;
        } elseif ($field->validation_type == 'imei') {
            $val = str_pad($imei, 15, '0', STR_PAD_LEFT);
        } elseif ($field->data_type == 'Numeric') {
            $val = '1';
        } else {
            $val = 'DATA';
        }
        
        if ($field->length) {
            $val = str_pad(substr($val, 0, $field->length), $field->length, '0');
        }
        
        $parts[$idx] = $val;
    }

    $rawPacket = implode($pt->delimiter ?: ',', $parts);
    
    $log = ImeiLog::create([
        'imei_id' => $device->id,
        'raw_packet' => $rawPacket,
        'logged_at' => now(),
    ]);

    echo "[" . date('H:i:s') . "] 📥 Log #{$log->id} | Parts: " . count($parts) . " | Packet: " . substr($rawPacket, 0, 50) . "...\n";
    
    sleep(3);
}
