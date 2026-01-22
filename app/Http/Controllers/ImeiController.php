<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ImeiModel;
use App\Imports\ImeiImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImeiController extends Controller
{
    public function viewImei(Request $request)
    {
        $imeis = ImeiModel::get();
        $url_type = self::getURLType();
        return view('view_uploaded_imei', ['imeis' => $imeis, 'url_type' => $url_type]);
    }
    public function uploadImei(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);
        $file = $request->file('csv_file');
        $rows = Excel::toArray(new ImeiImport, $file);
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {
                $checkImeiExist =  ImeiModel::where(['imei' => $value[0]])->first();
                if (!$checkImeiExist) {
                    $Imei = [
                        'imei' => $value[0  ],
                    ];
                    $savedImei =  ImeiModel::create($Imei);
                    $ImeiID = $savedImei->id;
                } else {
                    $ImeiID  = $checkImeiExist->id;
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file imported successfully.');
    }
}
