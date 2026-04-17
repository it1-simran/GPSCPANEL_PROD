<?php

namespace App\Services;

use App\Events\ImeiLogReceived;
use App\Models\ImeiCommand;
use App\Models\ImeiDevice;
use App\Models\ImeiLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ImeiTrackerService
{
    public function extractImei(string $payload): ?string
    {
        $data = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (!empty($data['imei'])) {
                return (string) $data['imei'];
            }
            if (!empty($data[35])) {
                return (string) $data[35];
            }
        }

        if (preg_match('/(?:^|\D)(\d{15})(?:\D|$)/', $payload, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function processPayload(string $payload, ?string $sourceIp = null, bool $claimCommand = false): array
    {
        $imei = $this->extractImei($payload);
        if (!$imei) {
            return [
                'handled' => false,
                'stored' => false,
                'broadcasted' => false,
                'imei' => null,
                'device' => null,
                'log' => null,
                'command' => null,
                'reason' => 'invalid_imei',
            ];
        }

        $device = ImeiDevice::where('imei', $imei)->first();
        if (!$device) {
            return [
                'handled' => false,
                'stored' => false,
                'broadcasted' => false,
                'imei' => $imei,
                'device' => null,
                'log' => null,
                'command' => null,
                'reason' => 'tracker_not_registered',
            ];
        }

        if ($device->isClosed()) {
            return [
                'handled' => true,
                'stored' => false,
                'broadcasted' => false,
                'imei' => $imei,
                'device' => $device,
                'log' => null,
                'command' => null,
                'reason' => 'closed',
            ];
        }

        if ($device->recordingHasExpired()) {
            $device->forceFill(['status' => ImeiDevice::STATUS_OFF])->save();

            return [
                'handled' => true,
                'stored' => false,
                'broadcasted' => false,
                'imei' => $imei,
                'device' => $device->fresh(),
                'log' => null,
                'command' => null,
                'reason' => 'expired',
            ];
        }

        if (!$device->isRecordingOn()) {
            return [
                'handled' => true,
                'stored' => false,
                'broadcasted' => false,
                'imei' => $imei,
                'device' => $device,
                'log' => null,
                'command' => null,
                'reason' => 'recording_off',
            ];
        }

        if (!$device->isWithinRecordingWindow()) {
            return [
                'handled' => true,
                'stored' => false,
                'broadcasted' => false,
                'imei' => $imei,
                'device' => $device,
                'log' => null,
                'command' => null,
                'reason' => 'outside_window',
            ];
        }

        $claimedCommand = null;

        $log = DB::transaction(function () use ($device, $payload, $sourceIp, $claimCommand, &$claimedCommand) {
            $log = ImeiLog::create([
                'imei_id' => $device->id,
                'raw_packet' => $payload,
                'source_ip' => $sourceIp,
                'logged_at' => now(),
            ]);

            if (Schema::hasColumn($device->getTable(), 'last_log_id')) {
                $device->forceFill(['last_log_id' => $log->id])->save();
            }

            $claimedCommand = null;
            if ($claimCommand) {
                $claimedCommand = ImeiCommand::where('imei_id', $device->id)
                    ->where('status', 0)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if ($claimedCommand) {
                    $claimedCommand->status = 1;
                    if (Schema::hasColumn($claimedCommand->getTable(), 'sent_at')) {
                        $claimedCommand->sent_at = now();
                    }
                    $claimedCommand->save();
                }
            }

            return $log;
        });

        $log->load('device');

        $broadcasted = false;
        try {
            broadcast(new ImeiLogReceived($log));
            $broadcasted = true;
        } catch (\Throwable $e) {
            Log::error('IMEI tracker broadcast failed', [
                'imei' => $imei,
                'message' => $e->getMessage(),
            ]);
        }

        return [
            'handled' => true,
            'stored' => true,
            'broadcasted' => $broadcasted,
            'imei' => $imei,
            'device' => $device->fresh(),
            'log' => $log,
            'command' => $claimedCommand ?? null,
            'reason' => 'stored',
        ];
    }

    public function buildTrackerCommandMetadata(?ImeiCommand $command): ?array
    {
        if (!$command) {
            return null;
        }

        return [
            'id' => $command->id,
            'command' => $command->command,
            'status' => (int) $command->status,
            'sent_at' => Schema::hasColumn($command->getTable(), 'sent_at') ? optional($command->sent_at)->toDateTimeString() : null,
        ];
    }

    public function buildDefaultFilters(?ImeiDevice $device): array
    {
        $end = now();
        if ($device && $device->effective_end_at && $device->effective_end_at->lt($end)) {
            $end = $device->effective_end_at->copy();
        }

        $start = $end->copy()->subDays(7);
        if ($device && $device->effective_start_at) {
            $start = $device->effective_start_at->copy();
        }

        return [
            'start_at' => $start,
            'end_at' => $end,
        ];
    }
}
