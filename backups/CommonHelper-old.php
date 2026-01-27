<?php

// app/CustomHelper.php

namespace App\Helper;

use App\DeviceCategory;
use App\Device;
use App\Writer;
use App\Template;
use App\Firmware;
use App\DataFields;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;

class CommonHelper
{  
    public static function getDeviceCategoryValue($key, $value) {
        $response = DataFields::where('fieldName', $key)->first();
    
        if ($response && $response->inputType == 'select') {
            $config = json_decode($response->validationConfig, true);
            
            if (!empty($config['selectValues']) && !empty($config['selectOptions'])) {
                $index = array_search((string)$value, $config['selectValues']);
                if ($index !== false && isset($config['selectOptions'][$index])) {
                    return $config['selectOptions'][$index];
                }
            }
        }
    
        return $value;
    }
    public static function getDeviceCategory()
    {
        $currentUser = Auth::user();
        if ($currentUser->user_type != 'Admin') {
            $deviceCategoryIdsArray = explode(',', $currentUser->device_category_id);
            $getDeviceCategory = DeviceCategory::select('*')->whereIn('id', $deviceCategoryIdsArray)->where('is_deleted', 0)->get();
        } else {
            $getDeviceCategory = DeviceCategory::select('*')->where('is_deleted', 0)->get();
        }
        return $getDeviceCategory;
    }
    public static function getDeviceCategoryTabs($device, $show_acc_wise, $urlType, $deviceCategoryId)
    {
        $currentUser = Auth::user();
        if ($currentUser->user_type != 'Admin') {
            $deviceCategoryIdsArray = explode(',',  $currentUser->device_category_id);   
            $getDeviceCategory = DeviceCategory::select('id', 'device_category_name')->whereIn('id', $deviceCategoryIdsArray)->where('is_deleted', 0)->get();
        } else {
            $getDeviceCategory = DeviceCategory::select('id', 'device_category_name')->where('is_deleted', 0)->get();
        }
        $html = '<div class="tabs">';
        foreach ($getDeviceCategory as $key => $category) {
            // dd($deviceCategoryId); 
            if ($deviceCategoryId) {
                $html .= '<button class="tablinks';
                if ($category->id == $deviceCategoryId) {
                    $html .= ' active';
                }
                $html .= '" onclick="openTab(event, \'tab' . $category->id . '\')">' . $category->device_category_name . '</button>';
            } else {
                $html .= '<button class="tablinks';
                if ($key == 0) {
                    $html .= ' active';
                }
                $html .= '" onclick="openTab(event, \'tab' . $category->id . '\')">' . $category->device_category_name . '</button>';
            }
        }
        $html .= '</div>';
        // Start tab contents
        foreach ($getDeviceCategory as $category) {
            // $getUser = Writer::select('id', 'name')->where(['created_by' => Auth::user()->id, 'is_deleted' => '0'])->get();
            $getUser = Writer::select('id', 'name')
                ->where('created_by', Auth::user()->id)
                ->where('is_deleted', '0')
                ->whereRaw("FIND_IN_SET(?, device_category_id)", [$category->id])
                ->get();
            // Initialize the query builder for Template
            $query = Template::select('id', 'template_name')->where('device_category_id', $category->id)->where('is_deleted', '0');

            if (Auth::user()->user_type == 'Admin') {
                // Condition for Reseller user type
                $query->where('verify', '1');
            } else {
                // Condition for Admin or other user types
                $query->where('id_user', Auth::user()->id)
                    ->where('verify', '2');
            }
            // Execute the query and get the result
            $getTemplates = $query->get();
            $html .= '<div id="tab' . $category->id . '" class="tabcontent">';
            $html .= '<div style="margin-bottom:15px;">';
            if (Auth::user()->user_type == 'Admin') {
                $html .= '<button class="btn btn-danger btn-sm delete_all" data-category-id="' . $category->id . '"  data-url="' . url('admin/deleteAll') . '">Delete All Selected</button>';
            }
            if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller') {
                $html .= '<button style="margin-left: 15px;" class="btn btn-primary btn-sm user-responsive" data-category-id="' . $category->id . '" data-url="/' . $urlType . '/assignuserAll" >Assign Account</button>';
            }
            $html .= '<button style="margin-left: 21px;" class="btn btn-info btn-sm template-responsive"  data-category-id="' . $category->id . '" data-url="/' . $urlType . '/assigtemplateAll">Assign Setting Template</button>';
            if ((Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller') && $show_acc_wise) {
                $html .= '<div style="float: right;" class="Row">
                    <form method="get" action="/' . $urlType . '/view-device-assign">';
                $html .= '<div class="form-group d-flex"><select class="btn-sm" id="searchUser" name="username" style="height: 33px;margin-top: 7px;">';

                if (count($getUser) > 0) {
                    $html .= '<option value="0">Account Wise list</option>';
                    foreach ($getUser as $user) {
                        $html .= '<option value="' . $user->id . '" >' . $user->name . '</option>';
                    }
                }
                $html .= '</select><button type="submit" class="btn btn-success btn-sm">Filter</button></div></form></div>';
            }
            $html .= '</div>';
            if(Auth::user()->user_type == "Admin"){
                $html .='<div class="col-lg-12 text-right margin-bottom-10">
                    <a href="/device-export-excel" class="btn btn-success">Download Excel</a>
                    <a href="/device-export-csv" class="btn btn-success">Download CSV</a>
                </div>';
            }   
            $html .= '<table id="datatable' . $category->id . '" class="example table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:14px;">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>Check All &nbsp; <input type="checkbox" id="master' . $category->id . '" onclick="dataTableCheckAll(' . $category->id . ')" data-trId="' . $category->id . '"> </th>';
            $html .= '<th style="width:70px;">Sr. No</th>';
            $html .= '<th>User Name</th>';
            $html .= '<th>Name</th>';
            $html .= '<th>IMEI</th>';
            $html .= '<th>Total Pings</th>';
            if (Auth::user()->user_type == 'Admin') {
                $html .= '<th>Ping Interval</th>';
            }
            $html .= '<th>Added On</th>';
            $html .= '<th>Last Settings Update</th>';


            if (Auth::user()->user_type == 'Admin') {
                $html .= '<th>Editable</th>';
                $html .= '<th>Logs</th>';
            }
            $html .= '<th>Default Configurations </th>';

            if (Auth::user()->user_type == 'Admin') {
                $html .= '<th>Delete</th>';
            }
            $html .= '</tr></thead><tbody>';

            if (count($device) > 0) {
                $i = 1;
                foreach ($device as $contact) {
                    $config = json_decode($contact->configurations, true);
                    // dd($config);
                    if ($category->id == $contact->device_category_id) {
                        $html .= '<tr>';
                        $html .= '<td><input type="checkbox" class="sub_chk'.$category->id.'" data-category="'.$category->id.'" data-id="' . $contact->id . '"></td>';
                        $html .= '<td>' . $i . '</td>';
                        $html .= '<td>' . (!empty($contact->username) ? $contact->username : 'Unassigned') . '</td>';
                        $html .= '<td>' . $contact->name . '</td>';
                        $html .= '<td>' . $contact->imei . '</td>';
                        $html .= '<td>' . (isset($config['total_pings']) ? $config['total_pings'] : 0) . '</td>';
                        if (Auth::user()->user_type == 'Admin') {
                            $html .= '<td>' . (isset($config['ping_interval']) ? $config['ping_interval']['value'] : "") . '</td>';
                        }
                        $html .= '<td>' . $contact->created_at . '</td>';
                        $html .= '<td>' . $contact->updated_at . '</td>';

                        if (Auth::user()->user_type == 'Admin') {
                            $html .= '<td>';
                            if (isset($config['is_editable']) && $config['is_editable']['value'] == '1') {
                                $html .= '<button class="btn btn-success btn-sm">Yes</button>';
                            } else {
                                $html .= '<button class="btn btn-danger btn-sm">No</button>';
                            }
                            $html .= '</td>';
                            $html .=  '<td><button class="btn btn-carrot"><a class="text-white" href="/admin/view-device-logs/'.$contact->id.'" style="color:#fff;">Logs</a></button></td>';
                        }
                        $html .= '<td class="margin-top-11"><a href="' . url('/' . strtolower(Auth::user()->user_type) . '/view-device-configurations/' . $contact->id) . '" class="btn btn-primary btn-info">View Configuration</a></td>';
                        if (Auth::user()->user_type == 'Admin') {
                            $html .= '<td>';
                            $html .= '<form action="' . route('device.delete', $contact->id) . '" method="post">';
                            $html .= csrf_field();
                            $html .= method_field('DELETE');
                            $html .= '<button onClick="javascript:return confirm(\'Are you sure you want to delete this?\');" class="btn btn-danger btn-sm" type="submit">Delete</button>';
                            $html .= '</form>';
                            $html .= '</td>';
                        }
                        $html .= '</tr>';
                        $i++;
                    }
                }
            }
            $html .= '</tbody></table>'; // Close tab content div
            $html .= self::getModels('user-responsive' . $category->id . '', 'user_assign_all', 'Account', $getUser, $category->id);
            $html .= self::getModels('template-responsive' . $category->id . '', 'temp_assign_all', 'Template', $getTemplates, $category->id);
            $html .= '</div>';
        }

        return $html;
    }
    public static function getModels($modalId, $submitRequestId, $type, $selectOptions = [], $id)
    {

        $html = '<div class="modal" id="' . $modalId . '" aria-hidden="true"><div class="modal-dialog modal-md"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title"><strong>Assign ' . $type . '</strong></h4></div><div class="modal-body"><div class="row"><div class="col-md-12"><div class="form-group"><label class="form-label">Select ' . $type . '</label>';
        if ($type == 'Account') {
            $html .= '<select class="assignDeviceUser form-control" id="id_user' . $id . '" name="devices[]"><optgroup label="Unassigned ' . $type . '">';
        } else {
            $html .= '<select class="assignDeviceTemp form-control" id="temp_id'.$id.'" name="template[]"><optgroup >';
        }
        if (count($selectOptions) > 0) {
            // dd($selectOptions);
            foreach ($selectOptions as $user) {

                $html .= '<option value="' . $user->id . '">';
                $html .= $type == 'Account' ? $user->name : $user->template_name;
                $html .= '</option>';
            }
        }
        $html .= '</optgroup></select><div class="text-center" style="font-size: 14px;margin-top: 15px;">Note: "You are trying to change the assigned Account. Device will be NO more visible in the current Account and its "Reseller" or "User" Accounts. Do you want to proceed? </div></div><div class="modal-footer text-center"><button type="button" data-attr="'.$id.'" data-category-id="'.$id.'" class="btn btn-primary btn-raised rippler rippler-default ' . $submitRequestId . '"><i class="fa fa-check"></i> Assign</button></div></div></div></div></div></div></div>';

        return $html;
    }
    public static function getDeviceCategoryName($id)
    {
        $category = DeviceCategory::select('device_category_name')->where('id', $id)->first();
        return isset($category) ? $category->device_category_name : '';
    }
    public static function getDeviceUserName($id)
    {
        $category = Writer::select('name')->where('id', $id)->first();
        return isset($category) ? $category->name : '';
    }
    public static function getDeviceConfigurationInputs($id)
    {

        $category = DeviceCategory::select('inputs')->where('id', $id)->first();
        return json_decode($category->inputs);
    }
    // public static function unassignDevices($device_category_id)
    // {
    //     $unassignedDevices  = Device::select('id', 'imei', 'device_category_id')->where('device_category_id', $device_category_id)->get();
    //     $html = '';
    //     if (count($unassignedDevices) > 0) {

