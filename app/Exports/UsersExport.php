<?php

namespace App\Exports;

use App\Template;
use App\Helper\CommonHelper;
use App\Writer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $templates = Template::where('is_deleted', 0)->get();

        // Format the data
        $data = $templates->map(function($template) {
            return [
                'ID' => $template->id,
                'Template Name' => $template->template_name,
                'Device Category' =>  CommonHelper::getDeviceCategoryName($template->device_category_id), // Add other fields as needed
                'Created at' => $template->created_at->format('Y-m-d H:i:s'),
                'Last Edit' => $template->updated_at->format('Y-m-d H:i:s'),
                'Default Template' => $template->default_template == 1 ? 'yes':'no',
                'Configurations' => $template->configurations
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Template Name',
            'Device Category',
            'Created at',
            'Last Edit',
            'Default Template',
            'Configurations'
            // Add more headings as needed
        ];
    }

}
