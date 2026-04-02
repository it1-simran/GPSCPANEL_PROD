<?php

namespace App\Events;

use App\Models\ImeiLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImeiLogReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ImeiLog $log)
    {
        $this->log = $log;
        
        // Ensure device is loaded for the channel name and logging
        if (!$this->log->relationLoaded('device')) {
            $this->log->load('device');
        }

        Log::info("ImeiLogReceived instantiated for IMEI: " . ($this->log->device->imei ?? 'UNKNOWN'));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Load device if not already loaded to get IMEI string
        if (!$this->log->relationLoaded('device')) {
            $this->log->load('device');
        }

        return new Channel('tracker.' . $this->log->device->imei);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'ImeiLogReceived';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'log' => [
                'id' => $this->log->id,
                'imei' => $this->log->device->imei ?? null,
                'logged_at' => $this->log->logged_at->toDateTimeString(),
                'source_ip' => $this->log->source_ip,
                'raw_packet' => $this->log->raw_packet,
            ]
        ];
    }
}
