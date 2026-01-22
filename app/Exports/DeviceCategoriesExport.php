<?php

namespace App\Exports;

use App\DeviceCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeviceCategoriesExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $deviceCategory = DeviceCategory::where('is_deleted', 0)->get();

        // Format the data
        $data = $deviceCategory->map(function($category) {
            return [
                'ID' => $category->id,
                'Device Category Name' => $category->device_category_name,
                'Created at' => $category->created_at,
                'Last Edit' => $category->updated_at,
                'Configurations Input' => $category->inputs,

            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Device Category Name',
            'Created at',
            'Last Edit',
            'Configurations Input',

            // Add more headings as needed
        ];
    }

}
