<?php

namespace App\Services;

use App\Models\ImeiCommand;
use App\Models\ImeiDevice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CommandExecutionService
{
    /**
     * Execute pending commands for a device
     */
    public function executePendingCommands(ImeiDevice $device)
    {
        $pendingCommands = $device->commands()
            ->where('status', ImeiCommand::STATUS_PENDING)
            ->get();

        $results = [];

        foreach ($pendingCommands as $command) {
            $result = $this->executeCommand($command, $device);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Execute a single command
     */
    public function executeCommand(ImeiCommand $command, ImeiDevice $device)
    {
        try {
            $startTime = microtime(true);

            // Mark as sent
            $command->markAsSent();

            // Parse and execute command
            $response = $this->sendCommandToDevice($device, $command->command);

            // Calculate response time
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            if ($response['success']) {
                // Mark as executed
                $command->markAsExecuted(
                    json_encode($response['data']),
                    $responseTime
                );

                Log::info("Command executed successfully", [
                    'command_id' => $command->id,
                    'imei' => $device->imei,
                    'command' => $command->command,
                    'response_time' => $responseTime,
                ]);

                return [
                    'success' => true,
                    'command_id' => $command->id,
                    'command' => $command->command,
                    'status' => 'Completed',
                    'response_time' => $responseTime,
                    'message' => 'Command executed successfully',
                ];
            } else {
                // Mark as failed
                $command->markAsFailed($response['error']);

                Log::warning("Command execution failed", [
                    'command_id' => $command->id,
                    'imei' => $device->imei,
                    'command' => $command->command,
                    'error' => $response['error'],
                ]);

                return [
                    'success' => false,
                    'command_id' => $command->id,
                    'command' => $command->command,
                    'status' => 'Failed',
                    'message' => $response['error'],
                ];
            }
        } catch (\Exception $e) {
            $command->markAsFailed($e->getMessage());

            Log::error("Command execution exception", [
                'command_id' => $command->id,
                'imei' => $device->imei,
                'command' => $command->command,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'command_id' => $command->id,
                'command' => $command->command,
                'status' => 'Failed',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send command to device endpoint
     */
    private function sendCommandToDevice(ImeiDevice $device, string $command)
    {
        // Get device connection info (IP, Port, etc.)
        $deviceIp = $device->last_ip ?? '127.0.0.1';
        $devicePort = $device->port ?? 5000;

        try {
            // Simulate sending command via socket/REST
            // In production, you'd implement actual device communication
            $response = $this->simulateDeviceResponse($command, $device);

            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to communicate with device: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Simulate device response for testing
     */
    private function simulateDeviceResponse(string $command, ImeiDevice $device)
    {
        // Simulate different responses based on command
        return match($command) {
            'PING' => [
                'success' => true,
                'data' => [
                    'ack' => 'PING_ACK',
                    'timestamp' => now()->toDateTimeString(),
                    'device_id' => $device->imei,
                ],
            ],
            'LOC_REQ' => [
                'success' => true,
                'data' => [
                    'latitude' => 28.61,
                    'longitude' => 77.20,
                    'accuracy' => 12,
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
            'SPD_REQ' => [
                'success' => true,
                'data' => [
                    'speed' => rand(15, 85),
                    'bearing' => rand(0, 360),
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
            'ENG_STOP' => [
                'success' => true,
                'data' => [
                    'action' => 'Engine Stopped',
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
            'ENG_START' => [
                'success' => true,
                'data' => [
                    'action' => 'Engine Started',
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
            'GEOFENCE:ENABLE' => [
                'success' => true,
                'data' => [
                    'action' => 'Geofence Enabled',
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
            default => [
                'success' => true,
                'data' => [
                    'ack' => 'ACK',
                    'command' => $command,
                    'timestamp' => now()->toDateTimeString(),
                ],
            ],
        };
    }

    /**
     * Get command execution status
     */
    public function getCommandStatus(ImeiCommand $command)
    {
        return [
            'command_id' => $command->id,
            'command' => $command->command,
            'status' => $command->status_label,
            'sent_at' => optional($command->sent_at)->toDateTimeString(),
            'executed_at' => optional($command->executed_at)->toDateTimeString(),
            'response_time' => $command->response_time,
            'response' => $command->device_response ? json_decode($command->device_response, true) : null,
        ];
    }
}
