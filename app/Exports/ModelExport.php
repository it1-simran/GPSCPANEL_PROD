<?php

namespace App\Exports;

use App\modal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ModelExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $models = modal::get();

        // Format the data
        $data = $models->map(function($model) {
            return [
                'ID' => $model->id,
                'Name' => $model->name,
                'User Id' => $model->user_id,
                'Firmware Id' => $model->firmware_id,
                'Created at' => $model->created_at,
                'Updated at' => $model->updated_at,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'User Id',
            'Firmware Id',
            'Created at',
            'Updated at',
        ];
    }

}
