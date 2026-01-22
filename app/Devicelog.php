<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class DeviceLog extends Model
{

    protected $fillable = [
        'device_id', 'user_id','log','action','is_active', 'created_at','updated_at'
    ];
}