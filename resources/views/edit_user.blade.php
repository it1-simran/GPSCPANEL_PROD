@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('edit-user') }}">
@endpush
@section('content')
<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;
use App\Models\TimezoneModel;

$timeZones = TimezoneModel::all();

use App\Template;
use App\DataFields;

$deviceCategoryIds = explode(',', $contact->device_category_id);

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
                <div class="col-lg-6 bgx-margin-top row ">
                  @foreach($getDeviceCategory as $deviceCategory)
                  <div class="row col-md-6">
                    <div class="col-xs-6 col-sm-6 col-md-4">
                      <label class='bgx-label-category'>{{$deviceCategory->device_category_name}}</label>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-4 text-right">
                      <input type="checkbox" {{ in_array($deviceCategory->id, $deviceCategoryIds) ? 'checked' : '' }} class="bgx-checkbox-category" name="deviceCategory[]" value="{{ $deviceCategory->id }}" onclick="getDeviceCategoryInput({{$contact->id}},{{$deviceCategory->id}})">
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @foreach($getDeviceCategoryconfig as $key => $category)
              @if(in_array($category->id,$deviceCategoryIds))

              <div class="device-category-fields card device-category-block-{{ $category->id }}">
                <div class="card-title">
                  <h4>{{ CommonHelper::getDeviceCategoryName($category->id) }}</h4>
                </div>
                <div class="card-details">
                  @php
                  $inputs = json_decode($category->inputs, true);
                  $totalInputs = count($inputs);
                  $inputIds = collect($inputs)->pluck('id')->toArray();
                  $dataFields = DataFields::whereIn('id', $inputIds)->get()->keyBy('id');
                  $user = Auth::user();

                  $templates = Template::where('device_category_id', $category->id)
                  ->where(function ($query) use ($user) {
                  if ($user->user_type == 'Admin') {
                  $query->whereNull('id_user');
                  } else {
                  $query->where('id_user', $user->id);
                  }
                  })
                  ->get();
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
                          <select class="userAccType form-control"
                            id="templates<?= $category->id ?>"
                            name="configuration[<?= $category->id ?>][template]"
                            onchange="changeTemplate(<?= $category->id ?>)">
                            <?php if (!empty($templates)): ?>
                              <?php foreach ($templates as $temp): ?>
                                <option value="<?= $temp['id'] ?>">
                                  <?= htmlspecialchars($temp['template_name']) ?>
                                  <?= $temp['default_template'] == 1 ? ' (Default)' : '' ?>
                                </option>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <option>No Template Found</option>
                            <?php endif; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                  @foreach($enhancedInputs as $index => $input)
                  @php
                  $configurationValue = isset($configurations[$key]) ? $configurations[$key]: null;
                  @endphp

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
                          <select class="form-control inputType" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" {{ $input['requiredFieldInput'] ? 'required' : '' }}>
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
                          <select class="inputType" id="configval{{$category->id}}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}][]" multiple {{ $input['requiredFieldInput'] ? 'required' : '' }}>
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
                          <input class="form-control {{$addClassTextArray}} {{$addClassIpUrl}}" type="{{ $input['type'] == 'number' ? 'number' : 'text' }}"
                            {!! $input['type']=='number' ? 'min="' . ($input['numberRange']['min'] ?? '' ) . '" max="' . ($input['numberRange']['max'] ?? '' ) . '"' : '' !!}
                            placeholder="Enter {{ isset($input['key']) ? $input['key'] :''  }}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]"
                            value="{{ isset($configurationValue) && isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value']) && $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] !== '' ? $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] : ($input['default'] ?? '') }}"
                            {{ $input['requiredFieldInput'] ? 'required' : '' }}>
                        </div>
                      </div>
                      @endif
                    </div>
                    @if ($index % 2 === 1 || $index === $totalInputs - 1)
                  </div>
                  @endif
                  @endforeach
                  @if(Auth::user()->user_type =='Admin')
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label>
                        <div class="col-lg-8">
                          <input type="number" name="configuration[{{ $category->id }}][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['ping_interval']['value'])  ? $configurationValue['ping_interval']['value'] : '' }}" />
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>
                        <div class="col-lg-6">
                          <label class="padding-10">Enable</label>
                          <input checked type="radio" name="configuration[{{ $category->id }}][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>
                          <label class="padding-10">Disable</label>
                          <input type="radio" name="configuration[{{ $category->id }}][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>
                        </div>
                      </div>
                    </div>

                  </div>
                  @else
                  <input type="hidden" name="configuration[{{ $category->id }}][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['ping_interval']['value'])  ? $configurationValue['ping_interval']['value'] : '' }}" />
                  <input type="hidden" name="configuration[{{ $category->id }}][is_editable]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['is_editable']['value'])  ? $configurationValue['is_editable']['value'] : '' }}" />
                  @endif
                  @if( $category->is_can_protocol == 1 )
                  <div class="row" style="padding: 0 15px;">
                    <div class="col-lg-12">
                      <div class="can-config-box isCanEnable{{$category->id}}">
                        <label class="can-config-label"><i class="fa fa-cogs"></i> CAN Configuration <span class="require">*</span></label>
                        @php
                        $value = isset($canConfigurations[$category->id] ) ?$canConfigurations[$category->id]: [];
                        $result = is_array($value) ? json_encode($value) : $value;
                        @endphp
                        <div class="can-config-input-wrap">
                          <input type="text" class="form-control can-config-input" name="canConfigurationArr[{{$category->id}}]" id="canConfigurationArr{{$category->id}}" value="{{$result}}" readonly />
                          <button type="button" class="can-copy-btn" onclick="copyCanConfig('canConfigurationArr{{$category->id}}')" title="Copy to clipboard">
                            <i class="fa fa-copy"></i>
                          </button>
                        </div>
                        <div class="alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                        <button type="button" class="btn btn-primary can-config-btn" onclick="openCanModal('{{ $category->id }}')">
                          <i class="fa fa-sliders" style="margin-right:6px;"></i> Configure CAN Protocol
                        </button>
                      </div>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="modal can-modal" id="canModal{{$category->id}}">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content can-modal-content">
                      <div class="can-accent-bar"></div>
                      <button type="button" class="can-close" data-dismiss="modal">&times;</button>
                      <div class="can-body">
                        <div class="can-hero">
                          <div class="can-icon-ring"><i class="fa fa-sliders"></i></div>
                          <h3 class="can-title">CAN Protocol Configuration</h3>
                          <p class="can-subtitle">Configure CAN bus parameters for {{ CommonHelper::getDeviceCategoryName($category->id) }}</p>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-plug"></i> CAN Channel <span class="require">*</span></label>
                          <select id="can_channel{{$category->id}}" name="canConfiguration[{{$category->id}}][can_channel]" class="form-control">
                            <option value="">-- Select CAN Channel --</option>
                            <option value="1">CAN 1</option>
                            <option value="2">CAN 2</option>
                            <option value="3">CAN 3</option>
                            <option value="4">CAN 4</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-tachometer"></i> CAN Baud Rate <span class="require">*</span></label>
                          <select id="can_baud_rate{{$category->id}}" name="canConfiguration[{{$category->id}}][can_baud_rate]" class="form-control">
                            <option value="">-- Select Baud Rate --</option>
                            <option value="500">500 kbps</option>
                            <option value="250">250 kbps</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-tag"></i> CAN ID Type <span class="require">*</span></label>
                          <select id="can_id_type{{$category->id}}" name="canConfiguration[{{$category->id}}][can_id_type]" class="form-control">
                            <option value="">-- Select CAN ID --</option>
                            <option value="0">Standard</option>
                            <option value="1">Extended</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-cogs"></i> CAN Protocol <span class="require">*</span></label>
                          <select id="can_protocol{{$category->id}}" name="canConfiguration[{{$category->id}}][can_protocol]" class="form-control" onchange="selectedCanProtocol('{{$category->id}}')">
                            <option value="">-- Select Protocol --</option>
                            <option value="1">J1979</option>
                            <option value="2">J1939</option>
                            <option value="3">Custom CAN</option>
                          </select>
                        </div>
                        <div class="can-dynamic-fields" id="dynamicCanFields{{$category->id}}"></div>
                        <div class="can-actions">
                          <button type="button" class="btn can-btn-cancel" data-dismiss="modal">Cancel</button>
                          <button type="button" class="btn can-btn-submit" onclick="generateJSON('{{$category->id}}')">
                            <i class="fa fa-check"></i> Submit
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
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

  function openCanModal(index) {
    $('#canModal' + index).modal('show');

    const config = JSON.parse(document.getElementById(`canConfigurationArr${index}`).value);
    // const config1 = JSON.parse(config);
    const canProtocolEl = $('#can_protocol' + index);
    canProtocolEl.one('change', function() {
      for (let field in config) {
        const value = config[field]?.value;
        if (document.getElementById(field)) {
          document.getElementById(field).value = value;
        }
      }
    });
    canProtocolEl.val(config['can_protocol' + index]['value']).trigger('change');
  }

  function openCanModal1(index) {
    $('#canModal1' + index).modal('show');
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

  function selectedCanProtocol(index) {
    let canProtocolValue = $('#can_protocol' + index).val();
    if (!canProtocolValue) return;

    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-can-protocol-fields') }}";

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: {
        protocol: canProtocolValue,
        _token: '{{ csrf_token() }}'
      },
      success: function(fields) {
        let html = '<div class="row">';

        fields.forEach(field => {
          const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
          const inputType = field.inputType;
          let config = {};
          try {
            config = JSON.parse($("#canConfigurationArr" + index).val());
          } catch (e) {
            console.warn("Invalid JSON, using empty config.");
          }
          let validation = {};
          console.log("config ==>", config);
          try {
            validation = JSON.parse(field.validationConfig || '{}');
          } catch (e) {
            console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
          }
          let value = config[fieldId]?.value ?? '';

          // Escape for input fields
          let escapedValue = String(value).replace(/"/g, '&quot;');
          let inputHtml = `<input type="hidden" name="idCanParameters[${index}][${fieldId}]" value="${field.id}" />`;
          inputHtml += `<input type="hidden" name="CanParametersType[${index}][${fieldId}]" value="${inputType}" />`;
          let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control"  placeholder="Enter ${field.fieldName}" value="${escapedValue}"`;
          if (inputType === 'number') {
            if (validation.numberInput) {
              attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
            }
            inputHtml += `<input type="number" ${attr} />`;
          } else if (inputType === 'select') {
            inputHtml += `<select ${attr}>`;

            const selectedValue = config[fieldId]?.value ?? '';

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach(option => {
                const isSelected = option === selectedValue ? 'selected' : '';
                inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = key == selectedValue ? 'selected' : '';
                inputHtml += `<option value="${key}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;
          } else if (inputType === 'multiselect') {
            inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${index}][${fieldId}][]">`;

            const selectedValue = config[fieldId]?.value ?? [];
            // Ensure selectedValue is always an array
            const selectedArray = Array.isArray(selectedValue) ? selectedValue : [selectedValue];

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach((option, key) => {
                const isSelected = selectedArray.includes(option) ? 'selected' : '';
                inputHtml += `<option value="${validation.selectValues[key]}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = selectedArray.includes(key) ? 'selected' : '';
                inputHtml += `<option value="${key}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;

            // Apply Select2
            setTimeout(() => {
              $(document).ready(function() {
                var $select = $('#' + fieldId);
                $select.select2({
                  placeholder: "Select up to 3 options",
                  width: "100%"
                });
                $select.on("change", function() {
                  var selected = $(this).select2("val");
                  if (selected && selected.length > validation.maxSelectValue) {
                    selected.splice(validation.maxSelectValue);
                    $(this).select2("val", selected);
                    alert("You can only select up to " + validation.maxSelectValue + " options.");
                  }
                });
              });
            }, 100);
          } else if (inputType === 'text_array') {
            let values = [""];
            let maxValue = validation.maxValueInput || 0;
            console.log("maxValue ==>", maxValue);
            inputHtml += `
              <div id="${fieldId}_wrapper_${index}" class="text-array-wrapper">
                ${values.map((val, i) => `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text"
                      maxlength='8'
                      id="${fieldId}${index}${i}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" 
                      value="${val.trim()}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `).join('')}
                <button type="button" class="btn btn-sm btn-primary add-text-input mt-1">
                  <i class="fa fa-plus"></i> Add
                </button>
              </div>
            `;
            inputHtml += `
              <input type="hidden" 
                id="${fieldId}" 
                name="canConfiguration[${index}][${fieldId}]" />
            `;
            setTimeout(function() {
              const wrapper = $("#" + fieldId + "_wrapper_" + index);
              const addButton = wrapper.find(".add-text-input");
              console.log("maxValue ==>", maxValue);
              wrapper.on("click", ".add-text-input", function() {
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count >= maxValue) {
                  alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                  addButton.prop("disabled", true);
                  return;
                }

                const newInput = `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text" 
                      id="${fieldId}${index}${count}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `;
                $(this).before(newInput);
                const newCount = wrapper.find(".text-array-item").length;
                if (maxValue && newCount >= maxValue) {
                  addButton.prop("disabled", true);
                }
                updateHiddenValue();
              });
              wrapper.on("click", ".remove-text-input", function() {
                $(this).closest(".text-array-item").remove();
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count < maxValue) {
                  addButton.prop("disabled", false);
                }
                updateHiddenValue();
              });
              wrapper.on("input", "input[type=text]", function() {
                updateHiddenValue();
              });

              function updateHiddenValue() {
                const values = [];
                wrapper.find("input[type=text]").each(function() {
                  const val = $(this).val().trim();
                  if (val) values.push(val);
                });
                $("#" + fieldId).val("{" + values.join(",") + "}");
              }
              updateHiddenValue();
            }, 100);
          } else if (inputType === 'hex') {
            
            let attr1 = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control text-array-space me-2"`;
            let maxValue = validation.maxValueInput || 0;
            if (validation.maxValueInput) {
              attr1 += `maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr1} value="${escapedValue}"/>`;

          } else {
            if (validation.maxValueInput) {
              attr += ` maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr} />`;
          }

          html += `<div class="col-md-12">
                    <div class="form-group" id="modalInput">
                        <label for="${fieldId}" class="control-label padding-left-14">
                            ${field.fieldName} <span class="require">*</span>
                        </label>
                        <div class="col-lg-12">
                            ${inputHtml}
                            <div class="col-sm-12 alert alert-danger ${fieldId}_error" role="alert" style="display:none"></div>
                        </div>
                    </div></div>`;
        });
        html += '</div>';
        $('#dynamicCanFields' + index).html(html).show();
      },
      error: function(xhr) {
        console.error("Error fetching CAN protocol fields", xhr);
      }
    });
  }

  function selectedCanProtocol1(index) {
    let canProtocolValue = $('#can_protocol' + index).val();
    if (!canProtocolValue) return;

    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-can-protocol-fields') }}";

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: {
        protocol: canProtocolValue,
        _token: '{{ csrf_token() }}'
      },
      success: function(fields) {
        let html = '<div class="row">';

        fields.forEach(field => {
          const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
          const inputType = field.inputType;
          let config = {};
          try {
            config = JSON.parse($("#canConfigurationArr" + index).val());
          } catch (e) {
            console.warn("Invalid JSON, using empty config.");
          }
          let validation = {};
          console.log("config ==>", config);
          try {
            validation = JSON.parse(field.validationConfig || '{}');
          } catch (e) {
            console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
          }
          let value = config[fieldId]?.value ?? '';

          // Escape for input fields
          let escapedValue = String(value).replace(/"/g, '&quot;');
          let inputHtml = `<input type="hidden" name="idCanParameters[${index}][${fieldId}]" value="${field.id}" />`;
          inputHtml += `<input type="hidden" name="CanParametersType[${index}][${fieldId}]" value="${inputType}" />`;
          let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control"  placeholder="Enter ${field.fieldName}" value="${escapedValue}"`;
          console.log('inputType ==>', field.fieldName, inputType);
          if (inputType === 'number') {
            if (validation.numberInput) {
              attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
            }
            inputHtml += `<input type="number" ${attr} />`;
          } else if (inputType === 'select') {
            inputHtml += `<select ${attr}>`;
            const selectedValue = config[fieldId]?.value ?? '';

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach((option, key) => {
                const isSelected = option === selectedValue ? 'selected' : '';
                inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = key == selectedValue ? 'selected' : '';
                inputHtml += `<option value="${validation.selectValues[key]}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;
          } else if (inputType === 'multiselect') {
            inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${index}][${fieldId}][]">`;

            const selectedValue = config[fieldId]?.value ?? [];

            console.log("selectedValue =>", selectedValue);

            // Ensure selectedValue is always an array
            const selectedArray = Array.isArray(selectedValue) ? selectedValue : [selectedValue];

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach(option => {
                const isSelected = selectedArray.includes(option) ? 'selected' : '';
                inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = selectedArray.includes(key) ? 'selected' : '';
                inputHtml += `<option value="${key}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;

            // Apply Select2

            setTimeout(() => {

              $(document).ready(function() {
                var $select = $('#' + fieldId);
                $select.select2({
                  placeholder: "Select up to 3 options",
                  width: "100%"
                });
                $select.on("change", function() {
                  var selected = $(this).select2("val");
                  if (selected && selected.length > validation.maxSelectValue) {
                    selected.splice(validation.maxSelectValue);
                    $(this).select2("val", selected);
                    alert("You can only select up to " + validation.maxSelectValue + " options.");
                  }
                });
              });
            }, 100);
          } else if (inputType === 'text_array') {
            let values = [""];
            let maxValue = validation.maxValueInput || 0;
            console.log("maxValue ==>", maxValue);
            inputHtml += `
              <div id="${fieldId}_wrapper_${index}" class="text-array-wrapper">
                ${values.map((val, i) => `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text"
                      maxlength='8'
                      id="${fieldId}${index}${i}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" 
                      value="${val.trim()}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `).join('')}
                <button type="button" class="btn btn-sm btn-primary add-text-input mt-1">
                  <i class="fa fa-plus"></i> Add
                </button>
              </div>
            `;
            inputHtml += `
              <input type="hidden" 
                id="${fieldId}" 
                name="canConfiguration[${index}][${fieldId}]" />
            `;
            setTimeout(function() {
              const wrapper = $("#" + fieldId + "_wrapper_" + index);
              const addButton = wrapper.find(".add-text-input");
              console.log("maxValue ==>", maxValue);
              wrapper.on("click", ".add-text-input", function() {
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count >= maxValue) {
                  alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                  addButton.prop("disabled", true);
                  return;
                }

                const newInput = `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text" 
                      id="${fieldId}${index}${count}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `;
                $(this).before(newInput);
                const newCount = wrapper.find(".text-array-item").length;
                if (maxValue && newCount >= maxValue) {
                  addButton.prop("disabled", true);
                }
                updateHiddenValue();
              });
              wrapper.on("click", ".remove-text-input", function() {
                $(this).closest(".text-array-item").remove();
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count < maxValue) {
                  addButton.prop("disabled", false);
                }
                updateHiddenValue();
              });
              wrapper.on("input", "input[type=text]", function() {
                updateHiddenValue();
              });

              function updateHiddenValue() {
                const values = [];
                wrapper.find("input[type=text]").each(function() {
                  const val = $(this).val().trim();
                  if (val) values.push(val);
                });
                $("#" + fieldId).val("{" + values.join(",") + "}");
              }
              updateHiddenValue();
            }, 100);
          } else if (inputType === 'hex') {
            let attr1 = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control text-array-space me-2"`;
            let maxValue = validation.maxValueInput || 0;
            if (validation.maxValueInput) {
              attr1 += `maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr1}  value="${escapedValue}"/>`;

          } else {
            if (validation.maxValueInput) {
              attr += ` maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr} />`;
          }

          html += `<div class="col-md-12 padding-3 padding-top-10">
                    <div class="form-group" id="modalInput">
                        <label for="${fieldId}" class="control-label padding-left-14">
                            ${field.fieldName} <span class="require">*</span>
                        </label>
                        <div class="col-lg-12">
                            ${inputHtml}
                            <div class="col-sm-12 alert alert-danger ${fieldId}_error" role="alert" style="display:none"></div>
                        </div>
                    </div></div>`;
        });
        html += '</div>';
        $('#dynamicCanFields1' + index).html(html).show();
      },
      error: function(xhr) {
        console.error("Error fetching CAN protocol fields", xhr);
      }
    });
  }

  function generateJSON(index) {
    let canConfigData = {};

    $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
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

    $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
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
      $('#canConfigurationArr' + index).val(JSON.stringify(canConfigData));
      $('#canModal1' + index).modal('hide');
    });
  }

  let existingCheckedValues = <?= json_encode($deviceCategoryIds) ?>;

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
  })
  $(document).ready(function() {
    $('#commentForm').submit(function(event) {
      //event.preventDefault();
      $('.error_msg').html('').hide();
      $('.success_msg').html('').hide();
      let error_msg = "";
      let formIsValid = true;


      $(this).find('input[required], select[required]').each(function() {
        let inputValue = $(this).val();
        let inputType = $(this).attr('type');
        let inputName = $(this).attr('name');
        let label = $(this).closest('.form-group').find('.control-label').text();


        if (inputType === 'number') {
          let minVal = parseFloat($(this).attr('min'));
          let maxVal = parseFloat($(this).attr('max'));
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
      });

      if (formIsValid) {
        let selectedUserType = $('#userType').length ? $('#userType').val() : "{{$contact->user_type}}";
        let actionUrl = "/{{$url_type}}/update-user/{{$contact->id}}/" + selectedUserType;
        let formData = $(this).serialize();

        $.ajax({
          url: actionUrl,
          type: "POST",
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
    // Get all checked checkbox values
    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-multiple-categories') }}";

    const isChecked = $(`input.bgx-checkbox-category[value="${deviceCategoryId}"]`).is(':checked');

    if (!isChecked) {
      existingCheckedValues = existingCheckedValues.filter(val => val != deviceCategoryId);
      $(`.device-category-block-${deviceCategoryId}`).remove(); // Remove section
    } else {


      let checkedValues = $('input.bgx-checkbox-category:checked').map(function() {
        return this.value;
      }).get();

      let newCheckedValues = checkedValues.filter(function(val) {
        return !existingCheckedValues.map(String).includes(String(val));
      });
      // Send all selected device category ids to the server
      $.ajax({
        url: actionUrl,
        type: "POST",
        data: {
          ids: newCheckedValues,
          userId: userId,
          _token: "{{ csrf_token() }}"
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
            let defaultTemplatesToTrigger = [];
            inputFields.forEach((data, adjustedIndex) => {
              const categoryId = data.id;
              let input = JSON.parse(data.inputs);
              let canEnable = data.is_can_protocol == 1 ? true : false;
              htmlContent += '<div class="device-category-fields card device-category-block-' + categoryId + '">';
              htmlContent += '<div class="card-title"><h4 >' + data.device_category_name + '</h4></div>';
              htmlContent += '<div class="card-details">';
              htmlContent += '<div class="row">';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Templates <span class="require">*</span></label><div class="col-lg-8"><select class="form-control userAccType" id="templates' + categoryId + '" name="configuration[' + categoryId + '][template]" onchange="changeTemplate(' + categoryId + ')">';
              if (templates[adjustedIndex] && templates[adjustedIndex].length > 0) {
                let selectedTemplate = templates[adjustedIndex].find(function(temp) {
                  return temp.default_template == 1;
                }) || templates[adjustedIndex][0];
                defaultTemplatesToTrigger.push({
                  index: categoryId,
                  id: selectedTemplate.id,
                  inputs: input
                });
                templates[adjustedIndex].forEach((temp) => {
                  htmlContent += '<option ' + (temp.id === selectedTemplate.id ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + '' + (temp.default_template == 1 ? ' (Default)' : '') + '</option>';
                });
              }
              // htmlContent += '<option>No Template Found</option>';
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
                  htmlContent += '<select class="form-control inputType" name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? '' : '') + '>';
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
                  htmlContent += '<select class="inputType" id="configval' + categoryId + '" name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? '' : '') + ' multiple>';
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
                    htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + categoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + String(configVal).replace(/"/g, '&quot;') + '" ' + (input.requiredFieldInput ? 'required' : '') + '>';
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
                      '<input class="form-control inputType ' + addClassTextArray + ' ' + addClassIpUrl + '" type="' +
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
              htmlContent += '<div class="row">';
              htmlContent += '<div class="col-lg-6"><div class="form-group">';
              htmlContent += '<label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label>';
              htmlContent += '<div class="col-lg-8">';
              htmlContent += '<input type="number" name="configuration[' + categoryId + '][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value=""/>';
              htmlContent += '</div></div></div>';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group">';
              htmlContent += '<label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<label class="padding-10">Enable</label><input checked type="radio" name="configuration[' + categoryId + '][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>';
              htmlContent += '<label class="padding-10">Disable</label><input type="radio" name="configuration[' + categoryId + '][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>';

              htmlContent += '</div></div></div>';
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
                          <div class="can-body">
                            <div class="can-hero">
                              <div class="can-icon-ring"><i class="fa fa-sliders"></i></div>
                              <h3 class="can-title">CAN Protocol Configuration</h3>
                              <p class="can-subtitle">Configure CAN bus parameters for ${data.device_category_name}</p>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-plug"></i> CAN Channel <span class="require">*</span></label>
                              <select class="form-control" id="can_channel${categoryId}" name="canConfiguration[${categoryId}][can_channel]" required>
                                <option value="">-- Select CAN Channel --</option>
                                <option value="1">CAN 1</option>
                                <option value="2">CAN 2</option>
                                <option value="3">CAN 3</option>
                                <option value="4">CAN 4</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-tachometer"></i> CAN Baud Rate <span class="require">*</span></label>
                              <select id="can_baud_rate${categoryId}" name="canConfiguration[${categoryId}][can_baud_rate]" class="form-control" required>
                                <option value="">-- Select Baud Rate --</option>
                                <option value="500">500 kbps</option>
                                <option value="250">250 kbps</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-tag"></i> CAN ID Type <span class="require">*</span></label>
                              <select id="can_id_type${categoryId}" name="canConfiguration[${categoryId}][can_id_type]" class="form-control" required>
                                <option value="">-- Select CAN ID --</option>
                                <option value="0">Standard</option>
                                <option value="1">Extended</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-cogs"></i> CAN Protocol <span class="require">*</span></label>
                              <select id="can_protocol${categoryId}" name="canConfiguration[${categoryId}][can_protocol]" class="form-control" onchange="selectedCanProtocol1(${categoryId})">
                                <option value="">-- Select Protocol --</option>
                                <option value="1">J1979</option>
                                <option value="2">J1939</option>
                                <option value="3">Custom CAN</option>
                              </select>
                            </div>
                            <div class="can-dynamic-fields" id="dynamicCanFields1${categoryId}"></div>
                            <div class="can-actions">
                              <button type="button" class="btn can-btn-cancel" data-dismiss="modal">Cancel</button>
                              <button type="button" class="btn can-btn-submit" onclick="generateJSON1(${categoryId})">
                                <i class="fa fa-check"></i> Submit
                              </button>
                            </div>
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
  }

  // Checkbox handled via onclick on each input.

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
          return key !== 'template';
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
              $(this).val(finalVal);
              if ($(this).is('select')) {
                $(this).trigger('change');
              }
            }
          });
        });

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
