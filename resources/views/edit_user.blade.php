@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('edit-user') }}">
<style>
  .device-config-view-only .device-config-field,
  .device-config-view-only input[name^="configuration"]:not([type="hidden"]) {
    background-color: #f7f7f7;
    cursor: not-allowed;
  }
  .device-config-view-only select.device-config-field:disabled {
    background-color: #f7f7f7;
    cursor: not-allowed;
  }
  .ping-interval-readonly {
    background-color: #f7f7f7 !important;
    cursor: not-allowed;
  }
</style>
@endpush
@section('content')
<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;
use App\Models\TimezoneModel;

$timeZones = TimezoneModel::all();

use App\Template;
use App\DataFields;

$deviceCategoryIds = array_values(array_filter(array_map('trim', explode(',', $contact->device_category_id ?? ''))));
$categoryConfigMap = $categoryConfigMap ?? [];
$categoryViewConfigMap = $categoryViewConfigMap ?? [];
$categoryDefaultTemplateMap = $categoryDefaultTemplateMap ?? [];
$categoryCanViewConfigMap = $categoryCanViewConfigMap ?? [];
$categoryAdminPingIntervalMap = $categoryAdminPingIntervalMap ?? [];
$configurations = json_decode($contact->configurations, true);
$canConfigurations = json_decode($contact->can_configurations, true);

$getDeviceCategory = CommonHelper::getDeviceCategory();
$getDeviceCategoryconfig = DeviceCategory::select("*")->orWhereIn('id', $deviceCategoryIds)->get();
// dd($getDeviceCategoryconfig);

$default_template = DB::table('templates')
  ->select('templates.*')
  ->where('templates.active_status', '1')
  ->get();
$get_default_template = DB::table('templates')
  ->select('templates.*')
  ->where('templates.default_template', '1')
  ->first();

$deviceConfigViewOnly = Auth::id() != $contact->id;
$isAdminUser = Auth::user()->user_type === 'Admin';
$showAdminConfigFields = $isAdminUser;
?>

