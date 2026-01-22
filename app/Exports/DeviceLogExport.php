<?php

namespace App\Exports;

use App\Devicelog;
use App\Helper\CommonHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeviceLogExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $devicelog = Devicelog::get();

        // Format the data
        $data = $devicelog->map(function($log) {
            return [
                'ID' => $log->id,
                'Device' => $log->device_id,
                'User' => CommonHelper::getUserName($log->user_id),
                'Logs' => $log->log,
                'Action' => $log->action,
                'Created at' => $log->created_at,
            ];
        });

        return $data;
    }
    public function headings(): array
    {
        return [
            'ID',
            'Device',
            'User',
            'Logs',
            'Action',
            'Created at',
        ];
    }

}
