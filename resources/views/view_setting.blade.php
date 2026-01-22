<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;

$configurations = json_decode($template_info['configurations'], true);
$getCanEnableByDeviceCategory = DeviceCategory::select('is_can_protocol')->where('id', $template_info['device_category_id'])->first();
// dd($configurations);
$canConfigurations = json_decode($template_info['can_configurations'], true);
// dd($configurations);
// dd($configurations['ping_interval']);
?>

@extends('layouts.apps')

@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Setting</a></li>
                        <li><a href="/{{$url_type}}/view-template">View Settings</a></li>
                        <li class="active"><a href="#">View Configurations</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="container bgx-custom-page">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white header-custom">
                                    <h4>Template Information and Configurations</h4>
                                </div>
                                <div class="card-body body-custom">
                                    {{-- Display User Information --}}
                                    <div class="user-info mb-4">
                                        <div class='col-lg-9'>
                                            <h5><b>Template Information:</b></h5>
                                        </div>
                                        <div class='row  bgx-configurations'>
                                            <div class="view-template-configuration">
                                                <div class='col-lg-5'>
                                                    <div class="bgx-table-container">
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell"><strong>Name:</strong> {{ $template_info['template_name'] }}</div>
                                                            <div class="bgx-table-cell"><strong>Device Category:</strong> {{ CommonHelper::getDeviceCategoryName($template_info['device_category_id']) }}</div>
                                                        </div>
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell"><strong>Created at:</strong> {{ CommonHelper::getDateAsTimeZone($template_info['created_at']) ?? 'N/A' }}</div>
                                                            <div class="bgx-table-cell"><strong>Last Edit:</strong> {{ CommonHelper::getDateAsTimeZone($template_info['updated_at']) ?? 'N/A'  }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-7' style='display: grid;justify-content: center;'>
                                                    <div id="span2" class="btn {{ $template_info['default_template'] == 1 ? 'btn-success active' : 'btn-danger' }}" style="margin: 3px 0px;">
                                                        Default Template - {{ $template_info['default_template'] == 1 ? 'Yes' : 'No' }}
                                                    </div>

                                                    @if(Auth::user()->user_type == "Admin")
                                                    <div id="span2" class="btn {{isset($configurations['ping_interval']['value']) ? 'btn-warning active' : 'btn-danger' }}" style="margin: 3px 0px;">
                                                        Ping interval - {{ isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : '0' }}
                                                    </div>

                                                    <div id="span2" class="btn {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'btn-info active' : 'btn-danger' }}" style="margin: 3px 0px;">
                                                        Editable- {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'Yes' : 'No' }}
                                                    </div>
                                                    @endif

                                                </div>
                                            </div>
                                            <div class="form-template-configuration" style="display:none;">
                                                <form class="validator form-horizontal " id="updateDeviceInfoConfiguration" method="post" action="/{{$url_type}}/update-template-info-configurations/{{$template_info['id']}}">
                                                    @method('PATCH')
                                                    @csrf
                                                    <div class="form-group "></div>
                                                    @if(Auth::user()->user_type!='Support')
                                                    <div class="form-group ">
                                                        <label for="curl" class="control-label col-lg-3"><b>Mark as Default Template</b></label>
                                                        <div class="col-lg-6">
                                                            <input type="checkbox" name="default_template" id="default_template" {{ $template_info['default_template'] == 1 ? 'checked' : '' }} style="    width: 40px;height: 25px">
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="form-group ">
                                                        <label for="curl" class="control-label col-lg-3">Name (optional)<span class="require">*</span></label>
                                                        <div class="col-lg-6">
                                                            <input class="form-control" placeholder="Enter Device Name" id="name" type="text" name="template_name" value="{{ $template_info['template_name']}}">
                                                        </div>
                                                    </div>
                                                    @if(Auth::user()->user_type=='Admin')
                                                    <div class="form-group ">
                                                        <label for="curl" class="control-label col-lg-3">Ping interval <span class="require">*</span></label>
                                                        <div class="col-lg-6">
                                                            <input class="form-control" placeholder="Enter Ping Interval" id="ping_interval" type="Number" name="configuration[ping_interval]" value="{{isset($configurations['ping_interval']['value']) ? $configurations['ping_interval']['value'] :''}}" onkeypress="return blockSpecialCharTransmission(event)" required />
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="curl" class="control-label col-lg-3">
                                                            Template Edit Permission <span class="require">*</span>
                                                        </label>
                                                        <div class="col-lg-6">
                                                            <label>Enable</label>
                                                            <input
                                                                {{ isset($configurations['is_editable']['value']) && $configurations['is_editable']['value'] == '1' ? 'checked' : '' }}
                                                                type="radio"
                                                                name="configuration[is_editable]"
                                                                value="1"
                                                                style="height:20px; width:20px; vertical-align: middle;"
                                                                required>
                                                            <label>Disable</label>
                                                            <input
                                                                {{ isset($configurations['is_editable']['value']) && $configurations['is_editable']['value'] == '0' ? 'checked' : '' }}
                                                                type="radio"
                                                                name="configuration[is_editable]"
                                                                value="0"
                                                                style="height:20px; width:20px; vertical-align: middle;"
                                                                required>
                                                        </div>
                                                    </div>

                                                    @endif
                                                    <div class="col-sm-12 bg-margin-top text-right">
                                                        <input type="hidden" id="device_id" name="device_id" value="{{$template_info['id']}}">
                                                        <button type="submit" class="btn btn-primary updateDeviceName">Save</button>
                                                        <button type="button" class="btn btn-secondary cancel-template-info-btn" data-key="0">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                            @if(Auth::user()->user_type != "Support")
                                            @if(isset($configurations['is_editable']['value']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                            <div class="row mt-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="button" class="btn btn-primary edit-template-btn" onclick="toggleEditTemplate()">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                            @else
                                            @if(Auth::user()->is_support_active)
                                            <div class="row mt-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="user-info ">
                                        <h5><b>Template Configurations:</b></h5>
                                        @empty($template_info['configurations'])
                                        <p class="col-md-12">No configurations found.</p>
                                        @else
                                        @php
                                        $categoryIds = explode(',',$template_info['device_category_id']);

                                        $deviceCategories= DeviceCategory::select('*')->whereIn('id', $categoryIds)->first();
                                        $input = json_decode($deviceCategories->inputs,true);

                                        @endphp
                                        <div class="row d-flex">
                                            <div class="col-lg-12 mb-4">
                                                <div class="configuration-item">
                                                    <h6><b>{{ CommonHelper::getDeviceCategoryName($template_info['device_category_id']) }}</b></h6>
                                                    <div class="bgx-configurations">
                                                        <div id="config">
                                                            <div class='row d-flex'>
                                                                <div class="col-lg-9">
                                                                    <p><strong>Firmware:</strong>
                                                                        {{ isset($configurations['firmware_id']) 
                                                                            ? CommonHelper::getFirmwareName($configurations['firmware_id']['value']) 
                                                                            : 'No firmware available' }}
                                                                    </p>
                                                                    @foreach ($input as $field => $value)
                                                                    <p>
                                                                        <strong>{{ $value['key'] }}:</strong>
                                                                        @php
                                                                        $key = strtolower(str_replace(' ', '_', $value['key']));
                                                                        $configValue = $configurations[$key]['value'] ?? '';
                                                                        @endphp

                                                                        {{ isset($configurations[$key]) 
                                                                        ? (is_array($configValue) 
                                                                            ? json_encode($configValue) 
                                                                            : CommonHelper::getDeviceCategoryValue($value['key'], $configValue)) 
                                                                        : '' }}
                                                                    </p>


                                                                    @endforeach
                                                                    <!--<p><strong>Ping Interval:</strong>-->
                                                                    <!--{{ isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : 0 }}-->
                                                                    <!--</p>-->
                                                                </div>
                                                                @if(Auth::user()->user_type != "Support")
                                                                @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                                                <div class="col-lg-3">
                                                                    <button type="button" class="btn btn-primary edit-btn" onclick="toggleEdit()"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                @endif
                                                                @else
                                                                @if(Auth::user()->is_support_active)
                                                                <div class="row mt-3">
                                                                    <div class="col-lg-12 text-center">
                                                                        <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <!--{{$url_type}}-->
                                                        <div id="form" style="display: none;">
                                                            <form action="/{{$url_type}}/update-template-configurations/{{$template_info['id']}}" method="POST">
                                                                @csrf
                                                                <div class='row'>
                                                                    <div class='col-sm-12 bgx-form-fields'>
                                                                        <?php echo CommonHelper::getSettingConfigurationInput($template_info['device_category_id'], $configurations);
                                                                        ?>
                                                                    </div>
                                                                    <div class='col-sm-12 bg-margin-top text-right'>
                                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                                        <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endempty
                                    </div>
                                    @if($getCanEnableByDeviceCategory->is_can_protocol == 1)
                                    <div class="user-info">
                                        <h4><b>CAN Protocol Configurations</b></h4>
                                        @empty($template_info['can_configurations'])

                                        @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        @else
                                        <?php echo CommonHelper::getCanProtocolTempConfigurationInput($template_info['device_category_id'], 0, $canConfigurations, $url_type, $template_info); ?>
                                        @if(Auth::user()->user_type != "Support")
                                        @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        @else
                                        @if(Auth::user()->is_support_active)
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        @endif
                                        @endempty
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</section>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function toggleEditTemplate() {
        $('.edit-template-btn').hide();
        $('.view-template-configuration').hide();
        $('.form-template-configuration').show();
    }

    function canConfigToggleEdit(key) {
        $('.edit-config-btn').hide();
        $('#canConfig-0').hide();
        $('#canConfigForm-0').show();
    }


    // Add event listeners to all edit buttons
    function toggleEdit(key) {
        // Toggle between display and form
        $('#config').hide();
        $('#form').show();
    }

    $(document).ready(function() {
        $('.templates').each(function() {
            // Get the ID of each element
            var id = $(this).attr('id');
            // ids.push(id);
            $('#' + id).select2({
                'placeholder': 'Select and Search '
            })
        });
        $('.cancel-template-info-btn').click(function() {
            $('.edit-template-btn').show();
            $('.view-template-configuration').show();
            $('.form-template-configuration').hide();
        });
        $('.cancel-config-btn').click(function() {
            var key = $(this).data('key');
            $('.edit-config-btn').show();
            $('#canConfig-0').show();
            $('#canConfigForm-0').hide();
        });
        // Add event listeners to all cancel buttons
        $('.cancel-btn').click(function() {
            var key = $(this).data('key');

            // Toggle between display and form
            $('#config').show();
            $('#form').hide();
        });
    });
</script>