<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;

$configurations = json_decode($template_info['configurations'], true) ?: [];
$getCanEnableByDeviceCategory = !empty($template_info['device_category_id'])
    ? DeviceCategory::select('is_can_protocol')->where('id', $template_info['device_category_id'])->first()
    : null;
// dd($configurations);
$canConfigurations = json_decode($template_info['can_configurations'], true);
// dd($configurations);
// dd($configurations['ping_interval']);
?>

@extends('layouts.apps')

@section('content')
<section id="main-content" class="view-template-config-page">
    <section class="wrapper">
        <div class="protocol-breadcrumb-wrap">
            <nav class="protocol-breadcrumb" aria-label="Breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ url($url_type . '/view-template') }}" class="bc-item">Settings</a>
                <span class="bc-sep">›</span>
                <a href="{{ url($url_type . '/view-template') }}" class="bc-item">View Settings</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">View Configurations</span>
            </nav>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel vtc-top-panel">
                    <div class="c_title">
                        <h2 class="vtc-main-title"><i class="fa fa-file-text-o"></i> Template Information</h2>
                    </div>
                    <div class="c_content vtc-info-body">
                                    {{-- Display User Information --}}
                                    <div class="user-info mb-0">
                                        <div class='row bgx-configurations vtc-info-layout'>
                                            <div class="view-template-configuration vtc-view-block row">
                                                <div class='col-lg-6 col-md-12 vtc-meta-col'>
                                                    <dl class="vtc-dl">
                                                        <div class="vtc-dl-row">
                                                            <dt>Name</dt>
                                                            <dd>{{ $template_info['template_name'] }}</dd>
                                                        </div>
                                                        <div class="vtc-dl-row">
                                                            <dt>Device Category</dt>
                                                            <dd>{{ CommonHelper::getDeviceCategoryName($template_info['device_category_id']) }}</dd>
                                                        </div>
                                                        <div class="vtc-dl-row">
                                                            <dt>Created at</dt>
                                                            <dd class="vtc-date">{{ CommonHelper::getDateAsTimeZone($template_info['created_at']) ?? 'N/A' }}</dd>
                                                        </div>
                                                        <div class="vtc-dl-row">
                                                            <dt>Last Edit</dt>
                                                            <dd class="vtc-date">{{ CommonHelper::getDateAsTimeZone($template_info['updated_at']) ?? 'N/A' }}</dd>
                                                        </div>
                                                    </dl>
                                                </div>
                                                <div class='col-lg-6 col-md-12 vtc-badges-col'>
                                                    <div class="vtc-badge-stack">
                                                    <span class="vtc-pill vtc-pill-default {{ $template_info['default_template'] == 1 ? 'is-yes' : 'is-no' }}">
                                                        <span class="vtc-pill-glyph"><i class="fa {{ $template_info['default_template'] == 1 ? 'fa-check' : 'fa-times' }}"></i></span>
                                                        Default Template — {{ $template_info['default_template'] == 1 ? 'Yes' : 'No' }}
                                                    </span>

                                                    @if(Auth::user()->user_type == "Admin")
                                                    <span class="vtc-pill vtc-pill-ping {{ isset($configurations['ping_interval']['value']) ? 'is-on' : '' }}">
                                                        <span class="vtc-pill-glyph"><i class="fa fa-wifi"></i></span>
                                                        Ping interval — {{ isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : '0' }}
                                                    </span>

                                                    <span class="vtc-pill vtc-pill-edit {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'is-yes' : 'is-no' }}">
                                                        <span class="vtc-pill-glyph"><i class="fa fa-pencil"></i></span>
                                                        Editable — {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'Yes' : 'No' }}
                                                    </span>
                                                    @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-template-configuration" style="display:none;">
                                                <form class="validator form-horizontal " id="updateDeviceInfoConfiguration" method="post" action="{{ url($url_type . '/update-template-info-configurations/' . $template_info['id']) }}">
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
                    </div>
                </div>
                <div class="c_panel vtc-config-panel">
                    <div class="c_title">
                        <h2 class="vtc-main-title"><i class="fa fa-sliders"></i> Template Configurations</h2>
                    </div>
                    <div class="c_content vtc-config-body">
                                    <div class="user-info mb-0">
                                        @empty($template_info['configurations'])
                                        <p class="col-md-12">No configurations found.</p>
                                        @else
                                        @php
                                        $categoryIds = explode(',', $template_info['device_category_id']);
                                        $deviceCategories = DeviceCategory::select('*')->whereIn('id', $categoryIds)->first();
                                        $input = ($deviceCategories && $deviceCategories->inputs)
                                            ? json_decode($deviceCategories->inputs, true)
                                            : [];
                                        if (!is_array($input)) {
                                            $input = [];
                                        }
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
                                                                    @foreach ($input ?? [] as $field => $value)
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
                                                            <form action="{{ url($url_type . '/update-template-configurations/' . $template_info['id']) }}" method="POST">
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
                    </div>
                </div>
                                    @if($getCanEnableByDeviceCategory && $getCanEnableByDeviceCategory->is_can_protocol == 1)
                <div class="c_panel vtc-can-panel">
                    <div class="c_title">
                        <h2 class="vtc-main-title"><i class="fa fa-microchip"></i> CAN Protocol Configurations</h2>
                    </div>
                    <div class="c_content">
                                    <div class="user-info mb-0">
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
                    </div>
                </div>
                                    @endif
            </div>
        </div>
    </section>
