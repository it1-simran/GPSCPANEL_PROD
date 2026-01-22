<?php

namespace App\Exports;

use App\Helper\CommonHelper;
use App\Writer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WritersExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $writers = Writer::where('is_deleted', 0)->get();

        // Format the data
        $data = $writers->map(function($writer) {
            return [
                'ID' => $writer->id,
                'Account Type' => $writer->user_type,
                'Name' => $writer->name, // Add other fields as needed
                'Mobile' => $writer->mobile,
                'Email' => $writer->email,
                'Login Password' => $writer->showLoginPassword,
                'Total Devices' => $writer->total_devices,
                'Total Pings' => $writer->total_pings,
                'Today Pings' => $writer->today_pings,
                'Default Configurations' => $writer->configurations,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Account Type',
            'Name',
            'Mobile',
            'Email',
            'Login Password',
            'Total Devices',
            'Total Pings',
            'Today Pings',
            'Default Configurations',

            // Add more headings as needed
        ];
    }

}
