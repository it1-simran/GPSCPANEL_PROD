<?php

namespace App\Http\Controllers;

use App\Template;
use App\Device;
use App\Firmware;
use DB;
use App\Helper\CommonHelper;
use App\Modal;
use App\Writer;
use App\DataFields;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Auth;
use Carbon\Carbon;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = DB::table('writers')->select('id', 'name')->get();
        $url_type = self::getURLType();
        return view('add_template', ['users' => $users, 'url_type' => $url_type]);
    }
    /**
     * Display a Bulk Template Add
     *
     * @return \Illuminate\Http\Response
     */
    public function assignTemplateBulk()
    {
        $url_type = self::getURLType();
        return view('assign_template_bulk', ['url_type' => $url_type]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        // dd($request);
        // dd($request->user_id);
        $userType = Auth::user()->user_type;

        $query = Template::where([
            'is_deleted' => '0',
            'device_category_id' => $request->deviceCategory,
            'default_template' => 1,
        ]);

        // Apply dynamic condition based on user_type
        if (Auth::check()) {
            if (Auth::user()->user_type !== 'Admin') {
                $query->where('id_user', Auth::user()->id);
            } else {
                $query->whereNull('id_user');
            }
        }

        $checkifFirst = $query->count();

        $validatedData = $request->validate([
            'template_name' => 'required',
            'deviceCategory' => 'required',
        ]);

        $config = $request->configuration;

        // dd($config);
        $idParameters = $request->idParameters;
        $converted = [];
        foreach ($idParameters as $key => $id) {
            if (isset($config[$key])) {
                $converted[$key] = [
                    'id' => intval($id),
                    'value' => $config[$key] ?? ''
                ];
            }
        }

        $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
        foreach ($commonFields as $index => $value) {
            $key = strtolower(str_replace(' ', '_', $value->fieldName));
            // if (isset($config[$key])) {
            if ($key == 'ping_interval' || $key == 'is_editable') {
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $config[$key] ?? ''
                ];
            }
            // }
        }
        $configuration = $converted;
        if (Auth::user()->user_type != 'Admin') {

            $contact = Writer::find($request->user_id);
            if (!$contact) {
                abort(404, "Writer not found with ID: $contact->user_id");
            }

            $device_category = explode(",", $contact->device_category_id);
            $userconfiguration = json_decode($contact->configurations, true);
            foreach ($device_category as $key => $category) {
                if ($category == $request->deviceCategory) {

                    $configuration['ping_interval']['value'] = $userconfiguration[$key]['ping_interval']['value'];
                    $configuration['is_editable']['value'] = $userconfiguration[$key]['is_editable']['value'];;
                }
            }

            //  dd($configuration);
            // $configuration['ping_interval']['value'] = 4;
            // $configuration['is_editable']['value'] = 1;

            //   $request->configuration = $configuration;
        }
        $canConfigurations = [];
        if (!empty($request->canConfigurationArr)) {
            $decoded = json_decode($request->canConfigurationArr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $canConfigurations = $decoded;
            }
        }
        $temp = [
            'id_user' => $request->user_id,
            'template_name' => $request->template_name,
            'device_category_id' => $request->deviceCategory,
            'configurations' => json_encode($configuration),
            'can_configurations' => json_encode($canConfigurations)
        ];
        if ($userType != 'Admin') {
            if ((isset($_POST['default_template']) && $_POST['default_template'] == "on") || $checkifFirst == 0) {
                $set_val = 1;

                // $contact =  DB::table('writers')->where('id', $request->user_id )->first();
                $contact = Writer::find($request->user_id);
                if (!$contact) {
                    abort(404, "Writer not found with ID: $contact->user_id");
                }

                $device_category = explode(",", $contact->device_category_id);
                $configuration = json_decode($contact->configurations, true);
                foreach ($device_category as $key => $category) {
                    if ($category == $request->deviceCategory) {

                        $configuration[$key] = $converted;
                    }
                }
                $contact->configurations = json_encode($configuration);
                $contact->save();
            } else {
                $set_val = 0;
            }
        } else {
            if ((isset($_POST['default_template']) && $_POST['default_template'] == "on") && $checkifFirst == 0) {
                $set_val = 1;
            } else {
                $set_val = 0;
            }
        }
        // dd($checkifFirst);
        $temp['default_template'] = $set_val;
        if (Auth::user()->user_type == 'Admin') {
            $temp['verify'] = 1;
        } else {

            $temp['verify'] = 2;
        }

        // dd($temp);

        $template =  Template::create($temp);
        $newTemplateId = $template->id;
        if ($request->default_template == 'on' && $template->id_user == Auth::user()->id) {
            $updateTemplate = Template::where('id', '!=',  $newTemplateId)
                ->where('device_category_id', $request->deviceCategory)
                ->where('id_user', Auth::user()->id)
                ->update([
                    'default_template' => 0,
                ]);
        }


        return json_encode(['status' => 200, 'status_msg' => $request->template_name . '- Settings Added Successfully']);
    }
    /**
     * Display the specified resource.
     * @param  \App\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function show(Template $template)
    {
        if (Auth::user()->user_type == 'Admin') {
            $templates = DB::table('templates')
                ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                ->select('templates.*', 'writers.name as username')
                ->where('templates.is_deleted', '0')
                ->where('verify', '1')
                ->orderBy('templates.default_template', 'DESC')
                ->get();
        } else {
            $templates = DB::table('templates')
                ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                ->select('templates.*', 'writers.name as username')
                ->where('templates.is_deleted', '0')
                ->where('verify', '2')
                ->where('id_user', auth()->id())
                ->orderBy('templates.default_template', 'DESC')
                ->get();
        }
        foreach ($templates as $key => $template) {
            if ($template->configurations) {
                $configurations = json_decode($template->configurations);
                $templates[$key]->configurations = $configurations;
            }
        }
        $url_type = self::getURLType();
        return view('view_template', ['templates' => $templates, 'url_type' => $url_type]);
    }
    public function assign(Request $request, $id)
    {
        // Validate incoming request data
        $request->validate([
            'devices' => 'required|array',
            'devices.*' => 'exists:devices,id'
        ]);
        // Determine redirect URL type
        $url_type = self::getURLType();
        // Retrieve the template by ID
        $template = Template::findOrFail($id);

        // Get devices based on IDs from the request
        $devices = Device::whereIn('id', $request->input('devices'))->get();

        $errors = [];
        $successfulUpdates = [];
        $updatedConfigurations = [];

        foreach ($devices as $device) {
            $deviceConfig = json_decode($device->configurations, true);
            $templateConfig = json_decode($template->configurations, true);
            if (isset($templateConfig['firmware_id'])) {
                $firmware = Firmware::find($templateConfig['firmware_id']);

                if (!$firmware) {
                    $errors[] = "Device ID {$device->id}: Firmware with ID {$templateConfig['firmware_id']} not found.";
                    continue;
                }

                $firmwareConfig = json_decode($firmware->configurations);
                $deviceConfig['firmware_id'] = $firmware->id;
                $deviceConfig['firmware_file'] = $firmwareConfig->filename;
                $deviceConfig['firmware_version'] = $firmwareConfig->version;

                if ($device->user_id === null) {
                    $deviceConfig['modelName'] = CommonHelper::getDeviceCategoryName($device->device_category_id);
                } else {
                    $assign_to_ids = explode(",", $device->assign_to_ids);
                    $models = Modal::where(['user_id' => $assign_to_ids[1], 'firmware_id' => $templateConfig['firmware_id']])->first();
                    if ($models) {
                        $deviceConfig['modelName'] = $models->name;
                    } else {
                        $errors[] = $device->imei;
                        continue;
                    }
                }

                // Merge template configuration
                $mergedConfig = array_merge($deviceConfig, $templateConfig);
                // Update device configurations in JSON format
                $device->configurations = json_encode($mergedConfig);
                $device->save();

                $successfulUpdates[] = $device->imei;
                $updatedConfigurations[$device->imei] = $mergedConfig; // Collect updated configurations
            } else {
                return redirect($url_type . '/view-template')->with([
                    'error' => "Firmware not Assigned to " . $template->template_name . " template .please assign firmware first.",
                ]);
            }
        }



        // Prepare success and error messages
        $successMessage = '';
        if (!empty($successfulUpdates)) {
            $successMessage .= 'Total Device Updated :' . count($successfulUpdates);
            $successMessage .= "Devices successfully updated for this imei: " . implode(', ', $successfulUpdates);
        }

        $errorMessage = '';
        if (!empty($errors)) {
            $errorMessage .= "Total Device Failed" . count($errors) . '</br>';
            $errorMessage .= "Errors occurred for devices:";
            $errorMessage .= "Device ID ";
            foreach ($errors as $error) {
                $errorMessage .= "$error" . ",";
            }
            $errorMessage .= ": Model name is not assigned to this " . CommonHelper::getFirmwareName($templateConfig['firmware_id']) . " firmware. Please contact the administrator.";
        }

        // Redirect with messages
        return redirect($url_type . '/view-template')->with([
            'success' => $successMessage,
            'error' => $errorMessage,
            'device_category_id' => $template->device_category_id,
            'updated_configurations' => $updatedConfigurations // Pass updated configurations
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function edit(Template $template, $id)
    {
        $currentUser = Auth::user();

        $template_info = Template::find($id);
        if ($currentUser->user_type == 'Admin') {
            $template_info = Template::find($id);
        } elseif ($currentUser->user_type == 'Reseller') {
            $checkUser = DB::table('templates')->where('id_user', $currentUser->id)->pluck('id_user')->toArray();

            // Check if the current user can edit the specified writer
            if (!in_array($template_info->id_user, $checkUser) && $currentUser->id != $template_info->id_user) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
        } else if ($currentUser->user_type == "User") {
            if ($currentUser->id != $template_info->id_user) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            } else {
                // Handle other user types or roles as needed

            }
        }
        $users = DB::table('writers')->select('id', 'name')->get();
        $url_type = self::getURLType();
        return view('edit_template', ['template_info' => $template_info, 'users' => $users, 'url_type' => $url_type]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Template $id)
    {
        if (Auth::user()->user_type == 'Admin') {
            $contact = Template::find($id);
            $contact_id = $request->input('id');
            $contact = Template::find($contact_id);
            if (isset($_POST['default_template']) && ($_POST['default_template'] == "on")) {
                $set_val = 1;
                DB::table('templates')->where('id', '!=', $contact_id)->where('device_category_id', $request->deviceCategory)->update(array('default_template' => 0));
            } else {
                $set_val = 0;
            }
            $contact->verify = '1';
            $contact->template_name =  $request->get('template_name');
            $contact->configurations = json_encode($request->configuration);
            $contact->device_category_id = $request->deviceCategory;
            $contact->default_template = $set_val;
            $contact->save();
        } else {
            $contact = Template::find($id);
            $contact_id = $request->input('id');
            $contact = Template::find($contact_id);
            if (isset($_POST['default_template']) && ($_POST['default_template'] == "on")) {
                $set_val = 1;
                DB::table('templates')->where('id', '!=', $contact_id)->where('device_category_id', $request->deviceCategory)->update(array('default_template' => 0));
            } else {
                $set_val = 0;
            }
            $contact->verify = '2';
            $contact->id_user = $request->user_id;
            $contact->template_name = $request->get('template_name');
            $contact->ip = $request->get('ip');
            $contact->port = $request->get('port');
            $contact->logs_interval = $request->get('logs_interval');
            $contact->sleep_interval = $request->get('sleep_interval');
            $contact->password = $request->get('password');
            $contact->trans_interval = $request->get('trans_interval');
            $contact->active_status = $request->get('active_status');
            $contact->fota = $request->get('fota');
            $contact->default_template = $set_val;

            $contact->save();
        }
        if (Auth::user()->user_type == 'Admin') {
            return redirect('admin/view-template')->with(['success' => $request->template_name . ' -updated Successfully', 'device_category_id' => $request->deviceCategory]);
        } else if (Auth::user()->user_type == 'Reseller') {
            return redirect('reseller/view-template')->with(['success' => $request->template_name . ' -updated Successfully', 'device_category_id' => $request->deviceCategory]);
        } else {
            return redirect('user/view-template')->with(['success' => $request->template_name . ' -updated Successfully', 'device_category_id' => $request->deviceCategory]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function destroy(Template $template, $id)
    {
        $template_data = Template::find($id);
        $template_data->is_deleted = '1';
        $template_data->save();
        if (Auth::user()->user_type == 'Admin') {
            return redirect('admin/view-template')->with(['error' => $template_data->template_name . '-Settings deleted Successfully', 'device_category_id' => $template_data->device_category_id]);
        } else if (Auth::user()->user_type == 'Reseller') {
            return redirect('reseller/view-template')->with(['error' => $template_data->template_name . '-Settings deleted Successfully', 'device_category_id' => $template_data->device_category_id]);
        } else {
            return redirect('user/view-template')->with(['error' => $template_data->template_name . '-Settings deleted Successfully', 'device_category_id' => $template_data->device_category_id]);
        }
    }
    public function viewTemplateConifiguration($id)
    {
        $template_info = Template::find($id);
        $url_type = self::getURLType();
        return view('view_setting', ['template_info' => $template_info, 'url_type' => $url_type]);
    }

    public function updateConfigurations(Request $request, $id)
    {
        // dd($request);
        $params = $request->configuration ?? [];
        $url_type = self::getURLType();
        // dd($params);
        // Fetch dynamic data fields
        $dataFields = DataFields::where(['is_common' => 0, 'fieldType' => 0])->get();

        $converted = [];

        foreach ($dataFields as $value) {
            $key = strtolower(str_replace(' ', '_', $value->fieldName));
            if (isset($params[$key])) {
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $params[$key]
                ];
            }
        }

        // Ensure firmware_id is always set
        if (isset($params['firmware_id'])) {
            $converted['firmware_id'] = [
                'id' => 84,
                'value' => $params['firmware_id']
            ];
        }
        $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
        foreach ($commonFields as $index => $value) {
            $key = strtolower(str_replace(' ', '_', $value->fieldName));
            // if (isset($config[$key])) {
            if ($key == 'ping_interval' || $key == 'is_editable') {
                $converted[$key] = [

                    'id' => $value->id,
                    'value' => $params[$key] ?? 1
                ];
            }
            // }
        }



        // Fetch template and validate
        $template = Template::findOrFail($id);
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        $existingConfigurations = $template->configurations !== "null"
            ? json_decode($template->configurations, true)
            : [];
        // Merge existing and new configurations
        $mergedConfigurations = array_merge($existingConfigurations, $converted);
        //  dd($mergedConfigurations);
        // If default template, update writer’s configuration
        if ($template->default_template == 1) {
            $contact = Writer::find($template->id_user);
            if ($contact) {
                $deviceCategories = explode(',', $contact->device_category_id);
                $writerConfigs = json_decode($contact->configurations, true) ?? [];

                foreach ($deviceCategories as $index => $categoryId) {
                    if ($categoryId == $template->device_category_id) {
                        $writerConfigs[$index] = $mergedConfigurations;
                    }
                }

                $contact->configurations = json_encode($writerConfigs);
                $contact->save();
            }
        }


        // Save updated template configurations
        $template->configurations = json_encode($mergedConfigurations);
        $template->updated_at = Carbon::now('UTC')->toDateTimeString();
        // dd($template->configurations);
        $template->save();

        return redirect("{$url_type}/view-template-configurations/{$id}")
            ->with('success', 'Device Updated Successfully!');
    }

    // public function updateConfigurations(Request $request, $id)
    // {

    //     $params = $request->configuration ?? [];
    //           $url_type = self::getURLType();
    //         // dd($params);
    //     // Fetch dynamic data fields
    //     $dataFields = DataFields::where(['is_common' => 0, 'fieldType' => 0])->get();

    //     $converted = [];

    //     foreach ($dataFields as $value) {
    //         $key = strtolower(str_replace(' ', '_', $value->fieldName));
    //         if (isset($params[$key])) {
    //             $converted[$key] = [
    //                 'id' => $value->id,
    //                 'value' => $params[$key]
    //             ];
    //         }
    //     }

    //     // Ensure firmware_id is always set
    //     if (isset($params['firmware_id'])) {
    //         $converted['firmware_id'] = [
    //             'id' => 84,
    //             'value' => $params['firmware_id']
    //         ];
    //     }
    //     $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
    //       foreach ($commonFields as $index => $value) {
    //           $key = strtolower(str_replace(' ', '_', $value->fieldName));
    //           // if (isset($config[$key])) {
    //               if($key == 'ping_interval' || $key == 'is_editable' ){
    //                   $converted[$key] = [

    //                       'id' => $value->id,
    //                       'value' => $params[$key] ?? 1
    //                   ];
    //               }
    //           // }
    //       }

    //     // Fetch template and validate
    //     $template = Template::findOrFail($id);
    //     if (!$template) {
    //         return response()->json(['error' => 'Template not found'], 404);
    //     }
    //     $existingConfigurations = $template->configurations !== "null"
    //         ? json_decode($template->configurations, true)
    //         : [];
    //     // Merge existing and new configurations
    //     $mergedConfigurations = array_merge($existingConfigurations, $converted);
    //     // dd($mergedConfigurations);
    //     // If default template, update writer’s configuration
    //     if ($template->default_template == 1) {
    //         $contact = Writer::find($template->id_user);
    //         if (!$contact) {
    //           return redirect("admin/view-template-configurations/{$id}")
    //             ->with('Error', 'User Not Found');
    //         }

    //         $deviceCategories = explode(',', $contact->device_category_id);
    //         $writerConfigs = json_decode($contact->configurations, true) ?? [];

    //         foreach ($deviceCategories as $index => $categoryId) {
    //             if ($categoryId == $template->device_category_id) {
    //                 $writerConfigs[$index] = $mergedConfigurations;
    //             }
    //         }

    //         $contact->configurations = json_encode($writerConfigs);
    //         $contact->save();
    //     }

    //     // Save updated template configurations
    //     $template->configurations = json_encode($mergedConfigurations);
    //     $template->save();

    //     return redirect("{$url_type}/view-template-configurations/{$id}")
    //         ->with('success', 'Device Updated Successfully!');
    // }

    // public function updateConfigurations(Request $request, $id)
    // {

    //     $params = $request->configuration;
    //     $keys = array_keys($params);
    //     $dataFields = DataFields::select("*")->where(['is_common'=> 0,'fieldType' => 0])->get();

    //     $converted = [];

    //     foreach ($dataFields as $value) {
    //         $fieldName = $value->fieldName;

    //         // Convert to snake_case
    //         $key = strtolower(str_replace(' ', '_', $fieldName));

    //         // Only process if key exists in $params
    //         if (array_key_exists($key, $params)) {
    //             $converted[$key] = [
    //                 'id' => $value->id,
    //                 'value' => $params[$key]
    //             ];
    //         }
    //     }   
    //     $converted['firmware_id'] = [
    //         'id' => 84,
    //         'value' => $params['firmware_id']
    //     ];
    //     $template = Template::find($id);
    //     if (!$template) {
    //         return response()->json(['error' => 'Template not found'], 404);
    //     }
    //     $configurations = $template->configurations !=  "null" ? json_decode($template->configurations, true) : [];
    //     $newConfigurations = $converted;
    //     $result = array_replace($configurations, $newConfigurations);
    //     foreach ($newConfigurations as $key => $value) {
    //         if (isset($configurations[$key])) {
    //             $configurations[$key] = $value;
    //         }
    //     }
    //     if($template->default_template == 1){
    //         $contact = Writer::find($template->id_user);
    //         if (!$contact) {
    //             abort(404, "Writer not found with ID: $id");
    //         }
    //         $device_category = explode(",",$contact->device_category_id);
    //         $configuration = json_decode($contact->configurations,true);
    //         foreach($device_category as $key => $category) {
    //             if($category == $template->device_category_id){

    //                 $configuration[$key] = $result;
    //             }
    //         } 
    //         $contact->configurations = json_encode($configuration);
    //         $contact->save();
    //     }
    //     $updatedConfigurationsJson = json_encode($result);
    //     $template->configurations = $updatedConfigurationsJson;
    //     $template->save();
    //     $url_type = self::getURLType();
    //     //     return redirect("admin/view-template-configurations/$id")
    //     //   ->with('success', "Device Updated Successfully!");
    //     return back()->with('success', "Device Updated Successfully!");
    // }
    public function viewUncategorized()
    {

        $templates = DB::table('templates')
            ->leftJoin('writers', 'writers.id', '=', 'templates.user_id')
            ->leftJoin('device_categories', 'device_categories.id', '=', 'templates.device_category_id')
            ->select('templates.*', 'writers.name as username')
            ->where('templates.is_deleted', '0')
            ->where('verify', '1')
            ->orderBy('templates.default_template', 'DESC')
            ->where('device_categories.is_deleted', 1)
            ->get();
        $url_type = self::getURLType();
        return view('view_uncategorized_templates', ['templates' => $templates, 'url_type' => $url_type]);
    }
    public function updateTemplateInfoConfigurations(Request $request, $id)
    {
        $template = Template::findOrFail($id); // Fails gracefully if template not found
        $template->template_name = $request->template_name;
        $template->default_template = $request->default_template === 'on' ? 1 : 0;
        if (Auth::user()->user_type === 'Admin') {
            $params = $request->configuration ?? [];
            $dataFields = DataFields::where('is_common', 1)->get();

            $converted = [];

            foreach ($dataFields as $field) {
                $key = strtolower(str_replace(' ', '_', $field->fieldName));
                if (array_key_exists($key, $params)) {
                    $converted[$key] = [
                        'id' => $field->id,
                        'value' => $params[$key],
                    ];
                }
            }

            $oldConfig = json_decode($template->configurations, true) ?? [];
            $mergedConfig = array_replace($oldConfig, $converted);


            $template->configurations = json_encode($mergedConfig);
            $template->updated_at = Carbon::now('UTC')->toDateTimeString();
        }

        $template->save();
        if ($request->default_template == 'on' && $template->id_user == Auth::user()->id && Auth::user()->user_type != 'Admin') {
            $updateTemplate = Template::where('id', '!=',  $id)
                ->where('id_user', Auth::user()->id)
                ->where('device_category_id', $template->device_category_id)
                ->update([
                    'default_template' => 0,
                ]);
        } else {
            $updateTemplate = Template::where('id', '!=',  $id)
                ->where('id_user', NULL)
                ->where('device_category_id', $template->device_category_id)
                ->update([
                    'default_template' => 0,
                ]);
        }

        return back()->with('success', "Device Updated Successfully!");
    }

    // public function updateTemplateInfoConfigurations(Request $request, $id)
    // {
    //     dd($request);
    //     if (Auth::user()->user_type == 'Admin') {
    //         $params = $request->configuration;
    //         $keys = array_keys($params);
    //         // print_r($keys);
    //         $dataFields = DataFields::select("*")->where(['is_common'=> 1])->get();

    //         $converted =[];

    //         foreach ($dataFields as $value) {
    //             $fieldName = $value->fieldName;

    //             // Convert to snake_case
    //             $key = strtolower(str_replace(' ', '_', $fieldName));

    //             // Only process if key exists in $params
    //             if (array_key_exists($key, $params)) {
    //                 $converted[$key] = [
    //                     'id' => $value->id,
    //                     'value' => $params[$key]
    //                 ];
    //             }
    //         }

    //         $template  = Template::find($id);
    //         $template->template_name = $request->template_name;
    //         $newChanges = $converted;
    //         $oldChanges = json_decode($template->configurations, true);
    //         $result = array_replace($oldChanges, $newChanges);
    //         $template->default_template = $request->default_template == 'on' ? 1 : 0;
    //         $template->configurations = json_encode($result);
    //     }else{
    //          $template  = Template::find($id);
    //          $template->template_name = $request->template_name;

    //     }
    //     $template->save();
    //     return back();
    // }
    public function updateCanProtocolTempConfigurations(Request $request, $id)
    {
        $params = $request->canConfiguration;

        // Fetch all CAN protocol data fields and key by lowercase snake_case fieldName
        $dataFields = DataFields::where('is_can_protocol', 1)
            ->get()
            ->keyBy(function ($item) {
                return strtolower(str_replace(' ', '_', $item->fieldName));
            });

        $converted = [];

        // Iterate over $params to preserve submitted order
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

        $template = Template::find($id);
        $oldChanges = json_decode($template->can_configurations, true) ?? [];
        $changedFields = [];

        foreach ($converted as $key => $value) {
            if (!isset($oldChanges[$key]) || $oldChanges[$key]['value'] !== $value['value']) {
                $oldValue = $oldChanges[$key]['value'] ?? 'N/A';
                $newValue = $value['value'];
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        // Save in the submitted sequence
        $template->can_configurations = json_encode($converted);
        $template->updated_at = Carbon::now('UTC')->toDateTimeString();
        $template->save();

        return back();
    }

    public function editDeviceTemplateBulk(Request $request)
    {
        //dd($request);

        $config = $request->configuration;
        // dd($config);
        $idParameters = $request->idParameters;
        $converted = [];
        foreach ($idParameters as $key => $id) {
            if (isset($config[$key])) {
                $converted[$key] = [
                    'id' => intval($id),
                    'value' => $config[$key] ?? ''
                ];
            }
        }
        $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
        foreach ($commonFields as $index => $value) {
            $key = strtolower(str_replace(' ', '_', $value->fieldName));
            if (isset($config[$key])) {
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $config[$key] ?? ''
                ];
            }
        }

        //dd($converted);
        // dd($converted);
        //$getTemplate = Template::find($request->templates);
        // $templatechanges  = json_decode($getTemplate->configurations, true);
        $templatechanges = $converted;
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:2048',
        ]);
        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');
            if ($file->isValid()) {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = [];
                $rowIndex = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    if ($rowIndex === 0) {
                        $rowIndex++;
                        continue;
                    }
                    $rowData = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $cellValue = $cell->getValue();
                        $cleanedValue = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $cellValue);
                        $rowData[] = $cleanedValue;
                    }
                    $data[] = $rowData;
                    $rowIndex++;
                }
                foreach ($data  as $val) {
                    $dataVal = Device::where(['imei' => $val[2], 'device_category_id' => $request->deviceCategory])->first();
                    if ($dataVal) {
                        $oldDevices = json_decode($dataVal->configurations, true);
                        $configuration = array_replace($oldDevices, $templatechanges);
                        $canConverted = !empty($request->canConfigurationArr) ? json_decode($request->canConfigurationArr, true) : [];
                        // dd($configuration);
                        $dataVal->can_configurations = $canConverted;
                        $dataVal->configurations = json_encode($configuration);
                        $dataVal->update();
                    }
                }
                return back()->with('success', "Device Updated Successfully!");
            }
        }
        return back()->withErrors('File is invalid or no file was uploaded.');
    }
}
