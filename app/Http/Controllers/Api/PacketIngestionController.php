<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessDevicePacket;

class PacketIngestionController extends Controller
{
    public function store(Request $request)
    {
        // We accept both string packet or JSON packet natively as a string payload
        $payload = $request->getContent();
        $sourceIp = $request->ip();

        if (empty(trim($payload))) {
            return response()->json(['error' => 'Empty payload'], 400);
        }

        ProcessDevicePacket::dispatch($payload, $sourceIp);

        return response()->json(['status' => 'Packet queued successfully'], 202);
    }
}