<!--main content start-->
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="eu-breadcrumb-wrap">
      <nav class="eu-breadcrumb">
        <a href="/{{$url_type}}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
        <a href="/{{$url_type}}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Account</span>
        <span class="bc-sep">›</span>
        <a href="/{{$url_type}}/view-user" class="bc-item">View Account</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Update Account</span>
      </nav>
    </div>
    <!--======== Page Title and Breadcrumbs End ========-->
    <!--======== Form Validation Content Start End ========-->
    <div class="row">
      <div class="col-md-12">
        <!--=========== START TAGS INPUT ===========-->
        <div class="c_panel">
          <div class="c_title">
            <h2>Update User</h2>
            <div class="clearfix"></div>
          </div>
          <!--/.c_title-->
          <div class="c_content">
          <div class="row" id="alert_msg" style='padding: 0px 20px;'>
            @include('partials.gps-inline-alerts')
            <div class="col-sm-12 alert alert-success success_msg" role="alert" style="display:none"></div>

            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
            <form class="validator form-horizontal userResellerEditForm" id="commentForm" method="post" action="/{{$url_type}}/update-user/{{$contact->id}}/{{$contact->user_type}}" onsubmit="return false;">
              @method('PATCH')
              @csrf
              <input type="hidden" class="current_utype" value="{{ $contact->user_type }}">
              @if($contact->user_type == 'Admin')
              <div class="form-group ">
                <label for="cname" class="control-label col-lg-3">Name <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class=" form-control" id="cname" name="name" type="text" value="{{ $contact->name }}" placeholder="Enter Name" required />
                </div>
              </div>
              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">E-Mail <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-control " id="cemail" type="email" name="email" value="{{ $contact->email }}" placeholder="Enter E-Mail" required />
                </div>
              </div>
              <div class="form-group">
                <label for="timezone" class="control-label col-lg-3">TimeZones <span class="require">*</span></label>
                <div class="col-lg-6">
                  <select name="timezone" class="select2" placeholder="Enter Time Zone" id="timezone">
                    <option value="">Please Select Time Zone</option>
                    @foreach($timeZones as $timezone)
                    <option value="{{ $timezone->name }}" {{ isset($user) && $contact->timezone == $timezone->name ? 'selected' : '' }}>
                      {{ $timezone->name }} ({{ $timezone->utc_offset }})
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group ">
                <label for="curl" class="control-label col-lg-3">Login Password </label>
                <div class="col-lg-6">
                  <input class="form-control" type="password" placeholder="Enter 4 digit device password" name="password" value="{{$contact->LoginPassword}}" required>
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-offset-3 col-lg-6">
                  <button class="btn btn-primary btn-flat" type="submit">Update</button>
                </div>
              </div>
              @else
              <input type="hidden" class="userNewAccType" name="acc_type_changed">
              <div class="userAccCases"></div>
              <!-- For dynamic fields -->
              @if(Auth::user()->user_type !='User')
              <div class="form-group ">
                <label for="curl" class="control-label col-lg-3">Account Type</label>
                <div class="col-lg-6">
                  <select data-prev="{{$contact->user_type}}" id="userType" class="form-control userAccType" name="user_type">
                    <option <?php echo (($contact->user_type == 'Reseller') ? 'selected' : '') ?> value="Reseller">Manufacturer</option>
                    <option <?php echo (($contact->user_type == 'User') ? 'selected' : '') ?> value="User">Dealer</option>
                    @if($currentUser->user_type =='Admin')
                    <option <?php echo (($contact->user_type == 'Support') ? 'selected' : '') ?> value="Support">Support</option>
                    @endif
                  </select>
                </div>
              </div>
              @endif
              <div class="form-group ">
                <label for="cname" class="control-label col-lg-3">Name <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class=" form-control" id="cname" name="name" type="text" value="{{ $contact->name }}" placeholder="Enter Name" required />
                </div>
              </div>
              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">Mobile <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-control " id="cmobile" type="text" name="mobile" value="{{ $contact->mobile }}" placeholder="Enter Mobile Number" maxlength="10" required />
                </div>
              </div>
              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">E-Mail <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-control " id="cemail" type="email" name="email" value="{{ $contact->email }}" placeholder="Enter E-Mail" required />
                </div>
              </div>
              <div class="form-group">
                <label for="timezone" class="control-label col-lg-3">TimeZones <span class="require">*</span></label>
                <div class="col-lg-6">
                  <select name="timezone" class="select2" id="timezone">
                    <option value="">Please Select Time Zone</option>
                    @foreach($timeZones as $timezone)
                    @php
                    $tzValue = $timezone->name . ' (' . $timezone->utc_offset . ')';
                    @endphp
                    <option value="{{ $timezone->name }}"
                      {{ isset($contact) && $contact->timezone == $timezone->name ? 'selected' : '' }}>
                      {{ $tzValue }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">2 Factor Authentication <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-check-input" id="twoFactorAuthentication" type="checkbox"
                    name="twoFactorAuthentication"
                    {{ $contact->twoFactorAuthentication ? 'checked' : '' }}
                    style="width: 52px;margin-top: 0;height: 20px;" />
                </div>
              </div>
              <div class="is-support-active" style="display: none;"></div>
              <div class="form-group bgx-margin-bottom row">
                <label for="curl" class="control-label col-lg-3">Device Categories<span class="require">*</span></label>
                <div class="col-lg-9">
                  <div style="margin-top: 8px; display: flex; justify-content: flex-start; flex-wrap: wrap; gap: 15px;">
                  @foreach($getDeviceCategory as $deviceCategory)
                  <div style="flex: 0 0 auto; min-width: 200px; max-width: 280px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                      <input type="checkbox" id="deviceCategory{{ $deviceCategory->id }}" {{ in_array((string) $deviceCategory->id, array_map('strval', $deviceCategoryIds), true) ? 'checked' : '' }} class="bgx-checkbox-category" name="deviceCategory[]" value="{{ $deviceCategory->id }}" data-user-id="{{ $contact->id }}" style="width: 18px; height: 18px; margin: 0; cursor: pointer; flex-shrink: 0;">
                      <label for="deviceCategory{{ $deviceCategory->id }}" class="bgx-label-category" style="margin: 0; margin-left: 12px; cursor: pointer; flex: 1; text-align: right;">{{$deviceCategory->device_category_name}}</label>
                    </div>
                  </div>
                  @endforeach
                  </div>
                </div>
              </div>
              @foreach($getDeviceCategoryconfig as $key => $category)
              @if(in_array((string) $category->id, array_map('strval', $deviceCategoryIds), true))

              <div class="device-category-fields card device-category-block-{{ $category->id }}{{ $deviceConfigViewOnly ? ' device-config-view-only' : '' }}" data-pre-enabled="1">
                <div class="card-title">
                  <h4>{{ CommonHelper::getDeviceCategoryName($category->id) }}</h4>
                  @if($deviceConfigViewOnly)
                  <small class="text-muted" style="display:block;margin-top:4px;"><i class="fa fa-eye"></i> Device configuration is view only</small>
                  @endif
                </div>
                <div class="card-details">
                  @php
                  $inputs = json_decode($category->inputs, true);
                  $totalInputs = count($inputs);
                  $inputIds = collect($inputs)->pluck('id')->toArray();
                  $dataFields = DataFields::whereIn('id', $inputIds)->get()->keyBy('id');
                  $templates = Template::where('device_category_id', $category->id)
                  ->where('id_user', $contact->id)
                  ->where('is_deleted', 0)
                  ->where('verify', 2)
                  ->orderByDesc('default_template')
                  ->orderBy('template_name')
                  ->get();

                  $configurationValue = !empty($categoryViewConfigMap[$category->id])
                    ? $categoryViewConfigMap[$category->id]
                    : ($categoryConfigMap[$category->id] ?? null);
                  $selectedTemplateId = null;
                  if (!empty($categoryDefaultTemplateMap[$category->id])) {
                    $selectedTemplateId = (int) $categoryDefaultTemplateMap[$category->id]->id;
                  } elseif (!empty($configurationValue['template']['value'])) {
                    $selectedTemplateId = (int) $configurationValue['template']['value'];
                  } elseif ($templates->isNotEmpty()) {
                    $defaultTemplate = $templates->firstWhere('default_template', 1);
                    $selectedTemplateId = $defaultTemplate ? $defaultTemplate->id : $templates->first()->id;
                  }
                  $enhancedInputs = collect($inputs)->map(function ($input) use ($dataFields) {
                  $input['validationConfig'] = $dataFields[$input['id']]->validationConfig ?? null;
                  return $input;
                  });
                  @endphp
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="templates<?= $key ?>" class="control-label col-lg-3">
                          Templates <span class="require">*</span>
                        </label>
                        <div class="col-lg-8">
                          <select class="userAccType form-control device-config-field"
                            id="templates<?= $category->id ?>"
                            name="configuration[<?= $category->id ?>][template]"
                            onchange="changeTemplate(<?= $category->id ?>)"
                            {{ $deviceConfigViewOnly ? 'disabled' : '' }}>
                            <option value="">Select Template</option>
                            <?php foreach ($templates as $temp): ?>
                              <option value="<?= $temp->id ?>" <?= $selectedTemplateId == $temp->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($temp->template_name) ?>
                                <?= $temp->default_template == 1 ? ' (Default)' : '' ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                          @if($deviceConfigViewOnly && $selectedTemplateId)
                          <input type="hidden" name="configuration[{{ $category->id }}][template]" value="{{ $selectedTemplateId }}">
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                  @foreach($enhancedInputs as $index => $input)

                  @if($index % 2 === 0)
                  <div class="row">
                    @endif
                    <div class="col-lg-6">
                      <input class="form-control inputType" type="hidden" placeholder="Enter {{$input['key']}}" name="idParameters[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" value="{{$input['id']}}" />
                      @if ($input['type'] == 'select')
                      @php
                      $validationConfig = json_decode($input['validationConfig'],true);
                      @endphp
                      <div class="form-group">
                        <label class="control-label col-lg-3">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}</label>
                        <div class="col-lg-8">
                          <select class="form-control inputType device-config-field" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" {{ $input['requiredFieldInput'] && !$deviceConfigViewOnly ? 'required' : '' }} {{ $deviceConfigViewOnly ? 'disabled' : '' }}>
                            <!-- <option value="">Please Select</option> -->
                            @foreach($validationConfig['selectOptions'] as $configkey => $option)
                            <option value="{{ $validationConfig['selectValues'][$configkey] }}" {{ isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]) && $configurationValue && strtolower($validationConfig['selectValues'][$configkey]) == $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] ? 'selected' : (!isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value']) && isset($input['default']) && strtolower($validationConfig['selectValues'][$configkey]) == strtolower($input['default']) ? 'selected' : '') }}>{{ $option }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      @elseif ($input['type'] == 'multiselect')
                      @php
                      $validationConfig = json_decode($input['validationConfig'],true);
                      @endphp
                      <div class="form-group">
                        <label class="control-label col-lg-3">
                          {{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}
                        </label>
                        <div class="col-lg-8">
                          <select class="inputType device-config-field" id="configval{{$category->id}}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}][]" multiple {{ $input['requiredFieldInput'] && !$deviceConfigViewOnly ? 'required' : '' }} {{ $deviceConfigViewOnly ? 'disabled' : '' }}>
                            @foreach($validationConfig['selectOptions'] as $configkey => $option)
                            @php
                            $inputKey = str_replace(' ', '_', strtolower($input['key']));
                            $rawValue = $configurationValue[$inputKey]['value'] ?? [];
                            if (is_string($rawValue)) {
                            $decoded = json_decode($rawValue, true);
                            $selectedValues = is_array($decoded) ? $decoded : explode(',', $rawValue);
                            } elseif (is_array($rawValue)) {
                            $selectedValues = $rawValue;
                            } else {
                            $selectedValues = [];
                            }
                            $selectedValues = array_map('strval', $selectedValues);
                            @endphp
                            <option value="{{ $validationConfig['selectValues'][$configkey] }}"
                              {{ in_array($validationConfig['selectValues'][$configkey], $selectedValues) ? 'selected' : '' }}>
                              {{ $option }}
                            </option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <script>
                        $(document).ready(function() {
                          console.log("$validationConfig['maxSelectValue'] ==>", <?= $validationConfig['maxSelectValue'] ?>);
                          var $select = $("#configval{{$category->id}}");

                          $select.select2({
                            placeholder: "Select up to 3 options",
                            width: "100%"
                          });
                          $select.on("change", function() {
                            var selected = $(this).select2("val");
                            if (selected && selected.length > <?= $validationConfig['maxSelectValue'] ?>) {
                              selected.splice(<?= $validationConfig['maxSelectValue'] ?>);
                              $(this).select2("val", selected);
                              alert("You can only select up to {{$validationConfig['maxSelectValue']}} options.");
                            }
                          });
                        });
                        // $(document).ready(function() {
                        //   $("#configval{{$category->id}}").select2({
                        //     placeholder: "Select options",
                        //     allowClear: true,
                        //     width: "100%"
                        //   });
                        // });
                      </script>

                      @else
                      <div class="form-group">
                        @php
                        $addClassTextArray = isset($input['type']) && $input['type'] == 'text' ? "text-array-space": '';
                        $addClassIpUrl = isset($input['type']) && $input['type'] == 'IP/URL' ? "ip-url-space" : '';
                        @endphp
                        <label class="control-label col-lg-3">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}</label>
                        <div class="col-lg-8">
                          <input class="form-control device-config-field {{$addClassTextArray}} {{$addClassIpUrl}}" type="{{ $input['type'] == 'number' ? 'number' : 'text' }}"
                            {!! $input['type']=='number' ? 'min="' . ($input['numberRange']['min'] ?? '' ) . '" max="' . ($input['numberRange']['max'] ?? '' ) . '"' : '' !!}
                            placeholder="Enter {{ isset($input['key']) ? $input['key'] :''  }}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]"
                            value="{{ isset($configurationValue) && isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value']) && $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] !== '' ? $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] : ($input['default'] ?? '') }}"
                            {{ $input['requiredFieldInput'] && !$deviceConfigViewOnly ? 'required' : '' }}
                            {{ $deviceConfigViewOnly ? 'readonly' : '' }}>
                        </div>
                      </div>
                      @endif
                    </div>
                    @if ($index % 2 === 1 || $index === $totalInputs - 1)
                  </div>
                  @endif
                  @endforeach
                  @php
                  $pingIntervalValue = $categoryAdminPingIntervalMap[$category->id] ?? 4;
                  if (!empty($configurationValue['ping_interval']['value'])) {
                    $pingIntervalValue = $configurationValue['ping_interval']['value'];
                  }
                  $isEditableValue = $configurationValue['is_editable']['value'] ?? '1';
                  @endphp
                  <div class="row">
                    @if($showAdminConfigFields)
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="curl" class="control-label col-lg-3">Ping Interval</label>
                        <div class="col-lg-8">
                          <input type="number" class="form-control device-config-field admin-config-field" name="configuration[{{ $category->id }}][ping_interval]" value="{{ $pingIntervalValue }}" min="1" step="1">
                        </div>
                      </div>
                    </div>
                    @else
                    <input type="hidden" name="configuration[{{ $category->id }}][ping_interval]" value="{{ $pingIntervalValue }}">
                    @endif
                    <input type="hidden" name="configuration[{{ $category->id }}][is_editable]" value="{{ $isEditableValue }}">
                  </div>
                  @if( $category->is_can_protocol == 1 )
                  <div class="row" style="padding: 0 15px;">
                    <div class="col-lg-12">
                      <div class="can-config-box isCanEnable{{$category->id}}">
                        <label class="can-config-label"><i class="fa fa-cogs"></i> CAN Configuration <span class="require">*</span></label>
                        @php
                        if ($deviceConfigViewOnly && !empty($categoryCanViewConfigMap[$category->id])) {
                          $value = $categoryCanViewConfigMap[$category->id];
                        } elseif (!empty($canConfigurations[$category->id])) {
                          $value = $canConfigurations[$category->id];
                          if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            $value = is_array($decoded) ? $decoded : [];
                          }
                        } elseif (!empty($categoryCanViewConfigMap[$category->id])) {
                          $value = $categoryCanViewConfigMap[$category->id];
                        } else {
                          $value = [];
                        }
                        $canConfigForInput = is_array($value) ? $value : (json_decode($value, true) ?: []);
                        $result = json_encode($canConfigForInput, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);
                        @endphp
                        <div class="can-config-input-wrap">
                          <input type="text" class="form-control can-config-input" name="canConfigurationArr[{{$category->id}}]" id="canConfigurationArr{{$category->id}}" value='{{ $result }}' readonly />
                          <button type="button" class="can-copy-btn" onclick="copyCanConfig('canConfigurationArr{{$category->id}}')" title="Copy to clipboard">
                            <i class="fa fa-copy"></i>
                          </button>
                        </div>
                        <div class="alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                        <button type="button" class="btn btn-primary can-config-btn" onclick="openCanModal('{{ $category->id }}')">
                          <i class="fa fa-sliders" style="margin-right:6px;"></i> {{ $deviceConfigViewOnly ? 'View CAN Protocol' : 'Configure CAN Protocol' }}
                        </button>
                      </div>
                    </div>
                  </div>
                <div class="modal can-modal" id="canModal{{$category->id}}">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content can-modal-content">
                      <div class="can-accent-bar"></div>
                      <button type="button" class="can-close" data-dismiss="modal">&times;</button>
                      <div class="can-scroll-wrap">
                      <div class="can-body">
                        <div class="can-hero">
                          <div class="can-icon-ring"><i class="fa fa-sliders"></i></div>
                          <h3 class="can-title">CAN Protocol Configuration</h3>
                          <p class="can-subtitle">Configure CAN bus parameters for {{ CommonHelper::getDeviceCategoryName($category->id) }}</p>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-plug"></i> CAN Channel <span class="require">*</span></label>
                          <select id="can_channel{{$category->id}}" name="canConfiguration[{{$category->id}}][can_channel]" class="form-control can-field-select">
                            <option value="">-- Select CAN Channel --</option>
                            <option value="1">CAN 1</option>
                            <option value="2">CAN 2</option>
                            <option value="3">CAN 3</option>
                            <option value="4">CAN 4</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-tachometer"></i> CAN Baud Rate <span class="require">*</span></label>
                          <select id="can_baud_rate{{$category->id}}" name="canConfiguration[{{$category->id}}][can_baud_rate]" class="form-control can-field-select">
                            <option value="">-- Select Baud Rate --</option>
                            <option value="500">500 kbps</option>
                            <option value="250">250 kbps</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-tag"></i> CAN ID Type <span class="require">*</span></label>
                          <select id="can_id_type{{$category->id}}" name="canConfiguration[{{$category->id}}][can_id_type]" class="form-control can-field-select">
                            <option value="">-- Select CAN ID --</option>
                            <option value="0">Standard</option>
                            <option value="1">Extended</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-cogs"></i> CAN Protocol <span class="require">*</span></label>
                          <select id="can_protocol{{$category->id}}" name="canConfiguration[{{$category->id}}][can_protocol]" class="form-control can-field-select">
                            <option value="">-- Select Protocol --</option>
                            <option value="1">J1979</option>
                            <option value="2">J1939</option>
                            <option value="3">Custom CAN</option>
                          </select>
                        </div>
                        <div class="can-dynamic-fields" id="dynamicCanFields{{$category->id}}"></div>
                      </div>
                      </div>
                      <div class="can-actions">
                        <button type="button" class="btn can-btn-cancel" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn can-btn-submit" onclick="generateJSON('{{$category->id}}')">
                          <i class="fa fa-check"></i> Submit
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                  @endif
                </div>
              </div>
              @endif
              @endforeach
              <div id="deviceCategoryInputFields"></div>
              <div class="form-group">
                <div class="bgx-save-button  col-lg-11">
                  <button class="btn btn-primary btn-flat" type="submit">Update</button>
                </div>
              </div>
              @endif
            </form>
            <hr>
          </div>
          <!--/.c_content-->
        </div>
        <!--/.c_panels-->
      </div>
    </div>
    <!--======== Form Validation Content Start End ========-->
  </section>
