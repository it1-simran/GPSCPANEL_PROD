<?php

namespace App\Http\Controllers\Webhooks;

use App\Contracts\SmsProviderInterface;
use App\Http\Controllers\Controller;
use App\Models\SmsDevice;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsWebhookController extends Controller
{
    protected $smsProvider;

    public function __construct(SmsProviderInterface $smsProvider)
    {
        $this->smsProvider = $smsProvider;
    }

    public function handle(Request $request)
    {
        if (!$this->smsProvider->validateWebhook($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $incoming = $this->smsProvider->parseIncomingSms($request);

        // Find the device by phone number
        $device = SmsDevice::where('phone_number', $incoming['from'])->first();

        if (!$device) {
            Log::warning('Received SMS from unknown device: ' . $incoming['from']);
            return response()->json(['status' => 'ignored']);
        }

        // Find the last outbound message to this device that hasn't been replied to
        $lastOutbound = SmsLog::where('device_id', $device->id)
            ->where('direction', 'outbound')
            ->orderBy('created_at', 'desc')
            ->first();

        // Create the inbound log
        $inboundLog = SmsLog::create([
            'device_id' => $device->id,
            'direction' => 'inbound',
            'content' => $incoming['text'],
            'status' => 'received',
            'provider_ref' => $incoming['provider_ref'],
            'replied_to_id' => $lastOutbound ? $lastOutbound->id : null,
        ]);

        // Update device last seen
        $device->update(['last_seen_at' => now()]);

        return response()->json(['status' => 'success']);
    }
}
