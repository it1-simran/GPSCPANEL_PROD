<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $table = 'sms_logs';

    protected $fillable = ['device_id', 'direction', 'content', 'status', 'provider_ref', 'replied_to_id'];

    public function device()
    {
        return $this->belongsTo(SmsDevice::class, 'device_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(SmsLog::class, 'replied_to_id');
    }

    public function replies()
    {
        return $this->hasMany(SmsLog::class, 'replied_to_id');
    }
}
