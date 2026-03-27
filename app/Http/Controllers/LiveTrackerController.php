<?php

namespace App\Http\Controllers;

use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use Illuminate\Http\Request;

class LiveTrackerController extends Controller
{
    public function index(Request $request)
    {
        $imei = $request->query('imei');
        $device = null;
        if ($imei) {
            $device = ImeiDevice::where('imei', $imei)->first();
        }

        $allDevices = ImeiDevice::all();

        return view('tracker.index', compact('device', 'imei', 'allDevices'));
    }

    public function fetchLogs(Request $request, $imei)
    {
        $device = ImeiDevice::where('imei', $imei)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $lastId = $request->query('last_id', 0);

        $logs = ImeiLog::where('imei_id', $device->id)
            ->where('id', '>', $lastId)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'logs' => $logs
        ]);
    }
    public function closeConnection(ImeiDevice $device)
    {
        $device->update(['status' => 'close']);
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
}
