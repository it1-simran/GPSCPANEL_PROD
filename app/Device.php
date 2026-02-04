<?php

namespace App;

use App\Models\BaseModel;
use Illuminate\Support\Str;
class Device extends BaseModel
{
    public function generateToken()
    {
        $this->api_token = Str::random(60);
        $this->save();
        return $this->api_token;
    }
  protected $fillable = [
        'user_id', 'master_id', 'assign_to_ids', 'name', 'device_category_id', 'configurations', 'can_configurations', 'errors', 'imei', 'ip', 'port', 'logs_interval', 'password', 'sleep_interval', 'trans_interval', 'fota', 'is_editable', 'ping_interval', 'active_status', 'deviceStatus', 'certificate_vltd_serial_no', 'certificate_vltd_icc_id', 'certificate_vehicle_registration_no', 'certificate_chassis_no', 'certificate_engine_no'
    ];
   protected $hidden = [
        'firmware_version',
    ];



}

