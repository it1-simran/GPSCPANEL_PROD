<?php

namespace App\Http\Controllers;

use App\Contracts\SmsProviderInterface;
use App\Models\SmsDevice;
use App\Models\SmsLog;
use App\Models\SmsCommandTemplate;
use Illuminate\Http\Request;

class SmsDeviceController extends Controller
{
    protected $smsProvider;

    public function __construct(SmsProviderInterface $smsProvider)
    {
        $this->smsProvider = $smsProvider;
    }

    public function index()
    {
        return view('sms-portal.dashboard', [
            'devices' => SmsDevice::with(['logs' => fn($q) => $q->latest()])->get(),
            'templates' => SmsCommandTemplate::all(),
        ]);
    }

    public function list()
    {
        return SmsDevice::with('logs')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|unique:sms_devices,phone_number',
            'name' => 'nullable|string',
        ]);

        $device = SmsDevice::create([
            'name' => $request->input('name') ?? 'Device ' . substr($request->input('phone_number'), -4),
            'phone_number' => $request->input('phone_number'),
            'imei' => 'TEMP-' . time(), // Temporary IMEI or could be requested
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'device' => $device,
        ]);
    }

    public function getLogs(SmsDevice $device)
    {
        return response()->json([
            'status' => 'success',
            'logs' => $device->logs()->latest()->take(50)->get(),
        ]);
    }

    public function sendCommand(Request $request, SmsDevice $device)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $message = $request->input('command');

        try {
            // 1. Send via provider
            $providerRef = $this->smsProvider->send($device, $message);

            // 2. Log the outbound message
            $log = SmsLog::create([
                'device_id' => $device->id,
                'direction' => 'outbound',
                'content' => $message,
                'status' => 'sent',
                'provider_ref' => $providerRef,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Command sent',
                'log_id' => $log->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send command: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function simulateInbound(Request $request, SmsDevice $device)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $lastOutbound = SmsLog::where('device_id', $device->id)
            ->where('direction', 'outbound')
            ->orderBy('created_at', 'desc')
            ->first();

        $log = SmsLog::create([
            'device_id' => $device->id,
            'direction' => 'inbound',
            'content' => $request->input('text'),
            'status' => 'received',
            'provider_ref' => 'sim-' . time(),
            'replied_to_id' => $lastOutbound ? $lastOutbound->id : null,
        ]);

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'success',
            'log' => $log,
        ]);
    }

    public function destroy(SmsDevice $device)
    {
        $device->logs()->delete(); // Clean up logs first
        $device->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Device removed',
        ]);
    }

    public function saveTemplate(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'payload' => 'required|string|max:500',
            'description' => 'nullable|string|max:500',
        ]);

        $template = SmsCommandTemplate::create([
            'label' => $request->input('label'),
            'payload' => $request->input('payload'),
            'description' => $request->input('description'),
        ]);

        return response()->json([
            'status' => 'success',
            'template' => $template,
            'message' => 'Template saved successfully',
        ]);
    }

    public function deleteTemplate(SmsCommandTemplate $template)
    {
        $template->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Template deleted successfully',
        ]);
    }
}
