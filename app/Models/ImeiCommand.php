<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImeiCommand extends Model
{
    use HasFactory;

    // Command Status Constants
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_EXECUTED = 2;
    const STATUS_FAILED = 3;

    protected $fillable = [
        'imei_id',
        'command',
        'status',
        'sent_at',
        'executed_at',
        'device_response',
        'response_time',
    ];

    protected $casts = [
        'status' => 'integer',
        'sent_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_EXECUTED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            default => 'Unknown',
        };
    }

    /**
     * Check if command is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if command is executed
     */
    public function isExecuted()
    {
        return $this->status === self::STATUS_EXECUTED;
    }

    /**
     * Mark command as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
        return $this;
    }

    /**
     * Mark command as executed
     */
    public function markAsExecuted($response = null, $responseTime = null)
    {
        $this->update([
            'status' => self::STATUS_EXECUTED,
            'executed_at' => now(),
            'device_response' => $response,
            'response_time' => $responseTime,
        ]);
        return $this;
    }

    /**
     * Mark command as failed
     */
    public function markAsFailed($response = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'device_response' => $response,
        ]);
        return $this;
    }

    public function device()
    {
        return $this->belongsTo(ImeiDevice::class, 'imei_id');
    }
}
