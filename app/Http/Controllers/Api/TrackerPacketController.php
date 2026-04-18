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
            'imei' => $result['imei'] ?? null,
            'cmd_id' => $commandMeta['id'] ?? null,
            'cmd' => $commandMeta['command'] ?? '',
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}
