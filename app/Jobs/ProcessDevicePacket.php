<?php

namespace App\Jobs;

use App\Services\ImeiTrackerService;
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

    public function handle(ImeiTrackerService $trackerService)
    {
        $result = $trackerService->processPayload($this->payload, $this->sourceIp, false);

        if (($result['reason'] ?? null) === 'invalid_imei') {
            Log::warning('ProcessDevicePacket: No valid IMEI found in payload.', ['payload' => $this->payload]);
            return;
        }

        Log::info('ProcessDevicePacket handled payload.', [
            'imei' => $result['imei'] ?? null,
            'stored' => $result['stored'] ?? false,
            'broadcasted' => $result['broadcasted'] ?? false,
            'reason' => $result['reason'] ?? null,
        ]);
    }
}
