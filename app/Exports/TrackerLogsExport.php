<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class TrackerLogsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;
    protected $device;
    protected $count;
    protected $serialNumber = 1;

    public function __construct($query, $device, $count)
    {
        $this->query = $query;
        $this->device = $device;
        $this->count = $count;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        $exportedOn = \App\Helper\CommonHelper::getDateAsTimeZone(now(), 'd-M-Y H:i:s');
        return [
            ['# TRACKER SYSTEM LOG EXPORT'],
            ['# Device IMEI:', optional($this->device)->imei],
            ['# Exported On:', $exportedOn],
            ['# Status Details:', "You have successfully downloaded {$this->count} logs."],
            ['Sr.No', 'ID', 'IMEI', 'Logged At', 'Source IP', 'Raw Packet']
        ];
    }

    public function map($log): array
    {
        return [
            $this->serialNumber++,
            $log->id,
            optional($this->device)->imei,
            $log->logged_at ? \App\Helper\CommonHelper::getDateAsTimeZone($log->logged_at, 'Y-m-d H:i:s') : null,
            $log->source_ip,
            $log->raw_packet,
        ];
    }
}
