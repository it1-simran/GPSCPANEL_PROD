<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsDevice extends Model
{
    protected $table = 'sms_devices';
    
    protected $fillable = ['name', 'imei', 'phone_number', 'is_active', 'metadata', 'last_seen_at'];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(SmsLog::class, 'device_id');
    }
}