    //         foreach ($unassignedDevices as $user) {
    //             if ($user->device_category_id == $device_category_id) {
    //                 $html .= '<option value="' . $user->id . '">' . $user->imei . '</option>';
    //             }
    //         }
    //     }

    //     return $html;
    // }
    public static function unassignDevices($device_category_id)
    {
        $user = Auth::user();
        if ($user->user_type == 'Admin') {
            $unassignedDevices = Device::select('id', 'imei', 'device_category_id')
            ->where('device_category_id', $device_category_id)
            ->get();
        } else if ($user->user_type == 'Reseller') {
            $unassignedDevices = Device::select('id', 'imei', 'device_category_id')
            ->where('device_category_id', $device_category_id)
            ->where('master_id', $user->id)
            ->get();
        }else{
            $unassignedDevices = Device::select('id', 'imei', 'device_category_id')
            ->where('device_category_id', $device_category_id)
            ->where('user_id', $user->id)
            ->get();
        }
        $html = '';
        if ($unassignedDevices->isNotEmpty()) {
            foreach ($unassignedDevices as $device) {
                if ($device->device_category_id == $device_category_id) {
                    $html .= '<option value="' . $device->id . '">' . $device->imei . '</option>';
                }
            }
        }
        return $html;
    }
    public static function getConfigurations($config, $url_type)
    {
        $configurations = json_decode($config);

        $html = "";

        foreach ($configurations as $input) {
            $html .= "<tr>";
            $html .= "<td>" . (isset($input->ip) ? $input->ip : '') . "</td>";
            $html .= "<td>" . (isset($input->port) ? $input->port : '') . "</td>";
            $html .= "<td>" . (isset($input->logs_interval) ? $input->logs_interval : '') . "</td>";
            $html .= "<td>" . (isset($input->sleep_interval) ? $input->sleep_interval : '') . "</td>";
            $html .= "<td>" . (isset($input->transmission_interval) ? $input->transmission_interval : '') . "</td>";
            $html .= "<td>" . (isset($input->active_status) ? $input->active_status : '') . "</td>";
            $html .= "<td>" . (isset($input->fota) ? $input->fota : '') . "</td>";
            $html .= '<td><a href="' . $url_type . '" class="btn btn-primary btn-sm">Edit</a></td>';
            $html .= "</tr>";
        }

        return $html;
    }

