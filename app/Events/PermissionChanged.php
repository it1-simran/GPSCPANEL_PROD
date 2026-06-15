<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PermissionChanged Event
 *
 * Fired whenever a user's permissions are changed (granted, revoked, or updated).
 * This event is used to log permission changes to the permission_changes table
 * for audit trail and real-time validation purposes.
 */
class PermissionChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User ID whose permissions were changed
     */
    public int $userId;

    /**
     * Type of change: 'granted', 'revoked', 'updated', 'bulk_updated'
     */
    public string $changeType;

    /**
     * Permission key that was changed (or description for bulk changes)
     */
    public ?string $permissionKey;

    /**
     * User ID of the admin who made the change
     */
    public int $changedBy;

    /**
     * Additional details about the change
     */
    public ?array $details;

    /**
     * Create a new event instance.
     *
     * @param int $userId User whose permissions were changed
     * @param string $changeType Type of change
     * @param string|null $permissionKey Permission key or change description
     * @param int $changedBy Admin who made the change
     * @param array|null $details Additional details
     */
    public function __construct(
        int $userId,
        string $changeType,
        ?string $permissionKey = null,
        int $changedBy = null,
        ?array $details = null
    ) {
        $this->userId = $userId;
        $this->changeType = $changeType;
        $this->permissionKey = $permissionKey;
        $this->changedBy = $changedBy ?? auth()->id();
        $this->details = $details;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
