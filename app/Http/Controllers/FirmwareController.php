<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Auth;
use App\DataFields;
use App\Helper\CommonHelper;
use App\esim;
use App\backend;
use App\Ccid;
use App\Device;
use App\Firmware;
use App\Writer;
use App\DeviceCategory;
use App\Imports\EntriesImport;
use App\Modal;
use App\notifications;
use Illuminate\Container\EntryNotFoundException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\QueryException;

class FirmwareController extends Controller
{
    //
    public function show()
    {
        // Rows are served one page at a time by listData() (server-side DataTables).
        $url_type = self::getURLType();
        return view('view_firmware', ['firmwares' => collect(), 'url_type' => $url_type, 'server_side' => true]);
    }

    /**
     * Server-side DataTables source for the firmware listing (per category tab).
     */
    public function listData(Request $request)
    {
        $categoryId = (int) $request->input('category_id');
        $urlType = self::getURLType();

        $category = DeviceCategory::select('id', 'is_esim')->where('id', $categoryId)->first();

        $base = DB::table('firmware')->where('device_category_id', $categoryId);

        return \App\Support\ServerSideTable::respond($request, $base, [
            'idColumn' => 'firmware.id',
            'searchColumns' => ['firmware.name', 'firmware.configurations'],
            'sortable' => [
                1 => 'firmware.id',
                2 => 'firmware.name',
                11 => 'firmware.is_default',
                13 => 'firmware.created_at',
                14 => 'firmware.updated_at',
            ],
            'defaultOrder' => [['firmware.id', 'asc']],
            'fetchRows' => function (array $ids) {
                return Firmware::withCount('modals')->whereIn('id', $ids)->get();
            },
            'renderRow' => function ($firmware, $srNo) use ($urlType, $category) {
                $e = [\App\Support\ServerSideTable::class, 'e'];
                $config = json_decode($firmware->configurations);

                $esim = '-';
                if (isset($config->esim)) {
                    $esim = ($category && $category->is_esim == 1)
                        ? CommonHelper::getEsim($config->esim)
                        : $config->esim;
                }

                $actions = '<div class="fw-actions-cluster">'
                    . '<button type="button" class="btn fw-action-btn fw-btn-edit"'
                    . ' data-firmware-id="' . $firmware->id . '"'
                    . ' data-filename="' . $e($config->filename ?? '') . '"'
                    . ' data-version="' . $e($config->version ?? '') . '"'
                    . ' data-notes="' . $e(isset($config->releasingNotes) ? trim($config->releasingNotes) : '') . '"'
                    . ' onclick="openEditModel(this)"><i class="fa fa-pencil"></i>Edit</button>'
                    . '<form id="deleteForm-' . $firmware->id . '" action="" method="post">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="button" class="btn fw-action-btn fw-btn-delete" onclick="showDeleteModal(' . $firmware->id . ')"><i class="fa fa-trash"></i>Delete</button>'
                    . '</form></div>';

                return [
                    (string) $srNo,
                    '<span class="fw-id-pill">' . $firmware->id . '</span>',
                    '<span class="fw-name-cell">' . $e($firmware->name) . '</span>',
                    isset($config->country) ? $e(CommonHelper::getCountryName($config->country)) : '-',
                    isset($config->state) ? $e(CommonHelper::getStateName($config->state)) : '-',
                    $e($esim),
                    isset($config->backend) ? $e(CommonHelper::getBackend($config->backend)) : '-',
                    '<span class="fw-chip fw-file-chip">' . $e($config->filename ?? '-') . '</span>',
                    '<span class="fw-chip">' . $e($config->fileSize ?? '-') . '</span>',
                    '<span class="fw-chip">' . $e($config->version ?? '-') . '</span>',
                    '<a href="/admin/view-firmware-models/' . $firmware->id . '" class="btn fw-action-btn fw-btn-model"><i class="fa fa-eye"></i>View Model</a>',
                    $firmware->is_default == 1 ? '<span class="fw-default-pill">Yes</span>' : '',
                    '<span class="fw-model-count">' . (int) $firmware->modals_count . '</span>',
                    '<span class="fw-date-cell">' . CommonHelper::getDateAsTimeZone($firmware->created_at) . '</span>',
                    '<span class="fw-date-cell">' . CommonHelper::getDateAsTimeZone($firmware->updated_at) . '</span>',
                    $actions,
                ];
            },
        ]);
    }

