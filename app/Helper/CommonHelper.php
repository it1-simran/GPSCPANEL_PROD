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
use phpDocumentor\Reflection\Types\Self_;

class CommonHelper
{
    public static function getDeviceCategoryValue($key, $value)
    {
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
    public static function getDateAsTimeZone($date)
    {
        $userTimezone = Auth::check() && !empty(Auth::user()->timezone)? Auth::user()->timezone: 'UTC';
        return \Carbon\Carbon::parse($date)->timezone($userTimezone)->format('d-M-y H:i:s');
    }
    public static function getDeviceCategoryTabs($device, $show_acc_wise, $urlType, $deviceCategoryId)
    {
        $currentUser = Auth::user();
        if ($currentUser->user_type != 'Admin') {
            $deviceCategoryIdsArray = explode(',',  $currentUser->device_category_id);
            $getDeviceCategory = DeviceCategory::select('id', 'device_category_name', 'is_certification_enable')->whereIn('id', $deviceCategoryIdsArray)->where('is_deleted', 0)->get();
        } else {
            $getDeviceCategory = DeviceCategory::select('id', 'device_category_name', 'is_certification_enable')->where('is_deleted', 0)->get();
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
                ->where('user_type', '!=', 'Support')
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
                $html .= '<button class="btn btn-danger btn-sm delete_all" data-category-id="' . $category->id . '" data-url="/' . $urlType . '/deleteAll">Delete All Selected</button>';
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
            if (Auth::user()->user_type == "Admin") {
                $html .= '<div class="col-lg-12 text-right margin-bottom-10">
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
            if (Auth::user()->user_type == 'Support') {
                $html .= '<th>Logs</th>';
            }
            $html .= '<th>Default Configurations </th>';
            if (Auth::user()->user_type == 'User' && (int)$category->is_certification_enable === 1) {
                $html .= '<th>Certificate</th>';
            }

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
                        $html .= '<td><input type="checkbox" class="sub_chk' . $category->id . '" data-category="' . $category->id . '" data-id="' . $contact->id . '"></td>';
                        $html .= '<td>' . $i . '</td>';
                        $html .= '<td>' . (!empty($contact->username) ? $contact->username : 'Unassigned') . '</td>';
                        $html .= '<td>' . $contact->name . '</td>';
                        $html .= '<td>' . $contact->imei . '</td>';
                        $html .= '<td>' . (isset($config['total_pings']) ? $config['total_pings'] : 0) . '</td>';
                        if (Auth::user()->user_type == 'Admin') {
                            $html .= '<td>' . (isset($config['ping_interval']) ? $config['ping_interval']['value'] : "") . '</td>';
                        }
                        $html .= '<td>' . self::getDateAsTimeZone($contact->created_at) . '</td>';
                        $html .= '<td>' . self::getDateAsTimeZone($contact->updated_at) . '</td>';

                        if (Auth::user()->user_type == 'Admin') {
                            $html .= '<td>';
                            if (isset($config['is_editable']) && $config['is_editable']['value'] == '1') {
                                $html .= '<button class="btn btn-success btn-sm">Yes</button>';
                            } else {
                                $html .= '<button class="btn btn-danger btn-sm">No</button>';
                            }
                            $html .= '</td>';
                            $html .=  '<td><button class="btn btn-carrot"><a class="text-white" href="/admin/view-device-logs/' . $contact->id . '" style="color:#fff;">Logs</a></button></td>';
                        }
                        if (Auth::user()->user_type == 'Support') {
                            $html .=  '<td><button class="btn btn-carrot"><a class="text-white" href="/support/view-device-logs/' . $contact->id . '" style="color:#fff;">Logs</a></button></td>';
                        }
                        $html .= '<td class="margin-top-11"><a href="' . url('/' . strtolower(Auth::user()->user_type) . '/view-device-configurations/' . $contact->id) . '" class="btn btn-primary btn-info">View Configuration</a></td>';
                        if (Auth::user()->user_type == 'User' && (int)$category->is_certification_enable === 1) {
                            $html .= '<td><a href="' . url('/' . strtolower(Auth::user()->user_type) . '/device/' . $contact->id . '/certificate') . '" class="btn btn-success btn-sm" data-device-id="' . $contact->id . '" data-category-id="' . $category->id . '">Certificate</a></td>';
                        }
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
            $html .= '<select class="assignDeviceTemp form-control" id="temp_id' . $id . '" name="template[]"><optgroup >';
        }
        if (count($selectOptions) > 0) {
            // dd($selectOptions);
            foreach ($selectOptions as $user) {

                $html .= '<option value="' . $user->id . '">';
                $html .= $type == 'Account' ? $user->name : $user->template_name;
                $html .= '</option>';
            }
        }
        $html .= '</optgroup></select><div class="text-center" style="font-size: 14px;margin-top: 15px;">Note: "You are trying to change the assigned Account. Device will be NO more visible in the current Account and its "Reseller" or "User" Accounts. Do you want to proceed? </div></div><div class="modal-footer text-center"><button type="button" data-attr="' . $id . '" data-category-id="' . $id . '" class="btn btn-primary btn-raised rippler rippler-default ' . $submitRequestId . '"><i class="fa fa-check"></i> Assign</button></div></div></div></div></div></div></div>';

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
        } else {
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
                                <select class="form-control inputType" name="configuration[' . $key . '][' . strtolower(str_replace(' ', '_', $inputs->key)) . ']" ' . ((isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput) ? 'required' : '') . '>';
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
                } else if ($inputs->type == 'multiselect') {
                $html .= '<div class="form-group">
                            <label class="control-label col-lg-5">' . $inputs->key . ' ' . (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>
                            <div class="col-lg-6">';
                $configKey = strtolower(str_replace(' ', '_', $inputs->key));
                $rawValue = $configurations[$configKey]['value'] ?? [];
                if (is_string($rawValue)) {
                    $decoded = json_decode($rawValue, true);
                    $selectedValues = is_array($decoded) ? $decoded : explode(',', $rawValue);
                } elseif (is_array($rawValue)) {
                    $selectedValues = $rawValue;
                } else {
                    $selectedValues = [];
                }
                $selectedValues = array_map('strval', $selectedValues);
                    $html .= '<select class="inputType select2-multiselect" 
                        id="configval' . $configKey . '"
                        name="configuration[' . $key . '][' . $configKey . '][]" 
                        multiple ' .
                    ((isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput) ? 'required' : '') .
                    ' style="width:100%">';

                foreach ($dataFieldOptions->selectOptions as $key1 => $option) {
                    $value = $dataFieldOptions->selectValues[$key1] ?? '';
                    $isSelected = in_array((string)$value, $selectedValues) ? 'selected' : '';
                    $html .= '<option value="' . htmlspecialchars($value) . '" ' . $isSelected . '>' . htmlspecialchars($option) . '</option>';
                }

                $html .= '</select>';
                $html .= '</div></div>';
                // Initialize Select2
                $html .= '<script>
                    $(document).ready(function () {
                        var $select = $("#configval' . $configKey . '");

                        $select.select2({
                            placeholder: "Select up to 3 options",
                            width: "100%"
                        });

                        $select.on("change", function () {
                            var selected = $(this).select2("val");

                            if (selected && selected.length > ' . $fieldValidate->maxSelectValue . ') {
                                // Remove the last selected item
                                selected.splice(' . $fieldValidate->maxSelectValue . ');
                                $(this).select2("val", selected);
                                alert("You can only select up to 3 options.");
                            }
                        });
                    });
                </script>';
                } else {
                if ($inputs->key == 'Password') {
                    $html .= '<div class="form-group">
                            <label for="ip" class="control-label col-lg-5">' . $inputs->key . ' ' . (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>
                            <div class="col-lg-6">
                                <input class="form-control"
                                placeholder="Enter ' . $inputs->key . '"
                                id="' . strtolower(str_replace(' ', '_', $inputs->key)) . '"
                                type="' . ($inputs->type == 'number' ? 'number' : 'text') . '"
                                name="configuration[' . $key . '][' . strtolower(str_replace(' ', '_', $inputs->key)) . ']"';
                    if (isset($inputs->type) && $inputs->type == 'number') {
                        $minValue = isset($fieldValidate->numberInput->min) ? ' minlength="' . $fieldValidate->numberInput->min . '"' : '';
                        $maxValue = isset($fieldValidate->numberInput->max) ? ' maxlength="' . $fieldValidate->numberInput->max . '"' : '';
                        $html .= $minValue . $maxValue;
                    }
                        $html .= ' value="' . (isset($configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value']) ? htmlspecialchars($configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value']) : '') . '" ' . ((isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput) ? 'required' : '') . ' />
                            </div>
                        </div>';
                } else {
                    $html .= '<div class="form-group">
                <label for="ip" class="control-label col-lg-5">' . $inputs->key . ' ' . (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>
                <div class="col-lg-6">
                    <input class="form-control"
                        placeholder="Enter ' . $inputs->key . '"
                        id="' . strtolower(str_replace(' ', '_', $inputs->key)) . '"
                        type="' . ($inputs->type == 'number' ? 'number' : 'text') . '"
                        name="configuration[' . $key . '][' . strtolower(str_replace(' ', '_', $inputs->key)) . ']"';
                    if (isset($inputs->type) && $inputs->type == 'number') {
                        $minValue = isset($fieldValidate->numberInput->min) ? ' min="' . $fieldValidate->numberInput->min . '"' : '';
                        $maxValue = isset($fieldValidate->numberInput->max) ? ' max="' . $fieldValidate->numberInput->max . '"' : '';
                        $html .= $minValue . $maxValue;
                    }
                    $maxLength = (isset($inputs->type) && ($inputs->type == 'text_array' || $inputs->type == 'text' || $inputs->type == 'IP/URL') && isset($fieldValidate->maxValueInput)) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';
                    $html .= $maxLength;
                        $html .= ' value="' . (isset($configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value']) ? htmlspecialchars($configurations[strtolower(str_replace(' ', '_', $inputs->key))]['value']) : '') . '" ' . ((isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput) ? 'required' : '') . ' />
                </div>
            </div>';
                }
            }
        }
        $editableValue = isset($configurations['is_editable']) ? $configurations['is_editable']['value'] : '';
        if (Auth::user()->user_type == "Admin") {
            $html .= '<div class="row">
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
        } else {
            $html .= ' <input type="hidden" name="configuration[' . $key . '][ping_interval]" value="' . (isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : '') . '" placeholder="Ping Interval" value=""/><input type="hidden" name="configuration[' . $key . '][is_editable]" value="' . (isset($configurations['is_editable']) ? $configurations['is_editable']['value'] : '') . '"  value=""/>';
        }

        return $html;
    }
    public static function getDeviceConfigurationInput($categoryId, $key, $configurations, $template_info, $url_type, $device)
    {
        // Retrieve category inputs configuration
        $categoryConfiguration = DeviceCategory::select('inputs')->where('id', $categoryId)->first();
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
            $html .= '<div class="bgx-table-cell"><p class="card-text"><strong>' . ucfirst(str_replace('_', ' ', $value1->key)) . ':</strong> ' .
                (isset($configurations[strtolower(str_replace(' ', '_', $value1->key))])
                    ? self::getDeviceCategoryValue(
                        $value1->key,
                        is_array($configurations[strtolower(str_replace(' ', '_', $value1->key))]['value'])
                            ? implode(', ', $configurations[strtolower(str_replace(' ', '_', $value1->key))]['value'])
                            : $configurations[strtolower(str_replace(' ', '_', $value1->key))]['value']
                    )
                    : '') .
                '</p></div>';
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
            $dataFieldOptions = self::getDataFieldById($input->id);
            $fieldValidate = json_decode($dataFieldOptions->validationConfig);
            $html .= '<div class="form-group">';
            $html .= '<label class="control-label col-lg-5">' . $input->key . ' ' . (isset($input->requiredFieldInput) && $input->requiredFieldInput ? '<span class="require">*</span>' : '') . '</label>';
            $html .= '<div class="col-lg-6">';

            if ($input->type == 'select') {
                // Render select input
                $html .= '<select class="form-control inputType" name="configuration[0][' . strtolower(str_replace(' ', '_', $input->key)) . ']">';
                foreach ($dataFieldOptions->selectOptions as $key => $option) {
                    $value = $dataFieldOptions->selectValues[$key] ?? '';
                    $configKey = strtolower(str_replace(' ', '_', $input->key));
                    $selectedValue = $configurations[$configKey] ?? '';
                    $isSelected = $selectedValue['value'] == $value ? 'selected' : '';
                    $html .= '<option ' . $isSelected . ' value="' . $value . '">' . $option . '</option>';
                }
                $html .= '</select>';
            } else if ($input->type == 'multiselect') {
                $configKey = strtolower(str_replace(' ', '_', $input->key));

                // Get the selected values from the configuration
                $selectedValues = $configurations[$configKey]['value'] ?? []; // assuming your structure is like ['value' => [...]] or ['value' => 'A,B']

                // Ensure selectedValues is always an array
                if (!is_array($selectedValues)) {
                    $selectedValues = explode(',', $selectedValues); // fallback if value is a comma-separated string
                }

                // Start building the HTML for multiselect input
                $html .= '<select id="configval' . $configKey . '" multiple class="inputType select2-multiselect" name="configuration[0][' . $configKey . '][]" style="width: 100%;">';

                // Loop through options to build each <option>
                foreach ($dataFieldOptions->selectOptions as $key => $option) {
                    $value = $dataFieldOptions->selectValues[$key] ?? '';
                    $isSelected = in_array((string)$value, array_map('strval', $selectedValues)) ? 'selected' : '';
                    $html .= '<option value="' . htmlspecialchars($value) . '" ' . $isSelected . '>' . htmlspecialchars($option) . '</option>';
                }

                $html .= '</select>';

                // Initialize Select2
                $html .= '<script>
                    $(document).ready(function () {
                        var $select = $("#configval' . $configKey . '");

                        $select.select2({
                            placeholder: "Select up to 3 options",
                            width: "100%"
                        });

                        $select.on("change", function () {
                            var selected = $(this).select2("val");

                            if (selected && selected.length > ' . $fieldValidate->maxSelectValue . ') {
                                // Remove the last selected item
                                selected.splice(' . $fieldValidate->maxSelectValue . ');
                                $(this).select2("val", selected);
                                alert("You can only select up to 3 options.");
                            }
                        });
                    });
                </script>';
                // $html .= <<<SCRIPT
                // <script>
                //     $(document).ready(function () {
                //         $('#configval{$configKey}').select2({
                //             placeholder: "Select options",
                //             allowClear: true,
                //             width: "100%"
                //         });
                //     });
                // </script>
                // SCRIPT;
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
                    $html .= '<input class="form-control ' . $addClassTextArray . ' ' . $addClassIpUrl . '" placeholder="Enter ' . $input->key . '" 
                    id="' . strtolower(str_replace(' ', '_', $input->key)) . '" 
                    type="' . ($input->type == 'number' ? 'number' : 'text') . '" name="configuration[0][' . strtolower(str_replace(' ', '_', $input->key)) . ']" value="' . (isset($configurations[strtolower(str_replace(' ', '_', $input->key))]['value']) ? htmlspecialchars($configurations[strtolower(str_replace(' ', '_', $input->key))]['value']) : '') . '"';
                    $html .= ' ' . $minValue . ' ' . $maxValue . ' ' . $maxLength;
                    $html .= ' ' . (isset($input->requiredFieldInput) && $input->requiredFieldInput ? 'required' : '') . ' />';
                }
            }

            $html .= '</div>'; // Close col-lg-6
            $html .= '</div>'; // Close form-group
        }
        $html .= '</div>'; // Close col-sm-12 bgx-form-fields
        $html .= '<div class="col-sm-12 bg-margin-top text-right">';
        $html .= '<input type="hidden" id="device_id" name="device_id" value="' . $device['id'] . '" />';
        $html .= '<button type="submit" class="btn btn-primary">Save</button>';
        $html .= '<button type="button" class="btn btn-secondary cancel-btn" data-key="' . $key . '">Cancel</button>';
        $html .= '</div>'; // Close col-sm-12 bg-margin-top text-right
        $html .= '</div>'; // Close row
        $html .= '</form>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
    public static function getDataFieldName($id)
    {
        $dataFieldName =  DataFields::select('fieldName')->where('id', $id)->first();
        return $dataFieldName->fieldName ?? '';
    }
    
