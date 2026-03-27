<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImeiDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'imei',
        'status',
        'schedule_start',
        'schedule_end',
    ];

    protected $casts = [
        'schedule_start' => 'datetime',
        'schedule_end' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(ImeiLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithinSchedule($query)
    {
        return $query->where(function ($q) {
            $now = now();
            $q->whereNull('schedule_start')->whereNull('schedule_end')
              ->orWhere(function ($sub) use ($now) {
                  $sub->whereNotNull('schedule_start')->whereNotNull('schedule_end')
                      ->where('schedule_start', '<=', $now)
                      ->where('schedule_end', '>=', $now);
              })
              ->orWhere(function ($sub) use ($now) {
                  $sub->whereNotNull('schedule_start')->whereNull('schedule_end')
                      ->where('schedule_start', '<=', $now);
              })
              ->orWhere(function ($sub) use ($now) {
                  $sub->whereNull('schedule_start')->whereNotNull('schedule_end')
                      ->where('schedule_end', '>=', $now);
              });
        });
    }
}
