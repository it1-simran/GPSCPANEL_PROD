<?php

namespace App\Console\Commands;

use App\Models\ImeiLog;
use Illuminate\Console\Command;

class PurgeOldImeiLogs extends Command
{
    protected $signature = 'imei-logs:purge {--days=30 : Delete logs older than the given number of days}';

    protected $description = 'Delete IMEI tracker logs older than the retention period.';

    public function handle()
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $deleted = ImeiLog::where('logged_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} IMEI logs older than {$days} days.");

        return 0;
    }
}