    public function add()
    {
        $countries = DB::table('countries')->get();
        $esim = esim::get();
        $backend = backend::get();
        $url_type = self::getURLType();
        return view('add_firmware', ['esim' => $esim, 'backend' => $backend, 'countries' => $countries, 'url_type' => $url_type]);
    }
    public function edit(Request $request)
    {
        $request->validate([
            'firmwareFile' => 'required|file|mimes:bin|max:2048',
            'firmware_version' => 'required|string|max:255',
            'firmwareIdEdit' => 'required',
        ]);
        $firmware = Firmware::find($request->firmwareIdEdit);

        if (!$firmware) {
            return json_encode(['status' => 403, 'status_msg' => 'Firmware not found.']);
        }
        $config = json_decode($firmware->configurations);
        if ($request->hasFile('firmwareFile')) {
            $file = $request->file('firmwareFile');
            $fileSize = $file->getSize();
            $formatted = preg_replace('/\D/', '', $request->firmware_version);

            $filename =  $txtFileName = $this->formatWithLeadingZero($config->deviceCategory)
                . $this->formatWithLeadingZero($config->country)
                . $this->formatWithLeadingZero($config->state)
                . $this->formatWithLeadingZero($config->esim)
                . $this->formatWithLeadingZero($config->backend)
                . $this->formatWithLeadingZero($firmware->id)
                . $formatted . '.bin';
            $file->move(public_path('/fw'), $filename);
            $config->filename = $filename;
            $config->fileSize =  $fileSize;
        }

        $config->releasingNotes = $request->releasingNotes;

        $config->version = $request->input('firmware_version');
        $firmware->configurations = json_encode($config);
        $firmware->save();

        // $models = Modal::where('firmware_id', $firmware->id)->groupBy('user_id')->get();
        // if (count($models) > 0) {
        //     foreach ($models as $model) {
        //          $devices = DB::table('devices')->select('*')->where(['user_id'=>$model->user_id])->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.firmware_id')) = ?", $firmware->id)->get();
        //          dd($devices);
        //         if(isset($filename)){
        //             // dd($devices);
        //             foreach ($devices as $device) {
        //                 $configuration = json_decode($device->configurations,true);
        //                 $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
        //                 foreach ($commonFields as $index => $value) {
        //                     $key = strtolower(str_replace(' ', '_', $value->fieldName));
        //                     // if (isset($configuration[$key])) {
        //                         $converted[$key] = [
        //                             'id' => $value->id,
        //                             'value' => $configuration[$key]['value'] ?? ''
        //                         ];
        //                     // }
        //                 }


        //                 // echo  $configuration['firmware_file']['value'];
        //                 // echo $filename;
        //                 $converted['firmware_file']['value'] =  $filename;
        //                 $converted['firmware_version']['value'] =   $request->input('firmware_version');
        //                 $converted['firmwareFileSize']['value'] = $fileSize?? 0;
        //                 $converted['modelname']['value'] = $model->name;
        //                 $converted['vendorId']['value'] = $model->vendorId ?? 0;
        //                 // dd($converted);
        //                 //$configuration->modelName = $request->modalName;
        //                 $mergedConfig = array_merge($configuration, $converted);
        //                 // dd($mergedConfig);
        //                 $config = json_encode($mergedConfig);
        //                 // dd($config);
        //                 $updateDevice = DB::table('devices')->where('id', $device->id)->update(['configurations' => $config]);

        //             }
        //         }
        //         // $temp = [
        //         //     'user_id' => $model->user_id,
        //         //     'notification' => "New Version Avaliable of Firmware " .$firmware->name. ' version' . $config->version ,
        //         //     'firmware_id' => $firmware->id,
        //         //     'is_view' => 0
        //         // ];
        //         // notifications::create($temp);
        //         // unset($temp);
        //     }
        // }

        return json_encode(['status' => 200, 'status_msg' => $firmware->name . '- Firmware Updated Successfully']);
    }
    public function showBackend()
    {
        $backendList = Backend::withCount('firmwares')->get();
        $url_type = self::getURLType();
        return view('view_backend', ['backend' => $backendList, 'url_type' => $url_type]);
    }
    public function showEsim()
    {
        $esimList = Esim::withCount('ccids')->get();
        $url_type = self::getURLType();
        return view('view_esim', ['esimList' => $esimList, 'url_type' => $url_type]);
    }
    public function createEsim(Request $request)
    {
        if ($request->esimId == '') {
            $esim = [
                'name' => $request->esimName,
                'profile_1' => $request->esimProvider1,
                'profile_2' => $request->esimProvider2
            ];

            $ESim =  esim::create($esim);
            $getESim = esim::orderBy('id', 'desc')->get();
            return json_encode(['status' => 200, 'status_msg' => $ESim->name . '- Settings Added Successfully', 'esims' => $getESim]);
        } else {
            $edit = 1;
            $ESim =  esim::find($request->esimId);
            $ESim->name = $request->esimName;
            $ESim->profile_1 = $request->esimProvider1;
            $ESim->profile_2 = $request->esimProvider2;
            $ESim->save();
            return json_encode(['status' => 200, 'status_msg' => $ESim->name . '- ESim Updated Successfully']);
        }
    }
    public function deleteEsim($id)
    {
        $ESim =  esim::find($id);
        if (!$ESim) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $ESim->delete();
        return redirect()->back()->with(['error' => $ESim->name . 'Esim deleted Successfully']);
    }
    public function createBackend(Request $request)
    {
        if ($request->backendId == '') {
            $checkExists = backend::where('name', $request->name)->first();
            if ($checkExists) {
                return json_encode(['status' => 400, 'status_msg' => 'Backend with this name already exists.']);
            }
            $backend = [
                'name' => $request->name,
            ];
            $Backend =  backend::create($backend);
            $getBackendAll = backend::orderBy('id', 'desc')->get();
            return json_encode(['status' => 200, 'status_msg' => $Backend->name . '- Settings Added Successfully', 'backend' => $getBackendAll]);
        } else {
            $checkExists = backend::where('name', $request->name)->where('id', '!=', $request->backendId)->first();
            if ($checkExists) {
                return json_encode(['status' => 400, 'status_msg' => 'Backend with this name already exists.']);
            }
            $Backend =  backend::find($request->backendId);
            $Backend->name = $request->name;
            $Backend->save();

            return json_encode(['status' => 200, 'status_msg' => $Backend->name . '- Settings Updated Successfully']);
        }
    }
    public function deleteBackend($id)
    {
        $backend =  backend::find($id);
        if (!$backend) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $backend->delete();
        return redirect()->back()->with(['error' => $backend->name . ' deleted Successfully']);
    }
    public function deleteFirmware($id, $response)
    {
        try {
            $firmware =  Firmware::find($id);
            if (!$firmware) {
                return response()->json(['error' => 'Item not found.'], 404);
            }
            $devicecategoryName = DB::table('device_categories')->select('device_category_name')->where(['id' => $firmware->device_category_id])->first();
            if ($response == "true") {
                $modal = Modal::where(['firmware_id' => $id]);
                $modal->delete();
                $devices = DB::table('devices')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.firmware_id')) = ?", [$id])
                    ->get();
                foreach ($devices as $device) {
                    $configuration  = json_decode($device->configurations);
                    $configuration->firmware_id = "0";
                    $configuration->firmware_file = "0";
                    $configuration->firmware_version = "0";
                    $configuration->modelName = $devicecategoryName->device_category_name;
                    $configuration->vendorId = "0";
                    DB::table('devices')
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.firmware_id')) = ?", [$id])
                        ->update([
                            'configurations' => json_encode($configuration)
                        ]);
                }
            }
            $firmware->delete();

            return redirect()->back()->with(['error' => $firmware->name . ' deleted Successfully']);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'This firmware cannot be deleted because devices or other data still reference it.');
        }
    }
    public function deleteModal($id, $response)
    {
        $modal =  modal::find($id);
        if (!$modal) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        try {
            $firmware = DB::table('firmware')->select('device_category_id')->where(['id' => $modal->firmware_id])->first();
            $devicecategoryName = DB::table('device_categories')->select('device_category_name')->where(['id' => $firmware->device_category_id])->first();
            if ($response == "true") {
                $devices = DB::table('devices')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.modelName')) = ?", [$modal->name])
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.firmware_id')) = ?", [$modal->firmware_id])
                    ->get();

                foreach ($devices as $device) {
                    $configuration  = json_decode($device->configurations);
                    $configuration->modelName = $devicecategoryName->device_category_name;
                    $configuration->vendorId = "0";
                    DB::table('devices')
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.modelName')) = ?", [$modal->name])
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(configurations, '$.firmware_id')) = ?", [$modal->firmware_id])
                        ->update([
                            'configurations' => json_encode($configuration)
                        ]);
                }
            }
            $modal->delete();

            return redirect()->back()->with(['error' => $modal->name . ' deleted Successfully']);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'This model cannot be deleted because devices still reference it.');
        }
    }
    protected function formatWithLeadingZero($number)
    {
        // Ensure the number is treated as a string
        $number = (int)$number;

        // Format the number to be at least 2 digits, with leading zero if necessary
        return sprintf('%02d', $number);
    }
    public function createFirmware(Request $request)
    {
        // dd($request);
        $rules = [
            'name' => 'required',
            'deviceCategory' => 'required',
            'country' => 'required',
            'state' => 'required',
            'backend' => 'required',
            'firmwareFile' => 'required|file|mimetypes:application/octet-stream|max:20480',
            'firmware_version' => 'required',
        ];

        if ($request->esimRequired == 1) {
            $rules['esim'] = 'required';
        }

        $validated = $request->validate($rules);
        if ($validated) {
            if ($request->hasFile('firmwareFile')) {
                $file = $request->file('firmwareFile');

                if ($file && $file->isValid()) {
                    $fetchLastFirmwareId = Firmware::max('id');
                    $formatted = preg_replace('/\D/', '', $request->firmware_version);
                    $txtFileName = $this->formatWithLeadingZero($request->deviceCategory)
                        . $this->formatWithLeadingZero($request->country)
                        . $this->formatWithLeadingZero($request->state)
                        . $this->formatWithLeadingZero($request->esim)
                        . $this->formatWithLeadingZero($request->backend)
                        . $this->formatWithLeadingZero($fetchLastFirmwareId + 1)
                        . $formatted . '.bin';

                    $destinationPath = public_path('/fw');
                    $txtFilePath = $this->convertBinToTxt($file, $destinationPath, $txtFileName);
                } else {
                    return redirect()->back()->withInput()->withErrors(['firmwareFile' => 'Uploaded file is not valid.']);
                }

                $checkifFirst = Firmware::where('device_category_id', $request->deviceCategory)->count();
                $set_val = ($checkifFirst == 0) ? 1 : 0;

                $arr = [
                    'name' => $request->name,
                    'device_category_id' => $request->deviceCategory,
                    'backend_id' => $request->backend,
                    'configurations' => json_encode([
                        'deviceCategory' => $request->deviceCategory,
                        'country' => $request->country,
                        'state' => $request->state,
                        'esim' => $request->esimRequired == 1 ? $request->esim : "N/A",
                        'backend' => $request->backend,
                        'filename' => $txtFilePath,
                        'fileSize' => $request->fileSize,
                        'releasingNotes' => $request->releasingNotes,
                        'version' => $request->firmware_version,
                    ]),
                    'is_default' => $set_val
                ];
                // dd($arr);
                $firmware = Firmware::create($arr);

                return redirect()->back()->with('success', $firmware->name . ' Firmware Created Successfully!!');
            }
        }

        // If no file was uploaded, redirect back with the input values
        return redirect()->back()->withInput()->withErrors(['firmwareFile' => 'Firmware file is required.']);
    }


    protected function convertBinToTxt($file, $destinationPath, $txtFileName)
    {
        $binaryContent = file_get_contents($file->getPathname());
        $textContent = $binaryContent;
        $txtFilePath = $destinationPath . '/' . $txtFileName;
        file_put_contents($txtFilePath, $textContent);
        return $txtFileName;
    }
    public function getStateByCountryCode(Request $request)
    {
        $countries = DB::table('countries')->where('id', $request->id)->first();

        if ($countries) {
            $states = DB::table('states')->where('country_id', $countries->id)->get();
            if ($states) {
                return json_encode(['status' => 200, 'message' => 'sucesss', 'states' => json_encode($states)]);
            }
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }

        return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
    }
    public function createModal(Request $request)
    {
        $arr = [
            'name' => $request->modalName,
            'vendorId' => $request->vendorId,
            'user_id' => $request->userAssign,
            'firmware_id' => $request->firmwareId
        ];
        // $firmware = DB::table('firmware')->select('*')->where(['id' => $request->firmwareId])->first();
        // $firmwareConfiguration  = json_decode($firmware->configurations);
        // $devices = DB::table('devices')->select('*')->where(['user_id'=>$request->userAssign])->get();
        // foreach ($devices as $device) {
        //     $configuration = json_decode($device->configurations,true);
        //     $configuration['firmware_id']['value'] = $request->firmwareId;
        //     $configuration['vendorId']['value'] = $request->vendorId;
        //     $configuration['firmware_file']['value'] =  $firmwareConfiguration->filename;
        //     $configuration['firmware_version']['value'] =  $firmwareConfiguration->version;
        //     $configuration['firmwareFileSize']['value'] = $firmwareConfiguration->fileSize ? $firmwareConfiguration->fileSize : 0;
        //     $configuration['modelName']['value'] = $request->modalName;
        //     $config = json_encode($configuration);
        //     DB::table('devices')->where('id', $device->id)->update(['configurations' => $config]);
        // }
        $modal = Modal::create($arr);

        return json_encode(['status' => 200, 'status_msg' => $modal->name . '- Modal Added Successfully']);
    }

    public function viewModals()
    {
        // Rows are served one page at a time by modelsListData().
        $url_type = self::getURLType();
        return view('view_modal', ['modalList' => collect(), 'url_type' => $url_type, 'server_side' => true]);
    }

    /**
     * Server-side DataTables source for the models listing.
     * Accepts an optional firmware_id to scope to one firmware's models.
     */
    public function modelsListData(Request $request)
    {
        $base = DB::table('modals')
            ->leftJoin('writers', 'writers.id', '=', 'modals.user_id')
            ->leftJoin('firmware', 'firmware.id', '=', 'modals.firmware_id');

        $firmwareId = (int) $request->input('firmware_id');
        if ($firmwareId > 0) {
            $base->where('modals.firmware_id', $firmwareId);
        }

        $urlType = self::getURLType();

        return \App\Support\ServerSideTable::respond($request, $base, [
            'idColumn' => 'modals.id',
            'searchColumns' => ['modals.name', 'modals.vendorId', 'writers.name', 'firmware.name'],
            'sortable' => [
                1 => 'modals.name',
                2 => 'modals.vendorId',
                3 => 'writers.name',
                4 => 'firmware.name',
                5 => 'modals.created_at',
                6 => 'modals.updated_at',
            ],
            'fetchRows' => function (array $ids) {
                return DB::table('modals')
                    ->leftJoin('writers', 'writers.id', '=', 'modals.user_id')
                    ->leftJoin('firmware', 'firmware.id', '=', 'modals.firmware_id')
                    ->select('modals.id', 'modals.name', 'modals.vendorId', 'modals.created_at', 'modals.updated_at',
                        'writers.name as user_name', 'firmware.name as firmware_name')
                    ->whereIn('modals.id', $ids)
                    ->get();
            },
            'renderRow' => function ($modal, $srNo) {
                $e = [\App\Support\ServerSideTable::class, 'e'];

                return [
                    (string) $srNo,
                    $e($modal->name),
                    $e($modal->vendorId),
                    $e($modal->user_name ?? ''),
                    $e($modal->firmware_name ?? ''),
                    CommonHelper::getDateAsTimeZone($modal->created_at),
                    CommonHelper::getDateAsTimeZone($modal->updated_at),
                    '<form id="deleteForm-' . $modal->id . '" action="" method="post">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="button" class="btn btn-danger btn-sm btn-vm-delete" title="Delete" aria-label="Delete" onclick="showDeleteModal(' . $modal->id . ')"><i class="fa fa-trash" aria-hidden="true"></i></button>'
                        . '</form>',
                ];
            },
        ]);
    }
    public function getModelName(Request $request)
    {
        $user = Auth::user();
        $firmware_id = $request->firmware_id;
        $user_id = $request->user_id; // Selected account ID
        $category_id = $request->category_id;

        if ($category_id && !$this->deviceCategoryAccess()->userHasCategory($user, $category_id)) {
            return response()->json([
                'status' => 403,
                'message' => 'You do not have access to this device category. Please contact your administrator.',
            ], 403);
        }

        $firmware = Firmware::find($firmware_id);
        $firmwareFileSize = 0;
        if ($firmware) {
            $config = json_decode($firmware->configurations);
            $firmwareFileSize = $config->fileSize ?? 0;
        }

        $device = $request->device_id ? \App\Device::find($request->device_id) : null;
        if ($device && !$this->deviceCategoryAccess()->userCanAccessDevice($user, $device)) {
            return response()->json([
                'status' => 403,
                'message' => 'You do not have access to this device. Please contact your administrator.',
            ], 403);
        }

        $target_user_id = ($user_id && $user_id != "" && $user_id != "No User Found") ? $user_id : $user->id;

        $modal = \App\Helper\CommonHelper::getModelByHierarchy($device, $firmware_id, $target_user_id, $category_id);

        if (!$modal) {
            if ($target_user_id == Auth::id()) {
                // For Unassigned devices (Admin's hierarchy search), use category name as model and "JSD a" as vendor
                $categoryName = \App\Helper\CommonHelper::getDeviceCategoryName($category_id);
                return response()->json([
                    'status' => 200,
                    'modal' => [
                        'name' => $categoryName,
                        'vendorId' => "JSD"
                    ],
                    'firmwareFileSize' => $firmwareFileSize
                ]);
            }

            return response()->json([
                'status' => 400,
                'message' => 'Model and Firmware combination does not exist. Please contact the administrator.',
                'firmwareFileSize' => $firmwareFileSize
            ]);
        }

        return response()->json([
            'status' => 200,
            'modal' => $modal,
            'firmwareFileSize' => $firmwareFileSize
        ]);
    }
    public function uploadEsim(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);
        $file = $request->file('csv_file');
        $rows = Excel::toArray(new EntriesImport, $file);
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {
                $checkESimExist =  esim::where(['name' => $value[1], 'profile_1' => $value[2], 'profile_2' => $value[3]])->first();
                if (!$checkESimExist) {
                    $esim = [
                        'name' => $value[1],
                        'profile_1' => $value[2],
                        'profile_2' => $value[3],
                    ];
                    $ESim =  esim::create($esim);
                    $esimID = $ESim->id;
                } else {
                    $esimID  = $checkESimExist->id;
                }
                $checkIfCcid = Ccid::where(['ccid' => $value[0]])->first();
                if (!$checkIfCcid) {
                    $arr = [
                        'ccid' => $value[0],
                        'customer_name' => $value[4],
                        'esim' => $esimID,

                    ];
                    $ccid = Ccid::create($arr);
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file imported successfully.');
    }
    public function esimCustomer(Request $request)
    {
        $esimCustomer = Ccid::get();
        $url_type = self::getURLType();
        return view('view_esim_customer', ['esimCustomer' => $esimCustomer, 'url_type' => $url_type]);
    }
    public function deleteEsimCustomer($id)
    {
        $ccid =  Ccid::find($id);
        if (!$ccid) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $ccid->delete();
        return redirect()->back()->with(['error' => $ccid->name . 'Esim deleted Successfully']);
    }
    public function updateFirmwareDevices(Request $request)
    {

        $notification = notifications::find($request->notification_id);
        $master_id = Auth::user()->id;
        $firmware = Firmware::find($request->firmware_id);
        $firmwareConfiguration = json_decode($firmware->configurations);
        $devices = Device::whereRaw("JSON_EXTRACT(configurations, '$.firmware_id') = '" . $request->firmware_id . "'")->where(['user_id' => $master_id])->get();
        foreach ($devices as $device) {

            $configuration = json_decode($device->configurations);

            $configuration->firmware_file = $firmwareConfiguration->filename;
            $configuration->firmware_version = $firmwareConfiguration->version;
            $device->configurations = json_encode($configuration);
            // Save the updated device record
            $device->save();
        }
        $notification->is_view = 1;
        $notification->save();
        return json_encode(['status' => 200]);
    }
    public function getModelById($id, $firmwareId)
    {
        $modal = Modal::where(['user_id' => $id, 'firmware_id' => $firmwareId])->first();
        return json_encode(['status' => 200, 'modal' => $modal]);
    }

    public function updateModal(Request $request)
    {
        $record = modal::find($request->modalId);

        if ($record) {
            $record->update([
                'name' => $request->modalName,
                'vendorId' => $request->vendorId,
            ]);
            // $firmware = DB::table('firmware')->select('*')->where(['id' => $request->firmwareId])->first();
            // $firmwareConfiguration  = json_decode($firmware->configurations);
            // $devices = DB::table('devices')->select('*')->where(['user_id'=>$request->userAssign,'device_category_id' => $firmware->device_category_id])->get();
            // $dataFields = DataFields::select("*")->where(['is_common'=> 1])->get();
            // $converted =[];

            // foreach ($dataFields as $value) {
            //     $fieldName = $value->fieldName;

            //     // Convert to snake_case
            //     $key = strtolower(str_replace(' ', '_', $fieldName));

            //     $converted[$key] = [
            //         'id' => $value->id,
            //         'value' => ''
            //     ];
            // }

            // foreach ($devices as $device) {

            //     $configuration = array_merge(json_decode($device->configurations, true), $converted);
            //     $configuration['firmware_id']['value'] = $request->firmwareId;

            //     $configuration['vendorId']['value'] = $request->vendorId;

            //     $configuration['firmware_file']['value'] =  $firmwareConfiguration->filename;
            //     $configuration['firmware_version']['value'] =  $firmwareConfiguration->version;
            //     $configuration['firmwareFileSize']['value'] = $firmwareConfiguration->fileSize ? $firmwareConfiguration->fileSize : 0;
            //     $configuration['ping_interval']['value'] = 4;
            //     $configuration['is_editable']['value'] = 1;
            //     // dd($configuration);
            //     $config = json_encode($configuration);

            //     DB::table('devices')->where('id', $device->id)->update(['configurations' => $config]);
            // }

            return json_encode([
                'status' => 200,
                'message' => 'Record updated successfully',
                'data' => $record,
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Record not found',
        ], 404);
    }
    public function viewFirmwareModel($id)
    {
        $modalList = collect(); // rows served by modelsListData(), scoped by firmware_id
        $deviceCategoryID = DB::table('firmware')->select("device_category_id")->where('id', $id)->first();
        // $getUser = DB::table('writers as w')
        // ->select('w.id', 'w.name')
        // ->leftJoin('modals as ml', 'w.id', '=', 'ml.user_id')
        // ->where('w.created_by', Auth::id())
        // ->where('w.is_deleted', 0)
        // ->whereRaw("FIND_IN_SET(?, w.device_category_id)", [$deviceCategoryID->device_category_id])
        // ->whereNull('ml.user_id')
        // ->get();
        $getUser = Writer::select('id', 'name')
            //->where('created_by', Auth::user()->id)
            ->where('created_by', Auth::user()->id)
            ->where('user_type', '!=', 'Support')
            ->where('is_deleted', '0')
            ->whereRaw("FIND_IN_SET(?, device_category_id)", [$deviceCategoryID->device_category_id])
            ->get();
        $url_type = self::getURLType();
        return view('view_modal', ['modalList' => $modalList, 'url_type' => $url_type, 'firmware_id' => $id, 'users' => $getUser, 'server_side' => true]);
    }
    public function getFirmwareWithModel(Request $request)
    {
        $user_id = $request->user_id;
        $category_id = $request->category_id;
        $auth_user = Auth::user();

        // 1. Determine the hierarchy chain for the target user (or current user if none selected)
        $target_id = ($user_id && $user_id != "" && $user_id != "No User Found") ? $user_id : $auth_user->id;
        $target_user = Writer::find($target_id);

        $search_ids = [$target_id];
        if ($target_user) {
            // Traverse up the created_by chain
            $current_parent = $target_user->created_by;
            while ($current_parent && $current_parent != "1" && count($search_ids) < 5) {
                $search_ids[] = $current_parent;
                $p_user = Writer::find($current_parent);
                $current_parent = $p_user ? $p_user->created_by : null;
            }
        }

        // 2. Fetch firmwares that have models defined for any user in the hierarchy
        $firmwares = Firmware::where('is_deleted', 0)
            ->whereIn('id', function ($query) use ($search_ids) {
                $query->select('firmware_id')
                    ->from('modals')
                    ->whereIn('user_id', $search_ids);
            });

        if ($category_id) {
            $firmwares->where('device_category_id', $category_id);
        }

        $firmwareList = $firmwares->get(['id', 'name']);

        // 3. Fallback for Admin or if list is empty
        if ($firmwareList->isEmpty()) {
            if ($auth_user->user_type == 'Admin' || $auth_user->user_type == 'Support') {
                $query = Firmware::where('is_deleted', 0);
                if ($category_id) {
                    $query->where('device_category_id', $category_id);
                }
                $firmwareList = $query->get(['id', 'name']);
            } else {
                // If it's a reseller/user and still empty, check if Admin has defined models for the direct Reseller (L1)
                $l1_reseller_id = end($search_ids);
                $firmwareList = Firmware::where('is_deleted', 0)
                    ->whereIn('id', function ($query) use ($l1_reseller_id) {
                        $query->select('firmware_id')
                            ->from('modals')
                            ->where('user_id', $l1_reseller_id);
                    })
                    ->when($category_id, function($q) use ($category_id) {
                        return $q->where('device_category_id', $category_id);
                    })
                    ->get(['id', 'name']);
            }
        }

        // Final fallback: show all category firmwares if Admin and still empty
        if ($firmwareList->isEmpty() && ($auth_user->user_type == 'Admin' || $auth_user->user_type == 'Support')) {
             $query = Firmware::where('is_deleted', 0);
             if ($category_id) $query->where('device_category_id', $category_id);
             $firmwareList = $query->get(['id', 'name']);
        }

        return response()->json([
            'status' => 200,
            'firmwareList' => $firmwareList
        ]);
    }
    public function getFirmware(Request $request)
    {
        $firmwares = Firmware::get();

        return response()->json([
            'status' => 200,
            'firmwareList' => $firmwares
        ]);
    }
}
