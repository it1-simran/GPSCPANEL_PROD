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
  
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/add-user.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/add-user.css')) }}">
@endpush
@section('content')
  <!--main content start-->
  
  <section id="main-content">
    <section class="wrapper">
      {{-- BREADCRUMB --}}
      <div class="au-breadcrumb-wrap">
        <nav class="au-breadcrumb">
          <div class="bc-home"><i class="fa fa-home"></i></div>
          <a href="{{ url('admin') }}" class="bc-item">Home</a>
          <span class="bc-sep">›</span>
          <a href="#" class="bc-item">Account Management</a>
          <span class="bc-sep">›</span>
          <span class="bc-item active">Add Account</span>
        </nav>
      </div>
      <!--======== Form Validation Content Start End ========-->
      <div class="row">
        <div class="col-md-12">
          <!--=========== START TAGS INPUT ===========-->
          <div class="c_panel" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); border: none; overflow: hidden; margin-bottom: 30px;">
            <div class="c_title" style="padding: 24px 30px; border-bottom: none; background-color: #76CF1C;">
              <h2 style="font-weight: 700; color: #ffffff; font-size: 20px; margin: 0;">Add Account</h2>
              <div class="clearfix"></div>
            </div><!--/.c_title-->
            <div class="c_content" style="padding: 30px;">
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
                <form class="validator" id="commentForm" method="post" action="#" onsubmit="return false">
                  @csrf
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="userType" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Account Type <span style="color: #ef4444;">*</span></label>
                        <select class="form-control" id="userType" name="user_type" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px;">
                          <option value="Reseller">Manufacturer</option>
                          <option selected="selected" value="User">Dealer</option>
                          @if($currentUser->user_type =='Admin')
                          <option value="Support">Support</option>
                          @endif
                        </select>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="cname" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Name <span style="color: #ef4444;">*</span></label>
                        <input class="form-control" placeholder="Enter Name" id="cname" name="name" type="text" required style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" />
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="cmobile" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Mobile <span style="color: #ef4444;">*</span></label>
                        <input class="form-control" placeholder="Enter Mobile Number" id="cmobile" type="text" name="mobile" maxlength="10" required style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" />
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="cemail" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">E-Mail <span style="color: #ef4444;">*</span></label>
                        <input class="form-control" placeholder="Enter E-Mail" id="cemail" type="email" name="email" required style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" />
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="timezone" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">TimeZones <span style="color: #ef4444;">*</span></label>
                        <select name="timezone" class="form-control" id="timezone" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569; width: 100%; cursor: pointer; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px;">
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

                    <div class="col-md-6">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="password" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Login Password <span style="color: #ef4444;">*</span></label>
                        <input class="form-control" placeholder="Enter Login password" type="password" id="password" name="password" required style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" />
                      </div>
                    </div>
                  </div>
                  <div class="is-support-active" style="display: none;"></div>
                  @if($currentUser->user_type =='Admin')

                  <div class="form-group" style="margin-top: 10px; margin-bottom: 30px;">
                    <label style="font-weight: 700; color: #334155; font-size: 16px; margin-bottom: 16px; display: block; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Device Categories <span style="color: #ef4444;">*</span></label>
                    <div class="row" style="margin-top: 16px;">
                      @foreach($getDeviceCategory as $deviceCategory)
                      <div class="col-md-3 col-sm-6" style="margin-bottom: 15px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s ease;">
                          <label class="bgx-label-category" style="margin: 0; font-weight: 600; color: #475569; cursor: pointer;">{{$deviceCategory->device_category_name}}</label>
                          <input type="checkbox" class="bgx-checkbox-category " name="deviceCategory[]" value="{{$deviceCategory->id}}" onclick="getDeviceCateGoryInput()" style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                        </div>
                      </div>
                      @endforeach
                    </div>
                  </div>
                  @endif
                  @if($currentUser->user_type == 'Reseller')
                  <div class="form-group" style="margin-top: 10px; margin-bottom: 30px;">
                    <label style="font-weight: 700; color: #334155; font-size: 16px; margin-bottom: 16px; display: block; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">Device Categories <span style="color: #ef4444;">*</span></label>
                    <div class="row" style="margin-top: 16px;">
                      @foreach($getDeviceCategory as $deviceCategory)
                      @if(in_array($deviceCategory->id,$deviceCategoryArr))
                      <div class="col-md-3 col-sm-6" style="margin-bottom: 15px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s ease;">
                          <label class="bgx-label-category" style="margin: 0; font-weight: 600; color: #475569; cursor: pointer;">{{$deviceCategory->device_category_name}}</label>
                          <input type="checkbox" {{ in_array($deviceCategory->id, $deviceCategoryArr) ? 'checked' : '' }} class="bgx-checkbox-category bgx-checkbox-category-{{$deviceCategory->id}}" name="deviceCategory[]" value="{{$deviceCategory->id}}" onclick="getDeviceCateGoryInput({{Auth::user()->id}},{{$deviceCategory->id}})" style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                        </div>
                      </div>
                      @endif
                      @endforeach
                    </div>
                  </div>
                  @foreach($getDeviceCategory as $key => $category)
                  @if(in_array($category->id,$deviceCategoryArr))

                  <div class="device-category-fields card device-category-block-{{$category->id}}" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 30px; overflow: hidden; background: #ffffff;">
                    <div class="card-title" style="background-color: #76CF1C; border-bottom: 1px solid #76CF1C; padding: 16px 24px; margin: 0;">
                      <h4 style="margin: 0; font-weight: 700; color: #ffffff; font-size: 16px;">{{ CommonHelper::getDeviceCategoryName($category->id) }} Configuration</h4>
                    </div>
                    <div class="card-details" style="padding: 24px;">
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
                        <div class="col-md-6">
                          <div class="form-group" style="margin-bottom: 24px;">
                            <label for="templates<?= $category->id ?>" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">
                              Templates <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="userAccType form-control"
                                id="templates<?= $category->id ?>"
                                name="configuration[<?= $category->id ?>][template]"
                                onchange="changeTemplate(<?= $category->id ?>)"
                                style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;">
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
                        <div class="col-md-6">
                            @php
                            $firmwares = \DB::table('firmware')->where('device_category_id', $category->id)->get();
                            @endphp
                          <div class="form-group" style="margin-bottom: 24px;">
                            <label for="firmware<?= $category->id ?>" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">
                              Firmware <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-control"
                                id="firmware<?= $category->id ?>"
                                data-index="<?= $category->id ?>"
                                data-category="<?= $category->id ?>"
                                data-category-name="{{ CommonHelper::getDeviceCategoryName($category->id) }}"
                                name="configuration[<?= $category->id ?>][firmware_id]"
                                onchange="changeFirmware(<?= $category->id ?>)"
                                <?= $firmwares->isEmpty() ? 'disabled' : '' ?>
                                style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;">
                                @if($firmwares->isEmpty())
                                    <option value="">Not Found</option>
                                @else
                                    @foreach($firmwares as $firmware)
                                    <option value="{{ $firmware->id }}" {{ $firmware->is_default == 1 ? 'selected' : '' }}>
                                      {{ $firmware->name }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group" style="margin-bottom: 24px;">
                            <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Model Name <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="form-control" name="configuration[<?= $category->id ?>][modelName]" id="modelName<?= $category->id ?>" readonly style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569; background-color: #f8fafc;" />
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group" style="margin-bottom: 24px;">
                            <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Vendor ID <span style="color: #ef4444;">*</span></label>
                            <input type="text" class="form-control" name="configuration[<?= $category->id ?>][vendorId]" id="vendorId<?= $category->id ?>" readonly style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569; background-color: #f8fafc;" />
                          </div>
                        </div>
                      </div>
                        <script>
                          $(document).ready(function() {
                            setTimeout(function() {
                              changeTemplate(<?= $category->id ?>);
                              changeFirmware(<?= $category->id ?>);
                            }, 500);
                          });
                        </script>
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
                            <input class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" type="hidden" placeholder="Enter {{$input['key']}}" name="idParameters[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" value="{{$input['id']}}" />
                            @php
                            $validationConfig = json_decode($input['validationConfig'],true);
                            @endphp
                            @if ($input['type'] == 'select')

                            <div class="form-group" style="margin-bottom: 24px;">
                              <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span style="color: #ef4444;">*</span>' : '' !!}</label>
                              <select class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" {{ $input['requiredFieldInput'] ? 'required' : '' }} style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;">
                                <!-- <option value="">Please Select</option> -->
                                @foreach($validationConfig['selectOptions'] as $configkey => $option)
                                <option value="{{ $validationConfig['selectValues'][$configkey] }}" {{ $configurationValue && strtolower($validationConfig['selectValues'][$configkey]) == $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                              </select>
                            </div>
                            @elseif ($input['type'] == 'multiselect')
                            @php
                            $validationConfig = json_decode($input['validationConfig'],true);
                            @endphp
                            <div class="form-group" style="margin-bottom: 24px;">
                              <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">
                                {{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span style="color: #ef4444;">*</span>' : '' !!}
                              </label>
                              <select class="inputType" id="configval{{$category->id}}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}][]" multiple {{ $input['requiredFieldInput'] ? 'required' : '' }} style="width: 100%;">
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
                            <div class="form-group" style="margin-bottom: 24px;">
                              @php
                              $addClassTextArray = isset($input['type']) && $input['type'] == 'text' ? "text-array-space": '';
                              $addClassIpUrl = isset($input['type']) && $input['type'] == 'IP/URL' ? "ip-url-space" : '';
                              @endphp

                              <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span style="color: #ef4444;">*</span>' : '' !!}</label>
                              @php
                              $configKey = str_replace(' ', '_', strtolower($input['key']));
                              $configVal = '';
                              if (isset($configurationValue) && isset($configurationValue[$configKey]) && array_key_exists('value', $configurationValue[$configKey])) {
                              $configVal = is_array($configurationValue[$configKey]['value'])
                              ? json_encode($configurationValue[$configKey]['value'])
                              : $configurationValue[$configKey]['value'];
                              }
                              @endphp
                              <input class="form-control {{$addClassTextArray}} {{$addClassIpUrl}}" type="{{ $input['type'] == 'number' ? 'number' : 'text' }}"
                                {!! $input['type']=='number' ? 'min="' . ($input['numberRange']['min'] ?? '' ) . '" max="' . ($input['numberRange']['max'] ?? '' ) . '"' : '' !!}
                                placeholder="Enter {{ isset($input['key']) ? $input['key'] :''  }}" name="configuration[{{ $category->id }}][{{ $configKey }}]"
                                value="{{ $configVal }}"
                                style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;"
                                {{ $input['requiredFieldInput'] ? 'required' : '' }}>
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
                          <div class="isCanEnable{{$category->id}}" style="padding: 0px 15px; margin-bottom: 24px;">
                            <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">
                              CAN Configuration <span style="color: #ef4444;">*</span>
                            </label>
                            <div>
                              @php
                              $value = isset($canConfigurations[$category->id] ) ?$canConfigurations[$category->id]: [];
                              $result = is_array($value) ? json_encode($value) : $value;
                              @endphp
                              <input type="text" class="form-control" name="canConfigurationArr[{{$category->id}}]" id="canConfigurationArr{{$category->id}}" value="{{$result}}" readonly style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; width: 100%; font-size: 14px; color: #475569; background-color: #f8fafc; margin-bottom: 16px;" />
                              <button type="button" class="btn btn-primary" onclick="openCanModal('{{ $category->id }}')" style="height: 44px; border-radius: 6px; padding: 0 20px; font-weight: 600; background-color: #76CF1C; border-color: #76CF1C; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fa fa-cogs"></i> Configure CAN Protocol
                              </button>
                            </div>
                            <div class="alert alert-danger modelName_error" role="alert" style="display: none; margin-top: 8px;"></div>
                          </div>
                          <div class="modal" id="canModal{{$category->id}}">
                            <div class="modal-dialog" style="max-width: 750px; width: 100%;">
                              <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                <div class="modal-header" style="background-color: #76CF1C; border-radius: 8px 8px 0 0; border-bottom: none; padding: 20px 24px;">
                                  <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1; text-shadow: none; font-size: 20px; margin-top: -2px;">
                                    <i class="fa fa-times"></i>
                                  </button>
                                  <h4 class="modal-title" style="font-weight: 700; color: white; font-size: 16px;">
                                    <i class="fa fa-cog" style="margin-right: 8px;"></i> CAN PROTOCOL CONFIGURATION
                                  </h4>
                                </div>
                                <div class="modal-body" style="background-color: #ffffff; padding: 30px;">
                                  <div id="canForm">
                                    <div class="row isCanEnable">
                                      <div class="col-md-6">
                                        <div class="form-group" style="margin-bottom: 24px;">
                                          <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">Can Channel <span style="color: #ef4444;">*</span></label>
                                          <select id="can_channel{{$category->id}}" name="canConfiguration[{{$category->id}}][can_channel]" class="form-control" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                            <option value="">-- Select CAN Channel --</option>
                                            <option value="1">CAN 1</option>
                                            <option value="2">CAN 2</option>
                                            <option value="3">CAN 3</option>
                                            <option value="4">CAN 4</option>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group" style="margin-bottom: 24px;">
                                          <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">Can Baud Rate <span style="color: #ef4444;">*</span></label>
                                          <select id="can_baud_rate{{$category->id}}" name="canConfiguration[{{$category->id}}][can_baud_rate]" class="form-control" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                            <option value="">-- Select Baud Rate --</option>
                                            <option value="500">500 kbps</option>
                                            <option value="250">250 kbps</option>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group" style="margin-bottom: 24px;">
                                          <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">Can ID Type <span style="color: #ef4444;">*</span></label>
                                          <select id="can_id_type{{$category->id}}" name="canConfiguration[{{$category->id}}][can_id_type]" class="form-control" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                            <option value="">-- Select Can ID --</option>
                                            <option value="0">Standard</option>
                                            <option value="1">Extended</option>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group" style="margin-bottom: 24px;">
                                          <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">
                                            CAN Protocol <span style="color: #ef4444;">*</span>
                                          </label>
                                          <select id="can_protocol{{$category->id}}" name="canConfiguration[{{$category->id}}][can_protocol]" class="form-control" onchange="selectedCanProtocol('{{$category->id}}')" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                            <option value="">-- Select Protocol --</option>
                                            <option value="1">J1979</option>
                                            <option value="2">J1939</option>
                                            <option value="3">Custom CAN</option>
                                          </select>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="row" id="dynamicCanFields{{$category->id}}"></div>
                                  </div>
                                </div>
                                <div class="modal-footer" style="border-top: 1px solid #f1f5f9; background-color: #ffffff; border-radius: 0 0 8px 8px; padding: 20px 30px;">
                                  <button type="button" class="btn btn-default" data-dismiss="modal" style="background-color: #e2e8f0; color: #475569; border: none; border-radius: 6px; padding: 10px 24px; font-weight: 600; font-size: 14px;">
                                    <i class="fa fa-times" style="margin-right: 6px;"></i> Cancel
                                  </button>
                                  <button type="button" class="btn btn-success" onclick="generateJSON('{{$category->id}}')" style="background-color: #76CF1C; border-color: #76CF1C; border-radius: 6px; padding: 10px 24px; font-weight: 600; font-size: 14px; margin-left: 12px; box-shadow: none;">
                                    <i class="fa fa-check" style="margin-right: 6px;"></i> Save Configuration
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        @endif
                      </div>
                    </div>
                    @endif

                    <input type="hidden" name="configuration[{{$category->id}}][ping_interval]" class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['ping_interval']['value'])  ? $configurationValue['ping_interval']['value'] : 4 }}" />
                    <input type="hidden" name="configuration[{{$category->id}}][is_editable]" class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" placeholder="Is Editable" value="{{ isset($configurationValue) && isset($configurationValue['is_editable']['value'])  ? $configurationValue['is_editable']['value'] : 1 }}" />
                    @endforeach

                    @endif
                    <div id="deviceCategoryInputFields"></div>
                    <div class="form-group" style="margin-top: 30px; text-align: right; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                      <button class="btn btn-primary" type="submit" style="background-color: #76CF1C; border-color: #76CF1C; border-radius: 6px; padding: 12px 30px; font-weight: 700; font-size: 16px; box-shadow: 0 4px 6px rgba(118, 207, 28, 0.2); transition: all 0.2s ease;">
                        <i class="fa fa-save" style="margin-right: 8px;"></i> Save Account
                      </button>
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
          let html = `<div class="form-group" style="margin-bottom: 24px; padding: 0 15px;">
            <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Configuration Edit Permission</label>
            <input type="checkbox" class="form-control" name="is_support_active" style="width: 24px; height: 24px; box-shadow: none; cursor: pointer;">
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
              ids: newCheckedValues,
              userId: "{{ Auth::user()->id }}"
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
                let authDeviceCategories = ("{{ Auth::user()->device_category_id }}" || "").split(',');
                inputFields.forEach((data, index) => {
                  let input = JSON.parse(data.inputs);
                  
                  let myConfigIndex = authDeviceCategories.indexOf(data.id.toString());
                  let myConfig = null;
                  if (myConfigIndex !== -1 && result.configurations) {
                      let parsedConfigs = JSON.parse(result.configurations);
                      if (parsedConfigs && parsedConfigs[myConfigIndex]) {
                          myConfig = parsedConfigs[myConfigIndex];
                      }
                  }

                  htmlContent += '<div class="device-category-fields device-category-block-' + deviceCategoryId + ' card" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 30px; overflow: hidden; background: #ffffff;">';
                  htmlContent += '<div class="card-title" style="background-color: #76CF1C; border-bottom: 1px solid #76CF1C; padding: 16px 24px; margin: 0;"><h4 style="margin: 0; font-weight: 700; color: #ffffff; font-size: 16px;">' + data.device_category_name + ' Configuration</h4></div>';
                  htmlContent += '<div class="card-details" style="padding: 24px;">';
                  htmlContent += '<div class="row">';
                  htmlContent += '<div class="col-lg-6">';
                  htmlContent += '<div class="form-group" style="margin-bottom: 24px;"><label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Templates <span class="require">*</span></label><div><select class="form-control userAccType" id="templates' + index + '" name="configuration[' + index + '][template]" class="select2" onchange="changeTemplate(' + index + ')">';
                  if (templates[index].length > 0) {
                    let hasDefault = false;
                    templates[index].forEach((temp) => {
                      if (temp.default_template == 1) {
                        hasDefault = true;
                        defaultTemplatesToTrigger.push({
                          index: index,
                          id: temp.id
                        });
                      }
                      htmlContent += '<option ' + (temp.default_template == 1 ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + ' ' + (temp.default_template == 1 ? ' (Default)' : '') + '</option>';
                    });
                    if (!hasDefault) {
                        defaultTemplatesToTrigger.push({
                            index: index,
                            id: templates[index][0].id
                        });
                    }
                  }
                  // htmlContent += '<option>No Template Found</option>';
                  htmlContent += '</select></div></div></div></div>';

                  input.forEach((input, index1) => {
                    let validation = JSON.parse(input.validationConfig);
                    let configVal = input.default || '';
                    if (myConfig && myConfig[input.key.replace(/\s+/g, '_').toLowerCase()]) {
                        configVal = myConfig[input.key.replace(/\s+/g, '_').toLowerCase()].value || '';
                    }

                    if (index1 % 2 === 0) {
                      htmlContent += '<div class="row">';
                    }
                    htmlContent += '<div class="col-lg-6">';
                    htmlContent += '<input class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                    if (input.type == 'select') {
                      htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                      htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                      htmlContent += '<div>';
                      htmlContent += '<select class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? '' : '') + '>';
                      // htmlContent += '<option value="">Please Select</option>';

                      validation?.selectOptions.forEach((option, optIndex) => {
                        htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                      });

                      htmlContent += '</select>';
                      htmlContent += '</div>';
                      htmlContent += '</div>';
                    } else if (input.type == 'multiselect') {
                      htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                      htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                      htmlContent += '<div>';
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
                        htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                        htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        htmlContent += '<div>';
                        htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                        htmlContent += '</div>';
                        htmlContent += '</div>';
                      } else {

                        let addClassTextArray = input?.type === 'text_array' ? 'text-array-space' : '';
                        let addClassIpUrl = input?.type === 'IP/URL' ? 'ip-url-space' : '';
                        htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                        htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        htmlContent += '<div>';
                        htmlContent += '<input class="form-control inputType ' + addClassTextArray + ' ' + addClassIpUrl + '" type="' +
                          (input.type === 'number' ? 'number' : 'text') + '" ' +
                          (input.type === 'number' && validation?.numberInput ?
                            'min="' + validation.numberInput.min + '" max="' + validation.numberInput.max + '" ' :
                            '') +
                          (input.type !== 'number' && validation?.maxValueInput ? 'maxlength="' + validation.maxValueInput + '"' : '') +
                          'placeholder="Enter ' + input.key + '" ' +
                          'name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
                          'value="' + configVal + '" ' +
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
                  htmlContent += '<div class="col-lg-6"><div class="form-group" style="margin-bottom: 24px;">';
                  htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Ping Interval <span class="require">*</span></label>';
                  htmlContent += '<div>';
                  htmlContent += '<input type="number" name="configuration[' + index + '][ping_interval]" class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" placeholder="Ping Interval" value=""/>';
                  htmlContent += '</div></div></div>';
                  htmlContent += '<div class="col-lg-6">';
                  htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                  htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Device Edit Permission<span class="require">*</span></label>';
                  htmlContent += '<div style="display: flex; gap: 16px; align-items: center; margin-top: 8px;">';
                  htmlContent += '<label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #475569; cursor: pointer;"><input checked type="radio" name="configuration[' + index + '][is_editable]" value="1" style="width: 18px; height: 18px; margin: 0; cursor: pointer; accent-color: #76CF1C;" required> Enable</label>';
                  htmlContent += '<label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #475569; cursor: pointer;"><input type="radio" name="configuration[' + index + '][is_editable]" value="0" style="width: 18px; height: 18px; margin: 0; cursor: pointer; accent-color: #76CF1C;" required> Disable</label>';
                  htmlContent += '</div></div></div>';
                  htmlContent += '</div>';
                  htmlContent += '</div></div></div>';
                  // Close device-category-fields
                });

                $('#deviceCategoryInputFields').html(htmlContent);

                setTimeout(() => {
                  if (typeof defaultTemplatesToTrigger !== 'undefined') {
                    defaultTemplatesToTrigger.forEach(task => {
                      changeTemplate(task.index, task.id);
                    });
                  }
                }, 300);
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
            ids: newCheckedValues,
            userId: "{{ Auth::user()->id }}"
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
              let authDeviceCategories = ("{{ Auth::user()->device_category_id }}" || "").split(',');
              inputFields.forEach((data, index) => {
                let input = JSON.parse(data.inputs);
                
                let myConfigIndex = authDeviceCategories.indexOf(data.id.toString());
                let myConfig = null;
                if (myConfigIndex !== -1 && result.configurations) {
                    let parsedConfigs = JSON.parse(result.configurations);
                    if (parsedConfigs && parsedConfigs[myConfigIndex]) {
                        myConfig = parsedConfigs[myConfigIndex];
                    }
                }

                let canEnable = data.is_can_protocol == 1 ? true : false;
                htmlContent += '<div class="device-category-fields card" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 30px; overflow: hidden; background: #ffffff;">';
                htmlContent += '<div class="card-title" style="background-color: #76CF1C; border-bottom: 1px solid #76CF1C; padding: 16px 24px; margin: 0;"><h4 style="margin: 0; font-weight: 700; color: #ffffff; font-size: 16px;">' + data.device_category_name + ' Configuration</h4></div>';
                htmlContent += '<div class="card-details" style="padding: 24px;">';
                htmlContent += '<div class="row">';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group" style="margin-bottom: 24px;"><label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Templates <span class="require">*</span></label><div><select class="form-control userAccType" id="templates' + index + '" name="configuration[' + index + '][template]" class="select2" onchange="changeTemplate(' + index + ')">';
                if (templates[index].length > 0) {
                  let hasDefault = false;
                  templates[index].forEach((temp) => {
                    if (temp.default_template == 1 || temp.is_default == 1) {
                      hasDefault = true;
                      defaultTemplatesToTrigger.push({
                        index: index,
                        id: temp.id
                      });
                    }
                    htmlContent += '<option ' + ((temp.default_template == 1 || temp.is_default == 1) ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + ' ' + ((temp.default_template == 1 || temp.is_default == 1) ? ' (Default)' : '') + '</option>';
                  });
                  if (!hasDefault) {
                      defaultTemplatesToTrigger.push({
                          index: index,
                          id: templates[index][0].id
                      });
                  }
                }
                htmlContent += '</select></div></div></div>';

                // Add Firmware selection
                let firmwares = JSON.parse(result.firmware);
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group" style="margin-bottom: 24px;"><label for="firmware' + index + '" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Firmware <span class="require">*</span></label><div><select class="form-control firmware-select" id="firmware' + index + '" data-index="' + index + '" data-category="' + data.id + '" data-category-name="' + data.device_category_name + '" name="configuration[' + index + '][firmware_id]" onchange="changeFirmware(' + index + ')" ' + ((!firmwares[index] || firmwares[index].length === 0) ? 'disabled' : '') + ' style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;">';
                if (firmwares[index] && firmwares[index].length > 0) {
                  firmwares[index].forEach((firmware) => {
                    htmlContent += '<option ' + (firmware.is_default == 1 ? "selected" : "") + ' value="' + firmware.id + '">' + firmware.name + '</option>';
                  });
                } else {
                  htmlContent += '<option value="">Not Found</option>';
                }
                htmlContent += '</select></div></div></div></div>';

                // Add Model and Vendor fields
                htmlContent += '<div class="row">';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group" style="margin-bottom: 24px;"><label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Model Name <span class="require">*</span></label><div><input type="text" class="form-control" name="configuration[' + index + '][modelName]" id="modelName' + index + '" readonly /></div></div></div>';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group" style="margin-bottom: 24px;"><label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Vendor ID <span class="require">*</span></label><div><input type="text" class="form-control" name="configuration[' + index + '][vendorId]" id="vendorId' + index + '" readonly /></div></div></div>';
                htmlContent += '</div>';

                input.forEach((input, index1) => {
                  let validation = JSON.parse(input.validationConfig);
                  let configVal = input.default || '';
                  if (myConfig && myConfig[input.key.replace(/\s+/g, '_').toLowerCase()]) {
                      configVal = myConfig[input.key.replace(/\s+/g, '_').toLowerCase()].value || '';
                  }

                  if (index1 % 2 === 0) {
                    htmlContent += '<div class="row">';
                  }
                  htmlContent += '<div class="col-lg-6">';
                  htmlContent += '<input class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                  if (input.type == 'select') {
                    htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                    htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div>';
                    htmlContent += '<select class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? '' : '') + '>';
                    // htmlContent += '<option value="">Please Select</option>';

                    validation?.selectOptions.forEach((option, optIndex) => {
                      let isSelected = (validation?.selectValues[optIndex] == configVal) ? 'selected' : '';
                      htmlContent += '<option ' + isSelected + ' value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                    });

                    htmlContent += '</select>';
                    htmlContent += '</div>';
                    htmlContent += '</div>';
                  } else if (input.type == 'multiselect') {
                    htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                    htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div>';
                    htmlContent += '<select class="inputType" id="configval' + index + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? '' : '') + ' multiple>';
                    // htmlContent += '<option value="">Please Select</option>';

                    validation?.selectOptions.forEach((option, optIndex) => {
                      let isSelected = (validation?.selectValues[optIndex] == input.default) ? 'selected' : '';
                      htmlContent += '<option ' + isSelected + ' value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
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
                      htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                      htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                      htmlContent += '<div>';
                      htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + (input.default || '') + '" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                      htmlContent += '</div>';
                      htmlContent += '</div>';
                    } else {
                      let addClassTextArray = input?.type === 'text_array' ? 'text-array-space' : '';
                      let addClassIpUrl = input?.type === 'IP/URL' ? 'ip-url-space' : '';
                      htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                      htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                      htmlContent += '<div>';
                      // htmlContent += '<input class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'min="' + validation?.numberInput?.min + '" max="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '" maxlength="' + validation?.maxValueInput') + '>';
                      htmlContent +=
                        '<input class="form-control inputType ' + addClassTextArray + ' ' + addClassIpUrl + '" type="' +
                        (input.type === 'number' ? 'number' : 'text') + '" ' +
                        (input.type === 'number' && validation?.numberInput ?
                          'min="' + validation.numberInput.min + '" max="' + validation.numberInput.max + '" ' :
                          '') +
                        (input.type !== 'number' && validation?.maxValueInput ? 'maxlength="' + validation.maxValueInput + '"' : '') +
                        'placeholder="Enter ' + input.key + '" ' +
                        'name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
                        'value="' + configVal + '" ' +
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
                htmlContent += '<div class="col-lg-6"><div class="form-group" style="margin-bottom: 24px;">';
                htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Ping Interval <span class="require">*</span></label>';
                htmlContent += '<div>';
                htmlContent += '<input type="number" name="configuration[' + index + '][ping_interval]" class="form-control inputType" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; font-size: 14px; color: #475569;" placeholder="Ping Interval" value=""/>';
                htmlContent += '</div></div></div>';
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<div class="form-group" style="margin-bottom: 24px;">';
                htmlContent += '<label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">Device Edit Permission<span class="require">*</span></label>';
                htmlContent += '<div style="display: flex; gap: 16px; align-items: center; margin-top: 8px;">';
                htmlContent += '<label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #475569; cursor: pointer;"><input checked type="radio" name="configuration[' + index + '][is_editable]" value="1" style="width: 18px; height: 18px; margin: 0; cursor: pointer; accent-color: #76CF1C;" required> Enable</label>';
                htmlContent += '<label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #475569; cursor: pointer;"><input type="radio" name="configuration[' + index + '][is_editable]" value="0" style="width: 18px; height: 18px; margin: 0; cursor: pointer; accent-color: #76CF1C;" required> Disable</label>';
                htmlContent += '</div></div></div>';
                if (canEnable) {
                  htmlContent += `
                <div class="col-lg-12 isCanEnable` + index + `" style="padding: 0px 15px; margin-bottom: 24px;">
                    <label style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 8px; display: block;">
                        CAN Configuration <span style="color: #ef4444;">*</span>
                    </label>
                    <div>
                        <input type="text" class="form-control" name="canConfigurationArr[${index}]" id="canConfigurationArr${index}" value="" readonly style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; width: 100%; font-size: 14px; color: #475569; background-color: #f8fafc; margin-bottom: 16px;" />
                        <button type="button" class="btn btn-primary" onclick="openCanModal(` + index + `)" style="height: 44px; border-radius: 6px; padding: 0 20px; font-weight: 600; background-color: #76CF1C; border-color: #76CF1C; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa fa-cogs"></i> Configure CAN Protocol
                        </button>
                    </div>
                    <div class="alert alert-danger modelName_error" role="alert" style="display: none; margin-top: 8px;"></div>
                </div>`;
                  htmlContent += `
                    <div class="modal" id="canModal` + index + `" aria-hidden="true">
                      <div class="modal-dialog" style="max-width: 750px; width: 100%;">
                        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                          <div class="modal-header" style="background-color: #76CF1C; border-radius: 8px 8px 0 0; border-bottom: none; padding: 20px 24px;">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: white; opacity: 1; text-shadow: none; font-size: 20px; margin-top: -2px;">
                              <i class="fa fa-times"></i>
                            </button>
                            <h4 class="modal-title" style="font-weight: 700; color: white; font-size: 16px;">
                              <i class="fa fa-cog" style="margin-right: 8px;"></i> CAN PROTOCOL CONFIGURATION
                            </h4>
                          </div>
                          <div class="modal-body" style="background-color: #ffffff; padding: 30px;">
                            <form id="canForm">
                              <div class="row isCanEnable">
                                <div class="col-md-6">
                                  <div class="form-group" style="margin-bottom: 24px;">
                                    <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">Can Channel <span style="color: #ef4444;">*</span></label>
                                    <select class="form-control" id="can_channel${index}" name="canConfiguration[${index}][can_channel]" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                        <option value="">-- Select CAN Channel --</option>
                                        <option value="1">CAN 1</option>
                                        <option value="2">CAN 2</option>
                                        <option value="3">CAN 3</option>
                                        <option value="4">CAN 4</option>
                                      </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group" style="margin-bottom: 24px;">
                                    <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">Can Baud Rate <span style="color: #ef4444;">*</span></label>
                                    <select id="can_baud_rate${index}" name="canConfiguration[${index}][can_baud_rate]" class="form-control" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                      <option value="">-- Select Baud Rate --</option>
                                      <option value="500">500 kbps</option>
                                      <option value="250">250 kbps</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group" style="margin-bottom: 24px;">
                                    <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">Can ID Type <span style="color: #ef4444;">*</span></label>
                                    <select id="can_id_type${index}" name="canConfiguration[${index}][can_id_type]" class="form-control" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                      <option value="">-- Select Can ID --</option>
                                      <option value="0">Standard</option>
                                      <option value="1">Extended</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group" style="margin-bottom: 24px;">
                                    <label class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;">
                                      CAN Protocol <span style="color: #ef4444;">*</span>
                                    </label>
                                    <select id="can_protocol${index}" name="canConfiguration[${index}][can_protocol]" class="form-control" onchange="selectedCanProtocol(${index})" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 44px; box-shadow: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px; font-size: 14px; color: #64748b;">
                                      <option value="">-- Select Protocol --</option>
                                      <option value="1">J1979</option>
                                      <option value="2">J1939</option>
                                      <option value="3">Custom CAN</option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                              <div class="row" id="dynamicCanFields${index}"></div>
                            </form>
                          </div>
                          <div class="modal-footer" style="border-top: 1px solid #f1f5f9; background-color: #ffffff; border-radius: 0 0 8px 8px; padding: 20px 30px;">
                            <button type="button" class="btn btn-default" data-dismiss="modal" style="background-color: #e2e8f0; color: #475569; border: none; border-radius: 6px; padding: 10px 24px; font-weight: 600; font-size: 14px;">
                              <i class="fa fa-times" style="margin-right: 6px;"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-success" onclick="generateJSON(${index})" style="background-color: #76CF1C; border-color: #76CF1C; border-radius: 6px; padding: 10px 24px; font-weight: 600; font-size: 14px; margin-left: 12px; box-shadow: none;">
                              <i class="fa fa-check" style="margin-right: 6px;"></i> Save Configuration
                            </button>
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

              setTimeout(() => {
                if (typeof defaultTemplatesToTrigger !== 'undefined') {
                  defaultTemplatesToTrigger.forEach(task => {
                    changeTemplate(task.index, task.id);
                    changeFirmware(task.index);
                  });
                }
              }, 300);
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

    function changeFirmware(index) {
      let firmwareSelect = $('#firmware' + index);
      let firmwareId = firmwareSelect.val();
      let categoryId = firmwareSelect.data('category');
      let userId = ''; // Add Account always has empty user at creation

      if (firmwareId && categoryId) {
        checkUserModalNameExist(index, userId, firmwareId, categoryId);
      } else {
        $('#modelName' + index).val(firmwareSelect.data('category-name') || 'N/A');
        $('#vendorId' + index).val("JSD");
      }
    }

    function checkUserModalNameExist(index, userId, firmwareId, categoryId) {
      let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-model-name') }}";
      $.ajax({
        url: actionUrl,
        type: "POST",
        data: {
          user_id: userId,
          firmware_id: firmwareId,
          category_id: categoryId,
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          let result = (typeof response === 'string') ? JSON.parse(response) : response;
          if (result.status == 200 && result.modal) {
            $('#modelName' + index).val(result.modal.name);
            $('#vendorId' + index).val(result.modal.vendorId);
          } else {
            // Default to JSD for unassigned/no model
            $('#modelName' + index).val($('#firmware' + index).data('category-name') || 'N/A');
            $('#vendorId' + index).val("JSD");
          }
        },
        error: function() {
          $('#modelName' + index).val($('#firmware' + index).data('category-name') || 'N/A');
          $('#vendorId' + index).val("JSD");
        }
      });
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
            let inputHeight = (inputType === 'text_array' || inputType === 'multiselect') ? '' : 'height: 44px;';
            let appearanceCSS = (inputType === 'select') ? "appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; padding-right: 36px;" : "";
            let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control" placeholder="Enter ${field.fieldName}" style="border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: none; font-size: 14px; color: #64748b; width: 100%; ${inputHeight} ${appearanceCSS}"`;

            if (inputType === 'number') {
              if (validation.numberInput) {
                attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
              }
              inputHtml += `<input type="number" ${attr} />`;
            } else if (inputType === 'select') {
              inputHtml += `<select ${attr}><option value="">-- Select ${field.fieldName} --</option>`;
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
              inputHtml += `<select id="${fieldId}" name="canConfiguration[${index}][${fieldId}][]" class="form-control can-multiselect" multiple style="border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: none; font-size: 14px; color: #64748b; width: 100%; min-height: 44px;">`;

              if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                const vals = Array.isArray(validation.selectValues) ? validation.selectValues : [];
                validation.selectOptions.forEach((option, key) => {
                  const optVal = vals[key] !== undefined ? vals[key] : option;
                  inputHtml += `<option value="${optVal}">${option}</option>`;
                });
              } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                Object.entries(validation.selectOptions).forEach(([key, value]) => {
                  inputHtml += `<option value="${key}">${value}</option>`;
                });
              } else {
                inputHtml += `<option value="">-- No options available --</option>`;
              }

              inputHtml += `</select>`;

              // Apply Select2 after DOM is ready
              setTimeout(() => {
                var $select = $('#' + fieldId);
                if ($select.length && typeof $.fn.select2 === 'function') {
                  $select.select2({
                    placeholder: 'Select options',
                    width: '100%',
                    dropdownParent: $('#canModal' + index),
                    allowClear: true
                  });
                  const maxSel = validation.maxSelectValue || 0;
                  if (maxSel) {
                    $select.on('change', function() {
                      const selected = $(this).val() || [];
                      if (selected.length > maxSel) {
                        selected.splice(maxSel);
                        $(this).val(selected).trigger('change.select2');
                        alert('You can only select up to ' + maxSel + ' options.');
                      }
                    });
                  }
                }
              }, 150);
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

            // All dynamic fields rendered full width
            let gridCol = 'col-md-12';
            
            html += `<div class="${gridCol}">
                      <div class="form-group" style="margin-bottom: 24px;">
                        <label for="${fieldId}" class="control-label" style="font-weight: 700; color: #334155; font-size: 14px; margin-bottom: 10px;" required>
                            ${field.fieldName} <span style="color: #ef4444;">*</span>
                        </label>
                        ${inputHtml}
                        <div class="alert alert-danger ${fieldId}_error" role="alert" style="display:none; margin-top: 5px; padding: 5px 10px;"></div>
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
        $('#templates' + index).val(id).trigger('change');
      }
      $.ajax({
        url: actionUrl,
        type: "POST",
        data: {
          id: templateId,
          _token: "{{ csrf_token() }}"
        },
        success: function(response) {
          console.log("Template Fetch Success for Index " + index + ":", response);
          let result;
          try {
            result = (typeof response === 'string') ? JSON.parse(response) : response;
          } catch (e) {
            console.error("Failed to parse response JSON", e, response);
            return;
          }

          if (result.status == 200) {
            let templateData = result.template;
            let template = templateData;
            
            try {
                let unwrapCount = 0;
                while (typeof template === 'string' && unwrapCount < 5) {
                    try { template = JSON.parse(template); unwrapCount++; } catch (e) { break; }
                }
            } catch (e) {
                console.error("Failed to parse template configurations", e, templateData);
                return;
            }

            if (Array.isArray(template) && template.length > 0) {
                template = template[0];
            }

            if (template && typeof template === 'object') {
                if (template.configurations) {
                    let configData = template.configurations;
                    let cCount = 0;
                    while (typeof configData === 'string' && cCount < 5) {
                        try { configData = JSON.parse(configData); cCount++; } catch (e) { break; }
                    }
                    template = typeof configData === 'object' && configData !== null ? configData : template;
                }

                console.log("Applying template configurations:", template);
                Object.keys(template).filter((key) => key !== 'template').forEach(function(key) {
                    let rawVal = template[key];
                    let val = rawVal;
                    if (rawVal && typeof rawVal === 'object' && 'value' in rawVal) {
                        val = rawVal.value;
                    }

                    // Normalize the key from template
                    let normKey = key.toLowerCase().replace(/\s+/g, '_').replace(/_\(sec\)$/, '').replace(/_sec$/, '').replace(/[^a-z0-9]/g, '');

                    // Broad recursive lookup for fields starting with configuration[index]
                    $('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name && name.startsWith(`configuration[${index}]`)) {
                            let matches = name.match(/\[([^\]]+)\]$/) || name.match(/\[([^\]]+)\]\[\]$/);
                            
                            if (matches && matches[1]) {
                                let fieldPart = matches[1];
                                let normFieldPart = fieldPart.toLowerCase().replace(/\s+/g, '_').replace(/_\(sec\)$/, '').replace(/_sec$/, '').replace(/[^a-z0-9]/g, '');

                                if (normFieldPart === normKey || fieldPart.toLowerCase() === key.toLowerCase() || fieldPart.toLowerCase().replace(/\s+/g, '_') === key.toLowerCase().replace(/\s+/g, '_')) {
                                    if ($(this).is(':radio') || $(this).is(':checkbox')) {
                                        if ($(this).val() == val) {
                                            $(this).prop('checked', true);
                                        }
                                    } else {
                                        let finalVal = val;
                                        if ($(this).attr('multiple')) {
                                            if (typeof val === 'string') {
                                                try {
                                                    let cleanStr = val.startsWith('{') && val.endsWith('}') ? '[' + val.substring(1, val.length - 1) + ']' : val;
                                                    finalVal = JSON.parse(cleanStr);
                                                } catch(e) {
                                                    finalVal = val.split(',');
                                                }
                                            } else if (!Array.isArray(val) && val != null) {
                                                finalVal = [val];
                                            }
                                        }
                                        
                                        if (finalVal !== undefined && finalVal !== null && finalVal !== "") {
                                            $(this).val(finalVal);
                                            if ($(this).is('select')) {
                                                $(this).trigger('change');
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            } else {
                console.warn("Template data is empty or invalid structure", template);
            }
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
  @stop