</section>
<style>
    .view-template-config-page .wrapper {
        padding-top: 8px !important;
    }

    .view-template-config-page .protocol-breadcrumb-wrap {
        padding: 4px 0 14px 0 !important;
        margin: 0 !important;
    }

    .view-template-config-page .protocol-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        flex-wrap: wrap;
        row-gap: 6px;
        background: #1e293b !important;
        border-radius: 50px !important;
        padding: 6px 18px 6px 8px !important;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18) !important;
    }

    .view-template-config-page .protocol-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #76CF1C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .view-template-config-page .protocol-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .view-template-config-page .protocol-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }

    .view-template-config-page .protocol-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .view-template-config-page .protocol-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .view-template-config-page .protocol-breadcrumb a.bc-item:hover {
        color: #e2e8f0;
    }

    .view-template-config-page .c_title h2::before {
        content: none !important;
        display: none !important;
    }

    .view-template-config-page .vtc-main-title {
        display: inline-flex !important;
        align-items: center;
        gap: 10px;
        margin: 0 !important;
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .view-template-config-page .vtc-main-title > i {
        color: #76CF1C;
        font-size: 14px;
    }

    .view-template-config-page .vtc-info-body .edit-template-btn,
    .view-template-config-page .vtc-config-body .edit-btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        background: #2d353c !important;
        border: none !important;
        padding: 8px 18px !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
    }

    .view-template-config-page .vtc-info-body .edit-template-btn:hover,
    .view-template-config-page .vtc-config-body .edit-btn:hover {
        background: #1e252b !important;
        color: #fff !important;
    }

    .view-template-config-page .vtc-info-layout {
        align-items: flex-start;
    }

    .view-template-config-page .vtc-meta-col {
        padding-right: 16px;
    }

    .view-template-config-page .vtc-dl {
        margin: 0;
    }

    .view-template-config-page .vtc-dl-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 8px 16px;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: baseline;
    }

    .view-template-config-page .vtc-dl-row:last-child {
        border-bottom: none;
    }

    .view-template-config-page .vtc-dl dt {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
    }

    .view-template-config-page .vtc-dl dd {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .view-template-config-page .vtc-date {
        font-weight: 600 !important;
        color: #475569 !important;
        font-size: 13px !important;
    }

    .view-template-config-page .vtc-badges-col {
        display: flex;
        justify-content: flex-end;
        align-items: flex-start;
    }

    @media (max-width: 991px) {
        .view-template-config-page .vtc-badges-col {
            justify-content: flex-start;
            margin-top: 16px;
        }
    }

    .view-template-config-page .vtc-badge-stack {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
        max-width: 320px;
        width: 100%;
    }

    .view-template-config-page .vtc-pill {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px 10px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.12);
        border: none;
        white-space: nowrap;
    }

    .view-template-config-page .vtc-pill-glyph {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.22);
        flex-shrink: 0;
        font-size: 12px;
    }

    .view-template-config-page .vtc-pill-default.is-yes {
        background: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
    }

    .view-template-config-page .vtc-pill-default.is-no {
        background: linear-gradient(135deg, #e57373 0%, #d32f2f 100%);
    }

    .view-template-config-page .vtc-pill-ping.is-on {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
    }

    .view-template-config-page .vtc-pill-ping:not(.is-on) {
        background: #94a3b8;
    }

    .view-template-config-page .vtc-pill-edit.is-yes {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }

    .view-template-config-page .vtc-pill-edit.is-no {
        background: linear-gradient(135deg, #e57373 0%, #c62828 100%);
    }

    .view-template-config-page .vtc-config-body #config .col-lg-9 p,
    .view-template-config-page .vtc-config-body .configuration-item h6 {
        margin-bottom: 10px;
    }

    .view-template-config-page .vtc-config-body #config .col-lg-9 p strong {
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 700;
        margin-right: 6px;
    }

    .view-template-config-page .vtc-config-panel .configuration-item h6 {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }

    .view-template-config-page .c_panel + .c_panel {
        margin-top: 22px !important;
    }
</style>
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
@endsection