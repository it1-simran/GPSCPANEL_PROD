<?php

namespace App\Imports;

use App\Entry;
use Maatwebsite\Excel\Concerns\ToModel;

class EntriesImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        
        return new Entry([
            'ccid' => $row['ccid'],
            'esim_make' => $row['esim_make'],
            'profile_1' => $row['profile_1'],
            'profile_2' => $row['profile_2'],
            'customer_name' => $row['customer_name'],
        ]);
    }
}
