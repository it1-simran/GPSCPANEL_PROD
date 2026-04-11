<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommandFetcher
{
    /**
     * Get pending commands for a specific IMEI
     * 
     * @param string $imei
     * @return array
     */
    public static function getPendingCommandsByImei(string $imei): array
    {
        try {
            // Find the imei_device record by IMEI string
            $imeiDevice = DB::table('imei_devices')
                ->where('imei', $imei)
                ->first();

            if (!$imeiDevice) {
                return [];
            }

            // Fetch all pending commands (status = 0) ordered by creation
            $commands = DB::table('imei_commands')
                ->where('imei_id', $imeiDevice->id)
                ->where('status', 0) // Pending
                ->orderBy('created_at', 'ASC')
                ->get()
                ->toArray();

            return $commands;
        } catch (\Exception $e) {
            // Log error but don't throw - fail gracefully
            \Log::error('CommandFetcher::getPendingCommandsByImei error', [
                'imei' => $imei,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Format commands for API response
     * 
     * @param array $commands
     * @return array
     */
    public static function formatCommandsForResponse(array $commands): array
    {
        return array_map(function ($command) {
            return [
                'id' => $command->id ?? $command['id'],
                'command' => $command->command ?? $command['command'],
                'created_at' => isset($command->created_at) 
                    ? Carbon::parse($command->created_at)->toIso8601String()
                    : Carbon::parse($command['created_at'])->toIso8601String(),
                'sent_at' => isset($command->sent_at) && $command->sent_at
                    ? Carbon::parse($command->sent_at)->toIso8601String()
                    : null,
            ];
        }, $commands);
    }

    /**
     * Mark commands as sent by command IDs
     * 
     * @param array $commandIds
     * @return bool
     */
    public static function markCommandsAsSent(array $commandIds): bool
    {
        if (empty($commandIds)) {
            return true;
        }

        try {
            DB::table('imei_commands')
                ->whereIn('id', $commandIds)
                ->update([
                    'status' => 1, // Sent
                    'sent_at' => Carbon::now('UTC')->toDateTimeString()
                ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('CommandFetcher::markCommandsAsSent error', [
                'command_ids' => $commandIds,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Mark commands as sent by IMEI (all pending commands)
     * 
     * @param string $imei
     * @return int Number of commands updated
     */
    public static function markPendingCommandsAsSentByImei(string $imei): int
    {
        try {
            $imeiDevice = DB::table('imei_devices')
                ->where('imei', $imei)
                ->first();

            if (!$imeiDevice) {
                return 0;
            }

            $updated = DB::table('imei_commands')
                ->where('imei_id', $imeiDevice->id)
                ->where('status', 0) // Pending
                ->update([
                    'status' => 1, // Sent
                    'sent_at' => Carbon::now('UTC')->toDateTimeString()
                ]);

            return $updated;
        } catch (\Exception $e) {
            \Log::error('CommandFetcher::markPendingCommandsAsSentByImei error', [
                'imei' => $imei,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
