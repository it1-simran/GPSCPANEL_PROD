<?php

namespace App\Exports;

use App\ccid;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EsimMasterExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $esimMasters = ccid::get();

        // Format the data
        $data = $esimMasters->map(function($esimmaster) {
            return [
                'ID' => $esimmaster->id,
                'CCID' => $esimmaster->ccid,
                'Customer Name' => $esimmaster->customer_name,
                'EsimID' => $esimmaster->esim,
                'Created At' => $esimmaster->created_at,
                'Updated At' => $esimmaster->updated_at,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'CCID',
            'Customer Name',
            'EsimID',
            'Configurations Input',
            'Created At',
            'Updated At',
            // Add more headings as needed
        ];
    }

}
