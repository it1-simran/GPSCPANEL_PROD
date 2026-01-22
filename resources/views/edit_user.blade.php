@extends('layouts.apps')
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
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Account</a></li>

            <li><a href="/{{$url_type}}/view-user">View Account</a></li>
            <li class="active"><a href="#">Update Account</a></li>
          </ul>
        </nav>
      </div>
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
            @if ($message = Session::get('error'))
            <div class="col-sm-12 alert alert-danger" role="alert">
              {{ $message }}
            </div>
            @endif
            @if ($errors->any())
            <div class="col-sm-12 alert alert-danger" role="alert">
              {{ $errors->first() }}
            </div>
            @endif
          </div>
          <div class="row" id="alert_msg" style='padding: 0px 20px;'>
            @if ($message = Session::get('success'))
            <div class="col-sm-12 alert alert-success" role="alert">
              {{ $message }}
            </div>
            @endif
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
                    <option <?php echo (($contact->user_type == 'Reseller') ? 'selected' : '') ?> value="Reseller">Reseller</option>
                    <option <?php echo (($contact->user_type == 'User') ? 'selected' : '') ?> value="User">User</option>
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
                            <option value="{{ $validationConfig['selectValues'][$configkey] }}" {{ isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]) && $configurationValue && strtolower($validationConfig['selectValues'][$configkey]) == $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] ? 'selected' : '' }}>{{ $option }}</option>
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
                            value="{{ isset($configurationValue) && isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'])  ? $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] : '' }}"
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
                  <div class="row">
                    @if( $category->is_can_protocol == 1 )
                    <div class="isCanEnable{{$category->id}}" style="padding: 0px 25px;">
                      <label for="canConfigurationArr" class="control-label">
                        CAN Configuration <span class="require">*</span>
                      </label>
                      <div class="col-lg-12 padding-1">
                        @php
                        $value = isset($canConfigurations[$category->id] ) ?$canConfigurations[$category->id]: [];
                        $result = is_array($value) ? json_encode($value) : $value;
                        @endphp
                        <input type="text" class="form-control" name="canConfigurationArr[{{$category->id}}]" id="canConfigurationArr{{$category->id}}" value="{{$result}}" readonly />
                        <div class="col-sm-12 alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                        <button type="button" class="btn btn-primary" onclick="openCanModal('{{ $category->id }}')">
                          Configure CAN Protocol
                        </button>
                      </div>
                    </div>
                    @endif
                  </div>
                </div>
                <div class="modal" id="canModal{{$category->id}}">
                  <div class="modal-dialog modal-md">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                          <i class="fa fa-times"></i>
                        </button>
                        <h5 class="modal-title">CAN Protocol Configuration</h5>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-12" style="padding: 0px 25px;">
                            <div id="canForm">
                              <!-- Protocol Selection -->
                              <div class="form-group isCanEnable">
                                <div style="margin:10px 0px;">
                                  <label for="curl" class="control-label padding-left-3">Can Channel<span class="require">*</span></label>
                                  <select id="can_channel{{$category->id}}" name="canConfiguration[{{$category->id}}][can_channel]" class="form-control">
                                    <option value="">-- Select CAN Channel --</option>
                                    <option value="1">CAN 1</option>
                                    <option value="2">CAN 2</option>
                                    <option value="3">CAN 3</option>
                                    <option value="4">CAN 4</option>
                                  </select>
                                </div>
                                <div style="margin:10px 0px;">
                                  <label class="control-label">Can Baud Rate <span class="require">*</span></label>
                                  <select id="can_baud_rate{{$category->id}}" name="canConfiguration[{{$category->id}}][can_baud_rate]" class="form-control">
                                    <option value="">-- Select Baud Rate --</option>
                                    <option value="500">500 kbps</option>
                                    <option value="250">250 kbps</option>
                                  </select>
                                </div>
                                <div style="margin:10px 0px;">
                                  <label class="control-label">Can ID Type <span class="require">*</span></label>
                                  <select id="can_id_type{{$category->id}}" name="canConfiguration[{{$category->id}}][can_id_type]" class="form-control">
                                    <option value="">-- Select Can ID --</option>
                                    <option value="0">Standard</option>
                                    <option value="1">Extended</option>
                                  </select>
                                </div>
                                <div style="margin:10px 0px;">
                                  <label for="can_protocol" class="control-label padding-left-3">
                                    CAN Protocol <span class="require">*</span>
                                  </label>
                                  <select id="can_protocol{{$category->id}}" name="canConfiguration[{{$category->id}}][can_protocol]" class="form-control" onchange="selectedCanProtocol(
                                    '{{$category->id}}')">
                                    <option value="">Select Protocol</option>
                                    <option value="1">J1979</option>
                                    <option value="2">J1939</option>
                                    <option value="3">Custom CAN</option>
                                  </select>
                                </div>
                                <div id="dynamicCanFields{{$category->id}}"></div>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-success mt-4" onclick="generateJSON('{{$category->id}}')">Submit</button>
                          </div>
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
        let actionUrl = "/{{$url_type}}/update-user/{{$contact->id}}/{{$contact->user_type}}";
        let formData = $(this).serialize();

        $.ajax({
          url: actionUrl,
          type: "POST",
          data: formData,
          success: function(response) {
            $(".success_msg").text("updated Successfully!").show();
            document.documentElement.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
            // window.location.reload();
          },
          error: function(xhr) {
            let errors = JSON.parse(xhr.responseText);
            $('.error_msg').empty();
            if (errors && errors.errors) {
              $.each(errors.errors, function(key, value) {
                $('.error_msg').append(value[0] + '<br>').show();
              });
            }
            document.documentElement.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          },
          complete: function() {
            $('#loading').hide();
          }
        });
      } else {
        $('.error_msg').text(error_msg).show();
        document.documentElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
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

      let newCheckedValues = checkedValues.filter(val => !existingCheckedValues.includes(val));
      // Send all selected device category ids to the server
      $.ajax({
        url: actionUrl,
        type: "POST",
        data: {
          ids: newCheckedValues,
          userId: userId
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
            let configValue = <?= json_encode($enhancedInputs) ?>;
            const offset = existingCheckedValues.length;
            inputFields.forEach((data, adjustedIndex) => {
              const index = offset + 1;
              let input = JSON.parse(data.inputs);
              let canEnable = data.is_can_protocol == 1 ? true : false;
              htmlContent += '<div class="device-category-fields card device-category-block-' + deviceCategoryId + '">';
              htmlContent += '<div class="card-title"><h4 >' + data.device_category_name + '</h4></div>';
              htmlContent += '<div class="card-details">';
              htmlContent += '<div class="row">';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Templates <span class="require">*</span></label><div class="col-lg-8"><select class="form-control userAccType" id="templates' + deviceCategoryId + '" name="configuration[' + deviceCategoryId + '][template]" class="select2" onchange="changeTemplate(' + index + ')">';
              if (templates[adjustedIndex].length > 0) {
                templates[adjustedIndex].forEach((temp) => {
                  // if (temp.default_template == 1) {
                  //   changeTemplate(index, temp.id)
                  // }
                  htmlContent += '<option ' + (temp.default_template == 1 ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + '' + (temp.default_template == 1 ? ' (Default)' : '') + '</option>';
                });
              }
              // htmlContent += '<option>No Template Found</option>';
              htmlContent += '</select></div></div></div></div>';

              input.forEach((input, index1) => {
                let validation = JSON.parse(input.validationConfig);
                if (index1 % 2 === 0) {
                  htmlContent += '<div class="row">';
                }
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<input class="form-control inputType" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                if (input.type == 'select') {
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<select class="form-control inputType" name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']["value"]" ' + (input.requiredFieldInput ? '' : '') + '>';
                  // htmlContent += '<option value="">Please Select</option>';

                  validation?.selectOptions.forEach((option, optIndex) => {
                    htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                  });

                  htmlContent += '</select>';
                  htmlContent += '</div>';
                  htmlContent += '</div>';
                } else if (input.type == 'multiselect') {
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<select class="inputType" id="configval' + deviceCategoryId + '" name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? '' : '') + ' multiple>';
                  // htmlContent += '<option value="">Please Select</option>';

                  validation?.selectOptions.forEach((option, optIndex) => {
                    htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                  });

                  htmlContent += '</select>';
                  htmlContent += '</div>';
                  htmlContent += '</div>';
                  setTimeout(() => {
                    $('#configval' + deviceCategoryId).select2({
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
                    htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
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
                      'name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
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
              htmlContent += '<input type="number" name="configuration[' + deviceCategoryId + '][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value=""/>';
              htmlContent += '</div></div></div>';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group">';
              htmlContent += '<label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<label class="padding-10">Enable</label><input checked type="radio" name="configuration[' + deviceCategoryId + '][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>';
              htmlContent += '<label class="padding-10">Disable</label><input type="radio" name="configuration[' + deviceCategoryId + '][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>';

              htmlContent += '</div></div></div>';
              if (canEnable) {
                htmlContent += `
                <div class="isCanEnable` + deviceCategoryId + `" style="padding: 0px 25px;">
                    <label for="canConfigurationArr" class="control-label" required>
                        CAN Configuration <span class="require">*</span>
                    </label>
                    <div class="col-lg-12 padding-1">
                        <input type="text" class="form-control" name="canConfigurationArr[${deviceCategoryId}]" id="canConfigurationArr${deviceCategoryId}" value="" readonly />
                        <div class="col-sm-12 alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                        <button type="button" class="btn btn-primary" onclick="openCanModal1(` + deviceCategoryId + `)">
                            Configure CAN Protocol
                        </button>
                    </div>
                </div>`;
                htmlContent += `
                    <div class="modal" id="canModal1` + deviceCategoryId + `" aria-hidden="true">
                      <div class="modal-dialog modal-md">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                              <i class="fa fa-times"></i>
                            </button>
                            <h5 class="modal-title">CAN Protocol Configuration</h5>
                          </div>
                          <div class="modal-body">
                            <div class="row">
                              <div class="col-md-12" style="padding: 0px 25px;">
                                <div id="canForm">
                                  <!-- Protocol Selection -->
                                    <div class="form-group isCanEnable">
                                      <div style="margin:10px 0px;">
                                        <label for="curl" class="control-label padding-left-3">Can Channel<span class="require">*</span></label>
                                        <select class="form-control" id="can_channel${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_channel]" required>
                                          <option value="">-- Select CAN Channel --</option>
                                          <option value="1">CAN 1</option>
                                          <option value="2">CAN 2</option>
                                          <option value="3">CAN 3</option>
                                          <option value="4">CAN 4</option>
                                        </select>
                                      </div>
                                      <div style="margin:10px 0px;">
                                        <label class="control-label">Can Baud Rate <span class="require">*</span></label>
                                        <select id="can_baud_rate${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_baud_rate]" class="form-control" required>
                                          <option value="">-- Select Baud Rate --</option>
                                          <option value="500">500 kbps</option>
                                          <option value="250">250 kbps</option>
                                        </select>
                                      </div>
                                      <div style="margin:10px 0px;">
                                        <label class="control-label">Can ID Type <span class="require">*</span></label>
                                        <select id="can_id_type${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_id_type]" class="form-control" required>
                                          <option value="">-- Select Can ID --</option>
                                          <option value="0">Standard</option>
                                          <option value="1">Extended</option>
                                        </select>
                                      </div>
                                      <div style="margin:10px 0px;">
                                        <label for="can_protocol" class="control-label padding-left-3">
                                          CAN Protocol <span class="require">*</span>
                                        </label>
                                        <select id="can_protocol${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_protocol]" class="form-control" onchange="selectedCanProtocol1(${deviceCategoryId})">
                                          <option value="">Select Protocol</option>
                                          <option value="1">J1979</option>
                                          <option value="2">J1939</option>
                                          <option value="3">Custom CAN</option>
                                        </select>
                                      </div>
                                    </div>
                                  <div id="dynamicCanFields1${deviceCategoryId}"></div>
                                </div>
                              </div>
                              <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-success mt-4" onclick="generateJSON1(${deviceCategoryId})">Submit</button>
                              </div>
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

            $('#deviceCategoryInputFields').html(htmlContent);
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

  // Event handler for checkbox change
  $('.bgx-checkbox-category').change(function() {
    getDeviceCategoryInput();
  });

  function changeTemplate(index, id = '') {
    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-template') }}";
    if (id == '') {
      let value = $("#templates" + index).val();
      id = value;
    }
    $.ajax({
      url: actionUrl,
      type: "POST",
      data: {
        id: id
      },
      success: function(response) {
        let result = JSON.parse(response);
        if (result.status == 200) {
          let template = JSON.parse(result.template);
          Object.keys(template).forEach(function(key) {
            let element = $("input[name='configuration[" + index + "][" + key + "]'], select[name='configuration[" + index + "][" + key + "]']");
            if (element.is('input')) {
              element.val(template[key]['value']);
            } else if (element.is('select') && key != 'template') {
              element.val(template[key]['value']);
            }
          });
          // Handle the response data as needed
        } else {
          console.error(result.message);
        }
      },
      error: function(xhr) {
        console.log('Error:', xhr.responseText);
        // Handle the error if AJAX request fails
      },
      complete: function() {
        $('#loading').hide(); // Hide loading indicator regardless of success or error
      }
    });
  }
</script>