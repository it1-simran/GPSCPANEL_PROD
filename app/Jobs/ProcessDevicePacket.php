<?php

namespace App\Jobs;

use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use App\Events\ImeiLogReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDevicePacket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;
    protected $sourceIp;

    public function __construct(string $payload, ?string $sourceIp = null)
    {
        $this->payload = $payload;
        $this->sourceIp = $sourceIp;
    }

    public function handle()
    {
        $imei = $this->extractImei($this->payload);

        if (!$imei) {
            Log::warning("ProcessDevicePacket: No valid IMEI found in payload.", ['payload' => $this->payload]);
            return;
        }

        $device = ImeiDevice::where('imei', $imei)->first();

        if (!$device) {
            Log::info("ProcessDevicePacket: Unregistered IMEI ignored.", ['imei' => $imei]);
            return;
        }

        if ($device->status === 'close') {
            Log::info("ProcessDevicePacket: Device marked as 'close'. Dropping packet.", ['imei' => $imei]);
            return;
        }

        if (!$this->isWithinSchedule($device)) {
            Log::info("ProcessDevicePacket: Device outside of schedule.", ['imei' => $imei]);
            return;
        }

        $log = null;
        if ($device->status === 'active') {
            $log = ImeiLog::create([
                'imei_id' => $device->id,
                'raw_packet' => $this->payload,
                'source_ip' => $this->sourceIp,
                'logged_at' => now(),
            ]);
        } else {
            // 'inactive' status - create a temporary model for broadcasting without saving to DB
            $log = new ImeiLog([
                'imei_id' => $device->id,
                'raw_packet' => $this->payload,
                'source_ip' => $this->sourceIp,
                'logged_at' => now(),
            ]);
            $log->setRelation('device', $device);
        }

        // Broadcast the log received event
        try {
            broadcast(new ImeiLogReceived($log));
            Log::info("Broadcast (".($device->status).") successful for IMEI: " . $imei);
        } catch (\Exception $e) {
            Log::error("Broadcast failed for IMEI " . $imei . ": " . $e->getMessage());
        }
    }

    protected function extractImei($payload)
    {
        $data = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['imei'])) {
            return $data['imei'];
        }

        if (preg_match('/(?:^|\D)(\d{15})(?:\D|$)/', $payload, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function isWithinSchedule($device)
    {
        return ImeiDevice::where('id', $device->id)->withinSchedule()->exists();
    }
}
