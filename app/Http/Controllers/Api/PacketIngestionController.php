<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImeiTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PacketIngestionController extends Controller
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

        return response()->json([
            'status' => ($result['handled'] ?? false) ? 'SUCCESS' : 'IGNORED',
            'handled' => (bool) ($result['handled'] ?? false),
            'stored' => (bool) ($result['stored'] ?? false),
            'broadcasted' => (bool) ($result['broadcasted'] ?? false),
            'reason' => $result['reason'] ?? null,
            'imei' => $result['imei'] ?? null,
            'command' => $commandMeta,
            'command_text' => $commandMeta['command'] ?? '',
            'command_id' => $commandMeta['id'] ?? null,
        ], 200);
    }
}
