<?php

namespace App\Http\Controllers;

use App\User;
use App\Admin;
use App\Writer;
use App\Device;
use App\Template;
use App\Firmware;
use App\Modal;
use App\DeviceCategory;
use App\Helper\CommonHelper;
use App\DataFields;
use App\Devicelog;
use DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Import\DeviceImport;
use Carbon\Carbon;
use PDF;
use GuzzleHttp\Client;


class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:12',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
    }
    public function index()
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where("user_type", "!=", "Admin")
            ->where('writers.is_deleted', '0')
            ->where('writers.created_by', Auth::user()->id)
            ->get();
        $default_template = DB::table('templates')
            ->select('templates.*')
            ->where('templates.default_template', '1')
            ->first();
        return view('add_device', ['users' => $users, 'default_template' => $default_template]);
    }


    public function viewDeviceCategoryFields(Request $request)
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where("user_type", "!=", "Admin")
            ->where('writers.is_deleted', '0')
            ->where('writers.created_by', Auth::user()->id)
            ->get();
        $dataFields = DB::table('data_fields')->select('*')->get();
        $url_type = self::getURLType();
        return view('view_device_category_field', ['users' => $users, 'url_type' => $url_type, 'dataFields' => $dataFields]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'imei' => "unique:devices|min:15|max:15",
            'firmware' => 'required',
        ]);
        $config = $request->configuration;
        $converted = [];
        $canConverted = !empty($request->canConfigurationArr) ? json_decode($request->canConfigurationArr, true) : [];
        $commonFields = DB::table("data_fields")->where(["is_common" => 1])->get();
        foreach ($commonFields as $index => $value) {
            if (strpos($value->fieldName, ' ') !== false) {
                $key = strtolower(str_replace(' ', '_', $value->fieldName));
            } else {
                $key = lcfirst(str_replace(' ', '_', $value->fieldName));
            }
            $converted[$key] = [
                'id' => $value->id,
                'value' => $config[$key] ?? ''
            ];
        }
        if (isset($request->user_id) && $request->user_id != '' && $request->user_id != 'No User Found') {
            $user = DB::table('writers')->where(['id' => $request->user_id])->first();
            $configuration = json_decode($user->configurations);
            $deviceCategoryId = explode(',', $user->device_category_id);
            $selectedConfig = [];
            foreach ($deviceCategoryId as $key => $id) {
                if ($id == $request->deviceCategory) {
                    $selectedConfig = $configuration[$key];
                }
            }
            $mergedConfig = array_merge($converted, (array) $selectedConfig);
            $converted = $mergedConfig;
        } else {
            $idParameters = $request->idParameters;
            foreach ($idParameters as $key => $id) {
                if (isset($config[$key])) {
                    $converted[$key] = [
                        'id' => intval($id),
                        'value' => $config[$key] ?? ''
                    ];
                }
            }
        }
        $firmware = Firmware::select('configurations')->where(['id' => $request->firmware])->first();
        $device_array =  $converted;
        $fimwareArr = json_decode($firmware->configurations, true);
        $device_array['firmware_id']['value'] =  $request->firmware;
        $device_array['firmware_file']['value'] = $fimwareArr['filename'];
        $device_array['firmware_version']['value'] = $fimwareArr['version'];
        $device_array['firmwareFileSize']['value'] = $fimwareArr['fileSize'];
        $master_id = Auth::user()->id;
        $mid = null;
        $assign_to_ids = '';
        if ($request->user_id) {
            $mid = $master_id;
            $assign_to_ids = $master_id;
        }
        $data = Device::create([
            'master_id' => $mid,
            'user_id' => $request->user_id,
            'assign_to_ids' => $assign_to_ids,
            'name' => $request->name,
            'imei' => $request->imei,
            'device_category_id' => $request->deviceCategory,
            'configurations' => json_encode($device_array),
            'can_configurations' => json_encode($canConverted)
        ]);
        $log = Devicelog::create([
            'device_id' => $data->id,
            'user_id' => $master_id,
            'log' => 'Device with imei no ' . $request->imei . ' Created Successfully',
            'action' => 'Created',
            'is_active' => 1
        ]);
        return json_encode(['status' => 200, 'status_message' => $request->imei . '- Device Added Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    // public function show(Device $device, Request $request)
    // {

    //     $user = Auth::user();

    //             $devices = DB::table('devices')

    //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')

    //         ->select('devices.*', 'writers.name as username')

    //         ->where('devices.is_deleted', '0')

    //         ->where('devices.user_id', $user->id)
    //         ->orwhereRaw('FIND_IN_SET(' . $user->id . ',devices.assign_to_ids)')
    //         ->get();

    //     // $devicesQuery = DB::table('devices')
    //     //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     //     ->select('devices.*', 'writers.name as username')
    //     //     ->where('devices.is_deleted', '0');

    //     // if ($user->user_type == 'Admin') {
    //     //     $devicesQuery->where(function ($query) {
    //     //         $query->whereNull('devices.user_id')->orWhere('devices.user_id', 0);
    //     //     });
    //     // } else {
    //     //     $devicesQuery->where('devices.user_id', $user->id);
    //     // }

    //     // $devices = $devicesQuery->get();
    //     // if (Auth::user()->user_type == 'Admin') {
    //     //     $user_id = NULL ?? 0;
    //     // } else {
    //     //     $user_id = Auth::user()->id;
    //     // }
    //     // $devices = DB::table('devices')
    //     //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     //     ->select('devices.*', 'writers.name as username')
    //     //     ->where('devices.is_deleted', '0')
    //     //     ->where('devices.user_id', $user_id)
    //     //     ->get();

    //     if (Auth::user()->user_type == 'Reseller') {
    //         //$users = DB::table('writers')->select('id','name')->where('writers.created_by',Auth::user()->id)->where('writers.is_deleted','0')->get();
    //     } else {
    //         //$users = DB::table('writers')->select('id','name')->where('writers.is_deleted','0')->get();
    //     }
    //     $users = DB::table('writers')->select('id', 'name')->where('writers.created_by', Auth::user()->id)->where('writers.is_deleted', '0')->get();
    //     // dd($users);
    //     if (Auth::user()->user_type == 'Reseller') {
    //         $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
    //     } else {
    //         $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
    //     }
    //     foreach ($devices as $dkey => $device) {
    //         $devices[$dkey]->username = '';
    //     }
    //     $url_type = self::getURLType();
    //     return view('view_device', ['users' => $users, 'device' => $devices, 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => false]);
    // }
    public function show(Device $device, Request $request)
    {

        if (Auth::user()->user_type == 'Admin') {
            $user_id = NULL;
        } else {
            $user_id = Auth::user()->id;
        }

        $user = Auth::user();

        $devicesQuery = DB::table('devices')
            ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
            ->select('devices.*', 'writers.name as username')
            ->where('devices.is_deleted', '0');

        if ($user->user_type == 'Admin') {
            $devicesQuery->where('devices.user_id', '!=', null);
        }
        if ($user->user_type != 'Admin') {
            $devicesQuery->where(function ($query) use ($user) {
                $query->where('devices.user_id', '!=', $user->id)
                    ->where(function ($q) use ($user) {
                        $q->whereIn('devices.user_id', function ($subquery) use ($user) {
                            $subquery->select('id')
                                ->from('writers')
                                ->where('created_by', $user->id)
                                ->where('is_deleted', '0');
                        })
                            ->orWhereNull('devices.user_id');
                    });
            });
        }

        $devices = $devicesQuery->get();
        $childUsersQuery = DB::table('writers')->select('id', 'name')->where('is_deleted', '0');
        // dd(Auth::user()->id);
        // Only direct children for resellers
        $childUsersQuery->where('created_by', Auth::user()->id);

        $childUsers = $childUsersQuery->pluck('name', 'id'); // [id => name]

        // Set username based on child user match
        foreach ($devices as $dkey => $device) {
            $userId = $device->user_id;
            $aids = explode(',', $device->assign_to_ids ?? '');
            $next_id = null;

            // Get next assigned user ID relative to current user
            if (!empty($aids)) {
                $next_id = self::getNextValue($aids, Auth::user()->id);
            }

            // Direct assignment
            if ($userId == Auth::user()->id || empty($userId)) {
                $devices[$dkey]->username = 'Unassigned';
            }
            // If next direct child exists in list and is a valid writer
            elseif ($next_id) {
                $w_details = DB::table('writers')->where('id', $next_id)->first();
                if ($w_details) {
                    $devices[$dkey]->username = $w_details->name;
                } else {
                    $devices[$dkey]->username = 'error_' . $device->id . '_' . $next_id;
                }
            }
            // Fallback (e.g., joined username)
            else {
                $devices[$dkey]->username = $device->username ?? 'Unassigned';
            }
        }


        // dd($devices);

        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('created_by', Auth::id())
            ->where('is_deleted', 0)
            ->where('user_type', '!=', 'Support')
            ->get();


        // dd($users);

        if (Auth::user()->user_type == 'Reseller') {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
        } else {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
        }

        // foreach ($devices as $dkey => $device) {
        //     $devices[$dkey]->username = '';
        // }

        $url_type = self::getURLType();


        return view('view_device', ['users' => $users, 'device' => $devices, 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => false]);
    }

    public function generateCertificate($id, Request $request)
    {
        $request->validate([
            'holder_name' => 'required|string|max:255',
            'authority_city' => 'required|string|max:255',
            'fitment_date' => 'required|date',
            'vehicle_registration_no' => 'required|string|max:255',
            'vltd_serial_no' => 'required|string|max:255',
            'vltd_make' => 'required|string|max:255',
            'vltd_model' => 'required|string|max:255',
            'chassis_no' => 'required|string|max:255',
            'engine_no' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'arai_tac' => 'nullable|string|max:255',
            'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
        ]);
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }
        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $config = json_decode($device->configurations, true);
        $iccId = '';
        if (is_array($config)) {
            $iccId = $config['ccid']['value'] ?? ($config['iccid']['value'] ?? '');
        }
        $provider = $request->service_provider ?? null;
        if (!$provider && isset($request->service_providers)) {
            if (is_array($request->service_providers)) {
                $provider = $request->service_providers[0] ?? null;
            } else {
                $provider = $request->service_providers;
            }
        }
        $araiTac = $isCertificationEnabled && !empty($deviceCategory->arai_tac_no) ? $deviceCategory->arai_tac_no : ($request->arai_tac ?? 'AS9076');
        $araiDateRaw = $isCertificationEnabled && !empty($deviceCategory->arai_date) ? $deviceCategory->arai_date : ($request->arai_date ?? '08-12-2025');
        $araiDate = Carbon::parse($araiDateRaw)->format('d-m-Y');
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $request->vltd_model;
        $data = [
            'holder_name' => $request->holder_name,
            'authority_city' => $request->authority_city,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'service_provider' => $provider,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
        ];
        $pdfLink = url('/AS9076.pdf');
        $qrText = $pdfLink;
        $client = new Client();
        $qrImageDataUri = null;
        try {
            $resp = $client->get('https://api.qrserver.com/v1/create-qr-code/', [
                'query' => [
                    'size' => '150x150',
                    'data' => $qrText
                ],
                'http_errors' => false,
                'timeout' => 10
            ]);
            if ($resp->getStatusCode() === 200) {
                $body = $resp->getBody()->getContents();
                $qrImageDataUri = 'data:image/png;base64,' . base64_encode($body);
            }
        } catch (\Throwable $e) {
            $qrImageDataUri = null;
        }
        $data['qr_image'] = $qrImageDataUri;
        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->download('certificate_' . $device->imei . '.pdf');
    }

    public function previewCertificate($id, Request $request)
    {
        $request->validate([
            'holder_name' => 'required|string|max:255',
            'authority_city' => 'required|string|max:255',
            'fitment_date' => 'required|date',
            'vehicle_registration_no' => 'required|string|max:255',
            'vltd_serial_no' => 'required|string|max:255',
            'vltd_make' => 'required|string|max:255',
            'vltd_model' => 'required|string|max:255',
            'chassis_no' => 'required|string|max:255',
            'engine_no' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'arai_tac' => 'nullable|string|max:255',
            'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
        ]);
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }
        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $config = json_decode($device->configurations, true);
        $iccId = '';
        if (is_array($config)) {
            $iccId = $config['ccid']['value'] ?? ($config['iccid']['value'] ?? '');
        }
        $provider = $request->service_provider ?? null;
        if (!$provider && isset($request->service_providers)) {
            if (is_array($request->service_providers)) {
                $provider = $request->service_providers[0] ?? null;
            } else {
                $provider = $request->service_providers;
            }
        }
        $araiTac = $isCertificationEnabled && !empty($deviceCategory->arai_tac_no) ? $deviceCategory->arai_tac_no : ($request->arai_tac ?? 'AS9076');
        $araiDateRaw = $isCertificationEnabled && !empty($deviceCategory->arai_date) ? $deviceCategory->arai_date : ($request->arai_date ?? '08-12-2025');
        $araiDate = Carbon::parse($araiDateRaw)->format('d-m-Y');
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $request->vltd_model;
        $data = [
            'holder_name' => $request->holder_name,
            'authority_city' => $request->authority_city,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'service_provider' => $provider,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
        ];
        $pdfLink = url('/AS9076.pdf');
        $qrText = $pdfLink;
        $client = new Client();
        $qrImageDataUri = null;
        try {
            $resp = $client->get('https://api.qrserver.com/v1/create-qr-code/', [
                'query' => [
                    'size' => '150x150',
                    'data' => $qrText
                ],
                'http_errors' => false,
                'timeout' => 10
            ]);
            if ($resp->getStatusCode() === 200) {
                $body = $resp->getBody()->getContents();
                $qrImageDataUri = 'data:image/png;base64,' . base64_encode($body);
            }
        } catch (\Throwable $e) {
            $qrImageDataUri = null;
        }
        $data['qr_image'] = $qrImageDataUri;
        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->stream('certificate_' . $device->imei . '.pdf');
    }

    public function certificatePage($id, Request $request)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }
        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $categoryName;
        $araiTac = $isCertificationEnabled ? ($deviceCategory->arai_tac_no ?? null) : null;
        $araiDate = $isCertificationEnabled ? ($deviceCategory->arai_date ?? null) : null;
        $deviceConfig = json_decode($device->configurations, true);
        $iccId = '';
        if (is_array($deviceConfig)) {
            $iccId = $deviceConfig['ccid']['value'] ?? ($deviceConfig['iccid']['value'] ?? '');
        }
        $config = json_decode($device->configurations, true) ?: [];
        $saved = $config['certificate_details'] ?? null;
        $editMode = (int) $request->query('edit', 0) === 1;
        return view('certificate_page', [
            'device' => $device,
            'category_name' => $categoryName,
            'vltd_model' => $vltdModel,
            'is_certification_enable' => $isCertificationEnabled,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'saved' => $saved,
            'edit_mode' => $editMode,
        ]);
    }
    public static function uniqueJson(Device $device, string $key, $value): bool
    {
        return !Device::where('id', '!=', $device->id)
            ->where(function ($query) use ($key, $value) {
                $query->whereJsonContains("configurations->certificate_details->$key", $value)
                    ->orWhereJsonContains("configurations->$key", $value);
            })
            ->exists();
    }
    public function saveCertificateDetails($id, Request $request)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }
        $uniqueIgnoreId = $device->id;
        $uniqueFields = [
            'vehicle_registration_no',
            'vltd_serial_no',
            'chassis_no',
            'engine_no',
            'vltd_icc_id',
        ];
        foreach ($uniqueFields as $field) {
            if (!empty($request->$field)) {
                if (!self::uniqueJson($device, $field, $request->$field)) {
                    throw ValidationException::withMessages([
                        $field => "This $field already exists.",
                    ]);
                }
            }
        }
        $request->validate([
            'holder_name' => 'required|string|max:255',
            'authority_city' => 'required|string|max:255',
            'fitment_date' => 'required|date',
            // 'vehicle_registration_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'vehicle_registration_no')->ignore($uniqueIgnoreId)],
            // 'vltd_serial_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'vltd_serial_no')->ignore($uniqueIgnoreId)],
            'vltd_make' => 'required|string|max:255',
            // 'vltd_model' => 'required|string|max:255',
            // 'chassis_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'chassis_no')->ignore($uniqueIgnoreId)],
            // 'engine_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'engine_no')->ignore($uniqueIgnoreId)],
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            //'vltd_icc_id' => ['nullable', 'string', 'max:255', Rule::unique('devices', 'vltd_icc_id')->ignore($uniqueIgnoreId)],
            // 'arai_tac' => 'nullable|string|max:255',
            // 'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
        ]);
        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $categoryName;
        $araiTac = $isCertificationEnabled && !empty($deviceCategory->arai_tac_no) ? $deviceCategory->arai_tac_no : ($request->arai_tac ?? 'AS9076');
        $araiDateRaw = $isCertificationEnabled && !empty($deviceCategory->arai_date) ? $deviceCategory->arai_date : ($request->arai_date ?? '08-12-2025');
        $araiDate = Carbon::parse($araiDateRaw)->format('Y-m-d');
        $serviceProvider = $request->service_provider ?? null;
        if (!$serviceProvider && isset($request->service_providers)) {
            if (is_array($request->service_providers)) {
                $serviceProvider = $request->service_providers[0] ?? null;
            } else {
                $serviceProvider = $request->service_providers;
            }
        }
        $config = json_decode($device->configurations, true) ?: [];
        $config['certificate_details'] = [
            'holder_name' => $request->holder_name,
            'authority_city' => $request->authority_city,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'vltd_icc_id' => $request->vltd_icc_id,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'service_provider' => $serviceProvider,
        ];
        $device->configurations = json_encode($config);
        // $device->certificate_vltd_serial_no = $request->vltd_serial_no;
        // $device->certificate_vltd_icc_id = $request->vltd_icc_id;
        // $device->certificate_vehicle_registration_no = $request->vehicle_registration_no;
        // $device->certificate_chassis_no = $request->chassis_no;
        // $device->certificate_engine_no = $request->engine_no;
        $device->update();
        return redirect('/user/device/' . $device->id . '/certificate/view');
    }

    public function viewCertificate($id)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }
        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $config = json_decode($device->configurations, true) ?: [];
        $details = $config['certificate_details'] ?? null;
        if (!$details) {
            return redirect('/user/device/' . $device->id . '/certificate');
        }
        if (empty($details['service_provider']) && isset($details['service_providers'])) {
            if (is_array($details['service_providers'])) {
                $details['service_provider'] = $details['service_providers'][0] ?? null;
            } else {
                $details['service_provider'] = $details['service_providers'];
            }
        }
        if ($isCertificationEnabled) {
            if (!empty($deviceCategory->certification_model_name)) {
                $details['vltd_model'] = $deviceCategory->certification_model_name;
            } else {
                $details['vltd_model'] = $details['vltd_model'] ?? $categoryName;
            }
            if (!empty($deviceCategory->arai_tac_no)) {
                $details['arai_tac'] = $deviceCategory->arai_tac_no;
            }
            if (!empty($deviceCategory->arai_date)) {
                $details['arai_date'] = Carbon::parse($deviceCategory->arai_date)->format('Y-m-d');
            }
        }
        $iccId = '';
        $conf = json_decode($device->configurations, true);
        if (is_array($conf)) {
            $iccId = $conf['ccid']['value'] ?? ($conf['iccid']['value'] ?? '');
        }
        $finalIccId = !empty($details['vltd_icc_id']) ? $details['vltd_icc_id'] : $iccId;
        $data = array_merge($details, [
            'vltd_icc_id' => $finalIccId,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
        ]);
        if (!empty($data['arai_date'])) {
            $data['arai_date'] = Carbon::parse($data['arai_date'])->format('d-m-Y');
        }
        $pdfLink = url('/AS9076.pdf');
        $qrText = $pdfLink;
        $client = new Client();
        $qrImageDataUri = null;
        try {
            $resp = $client->get('https://api.qrserver.com/v1/create-qr-code/', [
                'query' => [
                    'size' => '150x150',
                    'data' => $qrText
                ],
                'http_errors' => false,
                'timeout' => 10
            ]);
            if ($resp->getStatusCode() === 200) {
                $body = $resp->getBody()->getContents();
                $qrImageDataUri = 'data:image/png;base64,' . base64_encode($body);
            }
        } catch (\Throwable $e) {
            $qrImageDataUri = null;
        }
        $data['qr_image'] = $qrImageDataUri;
        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->stream('certificate_' . $device->imei . '.pdf');
    }

    public function testshowAssign(Device $device, Request $request)
    {
        $master_id = Auth::user()->id;
        self::getDeviceAssignToList(873);
        die();
    }
    // public function showAssign(Device $device, Request $request)
    // {
    //     $master_id = Auth::user()->id;
    //     $devices = DB::table('devices')
    //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     ->select('devices.*', 'writers.name as username')
    //     ->where('devices.is_deleted', '0')
    //     ->where('devices.user_id',$master_id)
    //     ->orwhereRaw('FIND_IN_SET(' . $master_id. ',devices.assign_to_ids)')
    //     ->get();
    //     // $devicesQuery = DB::table('devices')
    //     //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     //     ->select('devices.*', 'writers.name as username')
    //     //     ->where('devices.is_deleted', '0')
    //     //     ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
    //     //     // ->where(function ($q) use ($master_id) {
    //     //     //     $q->where('devices.user_id', $master_id)
    //     //     //       ->orWhereRaw('FIND_IN_SET(?, devices.assign_to_ids)', [$master_id]);
    //     //     // })
    //     //     ->whereNotNull('devices.user_id')
    //     //     ->where('devices.user_id', '!=', 0);

    //     // if (isset($_GET['username']) && $_GET['username'] != '' && $request->username != '0') {
    //     //     $devicesQuery->where('writers.id', $_GET['username']);
    //     // }

    //     // $devices = $devicesQuery->get();


    //     // if (isset($_GET['username']) && $_GET['username'] != '' && $request->username != '0') {

    //     //     $devices = DB::table('devices')

    //     //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')

    //     //         ->select('devices.*', 'writers.name as username')

    //     //         ->where('devices.is_deleted', '0')

    //     //         ->where('devices.master_id', $master_id)
    //     //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
    //     //         ->where('writers.id', $_GET['username'])

    //     //         ->get();
    //     // } else {

    //     //     $devices = DB::table('devices')

    //     //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')

    //     //         ->select('devices.*', 'writers.name as username')

    //     //         ->where('devices.is_deleted', '0')

    //     //         ->where('devices.user_id', $master_id)

    //     //         ->where('devices.user_id', '!=', '')

    //     //         //->orWhere('devices.master_id',$master_id)
    //     //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')

    //     //         ->get();
    //     // }

    //     $users = DB::table('writers')
    //         ->select('id', 'name')
    //         ->where('writers.created_by', Auth::user()->id)
    //         ->where('writers.is_deleted', '0')
    //         ->get();

    //     if (Auth::user()->user_type == 'Reseller') {
    //         $template_info = DB::table('templates')
    //             ->select('templates.*')
    //             ->where('templates.id_user', Auth::user()->id)
    //             ->where('templates.is_deleted', '0')
    //             ->where('verify', '2')
    //             ->get();
    //     } else {
    //         $template_info = DB::table('templates')
    //             ->select('templates.*')
    //             ->where('templates.is_deleted', '0')
    //             ->where('verify', '1')
    //             ->get();
    //     }

    //     if (count($devices) > 0) {
    //         foreach ($devices as $dkey => $device) {
    //             $uname = $device->username;
    //             $aids = explode(',', $device->assign_to_ids);
    //             if (count($aids) > 0) {
    //                 $next_id = self::getNextValue($aids, $master_id);
    //                 if ($next_id) {
    //                     $w_details = DB::table('writers')->where('id', $next_id)->first();
    //                     $uname = $w_details->name ?? 'error_' . $device->id . '_' . $next_id;
    //                 }
    //             }
    //             $devices[$dkey]->username = $uname;
    //         }
    //     }

    //     $url_type = self::getURLType();

    //     return view('view_device', [
    //         'users' => $users,
    //         'device' => $devices,
    //         'template_info' => $template_info,
    //         'url_type' => $url_type,
    //         'show_acc_wise' => true
    //     ]);
    // }

    public function showAssign(Device $device, Request $request)
    {
        // $master_id = Auth::user()->id;
        // if (isset($_GET['username']) && $_GET['username'] != '' && $request->username != '0') {
        //     $devices = DB::table('devices')
        //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
        //         ->select('devices.*', 'writers.name as username')
        //         ->where('devices.is_deleted', '0')
        //         ->where('devices.master_id', $master_id)
        //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
        //         ->where('writers.id', $_GET['username'])
        //         ->get();
        // } else {
        //     $devices = DB::table('devices')
        //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
        //         ->select('devices.*', 'writers.name as username')
        //         ->where('devices.is_deleted', '0')
        //         ->where('devices.master_id', $master_id)
        //         ->where('devices.user_id', '!=', '')
        //         ->where('devices.user_id', '!=', 0)
        //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
        //         ->get();
        // }
        $master_id = Auth::user()->id;
        if (Auth::user()->user_type == 'Admin') {

            // Admin: only devices that are unassigned
            $devices = DB::table('devices')
                ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                ->select('devices.*')
                ->where('devices.is_deleted', '0')
                ->where(function ($q) {
                    $q->whereNull('devices.user_id')
                        ->orWhere('devices.user_id', '')
                        ->orWhere('devices.user_id', 0);
                })
                ->get();
        } else {
            // Reseller: devices with master_id = reseller or included in assign_to_ids
            $devices = DB::table('devices')
                ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                ->select('devices.*')
                ->where('devices.is_deleted', '0')
                ->where(function ($q) use ($master_id) {
                    $q->where('devices.user_id', $master_id);
                    // ->orWhereRaw('FIND_IN_SET(' . $master_id . ', devices.assign_to_ids)');
                    // ->Where('devices.user_id', $master_id);
                })
                ->get();
        }

        $users = DB::table('writers')->select('id', 'name')->where('writers.created_by', Auth::user()->id)->where('writers.is_deleted', '0')->where('user_type', '!=', 'Support')->get();
        if (Auth::user()->user_type == 'Reseller') {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
        } else {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
        }
        if (count($devices) > 0) {
            foreach ($devices as $device) {
                $device->username = 'Unassigned';
            }
            // foreach ($devices as $dkey => $device) {
            //     $uname = 'Unassigned'; // default
            //     $aids = explode(',', $device->assign_to_ids);
            //     $userId = $device->user_id;
            //     $next_id = null;

            //     if (!empty($aids)) {
            //         $next_id = self::getNextValue($aids, $master_id);
            //     }

            //     // Determine if device should be treated as Unassigned
            //     if (empty($userId) || $userId == 0 || $userId == $master_id) {
            //         $uname = 'Unassigned';
            //     } elseif ($next_id) {
            //         $w_details = DB::table('writers')->where('id', $next_id)->first();
            //         $uname = $w_details->name ?? 'error_' . $device->id . '_' . $next_id;
            //     } elseif (!empty($device->username)) {
            //         // fallback: use the joined writer name (e.g., admin or other parent)
            //         $uname = $device->username;
            //     }

            //     $devices[$dkey]->username = $uname;
            // }
            //    foreach ($devices as $dkey => $device) {
            //         $uname = 'Unassigned'; // default

            //         $aids = explode(',', $device->assign_to_ids);
            //         $userId = $device->user_id;

            //         if (count($aids) > 0) {
            //             $next_id = self::getNextValue($aids, $master_id);

            //             // Condition: consider as unassigned if user_id is null/empty/0 OR equals the next_id
            //             if (empty($userId) || $userId == 0 || $userId == $next_id) {
            //                 $uname = 'Unassigned';
            //             } elseif ($next_id) {
            //                 $w_details = DB::table('writers')->where('id', $next_id)->first();
            //                 $uname = $w_details->name ?? 'error_' . $device->id . '_' . $next_id;
            //             }
            //         }

            //         $devices[$dkey]->username = $uname;
            //     }
        }
        $url_type = self::getURLType();
        return view('view_device', ['users' => $users, 'device' => $devices, 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => true]);
    }
    /**
     * Show the form for editing the specified resource.
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device, $id)
    {
        $currentUser = auth()->user();
        $device_info = Device::findOrFail($id);
        if ($currentUser->user_type == 'User') {
            $checkUsers = DB::table('devices')->where('user_id', $currentUser->id)->pluck('user_id')->toArray();
        } elseif ($currentUser->user_type == 'Reseller') {
            $checkUsers = DB::table('devices')->where('master_id', $currentUser->id)->pluck('user_id')->toArray();
        } else {
            $checkUsers = [$currentUser->id]; // Assuming admin or another user type
        }
        // Fetch users based on user type
        if ($currentUser->user_type == 'User' || $currentUser->user_type == 'Reseller') {
            // Check if the current user can edit the specified device
            if (!in_array($device_info->user_id, $checkUsers) && $currentUser->id != $device_info->user_id) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
            $users = DB::table('writers')
                ->select('id', 'name')
                ->where('created_by', $currentUser->id)
                ->get();
        } else {
            $users = DB::table('writers')
                ->select('id', 'name')
                ->get();
        }
        if (Auth::user()->user_type == 'Admin') {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
        } else {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
        }
        // Determine URL type and assigned user ID for device
        $url_type = self::getURLType();
        $uid = self::getAssignedUserIdForDevice($id);
        // Reset assigned user ID if current user owns the device
        if ($uid == $currentUser->id || $device_info->user_id == $currentUser->id) {
            $uid = '';
        }
        return view('edit_device', [
            'device_info' => $device_info,
            'users' => $users,
            'url_type' => $url_type,
            'uid' => $uid,
            'template_info' => $template_info
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $id)
    {
        $uid = Auth::user()->id;
        $request->validate([
            'imei' => "unique:devices,imei,{$request->input('id')}|max:15|min:15"
        ]);
        $contact_id = $request->input('id');
        $is_editable = DB::table('devices')->where('id', $contact_id)->first();
        $prev_uid = $request->input('prev_uid');
        if (Auth::user()->user_type != 'Admin' and $is_editable->is_editable == '1') {
            $contact = Device::find($contact_id);
            if (Auth::user()->user_type == 'Reseller') {
                if ($request->get('user_id')) {
                    if ($prev_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                    {
                        $contact->master_id = auth()->id();
                        $contact->user_id =  $request->get('user_id');
                        $assign_to_ids = self::getDeviceAssignToList($contact_id);
                        $contact->assign_to_ids =  $assign_to_ids;
                    } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                    {
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($uid, $is_editable->assign_to_ids, 'no');
                        $contact->master_id = auth()->id();
                        $contact->user_id =  $request->get('user_id');
                        $contact->assign_to_ids =  $new_assing_ids;
                    }
                } else {
                    if ($prev_uid != '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                    {
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($uid, $is_editable->assign_to_ids, 'yes');
                        $parent_acc = self::getUserParent(auth()->id());
                        $contact->master_id = $parent_acc;
                        $contact->user_id =  auth()->id();
                        $contact->assign_to_ids =  $new_assing_ids;
                    }
                }
            }
            $contact->configurations = json_encode($request->get('configuration'));
            $contact->update();
        } elseif (Auth::user()->user_type == 'Admin') {
            $contact = Device::find($contact_id);
            if ($request->get('user_id')) {
                if ($prev_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                {
                    $contact->master_id = auth()->id();
                    $contact->user_id =  $request->get('user_id');
                    $assign_to_ids = self::getDeviceAssignToList($contact_id);
                    $contact->assign_to_ids =  $assign_to_ids;
                } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                {
                    $contact->master_id = auth()->id();
                    $contact->user_id =  $request->get('user_id');
                    $assign_to_ids = self::getDeviceAssignToList($contact_id);
                    $contact->assign_to_ids =  $assign_to_ids;
                }
            } else {
                $contact->master_id = 0;
                $contact->user_id =  NULL;
                $contact->assign_to_ids =  '';
            }
            $contact->configurations = json_encode($request->get('configuration'));
            if (Auth::user()->user_type == 'Admin') {
                $contact->is_editable = $request->get('is_editable');
            }
            $contact->update();
        } else {
            return redirect()->back()->with('error', 'you do not have permission to update');
        }
        if (Auth::user()->user_type == 'Admin') {
            return redirect()->back()->with('success', $request->imei . '-Device updated Successfully');
        } else {
            return redirect()->back()->with('success', $request->imei . '-Device updated Successfully');
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device, $id)
    {
        $device_data = Device::find($id);
        $device_data->is_deleted = '1';
        $device_data->delete();
        return redirect()->back()->with(['error' => $device_data->imei . '-Device deleted Successfully', 'device_category_id' => $device_data->device_category_id]);
    }
    public function deleteAll(Request $request)
    {
        $ids = $request->ids;
        $deleteAll = DB::table("devices")->whereIn('id', explode(",", $ids))->delete();
        return response()->json(['success' => "Device Deleted successfully."]);
    }
    public function userassignAll(Request $request)
    {
        $master_id = Auth::user()->id;
        $devices = explode(",", $request->ids);
        $user_id = $request->user_id;
        $user_info = DB::table('writers')->select('writers.*')->where(['writers.id' => $user_id])->first();
        $user_device_cateogories = explode(',', $user_info->device_category_id);
        $user_configurations = json_decode($user_info->configurations, true);
        // dd($user_configurations);
        $device_category_id = 0;

        foreach ($devices as $id) {
            $device_info = Device::find($id);

            $device_category_id = $device_info->device_category_id;
            $device_uid = self::getAssignedUserIdForDevice($id);
            if ($device_uid == Auth::user()->id || $device_info->user_id == Auth::user()->id) {
                $device_uid = '';
            }
            $device_array = array();
            if (Auth::user()->user_type == 'Admin') {
                if ($user_id) {
                    if ($device_uid == '') {
                        $assign_to_ids = self::getDeviceAssignToList($id);
                        $device_array['master_id'] = auth()->id();
                        $device_array['user_id'] = $user_id;
                        $device_array['assign_to_ids'] = $assign_to_ids;
                    } else if ($device_uid != '' && $device_uid != $user_id) {
                        $assign_to_ids = self::getDeviceAssignToList($id);
                        $device_array['master_id'] = auth()->id();
                        $device_array['user_id'] = $user_id;
                        $device_array['assign_to_ids'] = $assign_to_ids;
                    }
                } else {
                    $device_array['master_id'] = 0;
                    $device_array['user_id'] = NULL;
                    $device_array['assign_to_ids'] = '';
                }
            } else {
                if ($user_id) {
                    if ($device_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                    {
                        $assign_to_ids = self::getDeviceAssignToList($id);
                        $device_array['master_id'] = auth()->id();
                        $device_array['user_id'] = $user_id;
                        $device_array['assign_to_ids'] = $assign_to_ids;
                    } else if ($device_uid != '' && $device_uid != $user_id) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                    {
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($master_id, $device_info->assign_to_ids, 'no');
                        $device_array['master_id'] = auth()->id();
                        $device_array['user_id'] = $user_id;
                        $device_array['assign_to_ids'] = $new_assing_ids;
                    }
                } else {
                    if ($device_uid != '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                    {
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($master_id, $device_info->assign_to_ids, 'yes');
                        $parent_acc = self::getUserParent(auth()->id());

                        $device_array['master_id'] = $parent_acc;
                        $device_array['user_id'] = auth()->id();
                        $device_array['assign_to_ids'] = $new_assing_ids;
                    }
                }
            }
            $configurations = [];
            $finalArray = [];
            //dd($device_info->configurations);
            $oldChanges = json_decode($device_info->configurations, true);
            foreach ($user_device_cateogories as $key => $device_cat) {
                if ($device_cat == $device_info->device_category_id) {
                    $newchanges = $user_configurations[$key];
                    $configurations = array_merge($oldChanges, $newchanges);
                }
            }
            // dd($configurations);
            // $models = DB::table('modals')->where('user_id', $request->user_id)->where('firmware_id', $configurations['firmware_id'])->first();
            // if($configurations['firmware_id']){
            if (Auth::user()->user_type == 'Admin') {
                $models = DB::table('modals')->where('user_id', $request->user_id)->where('firmware_id', $configurations['firmware_id']['value'])->first();
            } else {
                $assign_ids = explode(",", $device_array['assign_to_ids']);
                $models = DB::table('modals')->where('user_id', $assign_ids[1])->where('firmware_id', $configurations['firmware_id']['value'])->first();
            }
            // dd($models);
            $errors = [];
            $successfulUpdates = [];
            if ($models) {
                $configurations['modelName']['value'] = $models->name;
                $device_array['configurations'] = json_encode($configurations);
                $device_array['updated_at'] = date('Y-m-d H:i:s');
                DB::table('devices')->where('id', $id)->update($device_array);
                Devicelog::create([
                    'device_id' => $device_info->id,
                    'user_id' => auth()->id(),
                    'log' => CommonHelper::getUserName($user_id) . 'User Assign to Device with Imei No ' . $device_info->imei . ' Successfully!!',
                    'action' => 'Assign Account',
                    'is_active' => 1
                ]);
                $successfulUpdates[] = $device_info->imei;
            } else {
                $errors[] = $device_info->imei;
                continue;
            }
        }
        $successMessage = '';
        if (!empty($successfulUpdates)) {
            $successMessage .= 'Total Device Updated :' . count($successfulUpdates) . '</br>';

            $successMessage .= "Devices successfully updated for this imei: " . implode(', ', $successfulUpdates);
        }

        $errorMessage = '';
        if (!empty($errors)) {
            $errorMessage .= "Total Device Failed" . count($errors) . '</br>';
            $errorMessage = "Errors occurred for devices:";
            $errorMessage = "Device ID ";
            foreach ($errors as $error) {
                $errorMessage .= "$error" . ',';
            }
            $errorMessage  .= "Model name is not assigned to this " . CommonHelper::getFirmwareName($configurations['firmware_id']) . " firmware. Please contact the administrator.";
        }

        return response()->json([
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);
    }
    public function userassigtemplateAll(Request $request)
    {
        $deviceIds = explode(",", $request->ids);
        $templateId = $request->temp_id;
        $template = Template::find($templateId);
        // dd($template);

        if (!$template) {
            return response()->json(['error' => 'Template not found.'], 404);
        }
        $templateConfig = json_decode($template->configurations, true);

        $templateConfig['template'] = $templateId;
        if (!$templateConfig) {
            return response()->json(['error' => 'Invalid template configurations.'], 400);
        }
        $devices = Device::whereIn('id', $deviceIds)->get();
        $errors = [];
        $successfulUpdates = [];
        $updatedConfigurations = [];
        foreach ($devices as $device) {
            $deviceConfig = json_decode($device->configurations, true);
            if (!$deviceConfig) {
                continue;
            }
            if (isset($templateConfig['firmware_id']['value'])) {
                $firmware = Firmware::where('id', $templateConfig['firmware_id']['value'])->first();
                if (!$firmware) {
                    $errors[] = "Device ID {$device->id}: Firmware not found.";
                    continue;
                } else {
                    $firmwareConfig = json_decode($firmware['configurations']);

                    $deviceConfig['firmware_id']['value'] = $firmware->id;
                    $deviceConfig['firmware_file']['value'] = $firmwareConfig->filename;
                    $deviceConfig['firmware_version']['value'] = $firmwareConfig->version;
                }


                if ($device->user_id === null) {
                    $deviceConfig['modelName']['value'] = CommonHelper::getDeviceCategoryName($device->device_category_id);
                } else {
                    $assign_to_ids = explode(",", $device->assign_to_ids);
                    if (isset($assign_to_ids[1])) {
                        $models = Modal::where(['user_id' => $assign_to_ids[1], 'firmware_id' => $templateConfig['firmware_id']['value']])->first();
                        if ($models) {
                            $deviceConfig['modelName']['value'] = $models->name;
                        } else {
                            $errors[] = $device->imei;
                            continue;
                        }
                    }
                }
                $mergedConfig = array_merge($deviceConfig, $templateConfig);
                $device->configurations = json_encode($mergedConfig);
                $device->save();

                Devicelog::create([
                    'device_id' => $device->id,
                    'user_id' => auth()->id(),
                    'log' => 'Device with IMEI no ' . $device->imei . ' Assigned a New Templaten ' .  $template->template_name,
                    'action' => 'Updated Template',
                    'is_active' => 1
                ]);
                $successfulUpdates[] = $device->imei;
                $updatedConfigurations[$device->imei] = $mergedConfig;
            } else {
                return response()->json([
                    'error' => "Firmware not Assigned to " . $template->template_name . " template .please assign firmware first.",
                ]);
            }
        }


        // Determine redirect URL type
        $url_type = self::getURLType();

        // Prepare success and error messages
        $successMessage = '';
        if (!empty($successfulUpdates)) {
            $successMessage .= 'Total Device Updated :' . count($successfulUpdates) . '</br>';
            $successMessage .= "Devices successfully updated for this imei: " . implode(', ', $successfulUpdates);
        }

        $errorMessage = '';
        if (!empty($errors)) {
            $errorMessage = "Errors occurred for devices:" . count($errors) . '</br>';
            $errorMessage = "Device ID ";
            foreach ($errors as $error) {
                $errorMessage .= "$error" . ',';
            }
            $errorMessage  .= "Model name is not assigned to this " . CommonHelper::getFirmwareName($templateConfig['firmware_id']) . " firmware. Please contact the administrator.";
        }


        return response()->json([
            'success' => $successMessage,
            'error' => $errorMessage,
            'device_category_id' => $template->device_category_id,
            'updated_configurations' => $updatedConfigurations // Pass updated configurations
        ]);

        // Redirect with messages
        // return back()->with([
        //     'success' => $successMessage,
        //     'error' => $errorMessage,
        //     'device_category_id' => $template->device_category_id,
        //     'updated_configurations' => $updatedConfigurations // Pass updated configurations
        // ]);
    }

    public function showUserDevice()
    {
        if (Auth::user()->user_type == 'Support') {
            $devices = DB::table('devices')
                ->leftJoin('writers', function ($join) {
                    $join->on('writers.id', '=', 'devices.user_id')
                        ->where('writers.is_deleted', '=', '0');
                })
                ->select('devices.*', 'writers.name as username')
                ->where('devices.is_deleted', '0')
                ->get();
        } else {
            $devices = DB::table('devices')
                ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                ->select('devices.*', 'writers.name as username')
                ->where('devices.is_deleted', '0')
                ->where('writers.is_deleted', '0')
                ->where('devices.user_id', auth()->id())
                ->get();
        }
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('writers.is_deleted', '0')
            ->get();
        $template_info = [];
        $template_info = DB::table('templates')
            ->select('templates.*')
            ->where('templates.is_deleted', '0')
            ->where('verify', '2')
            ->where('id_user', auth()->id())
            ->get();

        $url_type = self::getURLType();
        return view('view_device', ['users' => $users, 'device' => $devices, 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => false]);
    }
    public function addMultipleDevice()
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('writers.user_type', '!=', 'Admin')
            ->where('writers.user_type', '!=', 'Support')
            ->where('writers.is_deleted', '0')
            ->when(Auth::user()->user_type !== 'Support', function ($query) {
                // Only restrict by created_by if NOT support
                $query->where('writers.created_by', Auth::user()->id);
            })
            ->get();

        $default_template = DB::table('templates')
            ->select('templates.*')
            ->where('templates.default_template', '1')
            ->first();
        return view('add_MultipleDevice', ['users' => $users, 'default_template' => $default_template]);
    }
    public function assignDeviceMultiple()
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('writers.user_type', '!=', 'Admin')
            ->where('writers.user_type', '!=', 'Support')
            ->where('writers.is_deleted', '0')
            ->when(Auth::user()->user_type !== 'Support', function ($query) {
                // Only restrict by created_by if NOT support
                $query->where('writers.created_by', Auth::user()->id);
            })
            ->get();

        $default_template = DB::table('templates')
            ->select('templates.*')
            ->where('templates.default_template', '1')
            ->first();
        return view('assign_device', ['users' => $users, 'default_template' => $default_template]);
    }
    public function submitImeiSheet(Request $request)
    {
        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $new_imei = $dup_imei = 0;
        $new_imei_html = $dup_imei_html = '';
        $data = $rows[0];
        unset($data[0]); // Remove header row

        $processedImeis = []; // Track processed IMEIs

        if (count($data) > 0) {
            foreach ($data as $value) {
                $sr_no = $value[0] ?? '';
                $name = $value[1] ?? '';
                $imei = isset($value[2]) ? strval($value[2]) : '';

                // Skip already processed IMEIs in the sheet
                if (in_array($imei, $processedImeis)) {
                    continue;
                }

                // Track current IMEI
                $processedImeis[] = $imei;

                if ($this->isValidIMEI($imei)) {
                    $record = DB::table('devices')
                        ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                        ->select('devices.*', 'writers.name as username')
                        ->where('devices.imei', $imei)
                        ->first();

                    if ($record) {
                        $dup_imei++;

                        $cname = $record->username ?? 'Unassigned';

                        $dup_imei_html .= '<tr>';
                        $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
                        $dup_imei_html .= '<td>' . $sr_no . '</td>';
                        $dup_imei_html .= '<td>' . $cname . '</td>';
                        $dup_imei_html .= '<td>' . $name . '</td>';
                        $dup_imei_html .= '<td>' . $record->imei . '</td>';
                        $dup_imei_html .= '<td>' . $record->created_at . '</td>';
                        $dup_imei_html .= '<td>' . $record->updated_at . '</td>';
                        $dup_imei_html .= '<td>' . $record->last_ping . '</td>';
                        $dup_imei_html .= '<td>' . $record->total_pings . '</td>';
                        $dup_imei_html .= '<td>' . $record->ping_interval . '</td>';
                        $dup_imei_html .= '<td>Yes</td>';
                        $dup_imei_html .= '</tr>';
                    } else {
                        $new_imei++;

                        $new_imei_html .= '<tr>';
                        $new_imei_html .= '<td><input type="checkbox" checked="checked" name="newemi[]" value="' . $imei . '"></td>';
                        $new_imei_html .= '<td>' . $sr_no . '</td>';
                        $new_imei_html .= '<td>' . $name . '</td>';
                        $new_imei_html .= '<td>' . $imei . '</td>';
                        $new_imei_html .= '</tr>';
                    }
                } else {
                    return json_encode([
                        "error" => 403,
                        "error_msg" => $imei . " is invalid. Please correct this."
                    ]);
                }
            }
        }

        return json_encode([
            'dup_imei' => $dup_imei,
            'new_imei' => $new_imei,
            'new_imei_html' => $new_imei_html,
            'dup_imei_html' => $dup_imei_html
        ]);
    }
    public function submitImeiSheetSupport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $data = $rows[0] ?? [];
        unset($data[0]); // Remove header row

        $processedImeis = [];
        $errorImeis = [];
        $notFoundImeis = [];

        $new_imei = $dup_imei = 0;
        $new_imei_html = $dup_imei_html = '';

        if (count($data) > 0) {
            foreach ($data as $row) {

                $sr_no = trim($row[0] ?? '');
                $name  = trim($row[1] ?? '');

                // --- FIX 1: Clean IMEI ---
                $imei = isset($row[2]) ? preg_replace('/\D/', '', trim($row[2])) : '';

                // --- FIX 2: Skip empty IMEI ---
                if ($imei === "" || strlen($imei) < 14) {
                    continue; // skip instead of returning error
                }

                // --- FIX 3: Avoid duplicates ---
                if (in_array($imei, $processedImeis)) {
                    continue;
                }
                $processedImeis[] = $imei;

                // --- FIX 4: Proper validation ---
                if (!$this->isValidIMEI($imei)) {
                    $errorImeis[] = $imei;
                    continue;
                }

                // --- Check in DB ---
                $deviceCheck = DB::table('devices')->where('imei', $imei)->first();

                if ($deviceCheck) {

                    // Already assigned to a user
                    if ($deviceCheck->user_id !== null) {
                        $errorImeis[] = $imei;
                        continue;
                    }

                    // Device exists & unassigned
                    $dup_imei++;

                    $writer = DB::table('writers')->where('id', $deviceCheck->user_id)->first();
                    $cname = $writer->name ?? 'Unassigned';

                    $dup_imei_html .= '
                    <tr>
                        <td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>
                        <td>' . $sr_no . '</td>
                        <td>' . $cname . '</td>
                        <td>' . $name . '</td>
                        <td>' . $deviceCheck->imei . '</td>
                        <td>' . $deviceCheck->created_at . '</td>
                        <td>' . $deviceCheck->updated_at . '</td>
                        <td>' . $deviceCheck->last_ping . '</td>
                        <td>' . $deviceCheck->total_pings . '</td>
                        <td>' . $deviceCheck->ping_interval . '</td>
                        <td>Yes</td>
                    </tr>';
                } else {
                    // IMEI not found in DB
                    $notFoundImeis[] = $imei;
                }
            }
        }

        // --- Final Response ---
        if (count($errorImeis) > 0 || count($notFoundImeis) > 0) {
            return json_encode([
                "error" => 403,
                "error_msg" => (count($errorImeis) > 0 ? "Invalid or Assigned IMEI: " . implode(", ", $errorImeis) : "") .
                    (count($notFoundImeis) > 0 ? " | Not found: " . implode(", ", $notFoundImeis) : ""),
                'dup_imei' => $dup_imei,
                'new_imei' => $new_imei,
                'new_imei_html' => $new_imei_html,
                'dup_imei_html' => $dup_imei_html
            ]);
        }

        return json_encode([
            'dup_imei' => $dup_imei,
            'new_imei' => $new_imei,
            'new_imei_html' => $new_imei_html,
            'dup_imei_html' => $dup_imei_html
        ]);
    }


    // public function submitImeiSheetSupport(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB limit
    //     ]);
    //     $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
    //     $new_imei = $dup_imei = 0;
    //     $new_imei_html = $dup_imei_html = '';
    //     $data = $rows[0];
    //     unset($data[0]);

    //     $processedImeis = [];
    //     $errorImeis = [];
    //     $notFoundImeis = [];

    //     if (count($data) > 0) {
    //         foreach ($data as $value) {
    //             $sr_no = $value[0] ?? '';
    //             $name = $value[1] ?? '';
    //             $imei = isset($value[2]) ? strval($value[2]) : '';

    //             if (in_array($imei, $processedImeis)) {
    //                 continue;
    //             }
    //             $processedImeis[] = $imei;

    //             if ($this->isValidIMEI($imei)) {
    //                 // Check if device exists in DB
    //                 $deviceCheck = DB::table('devices')->where('imei', $imei)->first();

    //                 if ($deviceCheck) {
    //                     // ✅ Case 1: Device exists but already assigned
    //                     if ($deviceCheck->user_id !== null) {
    //                         $errorImeis[] = $imei;
    //                         continue;
    //                     }

    //                     // ✅ Case 2: Device exists & unassigned (Support can claim these)
    //                     $dup_imei++;
    //                     $writer = DB::table('writers')->where('id', $deviceCheck->user_id)->first();
    //                     $cname = $writer->name ?? 'Unassigned';

    //                     $dup_imei_html .= '<tr>';
    //                     $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
    //                     $dup_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $dup_imei_html .= '<td>' . $cname . '</td>';
    //                     $dup_imei_html .= '<td>' . $name . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->imei . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->created_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->updated_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->last_ping . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->total_pings . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->ping_interval . '</td>';
    //                     $dup_imei_html .= '<td>Yes</td>';
    //                     $dup_imei_html .= '</tr>';
    //                 } else {
    //                     // ✅ Case 3: Device not in DB at all
    //                     $notFoundImeis[] = $imei;
    //                     continue;
    //                 }
    //             } else {
    //                 return json_encode([
    //                     "error" => 403,
    //                     "error_msg" => $imei . " is invalid. Please correct this."
    //                 ]);
    //             }
    //         }
    //     }

    //     // Handle errors
    //     if (count($errorImeis) > 0 || count($notFoundImeis) > 0) {
    //         $msg = [];
    //         if (count($errorImeis) > 0) {
    //             $msg[] = "Already exist (assigned to user): " . implode(", ", $errorImeis);
    //         }
    //         if (count($notFoundImeis) > 0) {
    //             $msg[] = "Not found in system: " . implode(", ", $notFoundImeis);
    //         }

    //         return json_encode([
    //             "error" => 403,
    //             "error_msg" => implode(" | ", $msg),
    //             'dup_imei' => $dup_imei,
    //             'new_imei' => $new_imei,
    //             'new_imei_html' => $new_imei_html,
    //             'dup_imei_html' => $dup_imei_html
    //         ]);
    //     }

    //     return json_encode([
    //         'dup_imei' => $dup_imei,
    //         'new_imei' => $new_imei,
    //         'new_imei_html' => $new_imei_html,
    //         'dup_imei_html' => $dup_imei_html
    //     ]);
    // }



    // public function submitImeiSheet(Request $request)
    // {
    //     $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
    //     $new_imei = $dup_imei = 0;
    //     $new_imei_html = $dup_imei_html = '';
    //     $data = $rows[0];
    //     unset($data[0]); // Remove header row

    //     $processedImeis = []; // Track processed IMEIs

    //     if (count($data) > 0) {
    //         foreach ($data as $value) {
    //             $sr_no = $value[0] ?? '';
    //             $name = $value[1] ?? '';
    //             $imei = isset($value[2]) ? strval($value[2]) : '';

    //             // Skip already processed IMEIs in the sheet
    //             if (in_array($imei, $processedImeis)) {
    //                 continue;
    //             }

    //             // Track current IMEI
    //             $processedImeis[] = $imei;

    //             if ($this->isValidIMEI($imei)) {
    //                 $record = DB::table('devices')
    //                     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //                     ->select('devices.*', 'writers.name as username')
    //                     ->where('devices.imei', $imei)
    //                     ->first();

    //                 if ($record) {
    //                     $dup_imei++;

    //                     $cname = $record->username ?? 'Unassigned';

    //                     $dup_imei_html .= '<tr>';
    //                     $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
    //                     $dup_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $dup_imei_html .= '<td>' . $cname . '</td>';
    //                     $dup_imei_html .= '<td>' . $name . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->imei . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->created_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->updated_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->last_ping . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->total_pings . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->ping_interval . '</td>';
    //                     $dup_imei_html .= '<td>Yes</td>';
    //                     $dup_imei_html .= '</tr>';
    //                 } else {
    //                     $new_imei++;

    //                     $new_imei_html .= '<tr>';
    //                     $new_imei_html .= '<td><input type="checkbox" checked="checked" name="newemi[]" value="' . $imei . '"></td>';
    //                     $new_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $new_imei_html .= '<td>' . $name . '</td>';
    //                     $new_imei_html .= '<td>' . $imei . '</td>';
    //                     $new_imei_html .= '</tr>';
    //                 }
    //             } else {
    //                 return json_encode([
    //                     "error" => 403,
    //                     "error_msg" => $imei . " is invalid. Please correct this."
    //                 ]);
    //             }
    //         }
    //     }

    //     return json_encode([
    //         'dup_imei' => $dup_imei,
    //         'new_imei' => $new_imei,
    //         'new_imei_html' => $new_imei_html,
    //         'dup_imei_html' => $dup_imei_html
    //     ]);
    // }

    // public function submitImeiSheet(Request $request)
    // {
    //     $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
    //     $new_imei = $dup_imei = 0;
    //     $new_imei_html = $dup_imei_html = '';
    //     $data = $rows[0];
    //     unset($data[0]);
    //     if (count($data) > 0) {
    //         foreach ($data as $value) {
    //             $sr_no = $value[0];
    //             $name = $value[1];
    //             $imei = strval($value[2]);
    //             if ($this->isValidIMEI($imei)) {
    //                 $record = DB::table('devices')->leftJoin('writers', 'writers.id', '=', 'devices.user_id')->select('devices.*', 'writers.name as username')->where('devices.imei', $imei)->first();
    //                 if ($record) {
    //                     $dup_imei = $dup_imei + 1;
    //                     $cname = 'Unassigned';
    //                     $active_status = 'Inactive';
    //                     $fota = 'No';
    //                     if ($record->username) {
    //                         $cname = $record->username;
    //                     }
    //                     // if ($record->active_status == 1) {
    //                     //     $active_status = 'Active';
    //                     // }
    //                     // if ($record->fota == 1) {
    //                     //     $fota = 'Yes';
    //                     // }
    //                     $dup_imei_html .= '<tr>';
    //                     $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
    //                     $dup_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $dup_imei_html .= '<td>' . $cname . '</td>';
    //                     $dup_imei_html .= '<td>' . $name . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->imei . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->created_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->updated_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->last_ping . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->total_pings . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->ping_interval . '</td>';
    //                     $dup_imei_html .= '<td>Yes</td>';
    //                     $dup_imei_html .= '</tr>';
    //                 } else {
    //                     $new_imei = $new_imei + 1;
    //                     $new_imei_html .= '<tr>';
    //                     $new_imei_html .= '<td><input type="checkbox" checked="checked" name="newemi[]" value="' . $imei . '"></td>';
    //                     $new_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $new_imei_html .= '<td>' . $name . '</td>';
    //                     $new_imei_html .= '<td>' . $imei . '</td>';
    //                     $new_imei_html .= '</tr>';
    //                 }
    //             } else {
    //                 return json_encode(["error" => 403, "error_msg" => $imei . " is invalid please correct this."]);
    //             }
    //         }
    //     }
    //     if ($new_imei == 0) {
    //         //$new_imei_html.='<tr><td colspan="4">No new IMEI found</td></tr>';
    //     }
    //     if ($dup_imei == 0) {
    //         //$dup_imei_html.='<tr><td colspan="4">No duplicate IMEI found</td></tr>';
    //     }
    //     return json_encode(array('dup_imei' => $dup_imei, 'new_imei' => $new_imei, 'new_imei_html' => $new_imei_html, 'dup_imei_html' => $dup_imei_html));
    // }

    private function isValidIMEI($imei)
    {
        // Remove any non-digit characters
        $imei = preg_replace('/[^0-9]/', '', $imei);

        // Check if IMEI is exactly 15 digits
        if (strlen($imei) !== 15) {
            return false;
        }

        // Check the Luhn algorithm (checksum validation)
        $sum = 0;
        $shouldDouble = false;
        for ($i = strlen($imei) - 1; $i >= 0; $i--) {
            $digit = intval($imei[$i]);
            if ($shouldDouble) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        return ($sum % 10 === 0);
    }

    public function submitMultipleDevice(Request $request)
    {
        $new_imei_list = $dup_imei_list = array();
        $dup_type = $request->get('dup_type');
        $config = $request->configuration;
        $converted = [];
        if ($dup_type != 'overwrite') {
            $commonFields = DB::table("data_fields")->where(["is_common" => 1])->get();
            foreach ($commonFields as $index => $value) {
                if (strpos($value->fieldName, ' ') !== false) {
                    $key = strtolower(str_replace(' ', '_', $value->fieldName));
                } else {
                    $key = lcfirst(str_replace(' ', '_', $value->fieldName));
                }
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $config[$key] ?? ''
                ];
            }
        }


        if (isset($request->user_id) && $request->user_id != '') {
            $user = DB::table('writers')->where(['id' => $request->user_id])->first();
            $configuration = json_decode($user->configurations);
            $deviceCategoryId = explode(',', $user->device_category_id);
            $selectedConfig = [];
            foreach ($deviceCategoryId as $key => $id) {
                if ($id == $request->deviceCategory) {
                    $selectedConfig = $configuration[$key];
                }
            }
            $mergedConfig = array_merge($converted, (array) $selectedConfig);
            $converted = $mergedConfig;
        } else {
            $idParameters = $request->idParameters;
            foreach ($idParameters as $key => $id) {
                if (isset($config[$key])) {
                    $converted[$key] = [
                        'id' => intval($id),
                        'value' => $config[$key] ?? ''
                    ];
                }
            }
        }
        // dd($converted);
        if ($request->get('new_imei_list')) {
            $new_imei_list = explode(',', $request->get('new_imei_list'));
        }
        if ($request->get('dup_imei_list')) {
            $dup_imei_list = explode(',', $request->get('dup_imei_list'));
        }
        // dd($dup_imei_list);
        //$temp_devices=DB::table('temp_devices')->select('temp_devices.*')->get(); 
        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {

                $name = $value[1];
                $imei = strval($value[2]);
                $deviceData = Device::Select('*')->where('imei', $imei)->first();
                if ($imei) {
                    $oldConfig = $deviceData ? json_decode($deviceData->configurations, true) : [];
                    $newConfig = array_merge($oldConfig, $converted);
                    $arr['configurations'] = json_encode($newConfig);
                    $arr['can_configurations'] = !empty($request->canConfigurationArr) ? json_decode($request->canConfigurationArr, true) : [];
                    if (in_array($imei, $new_imei_list)) {
                        $master_id = Auth::user()->id;
                        // $mid = $master_id;
                        $mid = $assign_to_ids = '';
                        if ($request->user_id) {
                            $mid = $master_id;
                            $assign_to_ids = $request->user_id;
                        }

                        $arr['name'] = $name;
                        $arr['imei'] = $imei;
                        $arr['master_id'] = $master_id;
                        $arr['assign_to_ids'] = $assign_to_ids;
                        $arr['device_category_id'] = $request->deviceCategory;
                        Device::create($arr);
                    }

                    if (in_array($imei, $dup_imei_list) && $dup_type == 'overwrite') {
                        $master_id = Auth::user()->id;
                        $mid = $assign_to_ids = '';

                        if ($request->user_id) {
                            $mid = $master_id;
                            $assign_to_ids = $request->user_id;
                        }
                        $arr['name'] = $name;
                        $arr['master_id'] = $mid;
                        $arr['assign_to_ids'] = $assign_to_ids;
                        $arr['user_id'] =  $request->user_id;
                        // $arr['device_category_id'] = $request->deviceCategory;
                        // dd($arr);
                        // dd($imei);
                        $device = DB::table('devices')->where('imei', $imei)->update($arr);
                        // dd($deviceData);
                        $log = Devicelog::create([
                            'device_id' => $deviceData->id,
                            'user_id' => $master_id,
                            'log' => 'Device with imei no ' . $imei . ' Created Successfully',
                            'action' => 'Created',
                            'is_active' => 1
                        ]);
                    }
                } else {
                    return back()->with('error', "imei not found in file");
                }
            }

            return back()->with('success', "Import Successfully");
        }
    }

    public function submitMultipleDeviceSupport(Request $request)
    {
        $new_imei_list = $dup_imei_list = array();
        $dup_type = $request->get('dup_type');
        $config = $request->configuration;
        $converted = [];
        if ($dup_type != 'overwrite') {
            $commonFields = DB::table("data_fields")->where(["is_common" => 1])->get();
            foreach ($commonFields as $index => $value) {
                if (strpos($value->fieldName, ' ') !== false) {
                    $key = strtolower(str_replace(' ', '_', $value->fieldName));
                } else {
                    $key = lcfirst(str_replace(' ', '_', $value->fieldName));
                }
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $config[$key] ?? ''
                ];
            }
        }


        if (isset($request->user_id) && $request->user_id != '') {
            $user = DB::table('writers')->where(['id' => $request->user_id])->first();
            $configuration = json_decode($user->configurations);
            $deviceCategoryId = explode(',', $user->device_category_id);
            $selectedConfig = [];
            foreach ($deviceCategoryId as $key => $id) {
                if ($id == $request->deviceCategory) {
                    $selectedConfig = $configuration[$key];
                }
            }
            $mergedConfig = array_merge($converted, (array) $selectedConfig);
            $converted = $mergedConfig;
        } else {
            $idParameters = $request->idParameters;
            foreach ($idParameters as $key => $id) {
                if (isset($config[$key])) {
                    $converted[$key] = [
                        'id' => intval($id),
                        'value' => $config[$key] ?? ''
                    ];
                }
            }
        }
        // dd($converted);
        if ($request->get('new_imei_list')) {
            $new_imei_list = explode(',', $request->get('new_imei_list'));
        }
        if ($request->get('dup_imei_list')) {
            $dup_imei_list = explode(',', $request->get('dup_imei_list'));
        }
        // dd($dup_imei_list);
        //$temp_devices=DB::table('temp_devices')->select('temp_devices.*')->get(); 
        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {

                $name = $value[1];
                $imei = strval($value[2]);
                $deviceData = Device::Select('*')->where('imei', $imei)->first();
                if ($imei) {
                    $oldConfig = $deviceData ? json_decode($deviceData->configurations, true) : [];
                    $newConfig = array_merge($oldConfig, $converted);
                    $arr['configurations'] = json_encode($newConfig);
                    if (in_array($imei, $new_imei_list)) {
                        $master_id = Auth::user()->id;
                        // $mid = $master_id;
                        $mid = $assign_to_ids = '';
                        if ($request->user_id) {
                            $mid = $master_id;
                            $assign_to_ids = $request->user_id;
                        }

                        $arr['name'] = $name;
                        $arr['imei'] = $imei;
                        $arr['master_id'] = $master_id;
                        $arr['assign_to_ids'] = $assign_to_ids;
                        $arr['device_category_id'] = $request->deviceCategory;
                        Device::create($arr);
                    }

                    if (in_array($imei, $dup_imei_list) && $dup_type == 'overwrite') {
                        $master_id = Auth::user()->id;
                        $mid = $assign_to_ids = '';

                        if ($request->user_id) {
                            $mid = $master_id;
                            $assign_to_ids = $request->user_id;
                        }
                        $arr['name'] = $name;
                        $arr['master_id'] = $mid;
                        $arr['assign_to_ids'] = $assign_to_ids;
                        $arr['user_id'] =  $request->user_id;
                        // $arr['device_category_id'] = $request->deviceCategory;
                        // dd($arr);
                        // dd($imei);
                        $device = DB::table('devices')->where('imei', $imei)->update($arr);
                        // dd($deviceData);
                        $log = Devicelog::create([
                            'device_id' => $deviceData->id,
                            'user_id' => $master_id,
                            'log' => 'Device with imei no ' . $imei . ' Created Successfully',
                            'action' => 'Created',
                            'is_active' => 1
                        ]);
                    }
                } else {
                    return back()->with('error', "imei not found in file");
                }
            }

            return back()->with('success', "Import Successfully");
        }
    }

    public function showConfigurations($id)
    {
        $device = Device::Find($id);
        $url_type = self::getURLType();
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'Reseller') {
            $checkUser = DB::table('devices')->where('master_id', $currentUser->id)->pluck('user_id')->toArray();
            $users = DB::table('writers')
                ->select('id', 'name')
                ->where('user_type', '!=', 'Admin')
                ->where('user_type', '!=', 'Support')
                ->where('is_deleted', 0)
                ->where('created_by', $currentUser->id)
                ->get();
            if (!in_array($device->user_id, $checkUser) && $currentUser->id != $device->user_id) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
        } else if ($currentUser->user_type == "User") {
            if ($currentUser->id != $device->user_id) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
            $users = DB::table('writers')
                ->select('id', 'name')
                ->where('user_type', '!=', 'Admin')
                ->where('user_type', '!=', 'Support')
                ->where('is_deleted', 0)
                ->where('created_by', $currentUser->id)
                ->get();
        } else {
            $users = DB::table('writers')
                ->select('id', 'name')
                ->where('is_deleted', 0)
                ->where('user_type', '!=', 'Admin')
                ->where('user_type', '!=', 'Support')
                ->where('created_by', Auth::user()->id)
                ->whereRaw('FIND_IN_SET(' . $device->device_category_id . ',device_category_id)')
                ->get();
        }
        $uid = self::getAssignedUserIdForDevice($id);
        if ($uid == $currentUser->id || $device->user_id == $currentUser->id) {
            $uid = '';
        }
        $firmware = Firmware::where('device_category_id', $device->device_category_id)->where('is_deleted', 0)->get();
        $query = Template::select('*')->where('device_category_id', $device->device_category_id)->where('is_deleted', '0');

        if (Auth::user()->user_type == 'Admin') {
            $query->where('verify', '1');
        } else {
            $query->where('id_user', Auth::user()->id)
                ->where('verify', '2');
        }
        $templates  = $query->get();
        return view('view_device_configurations', ["users" => $users, 'uid' => $uid, 'device' => $device, 'template_info' => $templates, 'url_type' => $url_type, 'firmware' => $firmware]);
    }
    public function updateDeviceConfigurations(Request $request, $id)
    {
        $params = $request->configuration[0];
        $keys = array_keys($params);
        // print_r($keys);
        $dataFields = DataFields::select("*")->where(['is_common' => 0, 'fieldType' => 0])->get();

        $converted = [];

        foreach ($dataFields as $value) {
            $fieldName = $value->fieldName;

            // Convert to snake_case
            $key = strtolower(str_replace(' ', '_', $fieldName));

            // Only process if key exists in $params
            if (array_key_exists($key, $params)) {
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $params[$key]
                ];
            }
        }
        // dd($converted);
        $device = Device::Find($id);
        $newChanges = $converted;
        $oldChanges = json_decode($device->configurations, true);
        $changedFields = [];
        foreach ($newChanges as $key => $value) {
            if (!isset($oldChanges[$key]) || $oldChanges[$key]['value'] !== $value['value']) {
                $oldValue = isset($oldChanges[$key]) ? $oldChanges[$key]['value'] : 'N/A';
                $newValue = $value['value'];
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }
        if ($newChanges) {
            $device->deviceStatus = "Pending";
        }
        $result = array_replace($oldChanges, $newChanges);
        $device->configurations = json_encode($result);
        $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
        $device->timestamps = false; // disable auto timestamps temporarily
        $device->updated_at = $utcTime;
        $device->save();
        $device->timestamps = true;
        // // vice->updated_at = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
        // dd($device->updated_at);
        //$device->save();
        if (!empty($changedFields)) {
            $changeLogMessage = '';
            foreach ($changedFields as $field => $change) {
                $formattedKey = ucfirst(str_replace('_', ' ', $field));
                $changeLogMessage .= "$formattedKey: {$change['old']} ➜ {$change['new']}; ";
            }

            Devicelog::create([
                'device_id' => $device->id,
                'user_id' =>  auth()->id(),
                'log' => 'Device with IMEI no ' . $device->imei . ' Configuration updated. Changes: ' . rtrim($changeLogMessage, '; '),
                'action' => 'Updated',
                'is_active' => 1
            ]);
        }

        return back();
    }
    public function updateCanProtocolConfigurations(Request $request, $id)
    {
        $params = $request->canConfiguration;
        $dataFields = DataFields::select("*")->where(['is_can_protocol' => 1])->get()->keyBy(function ($item) {
            // Convert field names to lowercase snake_case as key
            return strtolower(str_replace(' ', '_', $item->fieldName));
        });

        $converted = [];

        foreach ($params as $key => $value) {
            if (isset($dataFields[$key])) {
                if (isset($request->CanParametersType[$key]) && $request->CanParametersType[$key] == 'multiselect') {
                    $formattedMultiValue = '{' . implode(',', $value) . '}';
                    $converted[$key] = [
                        'id' => $dataFields[$key]->id,
                        'value' => $formattedMultiValue
                    ];
                } else {
                    $converted[$key] = [
                        'id' => $dataFields[$key]->id,
                        'value' => $value
                    ];
                }
            }
        }
        $device = Device::find($id);
        $newChanges = $converted;
        $oldChanges = json_decode($device->can_configurations, true) ?? [];
        $changedFields = [];

        foreach ($newChanges as $key => $value) {
            if (!isset($oldChanges[$key]) || $oldChanges[$key]['value'] !== $value['value']) {
                $oldValue = $oldChanges[$key]['value'] ?? 'N/A';
                $newValue = $value['value'];
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }
        if ($newChanges) {
            $device->deviceStatus = "Pending";
        }

        $device->can_configurations = json_encode($newChanges);
        $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
        $device->timestamps = false; // disable auto timestamps temporarily
        $device->updated_at = $utcTime;
        // $device->save();
        $device->timestamps = true;
        // $device->updated_at = Carbon::now('UTC')->toDateTimeString();
        $device->save();

        if (!empty($changedFields)) {
            $changeLogMessage = '';
            foreach ($changedFields as $field => $change) {
                $formattedKey = ucfirst(str_replace('_', ' ', $field));
                $oldValue = is_array($change['old']) ? json_encode($change['old']) : $change['old'];
                $newValue = is_array($change['new']) ? json_encode($change['new']) : $change['new'];

                $changeLogMessage .= "$formattedKey: {$oldValue} ➜ {$newValue}; ";
            }

            Devicelog::create([
                'device_id' => $device->id,
                'user_id' => auth()->id(),
                'log' => 'Device with IMEI no ' . $device->imei . ' Configuration updated. Changes: ' . rtrim($changeLogMessage, '; '),
                'action' => 'Updated',
                'is_active' => 1
            ]);
        }

        return back();
    }
    public function updateDeviceInfoConfigurations(Request $request, $id)
    {
        $params = $request->configuration;
        $dataFields = DataFields::select("*")->where(['is_common' => 1])->get();

        $converted = [];
        foreach ($dataFields as $index => $value) {
            $fieldName = $value->fieldName;

            if (strpos($fieldName, ' ') !== false) {
                $key = strtolower(str_replace(' ', '_', $fieldName));
            } else {
                $key = lcfirst($fieldName);
            }
            $converted[$key] = [
                'id' => $value->id,
                'value' => $params[$key] ?? ''
            ];
        }

        $firmware = Firmware::select('configurations')->where(['id' => $request->configuration['firmware_id']])->first();

        $device = Device::Find($id);
        $contact_id = $request->input('device_id');

        $is_editable = DB::table('devices')->where('id', $contact_id)->first();
        $config = json_decode($is_editable->configurations, true);
        // dd($config['is_editable']['value']);
        $prev_uid = $request->input('prev_uid');



        if (Auth::user()->user_type != 'Admin' && $config['is_editable']['value'] == '1') {
            $contact = Device::find($contact_id);
            if (Auth::user()->user_type == 'Reseller') {
                if ($request->get('user_id')) {
                    if ($prev_uid == '') {
                        $contact->master_id = Auth::user()->id;
                        $contact->user_id =   $request->get('user_id') != null ? $request->get('user_id') : auth()->id();
                        $assign_to_ids = self::getDeviceAssignToList($contact->user_id);
                        $contact->assign_to_ids =  $assign_to_ids;
                    } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) {
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser(Auth::user()->id, $is_editable->assign_to_ids, 'no');
                        $contact->master_id = Auth::user()->id;
                        $contact->user_id =  $request->get('user_id');
                        $contact->assign_to_ids =  $new_assing_ids;
                    }
                } else {
                    if ($prev_uid != '') {
                        // echo "hi";
                        // dd(auth()->id());
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser(Auth::user()->id, $is_editable->assign_to_ids, 'yes');
                        $parent_acc = self::getUserParent(Auth::user()->id);
                        // echo $parent_acc;
                        $contact->master_id = $parent_acc;
                        $contact->user_id =  $prev_uid;
                        $contact->assign_to_ids =  $new_assing_ids;
                    }
                }
            }
            //  dd($contact->assign_to_ids);
            // $contact->name  = $request->name;
            $firmwareChanges = json_decode($firmware->configurations, true);
            $converted['firmware_file']['value'] = $firmwareChanges['filename'];
            $converted['firmware_version']['value'] = $firmwareChanges['version'];
            $converted['firmwareFileSize']['value'] = $firmwareChanges['fileSize'];


            // $newChanges['firmware_file'] = $firmwareChanges['filename'];
            // $newChanges['firmware_version'] = $firmwareChanges['version'];
            $oldChanges = json_decode($contact->configurations, true);
            $converted['ping_interval']['value'] =  $params['ping_interval'] ?? $oldChanges['ping_interval']['value'];
            $converted['is_editable']['value'] =  $params['is_editable'] ?? $oldChanges['is_editable']['value'];
            $newChanges = $converted;
            // dd($newChanges);
            $changedFields = [];
            foreach ($newChanges as $key => $value) {
                if (!isset($oldChanges[$key]) || $oldChanges[$key]['value'] !== $value['value']) {
                    $oldValue = isset($oldChanges[$key]) ? $oldChanges[$key]['value'] : 'N/A';
                    $newValue = $value['value'];
                    $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
                }
            }

            if ($newChanges) {
                $contact->deviceStatus = 'Pending';
            }
            $result = array_replace($oldChanges, $newChanges);


            $contact->name  = $request->get('name');
            $contact->configurations = json_encode($result);
            $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
            $contact->timestamps = false; // disable auto timestamps temporarily
            $contact->updated_at = $utcTime;


            // $contact->updated_at = Carbon::now('UTC')->toDateTimeString();
            $contact->update();
            $contact->timestamps = true; // re-enable timestamps
            if (!empty($changedFields)) {
                $changeLogMessage = '';
                foreach ($changedFields as $field => $change) {
                    $formattedKey = ucfirst(str_replace('_', ' ', $field));
                    $changeLogMessage .= "$formattedKey: {$change['old']} ➜ {$change['new']}; ";
                }

                Devicelog::create([
                    'device_id' => $contact->id,
                    'user_id' => auth()->id(),
                    'log' => 'Device with IMEI no ' . $contact->imei . ' updated. Changes: ' . rtrim($changeLogMessage, '; '),
                    'action' => 'Updated',
                    'is_active' => 1,
                ]);
            }
        } elseif (Auth::user()->user_type == 'Admin') {

            $contact = Device::find($contact_id);
            if ($request->get('user_id')) {
                if ($prev_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                {
                    $contact->master_id = auth()->id();
                    $contact->user_id =  $request->get('user_id');
                    $assign_to_ids = self::getDeviceAssignToList($contact_id);
                    $contact->assign_to_ids =  $assign_to_ids;
                } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                {
                    $contact->master_id = auth()->id();
                    $contact->user_id =  $request->get('user_id');
                    $assign_to_ids = self::getDeviceAssignToList($contact_id);
                    $contact->assign_to_ids =  $assign_to_ids;
                }
            } else {
                $contact->master_id = 0;
                $contact->user_id =  NULL;
                $contact->assign_to_ids =  '';
            }
            $firmwareChanges = json_decode($firmware->configurations, true);
            $converted['firmware_file']['value'] = $firmwareChanges['filename'];
            $converted['firmware_version']['value'] = $firmwareChanges['version'];
            $converted['firmwareFileSize']['value'] = $firmwareChanges['fileSize'];
            $newChanges = $converted;

            $oldChanges = json_decode($contact->configurations, true);
            $converted['ping_interval']['value'] =  $params['ping_interval'] ?? $oldChanges['ping_interval']['value'];
            $converted['is_editable']['value'] =  $params['is_editable'] ?? $oldChanges['is_editable']['value'];
            $newChanges = $converted;
            $changedFields = [];

            foreach ($newChanges as $key => $value) {
                if (!isset($oldChanges[$key]) || (isset($oldChanges[$key]['value']) && $oldChanges[$key]['value'] !== $value['value'])) {
                    $oldValue = isset($oldChanges[$key]) ? $oldChanges[$key]['value'] : 'N/A';
                    $newValue = $value['value'];
                    $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
                }
            }

            if ($newChanges) {
                $contact->deviceStatus = 'Pending';
            }
            $result = array_replace($oldChanges, $newChanges);

            $contact->name  = $request->get('name');
            $contact->configurations = json_encode($result);
            if (Auth::user()->user_type == 'Admin') {
                $contact->is_editable = $request->get('is_editable');
            }
            $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
            $contact->timestamps = false; // disable auto timestamps temporarily
            $contact->updated_at = $utcTime;


            // $contact->updated_at = Carbon::now('UTC')->toDateTimeString();
            $contact->update();
            $contact->timestamps = true;
            $contact->update();
            if (!empty($changedFields)) {
                $changeLogMessage = '';
                foreach ($changedFields as $field => $change) {
                    $formattedKey = ucfirst(str_replace('_', ' ', $field));
                    $changeLogMessage .= "$formattedKey: {$change['old']} ➜ {$change['new']}; ";
                }

                Devicelog::create([
                    'device_id' => $contact->id,
                    'user_id' => auth()->id(),
                    'log' => 'Device with IMEI no ' . $contact->imei . ' updated. Changes: ' . rtrim($changeLogMessage, '; '),
                    'action' => 'Updated',
                    'is_active' => 1
                ]);
            }
            // $log = Devicelog::create([
            //     'device_id' => $contact->id,
            //     'user_id' => $request->get('user_id'),
            //     'log' => 'Device with imei no '.$contact->imei.' Updated Successfully' ,
            //     'action'=>'Created',
            //     'is_active' => 1
            // ]);
        } else {
            return redirect()->back()->with('error', 'you do not have permission to update');
        }
        return back();
    }
    public function viewUncategorized()
    {
        $device = Device::leftJoin('writers', 'writers.id', '=', 'devices.user_id')
            ->leftJoin('device_categories', 'device_categories.id', '=', 'devices.device_category_id')
            ->select(
                'devices.*',
                'writers.name as writer_name',
                'device_categories.device_category_name'
            )
            ->where('device_categories.is_deleted', 1)
            ->where('devices.is_deleted', 0)
            ->get();
        $url_type = self::getURLType();
        return view('view_uncategorized', ['device' => $device, 'url_type' => $url_type]);
    }
    public function checkModalName(Request $request)
    {
        $request->validate([
            'modalName' => 'required|string'
        ]);

        // Check if a record with the given modalName exists
        $exists = modal::where('name', $request->modalName)->exists();
        $userexist = modal::where(['name' =>  $request->modalName, 'user_id' => $request->userAssign, 'firmware_id' => $request->firmwareId])->exists();
        if ($userexist) {
            return response()->json(['status' => 400, 'message' => 'This Model Name is already assigned to this Account']);
        } else {
            // Return a JSON response indicating that the modal name is available
            return response()->json(['status' => 200, 'message' => 'Modal name is available']);
        }
        // if ($exists) {
        //     // Return a JSON response indicating that the modal name exists
        //     return response()->json(['status' => 400, 'message' => 'Modal name already exists']);
        // } else {
        //     // Return a JSON response indicating that the modal name is available
        //     return response()->json(['status' => 200, 'message' => 'Modal name is available'] );
        // }
    }
    public function destroyDataField(DataFields $dataField, $id)
    {
        $device_data_field = DataFields::find($id);
        // $device_data->is_deleted = '1';
        $device_data_field->delete();
        return redirect()->back()->with(['error' => 'Device Data Field deleted Successfully']);
    }
    public function getDataFields()
    {
        $dataFields = DataFields::get();
        return response()->json([
            'status' => 200,
            'status_message' => 'Data Fields Fetched Successfully',
            'data' => $dataFields
        ]);
    }
    public function addDeviceDataField(Request $request)
    {
        $data = $request->all();
        $dataBinding = [];
        if (isset($data['input_type']) && $data['field_type'] == 0) {
            switch ($data['input_type']) {
                case 'select':
                    $dataBinding['selectOptions'] = $data['selectOptions'][0] ?? [];
                    $dataBinding['selectValues'] = $data['selectValues'][0] ?? [];
                    break;
                case 'multiselect':
                    $dataBinding['selectOptions'] = $data['selectOptions'][0] ?? [];
                    $dataBinding['selectValues'] = $data['selectValues'][0] ?? [];
                    $dataBinding['maxSelectValue'] = $data['maxSelectValue'][0] ?? [];
                    break;
                case 'number':
                    $dataBinding['numberInput'] = $data['numberInput'] ?? ['min' => null, 'max' => null];
                    break;
                case 'text':
                case 'IP/URL':
                case 'hex':
                    $dataBinding['maxValueInput'] = $data['maxValueInput'][0][0] ?? null;
                    break;
                case 'text_array':
                    $dataBinding['maxValueInput'] = $data['maxValueInput'][0][0] ?? null;
                    break;
            }
        }
        try {
            $field = DataFields::updateOrCreate(
                ['id' => $request->input('dataFieldId')],
                isset($data['field_type']) && $data['field_type'] == 0  ? [
                    'fieldName' => $data['field_name'],
                    'fieldType' => $data['field_type'],
                    'inputType' => $data['field_type'] == 0 ?  $data['input_type'] : '',
                    'is_common' => isset($data['is_common']) && $data['is_common'] == 'on' ? 1 : 0,
                    'is_can_protocol' => isset($data['is_can_protocol']) && $data['is_can_protocol'] == 'on' ? 1 : 0,
                    'validationConfig' => $data['field_type'] == 0 ? json_encode($dataBinding) : '',
                ] : [
                    'fieldName' => $data['field_name'],
                    'fieldType' => $data['field_type'],
                    'inputType' => '',
                    'is_common' => isset($data['is_common']) && $data['is_common'] == 'on' ? 1 : 0,
                    'is_can_protocol' => isset($data['is_can_protocol']) && $data['is_can_protocol'] == 'on' ? 1 : 0,
                    'validationConfig' => '',
                ]
            );

            return response()->json([
                'status' => 200,
                'status_message' => ($request->imei ?? 'Device') . ' - Device ' . ($request->dataFieldId ? 'Updated' : 'Added') . ' Successfully',
                'data' => $field
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'status_message' => 'Failed to save device field.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getCanProtoColFields(Request $request)
    {
        $protocolType = $request->protocol;
        $fieldIdArray = [];
        if ($protocolType == '2') {
            $fieldIdArray = ['89', '90'];
        } else if ($protocolType == '1') {
            $fieldIdArray = ['91', '92'];
        } else if ($protocolType == '3') {
            $fieldIdArray = ['102', '93'];
        }
        $fields = DataFields::whereIn('id', $fieldIdArray)->get([
            'id',
            'fieldName',
            'inputType',
            'validationConfig'
        ]);

        return response()->json($fields);
    }
}
