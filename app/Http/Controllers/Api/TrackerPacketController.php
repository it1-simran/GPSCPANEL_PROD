<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImeiTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackerPacketController extends Controller
{
    public function store(Request $request, ImeiTrackerService $trackerService): JsonResponse
    {
        $payload = $request->getContent();

        if (empty(trim($payload))) {
            return response()->json([
                'status' => 'FAIL',
                'message' => 'EMPTY_PAYLOAD',
            ], 400);
        }

        $result = $trackerService->processPayload($payload, $request->ip(), true);
        $commandMeta = $trackerService->buildTrackerCommandMetadata($result['command'] ?? null);
        $device = $result['device'] ?? null;
        $log = $result['log'] ?? null;

        return response()->json([
            'status' => ($result['handled'] ?? false) ? 'SUCCESS' : 'IGNORED',
            'handled' => (bool) ($result['handled'] ?? false),
            'stored' => (bool) ($result['stored'] ?? false),
            'broadcasted' => (bool) ($result['broadcasted'] ?? false),
            'reason' => $result['reason'] ?? null,
            'imei' => $result['imei'] ?? null,
            'device' => $device ? [
                'id' => $device->id,
                'imei' => $device->imei,
                'status' => $device->status,
                'status_label' => $device->status_label,
                'schedule_start' => optional($device->schedule_start)->toDateTimeString(),
                'schedule_end' => optional($device->schedule_end)->toDateTimeString(),
                'effective_start_at' => optional($device->effective_start_at)->toDateTimeString(),
                'effective_end_at' => optional($device->effective_end_at)->toDateTimeString(),
            ] : null,
            'log' => $log ? [
                'id' => $log->id,
                'logged_at' => optional($log->logged_at)->toDateTimeString(),
                'source_ip' => $log->source_ip,
            ] : null,
            'command' => $commandMeta,
        ]);
    }
}
