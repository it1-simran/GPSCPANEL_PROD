<?php

namespace App\Imports;

use App\Entry;
use Maatwebsite\Excel\Concerns\ToModel;

class ImeiImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        
        return new Entry([
            'imei' => $row['imei'],
        ]);
    }
}
