<?php

namespace App\Http\Controllers;

use App\Models\TestPlan;
use App\Models\TestPlanExecution;
use App\Models\ImeiDevice;
use App\Services\TestPlanExecutionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestPlanExecutionController extends Controller
{
    protected $executionService;

    public function __construct(TestPlanExecutionService $executionService)
    {
        $this->executionService = $executionService;
    }

    public function index()
    {
        $testPlans = TestPlan::where('is_active', true)->get();
        $devices = ImeiDevice::all();
        return view('test_plans.validate', compact('testPlans', 'devices'));
    }

    public function execute(Request $request)
    {
        $validated = $request->validate([
            'imei' => 'required|string|exists:imei_devices,imei',
            'test_plan_id' => 'required|exists:test_plans,id',
        ]);

        $device = ImeiDevice::where('imei', $validated['imei'])->first();
        $testPlan = TestPlan::find($validated['test_plan_id']);

        $execution = TestPlanExecution::create([
            'test_plan_id' => $testPlan->id,
            'imei_device_id' => $device->id,
            'status' => TestPlanExecution::STATUS_PENDING,
        ]);

        // Start execution in a background-friendly way or synchronously for this demo
        // For simplicity in this environment, we might call it directly or use a queue
        // But since we want real-time streaming, we'll probably trigger it and then stream the logs.
        
        return response()->json([
            'success' => true,
            'execution_id' => $execution->id,
            'redirect' => route('admin.test-report', $execution->id)
        ]);
    }

    public function report(TestPlanExecution $execution)
    {
        $execution->load(['testPlan.steps', 'logs.step', 'device']);
        return view('test_plans.report', compact('execution'));
    }

    public function stop(TestPlanExecution $execution)
    {
        $execution->update(['status' => TestPlanExecution::STATUS_STOPPED, 'completed_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function stream(TestPlanExecution $execution)
    {
        return new StreamedResponse(function () use ($execution) {
            $lastLogId = 0;
            $startTime = time();
            $maxTime = 300; // 5 minutes

            // If not running, start it
            if ($execution->status === TestPlanExecution::STATUS_PENDING) {
                // Set the callback to stream logs immediately as they happen
                $this->executionService->setOnLogCallback(function($log) {
                    echo "event: log\n";
                    echo "data: " . json_encode([
                        'step_id' => $log->step_id,
                        'sequence' => $log->step ? $log->step->sequence : 'SYS',
                        'type' => $log->step ? $log->step->step_type : 'INIT',
                        'status' => $log->status,
                        'message' => $log->error_message,
                        'duration' => $log->duration_ms,
                        'output' => $log->output_data,
                    ]) . "\n\n";

                    if (ob_get_level() > 0) ob_flush();
                    flush();
                });

                // Execute synchronously but stream in real-time
                $this->executionService->execute($execution);

                // Execution is done, stream the complete event
                $execution->refresh();
                echo "event: complete\n";
                echo "data: " . json_encode([
                    'status' => $execution->status,
                    'summary' => $execution->summary,
                    'completed_at' => $execution->completed_at ? $execution->completed_at->toDateTimeString() : now()->toDateTimeString()
                ]) . "\n\n";

                if (ob_get_level() > 0) ob_flush();
                flush();
            } else {
                // Fallback for already running/completed executions
                while (time() - $startTime < $maxTime) {
                    if (connection_aborted()) break;

                    $execution->refresh();
                    
                    $logs = $execution->logs()->where('id', '>', $lastLogId)->get();
                    foreach ($logs as $log) {
                        echo "event: log\n";
                        echo "data: " . json_encode([
                            'step_id' => $log->step_id,
                            'sequence' => $log->step ? $log->step->sequence : 'SYS',
                            'type' => $log->step ? $log->step->step_type : 'INIT',
                            'status' => $log->status,
                            'message' => $log->error_message,
                            'duration' => $log->duration_ms,
                            'output' => $log->output_data,
                        ]) . "\n\n";
                        $lastLogId = $log->id;
                    }

                    if (in_array($execution->status, [TestPlanExecution::STATUS_PASSED, TestPlanExecution::STATUS_FAILED, TestPlanExecution::STATUS_STOPPED])) {
                        echo "event: complete\n";
                        echo "data: " . json_encode([
                            'status' => $execution->status,
                            'summary' => $execution->summary,
                            'completed_at' => $execution->completed_at ? $execution->completed_at->toDateTimeString() : now()->toDateTimeString()
                        ]) . "\n\n";
                        break;
                    }

                    echo "event: heartbeat\n";
                    echo "data: " . json_encode(['ts' => now()->toDateTimeString()]) . "\n\n";

                    if (ob_get_level() > 0) ob_flush();
                    flush();
                    sleep(1);
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
