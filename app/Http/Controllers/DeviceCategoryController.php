<?php

namespace App\Http\Controllers;

use App\Device;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\DeviceCategory;
use App\Firmware;
use App\Template;
use App\Writer;
use PDO;
use App\DataFields;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class DeviceCategoryController extends Controller
{
    public function index()
    {
        $users = DB::table('writers')->select('id', 'name')->get();
        $dataFields = DataFields::where(['fieldType' => 0])->get();
        $dataFieldsParams = DataFields::where(['fieldType' => 1])->get();
        $url_type = self::getURLType();

        return view('add_deviceCategory', [
            'users' => $users,
            'url_type' => $url_type,
            'dataFields' => $dataFields,
            'dataFieldsParams' => $dataFieldsParams
        ]);
    }

    public function store(Request $request)
    {

        $tempConfiguration = $request->dynamicField;
        $nameParameters = $request->nameParameters;
        $idParameters = $request->idParameters;
        $converted = [];
        foreach ($nameParameters as $index => $name) {
            $key = strtolower(str_replace(' ', '_', $name));
            if (isset($tempConfiguration[$key])) {
                $converted[$key] = [
                    'id' => $idParameters[$index],
                    'value' => $tempConfiguration[$key]
                ];
            }
        }
        $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
        foreach ($commonFields as $index => $value) {
            $key = strtolower(str_replace(' ', '_', $value->fieldName));

            $converted[$key] = [
                'id' => $value->id,
                'value' => $tempConfiguration[$key] ?? ''
            ];
        }
        $converted['ping_interval']['value'] = 4;
        $converted['is_editable']['value'] = 1;
        $tempConfiguration = $converted;
        $validatedData = $request->validate([
            'deviceName' => 'required',
            'arai_tac_no' => 'required_if:is_certification_enable,on|string|max:255',
            'arai_date' => 'required_if:is_certification_enable,on|date',
            'certification_model_name' => 'required_if:is_certification_enable,on|string|max:255',
        ]);
        $isCertificationEnabled = $request->is_certification_enable == 'on';
        $name = $request->nameParameters;
        $id = $request->idParameters;
        $type = $request->inputType;
        $selectOptions = $request->selectOptions;
        $selectValues = $request->selectValues;
        $numberInput = $request->numberInput;
        $defaultParameters = $request->defaultParameters;
        $defaultValue = $request->default;
        $requiredFieldInput = $request->inputFieldRequired;
        $result = [];

        //  dd($numberInput);
        for ($i = 0; $i < count($name); $i++) {
            $options = [];
            $values = [];
            if (isset($selectOptions[$i]) && !is_null($selectOptions[$i]) && $type[$i] == 'select') {
                foreach ($selectOptions[$i] as $key => $option) {
                    if (!empty($option)) {
                        $options[] = ['option' => $option, 'value' => $selectValues[$i][$key]];
                    }
                }
                $result[] = ['selectOptions' => $options];
            }
            $result[] = ['id' => $id[$i], 'key' => $name[$i], 'default' => $defaultValue[$i], 'type' => $type[$i], 'requiredFieldInput' => $requiredFieldInput[$i]];
            // 'numberRange' => $type[$i] == 'number' ? $numberInput[$i] : []
        }
        // dd($result);
        $deviceCategory = DeviceCategory::create([
            'device_category_name' => $request->deviceName,
            'inputs' => json_encode($result),
            'is_esim' => $request->is_esim == 'on' ? 1 : 0,
            'is_certification_enable' => $isCertificationEnabled ? 1 : 0,
            'arai_tac_no' => $isCertificationEnabled ? $request->arai_tac_no : null,
            'arai_date' => $isCertificationEnabled ? $request->arai_date : null,
            'certification_model_name' => $isCertificationEnabled ? $request->certification_model_name : null,
        ]);
        Template::create([
            'template_name' => $request->template_name,
            'configurations' => json_encode($tempConfiguration),
            'device_category_id' => $deviceCategory->id,
            'verify' => 1,
            'default_template' => 1
        ]);
        return back()->with(['status' => 200, 'success' => 'Device Added Successfully.']);
    }

    // public function store(Request $request)
    // {
    //     // dd($request);
    //     $tempConfiguration = $request->dynamicField;
    //     $nameParameters = $request->nameParameters;
    //     $idParameters = $request->idParameters;
    //     $converted = [];
    //     foreach ($nameParameters as $index => $name) {
    //         $key = strtolower(str_replace(' ', '_', $name)); // normalize to match $tempConfiguration
    //         if (isset($tempConfiguration[$key])) {
    //             $converted[$key] = [
    //                 'id' => $idParameters[$index],
    //                 'value' => $tempConfiguration[$key]
    //             ];
    //         }
    //     }
    //     $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
    //     foreach ($commonFields as $index => $value) {
    //         $key = strtolower(str_replace(' ', '_', $value->fieldName));
    //        if($key == 'ping_interval' || $key == 'is_editable' ){
    //             $converted[$key] = [
    //                 'id' => $value->id,
    //                 'value' => $tempConfiguration[$key] ?? ''
    //             ];
    //         }
    //     }
    //     // dd($commonFields);
    //     // dd($converted);
    //     $converted['ping_interval']['value'] = 4;
    //     $converted['is_editable']['value'] = 1;
    //     $tempConfiguration = $converted;
    //     // // Validate incoming requests if needed
    //     $validatedData = $request->validate([
    //         'deviceName' => 'required',
    //     ]);
    //     // Assuming $name and $inputDefaultValue are obtained from $request or any other source
    //     $name = $request->nameParameters;
    //     $id = $request->idParameters;
    //     $type = $request->inputType;
    //     $selectOptions = $request->selectOptions;
    //     $selectValues = $request->selectValues;
    //     $numberInput = $request->numberInput;
    //     // $parameter = $request->nameParameters;
    //     $defaultParameters = $request->defaultParameters;
    //     $defaultValue = $request->default;  
    //     //  echo "<pre>";
    //     // print_r( $name);
    //     // die("i'm here");

    //     $requiredFieldInput = $request->inputFieldRequired;
    //     $result = [];
    //     // $parametersResult = [];
    //     // for ($i=0 ; $i<count($parameter);$i++){
    //     //     $parametersResult[] = ['key' => $parameter[$i]];
    //     // }


    //     for ($i = 0; $i < count($name); $i++) {
    //         $options = [];
    //         $values = [];
    //         // Check if selectOptions[$i] is set and not null
    //         if (isset($selectOptions[$i]) && !is_null($selectOptions[$i]) && $type[$i] == 'select') {
    //             // Loop through each option in selectOptions[$i]

    //             foreach ($selectOptions[$i] as $key => $option) {
    //                 // Add non-empty options to the $options array
    //                 if (!empty($option)) {
    //                     $options[] = ['option' => $option, 'value' => $selectValues[$i][$key]];
    //                 }
    //             }
    //         }
    //         // dd( $requiredFieldInput[$i]);
    //         // if (!isset($requiredFieldInput[$i])) {
    //         //     $requiredFieldInput[$i] = 'off';
    //         // }
    //         $result[] = ['id'=> $id[$i],'key' => $name[$i],'default' => $defaultValue[$i],'type' => $type[$i],'requiredFieldInput' => $requiredFieldInput[$i], 'numberRange' => $type[$i] == 'number' ? $numberInput[$i] : [], 'selectOptions' => $options];
    //     }

    //     // dd($result);

    //     $deviceCategory = DeviceCategory::create([
    //         'device_category_name' => $request->deviceName,
    //         'inputs' => json_encode($result),
    //         // 'parameters' =>json_encode($parametersResult),
    //         'is_esim' => $request->is_esim == 'on'? 1: 0
    //     ]);
    //     Template::create([
    //         'template_name' => $request->template_name,
    //         'configurations' => json_encode($tempConfiguration),
    //         'device_category_id' => $deviceCategory->id,
    //         'verify' => 1,
    //         'default_template' => 1
    //     ]);
    //     return back()->with(['status' => 200, 'success' => 'Device Added Successfully.']);
    // }

    public function show()
    {
        $user = Auth::user();
        if ($user->user_type == 'Admin') {
            $deviceCategories = DB::table('device_categories')
                ->select(
                    'device_categories.*',
                    DB::raw('(SELECT COUNT(*) FROM templates WHERE templates.device_category_id = device_categories.id AND templates.is_deleted = 0) as templates_count'),
                    DB::raw('(SELECT COUNT(*) FROM writers WHERE FIND_IN_SET(device_categories.id, writers.device_category_id) > 0 AND writers.is_deleted = 0) as writers_count'),
                    DB::raw('(SELECT COUNT(*) FROM devices WHERE devices.device_category_id = device_categories.id AND devices.is_deleted = 0) as devices_count'),
                    DB::raw('(SELECT COUNT(*) FROM firmware WHERE firmware.device_category_id = device_categories.id) as firmware_count')
                )
                ->where('device_categories.is_deleted', '0')
                ->get();
        } else if ($user->user_type == 'Reseller') {
            $master_id = Auth::id();

            // Step 1: Get device category IDs from writers created by this user
            $writerCategories = DB::table('writers')
                ->where('is_deleted', 0)
                ->where('id', $master_id)
                ->pluck('device_category_id')
                ->toArray();
            // Step 2: Flatten comma-separated values into an array
            $categoryIds = collect($writerCategories)
                ->flatMap(function ($ids) {
                    return explode(',', $ids);
                })
                ->unique()
                ->filter()
                ->values()
                ->toArray();
            // Step 3: Use whereIn() to fetch only those categories
            $deviceCategories = DB::table('device_categories')
                ->select(
                    'device_categories.*',
                    DB::raw("(
                    SELECT COUNT(*) 
                    FROM templates 
                    WHERE templates.device_category_id = device_categories.id 
                        AND templates.is_deleted = 0 
                        AND templates.id_user = $master_id
                ) as templates_count"),
                    DB::raw("(
                    SELECT COUNT(*) 
                    FROM writers 
                    WHERE FIND_IN_SET(device_categories.id, writers.device_category_id) > 0 
                        AND writers.is_deleted = 0 
                        AND writers.created_by = $master_id
                ) as writers_count"),
                    DB::raw("(
                    SELECT COUNT(*) 
                    FROM devices 
                    WHERE devices.device_category_id = device_categories.id 
                        AND devices.is_deleted = 0 
                        AND devices.user_id = $master_id
                ) as devices_count"),
                    DB::raw("(
                    SELECT COUNT(*) 
                    FROM firmware 
                    WHERE firmware.device_category_id = device_categories.id
                ) as firmware_count")
                )
                ->where('device_categories.is_deleted', 0)
                ->whereIn('device_categories.id', $categoryIds)
                ->get();

            // $deviceCategories = DB::table('device_categories')
            // ->select(
            //     'device_categories.*',
            //     DB::raw("(
            //         SELECT COUNT(*) 
            //         FROM templates 
            //         WHERE templates.device_category_id = device_categories.id 
            //             AND templates.is_deleted = 0 
            //             AND templates.id_user = $master_id
            //     ) as templates_count"),
            //     DB::raw("(
            //         SELECT COUNT(*) 
            //         FROM writers 
            //         WHERE FIND_IN_SET(device_categories.id, writers.device_category_id) > 0 
            //             AND writers.is_deleted = 0 
            //             AND writers.created_by = $master_id
            //     ) as writers_count"),
            //     DB::raw("(
            //         SELECT COUNT(*) 
            //         FROM devices 
            //         WHERE devices.device_category_id = device_categories.id 
            //             AND devices.is_deleted = 0 
            //             AND devices.user_id = $master_id
            //     ) as devices_count"),
            //     DB::raw("(
            //         SELECT COUNT(*) 
            //         FROM firmware 
            //         WHERE firmware.device_category_id = device_categories.id
            //     ) as firmware_count")
            // )
            // ->where('device_categories.is_deleted', 0)
            // ->whereExists(function ($query) use ($master_id) {
            //     $query->select(DB::raw(1))
            //         ->from('writers')
            //         ->whereRaw("FIND_IN_SET(device_categories.id, writers           .device_category_id) > 0")
            //         ->where('writers.is_deleted', 0)
            //         ->where('writers.created_by', $master_id);
            // })
            // ->get();


            // $master_id = Auth::id(); // Current logged-in user ID

            // $deviceCategories = DB::table('device_categories')
            // ->select('device_categories.*',DB::raw("(SELECT COUNT(*) FROM templates WHERE templates.device_category_id = device_categories.id AND templates.is_deleted = 0 AND templates.created_by = $master_id) as templates_count"),DB::raw("(SELECT COUNT(*) FROM writers WHERE FIND_IN_SET(device_categories.id, writers.device_category_id) > 0 AND writers.is_deleted = 0 AND writers.created_by = $master_id) as writers_count"),DB::raw("(SELECT COUNT(*) FROM devices WHERE devices.device_category_id = device_categories.id AND devices.is_deleted = 0 AND devices.created_by = $master_id) as devices_count"),
            //     DB::raw("(
            //         SELECT COUNT(*) 
            //         FROM firmware 
            //         WHERE firmware.device_category_id = device_categories.id 
            //             AND firmware.created_by = $master_id
            //     ) as firmware_count")
            // )
            // ->where('device_categories.is_deleted', 0)
            // ->whereExists(function ($query) {
            //     $query->select(DB::raw(1))
            //         ->from('writers')
            //         ->whereRaw("FIND_IN_SET(device_categories.id, writers.device_category_id) > 0")
            //         ->where('writers.is_deleted', 0);
            // })
            // ->get();

        }
        return view('view_deviceCategory', ['device_categories' => $deviceCategories]);
    }

    public function restore()
    {
        $device_categories = DB::table('device_categories')
            ->where('is_deleted', '1')
            ->get();
        return view('restore_DeviceCategory', ['device_categories' => $device_categories]);
    }
    public function restoreDeviceCategory($id)
    {
        $device_category = DeviceCategory::find($id);
        $device_category->is_deleted = 0;
        $device_category->save();
        return back()->with('success', $device_category->device_category_name . ' Device Category Restore Successfully');
    }
    public function update($id)
    {
        $device_category = DeviceCategory::find($id);
        $device_category->inputs = json_decode($device_category->inputs);
        $device_category->parameters = json_decode($device_category->parameters);
        $dataFields = DataFields::where(['fieldType' => 0])->get();
        $dataFieldsParams = DataFields::where(['fieldType' => 1])->get();
        $url_type = self::getURLType();
        return view('edit_deviceCategory', [
            'device_category' => $device_category,
            'url_type' => $url_type,
            'dataFields' => $dataFields,
            'dataFieldsParams' => $dataFieldsParams
        ]);
    }
    public function normalize($string)
    {
        $string = strtolower($string);

        // Replace spaces with underscores
        $string = str_replace(' ', '_', $string);

        // Remove all characters except a-z, 0-9, and underscores
        return preg_replace('/[^a-z0-9_]/', '', $string);
    }
    public function updateDeviceCategory(Request $request)
    {
        $request->validate([
            'deviceName' => 'required',
            'arai_tac_no' => 'required_if:is_certification_enable,on|string|max:255',
            'arai_date' => 'required_if:is_certification_enable,on|date',
            'certification_model_name' => 'required_if:is_certification_enable,on|string|max:255',
        ]);
        $isCertificationEnabled = $request->is_certification_enable == 'on';
        $device_category = DeviceCategory::find($request->device_id);
        $device_category->device_category_name = $request->deviceName;
        $device_category->is_esim = $request->is_esim == "on" ? 1 : 0;
        $device_category->is_can_protocol = $request->is_can_enable == "on" ? 1 : 0;
        $device_category->is_certification_enable = $isCertificationEnabled ? 1 : 0;
        $device_category->arai_tac_no = $isCertificationEnabled ? $request->arai_tac_no : null;
        $device_category->arai_date = $isCertificationEnabled ? $request->arai_date : null;
        $device_category->certification_model_name = $isCertificationEnabled ? $request->certification_model_name : null;
        $nameParameters = $request->nameParameters;
        $idParameters = $request->idParameters;
        $defaultValues = $request->default;
        $templates = Template::where('device_category_id', $request->device_id)
            ->where('is_deleted', '0')
            ->get();
        $devices =  Device::where('device_category_id', $request->device_id)->get();
        $accounts = Writer::whereRaw("FIND_IN_SET(?, device_category_id)", [$request->device_id])->get();
        foreach ($accounts as $account) {
            $device_cat_ids = explode(",", $account->device_category_id);
            $config = json_decode($account->configurations, true);

            foreach ($device_cat_ids as $key => $cat_id) {
                if (!isset($config[$key])) continue;

                $section = $config[$key];
                $idToKeyMap = [];
                $uniqueSection = [];

                foreach ($section as $indexKey => $data) {
                    if (!isset($data['id'])) continue;

                    $id = $data['id'];

                    if (!isset($idToKeyMap[$id])) {
                        $idToKeyMap[$id] = $indexKey;
                        $uniqueSection[$indexKey] = $data;
                    } else {
                        $existingKey = $idToKeyMap[$id];
                    }
                }
                $newConfig = [];
                foreach ($nameParameters as $index => $param) {
                    $normalizedKey = strtolower(str_replace(' ', '_', $param));
                    if (isset($uniqueSection[$normalizedKey])) {
                        $newConfig[$normalizedKey] = $uniqueSection[$normalizedKey];
                    } else {
                        $found = 0;
                        foreach ($uniqueSection as $key1 => $section) {
                            if ($section['id'] == $idParameters[$index]) {
                                $found = 1;
                                $newConfig[$normalizedKey] = [
                                    'id' => $idParameters[$index],
                                    'value' => $section['value'] ?? ''
                                ];
                                unset($uniqueSection[$key1]);
                            }
                        }
                        if ($found == 0) {
                            $newConfig[$normalizedKey] = [
                                'id' => $idParameters[$index],
                                'value' => $defaultValues[$index] ?? ''
                            ];
                        }
                    }
                }

                $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
                foreach ($commonFields as $index => $value) {
                    if (strpos($value->fieldName, ' ') == true) {
                        $indexkey = strtolower(str_replace(' ', '_', $value->fieldName));
                    } else {
                        $indexkey = lcfirst(str_replace(' ', '_', $value->fieldName));
                    }
                    if ($indexkey == 'ping_interval' || $indexkey == 'is_editable') {
                        $newConfig[$indexkey] = [

                            'id' => $value->id,
                            'value' => $uniqueSection[$indexkey]['value'] ?? ''
                        ];
                    }
                }
                $config[$key] = $newConfig;
            }
            $account->configurations = json_encode($config);
            $account->update();
        }
        foreach ($devices as $device) {
            $config = json_decode($device->configurations, true);
            $newDeviceConfig = [];
            foreach ($nameParameters as $index => $param) {
                $normalizedKey = strtolower(str_replace(' ', '_', $param));
                if (isset($config[$normalizedKey])) {
                    // If exists in original config, use it
                    $newDeviceConfig[$normalizedKey] = $config[$normalizedKey];
                } else {
                    // Else use provided default and ID
                    $found = 0;
                    foreach ($config as $key1 => $section) {
                        if (isset($section['id']) && $section['id'] == $idParameters[$index]) {
                            $found = 1;
                            $newDeviceConfig[$normalizedKey] = [
                                'id' => $idParameters[$index],
                                'value' => $section['value'] ?? ''
                            ];
                            unset($config[$key1]);
                        }
                    }
                    if ($found == 0) {
                        $newDeviceConfig[$normalizedKey] = [
                            'id' => $idParameters[$index],
                            'value' => $defaultValues[$index] ?? ''
                        ];
                    }
                }
            }

            $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
            foreach ($commonFields as $index => $value) {
                if (strpos($value->fieldName, ' ') !== false) {
                    $indexkey = strtolower(str_replace(' ', '_', $value->fieldName));
                } else {
                    $indexkey = lcfirst(str_replace(' ', '_', $value->fieldName));
                }
                if (isset($config[$indexkey])) {
                    $newDeviceConfig[$indexkey] = [
                        'id' => $value->id,
                        'value' => $config[$indexkey]['value'] ?? ''
                    ];
                }
            }
            // Preserve runtime/statistics keys
            foreach (['activationDate', 'total_pings', 'last_ping'] as $statKey) {
                if (isset($config[$statKey])) {
                    $newDeviceConfig[$statKey] = $config[$statKey];
                }
            }
            $device->configurations = json_encode($newDeviceConfig);
            $device->update();
        }

        foreach ($templates as $temp) {
            $config = json_decode($temp->configurations, true);
            $newConfig = [];

            // Step 1: Build config only for parameters in $nameParameters
            foreach ($nameParameters as $index => $param) {
                $normalizedKey = strtolower(str_replace(' ', '_', $param));
                if (isset($config[$normalizedKey])) {
                    // If exists in original config, use it
                    $newConfig[$normalizedKey] = $config[$normalizedKey];
                } else {
                    // Else use provided default and ID
                    $found = 0;
                    foreach ($config as $key1 => $section) {
                        if (isset($section['id']) && $section['id'] == $idParameters[$index]) {
                            $found = 1;
                            $newConfig[$normalizedKey] = [
                                'id' => $idParameters[$index],
                                'value' => $section['value'] ?? ''
                            ];
                            unset($config[$key1]);
                        }
                    }
                    if ($found == 0) {
                        $newConfig[$normalizedKey] = [
                            'id' => $idParameters[$index],
                            'value' => $defaultValues[$index] ?? ''
                        ];
                    }
                }
            }
            $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
            foreach ($commonFields as $index => $value) {
                if (strpos($value->fieldName, ' ') !== false) {
                    $indexkey = strtolower(str_replace(' ', '_', $value->fieldName));
                } else {
                    $indexkey = lcfirst(str_replace(' ', '_', $value->fieldName));
                }
                if (isset($config[$indexkey])) {
                    $newConfig[$indexkey] = [
                        'id' => $value->id,
                        'value' => $config[$indexkey]['value'] ?? ''
                    ];
                }
            }
            $temp->configurations = json_encode($newConfig);
            $temp->update();
        }

        // Loop through each input type
        foreach ($request->inputType as $index => $type) {
            $options = [];
            $maxlength = [];
            $inputs = [];
            if (isset($request->selectOptions[$index]) && $type == 'select') {
                // Loop through each option in selectOptions[$i]
                foreach ($request->selectOptions[$index] as $key => $option) {
                    // Add non-empty options to the $options array
                    if (!empty($option)) {
                        $options[] = ['option' => $option, 'value' => $request->selectValues[$index][$key]];
                    }
                }
            }

            if (isset($request->maxValueInput[$index]) && $type == 'text') {
                // Loop through each option in selectOptions[$i]

                foreach ($request->maxValueInput[$index] as $key => $input) {
                    // Add non-empty options to the $options array
                    if (!empty($input)) {
                        $maxlength[$key] = $input;
                    }
                }
            }

            // Process numberInput if type is 'number'
            if ($type == 'number' && isset($request->numberInput[$index])) {
                $inputs = [];
                foreach ($request->numberInput[$index] as $key => $input) {
                    // Add non-empty options to the $inputs array
                    if (!empty($input)) {
                        $inputs[$key] = $input;
                    }
                }
                if (!empty($inputs)) {
                    $inputs = $inputs;
                }
            }

            // Determine requiredFieldInput
            $required = isset($request->inputFieldRequired[$index]) && $request->inputFieldRequired[$index] == 'on';
            $result[] = [
                'id' => $request->idParameters[$index],
                'key' => $request->nameParameters[$index],
                'default' => $request->default[$index],
                'type' => $type,
                'requiredFieldInput' => $required,
                'maxValueInput' => $maxlength,
                'numberRange' => $inputs,
                'selectOptions' => $options,
            ];
        }
        // Encode $result array to JSON and store in device_category
        $device_category->inputs = json_encode($result);
        $device_category->is_deleted = 0;
        $device_category->update();



        return back()->with('success', 'Device Category Updated Successfully');
    }
    public function updateDeviceParameters(Request $request)
    {
        // dd($request);
        $device_category = DeviceCategory::find($request->device_id);
        $parameter = $request->nameParameters;
        $parametersResult = [];
        for ($i = 0; $i < count($parameter); $i++) {
            $parametersResult[] = ['key' => $parameter[$i]];
        }
        $device_category->parameters = json_encode($parametersResult);
        $device_category->update();

        return back()->with('success', 'Device Parameter Updated Successfully');
    }

    public function getDeviceCategory(Request $request)
    {
        $device_category = DeviceCategory::find($request->id);
        $firmware = DB::table('firmware')->select('*')->where('device_category_id', $request->id)->get();
        if ($device_category) {
            $dataFields = DB::table('data_fields')->where('fieldType', 0)->get();
            if (Auth::user()->user_type == 'Admin') {
                $templates = DB::table('templates')
                    ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                    ->select('templates.*', 'writers.name as username')
                    ->where('templates.is_deleted', '0')
                    ->where('verify', '1')
                    ->where('templates.device_category_id', $request->id)
                    ->orderBy('templates.default_template', 'DESC')
                    ->get();
            } else if(Auth::user()->user_type == 'Support') {
                 $templates = DB::table('templates')
                ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                ->select('templates.*', 'writers.name as username')
                ->where('templates.is_deleted', '0')
                ->where('verify', '1')
                ->where('templates.device_category_id', $request->id)
                ->where('id_user',$request->user_id)
                ->orderBy('templates.default_template', 'DESC')
                ->get();
                
            } else {
                $templates = DB::table('templates')
                    ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                    ->select('templates.*', 'writers.name as username')
                    ->where('templates.is_deleted', '0')
                    ->where('verify', '2')
                    ->where('templates.device_category_id', $request->id)
                    ->where('id_user', auth()->id())
                    ->orderBy('templates.default_template', 'DESC')
                    ->get();
            }

            return json_encode(['status' => 200, 'message' => 'catgory inputs fetched', 'device_input' => $device_category->inputs, 'canEnable' => $device_category->is_can_protocol, 'templates' => json_encode($templates), 'firmware' => json_encode($firmware), 'dataFields' => json_encode($dataFields)]);
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }
    }
    public function getDeviceCategorySupport(Request $request)
    {
        $device_category = DeviceCategory::find($request->id);
        $firmware = DB::table('firmware')->select('*')->where('device_category_id', $request->id)->get();
        if ($device_category) {
            $authId = auth()->id();
            $selectedUserId = $request->userId;
            $dataFields = DB::table('data_fields')->where('fieldType', 0)->get();
            // $templates = DB::table('templates')
            //     ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
            //     ->select('templates.*', 'writers.name as username')
            //     ->where('templates.is_deleted', '0')
            //     ->where('verify', '2')
            //     ->where('templates.device_category_id', $request->id)
            //     ->where('id_user', auth()->id())
            //     ->orderBy('templates.default_template', 'DESC')
            //     ->get();
            $templates = DB::table('templates')
                ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                ->select(
                    'templates.*',
                    'writers.name as username',
                    'templates.default_template'
                )
                ->where('templates.is_deleted', '0')
                ->where('verify', '2')
                ->where('templates.device_category_id', $request->id)
                ->where(function ($query) use ($authId, $selectedUserId) {
                    $query->where('templates.id_user', $authId)
                        ->orWhere('templates.id_user', $selectedUserId);
                })
                ->orderBy('templates.default_template', 'DESC')
                ->get();
            return json_encode(['status' => 200, 'message' => 'catgory inputs fetched', 'device_input' => $device_category->inputs, 'templates' => json_encode($templates), 'firmware' => json_encode($firmware), 'dataFields' => json_encode($dataFields)]);
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }
    }
    public function getMultipleDeviceCategory(Request $request)
    {

        $device_category = [];
        $configurations = '';
        if ($request->has('userId')) {
            $getConfiguations = Writer::find($request->userId);
            $configurations = $getConfiguations->configurations;
        }
        foreach ($request->ids as $deviceId) {
            $device_category[] = DeviceCategory::find($deviceId);
        }
        if ($device_category) {
            $templates = [];
            $dataFields = DB::table('data_fields')->where('inputType', 'select')->get();
            foreach ($device_category as $category) {
                $inputs = json_decode($category->inputs, true);
                $inputIds = collect($inputs)->pluck('id')->toArray();
                // Fetch matching DataFields using left join-style behavior
                $dataFields = DataFields::whereIn('id', $inputIds)->get()->keyBy('id');
                $enhancedInputs = collect($inputs)->map(function ($input) use ($dataFields) {
                    $input['validationConfig'] = $dataFields[$input['id']]->validationConfig ?? null;
                    return $input;
                });
                $category->inputs = json_encode($enhancedInputs);
                if (Auth::user()->user_type == 'Reseller') {
                    $getTemplateByDeviceCategory = Template::select('*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->where(['device_category_id' => $category->id])->get();
                } else {
                    $getTemplateByDeviceCategory = Template::select('*')->where('templates.is_deleted', '0')->where('verify', '1')->where(['device_category_id' => $category->id])->get();
                }

                $templates[] = $getTemplateByDeviceCategory;
            }
            return json_encode(['status' => 200, 'message' => 'catgory inputs fetched', 'device' => json_encode($device_category), 'configurations' =>  $configurations, 'templates' => json_encode($templates), 'dataFields' => json_encode($dataFields)]);
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }
    }
    public function getTemplateValue(Request $request)
    {
        $template = Template::find($request->id);
        if ($template) {
            return json_encode(['status' => 200, 'message' => 'Template inputs fetched', 'template' => $template->configurations]);
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }
    }
    public function getTemplateConfiguration(Request $request)
    {
        $getTemplateConfiguration = Template::select('configurations')->where('id', $request->id)->get();
        if ($getTemplateConfiguration) {
            return json_encode(['status' => 200, 'message' => 'Template Configuration fetched', 'template' => json_encode($getTemplateConfiguration[0]['configurations'])]);
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }
    }
    public function deleteDeviceCategory(Request $request, $id)
    {

        $newCategoryId = $request->input('choosenDeviceCategory', $id);
        $newDevice_configurations = DeviceCategory::find($newCategoryId);
        $device_category = DeviceCategory::find($id);
        $default_template = Template::select('configurations')->where(['default_template' => 1, 'device_category_id' => $newDevice_configurations->id])->first();

        $writers = DB::table('writers')->whereRaw("FIND_IN_SET(?, device_category_id)", [$id])->get();
        if ($request->has('choosenDeviceCategory')) {
            Firmware::where('device_category_id', $id)->delete();
            $firmware = Firmware::where("device_category_id", $request->choosenDeviceCategory)->first();
            $newFirmware = json_decode($firmware->configurations, true);
            $deviceFind = Device::where(['device_category_id' => $id, 'is_deleted' => 0])->get();
            //dd($deviceFind);
            foreach ($deviceFind  as $dev) {
                $oldConfig = json_decode($dev->configurations, true);
                $oldConfig['firmware_id'] = $firmware->id;
                $oldConfig['firmware_file'] = $newFirmware['filename'];
                $oldConfig['firmware_version'] = $newFirmware['version'];
                if (isset($oldConfig['template'])) {
                    $oldTemplate = Template::select('*')->where('id', $oldConfig['template'])->first();

                    $result = array_diff_key($oldConfig, json_decode($oldTemplate->configurations, true));
                }
                $config = json_decode($default_template->configurations, true);

                $finalArray = array_merge($result, $config);
                Device::where('device_category_id', $id)->update(['device_category_id' =>  $newCategoryId, 'configurations' => json_encode($finalArray)]);
            }

            Template::where('device_category_id', $id)->update(['is_deleted' => 1]);


            foreach ($writers as $writer) {

                $deviceCategoriesConfig = json_decode($writer->configurations, true); // Decode as associative array      
                $deviceCategoryArr = explode(",", $writer->device_category_id);

                $isNewCategoryIdPresent = in_array($newCategoryId, $deviceCategoryArr);
                if ($isNewCategoryIdPresent) {
                    foreach ($deviceCategoryArr as $key => $deviceArr) {
                        if ($id == $deviceArr) {
                            unset($deviceCategoriesConfig[$key]);
                        }
                    }
                    $deviceCategoryArr = array_filter($deviceCategoryArr, function ($deviceId) use ($id) {
                        return $deviceId !== $id;
                    });

                    // Update configurations accordingly
                    $newarr = [];
                } else {
                    // Add the new category ID if it does not exist
                    foreach ($deviceCategoryArr as $key => $deviceArr) {
                        if ($id == $deviceArr) {
                            $deviceCategoryArr[$key] = $newCategoryId;
                            // $newarr = $deviceCategoriesConfig;
                            $deviceCategoriesConfig[$key] = json_decode($default_template->configurations, true);
                        }
                    }
                }

                $deviceCategoryArr = array_values($deviceCategoryArr);
                $deviceCategoriesConfig = array_values($deviceCategoriesConfig);
                $upadtedCategories = implode(",", $deviceCategoryArr);

                //  $upadtedCategories = implode(",", $removeIds);
                DB::table('writers')->where('id', $writer->id)->update(['device_category_id' => implode(",", $deviceCategoryArr), 'configurations' => json_encode($deviceCategoriesConfig)]);
            }
            // Mark the device category as deleted
            $device_category->is_deleted = '1';
            $device_category->save();
        } else {
            Firmware::where('device_category_id', $id)->delete();
            Template::where('device_category_id', $id)->update(['is_deleted' => 1]);
            foreach ($writers as $writer) {
                $deviceCategoriesConfig = json_decode($writer->configurations, true); // Decode as associative array

                $deviceCategoryArr = explode(",", $writer->device_category_id);
                $deviceCategoriesConfig = json_decode($writer->configurations, true);

                foreach ($deviceCategoryArr as $key => $deviceCategory) {
                    if ($deviceCategory  == $id) {
                        unset($deviceCategoryArr[$key]);
                        unset($deviceCategoriesConfig[$key]);
                    }
                }
                // Only update if there is a change to avoid unnecessary writes
                DB::table('writers')->where('id', $writer->id)->update(['device_category_id' => implode(',', $deviceCategoryArr), 'configurations' => json_encode($deviceCategoriesConfig)]);
            }
            $device_category->is_deleted = '1';
            $device_category->save();
        }
        if (Auth::user()->user_type == 'Admin') {
            return json_encode(['status' => 200, 'message' => $device_category->device_category_name . '-Settings deleted Successfully']);
        }
    }
}
