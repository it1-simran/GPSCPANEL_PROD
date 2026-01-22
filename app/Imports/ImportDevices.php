<?php

namespace App\Imports;

use App\Device;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportDevices implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Device([
            'name'    => @$row[1],
            'imei'    => @$row[2]

            

            
        ]);
    }
}
