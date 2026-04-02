<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDevicePacket;
use Illuminate\Http\Request;

class PacketIngestionController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->getContent();
        $sourceIp = $request->ip();

        if (empty(trim($payload))) {
            return response()->json(['error' => 'Empty payload'], 400);
        }

        ProcessDevicePacket::dispatch($payload, $sourceIp);

        return response()->json(['status' => 'Packet queued successfully'], 202);
    }
}