</section>
<!--======== Main Content End ========-->
@include('modals.userEditDelOptions')
@stop
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript">
  function copyCanConfig(inputId) {
    var input = document.getElementById(inputId);
    var text = input.value;
    navigator.clipboard.writeText(text).then(function() {
      var btn = input.closest('.can-config-input-wrap').querySelector('.can-copy-btn');
      btn.classList.add('copied');
      btn.innerHTML = '<i class="fa fa-check"></i>';
      setTimeout(function() {
        btn.classList.remove('copied');
        btn.innerHTML = '<i class="fa fa-copy"></i>';
      }, 1500);
    });
  }

  function setCanModalViewOnly(modal, viewOnly) {
    modal.toggleClass('is-view-only', !!viewOnly);
    modal.find('select, input:not([type="hidden"]), button.add-text-input, button.remove-text-input').prop('disabled', !!viewOnly);
    modal.find('.can-btn-submit').toggle(!viewOnly);
    modal.find('.can-title').text(viewOnly ? 'View CAN Protocol Configuration' : 'CAN Protocol Configuration');
    modal.find('.can-subtitle').text(viewOnly
      ? 'Review CAN bus parameters for this device category'
      : modal.find('.can-subtitle').data('default-subtitle') || modal.find('.can-subtitle').text());
    const $icon = modal.find('.can-icon-ring i');
    if (!$icon.data('default-class')) {
      $icon.data('default-class', $icon.attr('class'));
    }
    $icon.attr('class', viewOnly ? 'fa fa-eye' : $icon.data('default-class'));
    const $cancel = modal.find('.can-btn-cancel');
    if (!$cancel.data('default-text')) {
      $cancel.data('default-text', $.trim($cancel.text()) || 'Cancel');
    }
    $cancel.text(viewOnly ? 'Close' : $cancel.data('default-text'));
    if (viewOnly) {
      modal.find('.select2-container').addClass('can-select2-readonly');
    } else {
      modal.find('.select2-container').removeClass('can-select2-readonly');
    }
  }

  function normalizeCanProtocolValue(value) {
    const map = {
      '1': '1', '2': '2', '3': '3',
      'J1979': '1', 'J1939': '2', 'Custom CAN': '3', 'custom can': '3'
    };
    const key = String(value ?? '').trim();
    return Object.prototype.hasOwnProperty.call(map, key) ? map[key] : key;
  }

  function extractCanFieldValue(entry) {
    if (entry === undefined || entry === null || entry === '') {
      return null;
    }
    if (typeof entry === 'object' && entry !== null && 'value' in entry) {
      return entry.value;
    }
    return entry;
  }

  function hasCanConfigValue(value) {
    return value !== undefined && value !== null && value !== '';
  }

  function resolveRawConfigEntry(rawConfig, base, categoryId) {
    const id = String(categoryId);
    const preferredKeys = [base + id, base];
    let i;
    for (i = 0; i < preferredKeys.length; i++) {
      const key = preferredKeys[i];
      if (rawConfig[key] !== undefined && rawConfig[key] !== null && rawConfig[key] !== '') {
        return rawConfig[key];
      }
    }
    const matchingKeys = Object.keys(rawConfig).filter(function(key) {
      if (key === base) {
        return true;
      }
      if (key.indexOf(base) !== 0) {
        return false;
      }
      const suffix = key.slice(base.length);
      return suffix === '' || /^\d+$/.test(suffix);
    });
    if (matchingKeys.indexOf(base + id) !== -1) {
      return rawConfig[base + id];
    }
    if (matchingKeys.length === 1) {
      return rawConfig[matchingKeys[0]];
    }
    for (i = 0; i < matchingKeys.length; i++) {
      const candidate = extractCanFieldValue(rawConfig[matchingKeys[i]]);
      if (hasCanConfigValue(candidate) || candidate === 0 || candidate === '0') {
        return rawConfig[matchingKeys[i]];
      }
    }
    return null;
  }

  function normalizeCanConfigForCategory(rawConfig, categoryId) {
    if (!rawConfig || typeof rawConfig !== 'object') {
      return {};
    }
    const id = String(categoryId);
    const baseFields = ['can_channel', 'can_baud_rate', 'can_id_type', 'can_protocol'];
    const normalized = {};

    baseFields.forEach(function(base) {
      const suffixedKey = base + id;
      const entry = resolveRawConfigEntry(rawConfig, base, categoryId);
      if (entry === undefined || entry === null || entry === '') {
        return;
      }
      if (typeof entry === 'object' && entry !== null && 'value' in entry) {
        let val = entry.value;
        if (base === 'can_protocol') {
          val = normalizeCanProtocolValue(val);
        }
        normalized[suffixedKey] = { id: entry.id, value: val };
      } else {
        let val = entry;
        if (base === 'can_protocol') {
          val = normalizeCanProtocolValue(val);
        }
        normalized[suffixedKey] = { id: null, value: val };
      }
    });

    Object.keys(rawConfig).forEach(function(key) {
      if (baseFields.indexOf(key) !== -1 || baseFields.some(function(b) { return key === b + id; })) {
        return;
      }
      if (baseFields.some(function(b) { return key.indexOf(b) === 0 && /^\d+$/.test(key.slice(b.length)); })) {
        return;
      }
      normalized[key] = rawConfig[key];
    });

    return normalized;
  }

  function getCanConfigFieldValue(config, fieldId) {
    const entry = config[fieldId];
    if (entry && typeof entry === 'object' && 'value' in entry) {
      return entry.value;
    }
    if (entry !== undefined && entry !== null && typeof entry !== 'object') {
      return entry;
    }
    return '';
  }

  function applyCanBaseFieldValues(index, config) {
    ['can_channel', 'can_baud_rate', 'can_id_type'].forEach(function(base) {
      const key = base + index;
      const value = getCanConfigFieldValue(config, key);
      if (hasCanConfigValue(value) || value === 0 || value === '0') {
        $('#' + key).val(String(value));
      }
    });
    const protocolKey = 'can_protocol' + index;
    const protocolValue = getCanConfigFieldValue(config, protocolKey);
    if (hasCanConfigValue(protocolValue)) {
      $('#can_protocol' + index).val(normalizeCanProtocolValue(protocolValue));
    }
  }

  function applyCanDynamicFieldValues(config, categoryId, viewOnly, modal) {
    Object.keys(config).forEach(function(fieldKey) {
      if (['can_channel', 'can_baud_rate', 'can_id_type', 'can_protocol'].some(function(base) {
        return fieldKey === base || fieldKey === base + categoryId;
      })) {
        return;
      }
      const value = getCanConfigFieldValue(config, fieldKey);
      if (value === undefined || value === null || value === '') {
        return;
      }
      const fieldEl = document.getElementById(fieldKey);
      if (!fieldEl) {
        return;
      }
      const $field = $(fieldEl);
      if ($field.hasClass('can-multiselect')) {
        let values = value;
        if (typeof values === 'string' && values.charAt(0) === '{') {
          values = values.replace(/[{}]/g, '').split(',').filter(Boolean);
        }
        if (!Array.isArray(values)) {
          values = [values];
        }
        if ($field.data('select2')) {
          $field.select2('val', values);
        } else {
          $field.val(values);
        }
      } else {
        fieldEl.value = value;
      }
    });
  }

  function getCanProtocolModalContext(index) {
    const $select = $('#can_protocol' + index);
    const $modal = $select.closest('.can-modal');
    const modalId = $modal.attr('id') || '';
    const fieldsSuffix = modalId.indexOf('canModal1') === 0 ? '1' : '';
    return {
      modal: $modal,
      fieldsSuffix: fieldsSuffix,
      modalPrefix: fieldsSuffix
    };
  }

  function bindCanProtocolLoader(index, fieldsSuffix, modalPrefix, viewOnly, modal, config) {
    const canProtocolEl = $('#can_protocol' + index);
    canProtocolEl.off('change.canProtocolLoad');
    canProtocolEl.on('change.canProtocolLoad', function() {
      const protocolVal = normalizeCanProtocolValue($(this).val());
      if (protocolVal !== $(this).val()) {
        $(this).val(protocolVal);
      }
      loadCanProtocolFields(index, fieldsSuffix, modalPrefix, function() {
        applyCanDynamicFieldValues(config, index, viewOnly, modal);
      });
    });
  }

  function populateCanModalFromStoredConfig(index, modalPrefix, viewOnly) {
    const modal = $('#canModal' + modalPrefix + index);
    const fieldsSuffix = modalPrefix || '';
    const dynamicSelector = fieldsSuffix
      ? '#dynamicCanFields' + fieldsSuffix + index
      : '#dynamicCanFields' + index;
    const rawValue = (document.getElementById('canConfigurationArr' + index) || {}).value || '{}';
    let config = {};

    try {
      config = normalizeCanConfigForCategory(JSON.parse(rawValue), index);
    } catch (e) {
      config = {};
    }

    if (!modal.find('.can-subtitle').data('default-subtitle')) {
      modal.find('.can-subtitle').data('default-subtitle', modal.find('.can-subtitle').text());
    }

    ['can_channel', 'can_baud_rate', 'can_id_type', 'can_protocol'].forEach(function(base) {
      $('#' + base + index).val('');
    });
    $(dynamicSelector).empty();

    applyCanBaseFieldValues(index, config);
    bindCanProtocolLoader(index, fieldsSuffix, modalPrefix, viewOnly, modal, config);

    const protocolKey = 'can_protocol' + index;
    const protocolValue = getCanConfigFieldValue(config, protocolKey);
    const finishPopulate = function() {
      applyCanBaseFieldValues(index, config);
      applyCanDynamicFieldValues(config, index, viewOnly, modal);
      setCanModalViewOnly(modal, viewOnly);
    };

    if (hasCanConfigValue(protocolValue)) {
      loadCanProtocolFields(index, fieldsSuffix, modalPrefix, finishPopulate);
    } else {
      finishPopulate();
    }
  }

  function relocateCanModalsToBody() {
    $('.can-modal').each(function() {
      if (!$(this).parent().is('body')) {
        $(this).appendTo('body');
      }
    });
  }

  function openCanModal(index) {
    const $modal = $('#canModal' + index);
    relocateCanModalsToBody();
    $modal.modal('show');
    populateCanModalFromStoredConfig(index, '', deviceConfigViewOnly);
  }

  function openCanModal1(index) {
    const $modal = $('#canModal1' + index);
    relocateCanModalsToBody();
    $modal.modal('show');
    populateCanModalFromStoredConfig(index, '1', deviceConfigViewOnly);
  }


  // function openCanModal(index) {
  //   $('#canModal' + index).modal('show');
  //   $('#can_protocol' + index).trigger('change').val;
  //   const config = JSON.parse(document.getElementById(`canConfigurationArr${index}`).value);

  //   // Loop through each field and set values
  //   for (let field in config) {
  //     const value = config[field]?.value;

  //     if (document.getElementById(field)) {
  //       document.getElementById(field).value = value;
  //     }
  //   }
  // }

  function getCanFieldIcon(fieldName, inputType) {
    const name = String(fieldName || '').toLowerCase();
    if (name.indexOf('mode') !== -1) return 'fa-list';
    if (name.indexOf('pid') !== -1) return 'fa-barcode';
    if (name.indexOf('address') !== -1) return 'fa-map-marker';
    if (inputType === 'select' || inputType === 'multiselect') return 'fa-list-ul';
    if (inputType === 'number' || inputType === 'hex') return 'fa-hashtag';
    return 'fa-pencil';
  }

  function initCanMultiselect($select) {
    if (!$select || !$select.length || typeof $.fn.select2 !== 'function') {
      return;
    }
    if ($select.data('select2')) {
      $select.select2('destroy');
    }
    $select.removeClass('form-control inputType');
    const maxSel = parseInt($select.data('max-select'), 10) || 0;
    const placeholder = $select.data('placeholder') || 'Select options';
    $select.select2({ placeholder: placeholder, width: '100%' });
    if (maxSel > 0) {
      $select.off('change.euMaxSelect').on('change.euMaxSelect', function() {
        let selected = $(this).select2('val') || [];
        if (selected.length > maxSel) {
          selected.splice(maxSel);
          $(this).select2('val', selected);
          alert('You can only select up to ' + maxSel + ' options.');
        }
      });
    }
  }

  function initCanMultiselects(index, fieldsSuffix) {
    const selector = fieldsSuffix
      ? '#dynamicCanFields' + fieldsSuffix + index
      : '#dynamicCanFields' + index;
    $(selector).find('select.can-multiselect').each(function() {
      initCanMultiselect($(this));
    });
  }

  function buildCanDynamicFieldsHtml(index, fields) {
    let config = {};
    try {
      config = normalizeCanConfigForCategory(
        JSON.parse($('#canConfigurationArr' + index).val() || '{}'),
        index
      );
    } catch (e) {
      config = {};
    }

    let html = '<p class="can-protocol-section-label">Protocol-specific settings</p>';

    fields.forEach(function(field) {
      const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
      const inputType = field.inputType;
      let validation = {};
      try {
        validation = JSON.parse(field.validationConfig || '{}');
      } catch (e) {
        validation = {};
      }
      const value = getCanConfigFieldValue(config, fieldId);
      const escapedValue = String(value).replace(/"/g, '&quot;');
      const fieldLabel = String(field.fieldName).replace(/"/g, '&quot;');
      let inputHtml = `<input type="hidden" name="idCanParameters[${index}][${fieldId}]" value="${field.id}" />`;
      inputHtml += `<input type="hidden" name="CanParametersType[${index}][${fieldId}]" value="${inputType}" />`;
      let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control can-field-input" placeholder="Enter ${fieldLabel}" value="${escapedValue}"`;
      let selectAttr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control can-field-select"`;

      if (inputType === 'number') {
        if (validation.numberInput) {
          attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
        }
        inputHtml += `<input type="number" ${attr} />`;
      } else if (inputType === 'select') {
        const selectedValue = getCanConfigFieldValue(config, fieldId);
        inputHtml += `<select ${selectAttr}><option value="">-- Select --</option>`;
        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
          validation.selectOptions.forEach(function(option) {
            const isSelected = option === selectedValue ? 'selected' : '';
            inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
          });
        } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
          Object.entries(validation.selectOptions).forEach(function(entry) {
            const key = entry[0];
            const label = entry[1];
            const optValue = validation.selectValues && validation.selectValues[key] !== undefined
              ? validation.selectValues[key]
              : key;
            const isSelected = String(key) === String(selectedValue) || String(optValue) === String(selectedValue) ? 'selected' : '';
            inputHtml += `<option value="${optValue}" ${isSelected}>${label}</option>`;
          });
        }
        inputHtml += `</select>`;
      } else if (inputType === 'multiselect') {
        let selectedValue = getCanConfigFieldValue(config, fieldId);
        if (typeof selectedValue === 'string' && selectedValue.charAt(0) === '{') {
          selectedValue = selectedValue.replace(/[{}]/g, '').split(',').filter(Boolean);
        }
        const selectedArray = Array.isArray(selectedValue) ? selectedValue : [selectedValue];
        const maxSel = validation.maxSelectValue || 0;
        inputHtml += `<div class="can-multiselect-wrap"><select id="${fieldId}" name="canConfiguration[${index}][${fieldId}][]" class="can-multiselect" multiple data-max-select="${maxSel}" data-placeholder="${fieldLabel}">`;
        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
          validation.selectOptions.forEach(function(option, key) {
            const optValue = validation.selectValues && validation.selectValues[key] !== undefined
              ? validation.selectValues[key]
              : option;
            const isSelected = selectedArray.includes(option) || selectedArray.includes(optValue) ? 'selected' : '';
            inputHtml += `<option value="${optValue}" ${isSelected}>${option}</option>`;
          });
        } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
          Object.entries(validation.selectOptions).forEach(function(entry) {
            const key = entry[0];
            const label = entry[1];
            const isSelected = selectedArray.includes(key) ? 'selected' : '';
            inputHtml += `<option value="${key}" ${isSelected}>${label}</option>`;
          });
        }
        inputHtml += `</select></div>`;
      } else if (inputType === 'text_array') {
        const maxValue = validation.maxValueInput || 0;
        const values = [''];
        inputHtml += `<div id="${fieldId}_wrapper_${index}" class="text-array-wrapper">`;
        values.forEach(function(val, i) {
          inputHtml += `<div class="text-array-item">
            <input type="text" maxlength="8" id="${fieldId}${index}${i}" name="canConfiguration[${index}][${fieldId}][]" class="form-control can-field-input" placeholder="Enter ${fieldLabel}" value="${String(val).trim()}" />
            <button type="button" class="btn can-array-btn can-array-btn-remove remove-text-input"><i class="fa fa-minus"></i></button>
          </div>`;
        });
        inputHtml += `<button type="button" class="btn can-array-btn can-array-btn-add add-text-input"><i class="fa fa-plus"></i> Add</button></div>`;
        inputHtml += `<input type="hidden" id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" />`;
        setTimeout(function() {
          const wrapper = $('#' + fieldId + '_wrapper_' + index);
          const addButton = wrapper.find('.add-text-input');
          wrapper.on('click', '.add-text-input', function() {
            const count = wrapper.find('.text-array-item').length;
            if (maxValue && count >= maxValue) {
              alert('You can only add up to ' + maxValue + ' inputs for ' + field.fieldName + '.');
              addButton.prop('disabled', true);
              return;
            }
            const newInput = `<div class="text-array-item">
              <input type="text" id="${fieldId}${index}${count}" name="canConfiguration[${index}][${fieldId}][]" class="form-control can-field-input" placeholder="Enter ${fieldLabel}" />
              <button type="button" class="btn can-array-btn can-array-btn-remove remove-text-input"><i class="fa fa-minus"></i></button>
            </div>`;
            $(this).before(newInput);
            if (maxValue && wrapper.find('.text-array-item').length >= maxValue) {
              addButton.prop('disabled', true);
            }
            updateHiddenValue();
          });
          wrapper.on('click', '.remove-text-input', function() {
            $(this).closest('.text-array-item').remove();
            if (maxValue && wrapper.find('.text-array-item').length < maxValue) {
              addButton.prop('disabled', false);
            }
            updateHiddenValue();
          });
          wrapper.on('input', 'input[type=text]', updateHiddenValue);
          function updateHiddenValue() {
            const values = [];
            wrapper.find('input[type=text]').each(function() {
              const val = $(this).val().trim();
              if (val) values.push(val);
            });
            $('#' + fieldId).val('{' + values.join(',') + '}');
          }
          updateHiddenValue();
        }, 100);
      } else if (inputType === 'hex') {
        let hexAttr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control can-field-input" value="${escapedValue}"`;
        if (validation.maxValueInput) {
          hexAttr += ` maxlength="${validation.maxValueInput}"`;
        }
        inputHtml += `<input type="text" ${hexAttr} />`;
      } else {
        if (validation.maxValueInput) {
          attr += ` maxlength="${validation.maxValueInput}"`;
        }
        inputHtml += `<input type="text" ${attr} />`;
      }

      const icon = getCanFieldIcon(field.fieldName, inputType);
      html += `<div class="can-field-group">
        <label class="can-label" for="${fieldId}"><i class="fa ${icon}"></i> ${field.fieldName} <span class="require">*</span></label>
        ${inputHtml}
        <div class="can-field-error alert alert-danger ${fieldId}_error" role="alert" style="display:none"></div>
      </div>`;
    });

    return html;
  }

  function renderCanProtocolFields(index, fields, fieldsSuffix, modalSuffix, afterRender) {
    const containerSelector = fieldsSuffix
      ? '#dynamicCanFields' + fieldsSuffix + index
      : '#dynamicCanFields' + index;
    const modalSelector = modalSuffix
      ? '#canModal' + modalSuffix + index
      : '#canModal' + index;
    if (!Array.isArray(fields)) {
      fields = [];
    }
    const html = buildCanDynamicFieldsHtml(index, fields);
    $(containerSelector).html(html).show();
    initCanMultiselects(index, fieldsSuffix || '');
    let config = {};
    try {
      config = normalizeCanConfigForCategory(
        JSON.parse($('#canConfigurationArr' + index).val() || '{}'),
        index
      );
    } catch (e) {
      config = {};
    }
    setTimeout(function() {
      applyCanBaseFieldValues(index, config);
      const $modal = $(modalSelector);
      applyCanDynamicFieldValues(config, index, deviceConfigViewOnly, $modal);
      if (deviceConfigViewOnly) {
        setCanModalViewOnly($modal, true);
      }
      if (typeof afterRender === 'function') {
        afterRender();
      }
    }, 250);
  }

  function loadCanProtocolFields(index, fieldsSuffix, modalSuffix, afterLoad) {
    const dynamicSelector = fieldsSuffix
      ? '#dynamicCanFields' + fieldsSuffix + index
      : '#dynamicCanFields' + index;
    const canProtocolValue = normalizeCanProtocolValue($('#can_protocol' + index).val());
    if (!canProtocolValue) {
      $(dynamicSelector).empty();
      return;
    }
    $('#can_protocol' + index).val(canProtocolValue);
    const actionUrl = "{{ url($url_type . '/get-can-protocol-fields') }}";
    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: {
        protocol: canProtocolValue,
        _token: '{{ csrf_token() }}'
      },
      success: function(fields) {
        renderCanProtocolFields(index, fields, fieldsSuffix, modalSuffix, afterLoad);
      },
      error: function(xhr) {
        console.error('Error fetching CAN protocol fields', xhr);
        $(dynamicSelector).html(
          '<p class="can-field-error alert alert-danger" style="display:block">Unable to load protocol fields. Please try again.</p>'
        );
      }
    });
  }

  function selectedCanProtocol(index) {
    const ctx = getCanProtocolModalContext(index);
    loadCanProtocolFields(index, ctx.fieldsSuffix, ctx.modalPrefix);
  }

  function selectedCanProtocol1(index) {
    loadCanProtocolFields(index, '1', '1');
  }

  function generateJSON(index) {
    let canConfigData = {};
    const $modal = $('#canModal' + index);

    $modal.find('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
      let fieldId = $(this).attr('id');
      let value = $(this).val(); // could be string or array
      console.log("fieldId ==>", fieldId, " ====", value);
      // Handle can_protocol separately
      if (fieldId === `can_protocol${index}`) {
        canConfigData[fieldId] = {
          id: 97,
          value: value
        };
      } else if (fieldId == `can_channel${index}`) {
        canConfigData[fieldId] = {
          id: 94,
          value: value
        };
      } else if (fieldId == `can_baud_rate${index}`) {
        canConfigData[fieldId] = {
          id: 96,
          value: value
        };
      } else if (fieldId == `can_id_type${index}`) {
        canConfigData[fieldId] = {
          id: 95,
          value: value
        };
      } else {
        let hiddenInput = $(`input[name="idCanParameters[${index}][${fieldId}]"]`);
        let canParametersType = $(`input[name="CanParametersType[${index}][${fieldId}]"]`).val();
        let id = hiddenInput.val();

        if (id && value !== "") {
            if (canParametersType == 'multiselect') {
                const formattedMultiValue = `{${value.join(',')}}`;
                canConfigData[fieldId] = {
                  id: parseInt(id),
                  value: formattedMultiValue
                };
            } else {
                canConfigData[fieldId] = {
                    id: parseInt(id),
                    value: value // keep array as-is
                };
            }
        }
      }
    });

    // Set final JSON outside the loop
    $('#canConfigurationArr' + index).val(JSON.stringify(canConfigData));
    $('#canModal' + index).modal('hide');
  }


  function generateJSON1(index) {
    let canConfigData = {};
    const $modal = $('#canModal1' + index);

    $modal.find('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
      let fieldId = $(this).attr('id');
      let value = $(this).val();

      if (fieldId === `can_protocol${index}`) {
        canConfigData[fieldId] = {
          id: 97,
          value: value
        };
      } else if (fieldId == `can_channel${index}`) {
        canConfigData[fieldId] = {
          id: 94,
          value: value
        };
      } else if (fieldId == `can_baud_rate${index}`) {
        canConfigData[fieldId] = {
          id: 96,
          value: value
        };
      } else if (fieldId == `can_id_type${index}`) {
        canConfigData[fieldId] = {
          id: 95,
          value: value
        };
      } else {
        let hiddenInput = $(`input[name="idCanParameters[${index}][${fieldId}]"]`);
        let canParametersType = $(`input[name="CanParametersType[${index}][${fieldId}]"]`).val();
        let id = hiddenInput.val();

        if (id && value !== "") {
          if (canParametersType == 'multiselect') {
              const formattedMultiValue = `{${value.join(',')}}`;
              canConfigData[fieldId] = {
                id: parseInt(id),
                value: formattedMultiValue
              };
          } else {
              canConfigData[fieldId] = {
                id: parseInt(id),
                value: value
              };
          }
        }
      }
    });

    $('#canConfigurationArr' + index).val(JSON.stringify(canConfigData));
    $('#canModal1' + index).modal('hide');
  }

  let existingCheckedValues = <?= json_encode(array_map('strval', $deviceCategoryIds)) ?>;
  const preEnabledCategoryIds = existingCheckedValues.slice();
  const getCategoriesUrl = "{{ url($url_type . '/get-multiple-categories') }}";
  const csrfToken = "{{ csrf_token() }}";
  const deviceConfigViewOnly = @json($deviceConfigViewOnly);
  const showAdminConfigFields = @json($showAdminConfigFields);
  const categoryAdminPingIntervalMap = @json($categoryAdminPingIntervalMap);

  function isPreEnabledCategory(categoryId) {
    return preEnabledCategoryIds.map(String).includes(String(categoryId));
  }

  function isCategoryBlockViewOnly(categoryId) {
    return deviceConfigViewOnly && isPreEnabledCategory(categoryId);
  }

  function applyDeviceConfigViewOnlyToBlock(categoryId) {
    if (!isCategoryBlockViewOnly(categoryId)) {
      return;
    }
    const $block = $('.device-category-block-' + categoryId);
    if (!$block.length) {
      return;
    }
    $block.addClass('device-config-view-only');
    $block.find('select:not(.admin-config-field)').prop('disabled', true);
    $block.find('input:not([type="hidden"]):not(.admin-config-field), textarea:not(.admin-config-field)').each(function() {
      const $input = $(this);
      if ($input.attr('name') && $input.attr('name').indexOf('[is_editable]') !== -1) {
        return;
      }
      if ($input.is(':radio') || $input.is(':checkbox')) {
        $input.prop('disabled', true);
      } else {
        $input.prop('readonly', true).prop('disabled', true);
      }
    });
    if (!showAdminConfigFields) {
      $block.find('.admin-config-field').prop('readonly', true).prop('disabled', true);
      $block.find('input[name*="[is_editable]"]').prop('disabled', true);
    } else {
      $block.find('.admin-config-field').prop('readonly', false).prop('disabled', false);
      $block.find('input[name*="[is_editable]"]').prop('disabled', false);
    }
  }

  function handleSupportActiveVisibility() {
    let val = $('.userAccType').val();
    let support = $('.is-support-active');

    // Clear existing content
    support.empty();

    if (val === 'Support') {
      // Determine checked state from PHP variable
      let checkValue = "{{ $contact->is_support_active == 1 ? 'checked' : '' }}";

      let html = `
        <div class="form-group">
            <label for="is_support_active" class="control-label col-lg-3">Configuration Edit Permission</label>
            <div class="col-lg-6" style="position: absolute; left: 4%;">
                <input 
                    type="checkbox" 
                    class="form-control" 
                    name="is_support_active" 
                    style="height: 20px;"
                    ${checkValue}
                >
            </div>
        </div>`;
      support.html(html).show();
    } else {
      support.hide();
    }
  }

  $(document).on('change', '.userAccType', function() {
    handleSupportActiveVisibility();
  });
  $(window).on('load', function() {
    handleSupportActiveVisibility();
  });

  $(document).ready(function() {
    relocateCanModalsToBody();

    if ($.fn.validate && $('#commentForm').length) {
      $('#commentForm').validate({
        submitHandler: function() {
          return false;
        }
      });
    }

    $('.bgx-checkbox-category').on('change', function(e) {
      e.preventDefault();
      e.stopPropagation();
      getDeviceCategoryInput($(this).data('user-id'), $(this).val());
    });

    $('select[id^="can_protocol"]').each(function() {
      const index = this.id.replace('can_protocol', '');
      const ctx = getCanProtocolModalContext(index);
      if (ctx.modal.length) {
        bindCanProtocolLoader(index, ctx.fieldsSuffix, ctx.modalPrefix, deviceConfigViewOnly, ctx.modal, {});
      }
    });

    $('.templates').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');
      // ids.push(id);
      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });
    $('input[type="text"][name^="configuration"]').on('keypress', function(event) {
      if (event.which === 32) { // 32 is the ASCII code for space
        event.preventDefault(); // Prevent the space from being entered
      }
    });

    @foreach($categoryDefaultTemplateMap as $catId => $defaultTemplate)
    if ($('.device-category-block-{{ $catId }}[data-pre-enabled="1"]').length) {
      changeTemplate({{ $catId }}, {{ $defaultTemplate->id }});
    }
    @endforeach

    if (deviceConfigViewOnly) {
      preEnabledCategoryIds.forEach(function(catId) {
        applyDeviceConfigViewOnlyToBlock(catId);
      });
    }
  })
  $(document).ready(function() {
    $('#commentForm').submit(function(event) {
      event.preventDefault();
      $('.error_msg').html('').hide();
      $('.success_msg').html('').hide();
      let error_msg = "";
      let formIsValid = true;

      let checkedCategories = $('input.bgx-checkbox-category:checked').map(function() {
        return String(this.value);
      }).get();
      let loadedCategoryBlocks = $('.device-category-fields').map(function() {
        const match = (this.className || '').match(/device-category-block-(\d+)/);
        return match ? match[1] : null;
      }).get().filter(Boolean);

      if (checkedCategories.length > 0) {
        let missingBlocks = checkedCategories.filter(function(categoryId) {
          return loadedCategoryBlocks.indexOf(String(categoryId)) === -1;
        });
        if (missingBlocks.length > 0) {
          error_msg = 'Device category settings are still loading. Please wait a moment after enabling a category, then try saving again.';
          formIsValid = false;
        }
      }

      function validateRequiredField($field) {
        let inputValue = $field.val();
        let inputType = $field.attr('type');
        let label = $field.closest('.form-group').find('.control-label').text();

        if (inputType === 'number') {
          let minVal = parseFloat($field.attr('min'));
          let maxVal = parseFloat($field.attr('max'));
          let numericValue = parseFloat(inputValue);

          if (!isNaN(minVal) && numericValue < minVal) {
            error_msg = 'Validation Error: ' + label + ' should be greater than or equal to ' + minVal;
            formIsValid = false;
            return false;
          }

          if (!isNaN(maxVal) && numericValue > maxVal) {
            error_msg = 'Validation Error: ' + label + ' should be less than or equal to ' + maxVal;
            formIsValid = false;
            return false;
          }
        }

        if (inputValue === '') {
          error_msg = 'Validation Error: ' + label + ' is required';
          formIsValid = false;
          return false;
        }
      }

      if (!deviceConfigViewOnly) {
        $(this).find('input[required], select[required]').each(function() {
          if (validateRequiredField($(this)) === false) {
            return false;
          }
        });
      } else {
        $('.device-category-fields').each(function() {
          const match = (this.className || '').match(/device-category-block-(\d+)/);
          if (!match || isPreEnabledCategory(match[1])) {
            return;
          }
          $(this).find('input[required], select[required]').each(function() {
            if (validateRequiredField($(this)) === false) {
              return false;
            }
          });
        });
      }

      if (formIsValid) {
        let selectedUserType = $('#userType').length ? $('#userType').val() : "{{$contact->user_type}}";
        let actionUrl = "/{{$url_type}}/update-user/{{$contact->id}}/" + selectedUserType;
        let formData = $(this).serialize();

        $.ajax({
          url: actionUrl,
          type: "POST",
          dataType: 'json',
          data: formData,
          success: function(response) {
            let result = typeof response === 'object' ? response : null;
            if (!result && typeof response === 'string') {
              try { result = JSON.parse(response); } catch (e) { result = null; }
            }
            var message = (result && result.success) ? result.success : 'Account updated successfully.';
            if (window.notifyGpsSuccess) {
              window.notifyGpsSuccess(message);
            } else {
              $(".success_msg").text(message).show();
            }
          },
          error: function(xhr) {
            if (window.notifyGpsFromXhr) {
              window.notifyGpsFromXhr(xhr);
            } else {
              let errors = JSON.parse(xhr.responseText);
              $('.error_msg').empty();
              if (errors && errors.errors) {
                $.each(errors.errors, function(key, value) {
                  $('.error_msg').append(value[0] + '<br>').show();
                });
              }
            }
          },
          complete: function() {
            $('#loading').hide();
          }
        });
      } else {
        if (window.notifyGpsError) {
          window.notifyGpsError(error_msg);
        } else {
          $('.error_msg').text(error_msg).show();
        }
      }
    });
  });

  function getDeviceCategoryInput(userId, deviceCategoryId) {
    const checkbox = $(`input.bgx-checkbox-category[value="${deviceCategoryId}"]`);
    const isChecked = checkbox.is(':checked');

    if (!isChecked) {
      Swal.fire({
        title: 'Disable Device Category?',
        html: '<strong>Warning:</strong> Disabling this device category will remove access to it for all child accounts. Any templates associated with this device category for child accounts will also be permanently deleted. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, disable',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
      }).then(function(result) {
        if (result.isConfirmed) {
          existingCheckedValues = existingCheckedValues.filter(function(val) {
            return String(val) !== String(deviceCategoryId);
          });
          $(`.device-category-block-${deviceCategoryId}`).remove();
        } else {
          checkbox.prop('checked', true);
        }
      });
      return;
    }

    if ($(`.device-category-block-${deviceCategoryId}`).length > 0) {
      if (!existingCheckedValues.map(String).includes(String(deviceCategoryId))) {
        existingCheckedValues.push(String(deviceCategoryId));
      }
      return;
    }

    loadDeviceCategoryBlocks(userId, [deviceCategoryId]);
  }

  function loadDeviceCategoryBlocks(userId, categoryIds) {
    const newCheckedValues = categoryIds.filter(function(val) {
      return !existingCheckedValues.map(String).includes(String(val));
    });

    if (!newCheckedValues.length) {
      return;
    }

    $.ajax({
        url: getCategoriesUrl,
        type: "POST",
        data: {
          ids: newCheckedValues,
          userId: userId,
          _token: csrfToken
        },
        success: function(response) {
          let result = JSON.parse(response);
          let htmlContent = '';
          if (result.status == 200) {
            $("#templates").select2({
              placeholder: 'Select your country...',
              allowClear: true
            });

            let inputFields = JSON.parse(result.device);
            let templates = JSON.parse(result.templates);
            let parentTemplates = result.parentTemplates ? JSON.parse(result.parentTemplates) : [];
            let parentCanConfigs = result.parentCanConfigs ? JSON.parse(result.parentCanConfigs) : {};
            let templatesAreParentSourced = result.templatesAreParentSourced ? JSON.parse(result.templatesAreParentSourced) : [];
            let defaultTemplatesToTrigger = [];
            inputFields.forEach((data, adjustedIndex) => {
              const categoryId = data.id;
              let input = JSON.parse(data.inputs);
              let canEnable = data.is_can_protocol == 1 ? true : false;
              const categoryTemplates = templates[adjustedIndex] || [];
              const parentTemplate = parentTemplates[adjustedIndex] || null;
              const isParentSourced = templatesAreParentSourced[adjustedIndex] === true;
              const selectedTemplate = categoryTemplates.find(function(temp) {
                return temp.default_template == 1;
              }) || categoryTemplates[0] || parentTemplate;
              htmlContent += '<div class="device-category-fields card device-category-block-' + categoryId + '" data-pre-enabled="0"' + (isParentSourced && selectedTemplate ? ' data-parent-template-id="' + selectedTemplate.id + '"' : '') + '>';
              htmlContent += '<div class="card-title"><h4 >' + data.device_category_name + '</h4>';
              if (isParentSourced && categoryTemplates.length > 0) {
                htmlContent += '<small class="text-muted" style="display:block;margin-top:4px;"><i class="fa fa-copy"></i> Parent account templates — select one, edit below, then save to create this user\'s template</small>';
              } else if (!isParentSourced && categoryTemplates.length > 0) {
                htmlContent += '<small class="text-muted" style="display:block;margin-top:4px;"><i class="fa fa-list"></i> User templates for this device category</small>';
              }
              htmlContent += '</div>';
              htmlContent += '<div class="card-details">';
              htmlContent += '<div class="row">';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Templates <span class="require">*</span></label><div class="col-lg-8"><select class="form-control userAccType device-config-field template-select-' + categoryId + '" id="templates' + categoryId + '" name="configuration[' + categoryId + '][template]" onchange="changeTemplate(' + categoryId + ')"' + (categoryTemplates.length > 0 ? ' required' : '') + '>';
              htmlContent += '<option value="">Select Template</option>';
              if (categoryTemplates.length > 0) {
                defaultTemplatesToTrigger.push({
                  index: categoryId,
                  id: selectedTemplate.id,
                  inputs: input,
                  isParentTemplate: isParentSourced
                });
                categoryTemplates.forEach((temp) => {
                  const labelSuffix = (temp.default_template == 1 ? ' (Default)' : '') + (isParentSourced ? 'http://127.0.0.1:8000/reseller/manage-child-permissions?user_id=38' : '');
                  htmlContent += '<option ' + (temp.id === selectedTemplate.id ? "selected" : "") + ' value="' + temp.id + '">' + temp.template_name + labelSuffix + '</option>';
                });
                if (isParentSourced) {
                  htmlContent += '<input type="hidden" name="configuration[' + categoryId + '][template_source]" value="parent">';
                }
              }
              htmlContent += '</select></div></div></div></div>';

              input.forEach((input, index1) => {
                let validation = JSON.parse(input.validationConfig);
                let configVal = input.default || '';
                if (index1 % 2 === 0) {
                  htmlContent += '<div class="row">';
                }
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<input class="form-control inputType" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                if (input.type == 'select') {
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<select class="form-control inputType device-config-field" name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                  // htmlContent += '<option value="">Please Select</option>';

                  validation?.selectOptions.forEach((option, optIndex) => {
                    let optionValue = validation?.selectValues[optIndex];
                    let isSelected = String(optionValue).toLowerCase() === String(configVal).toLowerCase() ? ' selected' : '';
                    htmlContent += '<option value="' + optionValue + '"' + isSelected + '>' + option + '</option>';
                  });

                  htmlContent += '</select>';
                  htmlContent += '</div>';
                  htmlContent += '</div>';
                } else if (input.type == 'multiselect') {
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<select class="inputType device-config-field" id="configval' + categoryId + '" name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? 'required' : '') + ' multiple>';
                  // htmlContent += '<option value="">Please Select</option>';

                  validation?.selectOptions.forEach((option, optIndex) => {
                    let optionValue = validation?.selectValues[optIndex];
                    let isSelected = String(optionValue).toLowerCase() === String(configVal).toLowerCase() ? ' selected' : '';
                    htmlContent += '<option value="' + optionValue + '"' + isSelected + '>' + option + '</option>';
                  });

                  htmlContent += '</select>';
                  htmlContent += '</div>';
                  htmlContent += '</div>';
                  setTimeout(() => {
                    $('#configval' + categoryId).select2({
                      placeholder: 'Select options',
                      allowClear: true,
                      width: '100%'
                    });
                  }, 100);
                } else {
                  if (input.key == 'Password') {
                    htmlContent += '<div class="form-group">';
                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div class="col-lg-8">';
                    htmlContent += '<input class="form-control passwordInputValidation device-config-field" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + String(configVal).replace(/"/g, '&quot;') + '" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                    htmlContent += '</div>';
                    htmlContent += '</div>';
                  } else {
                    let addClassTextArray = input?.type === 'text_array' ? 'text-array-space' : '';
                    let addClassIpUrl = input?.type === 'IP/URL' ? 'ip-url-space' : '';
                    htmlContent += '<div class="form-group">';
                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div class="col-lg-8">';
                    // htmlContent += '<input class="form-control inputType" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'min="' + validation?.numberInput?.min + '" max="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '" maxlength="' + validation?.maxValueInput') + '>';
                    htmlContent +=
                      '<input class="form-control inputType device-config-field ' + addClassTextArray + ' ' + addClassIpUrl + '" type="' +
                      (input.type === 'number' ? 'number' : 'text') + '" ' +
                      (input.type === 'number' && validation?.numberInput ?
                        'min="' + validation.numberInput.min + '" max="' + validation.numberInput.max + '" ' :
                        '') +
                      'placeholder="Enter ' + input.key + '" ' +
                      'name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
                      'value="' + String(configVal).replace(/"/g, '&quot;') + '" ' +
                      (input.requiredFieldInput ?
                        'required' :
                        (validation?.maxValueInput ? 'maxlength="' + validation.maxValueInput + '"' : '')) +
                      '>';

                    htmlContent += '</div>';
                    htmlContent += '</div>';
                  }
                }
                htmlContent += '</div>'; // Close col-lg-6
                // Close the row for every second iteration or when it's the last item
                if (index1 % 2 === 1 || index1 === input.length - 1) {
                  htmlContent += '</div>'; // Close row
                }
              });
              //   htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label><div class="col-lg-8">
              //   htmlContent += '<input type="number" name="configuration[`ping_interval`][`value`]" place holder="Ping Inteval" value=""/>';
              //   htmlContent +='</div></div>';
              const pingIntervalValue = categoryAdminPingIntervalMap[categoryId] ?? categoryAdminPingIntervalMap[String(categoryId)] ?? 4;
              htmlContent += '<div class="row">';
              if (showAdminConfigFields) {
              htmlContent += '<div class="col-lg-6"><div class="form-group">';
              htmlContent += '<label class="control-label col-lg-3">Ping Interval</label>';
              htmlContent += '<div class="col-lg-8">';
              htmlContent += '<input type="number" class="form-control device-config-field admin-config-field" name="configuration[' + categoryId + '][ping_interval]" value="' + pingIntervalValue + '" min="1" step="1">';
              htmlContent += '</div></div></div>';
              } else {
              htmlContent += '<input type="hidden" name="configuration[' + categoryId + '][ping_interval]" value="' + pingIntervalValue + '">';
              }
              htmlContent += '<input type="hidden" name="configuration[' + categoryId + '][is_editable]" value="1">';
              htmlContent += '</div>';
              if (canEnable) {
                htmlContent += `
                <div class="row" style="padding: 0 15px;">
                  <div class="col-lg-12">
                    <div class="can-config-box isCanEnable${categoryId}">
                      <label class="can-config-label"><i class="fa fa-cogs"></i> CAN Configuration <span class="require">*</span></label>
                      <div class="can-config-input-wrap">
                        <input type="text" class="form-control can-config-input" name="canConfigurationArr[${categoryId}]" id="canConfigurationArr${categoryId}" value="" readonly />
                        <button type="button" class="can-copy-btn" onclick="copyCanConfig('canConfigurationArr${categoryId}')" title="Copy to clipboard">
                          <i class="fa fa-copy"></i>
                        </button>
                      </div>
                      <div class="alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                      <button type="button" class="btn btn-primary can-config-btn" onclick="openCanModal1(${categoryId})">
                        <i class="fa fa-sliders" style="margin-right:6px;"></i> Configure CAN Protocol
                      </button>
                    </div>
                  </div>
                </div>`;
                htmlContent += `
                    <div class="modal can-modal" id="canModal1${categoryId}" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content can-modal-content">
                          <div class="can-accent-bar"></div>
                          <button type="button" class="can-close" data-dismiss="modal">&times;</button>
                          <div class="can-scroll-wrap">
                          <div class="can-body">
                            <div class="can-hero">
                              <div class="can-icon-ring"><i class="fa fa-sliders"></i></div>
                              <h3 class="can-title">CAN Protocol Configuration</h3>
                              <p class="can-subtitle">Configure CAN bus parameters for ${data.device_category_name}</p>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-plug"></i> CAN Channel <span class="require">*</span></label>
                              <select class="form-control can-field-select" id="can_channel${categoryId}" name="canConfiguration[${categoryId}][can_channel]" required>
                                <option value="">-- Select CAN Channel --</option>
                                <option value="1">CAN 1</option>
                                <option value="2">CAN 2</option>
                                <option value="3">CAN 3</option>
                                <option value="4">CAN 4</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-tachometer"></i> CAN Baud Rate <span class="require">*</span></label>
                              <select id="can_baud_rate${categoryId}" name="canConfiguration[${categoryId}][can_baud_rate]" class="form-control can-field-select" required>
                                <option value="">-- Select Baud Rate --</option>
                                <option value="500">500 kbps</option>
                                <option value="250">250 kbps</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-tag"></i> CAN ID Type <span class="require">*</span></label>
                              <select id="can_id_type${categoryId}" name="canConfiguration[${categoryId}][can_id_type]" class="form-control can-field-select" required>
                                <option value="">-- Select CAN ID --</option>
                                <option value="0">Standard</option>
                                <option value="1">Extended</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-cogs"></i> CAN Protocol <span class="require">*</span></label>
                              <select id="can_protocol${categoryId}" name="canConfiguration[${categoryId}][can_protocol]" class="form-control can-field-select">
                                <option value="">-- Select Protocol --</option>
                                <option value="1">J1979</option>
                                <option value="2">J1939</option>
                                <option value="3">Custom CAN</option>
                              </select>
                            </div>
                            <div class="can-dynamic-fields" id="dynamicCanFields1${categoryId}"></div>
                          </div>
                          </div>
                          <div class="can-actions">
                            <button type="button" class="btn can-btn-cancel" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn can-btn-submit" onclick="generateJSON1(${categoryId})">
                              <i class="fa fa-check"></i> Submit
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  `;

              }
              htmlContent += '</div>';

              htmlContent += '</div></div></div>';

            });

            $('#deviceCategoryInputFields').append(htmlContent);
            relocateCanModalsToBody();
            newCheckedValues.forEach(function(val) {
              const categoryId = String(val);
              const canVal = parentCanConfigs[categoryId] || parentCanConfigs[val];
              if (canVal && typeof canVal === 'object' && Object.keys(canVal).length) {
                $('#canConfigurationArr' + categoryId).val(JSON.stringify(canVal));
              }
              const ctx = getCanProtocolModalContext(categoryId);
              if (ctx.modal.length) {
                bindCanProtocolLoader(categoryId, ctx.fieldsSuffix, ctx.modalPrefix, false, ctx.modal, {});
              }
            });
            newCheckedValues.forEach(function(val) {
              if (!existingCheckedValues.map(String).includes(String(val))) {
                existingCheckedValues.push(String(val));
              }
            });

            defaultTemplatesToTrigger.forEach(function(task) {
              $('.device-category-block-' + task.index).data('categoryInputs', task.inputs);
            });

            setTimeout(function() {
              defaultTemplatesToTrigger.forEach(function(task) {
                applyInputDefaults(task.index, task.inputs);
                changeTemplate(task.index, task.id);
              });
            }, 300);
          } else {
            $('#deviceCategoryInputFields').html('<p>No input fields found.</p>');
            alert(result.message);
          }
        },
        error: function(xhr) {
          console.log(xhr.responseText);
          $('#deviceCategoryInputFields').html('<p>Error retrieving data.</p>').show();
        },
        complete: function() {
          $('#loading').hide();
        }
      });
  }

  function applyInputDefaults(categoryId, inputs) {
    if (!inputs || !inputs.length) {
      return;
    }

    inputs.forEach(function(input) {
      if (input.default === undefined || input.default === null || input.default === '') {
        return;
      }

      let key = input.key.replace(/\s+/g, '_').toLowerCase();
      let field = $("input[name='configuration[" + categoryId + "][" + key + "]'], select[name='configuration[" + categoryId + "][" + key + "]'], select[name='configuration[" + categoryId + "][" + key + "][]']");
      if (!field.length) {
        return;
      }

      if (field.is(':radio') || field.is(':checkbox')) {
        field.filter('[value="' + input.default + '"]').prop('checked', true);
        return;
      }

      if (!field.val() || field.val() === '') {
        field.val(input.default);
        if (field.is('select')) {
          field.trigger('change');
        }
      }
    });
  }

  function changeTemplate(index, id = '', categoryInputs = null) {
    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-template') }}";
    let templateId = id;
    if (templateId === '') {
      templateId = $("#templates" + index).val();
    } else {
      $("#templates" + index).val(templateId);
    }

    if (!templateId) {
      return;
    }

    $.ajax({
      url: actionUrl,
      type: "POST",
      data: {
        id: templateId,
        _token: "{{ csrf_token() }}"
      },
      success: function(response) {
        let result;
        try {
          result = (typeof response === 'string') ? JSON.parse(response) : response;
        } catch (e) {
          console.error('Failed to parse template response', e, response);
          return;
        }

        if (result.status != 200) {
          console.error(result.message);
          return;
        }

        let template = result.template;
        try {
          let unwrapCount = 0;
          while (typeof template === 'string' && unwrapCount < 5) {
            template = JSON.parse(template);
            unwrapCount++;
          }
        } catch (e) {
          console.error('Failed to parse template configurations', e, result.template);
          return;
        }

        if (!template || typeof template !== 'object') {
          return;
        }

        Object.keys(template).filter(function(key) {
          return key !== 'template' && key !== 'ping_interval';
        }).forEach(function(key) {
          let rawVal = template[key];
          let val = rawVal;
          if (rawVal && typeof rawVal === 'object' && 'value' in rawVal) {
            val = rawVal.value;
          }

          let normKey = key.toLowerCase().replace(/\s+/g, '_').replace(/_\(sec\)$/, '').replace(/_sec$/, '').replace(/[^a-z0-9]/g, '');

          $('input, select').each(function() {
            let name = $(this).attr('name');
            if (!name || !name.startsWith('configuration[' + index + ']')) {
              return;
            }

            let matches = name.match(/\[([^\]]+)\]$/) || name.match(/\[([^\]]+)\]\[\]$/);
            if (!matches || !matches[1]) {
              return;
            }

            let fieldPart = matches[1];
            let normFieldPart = fieldPart.toLowerCase().replace(/\s+/g, '_').replace(/_\(sec\)$/, '').replace(/_sec$/, '').replace(/[^a-z0-9]/g, '');

            if (normFieldPart !== normKey && fieldPart.toLowerCase() !== key.toLowerCase() && fieldPart.toLowerCase().replace(/\s+/g, '_') !== key.toLowerCase().replace(/\s+/g, '_')) {
              return;
            }

            if ($(this).is(':radio') || $(this).is(':checkbox')) {
              if ($(this).val() == val) {
                $(this).prop('checked', true);
              }
              return;
            }

            let finalVal = val;
            if ($(this).attr('multiple')) {
              if (typeof val === 'string') {
                try {
                  let cleanStr = val.startsWith('{') && val.endsWith('}') ? '[' + val.substring(1, val.length - 1) + ']' : val;
                  finalVal = JSON.parse(cleanStr);
                } catch (e) {
                  finalVal = val.split(',');
                }
              } else if (!Array.isArray(val) && val != null) {
                finalVal = [val];
              }
            }

            if (finalVal !== undefined && finalVal !== null && finalVal !== '') {
              if ($(this).is(':disabled') || $(this).prop('readonly')) {
                return;
              }
              $(this).val(finalVal);
              if ($(this).is('select')) {
                $(this).trigger('change');
              }
            }
          });
        });

        if (isCategoryBlockViewOnly(index)) {
          applyDeviceConfigViewOnlyToBlock(index);
        }

        if (categoryInputs) {
          applyInputDefaults(index, categoryInputs);
        } else {
          let block = $('.device-category-block-' + index);
          let storedInputs = block.data('categoryInputs');
          if (storedInputs) {
            applyInputDefaults(index, storedInputs);
          }
        }
      },
      error: function(xhr) {
        console.log('Error:', xhr.responseText);
      },
      complete: function() {
        $('#loading').hide();
      }
    });
  }
</script>
