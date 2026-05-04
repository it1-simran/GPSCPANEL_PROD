<?php

/**
 * Test Plan Automation Validator
 */

use Illuminate\Contracts\Console\Kernel;
use App\Models\ImeiDevice;
use App\Models\TestPlan;
use App\Models\TestPlanExecution;
use App\Models\ImeiLog;
use App\Models\PacketType;
use App\Services\TestPlanExecutionService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// --- CONFIGURATION ---
$imei = '490154203237518'; 
$testPlanId = 1;          

echo "\n🚀 Starting Test Plan System Validation...\n";

$device = ImeiDevice::where('imei', $imei)->first();
$plan = TestPlan::find($testPlanId);
if (!$device || !$plan) die("❌ Error: Device or Plan not found.\n");

echo "✅ Device: {$device->imei}\n";
echo "✅ Plan: {$plan->name}\n";

$execution = TestPlanExecution::create([
    'test_plan_id' => $plan->id,
    'imei_device_id' => $device->id,
    'status' => 'pending',
    'started_at' => now(),
]);

echo "📝 Execution Created: ID #{$execution->id}\n";
echo "📡 Simulating incoming device traffic...\n";

$rawPacket = 'MOCK_DATA';
$validateStep = $plan->steps->where('step_type', 'validate_response')->first();
if ($validateStep && isset($validateStep->config['packet_type_id'])) {
    $pt = PacketType::with('fields')->find($validateStep->config['packet_type_id']);
    if ($pt) {
        $fields = $pt->fields->sortBy('sequence');
        $parts = [];
        foreach ($fields as $field) {
            $val = '0';
            if ($field->sequence == 1 && $pt->header_identifier) $val = $pt->header_identifier;
            elseif ($field->fixed_value) $val = $field->fixed_value;
            elseif ($field->length) $val = str_pad('1', $field->length, '0');
            else $val = '1';
            $parts[] = $val;
        }
        $rawPacket = implode($pt->delimiter ?: ',', $parts);
    }
}

$log = ImeiLog::create([
    'imei_id' => $device->id,
    'raw_packet' => $rawPacket,
    'logged_at' => now(),
]);

echo "📥 Log Inserted: ID #{$log->id}\n";
echo "📄 Raw: {$rawPacket}\n";

echo "\n⚙️ Running Execution Service...\n";

try {
    $service = app(TestPlanExecutionService::class);
    $service->execute($execution);
    $execution->refresh();
    
    echo "\n------------------------------------------------\n";
    echo "📊 FINAL STATUS: " . strtoupper($execution->status) . "\n";
    echo "📝 SUMMARY: " . $execution->summary . "\n";
    echo "------------------------------------------------\n";

    foreach ($execution->logs as $log) {
        $icon = $log->status === 'pass' ? '✅' : '❌';
        echo "$icon Step #{$log->step->sequence}: {$log->status} " . ($log->error_message ? "({$log->error_message})" : "") . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Crashed: " . $e->getMessage() . "\n";
}
echo "\n🏁 Validation Complete.\n";
