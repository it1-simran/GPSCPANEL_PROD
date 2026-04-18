<?php

namespace App\Http\Controllers;

use App\Models\ImeiCommand;
use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Services\ImeiTrackerService;
use App\Services\CommandExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiveTrackerController extends Controller
{
    public function __construct(
        protected ImeiTrackerService $trackerService,
        protected CommandExecutionService $commandExecutionService
    )
    {
    }

    public function index(Request $request)
    { 
        $imei = $request->query('imei');
        $device = $imei ? ImeiDevice::with(['commands' => function ($query) {
            $query->latest();
        }])->where('imei', $imei)->first() : null;
        $allDevices = ImeiDevice::orderBy('imei')->get();
        $defaults = $this->trackerService->buildDefaultFilters($device);

        $startAt = $request->query('start_at') ? \App\Helper\CommonHelper::convertLocalToUTC($request->query('start_at')) : $defaults['start_at'];
        $endAt = $request->query('end_at') ? \App\Helper\CommonHelper::convertLocalToUTC($request->query('end_at')) : $defaults['end_at'];

        if ($device && $device->effective_end_at && $endAt->gt($device->effective_end_at)) {
            $endAt = $device->effective_end_at->copy();
        }
        if ($startAt->gt($endAt)) {
            $startAt = $endAt->copy()->subDays(7);
        }

        $initialLogs = collect();
        $totalLogsCount = 0;
        if ($device) {
            $baseQuery = $this->baseLogsQuery($device, $startAt, $endAt);
            $totalLogsCount = $baseQuery->count();
            $initialLogs = $baseQuery
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get()
                ->reverse()
                ->values();
        }

        return view('tracker.index', [
            'device' => $device,
            'imei' => $imei,
            'allDevices' => $allDevices,
            'initialLogs' => $initialLogs,
            'totalLogsCount' => $totalLogsCount,
            'filters' => [
                'start_at' => $startAt->format('Y-m-d\TH:i'),
                'end_at' => $endAt->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function fetchLogs(Request $request, $imei)
    {
        $device = ImeiDevice::where('imei', $imei)->first();
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        [$startAt, $endAt] = $this->validatedWindow($device, $request);
        $lastId = (int) $request->query('last_id', 0);

        $query = $this->baseLogsQuery($device, $startAt, $endAt);
        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        }

        $logs = $query->orderBy('id', 'asc')->limit(200)->get();

        $formattedLogs = $logs->map(function ($log) {
            $logArray = $log->toArray();
            $logArray['logged_at_formatted'] = $log->logged_at ? \App\Helper\CommonHelper::getDateAsTimeZone($log->logged_at, 'Y-m-d H:i:s') : null;
            $logArray['logged_at'] = $log->logged_at ? \App\Helper\CommonHelper::getDateAsTimeZone($log->logged_at, 'Y-m-d H:i:s') : null;
            return $logArray;
        });

        return response()->json([
            'logs' => $formattedLogs,
            'last_id' => $logs->max('id') ?: $lastId,
            'status' => $device->status,
            'status_label' => $device->status_label,
            'effective_end_at' => $device->effective_end_at ? \App\Helper\CommonHelper::getDateAsTimeZone($device->effective_end_at, 'Y-m-d H:i:s') : null,
        ]);
    }

    public function queueCommand(Request $request, ImeiDevice $device)
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:500'],
        ]);

        $command = ImeiCommand::create([
            'imei_id' => $device->id,
            'command' => $validated['command'],
            'status' => ImeiCommand::STATUS_PENDING,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Command queued successfully.',
                'command' => $command
            ]);
        }

        return back()->with('success', 'Command queued successfully.');
    }

    /**
     * Execute a queued command and update its status
     * API Endpoint: POST /api/commands/execute
     */
    public function executeCommand(Request $request)
    {
        $validated = $request->validate([
            'imei' => ['required', 'string'],
            'command_name' => ['nullable', 'string'],
            'command' => ['required', 'string'],
        ]);

        // Find device by IMEI
        $device = ImeiDevice::where('imei', $validated['imei'])->first();
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        // Find the latest pending command matching this command text
        $command = ImeiCommand::where('imei_id', $device->id)
            ->where('command', $validated['command'])
            ->where('status', ImeiCommand::STATUS_PENDING)
            ->latest()
            ->first();

        if (!$command) {
            return response()->json([
                'success' => false,
                'message' => 'No pending command found',
            ], 404);
        }

        // Execute the command
        $result = $this->commandExecutionService->executeCommand($command, $device);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => [
                'command_id' => $command->id,
                'command' => $command->command,
                'status' => $command->status_label,
                'response_time' => $command->response_time,
                'executed_at' => $command->executed_at ? \App\Helper\CommonHelper::getDateAsTimeZone($command->executed_at, 'Y-m-d H:i:s') : null,
            ]
        ]);
    }

    /**
     * Get command status
     * API Endpoint: GET /api/commands/status?imei=xxx&command=yyy
     */
    public function getCommandStatus(Request $request)
    {
        $imei = $request->query('imei');
        $commandText = $request->query('command');

        $device = ImeiDevice::where('imei', $imei)->first();
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        $commands = ImeiCommand::where('imei_id', $device->id);
        
        if ($commandText) {
            $commands->where('command', $commandText);
        }

        $commands = $commands->latest()->limit(10)->get();

        return response()->json([
            'success' => true,
            'device_imei' => $imei,
            'commands' => $commands->map(function ($cmd) {
                return [
                    'id' => $cmd->id,
                    'command' => $cmd->command,
                    'status' => $cmd->status_label,
                    'status_code' => $cmd->status,
                    'sent_at' => $cmd->sent_at ? \App\Helper\CommonHelper::getDateAsTimeZone($cmd->sent_at, 'Y-m-d H:i:s') : null,
                    'executed_at' => $cmd->executed_at ? \App\Helper\CommonHelper::getDateAsTimeZone($cmd->executed_at, 'Y-m-d H:i:s') : null,
                    'response_time' => $cmd->response_time,
                    'response' => $cmd->device_response ? json_decode($cmd->device_response, true) : null,
                ];
            })
        ]);
    }

    public function downloadLogs(Request $request, ImeiDevice $device)
    {
        // Increase limits for large Excel exports
        set_time_limit(300); // 5 minutes execution time
        ini_set('memory_limit', '512M');

        [$startAt, $endAt] = $this->validatedWindow($device, $request);
        
        $query = $this->baseLogsQuery($device, $startAt, $endAt)->orderBy('id');
        $count = $query->count();
        $filename = 'tracker-logs-' . $device->imei . '-' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TrackerLogsExport($query, $device, $count),
            $filename
        );
    }

    public function closeConnection(ImeiDevice $device)
    {
        $device->update(['status' => ImeiDevice::STATUS_CLOSE]);
        return response()->json(['success' => true]);
    }

    public function testBroadcast(ImeiDevice $device)
    {
        $log = new ImeiLog([
            'imei_id' => $device->id,
            'raw_packet' => 'TEST_PULSE_' . now()->format('H:i:s') . '_OK',
            'source_ip' => '127.0.0.1',
            'logged_at' => now(),
        ]);
        $log->setRelation('device', $device);

        broadcast(new \App\Events\ImeiLogReceived($log));

        return response()->json(['success' => true]);
    }

    protected function validatedWindow(ImeiDevice $device, Request $request): array
    {
        $defaults = $this->trackerService->buildDefaultFilters($device);
        $startAt = $request->query('start_at') ? \App\Helper\CommonHelper::convertLocalToUTC($request->query('start_at')) : $defaults['start_at'];
        $endAt = $request->query('end_at') ? \App\Helper\CommonHelper::convertLocalToUTC($request->query('end_at')) : $defaults['end_at'];

        if ($device && $device->effective_end_at && $endAt->gt($device->effective_end_at)) {
            $endAt = $device->effective_end_at->copy();
        }

        if ($startAt->gt($endAt)) {
            $startAt = $endAt->copy()->subDays(7);
        }

        return [$startAt, $endAt];
    }

    protected function baseLogsQuery(ImeiDevice $device, Carbon $startAt, Carbon $endAt)
    {
        return ImeiLog::with('device')
            ->where('imei_id', $device->id)
            ->whereBetween('logged_at', [$startAt, $endAt]);
    }
}
