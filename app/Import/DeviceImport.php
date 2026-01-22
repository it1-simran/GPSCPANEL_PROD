<?php

namespace App\Import;
use App\Device;
use Maatwebsite\Excel\Concerns\ToModel;


class DeviceImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
  
    public function model(array $row)
    {
        return new Device([
            'name'=>$row['name'],
            'imei' => $row['imei']
            // 'device_model' => $row['device_model'],            
            // 'ip' => $row['ip'],     
            // 'port' => $row['port'],
            // 'logs_interval' => $row['logs_interval'] ,
            // 'sleep_interval' => $row['sleep_interval'],
            // 'trans_interval' => $row['trans_interval'],
            // 'fota' => $row['fota'],
            // 'is_editable' => $row['is_editable'],
            // 'ping_interval' => $row['ping_interval'],
            // 'active_status' => $row['active_status']
        ]);
    }
}