    public static function getTemplates($id, $key, $selected)
    {
        $device_category = DeviceCategory::find($id);
        if (Auth::user()->user_type == 'Reseller') {
            // $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
            $getTemplateByDeviceCategory = Template::select('*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->where('device_category_id', $device_category->id)->get();
        } else {
            $getTemplateByDeviceCategory = Template::select('*')->where('templates.is_deleted', '0')->where('verify', '1')->where('device_category_id', $device_category->id)->get();
        }
        $html = '<select class="templates" id="templates' . $key . '" name="configuration[' . $key . '][template]"  onchange="changeTemplate(' . $key . ')">';

        if (count($getTemplateByDeviceCategory) > 0) {
            // $html .= '<option value ="">please Select</option>';
            foreach ($getTemplateByDeviceCategory as $temp) {
                $html .= '<option ' . (($selected != '' && $selected == $temp->id) ? 'selected' : '') . ' value="' . $temp->id . '">' . $temp->template_name . '</option>';
            }
        } else {
            $html .= '<option> No Template Found</option>';
        }
        $html .= '</select>';


        return $html;
        // }
    }
    public static function getTemplatesInfo($categoryId)
    {
        if (Auth::user()->user_type == 'Admin') {
            $templates = Template::leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                ->select('templates.*', 'writers.name as username')
                ->where('templates.is_deleted', '0')
                ->where('verify', '1')
                ->where('templates.device_category_id', $categoryId)
                ->orderBy('templates.default_template', 'DESC')
                ->get();
        } else {
            $templates = Template::leftJoin('writers', 'writers.id', '=', 'templates.user_id')
                ->select('templates.*', 'writers.name as username')
                ->where('templates.is_deleted', '0')
                ->where('verify', '2')
                ->where('id_user', auth()->id())
                ->where('templates.device_category_id', $categoryId)
                ->orderBy('templates.default_template', 'DESC')
                ->get();
        }
        return $templates;
    }
    public static function getTemplateNameById($id)
    {
        $template = Template::select('template_name')->where('id', $id)->first();
        return $template->template_name;
    }
    public static function getConfigurationInput($categoryId, $key, $configurations)
    {
        //dd($categoryId);
        $categoryConfiguration = DeviceCategory::select('inputs')->where('id', $categoryId)->first();
        if (!$categoryConfiguration) {
            return ''; // Handle case where category configuration is not found
        }

        $inputFields = json_decode($categoryConfiguration->inputs);

        $html = '';

        foreach ($inputFields as $inputs) {
               $html .= '<input class="form-control inputType" type="hidden"  
                        name="idParameters[' . $key . '][]" 
                        value="' . (isset($configurations[strtolower(str_replace(' ', '_', $inputs->key))]) ? $configurations[strtolower(str_replace(' ', '_', $inputs->key))]['id'] : '') . '"/>';
            $dataFieldOptions = self::getDataFieldById($inputs->id);
            $fieldValidate = json_decode($dataFieldOptions->validationConfig);
            if ($inputs->type == 'select') {
                $html .= '<div class="form-group">
                            <label class="control-label col-lg-5">' . $inputs->key . ' ' . (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>
                            <div class="col-lg-6">
                                <select class="form-control inputType" name="configuration[' . $key . '][' . strtolower(str_replace(' ', '_', $inputs->key)) . ']">';
                foreach ($dataFieldOptions->selectOptions as $keyField => $option) {
                    $value = $dataFieldOptions->selectValues[$keyField] ?? '';
                    $configKey = strtolower(str_replace(' ', '_', $inputs->key));
                    $selectedValue = $configurations[$configKey] ?? '';
                    $isSelected = $selectedValue == $value ? 'selected' : '';
                
                    $html .= '<option ' . $isSelected . ' value="' . $value . '">' . $option . '</option>';
                }
                // foreach ($inputs->selectOptions as $option) {
                //     $html .= '<option ' . (isset($configurations[strtolower(str_replace(' ', '_', $inputs->key))]) && strtolower($configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value']) == $option->value ? 'selected' : '') . ' value="' . $option->value . '">' . $option->option . '</option>';
                // }

                $html .= '</select>
                            </div>
                        </div>';
            } else {
                if ($inputs->key == 'Password') {
                    $html .= '<div class="form-group">
                    <label for="ip" class="control-label col-lg-5">' . $inputs->key . ' ' . (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>
                    <div class="col-lg-6">
                        <input class="form-control"
                            placeholder="Enter ' . $inputs->key . '"
                            id="' . strtolower(str_replace(' ', '_', $inputs->key)) . '"
                            type="' . (isset($inputs->requiredFieldInput) && $inputs->type == 'number' ? 'number' : 'text') . '"
                            name="configuration[' . $key . '][' . strtolower(str_replace(' ', '_', $inputs->key)) . ']"';
                    if (isset($inputs->type) && $inputs->type == 'number') {
                        $minValue = isset($fieldValidate->numberInput->min) ? ' minlength="' . $fieldValidate->numberInput->min . '"' : '';
                        $maxValue = isset($fieldValidate->numberInput->max) ? ' maxlength="' . $fieldValidate->numberInput->max . '"' : '';
                        $html .= $minValue . $maxValue;
                    }
                    $html .= ' value="' . (isset($configurations[strtolower(str_replace(' ', '_', $inputs->key))]) ? $configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value'] : '') . '"
                            required />
                    </div>
                </div>';
                } else {
                    $html .= '<div class="form-group">
                <label for="ip" class="control-label col-lg-5">' . $inputs->key . ' ' . (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>
                <div class="col-lg-6">
                    <input class="form-control"
                        placeholder="Enter ' . $inputs->key . '"
                        id="' . strtolower(str_replace(' ', '_', $inputs->key)) . '"
                        type="' . (isset($inputs->requiredFieldInput) && $inputs->type == 'number' ? 'number' : 'text') . '"
                        name="configuration[' . $key . '][' . strtolower(str_replace(' ', '_', $inputs->key)) . ']"';
                    if (isset($inputs->type) && $inputs->type == 'number') {
                        $minValue = isset($fieldValidate->numberInput->min) ? ' min="' . $fieldValidate->numberInput->min . '"' : '';
                        $maxValue = isset($fieldValidate->numberInput->max) ? ' max="' . $fieldValidate->numberInput->max . '"' : '';
                        $html .= $minValue . $maxValue;
                    }
                    $maxLength = (isset($inputs->type) && ($inputs->type == 'text_array' || $inputs->type == 'text' || $inputs->type == 'IP/URL') && isset($fieldValidate->maxValueInput)) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';
                    $html .= $maxLength;
                    $html .= ' value="' . (isset($configurations[strtolower(str_replace(' ', '_', $inputs->key))]) ? $configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value'] : '') . '"
                        required />
                </div>
            </div>';
                }
              
            }
             
        }
        $editableValue = isset($configurations['is_editable']) ? $configurations['is_editable']['value'] : '';
        if(Auth::user()->user_type == "Admin"){
        $html .='<div class="row">
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label for="curl" class="control-label col-lg-5">Ping Interval <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input type="number" name="configuration[' . $key . '][ping_interval]"
                            value="' . (isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : '') . '"
                            class="form-control inputType" placeholder="Ping Interval" value=""/>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label for="curl" class="control-label col-lg-5">Device Edit Permission<span class="require">*</span></label>
                      <div class="col-lg-6">
                      
                         <input type="radio" name="configuration[' . $key . '][is_editable]" value="1" ' . ($editableValue == '1' ? 'checked' : '') . ' style="height:20px; width:20px; vertical-align: middle;" required>
                            <label class="padding-10">Enable</label>
                            
                        <input type="radio" name="configuration[' . $key . '][is_editable]" value="0" ' . ($editableValue == '0' ? 'checked' : '') . ' style="height:20px; width:20px; vertical-align: middle;" required>
                            <label class="padding-10">Disable</label>
                      </div>
                    </div>
                  </div>
                </div>';
        }else{
                $html .= ' <input type="hidden" name="configuration[' . $key . '][ping_interval]" value="' . (isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : '') . '" placeholder="Ping Interval" value=""/><input type="hidden" name="configuration[' . $key . '][is_editable]" value="' . (isset($configurations['is_editable']) ?$configurations['ping_interval']['value'] : '') . '"  value=""/>';
        }

        return $html;
    }
    public static function getDeviceConfigurationInput($categoryId, $key, $configurations, $template_info, $url_type, $device)
    {
        // Retrieve category inputs configuration
        $categoryConfiguration = DeviceCategory::select('inputs')->where('id', $categoryId)->first();
        // dd($categoryConfiguration);
        if (!$categoryConfiguration) {
            return ''; // Handle case where category configuration is not found
        }

        $inputFields = json_decode($categoryConfiguration->inputs);
        // Start building the HTML
        $html = '<div class="configuration-item">';
        $html .= '<h6><b>' . CommonHelper::getDeviceCategoryName($categoryId) . '</b></h6>';
        $html .= '<div class="bgx-configurations">';
        $html .= '<div id="config-' . $key . '" class="row">';
        // Render current configuration details
        foreach ($inputFields as $field => $value1) {

            $html .= '<div class="col-lg-3 mb-3">';
            $html .= '<div class=" bgx-table-container">';
            $html .= '<div class="bgx-table-row">';
            $html .= '<div class="bgx-table-cell"><p class="card-text"><strong>' . ucfirst(str_replace('_', ' ', $value1->key)) . ':</strong> ' . (isset($configurations[strtolower(str_replace(' ', '_', $value1->key))]) ? self::getDeviceCategoryValue($value1->key,$configurations[strtolower(str_replace(' ', '_', $value1->key))]['value']) : '') . '</p></div>';
            $html .= '</div>'; // Close card-body
            $html .= '</div>'; // Close card
            $html .= '</div>'; // Close col-lg-4 mb-3
        }
        $html .= '</div>'; // Close row (config-$key)
        // Render edit form
        $html .= '<div id="form-' . $key . '" style="display: none;">';
        $html .= '<form action="/' . $url_type . '/update-device-configurations/' . $device['id'] . '" method="POST">';
        $html .= csrf_field();
        $html .= '<div class="row">';
        $html .= '<div class="col-sm-12 bgx-form-fields">';
        // Loop through category inputs configuration
        foreach ($inputFields as $field => $input) {
            // echo "<pre>";
            // print_r($input);
            $dataFieldOptions = self::getDataFieldById($input->id);
            $fieldValidate = json_decode($dataFieldOptions->validationConfig);
            // echo "<pre>";
            // print_r($fieldValidate);
            // echo  $fieldValidate->maxValueInput;
            // print_r($fieldValidate['maxValueInput']);
            $html .= '<div class="form-group">';
            $html .= '<label class="control-label col-lg-5">' . $input->key . ' ' . (isset($input->requiredFieldInput) && $input->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>';
            $html .= '<div class="col-lg-6">';

            if ($input->type == 'select') {
                // Render select input
                $html .= '<select class="form-control inputType" name="configuration[0][' . strtolower(str_replace(' ', '_', $input->key)) . ']">';
                // $html .= '<option value="">Please Select</option>';
               
                // dd($dataFieldOptions);
                foreach ($dataFieldOptions->selectOptions as $key => $option) {
                    $value = $dataFieldOptions->selectValues[$key] ?? '';
                    $configKey = strtolower(str_replace(' ', '_', $input->key));
                    $selectedValue = $configurations[$configKey] ?? '';
                    $isSelected = $selectedValue == $value ? 'selected' : '';
                
                    $html .= '<option ' . $isSelected . ' value="' . $value . '">' . $option . '</option>';
                }
                // foreach ($dataFieldOptions->selectOptions as $key => $option) {
                //     $html .= '<option ' . (isset($configurations[strtolower(str_replace(' ', '_', $input->key))]) && strtolower($configurations[strtolower(str_replace(' ', '_', $option))]) == $option ? 'selected' : '') . ' value="' . $dataFieldOptions->selectValues[$key] . '">' . $option . '</option>';
                // }
                // dd($dataFieldOptions );
                // foreach ($dataFieldOptions->selectOptions as $key => $option) {
                //     $html .= '<option ' . (isset($configurations[strtolower(str_replace(' ', '_', $input->key))]) && strtolower($configurations[strtolower(str_replace(' ', '_', $option))]) == $option->value ? 'selected' : '') . ' value="' . $dataFieldOptions->selectValues . '">' . $option . '</option>';
                // }

                $html .= '</select>';
            } else {
                // Render text input
                if ($input->key == 'Password') {
                    $minValue = isset($input->type) && $input->type == 'number' && isset($fieldValidate->numberInput->min) ? 'minlength="' . $fieldValidate->numberInput->min . '"' : '';
                    $maxValue = isset($input->type) && $input->type == 'number' && isset($fieldValidate->numberInput->max) ? 'maxlength="' . $fieldValidate->numberInput->max . '"' : '';
                    
                    $html .= '<input class="form-control" 
                                placeholder="Enter ' . $input->key . '" 
                                id="' . strtolower(str_replace(' ', '_', $input->key)) . '" 
                                type="' . (isset($input->requiredFieldInput) && $input->type == 'number' ? 'number' : 'text') . '" 
                                name="configuration[0][' . strtolower(str_replace(' ', '_', $input->key)) . ']" 
                                value="' . (isset($configurations[strtolower(str_replace(' ', '_', $input->key))]) ? htmlspecialchars($configurations[strtolower(str_replace(' ', '_', $input->key))]) : '') . '"';

                    $html .= ' ' . $minValue . ' ' . $maxValue; // Concatenate min and max attributes
                    $html .= ' required />';
                } else {
                    $minValue = isset($input->type) && $input->type == 'number' && isset($fieldValidate->numberInput->min) ? 'min="' . $fieldValidate->numberInput->min . '"' : '';
                    $maxValue = isset($input->type) && $input->type == 'number' && isset($fieldValidate->numberInput->max) ? 'max="' . $fieldValidate->numberInput->max . '"' : '';
                    //$maxLength = '';
                    $maxLength = (isset($input->type) && ($input->type == 'text_array' || $input->type == 'text' || $input->type == 'IP/URL') && isset($fieldValidate->maxValueInput)) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';
                    $addClassTextArray = isset($input->type) && $input->type == 'text_array' ? 'text-array-space' : '';
                    $addClassIpUrl = isset($input->type) && $input->type == 'IP/URL' ? 'ip-url-space' : '';
                    $html .= '<input class="form-control '.$addClassTextArray.' '.$addClassIpUrl.'" 
                                    placeholder="Enter ' . $input->key . '" 
                                    id="' . strtolower(str_replace(' ', '_', $input->key)) . '" 
                                    type="' . (isset($input->requiredFieldInput) && $input->type == 'number' ? 'number' : 'text') . '" 
                                    name="configuration[0][' . strtolower(str_replace(' ', '_', $input->key)) . ']" 
                                    value="' . (isset($configurations[strtolower(str_replace(' ', '_', $input->key))]['value']) ? htmlspecialchars($configurations[strtolower(str_replace(' ', '_', $input->key))]['value']) : '') . '"';

                    $html .= ' ' . $minValue . ' ' . $maxValue .' '. $maxLength; // Concatenate min and max attributes
                    $html .= ' required />';
                }
            }

            $html .= '</div>'; // Close col-lg-6
            $html .= '</div>'; // Close form-group
        }

        // Close form fields and add save/cancel buttons
        $html .= '</div>'; // Close col-sm-12 bgx-form-fields

        $html .= '<div class="col-sm-12 bg-margin-top text-right">';
        $html .= '<input type="hidden" id="device_id" name="device_id" value="' . $device['id'] . '" />';
        $html .= '<button type="submit" class="btn btn-primary">Save</button>';
        $html .= '<button type="button" class="btn btn-secondary cancel-btn" data-key="' . $key . '">Cancel</button>';
        $html .= '</div>'; // Close col-sm-12 bg-margin-top text-right
        $html .= '</div>'; // Close row

        $html .= '</form>'; // Close form
        $html .= '</div>'; // Close form-$key

        $html .= '</div>'; // Close bgx-configurations
        $html .= '</div>'; // Close configuration-item

        return $html;
    }
    public static function getDataFieldById($id) {
        $data = DB::table('data_fields')->where('id',$id)->first();
    
        if ($data && $data->validationConfig) {
            $validation = json_decode($data->validationConfig, true); // decode as array
    
            $data->selectOptions = $validation['selectOptions'] ?? [];
            $data->selectValues = $validation['selectValues'] ?? [];
        }
    
        return $data;
    }

    public static function getESimMakeBYCCID($id){
        $ccids = DB::table('ccids')->where('ccid',$id)->first();
        if($ccids){
        return self::getEsim($ccids->esim);
        }else{
        return "<p style='color:red;'>CCID Not in master Data</p>";
        }
    }
    public static function getDeviceSettings($categoryId, $key, $configurations)
    {

        // Start building the HTML
        $html = '<div class="configuration-item">';
        $html .= '<div class="bgx-configurations">';
        $html .= '<div id="config-' . $key . '" class="row">';
        foreach ($configurations  as $field => $value) {
                if($field == "ccid"){
          
                    $html .= '<div class="col-lg-3 mb-3">';
                    $html .= '<div class=" bgx-table-container">';
                    $html .= '<div class="bgx-table-row">';
                    $html .= '<div class="bgx-table-cell"<<p class="card-text"><strong>' . ucfirst(str_replace('_', ' ',  $field)) . ':</strong> ' . $value['value'] .' '.self::getESimMakeBYCCID($value['value']).'</p></div>';
                    $html .= '</div>'; // Close card-body
                    $html .= '</div>'; // Close card
                    $html .= '</div>'; // Close col-lg-4 mb-3
                }else{

                
                $html .= '<div class="col-lg-3 mb-3">';
                $html .= '<div class=" bgx-table-container">';
                $html .= '<div class="bgx-table-row">';
                $html .= '<div class="bgx-table-cell"<<p class="card-text"><strong>' . ucfirst(str_replace('_', ' ',  $field)) . ':</strong> ' . $value['value'] .'</p></div>';
                $html .= '</div>'; // Close card-body
                $html .= '</div>'; // Close card
                $html .= '</div>'; // Close col-lg-4 mb-3
                }
            }
        $html .= '</div>'; // Close row (config-$key)
        $html .= '</div>'; // Close bgx-configurations
        $html .= '</div>'; // Close configuration-item
        return $html;
    }

    public static function getSettingConfigurationInput($categoryId, $config)
    {   
        $getDeviceCategory = DeviceCategory::where('id', $categoryId)->first();
        // dd($getDeviceCategory);
        $inputFields = json_decode($getDeviceCategory->inputs, true); // Change to object notation ->inputs
        // print_r($inputFields);
        
        $firmwares = Firmware::where('device_category_id',$categoryId)->get();
        // print_r($firmwares);
        // die();
        $html = "";

        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-5">Firmware</label>';
        $html .= '<div class="col-lg-6">';
        $html .= '<select id="firmware" name="configuration[firmware_id]" class="form-control" placeholder="Search and Select">';

        foreach($firmwares as $firmware) {
            $selected = (isset($config['firmware_id']) && $config['firmware_id'] == $firmware->id) ? 'selected' : '';
            $html .= '<option ' . $selected . ' value="' . $firmware->id . '">' . $firmware->name . '</option>';
        }
        $html .='</select>';
        $html .= '</div>';
        $html .= '</div>';
        foreach ($inputFields as $key => $input) {
            if(isset($config[strtolower(str_replace(' ', '_', $input['key']))]['id'])){
            $html .= '<div class="form-group">';
            $html .= '<label class="control-label col-lg-5">' . $input['key'] . ' ' . (isset($input['requiredFieldInput']) && $input['requiredFieldInput'] ? '<span class="require">*</span>' : '') . '</label>';
            $html .= '<div class="col-lg-6">';
            $dataFieldOptions = self::getDataFieldById($config[strtolower(str_replace(' ', '_', $input['key']))]['id']);
            $fieldValidate = json_decode($dataFieldOptions->validationConfig);
            if ($input['type'] == 'select') {
                $html .= '<select class="form-control inputType" name="configuration[' . strtolower(str_replace(' ', '_', $input['key'])) . ']"  ' . (isset($input['requiredFieldInput']) && $input['requiredFieldInput'] ? 'required' : '') . '>';         
                // $html .= '<option value="">Please Select</option>';
                 foreach ($dataFieldOptions->selectOptions as $key => $option) {
                    $value = $dataFieldOptions->selectValues[$key] ?? '';
                    $configKey = strtolower(str_replace(' ', '_', $input['key']));
                    $selectedValue = $configurations[$configKey] ?? '';
                    $isSelected = $selectedValue == $value ? 'selected' : '';
                
                    $html .= '<option ' . $isSelected . ' value="' . $value . '">' . $option . '</option>';
                }
                // foreach ($dataFieldOptions as $option) {
                //     $selected = isset($config[strtolower(str_replace(' ', '_', $input['key']))]) && strtolower($config[strtolower(str_replace(' ', '_', $input['key']))]) == strtolower($option['value']) ? 'selected' : '';
                //     $html .= '<option ' . $selected . ' value="' . strtolower($option['value']) . '">' . $option . '</option>';
                // }
                $html .= '</select>';
            } else {
                if ($input['key'] == 'Password') {
                    $inputType = isset($input['requiredFieldInput']) && $input['type'] == 'number' ? 'number' : 'text';
                    $minValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->min) ? 'minlength="' .  $fieldValidate->numberInput->min . '"' : '';
                    $maxValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->max) ? 'maxlength="' . $fieldValidate->numberInput->max . '"' : '';
                    $maxLength = isset($input['type']) && $input['type'] == 'text' || $input['type'] == 'IP/URL' || $input['type'] == 'text_array'  && isset($fieldValidate->maxValueInput) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';
                    $value = isset($config[strtolower(str_replace(' ', '_', $input['key']))]) ? $config[strtolower(str_replace(' ', '_', $input['key']))]['value'] : $input['default'];
                    $html .= '<input class="form-control" placeholder="Enter ' . $input['key'] . '" id="' . strtolower(str_replace(' ', '_', $input['key'])) . '" type="' . $inputType . '" ';
                    $html .= $minValue . ' ' . $maxValue . ' ' .$maxLength.' ';
                    $html .= 'name="configuration[' . strtolower(str_replace(' ', '_', $input['key'])) . ']" value="' . $value . '" required />';
                } else {
                    $addClassTextArray = isset($input['type']) && $input['type'] == 'text_array' ? "text-array-space": '';
                    $addClassIpUrl = isset($input['type']) && $input['type'] == 'IP/URL' ? "ip-url-space" : '';
                    $inputType = isset($input['requiredFieldInput']) && $input['type'] == 'number' ? 'number' : 'text';
                    $minValue = isset($input['type']) && $input['type'] == 'number' && isset( $fieldValidate->numberInput->min) ? 'min="' .  $fieldValidate->numberInput->min . '"' : '';
                    $maxValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->max) ? 'max="' . $fieldValidate->numberInput->max . '"' : '';
                    $maxLength = isset($input['type']) && $input['type'] == 'text' || $input['type'] == 'IP/URL' || $input['type'] == 'text_array' && isset($fieldValidate->maxValueInput) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';

                    $value = isset($config[strtolower(str_replace(' ', '_', $input['key']))]) ? $config[strtolower(str_replace(' ', '_', $input['key']))]['value'] : $input['default'];
                    $html .= '<input class="form-control '.$addClassTextArray.' '.$addClassIpUrl.'" placeholder="Enter ' . $input['key'] . '" id="' . strtolower(str_replace(' ', '_', $input['key'])) . '" type="' . $inputType . '" ';
                    $html .= $minValue . ' ' . $maxValue . ' ' . $maxLength. ' ';
                    $html .= 'name="configuration[' . strtolower(str_replace(' ', '_', $input['key'])) . ']" value="' . $value . '" required />';
                }
            }

            $html .= '</div>';
            $html .= '</div>';
            }
        }
     $html .='<input type="hidden" name="configuration[ping_interval]"
                        value="' . (isset($config['ping_interval']) ? $config['ping_interval']['value'] : '') . '"
                        class="form-control inputType" placeholder="Ping Interval" value=""/>';

        $html .= '';

        return $html;
    }
    public static function countNoOfDevices($id){
        $count = Device::Select("id")->where('device_category_id',$id)->count();
        return $count;
    }
    public static function getFirmwareName($id){
        if($id != ""){
            $firmware = Firmware::select('name')->where(['id'=>$id])->first();
            if($firmware){
                return $firmware->name;
            }else{
                return 'not authorized';
            }
        }else{
            return 'not authorized';
        }
    }
    public static function  getUserName($id){
        $user = Writer::select('name')->where(['id'=>$id])->first();
        if($user){
        return $user->name;
        }else{
            return 'not authorized';
        }
    }
    public static function getCountryName($id){
        $stateName = DB::table('countries')->where('id',$id)->first();
        return $stateName->name;
    }
    public static function getStateName($id){
        $stateName = DB::table('states')->where('id',$id)->first();
        return $stateName->name;
    }
    public static function getEsim($id){
        $stateName = DB::table('esims')->where('id',$id)->first();
        return isset($stateName) ? $stateName->name .' ('.$stateName->profile_1.'+'.$stateName->profile_2.')': '';
    }
    public static function getBackend($id){
        $stateName = DB::table('backends')->where('id',$id)->first();
        return isset($stateName) ? $stateName->name :'';
    }
    public static function getUsersByDeviceCategory($categoryId){
        $users  = DB::table('writers')->get();
        $arr = [];
        foreach($users as $user){
            $device_category_id = explode(',',$user->device_category_id);
            if(in_array($categoryId, $device_category_id)){
                $arr[] = $user;
            }
        }
        return $arr;
    }
    public static function getReleasingNotes($id){
        $firmware = Firmware::find($id);
        $configurations = json_decode($firmware->configurations);
        $html = "<h3>Releasing Note for Firmware ".$firmware->name." Version "." ".$configurations->version." </h6>";
        $html .= isset($configurations->releasingNotes)? $configurations->releasingNotes : '';
        return $html;
    }
}
