<?php

namespace App\Exports;

use App\Models\ImeiModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ImeiListExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ImeiModel::query()->orderBy('id')->get()->values()->map(function ($row, $index) {
            return [
                'Sr. No.' => $index + 1,
                'IMEI' => " " . $row->imei,
                'Created At' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '',
                'Last Edit' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sr. No.',
            'IMEI',
            'Created At',
            'Last Edit',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
