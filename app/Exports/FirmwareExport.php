<?php

namespace App\Exports;

use App\Firmware;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FirmwareExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $firmwares = Firmware::get();

        // Format the data
        $data = $firmwares->map(function($firmware) {
            return [
                'ID' => $firmware->id,
                'Name' => $firmware->name,
                'Device Category ID' => $firmware->device_category_id,
                'Backend ID' => $firmware->backend_id,
                'Configurations' => $firmware->configurations,
                'Created At' => $firmware->created_at,
                'Updated At' => $firmware->updated_at,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Device Category ID',
            'Backend ID',
            'Configurations',
            'Created At',
            'Updated At',
        ];
    }

}
