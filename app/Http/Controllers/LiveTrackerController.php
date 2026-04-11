<?php

namespace App\Http\Controllers;

use App\Models\ImeiCommand;
use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Services\ImeiTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LiveTrackerController extends Controller
{
    public function __construct(protected ImeiTrackerService $trackerService)
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

        $startAt = $request->query('start_at') ? Carbon::parse($request->query('start_at')) : $defaults['start_at'];
        $endAt = $request->query('end_at') ? Carbon::parse($request->query('end_at')) : $defaults['end_at'];

        if ($device && $device->effective_end_at && $endAt->gt($device->effective_end_at)) {
            $endAt = $device->effective_end_at->copy();
        }
        if ($startAt->gt($endAt)) {
            $startAt = $endAt->copy()->subHour();
        }

        $initialLogs = collect();
        if ($device) {
            $initialLogs = $this->baseLogsQuery($device, $startAt, $endAt)
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

        return response()->json([
            'logs' => $logs,
            'last_id' => $logs->max('id') ?: $lastId,
            'status' => $device->status,
            'status_label' => $device->status_label,
            'effective_end_at' => optional($device->effective_end_at)->toDateTimeString(),
        ]);
    }

    public function queueCommand(Request $request, ImeiDevice $device)
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:500'],
        ]);

        ImeiCommand::create([
            'imei_id' => $device->id,
            'command' => $validated['command'],
            'status' => 0,
        ]);

        return back()->with('success', 'Command queued successfully.');
    }

    public function downloadLogs(Request $request, ImeiDevice $device)
    {
        [$startAt, $endAt] = $this->validatedWindow($device, $request);
        $logs = $this->baseLogsQuery($device, $startAt, $endAt)->orderBy('id')->get();
        $filename = 'tracker-logs-' . $device->imei . '-' . now()->format('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'IMEI', 'Logged At', 'Source IP', 'Raw Packet']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    optional($log->device)->imei,
                    optional($log->logged_at)->toDateTimeString(),
                    $log->source_ip,
                    $log->raw_packet,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
        $startAt = $request->query('start_at') ? Carbon::parse($request->query('start_at')) : $defaults['start_at'];
        $endAt = $request->query('end_at') ? Carbon::parse($request->query('end_at')) : $defaults['end_at'];

        if ($device->effective_end_at && $endAt->gt($device->effective_end_at)) {
            $endAt = $device->effective_end_at->copy();
        }

        if ($startAt->gt($endAt)) {
            $startAt = $endAt->copy()->subHour();
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
