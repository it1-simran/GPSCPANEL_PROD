  <?php

  use App\Helper\CommonHelper;
  use App\Models\TimezoneModel;

  $timeZones = TimezoneModel::all();

  use App\DataFields;
  use App\Template;

  $getDeviceCategory = CommonHelper::getDeviceCategory();
  $currentUser =  Auth::user();
  if ($currentUser->user_type == 'Reseller') {
    $getDeviceCategoryId = $currentUser->device_category_id;
    $deviceCategoryArr = explode(",", $getDeviceCategoryId);
    $configurations = json_decode($currentUser->configurations, true);
  }

  ?>
  @extends('layouts.apps')
  @section('content')
  <!--main content start-->
  <section id="main-content">
    <section class="wrapper">
      <!--======== Page Title and Breadcrumbs Start ========-->
      <div class="top-page-header">
        <div class="page-breadcrumb">
          <nav class="c_breadcrumbs">
            <ul>
              <li><a href="#">Account</a></li>
              <li class="active"><a href="#">Add Account</a></li>
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
              <h2>Add Account</h2>
              <div class="clearfix"></div>
            </div><!--/.c_title-->
            <div class="c_content">
              <div class="row" id="alert_msg">
                @if ($message = Session::get('success'))
                <div class="col-sm-12 alert alert-success" role="alert">
                  {{ $message }}
                </div>
                @endif
                @if ($message = Session::get('error'))
                <div class="col-sm-12 alert alert-danger" role="alert">
                  {{ $message }}
                </div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger">
                  <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
                @endif
                <div class="col-sm-12 alert alert-success success_msg" role="alert" style="display:none"></div>
                <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                <form class="validator form-horizontal" id="commentForm" method="post" action="#" onsubmit="return false">
                  @csrf
                  <div class="form-group">
                    <label for="curl" class="control-label col-lg-3">Account Type</label>
                    <div class="col-lg-6">
                      <select class="form-control" id="userType" name="user_type">
                        <option value="Reseller">Manufacturer</option>
                        <option selected="selected" value="User">Dealer</option>
                        @if($currentUser->user_type =='Admin')
                        <option value="Support">Support</option>
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="cname" class="control-label col-lg-3">Name <span class="require">*</span></label>
                    <div class="col-lg-6">
                      <input class=" form-control" placeholder="Enter Name" id="cname" name="name" type="text" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="cemail" class="control-label col-lg-3">Mobile <span class="require">*</span></label>
                    <div class="col-lg-6">
                      <input class="form-control" placeholder="Enter Mobile Number" id="cmobile" type="text" name="mobile" maxlength="10" required />
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="cemail" class="control-label col-lg-3">E-Mail <span class="require">*</span></label>
                    <div class="col-lg-6">
                      <input class="form-control" placeholder="Enter E-Mail" id="cemail" type="email" name="email" required />
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
                        <option value="{{ $timezone->name }}">
                          {{ $tzValue }}
                        </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="curl" class="control-label col-lg-3">Login Password <span class="require">*</span></label>
                    <div class="col-lg-6">
                      <input class="form-control" placeholder="Enter Login password" type="password" name="password" required />
                    </div>
                  </div>
                  <div class="is-support-active" style="display: none;"></div>
                  @if($currentUser->user_type =='Admin')

                  <div class="form-group bgx-margin-bottom row">
                    <label for="curl" class="control-label col-lg-3">Device Categories<span class="require">*</span></label>
                    <div class="col-lg-9 bgx-margin-top row ">
                      @foreach($getDeviceCategory as $deviceCategory)
                      <div class="row col-md-6">
                        <div class="col-xs-6 col-sm-6 col-md-4">
                          <label class='bgx-label-category'>{{$deviceCategory->device_category_name}}</label>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-2 text-right">
                          <input type="checkbox" class="bgx-checkbox-category " name="deviceCategory[]" value="{{$deviceCategory->id}}" onclick="getDeviceCateGoryInput()">
                        </div>
                      </div>
                      @endforeach
                    </div>
                  </div>
                  @endif
                  @if($currentUser->user_type == 'Reseller')
                  <div class="form-group bgx-margin-bottom row">
                    <label for="curl" class="control-label col-lg-3">Device Categories<span class="require">*</span></label>
                    <div class="col-lg-6 bgx-margin-top row ">
                      @foreach($getDeviceCategory as $deviceCategory)
                      @if(in_array($deviceCategory->id,$deviceCategoryArr))
                      <div class="row">
                        <div class="col-md-4">
                          <label class='bgx-label-category'>{{$deviceCategory->device_category_name}}</label>
                        </div>
                        <div class="col-md-2">
                          <input type="checkbox" {{ in_array($deviceCategory->id, $deviceCategoryArr) ? 'checked' : '' }} class="bgx-checkbox-category bgx-checkbox-category-{{$deviceCategory->id}}" name="deviceCategory[]" value="{{$deviceCategory->id}}" onclick="getDeviceCateGoryInput({{Auth::user()->id}},{{$deviceCategory->id}})">
                        </div>
                      </div>
                      @endif
                      @endforeach
                    </div>
                  </div>
                  @foreach($getDeviceCategory as $key => $category)
                  @if(in_array($category->id,$deviceCategoryArr))

                  <div class="device-category-fields card device-category-block-{{$category->id}}">
                    <div class="card-title">
                      <h4>{{ CommonHelper::getDeviceCategoryName($category->id) }}</h4>
                    </div>
                    <div class="card-details">
                      @php
                      $inputs = json_decode($category->inputs, true);
                      $totalInputs = count($inputs);
                      $inputIds = collect($inputs)->pluck('id')->toArray();
                      $dataFields = DataFields::whereIn('id', $inputIds)->get()->keyBy('id');
                      $enhancedInputs = collect($inputs)->map(function ($input) use ($dataFields) {
                      $input['validationConfig'] = $dataFields[$input['id']]->validationConfig ?? null;
                      return $input;
                      });
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
                      @endphp
                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label for="templates<?= $category->id ?>" class="control-label col-lg-3">
                              Templates <span class="require">*</span>
                            </label>
                            <div class="col-lg-8">
                              <select class="userAccType form-control "
                                id="templates<?= $category->id ?>"
                                name="configuration[<?= $category->id ?>][template]"
                                onchange="changeTemplate(<?= $category->id ?>)">
                                <?php if (!empty($templates)): ?>
                                  <?php foreach ($templates as $temp): ?>
                                    <option value="<?= $temp['id'] ?>"
                                      <?= $temp['default_template'] == 1 ? 'selected' : '' ?>>
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
                      @if(isset($input['key']))
                      @php

                      // Check if $configurations is defined and has the current index
                      $config = json_decode($currentUser['configurations'],true);
                      $configurationValue = isset($configurations[$key]) ? $configurations[$key]: null;
                      @endphp
                      @if($index % 2 === 0)
                      <div class="row">
                        @endif
                        @if(isset($input['key']))
                        <div class="col-lg-6">
                          <input class="form-control inputType" type="hidden" placeholder="Enter {{$input['key']}}" name="idParameters[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" value="{{$input['id']}}" />
                          @php
                          $validationConfig = json_decode($input['validationConfig'],true);
                          @endphp
                          @if ($input['type'] == 'select')

                          <div class="form-group">
                            <label class="control-label col-lg-3">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}</label>
                            <div class="col-lg-8">
                              <select class="form-control inputType" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" {{ $input['requiredFieldInput'] ? 'required' : '' }}>
                                <!-- <option value="">Please Select</option> -->
                                @foreach($validationConfig['selectOptions'] as $configkey => $option)
                                <option value="{{ $validationConfig['selectValues'][$configkey] }}" {{ $configurationValue && strtolower($validationConfig['selectValues'][$configkey]) == $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] ? 'selected' : '' }}>{{ $option }}</option>
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
                              var $select = $("#configval{{$category->id}}");

                              $select.select2({
                                placeholder: "Select up to " + <?= $validationConfig['maxSelectValue'] ?> + " options",
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
                                value="{{ 
                                    isset($input['key']) && isset($configurationValue) 
                                        ? (is_array($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value']) 
                                            ? json_encode($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value']) 
                                            : $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value']) 
                                        : '' 
                                }}"

                                {{ $input['requiredFieldInput'] ? 'required' : '' }}>
                            </div>

                          </div>
                          @endif
                        </div>
                        @endif
                        @if ($index % 2 === 1 || $index === $totalInputs - 1)
                      </div>
                      @endif
                      @endif
                      @endforeach
                      @if( $category->is_can_protocol == 1 )
                      <div class="row">
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
                                          <select id="can_channel{{$category->id}}" name="canConfiguration[{{$category->id}}][can_channel]">
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
                                      </div>
                                      <div id="dynamicCanFields{{$category->id}}"></div>
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
                    </div>
                  </div>
                  @endif

                  <input type="hidden" name="configuration[{{$category->id}}][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['ping_interval']['value'])  ? $configurationValue['ping_interval']['value'] : 4 }}" />
                  <input type="hidden" name="configuration[{{$category->id}}][is_editable]" class="form-control inputType" placeholder="Is Editable" value="{{ isset($configurationValue) && isset($configurationValue['is_editable']['value'])  ? $configurationValue['is_editable']['value'] : 1 }}" />
                  @endforeach

                  @endif
                  <div id="deviceCategoryInputFields"></div>
                  <div class="form-group">
                    <div class="bgx-save-button col-lg-11">
                      <button class="btn btn-primary btn-flat" type="submit">Save</button>
                    </div>
                  </div>
                </form>
                <hr>
              </div><!--/.c_content-->
            </div><!--/.c_panels-->
          </div>
        </div>
        <!--======== Form Validation Content Start End ========-->
    </section>
  </section>

  <!--======== Main Content End ========-->
  @stop
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <script type="text/javascript">
    // if ({{Auth::user()->user_type == 'Reseller'}}) {
    let existingCheckedValues = <?php Auth::user()->user_type == 'Reseller' ? json_encode($deviceCategoryArr) : [] ?>

    // }
    $(document).ready(function() {
      $('#userType').change(function() {
        let val = $(this).val();
        let support = $('.is-support-active');
        if (val == 'Support') {
          support.show();
          let html = `<div class="form-group">
            <label for="curl" class="control-label col-lg-3">Configuration Edit Permission</label>
            <div class="col-lg-6"  style="position: absolute;left: 4%;">
              <input type="checkbox" class="form-control" name="is_support_active" style="height:20px;">
            </div>
          </div>`;
          support.append(html)
        } else {
          support.hide();
          support.append('')
        }
      });
      $('#can_channel').select2({
        placeholder: "Search and Select",
      });
      $('#can_protocol').select2({
        placeholder: "Search and Select",
      });

      $('#container').on('input', '.inputType', function() {
        var value = $(this).val();
        $(this).val(value.replace(/\s/g, '')); // Remove all spaces
      });
      $('#commentForm').submit(function(event) {
        $('.error_msg').empty().hide();
        event.preventDefault();

        let error_msg = "";
        let formIsValid = true;

        $('#cmobile').on('input', function() {
          var maxLength = 10;
          if ($(this).val().length > maxLength) {
            error_msg = 'phone number should be ' + minVal;
            formIsValid = false;
            return false;
          }
        });
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
          let actionUrl = "<?php echo url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/register/writer'); ?>";
          let formData = $(this).serialize();

          $.ajax({
            url: actionUrl,
            type: "POST",
            data: formData,
            success: function(response) {
              let result = JSON.parse(response);
              $('.success_msg').append(result.success).show();

              document.documentElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
              });
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

    function getDeviceCateGoryInput(userId = '', deviceCategoryId = '') {
      let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-multiple-categories') }}";
      const userType = "{{ Auth::user()->user_type }}";
      const isChecked = $(`input.bgx-checkbox-category[value="${deviceCategoryId}"]`).is(':checked');
      let newCheckedValues = []
      if (userType == 'Reseller') {
        if (!isChecked) {
          existingCheckedValues = Array.isArray(existingCheckedValues) ? existingCheckedValues : [];

          existingCheckedValues = existingCheckedValues.filter(val => val != deviceCategoryId);
          $(`.device-category-block-${deviceCategoryId}`).remove(); // Remove section
        } else {
          const selector = `input.bgx-checkbox-category-${deviceCategoryId}[value='${deviceCategoryId}']:checked`;
          let $elements = $(selector);
          let checkedValues = $elements.map(function() {
            return this.value;
          }).get();
          existingCheckedValues = Array.isArray(existingCheckedValues) ? existingCheckedValues : [];

          let newCheckedValues = existingCheckedValues.length > 0 ?
            checkedValues.filter(val => !existingCheckedValues.includes(val)) :
            checkedValues;
          $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
              ids: newCheckedValues
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
                inputFields.forEach((data, index) => {
                  let input = JSON.parse(data.inputs);
                  htmlContent += '<div class="device-category-fields device-category-block-' + deviceCategoryId + ' card">';
                  htmlContent += '<div class="card-title"><h4 >' + data.device_category_name + '</h4></div>';
                  htmlContent += '<div class="card-details">';
                  htmlContent += '<div class="row">';
                  htmlContent += '<div class="col-lg-6">';
                  htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Templates <span class="require">*</span></label><div class="col-lg-8"><select class="form-control userAccType" id="templates' + index + '" name="configuration[' + index + '][template]" class="select2" onchange="changeTemplate(' + index + ')">';
                  if (templates[index].length > 0) {
                    templates[index].forEach((temp) => {
                      if (temp.default_template == 1) {
                        changeTemplate(index, temp.id)
                      }
                      htmlContent += '<option ' + (temp.default_template == 1 ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + ' ' + (temp.default_template == 1 ? ' (Default)' : '') + '</option>';
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
                    htmlContent += '<input class="form-control inputType" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                    if (input.type == 'select') {
                      htmlContent += '<div class="form-group">';
                      htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                      htmlContent += '<div class="col-lg-8">';
                      htmlContent += '<select class="form-control inputType" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']["value"]" ' + (input.requiredFieldInput ? '' : '') + '>';
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
                      htmlContent += '<select class="inputType" id="configval' + index + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? '' : '') + ' multiple>';
                      // htmlContent += '<option value="">Please Select</option>';

                      validation?.selectOptions.forEach((option, optIndex) => {
                        htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                      });

                      htmlContent += '</select>';
                      htmlContent += '</div>';
                      htmlContent += '</div>';
                      setTimeout(() => {
                        $('#configval' + index).select2({
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
                        htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                        htmlContent += '</div>';
                        htmlContent += '</div>';
                      } else {

                        let addClassTextArray = input?.type === 'text_array' ? 'text-array-space' : '';
                        let addClassIpUrl = input?.type === 'IP/URL' ? 'ip-url-space' : '';
                        htmlContent += '<div class="form-group">';
                        htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        htmlContent += '<div class="col-lg-8">';
                        htmlContent += '<input class="form-control inputType ' + addClassTextArray + ' ' + addClassIpUrl + '" type="' +
                          (input.type === 'number' ? 'number' : 'text') + '" ' +
                          (input.type === 'number' && validation?.numberInput ?
                            'min="' + validation.numberInput.min + '" max="' + validation.numberInput.max + '" ' :
                            '') +
                          (input.type !== 'number' && validation?.maxValueInput ? 'maxlength="' + validation.maxValueInput + '"' : '') +
                          'placeholder="Enter ' + input.key + '" ' +
                          'name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
                          (input.requiredFieldInput ?
                            'required' :
                            '') +
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
                  htmlContent += '<div class="row">';
                  htmlContent += '<div class="col-lg-6"><div class="form-group">';
                  htmlContent += '<label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<input type="number" name="configuration[' + index + '][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value=""/>';
                  htmlContent += '</div></div></div>';
                  htmlContent += '<div class="col-lg-6">';
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>';
                  htmlContent += '<div class="col-lg-6">';
                  htmlContent += '<label class="padding-10">Enable</label><input checked type="radio" name="configuration[' + index + '][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>';
                  htmlContent += '<label class="padding-10">Disable</label><input type="radio" name="configuration[' + index + '][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>';
                  htmlContent += '</div></div></div>';
                  htmlContent += '</div>';
                  htmlContent += '</div></div></div>';
                  // Close device-category-fields
                });

                $('#deviceCategoryInputFields').html(htmlContent);
              } else {
                $('#deviceCategoryInputFields').html('<p>No input fields found.</p>');
                alert(result.message);
              }
            },
            error: function(xhr) {
              console.log(xhr.responseText); // Handle error  
            },
            complete: function() {
              $('#loading').hide(); // Hide loading indicator regardless of success or error
            }
          });
        }
      } else {

        var checkedValues = $('.bgx-checkbox-category:checked').map(function() {
          return this.value;
        }).get();
        newCheckedValues = checkedValues;
        if (checkedValues.length === 0) {
          $('#deviceCategoryInputFields').html('');
          return;
        }
        $.ajax({
          url: actionUrl,
          type: "POST",
          data: {
            ids: newCheckedValues
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
              inputFields.forEach((data, index) => {
                let input = JSON.parse(data.inputs);
                let canEnable = data.is_can_protocol == 1 ? true : false;
                // if (canEnable) {
                //   $('.isCanEnable' + index).show();
                // }
                htmlContent += '<div class="device-category-fields card">';
                htmlContent += '<div class="card-title"><h4 >' + data.device_category_name + '</h4></div>';
                htmlContent += '<div class="card-details">';
                htmlContent += '<div class="row">';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Templates <span class="require">*</span></label><div class="col-lg-8"><select class="form-control userAccType" id="templates' + index + '" name="configuration[' + index + '][template]" class="select2" onchange="changeTemplate(' + index + ')">';
                if (templates[index].length > 0) {
                  templates[index].forEach((temp) => {
                    if (temp.default_template == 1) {
                      changeTemplate(index, temp.id)
                    }
                    htmlContent += '<option ' + (temp.default_template == 1 ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + ' ' + (temp.default_template == 1 ? ' (Default)' : '') + '</option>';
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
                  htmlContent += '<input class="form-control inputType" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                  if (input.type == 'select') {
                    htmlContent += '<div class="form-group">';
                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div class="col-lg-8">';
                    htmlContent += '<select class="form-control inputType" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']["value"]" ' + (input.requiredFieldInput ? '' : '') + '>';
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
                    htmlContent += '<select class="inputType" id="configval' + index + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? '' : '') + ' multiple>';
                    // htmlContent += '<option value="">Please Select</option>';

                    validation?.selectOptions.forEach((option, optIndex) => {
                      htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                    });

                    htmlContent += '</select>';
                    htmlContent += '</div>';
                    htmlContent += '</div>';
                    setTimeout(() => {
                      $(document).ready(function() {
                        var $select = $("#configval" + index);

                        $select.select2({
                          placeholder: "Select up to 3 options",
                          width: "100%"
                        });

                        $select.on("change", function() {
                          var selected = $(this).select2("val");

                          if (selected && selected.length > validation.maxSelectValue) {
                            // Remove the last selected item
                            selected.splice(validation.maxSelectValue);
                            $(this).select2("val", selected);
                            alert("You can only select up to " + validation.maxSelectValue + " options.");
                          }
                        });
                      });
                    }, 100);
                  } else {
                    if (input.key == 'Password') {
                      htmlContent += '<div class="form-group">';
                      htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                      htmlContent += '<div class="col-lg-8">';
                      htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
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
                        (input.type !== 'number' && validation?.maxValueInput ? 'maxlength="' + validation.maxValueInput + '"' : '') +
                        'placeholder="Enter ' + input.key + '" ' +
                        'name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
                        (input.requiredFieldInput ?
                          'required' :
                          '') +
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
                htmlContent += '<div class="row">';
                htmlContent += '<div class="col-lg-6"><div class="form-group">';
                htmlContent += '<label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label>';
                htmlContent += '<div class="col-lg-8">';
                htmlContent += '<input type="number" name="configuration[' + index + '][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value=""/>';
                htmlContent += '</div></div></div>';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group">';
                htmlContent += '<label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<label class="padding-10">Enable</label><input checked type="radio" name="configuration[' + index + '][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>';
                htmlContent += '<label class="padding-10">Disable</label><input type="radio" name="configuration[' + index + '][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>';
                htmlContent += '</div></div></div>';
                if (canEnable) {
                  htmlContent += `
                <div class="isCanEnable` + index + `" style="padding: 0px 25px;">
                    <label for="canConfigurationArr" class="control-label" required>
                        CAN Configuration <span class="require">*</span>
                    </label>
                    <div class="col-lg-12 padding-1">
                        <input type="text" class="form-control" name="canConfigurationArr[${index}]" id="canConfigurationArr${index}" value="" readonly />
                        <div class="col-sm-12 alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                        <button type="button" class="btn btn-primary" onclick="openCanModal(` + index + `)">
                            Configure CAN Protocol
                        </button>
                    </div>
                </div>`;
                  htmlContent += `
                    <div class="modal" id="canModal` + index + `" aria-hidden="true">
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
                                <form id="canForm">
                                  <!-- Protocol Selection -->
                                  <div class="form-group isCanEnable">
                                    <div style="margin:10px 0px;">
                                      <label for="curl" class="control-label padding-left-3">Can Channel<span class="require">*</span></label>
                                      <select class="form-control" id="can_channel${index}" name="canConfiguration[${index}][can_channel]">
                                          <option value="">-- Select CAN Channel --</option>
                                          <option value="1">CAN 1</option>
                                          <option value="2">CAN 2</option>
                                          <option value="3">CAN 3</option>
                                          <option value="4">CAN 4</option>
                                        </select>
                                    </div>
                                      <div style="margin:10px 0px;">
                                        <label class="control-label">Can Baud Rate <span class="require">*</span></label>
                                        <select id="can_baud_rate${index}" name="canConfiguration[${index}][can_baud_rate]" class="form-control">
                                          <option value="">-- Select Baud Rate --</option>
                                          <option value="500">500 kbps</option>
                                          <option value="250">250 kbps</option>
                                        </select>
                                      </div>
                                      <div style="margin:10px 0px;">
                                        <label class="control-label">Can ID Type <span class="require">*</span></label>
                                        <select id="can_id_type${index}" name="canConfiguration[${index}][can_id_type]" class="form-control">
                                          <option value="">-- Select Can ID --</option>
                                          <option value="0">Standard</option>
                                          <option value="1">Extended</option>
                                        </select>
                                      </div>
                                      <div style="margin:10px 0px;">
                                        <label for="can_protocol" class="control-label padding-left-3">
                                          CAN Protocol <span class="require">*</span>
                                        </label>
                                        <select id="can_protocol${index}" name="canConfiguration[${index}][can_protocol]" class="form-control" onchange="selectedCanProtocol(${index})">
                                          <option value="">Select Protocol</option>
                                          <option value="1">J1979</option>
                                          <option value="2">J1939</option>
                                          <option value="3">Custom CAN</option>
                                        </select>
                                      </div>
                                  </div>

                                  <div id="dynamicCanFields${index}"></div>
                                </form>
                              </div>
                              <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-success mt-4" onclick="generateJSON(${index})">Submit</button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  `;

                }
                htmlContent += '</div>';
                htmlContent += '</div></div></div>'; // Close device-category-fields
              });

              $('#deviceCategoryInputFields').html(htmlContent);
            } else {
              $('#deviceCategoryInputFields').html('<p>No input fields found.</p>');
              alert(result.message);
            }
          },
          error: function(xhr) {
            console.log(xhr.responseText); // Handle error  
          },
          complete: function() {
            $('#loading').hide(); // Hide loading indicator regardless of success or error
          }
        });

      }
    }
    $(document).ready(function() {
      //$('#templates0').select2();


    });

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
            let validation = {};
            try {
              validation = JSON.parse(field.validationConfig || '{}');
            } catch (e) {
              console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
            }
            let inputHtml = `<input type="hidden" name="idCanParameters[${index}][${fieldId}]" value="${field.id}" />`;
            inputHtml += `<input type="hidden" name="CanParametersType[${index}][${fieldId}]" value="${inputType}" />`;
            let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control"  placeholder="Enter ${field.fieldName}"`;

            if (inputType === 'number') {
              if (validation.numberInput) {
                attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
              }
              inputHtml += `<input type="number" ${attr} />`;
            } else if (inputType === 'select') {
              inputHtml += `<select ${attr}>`;
              if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                validation.selectOptions.forEach(option => {
                  inputHtml += `<option value="${option}">${option}</option>`;
                });
              } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                Object.entries(validation.selectOptions).forEach(([key, value]) => {
                  inputHtml += `<option value="${key}">${value}</option>`;
                });
              } else {
                inputHtml += `<option value="">-- Select --</option>`;
              }
              inputHtml += `</select>`;
            } else if (inputType == 'multiselect') {
              inputHtml += `<select ${attr} multiple >`;

              if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                validation.selectOptions.forEach((option, key) => {
                  inputHtml += `<option value="${validation.selectValues[key]}">${option}</option>`;
                });
              } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                Object.entries(validation.selectOptions).forEach(([key, value]) => {
                  inputHtml += `<option value="${key}">${value}</option>`;
                });
              } else {
                inputHtml += `<option value="">-- Select --</option>`;
              }

              inputHtml += `</select>`;

              // Apply Select2
              setTimeout(() => {
                var $select = $('#' + fieldId);
                if ($select.length) {
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
                }
              }, 100);
            } else if (inputType === 'text_array') {
              console.log(validation);
              let values = [""];
              let maxValue = validation.maxValueInput || 0;
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
                console.log("maxValue ==>", maxValue);
                wrapper.on("click", ".add-text-input", function() {
                  const count = wrapper.find(".text-array-item").length;
                  if (maxValue && count >= maxValue) {
                    alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
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
                });

                // 🗑 Remove input
                wrapper.on("click", ".remove-text-input", function() {
                  $(this).closest(".text-array-item").remove();
                  updateHiddenValue();
                });

                // ✍ Update hidden field on input change
                wrapper.on("input", "input[type=text]", function() {
                  updateHiddenValue();
                });

                // 🔁 Keep hidden field updated
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
              inputHtml += `<input type="text" ${attr1} />`;

            } else {
              // default to text input
              if (validation.maxValueInput) {
                attr += ` maxlength="${validation.maxValueInput}"`;
              }
              inputHtml += `<input type="text" ${attr} />`;
            }

            html += `<div class="col-md-12 padding-3 padding-top-10">
                    <div class="form-group" id="modalInput">
                        <label for="${fieldId}" class="control-label padding-left-14" required>
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

    function generateJSON(index) {
      let canConfigData = {};

      $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
        let fieldId = $(this).attr('id'); // Or extract from name if needed
        let value = $(this).val();
        // Special handling for can_protocol
        if (fieldId == `can_protocol${index}`) {
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
          // Check if id and value are not empty/null
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
        $('#canModal' + index).modal('hide');
      });
    }

    function openCanModal(index) {
      $('#canModal' + index).modal('show');
    }

    function changeTemplate(index, id = '') {
      let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-template') }}";
      if (id == '') {
        var templateId = $('#templates' + index).val();
      } else {
        var templateId = id;
      }
      $.ajax({
        url: actionUrl,
        type: "POST",
        data: {
          id: templateId
        },
        success: function(response) {
          let result = JSON.parse(response);

          if (result.status == 200) {
            let template = JSON.parse(result.template);
            Object.keys(template)
              .filter((key) => key !== 'template')
              .forEach(function(key) {
                let element = $(`input[name='configuration[${index}][${key}]'], select[name='configuration[${index}][${key}]']`);

                if (element.is('input') || element.is('select')) {
                  element.val(template[key]['value']);
                }
              });
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