        public static function getFieldValueById($id, $value)
    {
        $dataField = DataFields::find($id);

        if (!$dataField) {
            return htmlspecialchars($value);
        }
        if ($dataField->inputType === 'select') {
            $validationConfig = json_decode($dataField->validationConfig, true);
            if (
                isset($validationConfig['selectValues'], $validationConfig['selectOptions']) &&
                is_array($validationConfig['selectValues']) &&
                is_array($validationConfig['selectOptions'])
            ) {
                $key = array_search($value, $validationConfig['selectValues'], true);

                if ($key !== false && isset($validationConfig['selectOptions'][$key])) {
                    return htmlspecialchars($validationConfig['selectOptions'][$key]);
                }
            }
        }
        return htmlspecialchars($value);
    }

    public static function getCanProtocolConfigurationInput($categoryId, $key2, $configurations, $url_type, $device)
    {
        $html = '';
        $html .= '<div class="configuration-item">';
        $html .= '<h6><b>' . CommonHelper::getDeviceCategoryName($categoryId) . '</b></h6>';
        $html .= '<div class="bgx-configurations">';
        $html .= '<div id="canConfig-' . $key2 . '" class="row">';
        // Show current CAN configuration summary
        foreach ($configurations as $key => $config) {
            $value = isset($configurations[$key]['value']) ? $configurations[$key]['value'] : '';
            if (is_array($value)) $value = implode(', ', $value);
            $html .= '
            <div class="col-lg-3 mb-3">
                <div class="bgx-table-container">
                    <div class="bgx-table-row">
                        <div class="bgx-table-cell">
                            <p class="card-text" style="white-space: normal; word-break: break-word;">
                                <strong>' . self::getDataFieldName($config['id']) . ':</strong> ' . self::getFieldValueById($config['id'], $value) . '
                            </p>
                        </div>
                    </div>
                </div>
            </div>';
        }
        $html .= '</div>'; // end summary row
        // Editable CAN configuration (hidden by default)
        $html .= '<div id="canConfigForm-' . $key2 . '" style="display:none;">';
        $html .= '<form id="canForm-' . $device['id'] . '" action="/' . $url_type . '/update-canprotocol-configurations/' . $device['id'] . '" method="POST">';
        $html .= csrf_field();
        // CAN channel + protocol selection dropdowns
        $html .= '
        <div class="row">
            <div class="col-md-6">
                <label class="control-label">CAN Channel <span class="require">*</span></label>
                <select id="can_channel_' . $device['id'] . '" name="canConfiguration[can_channel]" class="form-control" required>
                    <option value="">-- Select CAN Channel --</option>
                    <option value="1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '1' ? 'selected' : '') . '>CAN 1</option>
                    <option value="2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '2' ? 'selected' : '') . '>CAN 2</option>
                    <option value="3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '3' ? 'selected' : '') . '>CAN 3</option>
                    <option value="4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '4' ? 'selected' : '') . '>CAN 4</option>
                </select>
            </div>
            <div class="col-md-6" style="margin:10px 0px;">
                <label class="control-label">Can Baud Rate <span class="require">*</span></label>
                <select id="can_baud_rate_' . $device['id'] . '" name="canConfiguration[can_baud_rate]" class="form-control" required>
                    <option value="">-- Select Protocol --</option>
                    <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
                    <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
                </select>
            </div>
            <div class="col-md-6" style="margin:10px 0px;">
                <label class="control-label">Can ID Type <span class="require">*</span></label>
                <select id="can_id_type_' . $device['id'] . '" name="canConfiguration[can_id_type]" class="form-control" required>
                    <option value="">-- Select Protocol --</option>
                    <option value="0" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '0' ? 'selected' : '') . '>Standard</option>
                    <option value="1" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '1' ? 'selected' : '') . '>Extended</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="control-label">CAN Protocol <span class="require">*</span></label>
                <select id="can_protocol_' . $device['id'] . '" name="canConfiguration[can_protocol]" class="form-control ip-url-space" onchange="selectedCanProtocol(' . $device['id'] . ')" required>
                    <option value="">-- Select Protocol --</option>
                    <option value="1" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '1' ? 'selected' : '') . '>J1979</option>
                    <option value="2" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '2' ? 'selected' : '') . '>J1939</option>
                    <option value="3" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '3' ? 'selected' : '') . '>Custom CAN</option>
                </select>
            </div>
        </div>';
        // Placeholder for dynamic CAN fields
        $html .= '<div id="dynamicCanFields-' . $device['id'] . '"></div>';
        // Form action buttons
        $html .= '
        <div class="col-sm-12 bg-margin-top text-right mt-3">
            <input type="hidden" name="device_id" value="' . $device['id'] . '" />
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary cancel-config-btn" data-key="' . $key2 . '">Cancel</button>
        </div>';

        $html .= '</form></div></div></div>';
        // ✅ Fixed JavaScript with saved value injection
        $html .= '
        <script>
            function selectedCanProtocol(deviceId) {
                var canProtocolValue = $("#can_protocol_" + deviceId).val();
                if (!canProtocolValue) return;

                $.ajax({
                    url: "' . url(
            (Auth::user()->user_type == "Admin"
                ? "admin"
                : (Auth::user()->user_type == "Reseller"
                    ? "reseller"
                    : (Auth::user()->user_type == "Support"
                        ? "support"
                        : "user"
                    )
                )
            ) . "/get-can-protocol-fields"
        ) . '",
                    type: "POST",
                    data: {
                        protocol: canProtocolValue,
                        _token: "' . csrf_token() . '"
                    },
                    success: function(fields) {
                        var html = "<div class=\'row\'>";
                        var savedConfig = ' . json_encode($configurations) . ';
                        fields.forEach(function(field) {
                            var fieldId = field.fieldName.replace(/\s+/g, "_").replace(/[^a-zA-Z0-9_]/g, "").toLowerCase();;
                            var inputType = field.inputType;
                            var savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
                            console.log("savedValue ==>",savedValue);
                            var validation = {};
                            try {
                                validation = JSON.parse(field.validationConfig || "{}");
                            } catch(e) {
                                console.warn("Invalid JSON in validationConfig for field:", field.fieldName);
                            }

                            var inputHtml = "<input type=\'hidden\' name=\'idCanParameters[" + fieldId + "]\' value=\'" + field.id + "\' />";
                            inputHtml += "<input type=\'hidden\' name=\'CanParametersType[" + fieldId + "]\' value=\'" + inputType + "\' />";
                            var attr = "id=\'" + fieldId + "_"+ deviceId + "\' name=\'canConfiguration[" + fieldId + "]\' class=\'form-control ip-url-space\' placeholder=\'Enter " + field.fieldName + "\'";

                            if (inputType === "number") {
                                if (validation.numberInput) {
                                    attr += " min=\'" + validation.numberInput.min + "\' max=\'" + validation.numberInput.max + "\'";
                                }
                                inputHtml += "<input type=\'number\' " + attr + " value=\'" + savedValue + "\' />";
                            } 
                            else if (inputType === "multiselect") {
                                var selectedValues = Array.isArray(savedValue) ? savedValue : (savedValue ? savedValue.split(",") : []);
                                inputHtml += "<select id=\'" + fieldId + "_" + deviceId + "\' multiple name=\'canConfiguration[" + fieldId + "][]\'>";
                                if (Array.isArray(savedValue)) {
                                    selectedValues = savedValue;
                                } else if (typeof savedValue === "string") {

                                    // Case 1: Format like {"65265","65276"}
                                    if (savedValue.startsWith("{") && savedValue.endsWith("}")) {
                                        selectedValues = savedValue
                                            .slice(1, -1)                // remove {}
                                            .split(",")                  // split into items
                                            .map(v => v.replace(/"/g, "")); // remove quotes
                                    }

                                    // Case 2: Format like 65265,65276
                                    else if (savedValue.length > 0) {
                                        selectedValues = savedValue
                                            .split(",")
                                            .map(v => v.replace(/"/g, "")); // remove quotes
                                    }
                                }
                                if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                                    validation.selectOptions.forEach(function(option, index) {
                                        var val = validation.selectValues ? validation.selectValues[index] : option;
                                        var selected = selectedValues.includes(val) ? "selected" : "";
                                        inputHtml += "<option value=\'" + val + "\' " + selected + ">" + option + "</option>";
                                    });
                                } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
                                    Object.entries(validation.selectOptions).forEach(function([key, value]) {
                                        var selected = selectedValues.includes(key) ? "selected" : "";
                                        inputHtml += "<option value=\'" + key + "\' " + selected + ">" + value + "</option>";
                                    });
                                }

                                inputHtml += "</select>";

                                setTimeout(() => {
                                    const $select = $("#" + fieldId + "_" + deviceId);

                                    // Initialize Select2
                                    $select.select2({
                                        placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
                                        width: "100%"
                                    });

                                    const isMultiple = $select.prop("multiple");

                                    if (isMultiple && validation.maxSelectValue) {
                                        let lastValidSelection = $select.val() || [];

                                        $select.on("change", function () {
                                            var selected = $(this).select2("val");

                                            if (selected && selected.length > validation.maxSelectValue) {
                                                selected.splice(validation.maxSelectValue);
                                                $(this).select2("val", selected);
                                                alert("You can only select up to " + validation.maxSelectValue + " options.");
                                            }
                                        });
                                    }
                                    $select.on("change", function () {
                                        var selected = $(this).val() || [];

                                        // Convert to required format
                                        var formattedValue = `{${selected.map(v => `${v}`).join(",")}}`;

                                        // Store formatted value (you can send this in AJAX or form hidden input)
                                        $(this).data("formatted-value", formattedValue);

                                        console.log("Formatted:", formattedValue);
                                    });

                                }, 200);
                            }
                            else if (inputType === "select") {
                                inputHtml += "<select " + attr + ">";
                                if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                                    validation.selectOptions.forEach(function(option, index) {
                                        var selected = savedValue == validation.selectValues[index] ? "selected" : "";
                                        inputHtml += "<option value=\'" + validation.selectValues[index] + "\' " + selected + ">" + option + "</option>";
                                    });
                                } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
                                    Object.entries(validation.selectOptions).forEach(function([key, value]) {
                                        var selected = savedValue == validation.selectValues[key] ? "selected" : "";
                                        inputHtml += "<option value=\'" + validation.selectValues[key] + "\' " + selected + ">" + value + "</option>";
                                    });
                                }
                                inputHtml += "</select>";
                                setTimeout(() => {
                                    const $select = $("#" + fieldId + "_" + deviceId);
                                    $select.select2({
                                        placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
                                        width: "100%"
                                    });
                                    $select.on("change", function () {
                                     var selected = $(this).select2("val");
                                     if (selected && selected.length > validation.maxSelectValue) {
                                        // Remove the last selected item
                                        selected.splice(validation.maxSelectValue);
                                        $(this).select2("val", selected);
                                        alert("You can only select up to "+ validation.maxSelectValue +" options.");
                                     }
                                    });
                                }, 200);
                            } 
                            else if (inputType === "text_array") {
                                var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
                                var maxValue = validation.maxValueInput || 0;
                                inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
                                    values.map(function(val, index) {
                                        return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
                                            "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
                                            "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
                                        "</div>";
                                    }).join("") +
                                    "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
                                "</div>";

                                // Single hidden field to store formatted values
                                inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration[" + fieldId + "]\' />";

                                setTimeout(function() {
                                    var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

                                    wrapper.on("click", ".add-text-input", function() {
                                        var count = wrapper.find(".text-array-item").length;
                                        if (maxValue && count >= maxValue) {
                                            alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                                            return;
                                        }
                                        var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
                                            "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
                                            "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
                                        "</div>";
                                        var addedElement = $(newInput).insertBefore($(this));
                                        addedElement.find("input").focus();
                                    });

                                    wrapper.on("click", ".remove-text-input", function() {
                                        $(this).closest(".text-array-item").remove();
                                        updateHiddenValue();
                                    });

                                    wrapper.on("input", "input[type=text]", function() {
                                        updateHiddenValue();
                                    });

                                    function updateHiddenValue() {
                                        var values = [];
                                        wrapper.find("input[type=text]").each(function() {
                                            var val = $(this).val().trim();
                                            if (val) values.push(val);
                                        });
                                        $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
                                    }

                                    updateHiddenValue();
                                }, 100);
                            } else if (inputType === "hex") {
                                var value = savedValue ? savedValue : "";
                                let attr1 = `id="${fieldId}" name="canConfiguration[${fieldId}]" class="form-control text-array-space me-2"`;
                                let maxValue = validation.maxValueInput || 0;
                                if (validation.maxValueInput) {
                                    attr1 += `maxlength="${validation.maxValueInput}" `;
                                }
                                inputHtml += `<input type="text" ${attr1} value="${value}"/>`;

                            } else {
                                var value = savedValue ? savedValue : "";
                                if (validation.maxValueInput) attr += " maxlength=\'" + validation.maxValueInput + "\'";
                                inputHtml += "<input type=\'text\' " + attr + " value=\'" + value + "\' />";
                            }
                            html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
                                "<label class=\'control-label\'>" +
                                field.fieldName +
                                " " +
                                (inputType === "text_array"
                                    ? "(You can choose up to " + validation.maxValueInput + ")"
                                    : "") +
                                " <span class=\'require\'>*</span></label>" +
                                inputHtml +
                            "</div>";


                        });

                        html += "</div>";
                        $("#dynamicCanFields-" + deviceId).html(html);
                    },
                    error: function(xhr) {
                        console.error("Error fetching CAN protocol fields", xhr);
                    }
                });
            }

            // Auto-load if saved protocol exists
            $(document).ready(function() {
                var selectedProtocol = $("#can_protocol_' . $device["id"] . '").val();
                if (selectedProtocol) {
                    selectedCanProtocol(' . $device["id"] . ');
                }
            });
        </script>';

        return $html;
    }
      public static function getCanProtocolTempConfigurationInput($categoryId, $key2, $configurations, $url_type, $device)
    {

        $html = '';
        $html .= '<div class="configuration-item">';
        $html .= '<h6><b>' . CommonHelper::getDeviceCategoryName($categoryId) . '</b></h6>';
        $html .= '<div class="bgx-configurations">';
        $html .= '<div id="canConfig-' . $key2 . '" class="row">';

        // Show current CAN configuration summary
        if ($configurations != null) {
            foreach ($configurations as $key => $config) {
                $value = isset($configurations[$key]['value']) ? $configurations[$key]['value'] : '';
                if (is_array($value)) $value = implode(', ', $value);

                $html .= '
            <div class="col-lg-3 mb-3">
                <div class="bgx-table-container">
                    <div class="bgx-table-row">
                        <div class="bgx-table-cell">
                            <p class="card-text">
                                <strong>' . self::getDataFieldName($config['id']) . ':</strong> ' . self::getFieldValueById($config['id'], $value) . '
                            </p>
                        </div>
                    </div>
                </div>
            </div>';
            }
        }

        $html .= '</div>'; // end summary row

        // Editable CAN configuration (hidden by default)
        $html .= '<div id="canConfigForm-' . $key2 . '" style="display:none;">';
        $html .= '<form id="canForm-' . $device['id'] . '" action="/' . $url_type . '/update-canprotocol-temp-configurations/' . $device['id'] . '" method="POST">';
        $html .= csrf_field();

        // CAN channel + protocol selection dropdowns
        $html .= '
        <div class="row">
            <div class="col-md-6">
                <label class="control-label">CAN Channel <span class="require">*</span></label>
                <select id="can_channel_' . $device['id'] . '" name="canConfiguration[can_channel]" class="form-control" required>
                    <option value="">-- Select CAN Channel --</option>
                    <option value="1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '1' ? 'selected' : '') . '>CAN 1</option>
                    <option value="2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '2' ? 'selected' : '') . '>CAN 2</option>
                    <option value="3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '3' ? 'selected' : '') . '>CAN 3</option>
                    <option value="4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '4' ? 'selected' : '') . '>CAN 4</option>
                </select>
            </div>
            <div class="col-md-6" style="margin:10px 0px;">
                <label class="control-label">Can Baud Rate <span class="require">*</span></label>
                <select id="can_baud_rate_' . $device['id'] . '" name="canConfiguration[can_baud_rate]" class="form-control" required>
                    <option value="">-- Select Baud Rate --</option>
                    <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
                    <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
                </select>
            </div>
            <div class="col-md-6" style="margin:10px 0px;">
                <label class="control-label">Can ID Type <span class="require">*</span></label>
                <select id="can_id_type_' . $device['id'] . '" name="canConfiguration[can_id_type]" class="form-control" required>
                    <option value="">-- Select Can ID --</option>
                    <option value="0" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '0' ? 'selected' : '') . '>Standard</option>
                    <option value="1" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '1' ? 'selected' : '') . '>Extended</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="control-label">CAN Protocol <span class="require">*</span></label>
                <select id="can_protocol_' . $device['id'] . '" name="canConfiguration[can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $device['id'] . ')" required>
                    <option value="">-- Select Protocol --</option>
                    <option value="1" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '1' ? 'selected' : '') . '>J1979</option>
                    <option value="2" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '2' ? 'selected' : '') . '>J1939</option>
                    <option value="3" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '3' ? 'selected' : '') . '>Custom CAN</option>
                </select>
            </div>
        </div>';

        // Placeholder for dynamic CAN fields
        $html .= '<div id="dynamicCanFields-' . $device['id'] . '"></div>';

        // Form action buttons
        $html .= '
        <div class="col-sm-12 bg-margin-top text-right mt-3">
            <input type="hidden" name="device_id" value="' . $device['id'] . '" />
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary cancel-config-btn" data-key="' . $key2 . '">Cancel</button>
        </div>';

        $html .= '</form></div></div></div>';

        // ✅ Fixed JavaScript with saved value injection
        $html .= '
        <script>
            function selectedCanProtocol(deviceId) {
                const canProtocolValue = $("#can_protocol_" + deviceId).val();
                if (!canProtocolValue) return;
                $.ajax({
                    url: "' . url(
            (Auth::user()->user_type == "Admin"
                ? "admin"
                : (Auth::user()->user_type == "reseller"
                    ? "reseller"
                    : (Auth::user()->user_type == "support"
                        ? "support"
                        : "user"
                    )
                )
            ) . "/get-can-protocol-fields"
        ) . '",
                    type: "POST",
                    data: {
                        protocol: canProtocolValue,
                        _token: "' . csrf_token() . '"
                    },
                    success: function(fields) {
                        let html = "<div class=\'row\'>";
                        const savedConfig = ' . json_encode($configurations) . ';
                        fields.forEach(function(field) {
                            const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
                            const inputType = field.inputType;
                            const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
                            let validation = {};
                            try {
                                validation = JSON.parse(field.validationConfig || "{}");
                            } catch (e) {
                                console.warn("Invalid JSON in validationConfig for field:", field.fieldName);
                            }
                            let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
                            inputHtml += "<input type=\'hidden\' name=\'CanParametersType[" + fieldId + "]\' value=\'" + inputType + "\' />";
                            let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${fieldId}]" class="form-control ip-url-space" placeholder="Enter ${field.fieldName}"`;
                            if (inputType === "number") {
                                if (validation.numberInput) {
                                    attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
                                }
                                inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
                            } else if (inputType === "multiselect") {
                                let selectedValues = Array.isArray(savedValue)
                                    ? savedValue
                                    : (savedValue ? savedValue.split(",") : []);

                                if (Array.isArray(savedValue)) {
                                    selectedValues = savedValue;
                                } else if (typeof savedValue === "string") {

                                    // Case 1: Format like {"65265","65276"}
                                    if (savedValue.startsWith("{") && savedValue.endsWith("}")) {
                                        selectedValues = savedValue
                                            .slice(1, -1)                // remove {}
                                            .split(",")                  // split into items
                                            .map(v => v.replace(/"/g, "")); // remove quotes
                                    }

                                    // Case 2: Format like 65265,65276
                                    else if (savedValue.length > 0) {
                                        selectedValues = savedValue
                                            .split(",")
                                            .map(v => v.replace(/"/g, "")); // remove quotes
                                    }
                                }
                                inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${fieldId}][]">`;
                                if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                                    validation.selectOptions.forEach((option, index) => {
                                        const val = validation.selectValues ? validation.selectValues[index] : option;
                                        const selected = selectedValues.includes(val) ? "selected" : "";
                                        inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
                                    });
                                } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
                                    Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                        const selected = selectedValues.includes(key) ? "selected" : "";
                                        inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
                                    });
                                }
                                inputHtml += `</select>`;
                                setTimeout(() => {
                                    const $select = $("#" + fieldId + "_" + deviceId);
                                    $select.select2({
                                        placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
                                        width: "100%"
                                    });
                                    $select.on("change", function () {
                                     var selected = $(this).select2("val");
                                     if (selected && selected.length > validation.maxSelectValue) {
                                        // Remove the last selected item
                                        selected.splice(validation.maxSelectValue);
                                        $(this).select2("val", selected);
                                        alert("You can only select up to "+ validation.maxSelectValue +" options.");
                                     }
                                     $select.on("change", function () {
                                        var selected = $(this).val() || [];

                                        // Convert to required format
                                        var formattedValue = `{${selected.map(v => `${v}`).join(",")}}`;

                                        // Store formatted value (you can send this in AJAX or form hidden input)
                                        $(this).data("formatted-value", formattedValue);

                                        console.log("Formatted:", formattedValue);
                                    });
                                    });
                                }, 200);
                            } else if (inputType === "select") {
                                inputHtml += `<select ${attr}>`;
                                if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                                    validation.selectOptions.forEach(option => {
                                        const selected = savedValue == option ? "selected" : "";
                                        inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
                                    });
                                } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
                                    Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                        const selected = savedValue == key ? "selected" : "";
                                        inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
                                    });
                                }
                                inputHtml += `</select>`;
                            } else if (inputType === "text_array") {
                                var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
                                var maxValue = validation.maxValueInput || 0;
                                inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
                                    values.map(function(val, index) {
                                        return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
                                            "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
                                            "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
                                        "</div>";
                                    }).join("") +
                                    "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
                                "</div>";

                                // Single hidden field to store formatted values
                                inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration[" + fieldId + "]\' />";

                                setTimeout(function() {
                                    var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

                                    wrapper.on("click", ".add-text-input", function() {
                                        var count = wrapper.find(".text-array-item").length;

                                        if (maxValue && count >= maxValue) {
                                            alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                                            return;
                                        }
                                        var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
                                            "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
                                            "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
                                        "</div>";
                                        $(this).before(newInput);
                                    });

                                    wrapper.on("click", ".remove-text-input", function() {
                                        $(this).closest(".text-array-item").remove();
                                        updateHiddenValue();
                                    });

                                    wrapper.on("input", "input[type=text]", function() {
                                        updateHiddenValue();
                                    });

                                    function updateHiddenValue() {
                                        var values = [];
                                        wrapper.find("input[type=text]").each(function() {
                                            var val = $(this).val().trim();
                                            if (val) values.push(val);
                                        });
                                        $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
                                    }

                                    updateHiddenValue();
                                }, 100);
                            } else if (inputType === "hex") {
                                var value = savedValue ? savedValue : "";
                                let attr1 = `id="${fieldId}" name="canConfiguration[${fieldId}]" class="form-control text-array-space me-2"`;
                                let maxValue = validation.maxValueInput || 0;
                                if (validation.maxValueInput) {
                                    attr1 += `maxlength="${validation.maxValueInput}"`;
                                }
                                inputHtml += `<input type="text" ${attr1} value="${value}"/>`;

                            } else {
                                const value = savedValue ? savedValue : "";
                                if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
                                inputHtml += `<input type="text" ${attr} value="${value}" />`;
                            }

                            html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
                                "<label class=\'control-label\'>" +
                                field.fieldName +
                                " " +
                                (inputType === "text_array"
                                    ? "(You can choose up to " + validation.maxValueInput + ")"
                                    : "") +
                                " <span class=\'require\'>*</span></label>" +
                                inputHtml +
                            "</div>";
                        });
                        html += "</div>";
                        $("#dynamicCanFields-" + deviceId).html(html);
                    },
                    error: function(xhr) {
                        console.error("Error fetching CAN protocol fields", xhr);
                    }
                });
            }
            // Auto-load if saved protocol exists
            $(document).ready(function() {
                const selectedProtocol = $("#can_protocol_' . $device['id'] . '").val();
                if (selectedProtocol) {
                    selectedCanProtocol(' . $device['id'] . ');
                }
            });
        </script>';
        return $html;
    }
    public static function getCanProtocolWriterConfigurationInput($key, $configurations)
    {
        $html = '';
        // CAN basic configuration
        $html .= '
        <div class="row">
            <div class="col-md-12">
                <label class="control-label">CAN Channel <span class="require">*</span></label>
                <select id="can_channel_' . $key . '" name="canConfiguration[' . $key . '][can_channel]" class="form-control" required>
                    <option value="">-- Select CAN Channel --</option>
                    <option value="1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '1' ? 'selected' : '') . '>CAN 1</option>
                    <option value="2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '2' ? 'selected' : '') . '>CAN 2</option>
                    <option value="3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '3' ? 'selected' : '') . '>CAN 3</option>
                    <option value="4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '4' ? 'selected' : '') . '>CAN 4</option>
                </select>
            </div>
            <div class="col-md-12 mt-3">
                <label class="control-label">CAN Baud Rate <span class="require">*</span></label>
                <select id="can_baud_rate_' . $key . '" name="canConfiguration[' . $key . '][can_baud_rate]" class="form-control" required>
                    <option value="">-- Select Baud Rate --</option>
                    <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
                    <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
                </select>
            </div>
            <div class="col-md-12 mt-3">
                <label class="control-label">CAN ID Type <span class="require">*</span></label>
                <select id="can_id_type_' . $key . '" name="canConfiguration[' . $key . '][can_id_type]" class="form-control" required>
                    <option value="">-- Select ID Type --</option>
                    <option value="0" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '0' ? 'selected' : '') . '>Standard</option>
                    <option value="1" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '1' ? 'selected' : '') . '>Extended</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="control-label">CAN Protocol <span class="require">*</span></label>
                <select id="can_protocol_' . $key . '" name="canConfiguration[' . $key . '][can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $key . ')" required>
                    <option value="">-- Select Protocol --</option>
                    <option value="1" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '1' ? 'selected' : '') . '>J1979</option>
                    <option value="2" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '2' ? 'selected' : '') . '>J1939</option>
                    <option value="3" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '3' ? 'selected' : '') . '>Custom CAN</option>
                </select>
            </div>';

        $html .= '<div id="dynamicCanFields-' . $key . '"></div>';
        $html .= '
        <div class="col-sm-12 bg-margin-top text-right mt-3">
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary cancel-can-btn" data-key="' . $key . '">Cancel</button>
        </div>';
        $html .= '
        <script>
            function selectedCanProtocol(deviceId) {
                const canProtocolValue = $("#can_protocol_" + deviceId).val();
                if (!canProtocolValue) return;
                $.ajax({
                    url: "' . url(
                        (Auth::user()->user_type == "Admin"
                            ? "admin"
                            : (Auth::user()->user_type == "reseller"
                                ? "reseller"
                                : (Auth::user()->user_type == "support"
                                    ? "support"
                                    : "user"
                                )
                            )
                        ) . "/get-can-protocol-fields"
                    ) . '",
                    type: "POST",
                    data: {
                        protocol: canProtocolValue,
                        _token: "' . csrf_token() . '"
                    },
                    success: function(fields) {
                        let html = "<div class=\'\'>";
                        const savedConfig = ' . json_encode($configurations) . ';
                        fields.forEach(function(field) {
                            const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
                            const inputType = field.inputType;
                            const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
                            let validation = {};
                            try { validation = JSON.parse(field.validationConfig || "{}"); } 
                            catch (e) { console.warn("Invalid JSON in validationConfig for field:", field.fieldName); }

                            let inputHtml = `<input type="hidden" name="idCanParameters[${deviceId}][${fieldId}]" value="${field.id}" />`;
                            inputHtml += `<input type="hidden" name="CanParametersType[${deviceId}][${fieldId}]" value="${inputType}"/>`;
                            let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${deviceId}][${fieldId}]" class="form-control ip-url-space" placeholder="Enter ${field.fieldName}"`;

                            if (inputType === "number") {
                                if (validation.numberInput) { attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`; }
                                inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
                            } else if (inputType === "multiselect") {
                                let selectedValues = Array.isArray(savedValue) ? savedValue : (savedValue ? savedValue.split(",") : []);
                                inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${deviceId}][${fieldId}][]">`;
                                if (Array.isArray(savedValue)) {
                                    selectedValues = savedValue;
                                } else if (typeof savedValue === "string") {

                                    // Case 1: Format like {"65265","65276"}
                                    if (savedValue.startsWith("{") && savedValue.endsWith("}")) {
                                        selectedValues = savedValue
                                            .slice(1, -1)                // remove {}
                                            .split(",")                  // split into items
                                            .map(v => v.replace(/"/g, "")); // remove quotes
                                    }

                                    // Case 2: Format like 65265,65276
                                    else if (savedValue.length > 0) {
                                        selectedValues = savedValue
                                            .split(",")
                                            .map(v => v.replace(/"/g, "")); // remove quotes
                                    }
                                }
                                if (validation.selectOptions) {
                                    if (Array.isArray(validation.selectOptions)) {
                                        validation.selectOptions.forEach((option, index) => {
                                            const val = validation.selectValues ? validation.selectValues[index] : option;
                                            const selected = selectedValues.includes(val) ? "selected" : "";
                                            inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
                                        });
                                    } else {
                                        Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                            const selected = selectedValues.includes(key) ? "selected" : "";
                                            inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
                                        });
                                    }
                                }
                                inputHtml += `</select>`;
                                setTimeout(() => { 
                                    $("#" + fieldId + "_" + deviceId).select2({placeholder: "Select up to " + (validation.maxSelectValue || ""), width: "100%"});
                                    $("#" + fieldId + "_" + deviceId).on("change", function () {
                                        var selected = $(this).select2("val");
            
                                        if (selected && selected.length > validation.maxSelectValue) {
                                           // Remove the last selected item
                                           selected.splice(validation.maxSelectValue);
                                           $(this).select2("val", selected);
                                           alert("You can only select up to "+validation.maxSelectValue+" options.");
                                        }
                                    });
                                    $("#" + fieldId + "_" + deviceId).on("change", function () {
                                        var selected = $(this).val() || [];

                                        // Convert to required format
                                        var formattedValue = `{${selected.map(v => `${v}`).join(",")}}`;

                                        // Store formatted value (you can send this in AJAX or form hidden input)
                                        $(this).data("formatted-value", formattedValue);

                                        console.log("Formatted:", formattedValue);
                                    });

                                }, 200);
                            } else if (inputType === "select") {
                                inputHtml += `<select ${attr}>`;
                                if (validation.selectOptions) {
                                    if (Array.isArray(validation.selectOptions)) {
                                        validation.selectOptions.forEach(option => {
                                            const selected = savedValue == option ? "selected" : "";
                                            inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
                                        });
                                    } else {
                                        Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                            const selected = savedValue == key ? "selected" : "";
                                            inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
                                        });
                                    }
                                }
                                inputHtml += `</select>`;
                            } else if (inputType === "text_array") {
                                var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
                                var maxValue = validation.maxValueInput || 0;
                                
                                inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
                                    values.map(function(val, index) {
                                        return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
                                            "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration["+deviceId+"][" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
                                            "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
                                        "</div>";
                                    }).join("") +
                                    "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
                                "</div>";

                                // Single hidden field to store formatted values
                                inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration["+deviceId+"][" + fieldId + "]\' />";

                                setTimeout(function() {
                                    var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

                                    wrapper.on("click", ".add-text-input", function() {
                                        var count = wrapper.find(".text-array-item").length;
                                        if (maxValue && count >= maxValue) {
                                            alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                                            return;
                                        }
                                        var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
                                            "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration["+deviceId+"][" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
                                            "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
                                        "</div>";
                                        $(this).before(newInput);
                                    });

                                    wrapper.on("click", ".remove-text-input", function() {
                                        $(this).closest(".text-array-item").remove();
                                        updateHiddenValue();
                                    });

                                    wrapper.on("input", "input[type=text]", function() {
                                        updateHiddenValue();
                                    });

                                    function updateHiddenValue() {
                                        var values = [];
                                        wrapper.find("input[type=text]").each(function() {
                                            var val = $(this).val().trim();
                                            if (val) values.push(val);
                                        });
                                        $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
                                    }

                                    updateHiddenValue();
                                }, 100);
                            } else if (inputType === "hex") {
                                var value = savedValue ? savedValue : "";
                                let attr1 = `id="${fieldId}_${deviceId}" name="canConfiguration[${deviceId}][${fieldId}]" class="form-control text-array-space me-2"`;
                                let maxValue = validation.maxValueInput || 0;
                                if (validation.maxValueInput) {
                                    attr1 += `maxlength="${validation.maxValueInput}"`;
                                }
                                inputHtml += `<input type="text" ${attr1} value="${savedValue}"/>`;

                            } else {
                                if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
                                inputHtml += `<input type="text" ${attr} value="${savedValue}" />`;
                            }

                            html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
                                "<label class=\'control-label\'>" +
                                field.fieldName +
                                " " +
                                (inputType === "text_array"
                                    ? "(You can choose up to " + validation.maxValueInput + ")"
                                    : "") +
                                " <span class=\'require\'>*</span></label>" +
                                inputHtml +
                            "</div>";
                        });
                        html += "</div>";
                        $("#dynamicCanFields-" + deviceId).html(html);
                    },
                    error: function(xhr) { console.error("Error fetching CAN protocol fields", xhr); }
                });
            }

            $(document).ready(function() {
                const selectedProtocol = $("#can_protocol_' . $key . '").val();
                if (selectedProtocol) { selectedCanProtocol(' . $key . '); }
            });
        </script>';

        return $html;
    }
    // public static function getCanProtocolTempConfigurationInput($categoryId, $key2, $configurations, $url_type, $device)
    // {

    //     $html = '';
    //     $html .= '<div class="configuration-item">';
    //     $html .= '<h6><b>' . CommonHelper::getDeviceCategoryName($categoryId) . '</b></h6>';
    //     $html .= '<div class="bgx-configurations">';
    //     $html .= '<div id="canConfig-' . $key2 . '" class="row">';

    //     // Show current CAN configuration summary
    //     if ($configurations != null) {
    //         foreach ($configurations as $key => $config) {
    //             $value = isset($configurations[$key]['value']) ? $configurations[$key]['value'] : '';
    //             if (is_array($value)) $value = implode(', ', $value);

    //             $html .= '
    //         <div class="col-lg-3 mb-3">
    //             <div class="bgx-table-container">
    //                 <div class="bgx-table-row">
    //                     <div class="bgx-table-cell">
    //                         <p class="card-text" style="white-space: normal; word-break: break-word;">
    //                             <strong>' . self::getDataFieldName($config['id']) . ':</strong> ' . self::getFieldValueById($config['id'], $value) . '
    //                         </p>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>';
    //         }
    //     }

    //     $html .= '</div>'; // end summary row

    //     // Editable CAN configuration (hidden by default)
    //     $html .= '<div id="canConfigForm-' . $key2 . '" style="display:none;">';
    //     $html .= '<form id="canForm-' . $device['id'] . '" action="/' . $url_type . '/update-canprotocol-temp-configurations/' . $device['id'] . '" method="POST">';
    //     $html .= csrf_field();

    //     // CAN channel + protocol selection dropdowns
    //     $html .= '
    //     <div class="row">
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Channel <span class="require">*</span></label>
    //             <select id="can_channel_' . $device['id'] . '" name="canConfiguration[can_channel]" class="form-control" required>
    //                 <option value="">-- Select CAN Channel --</option>
    //                 <option value="1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '1' ? 'selected' : '') . '>CAN 1</option>
    //                 <option value="2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '2' ? 'selected' : '') . '>CAN 2</option>
    //                 <option value="3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '3' ? 'selected' : '') . '>CAN 3</option>
    //                 <option value="4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '4' ? 'selected' : '') . '>CAN 4</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can Baud Rate <span class="require">*</span></label>
    //             <select id="can_baud_rate_' . $device['id'] . '" name="canConfiguration[can_baud_rate]" class="form-control" required>
    //                 <option value="">-- Select Baud Rate --</option>
    //                 <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
    //                 <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can ID Type <span class="require">*</span></label>
    //             <select id="can_id_type_' . $device['id'] . '" name="canConfiguration[can_id_type]" class="form-control" required>
    //                 <option value="">-- Select Can ID --</option>
    //                 <option value="0" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '0' ? 'selected' : '') . '>Standard</option>
    //                 <option value="1" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '1' ? 'selected' : '') . '>Extended</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Protocol <span class="require">*</span></label>
    //             <select id="can_protocol_' . $device['id'] . '" name="canConfiguration[can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $device['id'] . ')" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="1" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '1' ? 'selected' : '') . '>J1979</option>
    //                 <option value="2" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '2' ? 'selected' : '') . '>J1939</option>
    //                 <option value="3" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '3' ? 'selected' : '') . '>Custom CAN</option>
    //             </select>
    //         </div>
    //     </div>';

    //     // Placeholder for dynamic CAN fields
    //     $html .= '<div id="dynamicCanFields-' . $device['id'] . '"></div>';

    //     // Form action buttons
    //     $html .= '
    //     <div class="col-sm-12 bg-margin-top text-right mt-3">
    //         <input type="hidden" name="device_id" value="' . $device['id'] . '" />
    //         <button type="submit" class="btn btn-primary">Save</button>
    //         <button type="button" class="btn btn-secondary cancel-config-btn" data-key="' . $key2 . '">Cancel</button>
    //     </div>';

    //     $html .= '</form></div></div></div>';

    //     // ✅ Fixed JavaScript with saved value injection
    //     $html .= '
    //     <script>
    //         function selectedCanProtocol(deviceId) {
    //             const canProtocolValue = $("#can_protocol_" + deviceId).val();
    //             if (!canProtocolValue) return;
    //             $.ajax({
    //                 url: "' . url(
    //         (Auth::user()->user_type == "Admin"
    //             ? "admin"
    //             : (Auth::user()->user_type == "reseller"
    //                 ? "reseller"
    //                 : (Auth::user()->user_type == "support"
    //                     ? "support"
    //                     : "user"
    //                 )
    //             )
    //         ) . "/get-can-protocol-fields"
    //     ) . '",
    //                 type: "POST",
    //                 data: {
    //                     protocol: canProtocolValue,
    //                     _token: "' . csrf_token() . '"
    //                 },
    //                 success: function(fields) {
    //                     let html = "<div class=\'row\'>";
    //                     const savedConfig = ' . json_encode($configurations) . ';
    //                     fields.forEach(function(field) {
    //                         const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
    //                         const inputType = field.inputType;
    //                         const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
    //                         let validation = {};
    //                         try {
    //                             validation = JSON.parse(field.validationConfig || "{}");
    //                         } catch (e) {
    //                             console.warn("Invalid JSON in validationConfig for field:", field.fieldName);
    //                         }
    //                         let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
    //                         let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${fieldId}]" class="form-control ip-url-space" placeholder="Enter ${field.fieldName}"`;
    //                         if (inputType === "number") {
    //                             if (validation.numberInput) {
    //                                 attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
    //                             }
    //                             inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
    //                         } else if (inputType === "multiselect") {
    //                             const selectedValues = Array.isArray(savedValue)
    //                                 ? savedValue
    //                                 : (savedValue ? savedValue.split(",") : []);
    //                             inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${fieldId}][]">`;
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach((option, index) => {
    //                                     const val = validation.selectValues ? validation.selectValues[index] : option;
    //                                     const selected = selectedValues.includes(val) ? "selected" : "";
    //                                     inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                     const selected = selectedValues.includes(key) ? "selected" : "";
    //                                     inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                 });
    //                             }
    //                             inputHtml += `</select>`;
    //                             setTimeout(() => {
    //                                 const $select = $("#" + fieldId + "_" + deviceId);
    //                                 $select.select2({
    //                                     placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
    //                                     width: "100%"
    //                                 });
    //                                 $select.on("change", function () {
    //                                  var selected = $(this).select2("val");
    //                                  if (selected && selected.length > validation.maxSelectValue) {
    //                                     // Remove the last selected item
    //                                     selected.splice(validation.maxSelectValue);
    //                                     $(this).select2("val", selected);
    //                                     alert("You can only select up to "+ validation.maxSelectValue +" options.");
    //                                  }
    //                                 });
    //                             }, 200);
    //                         } else if (inputType === "select") {
    //                             inputHtml += `<select ${attr}>`;
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach(option => {
    //                                     const selected = savedValue == option ? "selected" : "";
    //                                     inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                     const selected = savedValue == key ? "selected" : "";
    //                                     inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                 });
    //                             }
    //                             inputHtml += `</select>`;
    //                         } else if (inputType === "text_array") {
    //                             var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
    //                             var maxValue = validation.maxValueInput || 0;
    //                             inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
    //                                 values.map(function(val, index) {
    //                                     return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                 }).join("") +
    //                                 "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
    //                             "</div>";

    //                             // Single hidden field to store formatted values
    //                             inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration[" + fieldId + "]\' />";

    //                             setTimeout(function() {
    //                                 var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

    //                                 wrapper.on("click", ".add-text-input", function() {
    //                                     var count = wrapper.find(".text-array-item").length;

    //                                     if (maxValue && count >= maxValue) {
    //                                         alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
    //                                         return;
    //                                     }
    //                                     var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                     var addedElement = $(newInput).insertBefore($(this));
    //                                     addedElement.find("input").focus();
    //                                 });

    //                                 wrapper.on("click", ".remove-text-input", function() {
    //                                     $(this).closest(".text-array-item").remove();
    //                                     updateHiddenValue();
    //                                 });

    //                                 wrapper.on("input", "input[type=text]", function() {
    //                                     updateHiddenValue();
    //                                 });

    //                                 function updateHiddenValue() {
    //                                     var values = [];
    //                                     wrapper.find("input[type=text]").each(function() {
    //                                         var val = $(this).val().trim();
    //                                         if (val) values.push(val);
    //                                     });
    //                                     $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
    //                                 }

    //                                 updateHiddenValue();
    //                             }, 100);
    //                         } else {
    //                             const value = savedValue ? savedValue : "";
    //                             if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
    //                             inputHtml += `<input type="text" ${attr} value="${value}" />`;
    //                         }

    //                         html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
    //                             "<label class=\'control-label\'>" +
    //                             field.fieldName +
    //                             " " +
    //                             (inputType === "text_array"
    //                                 ? "(You can choose up to " + validation.maxValueInput + ")"
    //                                 : "") +
    //                             " <span class=\'require\'>*</span></label>" +
    //                             inputHtml +
    //                         "</div>";
    //                     });
    //                     html += "</div>";
    //                     $("#dynamicCanFields-" + deviceId).html(html);
    //                 },
    //                 error: function(xhr) {
    //                     console.error("Error fetching CAN protocol fields", xhr);
    //                 }
    //             });
    //         }
    //         // Auto-load if saved protocol exists
    //         $(document).ready(function() {
    //             const selectedProtocol = $("#can_protocol_' . $device['id'] . '").val();
    //             if (selectedProtocol) {
    //                 selectedCanProtocol(' . $device['id'] . ');
    //             }
    //         });
    //     </script>';
    //     return $html;
    // }
    // public static function getCanProtocolWriterConfigurationInput($key, $configurations)
    // {
    //     $html = '';
    //     // CAN basic configuration
    //     $html .= '
    //     <div class="row">
    //         <div class="col-md-12">
    //             <label class="control-label">CAN Channel <span class="require">*</span></label>
    //             <select id="can_channel_' . $key . '" name="canConfiguration[' . $key . '][can_channel]" class="form-control" required>
    //                 <option value="">-- Select CAN Channel --</option>
    //                 <option value="1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '1' ? 'selected' : '') . '>CAN 1</option>
    //                 <option value="2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '2' ? 'selected' : '') . '>CAN 2</option>
    //                 <option value="3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '3' ? 'selected' : '') . '>CAN 3</option>
    //                 <option value="4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == '4' ? 'selected' : '') . '>CAN 4</option>
    //             </select>
    //         </div>
    //         <div class="col-md-12 mt-3">
    //             <label class="control-label">CAN Baud Rate <span class="require">*</span></label>
    //             <select id="can_baud_rate_' . $key . '" name="canConfiguration[' . $key . '][can_baud_rate]" class="form-control" required>
    //                 <option value="">-- Select Baud Rate --</option>
    //                 <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
    //                 <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
    //             </select>
    //         </div>
    //         <div class="col-md-12 mt-3">
    //             <label class="control-label">CAN ID Type <span class="require">*</span></label>
    //             <select id="can_id_type_' . $key . '" name="canConfiguration[' . $key . '][can_id_type]" class="form-control" required>
    //                 <option value="">-- Select ID Type --</option>
    //                 <option value="0" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '0' ? 'selected' : '') . '>Standard</option>
    //                 <option value="1" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == '1' ? 'selected' : '') . '>Extended</option>
    //             </select>
    //         </div>
    //         <div class="col-md-12">
    //             <label class="control-label">CAN Protocol <span class="require">*</span></label>
    //             <select id="can_protocol_' . $key . '" name="canConfiguration[' . $key . '][can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $key . ')" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="1" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '1' ? 'selected' : '') . '>J1979</option>
    //                 <option value="2" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '2' ? 'selected' : '') . '>J1939</option>
    //                 <option value="3" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == '3' ? 'selected' : '') . '>Custom CAN</option>
    //             </select>
    //         </div>';

    //     $html .= '<div id="dynamicCanFields-' . $key . '"></div>';
    //     $html .= '
    //     <div class="col-sm-12 bg-margin-top text-right mt-3">
    //         <button type="submit" class="btn btn-primary">Update</button>
    //         <button type="button" class="btn btn-secondary cancel-can-btn" data-key="' . $key . '">Cancel</button>
    //     </div>';
    //     $html .= '
    //     <script>
    //         function selectedCanProtocol(deviceId) {
    //             const canProtocolValue = $("#can_protocol_" + deviceId).val();
    //             if (!canProtocolValue) return;
    //             $.ajax({
    //                 url: "' . url(
    //         (Auth::user()->user_type == "Admin"
    //             ? "admin"
    //             : (Auth::user()->user_type == "reseller"
    //                 ? "reseller"
    //                 : (Auth::user()->user_type == "support"
    //                     ? "support"
    //                     : "user"
    //                 )
    //             )
    //         ) . "/get-can-protocol-fields"
    //     ) . '",
    //                 type: "POST",
    //                 data: {
    //                     protocol: canProtocolValue,
    //                     _token: "' . csrf_token() . '"
    //                 },
    //                 success: function(fields) {
    //                     let html = "<div class=\'\'>";
    //                     const savedConfig = ' . json_encode($configurations) . ';
    //                     fields.forEach(function(field) {
    //                         const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
    //                         const inputType = field.inputType;
    //                         const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
    //                         let validation = {};
    //                         try { validation = JSON.parse(field.validationConfig || "{}"); } 
    //                         catch (e) { console.warn("Invalid JSON in validationConfig for field:", field.fieldName); }

    //                         let inputHtml = `<input type="hidden" name="idCanParameters[${deviceId}][${fieldId}]" value="${field.id}" />`;
    //                         let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${deviceId}][${fieldId}]" class="form-control ip-url-space" placeholder="Enter ${field.fieldName}"`;

    //                         if (inputType === "number") {
    //                             if (validation.numberInput) { attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`; }
    //                             inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
    //                         } else if (inputType === "multiselect") {
    //                             const selectedValues = Array.isArray(savedValue) ? savedValue : (savedValue ? savedValue.split(",") : []);
    //                             inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${deviceId}][${fieldId}][]">`;
    //                             if (validation.selectOptions) {
    //                                 if (Array.isArray(validation.selectOptions)) {
    //                                     validation.selectOptions.forEach((option, index) => {
    //                                         const val = validation.selectValues ? validation.selectValues[index] : option;
    //                                         const selected = selectedValues.includes(val) ? "selected" : "";
    //                                         inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
    //                                     });
    //                                 } else {
    //                                     Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                         const selected = selectedValues.includes(key) ? "selected" : "";
    //                                         inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                     });
    //                                 }
    //                             }
    //                             inputHtml += `</select>`;
    //                             setTimeout(() => { 
    //                                 $("#" + fieldId + "_" + deviceId).select2({placeholder: "Select up to " + (validation.maxSelectValue || ""), width: "100%"});
    //                                 $("#" + fieldId + "_" + deviceId).on("change", function () {
    //                                     var selected = $(this).select2("val");
            
    //                                     if (selected && selected.length > validation.maxSelectValue) {
    //                                       // Remove the last selected item
    //                                       selected.splice(validation.maxSelectValue);
    //                                       $(this).select2("val", selected);
    //                                       alert("You can only select up to "+validation.maxSelectValue+" options.");
    //                                     }
    //                                 });
    //                             }, 200);
    //                         } else if (inputType === "select") {
    //                             inputHtml += `<select ${attr}>`;
    //                             if (validation.selectOptions) {
    //                                 if (Array.isArray(validation.selectOptions)) {
    //                                     validation.selectOptions.forEach(option => {
    //                                         const selected = savedValue == option ? "selected" : "";
    //                                         inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
    //                                     });
    //                                 } else {
    //                                     Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                         const selected = savedValue == key ? "selected" : "";
    //                                         inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                     });
    //                                 }
    //                             }
    //                             inputHtml += `</select>`;
    //                         } else if (inputType === "text_array") {
    //                             var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
    //                             var maxValue = validation.maxValueInput || 0;
                                
    //                             inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
    //                                 values.map(function(val, index) {
    //                                     return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration["+deviceId+"][" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                 }).join("") +
    //                                 "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
    //                             "</div>";

    //                             // Single hidden field to store formatted values
    //                             inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration["+deviceId+"][" + fieldId + "]\' />";

    //                             setTimeout(function() {
    //                                 var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

    //                                 wrapper.on("click", ".add-text-input", function() {
    //                                     var count = wrapper.find(".text-array-item").length;
    //                                     if (maxValue && count >= maxValue) {
    //                                         alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
    //                                         return;
    //                                     }
    //                                     var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration["+deviceId+"][" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                     var addedElement = $(newInput).insertBefore($(this));
    //                                     addedElement.find("input").focus();
    //                                 });

    //                                 wrapper.on("click", ".remove-text-input", function() {
    //                                     $(this).closest(".text-array-item").remove();
    //                                     updateHiddenValue();
    //                                 });

    //                                 wrapper.on("input", "input[type=text]", function() {
    //                                     updateHiddenValue();
    //                                 });

    //                                 function updateHiddenValue() {
    //                                     var values = [];
    //                                     wrapper.find("input[type=text]").each(function() {
    //                                         var val = $(this).val().trim();
    //                                         if (val) values.push(val);
    //                                     });
    //                                     $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
    //                                 }

    //                                 updateHiddenValue();
    //                             }, 100);
    //                         } else {
    //                             if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
    //                             inputHtml += `<input type="text" ${attr} value="${savedValue}" />`;
    //                         }

    //                         html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
    //                             "<label class=\'control-label\'>" +
    //                             field.fieldName +
    //                             " " +
    //                             (inputType === "text_array"
    //                                 ? "(You can choose up to " + validation.maxValueInput + ")"
    //                                 : "") +
    //                             " <span class=\'require\'>*</span></label>" +
    //                             inputHtml +
    //                         "</div>";
    //                     });
    //                     html += "</div>";
    //                     $("#dynamicCanFields-" + deviceId).html(html);
    //                 },
    //                 error: function(xhr) { console.error("Error fetching CAN protocol fields", xhr); }
    //             });
    //         }

    //         $(document).ready(function() {
    //             const selectedProtocol = $("#can_protocol_' . $key . '").val();
    //             if (selectedProtocol) { selectedCanProtocol(' . $key . '); }
    //         });
    //     </script>';

    //     return $html;
    // }
    
    // commented on 12-11-2025 to retrive value name
    // public static function getCanProtocolConfigurationInput($categoryId, $key2, $configurations, $url_type, $device)
    // {
    //     $html = '';
    //     $html .= '<div class="configuration-item">';
    //     $html .= '<h6><b>' . CommonHelper::getDeviceCategoryName($categoryId) . '</b></h6>';
    //     $html .= '<div class="bgx-configurations">';
    //     $html .= '<div id="canConfig-' . $key2 . '" class="row">';
    //     // Show current CAN configuration summary
    //     foreach ($configurations as $key => $config) {
    //         $value = isset($configurations[$key]['value']) ? $configurations[$key]['value'] : '';
    //         if (is_array($value)) $value = implode(', ', $value);
    //         $html .= '
    //         <div class="col-lg-3 mb-3">
    //             <div class="bgx-table-container">
    //                 <div class="bgx-table-row">
    //                     <div class="bgx-table-cell">
    //                         <p class="card-text">
    //                             <strong>' . self::getDataFieldName($config['id']) . ':</strong> ' . htmlspecialchars($value) . '
    //                         </p>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>';
    //     }
    //     $html .= '</div>'; // end summary row
    //     // Editable CAN configuration (hidden by default)
    //     $html .= '<div id="canConfigForm-' . $key2 . '" style="display:none;">';
    //     $html .= '<form id="canForm-' . $device['id'] . '" action="/' . $url_type . '/update-canprotocol-configurations/' . $device['id'] . '" method="POST">';
    //     $html .= csrf_field();
    //     // CAN channel + protocol selection dropdowns
    //     $html .= '
    //     <div class="row">
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Channel <span class="require">*</span></label>
    //             <select id="can_channel_' . $device['id'] . '" name="canConfiguration[can_channel]" class="form-control" required>
    //                 <option value="">-- Select CAN Channel --</option>
    //                 <option value="CAN 1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 1' ? 'selected' : '') . '>CAN 1</option>
    //                 <option value="CAN 2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 2' ? 'selected' : '') . '>CAN 2</option>
    //                 <option value="CAN 3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 3' ? 'selected' : '') . '>CAN 3</option>
    //                 <option value="CAN 4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 4' ? 'selected' : '') . '>CAN 4</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can Baud Rate <span class="require">*</span></label>
    //             <select id="can_baud_rate_' . $device['id'] . '" name="canConfiguration[can_baud_rate]" class="form-control" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
    //                 <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can ID Type <span class="require">*</span></label>
    //             <select id="can_id_type_' . $device['id'] . '" name="canConfiguration[can_id_type]" class="form-control" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="Standard" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == 'Standard' ? 'selected' : '') . '>Standard</option>
    //                 <option value="Extended" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == 'Extended' ? 'selected' : '') . '>Extended</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Protocol <span class="require">*</span></label>
    //             <select id="can_protocol_' . $device['id'] . '" name="canConfiguration[can_protocol]" class="form-control ip-url-space" onchange="selectedCanProtocol(' . $device['id'] . ')" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="J1979" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'J1979' ? 'selected' : '') . '>J1979</option>
    //                 <option value="J1939" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'J1939' ? 'selected' : '') . '>J1939</option>
    //                 <option value="custom" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'custom' ? 'selected' : '') . '>Custom CAN</option>
    //             </select>
    //         </div>
    //     </div>';
    //     // Placeholder for dynamic CAN fields
    //     $html .= '<div id="dynamicCanFields-' . $device['id'] . '"></div>';
    //     // Form action buttons
    //     $html .= '
    //     <div class="col-sm-12 bg-margin-top text-right mt-3">
    //         <input type="hidden" name="device_id" value="' . $device['id'] . '" />
    //         <button type="submit" class="btn btn-primary">Save</button>
    //         <button type="button" class="btn btn-secondary cancel-config-btn" data-key="' . $key2 . '">Cancel</button>
    //     </div>';

    //     $html .= '</form></div></div></div>';
    //     // ✅ Fixed JavaScript with saved value injection
    //     $html .= '
    //     <script>
    //         function selectedCanProtocol(deviceId) {
    //             var canProtocolValue = $("#can_protocol_" + deviceId).val();
    //             if (!canProtocolValue) return;

    //             $.ajax({
    //                 url: "' . url((Auth::user()->user_type == "Admin"? "admin": (Auth::user()->user_type == "Reseller"? "reseller": (Auth::user()->user_type == "Support"? "support": "user"))) . "/get-can-protocol-fields") . '",
    //                 type: "POST",
    //                 data: {
    //                     protocol: canProtocolValue,
    //                     _token: "' . csrf_token() . '"
    //                 },
    //                 success: function(fields) {
    //                     var html = "<div class=\'row\'>";
    //                     var savedConfig = ' . json_encode($configurations) . ';
    //                     fields.forEach(function(field) {
    //                         var fieldId = field.fieldName.replace(/\s+/g, "_").replace(/[^a-zA-Z0-9_]/g, "").toLowerCase();;
    //                         var inputType = field.inputType;
    //                         var savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
    //                         var validation = {};
    //                         try {
    //                             validation = JSON.parse(field.validationConfig || "{}");
    //                         } catch(e) {
    //                             console.warn("Invalid JSON in validationConfig for field:", field.fieldName);
    //                         }

    //                         var inputHtml = "<input type=\'hidden\' name=\'idCanParameters[" + fieldId + "]\' value=\'" + field.id + "\' />";

    //                         var attr = "id=\'" + fieldId + "_"+ deviceId + "\' name=\'canConfiguration[" + fieldId + "]\' class=\'form-control ip-url-space\' placeholder=\'Enter " + field.fieldName + "\'";

    //                         if (inputType === "number") {
    //                             if (validation.numberInput) {
    //                                 attr += " min=\'" + validation.numberInput.min + "\' max=\'" + validation.numberInput.max + "\'";
    //                             }
    //                             inputHtml += "<input type=\'number\' " + attr + " value=\'" + savedValue + "\' />";
    //                         } 
    //                         else if (inputType === "multiselect") {
    //                             var selectedValues = Array.isArray(savedValue) ? savedValue : (savedValue ? savedValue.split(",") : []);
    //                             inputHtml += "<select id=\'" + fieldId + "_" + deviceId + "\' multiple name=\'canConfiguration[" + fieldId + "][]\'>";
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach(function(option, index) {
    //                                     var val = validation.selectValues ? validation.selectValues[index] : option;
    //                                     var selected = selectedValues.includes(val) ? "selected" : "";
    //                                     inputHtml += "<option value=\'" + val + "\' " + selected + ">" + option + "</option>";
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(function([key, value]) {
    //                                     var selected = selectedValues.includes(key) ? "selected" : "";
    //                                     inputHtml += "<option value=\'" + key + "\' " + selected + ">" + value + "</option>";
    //                                 });
    //                             }
    //                             inputHtml += "</select>";

    //                           setTimeout(() => {
    //                                 const $select = $("#" + fieldId + "_" + deviceId);
                                
    //                                 // Initialize Select2
    //                                 $select.select2({
    //                                     placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
    //                                     width: "100%"
    //                                 });
                                
    //                                 const isMultiple = $select.prop("multiple");
                                
    //                                 if (isMultiple && validation.maxSelectValue) {
    //                                     let lastValidSelection = $select.val() || [];
                                
    //                                   $select.on("change", function () {
    //                                         var selected = $(this).select2("val");
                
    //                                         if (selected && selected.length > validation.maxSelectValue) {
    //                                             // Remove the last selected item
    //                                             selected.splice(validation.maxSelectValue);
    //                                             $(this).select2("val", selected);
    //                                             alert("You can only select up to "+validation.maxSelectValue+" options.");
    //                                         }
    //                                     });
    //                                                 }
    //                             }, 200);
    //                         } 
    //                         else if (inputType === "select") {
    //                             inputHtml += "<select " + attr + ">";
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach(function(option, index) {
    //                                     var selected = savedValue == validation.selectValues[index] ? "selected" : "";
    //                                     inputHtml += "<option value=\'" + validation.selectValues[index] + "\' " + selected + ">" + option + "</option>";
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(function([key, value]) {
    //                                     var selected = savedValue == validation.selectValues[key] ? "selected" : "";
    //                                     inputHtml += "<option value=\'" + validation.selectValues[key] + "\' " + selected + ">" + value + "</option>";
    //                                 });
    //                             }
    //                             inputHtml += "</select>";
    //                             setTimeout(() => {
    //                                 const $select = $("#" + fieldId + "_" + deviceId);
    //                                 $select.select2({
    //                                     placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
    //                                     width: "100%"
    //                                 });
    //                                 $select.on("change", function () {
    //                                  var selected = $(this).select2("val");
    //                                  if (selected && selected.length > validation.maxSelectValue) {
    //                                     // Remove the last selected item
    //                                     selected.splice(validation.maxSelectValue);
    //                                     $(this).select2("val", selected);
    //                                     alert("You can only select up to "+ validation.maxSelectValue +" options.");
    //                                  }
    //                                 });
    //                             }, 200);
    //                         } 
    //                         else if (inputType === "text_array") {
    //                             var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
    //                             var maxValue = validation.maxValueInput || 0;
    //                             inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
    //                                 values.map(function(val, index) {
    //                                     return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                 }).join("") +
    //                                 "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
    //                             "</div>";

    //                             // Single hidden field to store formatted values
    //                             inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration[" + fieldId + "]\' />";

    //                             setTimeout(function() {
    //                                 var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

    //                                 wrapper.on("click", ".add-text-input", function() {
    //                                     var count = wrapper.find(".text-array-item").length;
    //                                     if (maxValue && count >= maxValue) {
    //                                         alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
    //                                         return;
    //                                     }
    //                                     var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                     $(this).before(newInput);
    //                                 });

    //                                 wrapper.on("click", ".remove-text-input", function() {
    //                                     $(this).closest(".text-array-item").remove();
    //                                     updateHiddenValue();
    //                                 });

    //                                 wrapper.on("input", "input[type=text]", function() {
    //                                     updateHiddenValue();
    //                                 });

    //                                 function updateHiddenValue() {
    //                                     var values = [];
    //                                     wrapper.find("input[type=text]").each(function() {
    //                                         var val = $(this).val().trim();
    //                                         if (val) values.push(val);
    //                                     });
    //                                     $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
    //                                 }

    //                                 updateHiddenValue();
    //                             }, 100);
    //                         }
    //                         else {
    //                             var value = savedValue ? savedValue : "";
    //                             if (validation.maxValueInput) attr += " maxlength=\'" + validation.maxValueInput + "\'";
    //                             inputHtml += "<input type=\'text\' " + attr + " value=\'" + value + "\' />";
    //                         }
    //                         html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
    //                             "<label class=\'control-label\'>" +
    //                             field.fieldName +
    //                             " " +
    //                             (inputType === "text_array"
    //                                 ? "(You can choose up to " + validation.maxValueInput + ")"
    //                                 : "") +
    //                             " <span class=\'require\'>*</span></label>" +
    //                             inputHtml +
    //                         "</div>";


    //                     });

    //                     html += "</div>";
    //                     $("#dynamicCanFields-" + deviceId).html(html);
    //                 },
    //                 error: function(xhr) {
    //                     console.error("Error fetching CAN protocol fields", xhr);
    //                 }
    //             });
    //         }

    //         // Auto-load if saved protocol exists
    //         $(document).ready(function() {
    //             var selectedProtocol = $("#can_protocol_' . $device["id"] . '").val();
    //             if (selectedProtocol) {
    //                 selectedCanProtocol(' . $device["id"] . ');
    //             }
    //         });
    //     </script>';

    //     return $html;
    // }
    // public static function getCanProtocolTempConfigurationInput($categoryId, $key2, $configurations, $url_type, $device)
    // {

    //     $html = '';
    //     $html .= '<div class="configuration-item">';
    //     $html .= '<h6><b>' . CommonHelper::getDeviceCategoryName($categoryId) . '</b></h6>';
    //     $html .= '<div class="bgx-configurations">';
    //     $html .= '<div id="canConfig-' . $key2 . '" class="row">';

    //     // Show current CAN configuration summary
    //     if ($configurations != null) {
    //         foreach ($configurations as $key => $config) {
    //             $value = isset($configurations[$key]['value']) ? $configurations[$key]['value'] : '';
    //             if (is_array($value)) $value = implode(', ', $value);

    //             $html .= '
    //         <div class="col-lg-3 mb-3">
    //             <div class="bgx-table-container">
    //                 <div class="bgx-table-row">
    //                     <div class="bgx-table-cell">
    //                         <p class="card-text">
    //                             <strong>' . self::getDataFieldName($config['id']) . ':</strong> ' . htmlspecialchars($value) . '
    //                         </p>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>';
    //         }
    //     }

    //     $html .= '</div>'; // end summary row

    //     // Editable CAN configuration (hidden by default)
    //     $html .= '<div id="canConfigForm-' . $key2 . '" style="display:none;">';
    //     $html .= '<form id="canForm-' . $device['id'] . '" action="/' . $url_type . '/update-canprotocol-temp-configurations/' . $device['id'] . '" method="POST">';
    //     $html .= csrf_field();

    //     // CAN channel + protocol selection dropdowns
    //     $html .= '
    //     <div class="row">
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Channel <span class="require">*</span></label>
    //             <select id="can_channel_' . $device['id'] . '" name="canConfiguration[can_channel]" class="form-control" required>
    //                 <option value="">-- Select CAN Channel --</option>
    //                 <option value="CAN 1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 1' ? 'selected' : '') . '>CAN 1</option>
    //                 <option value="CAN 2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 2' ? 'selected' : '') . '>CAN 2</option>
    //                 <option value="CAN 3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 3' ? 'selected' : '') . '>CAN 3</option>
    //                 <option value="CAN 4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 4' ? 'selected' : '') . '>CAN 4</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can Baud Rate <span class="require">*</span></label>
    //             <select id="can_baud_rate_' . $device['id'] . '" name="canConfiguration[can_baud_rate]" class="form-control" required>
    //                 <option value="">-- Select Baud Rate --</option>
    //                 <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
    //                 <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can ID Type <span class="require">*</span></label>
    //             <select id="can_id_type_' . $device['id'] . '" name="canConfiguration[can_id_type]" class="form-control" required>
    //                 <option value="">-- Select Can ID --</option>
    //                 <option value="Standard" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == 'Standard' ? 'selected' : '') . '>Standard</option>
    //                 <option value="Extended" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == 'Extended' ? 'selected' : '') . '>Extended</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Protocol <span class="require">*</span></label>
    //             <select id="can_protocol_' . $device['id'] . '" name="canConfiguration[can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $device['id'] . ')" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="J1979" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'J1979' ? 'selected' : '') . '>J1979</option>
    //                 <option value="J1939" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'J1939' ? 'selected' : '') . '>J1939</option>
    //                 <option value="custom" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'custom' ? 'selected' : '') . '>Custom CAN</option>
    //             </select>
    //         </div>
    //     </div>';

    //     // Placeholder for dynamic CAN fields
    //     $html .= '<div id="dynamicCanFields-' . $device['id'] . '"></div>';

    //     // Form action buttons
    //     $html .= '
    //     <div class="col-sm-12 bg-margin-top text-right mt-3">
    //         <input type="hidden" name="device_id" value="' . $device['id'] . '" />
    //         <button type="submit" class="btn btn-primary">Save</button>
    //         <button type="button" class="btn btn-secondary cancel-config-btn" data-key="' . $key2 . '">Cancel</button>
    //     </div>';

    //     $html .= '</form></div></div></div>';

    //     // ✅ Fixed JavaScript with saved value injection
    //     $html .= '
    //     <script>
    //         function selectedCanProtocol(deviceId) {
    //             const canProtocolValue = $("#can_protocol_" + deviceId).val();
    //             if (!canProtocolValue) return;
    //             $.ajax({
    //                 url: "' . url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'reseller' ? 'reseller' : 'user')) . '/get-can-protocol-fields') . '",
    //                 type: "POST",
    //                 data: {
    //                     protocol: canProtocolValue,
    //                     _token: "' . csrf_token() . '"
    //                 },
    //                 success: function(fields) {
    //                     let html = "<div class=\'row\'>";
    //                     const savedConfig = ' . json_encode($configurations) . ';
    //                     fields.forEach(function(field) {
    //                         const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
    //                         const inputType = field.inputType;
    //                         const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
    //                         let validation = {};
    //                         try {
    //                             validation = JSON.parse(field.validationConfig || "{}");
    //                         } catch (e) {
    //                             console.warn("Invalid JSON in validationConfig for field:", field.fieldName);
    //                         }
    //                         let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
    //                         let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${fieldId}]" class="form-control ip-url-space" placeholder="Enter ${field.fieldName}"`;
    //                         if (inputType === "number") {
    //                             if (validation.numberInput) {
    //                                 attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
    //                             }
    //                             inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
    //                         } else if (inputType === "multiselect") {
    //                             const selectedValues = Array.isArray(savedValue)
    //                                 ? savedValue
    //                                 : (savedValue ? savedValue.split(",") : []);
    //                             inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${fieldId}][]">`;
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach((option, index) => {
    //                                     const val = validation.selectValues ? validation.selectValues[index] : option;
    //                                     const selected = selectedValues.includes(val) ? "selected" : "";
    //                                     inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                     const selected = selectedValues.includes(key) ? "selected" : "";
    //                                     inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                 });
    //                             }
    //                             inputHtml += `</select>`;
    //                             setTimeout(() => {
    //                                 const $select = $("#" + fieldId + "_" + deviceId);
    //                                 $select.select2({
    //                                     placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
    //                                     width: "100%"
    //                                 });
    //                                 $select.on("change", function () {
    //                                  var selected = $(this).select2("val");
    //                                  if (selected && selected.length > validation.maxSelectValue) {
    //                                     // Remove the last selected item
    //                                     selected.splice(validation.maxSelectValue);
    //                                     $(this).select2("val", selected);
    //                                     alert("You can only select up to "+ validation.maxSelectValue +" options.");
    //                                  }
    //                                 });
    //                             }, 200);
    //                         } else if (inputType === "select") {
    //                             inputHtml += `<select ${attr}>`;
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach(option => {
    //                                     const selected = savedValue == option ? "selected" : "";
    //                                     inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                     const selected = savedValue == key ? "selected" : "";
    //                                     inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                 });
    //                             }
    //                             inputHtml += `</select>`;
    //                         } else if (inputType === "text_array") {
    //                             var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
    //                             var maxValue = validation.maxValueInput || 0;
    //                             inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
    //                                 values.map(function(val, index) {
    //                                     return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                 }).join("") +
    //                                 "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
    //                             "</div>";

    //                             // Single hidden field to store formatted values
    //                             inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration[" + fieldId + "]\' />";

    //                             setTimeout(function() {
    //                                 var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

    //                                 wrapper.on("click", ".add-text-input", function() {
    //                                     var count = wrapper.find(".text-array-item").length;

    //                                     if (maxValue && count >= maxValue) {
    //                                         alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
    //                                         return;
    //                                     }
    //                                     var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration[" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                     $(this).before(newInput);
    //                                 });

    //                                 wrapper.on("click", ".remove-text-input", function() {
    //                                     $(this).closest(".text-array-item").remove();
    //                                     updateHiddenValue();
    //                                 });

    //                                 wrapper.on("input", "input[type=text]", function() {
    //                                     updateHiddenValue();
    //                                 });

    //                                 function updateHiddenValue() {
    //                                     var values = [];
    //                                     wrapper.find("input[type=text]").each(function() {
    //                                         var val = $(this).val().trim();
    //                                         if (val) values.push(val);
    //                                     });
    //                                     $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
    //                                 }

    //                                 updateHiddenValue();
    //                             }, 100);
    //                         } else {
    //                             const value = savedValue ? savedValue : "";
    //                             if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
    //                             inputHtml += `<input type="text" ${attr} value="${value}" />`;
    //                         }

    //                         html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
    //                             "<label class=\'control-label\'>" +
    //                             field.fieldName +
    //                             " " +
    //                             (inputType === "text_array"
    //                                 ? "(You can choose up to " + validation.maxValueInput + ")"
    //                                 : "") +
    //                             " <span class=\'require\'>*</span></label>" +
    //                             inputHtml +
    //                         "</div>";
    //                     });
    //                     html += "</div>";
    //                     $("#dynamicCanFields-" + deviceId).html(html);
    //                 },
    //                 error: function(xhr) {
    //                     console.error("Error fetching CAN protocol fields", xhr);
    //                 }
    //             });
    //         }
    //         // Auto-load if saved protocol exists
    //         $(document).ready(function() {
    //             const selectedProtocol = $("#can_protocol_' . $device['id'] . '").val();
    //             if (selectedProtocol) {
    //                 selectedCanProtocol(' . $device['id'] . ');
    //             }
    //         });
    //     </script>';
    //     return $html;
    // }
    // public static function getCanProtocolWriterConfigurationInput($key, $configurations)
    // {
    //     $html = '';
    //     // CAN basic configuration
    //     $html .= '
    //     <div class="row">
    //         <div class="col-md-12">
    //             <label class="control-label">CAN Channel <span class="require">*</span></label>
    //             <select id="can_channel_' . $key . '" name="canConfiguration[' . $key . '][can_channel]" class="form-control" required>
    //                 <option value="">-- Select CAN Channel --</option>
    //                 <option value="CAN 1" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 1' ? 'selected' : '') . '>CAN 1</option>
    //                 <option value="CAN 2" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 2' ? 'selected' : '') . '>CAN 2</option>
    //                 <option value="CAN 3" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 3' ? 'selected' : '') . '>CAN 3</option>
    //                 <option value="CAN 4" ' . (isset($configurations['can_channel']['value']) && $configurations['can_channel']['value'] == 'CAN 4' ? 'selected' : '') . '>CAN 4</option>
    //             </select>
    //         </div>
    //         <div class="col-md-12 mt-3">
    //             <label class="control-label">CAN Baud Rate <span class="require">*</span></label>
    //             <select id="can_baud_rate_' . $key . '" name="canConfiguration[' . $key . '][can_baud_rate]" class="form-control" required>
    //                 <option value="">-- Select Baud Rate --</option>
    //                 <option value="500" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
    //                 <option value="250" ' . (isset($configurations['can_baud_rate']['value']) && $configurations['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
    //             </select>
    //         </div>
    //         <div class="col-md-12 mt-3">
    //             <label class="control-label">CAN ID Type <span class="require">*</span></label>
    //             <select id="can_id_type_' . $key . '" name="canConfiguration[' . $key . '][can_id_type]" class="form-control" required>
    //                 <option value="">-- Select ID Type --</option>
    //                 <option value="Standard" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == 'Standard' ? 'selected' : '') . '>Standard</option>
    //                 <option value="Extended" ' . (isset($configurations['can_id_type']['value']) && $configurations['can_id_type']['value'] == 'Extended' ? 'selected' : '') . '>Extended</option>
    //             </select>
    //         </div>
    //         <div class="col-md-12">
    //             <label class="control-label">CAN Protocol <span class="require">*</span></label>
    //             <select id="can_protocol_' . $key . '" name="canConfiguration[' . $key . '][can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $key . ')" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="J1979" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'J1979' ? 'selected' : '') . '>J1979</option>
    //                 <option value="J1939" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'J1939' ? 'selected' : '') . '>J1939</option>
    //                 <option value="custom" ' . (isset($configurations['can_protocol']['value']) && $configurations['can_protocol']['value'] == 'custom' ? 'selected' : '') . '>Custom CAN</option>
    //             </select>
    //         </div>';

    //     $html .= '<div id="dynamicCanFields-' . $key . '"></div>';
    //     $html .= '
    //     <div class="col-sm-12 bg-margin-top text-right mt-3">
    //         <button type="submit" class="btn btn-primary">Update</button>
    //         <button type="button" class="btn btn-secondary cancel-can-btn" data-key="' . $key . '">Cancel</button>
    //     </div>';
    //     $html .= '
    //     <script>
    //         function selectedCanProtocol(deviceId) {
    //             const canProtocolValue = $("#can_protocol_" + deviceId).val();
    //             if (!canProtocolValue) return;
    //             $.ajax({
    //                 url: "' . url((Auth::user()->user_type == "Admin" ? "admin" : "reseller") . "/get-can-protocol-fields") . '",
    //                 type: "POST",
    //                 data: {
    //                     protocol: canProtocolValue,
    //                     _token: "' . csrf_token() . '"
    //                 },
    //                 success: function(fields) {
    //                     let html = "<div class=\'\'>";
    //                     const savedConfig = ' . json_encode($configurations) . ';
    //                     fields.forEach(function(field) {
    //                         const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
    //                         const inputType = field.inputType;
    //                         const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
    //                         let validation = {};
    //                         try { validation = JSON.parse(field.validationConfig || "{}"); } 
    //                         catch (e) { console.warn("Invalid JSON in validationConfig for field:", field.fieldName); }

    //                         let inputHtml = `<input type="hidden" name="idCanParameters[${deviceId}][${fieldId}]" value="${field.id}" />`;
    //                         let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${deviceId}][${fieldId}]" class="form-control ip-url-space" placeholder="Enter ${field.fieldName}"`;

    //                         if (inputType === "number") {
    //                             if (validation.numberInput) { attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`; }
    //                             inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
    //                         } else if (inputType === "multiselect") {
    //                             const selectedValues = Array.isArray(savedValue) ? savedValue : (savedValue ? savedValue.split(",") : []);
    //                             inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${deviceId}][${fieldId}][]">`;
    //                             if (validation.selectOptions) {
    //                                 if (Array.isArray(validation.selectOptions)) {
    //                                     validation.selectOptions.forEach((option, index) => {
    //                                         const val = validation.selectValues ? validation.selectValues[index] : option;
    //                                         const selected = selectedValues.includes(val) ? "selected" : "";
    //                                         inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
    //                                     });
    //                                 } else {
    //                                     Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                         const selected = selectedValues.includes(key) ? "selected" : "";
    //                                         inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                     });
    //                                 }
    //                             }
    //                             inputHtml += `</select>`;
    //                             setTimeout(() => { 
    //                                 $("#" + fieldId + "_" + deviceId).select2({placeholder: "Select up to " + (validation.maxSelectValue || ""), width: "100%"});
    //                                 $("#" + fieldId + "_" + deviceId).on("change", function () {
    //                                     var selected = $(this).select2("val");
            
    //                                     if (selected && selected.length > validation.maxSelectValue) {
    //                                       // Remove the last selected item
    //                                       selected.splice(validation.maxSelectValue);
    //                                       $(this).select2("val", selected);
    //                                       alert("You can only select up to "+validation.maxSelectValue+" options.");
    //                                     }
    //                                 });
    //                             }, 200);
    //                         } else if (inputType === "select") {
    //                             inputHtml += `<select ${attr}>`;
    //                             if (validation.selectOptions) {
    //                                 if (Array.isArray(validation.selectOptions)) {
    //                                     validation.selectOptions.forEach(option => {
    //                                         const selected = savedValue == option ? "selected" : "";
    //                                         inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
    //                                     });
    //                                 } else {
    //                                     Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                         const selected = savedValue == key ? "selected" : "";
    //                                         inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                     });
    //                                 }
    //                             }
    //                             inputHtml += `</select>`;
    //                         } else if (inputType === "text_array") {
    //                             var values = savedValue ? savedValue.replace(/[{}]/g, "").split(",") : [""];
    //                             var maxValue = validation.maxValueInput || 0;
                                
    //                             inputHtml += "<div id=\'" + fieldId + "_wrapper_" + deviceId + "\' class=\'text-array-wrapper\'>" +
    //                                 values.map(function(val, index) {
    //                                     return "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' maxlength=\'8\' id=\'" + fieldId + "_" + deviceId + "_" + index + "\' name=\'canConfiguration["+deviceId+"][" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' value=\'" + val.trim() + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                 }).join("") +
    //                                 "<button type=\'button\' class=\'btn btn-sm btn-primary add-text-input mt-1\'><i class=\'fa fa-plus\'></i> Add</button>" +
    //                             "</div>";

    //                             // Single hidden field to store formatted values
    //                             inputHtml += "<input type=\'hidden\' id=\'" + fieldId + "_" + deviceId + "_formatted\' name=\'canConfiguration["+deviceId+"][" + fieldId + "]\' />";

    //                             setTimeout(function() {
    //                                 var wrapper = $("#" + fieldId + "_wrapper_" + deviceId);

    //                                 wrapper.on("click", ".add-text-input", function() {
    //                                     var count = wrapper.find(".text-array-item").length;
    //                                     if (maxValue && count >= maxValue) {
    //                                         alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
    //                                         return;
    //                                     }
    //                                     var newInput = "<div class=\'text-array-item d-flex align-items-center mb-2\'>" +
    //                                         "<input type=\'text\' id=\'" + fieldId + "_" + deviceId + "_" + count + "\' name=\'canConfiguration["+deviceId+"][" + fieldId + "][]\' class=\'form-control text-array-space me-2\' placeholder=\'Enter " + field.fieldName + "\' />" +
    //                                         "<button type=\'button\' class=\'btn btn-sm btn-danger remove-text-input\'><i class=\'fa fa-minus\'></i></button>" +
    //                                     "</div>";
    //                                     $(this).before(newInput);
    //                                 });

    //                                 wrapper.on("click", ".remove-text-input", function() {
    //                                     $(this).closest(".text-array-item").remove();
    //                                     updateHiddenValue();
    //                                 });

    //                                 wrapper.on("input", "input[type=text]", function() {
    //                                     updateHiddenValue();
    //                                 });

    //                                 function updateHiddenValue() {
    //                                     var values = [];
    //                                     wrapper.find("input[type=text]").each(function() {
    //                                         var val = $(this).val().trim();
    //                                         if (val) values.push(val);
    //                                     });
    //                                     $("#" + fieldId + "_" + deviceId + "_formatted").val("{" + values.join(",") + "}");
    //                                 }

    //                                 updateHiddenValue();
    //                             }, 100);
    //                         } else {
    //                             if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
    //                             inputHtml += `<input type="text" ${attr} value="${savedValue}" />`;
    //                         }

    //                         html +="<div class=\'col-md-6 mt-3 mb-2\' style=\'margin: 10px 0px;\'>" +
    //                             "<label class=\'control-label\'>" +
    //                             field.fieldName +
    //                             " " +
    //                             (inputType === "text_array"
    //                                 ? "(You can choose up to " + validation.maxValueInput + ")"
    //                                 : "") +
    //                             " <span class=\'require\'>*</span></label>" +
    //                             inputHtml +
    //                         "</div>";
    //                     });
    //                     html += "</div>";
    //                     $("#dynamicCanFields-" + deviceId).html(html);
    //                 },
    //                 error: function(xhr) { console.error("Error fetching CAN protocol fields", xhr); }
    //             });
    //         }

    //         $(document).ready(function() {
    //             const selectedProtocol = $("#can_protocol_' . $key . '").val();
    //             if (selectedProtocol) { selectedCanProtocol(' . $key . '); }
    //         });
    //     </script>';

    //     return $html;
    // }

    // public static function getCanProtocolWriterConfigurationInput($key, $configurations)
    // {
    //     $html = '';
    //     $html .= '
    //     <div class="row">
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Channel <span class="require">*</span></label>
    //             <select id="can_channel_' . $key . '" name="canConfiguration[' . $key . '][can_channel]" class="form-control" required>
    //                 <option value="">-- Select CAN Channel --</option>
    //                 <option value="CAN 1" ' . (isset($configurations[$key]['can_channel']['value']) && $configurations[$key]['can_channel']['value'] == 'CAN 1' ? 'selected' : '') . '>CAN 1</option>
    //                 <option value="CAN 2" ' . (isset($configurations[$key]['can_channel']['value']) && $configurations[$key]['can_channel']['value'] == 'CAN 2' ? 'selected' : '') . '>CAN 2</option>
    //                 <option value="CAN 3" ' . (isset($configurations[$key]['can_channel']['value']) && $configurations[$key]['can_channel']['value'] == 'CAN 3' ? 'selected' : '') . '>CAN 3</option>
    //                 <option value="CAN 4" ' . (isset($configurations[$key]['can_channel']['value']) && $configurations[$key]['can_channel']['value'] == 'CAN 4' ? 'selected' : '') . '>CAN 4</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6">
    //             <label class="control-label">CAN Protocol <span class="require">*</span></label>
    //             <select id="can_protocol_' . $key . '" name="canConfiguration[' . $key . '][can_protocol]" class="form-control" onchange="selectedCanProtocol(' . $key . ')" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="J1979" ' . (isset($configurations[$key]['can_protocol']['value']) && $configurations[$key]['can_protocol']['value'] == 'J1979' ? 'selected' : '') . '>J1979</option>
    //                 <option value="J1939" ' . (isset($configurations[$key]['can_protocol']['value']) && $configurations[$key]['can_protocol']['value'] == 'J1939' ? 'selected' : '') . '>J1939</option>
    //                 <option value="custom" ' . (isset($configurations[$key]['can_protocol']['value']) && $configurations[$key]['can_protocol']['value'] == 'custom' ? 'selected' : '') . '>Custom CAN</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can Baud Rate <span class="require">*</span></label>
    //             <select id="can_baud_rate_' . $key . '" name="canConfiguration[' . $key . '][can_baud_rate]" class="form-control" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="500" ' . (isset($configurations[$key]['can_baud_rate']['value']) && $configurations[$key]['can_baud_rate']['value'] == '500' ? 'selected' : '') . '>500 kbps</option>
    //                 <option value="250" ' . (isset($configurations[$key]['can_baud_rate']['value']) && $configurations[$key]['can_baud_rate']['value'] == '250' ? 'selected' : '') . '>250 kbps</option>
    //             </select>
    //         </div>
    //         <div class="col-md-6" style="margin:10px 0px;">
    //             <label class="control-label">Can ID Type <span class="require">*</span></label>
    //             <select id="can_id_type_' .$key . '" name="canConfiguration[' . $key . '][can_id_type]" class="form-control" required>
    //                 <option value="">-- Select Protocol --</option>
    //                 <option value="Standard" ' . (isset($configurations[$key]['can_id_type']['value']) && $configurations[$key]['can_id_type']['value'] == 'Standard' ? 'selected' : '') . '>Standard</option>
    //                 <option value="Extended" ' . (isset($configurations[$key]['can_id_type']['value']) && $configurations[$key]['can_id_type']['value'] == 'Extended' ? 'selected' : '') . '>Extended</option>
    //             </select>
    //         </div>
    //     </div>';
    //     // foreach ($configurations as $field => $input) {
    //     //     $fieldKey = strtolower(str_replace(' ', '_', $field));
    //     //     // Fetch data field options from DB
    //     //     $dataFieldOptions = self::getDataFieldById($input['id']);
    //     //     $fieldValidate = json_decode($dataFieldOptions->validationConfig ?? '{}');
    //     //     $fieldType = $dataFieldOptions->inputType ?? 'text';
    //     //     $fieldValue = $input['value'] ?? ($configurations[$fieldKey]['value'] ?? '');
    //     //     $fieldID = ($configurations[$fieldKey]['id'] ?? '');
    //     //     $isPasswordField = ($input['key'] ?? '') === 'Password';
    //     //     // Start row
    //     //     $html .= '<div class="form-group">';
    //     //     // Label
    //     //     $html .= '<label for="' . $fieldKey . '" class="col-lg-5 col-form-label">';
    //     //     $html .= htmlspecialchars($field) . ' <span class="require">*</span>';
    //     //     $html .= '</label>';
    //     //     // Input wrapper
    //     //     $html .= '<div class="col-lg-6">';
    //     //     $html .= '<input 
    //     //                 type="hidden" 
    //     //                 name="idParameters[' . $key . '][' . $fieldKey . ']" 
    //     //                 value="' . htmlspecialchars($fieldID) . '" 
    //     //                 required />';
    //     //     if ($fieldType === 'select') {
    //     //         $html .= '<select class="form-control inputType" name="canConfiguration[' . $key . '][' . $fieldKey . ']" id="' . $fieldKey . '">';
    //     //         $html .= '<option value="">-- Select --</option>';
    //     //         foreach ($dataFieldOptions->selectOptions as $key1 => $option) {
    //     //             $value = $dataFieldOptions->selectValues[$key1] ?? '';
    //     //             $selected = $fieldValue == $value ? 'selected' : '';
    //     //             $html .= '<option ' . $selected . ' value="' . htmlspecialchars($value) . '">' . htmlspecialchars($option) . '</option>';
    //     //         }
    //     //         $html .= '</select>';
    //     //     } else if ($fieldType === 'multiselect') {
    //     //         $html .= '<select id="' . htmlspecialchars($fieldKey) . $key . '" 
    //     //        name="canConfiguration[' . $key . '][' . htmlspecialchars($fieldKey) . '][]" 
    //     //        multiple>';
    //     //         $selectedValue = isset($configurations[$fieldKey]['value']) ? $configurations[$fieldKey]['value'] : [];
    //     //         $selectedArray = is_array($selectedValue) ? $selectedValue : [$selectedValue];
    //     //         if (isset($dataFieldOptions->selectOptions) && is_array($dataFieldOptions->selectOptions)) {
    //     //             foreach ($dataFieldOptions->selectOptions as $index1 => $option) {
    //     //                 $isSelected = in_array($dataFieldOptions->selectValues[$index1], $selectedArray) ? 'selected' : '';
    //     //                 $html .= '<option value="' . htmlspecialchars($dataFieldOptions->selectValues[$index1]) . '" ' . $isSelected . '>' . htmlspecialchars($option) . '</option>';
    //     //             }
    //     //         } elseif (isset($dataFieldOptions->selectOptions) && is_object($dataFieldOptions->selectOptions)) {
    //     //             foreach ((array)$dataFieldOptions->selectOptions as $index => $label) {
    //     //                 $isSelected = in_array($dataFieldOptions->selectValues[$index], $selectedArray) ? 'selected' : '';
    //     //                 $html .= '<option value="' . htmlspecialchars($dataFieldOptions->selectValues[$index]) . '" ' . $isSelected . '>' . htmlspecialchars($label) . '</option>';
    //     //             }
    //     //         } else {
    //     //             $html .= '<option value="">-- Select --</option>';
    //     //         }
    //     //         $html .= '</select>';
    //     //         $html .= '<script>
    //     //             $(document).ready(function () {
    //     //                 var $select = $("#' . addslashes($fieldKey) . $key  . '");

    //     //                 $select.select2({
    //     //                     placeholder: "Select up to 3 options",
    //     //                     width: "100%"
    //     //                 });

    //     //                 $select.on("change", function () {
    //     //                     var selected = $(this).select2("val");

    //     //                     if (selected && selected.length > ' . $fieldValidate->maxSelectValue . ') {
    //     //                        // Remove the last selected item
    //     //                        selected.splice(' . $fieldValidate->maxSelectValue . ');
    //     //                        $(this).select2("val", selected);
    //     //                        alert("You can only select up to 3 options.");
    //     //                     }
    //     //                 });
    //     //             });
    //     //         </script>';
    //     //     } else {
    //     //         // Setup validation attributes
    //     //         $minAttr = '';
    //     //         $maxAttr = '';
    //     //         $maxLengthAttr = '';
    //     //         $extraClasses = '';
    //     //         if ($fieldType === 'number') {
    //     //             if (!empty($fieldValidate->numberInput->min)) {
    //     //                 $minAttr = 'min="' . $fieldValidate->numberInput->min . '"';
    //     //             }
    //     //             if (!empty($fieldValidate->numberInput->max)) {
    //     //                 $maxAttr = 'max="' . $fieldValidate->numberInput->max . '"';
    //     //             }
    //     //         }
    //     //         if (in_array($fieldType, ['text', 'text_array', 'IP/URL']) && !empty($fieldValidate->maxValueInput)) {
    //     //             $maxLengthAttr = 'maxlength="' . $fieldValidate->maxValueInput . '"';
    //     //         }
    //     //         if ($fieldType === 'text_array') {
    //     //             $extraClasses .= ' text-array-space';
    //     //         } elseif ($fieldType === 'IP/URL') {
    //     //             $extraClasses .= ' ip-url-space';
    //     //         }
    //     //         $inputType = ($fieldType === 'number') ? 'number' : 'text';
    //     //         $html .= '<input 
    //     //                 class="form-control' . $extraClasses . '" 
    //     //                 placeholder="Enter ' . htmlspecialchars($field) . '" 
    //     //                 id="' . $fieldKey . '" 
    //     //                 type="' . $inputType . '" 
    //     //                 name="canConfiguration[' . $key . '][' . $fieldKey . ']" 
    //     //                 value="' . htmlspecialchars($fieldValue) . '" 
    //     //                 ' . $minAttr . ' ' . $maxAttr . ' ' . $maxLengthAttr . ' 
    //     //                 required />';
    //     //     }
    //     //     $html .= '</div>'; // col-lg-6
    //     //     $html .= '</div>'; // form-group row
    //     // }
    //     $html .= '<div class="col-sm-12 bg-margin-top text-right">
    //                     <button type="submit" class="btn btn-primary">Update</button>
    //                     <button type="button" class="btn btn-secondary cancel-can-btn" data-key="' . $key . '">Cancel</button>
    //                 </div';
    //     $html .= '
    //     <script>
    //         function selectedCanProtocol(deviceId) {
    //             const canProtocolValue = $("#can_protocol_" + deviceId).val();
    //             if (!canProtocolValue) return;
    //             $.ajax({
    //                 url: "' . url((Auth::user()->user_type == "Admin" ? "admin" : "reseller") . "/get-can-protocol-fields") . '",
    //                 type: "POST",
    //                 data: {
    //                     protocol: canProtocolValue,
    //                     _token: "' . csrf_token() . '"
    //                 },
    //                 success: function(fields) {
    //                     let html = "<div class=\'row\'>";
    //                     const savedConfig = ' . json_encode($configurations) . ';
    //                     fields.forEach(function(field) {
    //                         const fieldId = field.fieldName.replace(/\\s+/g, "_").toLowerCase();
    //                         const inputType = field.inputType;
    //                         const savedValue = savedConfig[fieldId] && savedConfig[fieldId].value ? savedConfig[fieldId].value : "";
    //                         let validation = {};
    //                         try {
    //                             validation = JSON.parse(field.validationConfig || "{}");
    //                         } catch (e) {
    //                             console.warn("Invalid JSON in validationConfig for field:", field.fieldName);
    //                         }
    //                         let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
    //                         let attr = `id="${fieldId}_${deviceId}" name="canConfiguration[${fieldId}]" class="form-control" placeholder="Enter ${field.fieldName}"`;
    //                         if (inputType === "number") {
    //                             if (validation.numberInput) {
    //                                 attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
    //                             }
    //                             inputHtml += `<input type="number" ${attr} value="${savedValue}" />`;
    //                         } else if (inputType === "multiselect") {
    //                             const selectedValues = Array.isArray(savedValue)
    //                                 ? savedValue
    //                                 : (savedValue ? savedValue.split(",") : []);
    //                             inputHtml += `<select id="${fieldId}_${deviceId}" multiple name="canConfiguration[${fieldId}][]">`;
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach((option, index) => {
    //                                     const val = validation.selectValues ? validation.selectValues[index] : option;
    //                                     const selected = selectedValues.includes(val) ? "selected" : "";
    //                                     inputHtml += `<option value="${val}" ${selected}>${option}</option>`;
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                     const selected = selectedValues.includes(key) ? "selected" : "";
    //                                     inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                 });
    //                             }
    //                             inputHtml += `</select>`;
    //                             setTimeout(() => {
    //                                 const $select = $("#" + fieldId + "_" + deviceId);
    //                                 $select.select2({
    //                                     placeholder: "Select up to " + (validation.maxSelectValue || "") + " options",
    //                                     width: "100%"
    //                                 });
    //                             }, 200);
    //                         } else if (inputType === "select") {
    //                             inputHtml += `<select ${attr}>`;
    //                             if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
    //                                 validation.selectOptions.forEach(option => {
    //                                     const selected = savedValue == option ? "selected" : "";
    //                                     inputHtml += `<option value="${option}" ${selected}>${option}</option>`;
    //                                 });
    //                             } else if (validation.selectOptions && typeof validation.selectOptions === "object") {
    //                                 Object.entries(validation.selectOptions).forEach(([key, value]) => {
    //                                     const selected = savedValue == key ? "selected" : "";
    //                                     inputHtml += `<option value="${key}" ${selected}>${value}</option>`;
    //                                 });
    //                             }
    //                             inputHtml += `</select>`;
    //                         } else {
    //                             const value = savedValue ? savedValue : "";
    //                             if (validation.maxValueInput) attr += ` maxlength="${validation.maxValueInput}"`;
    //                             inputHtml += `<input type="text" ${attr} value="${value}" />`;
    //                         }
    //                         html += `
    //                             <div class="col-md-6 mt-3 mb-2" style="margin: 10px 0px;">
    //                                 <label class="control-label">${field.fieldName} <span class="require">*</span></label>
    //                                 ${inputHtml}
    //                             </div>`;
    //                     });
    //                     html += "</div>";
    //                     $("#dynamicCanFields-" + deviceId).html(html);
    //                 },
    //                 error: function(xhr) {
    //                     console.error("Error fetching CAN protocol fields", xhr);
    //                 }
    //             });
    //         }
    //         // Auto-load if saved protocol exists
    //         $(document).ready(function() {
    //             const selectedProtocol = $("#can_protocol_' . $key . '").val();
    //             if (selectedProtocol) {
    //                 selectedCanProtocol(' . $key . ');
    //             }
    //         });
    //     </script>';
    //     return $html;
    // }
    public static function getDataFieldById($id)
    {
        $data = DB::table('data_fields')->where('id', $id)->first();
        if ($data && $data->validationConfig) {
            $validation = json_decode($data->validationConfig, true); // decode as array
            $data->selectOptions = $validation['selectOptions'] ?? [];
            $data->selectValues = $validation['selectValues'] ?? [];
        }
        return $data;
    }
    public static function getESimMakeBYCCID($id)
    {
        $ccids = DB::table('ccids')->where('ccid', $id)->first();
        if ($ccids) {
            return self::getEsim($ccids->esim);
        } else {
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
            if ($field == "ccid") {
                $html .= '<div class="col-lg-3 mb-3">';
                $html .= '<div class=" bgx-table-container">';
                $html .= '<div class="bgx-table-row">';
                $html .= '<div class="bgx-table-cell"<<p class="card-text"><strong>' . ucfirst(str_replace('_', ' ',  $field)) . ':</strong> ' . $value['value'] . ' ' . self::getESimMakeBYCCID($value['value']) . '</p></div>';
                $html .= '</div>'; // Close card-body
                $html .= '</div>'; // Close card
                $html .= '</div>'; // Close col-lg-4 mb-3
            } else {
                $html .= '<div class="col-lg-3 mb-3">';
                $html .= '<div class=" bgx-table-container">';
                $html .= '<div class="bgx-table-row">';
                $html .= '<div class="bgx-table-cell"<<p class="card-text"><strong>' . ucfirst(str_replace('_', ' ',  $field)) . ':</strong> ' . $value['value'] . '</p></div>';
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
    public static function getSettingConfigurationInput($categoryId, $configurations)
    {
        $getDeviceCategory = DeviceCategory::where('id', $categoryId)->first();
        $inputFields = json_decode($getDeviceCategory->inputs, true);
        $firmwares = Firmware::where('device_category_id', $categoryId)->get();
        $html = "";
        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-lg-5">Firmware</label>';
        $html .= '<div class="col-lg-6">';
        $html .= '<select id="firmware" name="configuration[firmware_id]" class="form-control" placeholder="Search and Select">';
        foreach ($firmwares as $firmware) {
            $selected = (isset($configurations['firmware_id']) && $configurations['firmware_id'] == $firmware->id) ? 'selected' : '';
            $html .= '<option ' . $selected . ' value="' . $firmware->id . '">' . $firmware->name . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
        foreach ($inputFields as $key => $input) {
            if (isset($configurations[strtolower(str_replace(' ', '_', $input['key']))]['id'])) {
                $html .= '<div class="form-group">';
                $html .= '<label class="control-label col-lg-5">' . $input['key'] . ' ' . (isset($input['requiredFieldInput']) && $input['requiredFieldInput'] ? '<span class="require">*</span>' : '') . '</label>';
                $html .= '<div class="col-lg-6">';
                $dataFieldOptions = self::getDataFieldById($configurations[strtolower(str_replace(' ', '_', $input['key']))]['id']);
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
                    $html .= '</select>';
                } else if ($input['type'] == 'multiselect') {
                    $configKey = strtolower(str_replace(' ', '_', $input['key']));
                    $rawValue = $configurations[$configKey]['value'] ?? [];
                    if (is_string($rawValue)) {
                        $decoded = json_decode($rawValue, true);
                        $selectedValues = is_array($decoded) ? $decoded : explode(',', $rawValue);
                    } elseif (is_array($rawValue)) {
                        $selectedValues = $rawValue;
                    } else {
                        $selectedValues = [];
                    }
                    $selectedValues = array_map('strval', $selectedValues);
                    $html .= '<select class="inputType select2-multiselect" 
                        id="configval' . $configKey . '"
                        name="configuration[' . $configKey . '][]" 
                        multiple ' .
                        (isset($input['requiredFieldInput']) && $input['requiredFieldInput'] ? 'required' : '') .
                        ' style="width:100%">';
                    foreach ($dataFieldOptions->selectOptions as $key => $option) {
                        $value = $dataFieldOptions->selectValues[$key] ?? '';
                        // $selectedValue = $configurations[$configKey] ?? '';
                        $isSelected = in_array((string)$value, $selectedValues) ? 'selected' : '';
                        $html .= '<option value="' . htmlspecialchars($value) . '" ' . $isSelected . '>' . htmlspecialchars($option) . '</option>';
                    }
                    $html .= '</select>';
                    // Initialize Select2
                    $html .= '<script>
                    $(document).ready(function () {
                        var $select = $("#configval' . $configKey . '");
                        $select.select2({
                            placeholder: "Select up to 3 options",
                            width: "100%"
                        });
                        $select.on("change", function () {
                            var selected = $(this).select2("val");
                            if (selected && selected.length > ' . $fieldValidate->maxSelectValue . ') {
                                // Remove the last selected item
                                selected.splice(' . $fieldValidate->maxSelectValue . ');
                                $(this).select2("val", selected);
                                alert("You can only select up to 3 options.");
                            }
                        });
                    });
                </script>';
                } else {
                    if ($input['key'] == 'Password') {
                        $inputType = ($input['type'] == 'number' ? 'number' : 'text');
                        $minValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->min) ? 'minlength="' .  $fieldValidate->numberInput->min . '"' : '';
                        $maxValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->max) ? 'maxlength="' . $fieldValidate->numberInput->max . '"' : '';
                        $maxLength = ((isset($input['type']) && ($input['type'] == 'text' || $input['type'] == 'IP/URL' || $input['type'] == 'text_array')) && isset($fieldValidate->maxValueInput)) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';
                        $value = isset($configurations[strtolower(str_replace(' ', '_', $input['key']))]) ? $configurations[strtolower(str_replace(' ', '_', $input['key']))]['value'] : $input['default'];
                        $html .= '<input class="form-control" placeholder="Enter ' . $input['key'] . '" id="' . strtolower(str_replace(' ', '_', $input['key'])) . '" type="' . $inputType . '" ';
                        $html .= $minValue . ' ' . $maxValue . ' ' . $maxLength . ' ';
                        $html .= 'name="configuration[' . strtolower(str_replace(' ', '_', $input['key'])) . ']" value="' . htmlspecialchars($value) . '" ' . ((isset($input['requiredFieldInput']) && $input['requiredFieldInput']) ? 'required' : '') . ' />';
                    } else {
                        $addClassTextArray = isset($input['type']) && $input['type'] == 'text_array' ? "text-array-space" : '';
                        $addClassIpUrl = isset($input['type']) && $input['type'] == 'IP/URL' ? "ip-url-space" : '';
                        $inputType = ($input['type'] == 'number' ? 'number' : 'text');
                        $minValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->min) ? 'min="' .  $fieldValidate->numberInput->min . '"' : '';
                        $maxValue = isset($input['type']) && $input['type'] == 'number' && isset($fieldValidate->numberInput->max) ? 'max="' . $fieldValidate->numberInput->max . '"' : '';
                        $maxLength = ((isset($input['type']) && ($input['type'] == 'text' || $input['type'] == 'IP/URL' || $input['type'] == 'text_array')) && isset($fieldValidate->maxValueInput)) ? 'maxlength="' . $fieldValidate->maxValueInput . '"' : '';
                        $key = strtolower(str_replace(' ', '_', $input['key']));
                        $value = isset($configurations[$key])
                            ? (is_array($configurations[$key]['value'])
                                ? json_encode($configurations[$key]['value'])
                                : $configurations[$key]['value'])
                            : $input['default'];
                        $html .= '<input class="form-control ' . $addClassTextArray . ' ' . $addClassIpUrl . '" placeholder="Enter ' . $input['key'] . '" id="' . strtolower(str_replace(' ', '_', $input['key'])) . '" type="' . $inputType . '" ';
                        $html .= $minValue . ' ' . $maxValue . ' ' . $maxLength . ' ';
                        $html .= 'name="configuration[' . strtolower(str_replace(' ', '_', $input['key'])) . ']" value="' . htmlspecialchars($value) . '" ' . ((isset($input['requiredFieldInput']) && $input['requiredFieldInput']) ? 'required' : '') . ' />';
                    }
                }
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        $html .= '<input type="hidden" name="configuration[ping_interval]"
                        value="' . (isset($config['ping_interval']) ? $config['ping_interval']['value'] : '') . '"
                        class="form-control inputType" placeholder="Ping Interval" value=""/>';
        $html .= '';
        return $html;
    }
    public static function countNoOfDevices($id)
    {
        $count = Device::Select("id")->where('device_category_id', $id)->count();
        return $count;
    }
    public static function getFirmwareName($id)
    {
        if ($id != "") {
            $firmware = Firmware::select('name')->where(['id' => $id])->first();
            if ($firmware) {
                return $firmware->name;
            } else {
                return 'not authorized';
            }
        } else {
            return 'not authorized';
        }
    }
    public static function  getUserName($id)
    {
        $user = Writer::select('name')->where(['id' => $id])->first();
        if ($user) {
            return $user->name;
        } else {
            return 'not authorized';
        }
    }
    public static function getCountryName($id)
    {
        $stateName = DB::table('countries')->where('id', $id)->first();
        return $stateName->name;
    }
    public static function getStateName($id)
    {
        $stateName = DB::table('states')->where('id', $id)->first();
        return $stateName->name;
    }
    public static function getEsim($id)
    {
        $stateName = DB::table('esims')->where('id', $id)->first();
        return isset($stateName) ? $stateName->name . ' (' . $stateName->profile_1 . '+' . $stateName->profile_2 . ')' : '';
    }
    public static function getBackend($id)
    {
        $stateName = DB::table('backends')->where('id', $id)->first();
        return isset($stateName) ? $stateName->name : '';
    }
    public static function getUsersByDeviceCategory($categoryId)
    {
        $users  = DB::table('writers')->get();
        $arr = [];
        foreach ($users as $user) {
            $device_category_id = explode(',', $user->device_category_id);
            if (in_array($categoryId, $device_category_id)) {
                $arr[] = $user;
            }
        }
        return $arr;
    }
    public static function getReleasingNotes($id)
    {
        $firmware = Firmware::find($id);
        $configurations = json_decode($firmware->configurations);
        $html = "<h3>Releasing Note for Firmware " . $firmware->name . " Version " . " " . $configurations->version . " </h6>";
        $html .= isset($configurations->releasingNotes) ? $configurations->releasingNotes : '';
        return $html;
    }
    public static function getTimezoneByName($name)
    {
        $timezone = DB::table('timezones')->where('name', $name)->first();
        if ($timezone) {
            return $timezone->name . ' (' . $timezone->utc_offset . ')';
        }
        return 'N/A';
    }
}
