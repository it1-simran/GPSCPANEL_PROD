<?php

namespace App\Exports;

use App\esim;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EsimExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $esims = esim::get();

        // Format the data
        $data = $esims->map(function($esim) {
            return [
                'ID' => $esim->id,
                'Name' => $esim->name,
                'Profile 1' => $esim->profile_1,
                'Profile 2' => $esim->profile_2,
                'Created at' => $esim->created_at,
                'Updated at' => $esim->updated_at,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Profile 1',
            'Profile 2',
            'Created at',
            'Updated at',
        ];
    }

}
