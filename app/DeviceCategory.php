<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceCategory extends Model
{
    //

    protected $fillable = ['device_category_name','inputs','parameters','is_esim','is_can_protocol'];

}
