<?php

namespace App\Exports;

use App\Device;
use App\Helper\CommonHelper;
use App\Services\DeviceCategoryAccessService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DevicesExports implements FromCollection, WithHeadings
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Device::where('is_deleted', 0);
        app(DeviceCategoryAccessService::class)->applyCategoryScopeToQuery($query, $this->user);

        $Devices = $query->get();

        $data = $Devices->map(function ($device) {
            $config = json_decode($device->configurations, true);
            return [
                'ID' => $device->id,
                'User Name' => $device->name,
                'Name' => CommonHelper::getDeviceCategoryName($device->device_category_id),
                'IMEI' => $device->imei,
                'Total Pings' => $device->total_pings,
                'Ping Interval' => (isset($config['ping_interval']) ? $config['ping_interval'] : ""),
                'Added On' => $device->created_at,
                'Last Edit' => $device->updated_at,
                'Editable' => $device->is_editable == '1' ? 'yes' : 'no',
                'Configurations' => $device->configurations
            ];
        });

        return $data;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User Name',
            'Name',
            'IMEI',
            'Total Pings',
            'Ping Interval',
            'Added On',
            'Last Edit',
            'Editable',
            'Configurations'
        ];
    }
}
