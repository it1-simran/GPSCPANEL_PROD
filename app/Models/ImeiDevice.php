<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class ImeiDevice extends Model
{
    use HasFactory;

    public const STATUS_ON = 'active';
    public const STATUS_OFF = 'inactive';
    public const STATUS_CLOSE = 'close';

    protected $fillable = [
        'imei',
        'status',
        'schedule_start',
        'schedule_end',
        'start_at',
        'end_at',
        'last_log_id',
    ];

    protected $casts = [
        'schedule_start' => 'datetime',
        'schedule_end' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'last_log_id' => 'integer',
    ];

    protected static array $columnCache = [];

    public function logs()
    {
        return $this->hasMany(ImeiLog::class);
    }

    public function commands()
    {
        return $this->hasMany(ImeiCommand::class, 'imei_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ON);
    }

    public function scopeWithinSchedule($query)
    {
        $now = now();
        $startColumn = static::hasTrackerColumn('start_at') ? 'start_at' : 'schedule_start';
        $endColumn = static::hasTrackerColumn('end_at') ? 'end_at' : 'schedule_end';

        return $query->where(function ($q) use ($now, $startColumn, $endColumn) {
            $q->where(function ($sub) use ($now, $startColumn) {
                $sub->whereNull($startColumn)->orWhere($startColumn, '<=', $now);
            })->where(function ($sub) use ($now, $endColumn) {
                $sub->whereNull($endColumn)->orWhere($endColumn, '>=', $now);
            });
        });
    }

    public function getEffectiveStartAtAttribute(): ?Carbon
    {
        $startAt = static::hasTrackerColumn('start_at') ? $this->getAttribute('start_at') : null;

        return $startAt ?: $this->schedule_start;
    }

    public function getEffectiveEndAtAttribute(): ?Carbon
    {
        $endAt = static::hasTrackerColumn('end_at') ? $this->getAttribute('end_at') : null;

        return $endAt ?: $this->schedule_end;
    }

    public function isRecordingOn(): bool
    {
        return $this->status === self::STATUS_ON;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSE;
    }

    public function isWithinRecordingWindow(?Carbon $at = null): bool
    {
        $at = $at ?: now();
        $start = $this->effective_start_at;
        $end = $this->effective_end_at;

        if ($start && $at->lt($start)) {
            return false;
        }

        if ($end && $at->gt($end)) {
            return false;
        }

        return true;
    }

    public function recordingHasExpired(?Carbon $at = null): bool
    {
        $at = $at ?: now();
        $end = $this->effective_end_at;

        return $end ? $at->gt($end) : false;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === self::STATUS_ON ? 'ON' : ($this->status === self::STATUS_OFF ? 'OFF' : 'CLOSE');
    }

    public static function hasTrackerColumn(string $column): bool
    {
        if (!array_key_exists($column, static::$columnCache)) {
            static::$columnCache[$column] = Schema::hasColumn((new static())->getTable(), $column);
        }

        return static::$columnCache[$column];
    }
}
