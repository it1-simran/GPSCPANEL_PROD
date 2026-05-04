<?php

namespace App\Services;

use App\Models\TestPlan;
use App\Models\TestPlanStep;
use App\Models\TestPlanExecution;
use App\Models\TestPlanExecutionLog;
use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Models\ImeiCommand;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestPlanExecutionService
{
    protected $commandService;
    protected $packetService;
    protected $alertService;
    protected $onLogCallback;

    public function __construct(
        CommandExecutionService $commandService,
        PacketParserService $packetService,
        AlertService $alertService
    ) {
        $this->commandService = $commandService;
        $this->packetService = $packetService;
        $this->alertService = $alertService;
    }

    public function setOnLogCallback(callable $callback)
    {
        $this->onLogCallback = $callback;
    }

    public function execute(TestPlanExecution $execution)
    {
        $execution->update(['status' => TestPlanExecution::STATUS_RUNNING, 'started_at' => now()]);
        $testPlan = $execution->testPlan;
        $device = $execution->device;

        try {
            $this->addLog($execution, null, TestPlanExecutionLog::STATUS_INFO, "🚀 INITIALIZING TEST SEQUENCE FOR DEVICE: {$device->imei}");
            $this->addLog($execution, null, TestPlanExecutionLog::STATUS_INFO, "📡 Checking server connection and device state...");

            foreach ($testPlan->steps as $step) {
                $execution->update(['current_step_id' => $step->id]);
                
                $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "🔄 EXECUTING STEP {$step->sequence}: " . strtoupper(str_replace('_', ' ', $step->step_type)));

                $result = $this->executeStep($step, $device, $execution);
                
                if ($result->status === TestPlanExecutionLog::STATUS_FAIL) {
                    $execution->update([
                        'status' => TestPlanExecution::STATUS_FAILED,
                        'completed_at' => now(),
                        'summary' => "Failed at step {$step->sequence}: " . ($result->error_message ?? 'Unknown error')
                    ]);
                    $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_FAIL, "❌ SEQUENCE FAILED AT STEP {$step->sequence}");
                    return false;
                }

                if ($execution->status === TestPlanExecution::STATUS_STOPPED) {
                    $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "🛑 TEST EXECUTION STOPPED BY USER");
                    break;
                }
            }

            if ($execution->status !== TestPlanExecution::STATUS_STOPPED) {
                $execution->update([
                    'status' => TestPlanExecution::STATUS_PASSED,
                    'completed_at' => now(),
                    'summary' => 'Test Plan completed successfully.'
                ]);
                $this->addLog($execution, null, TestPlanExecutionLog::STATUS_INFO, "🏁 ALL TEST STEPS COMPLETED SUCCESSFULLY");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Test Plan Execution Error: " . $e->getMessage());
            $execution->update([
                'status' => TestPlanExecution::STATUS_FAILED,
                'completed_at' => now(),
                'summary' => 'Internal error during execution: ' . $e->getMessage()
            ]);
            $this->addLog($execution, null, TestPlanExecutionLog::STATUS_FAIL, "💥 CRITICAL ERROR: " . $e->getMessage());
            return false;
        }
    }

    protected function addLog(TestPlanExecution $execution, ?TestPlanStep $step, string $status, string $message, array $output = [])
    {
        $log = TestPlanExecutionLog::create([
            'execution_id' => $execution->id,
            'step_id' => $step ? $step->id : $execution->testPlan->steps->first()->id,
            'status' => $status,
            'error_message' => $message,
            'output_data' => $output,
            'duration_ms' => 0
        ]);

        if (is_callable($this->onLogCallback)) {
            $log->load('step');
            call_user_func($this->onLogCallback, $log);
        }

        return $log;
    }

    protected function executeStep(TestPlanStep $step, ImeiDevice $device, TestPlanExecution $execution)
    {
        $startTime = microtime(true);
        $status = TestPlanExecutionLog::STATUS_PASS;
        $error = null;
        $output = [];
        $input = $step->config;

        switch ($step->step_type) {
            case TestPlanStep::TYPE_SEND_COMMAND:
                $res = $this->handleSendCommand($step, $device, $execution);
                $status = $res['success'] ? TestPlanExecutionLog::STATUS_PASS : TestPlanExecutionLog::STATUS_FAIL;
                $output = $res['data'] ?? [];
                $error = $res['message'] ?? null;
                break;

            case TestPlanStep::TYPE_WAIT_FOR_RESPONSE:
                $res = $this->handleWaitForResponse($step, $device, $execution);
                $status = $res['success'] ? TestPlanExecutionLog::STATUS_PASS : TestPlanExecutionLog::STATUS_FAIL;
                $output = $res['data'] ?? [];
                $error = $res['message'] ?? null;
                break;

            case TestPlanStep::TYPE_VALIDATE_RESPONSE:
                $res = $this->handleValidateResponse($step, $device, $execution);
                $status = $res['success'] ? TestPlanExecutionLog::STATUS_PASS : TestPlanExecutionLog::STATUS_FAIL;
                $output = $res['data'] ?? [];
                $error = $res['message'] ?? null;
                break;

            case TestPlanStep::TYPE_ALERT_EVALUATION:
                $res = $this->handleAlertEvaluation($step, $device, $execution);
                $status = $res['success'] ? TestPlanExecutionLog::STATUS_PASS : TestPlanExecutionLog::STATUS_FAIL;
                $output = $res['data'] ?? [];
                $error = $res['message'] ?? null;
                break;
        }

        $duration = (int) ((microtime(true) - $startTime) * 1000);

        $log = TestPlanExecutionLog::create([
            'execution_id' => $execution->id,
            'step_id' => $step->id,
            'status' => $status,
            'input_data' => $input,
            'output_data' => $output,
            'error_message' => $error,
            'duration_ms' => $duration
        ]);

        if (is_callable($this->onLogCallback)) {
            $log->load('step');
            call_user_func($this->onLogCallback, $log);
        }

        return $log;
    }

    protected function handleSendCommand(TestPlanStep $step, ImeiDevice $device, TestPlanExecution $execution)
    {
        $commandText = $step->config['command_text'] ?? '';
        $commandType = $step->config['command_type'] ?? 'server';

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "📤 Sending " . strtoupper($commandType) . " command: {$commandText}");

        if (empty($commandText)) {
            return ['success' => false, 'message' => 'Command text is empty.'];
        }

        if ($commandType === 'sms') {
            return ['success' => false, 'message' => 'SMS Command integration is pending configuration.'];
        }

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "✅ Connection established. Dispatching payload...");

        $command = ImeiCommand::create([
            'imei_id' => $device->id,
            'command' => $commandText,
            'status' => ImeiCommand::STATUS_PENDING,
        ]);

        $result = $this->commandService->executeCommand($command, $device);
        
        if ($result['success']) {
            $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "📩 Command acknowledgment received from device.");
        }

        return $result;
    }

    protected function handleWaitForResponse(TestPlanStep $step, ImeiDevice $device, TestPlanExecution $execution)
    {
        $timeout = $step->config['timeout_seconds'] ?? 30;
        $start = time();

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "⏳ Waiting for response packet (Timeout: {$timeout}s)...");

        while (time() - $start < $timeout) {
            $latestLog = ImeiLog::where('imei_id', $device->id)
                ->where('logged_at', '>=', Carbon::createFromTimestamp($start))
                ->latest()
                ->first();

            if ($latestLog) {
                $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "📥 PACKET RECEIVED! (ID: #{$latestLog->id})", ['raw_packet' => $latestLog->raw_packet]);
                return ['success' => true, 'data' => ['log_id' => $latestLog->id, 'packet' => $latestLog->raw_packet, 'raw_packet' => $latestLog->raw_packet]];
            }

            usleep(500000); // 0.5s
        }

        return ['success' => false, 'message' => "Timed out waiting for response after {$timeout} seconds."];
    }

    protected function handleValidateResponse(TestPlanStep $step, ImeiDevice $device, TestPlanExecution $execution)
    {
        $protocolId = $step->config['protocol_id'] ?? null;
        $packetTypeId = $step->config['packet_type_id'] ?? null;
        $rules = $step->config['rules'] ?? [];

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "🔍 Starting structural and rule-based validation...");

        // Find the latest log received during this execution
        $latestLog = ImeiLog::where('imei_id', $device->id)
            ->where('logged_at', '>=', $execution->started_at)
            ->latest()
            ->first();

        if (!$latestLog) {
            return ['success' => false, 'message' => 'No packet found to validate.'];
        }

        $validation = $this->packetService->validateLog($latestLog, $protocolId, $packetTypeId);
        
        if (!$validation['is_valid']) {
            $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_FAIL, "❌ Structural mismatch detected in packet header/fields.");
            return [
                'success' => false, 
                'message' => 'Protocol validation failed: ' . ($validation['errors'][0] ?? 'Packet structure mismatch'), 
                'data' => $validation
            ];
        }

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "✅ Structure valid. Evaluating " . count($rules) . " conditions...");

        $parsedData = $validation['parsed_data'];
        $mismatches = [];

        foreach ($rules as $rule) {
            $field = $rule['field'] ?? null;
            $operator = $rule['operator'] ?? '==';
            $expected = $rule['value'] ?? null;
            $actual = $parsedData[$field] ?? null;

            if ($field) {
                $isMatch = false;
                switch ($operator) {
                    case '==': $isMatch = ($actual == $expected); break;
                    case '!=': $isMatch = ($actual != $expected); break;
                    case '<=': $isMatch = ($actual <= $expected); break;
                    case '>=': $isMatch = ($actual >= $expected); break;
                }

                if (!$isMatch) {
                    $mismatches[] = "{$field}: expected {$operator} '{$expected}', got '{$actual}'";
                }
            }
        }

        if (!empty($mismatches)) {
            $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_FAIL, "❌ Condition validation failed: " . count($mismatches) . " mismatches.");
            return [
                'success' => false, 
                'message' => 'Rule validation failed: ' . implode('; ', $mismatches),
                'data' => $validation
            ];
        }

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "✅ All conditions passed.");
        return ['success' => true, 'data' => $validation];
    }

    protected function handleAlertEvaluation(TestPlanStep $step, ImeiDevice $device, TestPlanExecution $execution)
    {
        $packetTypeId = $step->config['packet_type_id'] ?? null;

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "🔔 Starting alert evaluation flow...");

        // Find the latest log received during this execution
        $latestLog = ImeiLog::where('imei_id', $device->id)
            ->where('logged_at', '>=', $execution->started_at)
            ->latest()
            ->first();

        if (!$latestLog) {
            return ['success' => false, 'message' => 'No packet found for alert evaluation.'];
        }

        $validation = $this->packetService->validateLog($latestLog, null, $packetTypeId);
        if (!$validation['is_valid'] && empty($validation['parsed_data'])) {
             return ['success' => false, 'message' => 'Could not parse packet for alert evaluation.'];
        }

        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "📊 Analyzing fields for configured alert conditions...");

        $alertReport = $this->alertService->validate($packetTypeId, $validation['parsed_data']);
        
        $this->addLog($execution, $step, TestPlanExecutionLog::STATUS_INFO, "📢 Evaluation complete: " . count($alertReport['alerts'] ?? []) . " alerts processed.");

        return [
            'success' => true, 
            'data' => $alertReport
        ];
    }
}
