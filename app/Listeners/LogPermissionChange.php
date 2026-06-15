<?php

namespace App\Listeners;

use App\Events\PermissionChanged;
use App\Services\RealtimePermissionValidator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Log Permission Change Listener
 *
 * Listens for PermissionChanged events and logs them to the permission_changes table
 * for audit trail and real-time validation purposes.
 */
class LogPermissionChange implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying a failed job.
     *
     * @var int
     */
    public $backoff = 5;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PermissionChanged  $event
     * @return void
     */
    public function handle(PermissionChanged $event)
    {
        try {
            // Log to permission_changes table
            DB::table('permission_changes')->insert([
                'user_id' => $event->userId,
                'permission_id' => null,
                'permission_key' => $event->permissionKey,
                'change_type' => $event->changeType,
                'changed_by' => $event->changedBy,
                'changed_at' => now(),
            ]);

            // Log to application log
            Log::info('Permission changed', [
                'user_id' => $event->userId,
                'change_type' => $event->changeType,
                'permission_key' => $event->permissionKey,
                'changed_by' => $event->changedBy,
                'details' => $event->details,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log permission change', [
                'error' => $e->getMessage(),
                'user_id' => $event->userId,
                'change_type' => $event->changeType,
            ]);

            // Re-throw to trigger queue retry
            throw $e;
        }
    }
}
