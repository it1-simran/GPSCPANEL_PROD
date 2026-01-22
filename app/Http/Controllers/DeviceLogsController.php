<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DeviceLogsController extends Controller
{
    public function index($id)
    {
        $deviceLogs = DB::table('device_logs')
        ->where("device_id", "=", $id)
        ->get();
        return view('view_device_logs', ['deviceLogs'=>$deviceLogs]);
    }
}