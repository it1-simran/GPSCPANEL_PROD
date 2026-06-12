<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DeviceLogsController extends Controller
{
    public function index($id)
    {
        if ($denied = $this->authorizeDeviceCategoryAccess($id)) {
            return $denied;
        }

        $deviceLogs = DB::table('device_logs')
        ->where("device_id", "=", $id)
        ->get();
        return view('view_device_logs', ['deviceLogs'=>$deviceLogs]);
    }
}