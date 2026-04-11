<?php

namespace App\Console\Commands;

use App\Models\ImeiCommand;
use App\Models\ImeiDevice;
use App\Services\CommandExecutionService;
use Illuminate\Console\Command;

class ProcessQueuedCommands extends Command
{
    protected $signature = 'commands:process {--device-imei= : Process commands for specific IMEI only}';
    protected $description = 'Process and execute queued commands for all or specific devices';

    public function __construct(protected CommandExecutionService $executionService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting command processing...');

        // Get devices
        $query = ImeiDevice::query();

        if ($this->option('device-imei')) {
            $query->where('imei', $this->option('device-imei'));
        }

        $devices = $query->with('commands')->get();

        if ($devices->isEmpty()) {
            $this->warn('No devices found.');
            return;
        }

        $totalProcessed = 0;
        $totalExecuted = 0;
        $totalFailed = 0;

        foreach ($devices as $device) {
            // Get pending commands
            $pendingCommands = $device->commands()
                ->where('status', ImeiCommand::STATUS_PENDING)
                ->get();

            if ($pendingCommands->isEmpty()) {
                continue;
            }

            $this->info("\nProcessing device: {$device->imei} ({$pendingCommands->count()} pending commands)");

            foreach ($pendingCommands as $command) {
                $result = $this->executionService->executeCommand($command, $device);

                if ($result['success']) {
                    $this->line("  ✓ {$result['command']} - {$result['status']} ({$result['response_time']}ms)");
                    $totalExecuted++;
                } else {
                    $this->error("  ✗ {$result['command']} - {$result['status']}: {$result['message']}");
                    $totalFailed++;
                }

                $totalProcessed++;
            }
        }

        $this->info("\n" . str_repeat('═', 60));
        $this->info("SUMMARY");
        $this->info(str_repeat('═', 60));
        $this->line("Total Processed: {$totalProcessed}");
        $this->line("✓ Executed: {$totalExecuted}");
        $this->error("✗ Failed: {$totalFailed}");
        $this->info(str_repeat('═', 60));
    }
}
