<?php

namespace App\Exports;

use App\backend;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BackendExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $backends = backend::get();

        // Format the data
        $data = $backends->map(function($backend) {
            return [
                'ID' => $backend->id,
                'Name' => $backend->name,
                'Created At' => $backend->created_at,
                'Updated At' => $backend->updated_at,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Created At',
            'Updated At',
        ];
    }

}
