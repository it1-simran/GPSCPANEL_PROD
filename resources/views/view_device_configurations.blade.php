<?php

use App\DeviceCategory;
use App\Helper\CommonHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$configurations = json_decode($device['configurations'], true);
$canConfigurations = $device['can_configurations'] != "" ? json_decode($device['can_configurations'], true) : [];
$getCanEnableByDeviceCategory = DeviceCategory::select('is_can_protocol')->where('id', $device['device_category_id'])->first();
$parameters = json_decode($device['parameters'], true);
$getFirmwareFromConfigurations = isset($configurations['firmware_id']['value']) ? DB::table('firmware')->where(['id' => $configurations['firmware_id']['value']])->first() : null;
if ($getFirmwareFromConfigurations != null) {
    $firmwareConfiguration = json_decode($getFirmwareFromConfigurations->configurations, true);
    $firmwareName = $getFirmwareFromConfigurations->name ?? '--';
    $filename = $firmwareConfiguration['filename'] ?? "--";
    $fileVersion = $firmwareConfiguration['version'] ?? "--";
    $fileSize = $firmwareConfiguration['fileSize'] ?? "--";
}

$errors = json_decode($device['errors'], true);
?>
@extends('layouts.apps')

@section('content')
<div class="modal" id="deviceUserPreviewModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><strong>Confirmation</strong></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center" style="font-size: 14px;margin-bottom: 15px;">
                        "You are trying to change the assigned Account. Device will be NO more visible in the current Account and its "Reseller" or "User" Accounts. Do you want to proceed?
                    </div>
                    <div class="col-md-12 text-center">
                        <button type="button" data-type="yes" class="btn btn-primary btn-raised selectDeviceUserChange">Yes</button>
                        <button type="button" data-type="no" class="btn btn-primary btn-raised selectDeviceUserChange">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Device </a></li>
                        <li><a href="javascript:history.back()">View Devices</a></li>
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
                                    <h4>Device and Configurations</h4>
                                </div>
                                <div class="card-body body-custom">
                                    @if( $errors && count($errors) > 0)
                                    @foreach($errors as $error)
                                    <div class="col-sm-12 alert alert-danger" role="alert">
                                        {{$error}}
                                    </div>
                                    @endforeach
                                    @endif
                                    {{-- Display User Information --}}
                                    <div class="user-info mb-4">
                                        <div class='col-lg-9'>
                                            <h4><b>Device Information</b></h4>
                                        </div>
                                        <div class='row  bgx-configurations view-device-configuration'>
                                            <div class='col-lg-5'>
                                                <div class="bgx-table-container">
                                                    <div class="bgx-table-row">
                                                        <div class="bgx-table-cell"><strong>Device Name:</strong>{{ $device['name'] ?? '' }} </div>
                                                        <div class="bgx-table-cell"><strong>Device Model:</strong>{{ $configurations['modelName']['value'] ?? '' }} </div>
                                                    </div>
                                                    <div class="bgx-table-row">
                                                        <div class="bgx-table-cell"><strong>Vendor ID:</strong> {{ $configurations['vendorId']['value'] ?? '' }}</div>
                                                        <div class="bgx-table-cell"><strong>Device Category:</strong>{{ CommonHelper::getDeviceCategoryName($device['device_category_id']) ?? '' }} </div>
                                                    </div>
                                                    <div class="bgx-table-row">
                                                        <div class="bgx-table-cell">
                                                            <strong>Account Assigned: </strong>
                                                            {{ isset($device['user_id']) && $device['user_id'] != null ? CommonHelper::getDeviceUserName($device['user_id']) : 'Unassigned' }}
                                                        </div>
                                                        <div class="bgx-table-cell"><strong>IMEI:</strong>{{$device['imei'] ?? ''}}</div>
                                                    </div>
                                                    @if(Auth::user()->user_type == 'Admin' || Auth::user()->user_type=="Support")
                                                    <div class="bgx-table-row">
                                                        <div class="bgx-table-cell">
                                                            <strong>Firmware ID:</strong>
                                                            {{ isset($configurations['firmware_id']) ? $configurations['firmware_id']['value'] : 'Not Available' }}
                                                        </div>
                                                        <div class="bgx-table-cell">
                                                            <strong>Firmware Name:</strong>
                                                            {{$firmwareName ?? "--"}}
                                                            <!--{{ isset($configurations['firmware_id']) ? CommonHelper::getFirmwareName($configurations['firmware_id']['value']) : 'Not Available' }}-->
                                                        </div>
                                                    </div>
                                                    <div class="bgx-table-row">
                                                         @if(Auth::user()->user_type == 'Admin')
                                                        <div class="bgx-table-cell"><strong>Firmware File:</strong>
                                                            @if(isset($filename))
                                                            @if(Auth::user()->user_type == 'Admin')
                                                            <a href="{{ asset('fw/' .$filename) }}" target="_blank">{{$filename}}</a>
                                                            @else
                                                            {{$filename}}
                                                            @endif
                                                            @else
                                                            Not Available
                                                            @endif
                                                        </div>
                                                        @endif
                                                        <div class="bgx-table-cell">
                                                            <strong>Firmware Version:</strong>
                                                            {{$fileVersion ?? "--"}}
                                                            <!--{{ isset($configurations['firmware_version']) ? $configurations['firmware_version']['value'] : 'Not Available' }}-->
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <div class="bgx-table-row">
                                                        @if(Auth::user()->user_type == 'Admin')
                                                        <div class="bgx-table-cell">
                                                            <strong>Firmware Filesize:</strong>
                                                            {{$fileSize ?? "--"}}
                                                            <!--{{ isset($configurations['firmware_version']) ? $configurations['firmware_version']['value'] : 'Not Available' }}-->
                                                        </div>
                                                        @endif
                                                        <div class="bgx-table-cell">
                                                            <strong>Configuration Status:</strong>
                                                            @php
                                                            $status = $device['deviceStatus'] ?? '';
                                                            $color = match($status) {
                                                            'Pending' => 'text-warning font-bold',
                                                            'Completed' => 'text-success font-bold',
                                                            default => 'text-gray-600',
                                                            };
                                                            @endphp
                                                            <span class="{{ $color }}">
                                                                @if($status == 'Completed')
                                                                {{ $status }} on {{ CommonHelper::getDateAsTimeZone($device->api_updated_at) }}
                                                                @else
                                                                {{ $status }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="bgx-table-row">
                                                        <div class="bgx-table-cell"><strong>Activation Date:</strong> {{ isset($configurations['activationDate']) ? CommonHelper::getDateAsTimeZone($configurations['activationDate']) : '' }}</div>
                                                        <div class="bgx-table-cell"><strong>Created at:</strong> {{ isset($device['created_at']) ? CommonHelper::getDateAsTimeZone($device['created_at']) : '' }}</div>
                                                    </div>
                                                    <div class="bgx-table-row">
                                                        <div class="bgx-table-cell"><strong>Last Edit:</strong> {{ isset($device['updated_at']) ? CommonHelper::getDateAsTimeZone($device['updated_at']) : '' }}</div>
                                                    </div>
                                                </div>
                                                <div style="margin-top:20px;">
                                                    @if(Auth::user()->user_type == "Admin")
                                                    <div id="span2" class="btn {{ $device['is_editable'] == 1 ? 'btn-success active' : 'btn-danger' }}" style="margin: 3px 0px;">
                                                        Editable - {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'Yes' : 'No' }}
                                                    </div>
                                                    <div id="span1" class="btn btn-primary" style='margin:3px 0px;'>Ping Interval - {{$configurations['ping_interval']['value'] ?? 0}}</div>
                                                    @endif
                                                    <div id="span3" class="btn btn-info" style='margin:3px 0px;float:left;'>Total Pings - {{ $configurations['total_pings'] ?? 0}}</div>
                                                </div>
                                            </div>
                                            <div class='col-lg-7'>
                                                <div class='row bgx-map-configurations'>
                                                    <div class='col-lg-12'>
                                                        <div id="map" style='width:100%;height:250px;'></div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(Auth::user()->user_type != "Support")
                                            @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                            <div class="row mt-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="button" class="btn btn-primary edit-device-btn" onclick="toggleEditDevice()">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                            @else
                                            @if(Auth::user()->is_support_active)
                                            <div class="row mt-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="button" class="btn btn-primary edit-btn" onclick="toggleEditDevice('')">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                            @endif
                                        </div>
                                        <div class='row  bgx-configurations edit-device-configuration' style="display:none;">
                                            <form class="validator form-horizontal " id="updateDeviceInfoConfiguration" method="post" action="/{{$url_type}}/update-device-info-configurations/{{$device['id']}}">
                                                @method('PATCH')
                                                @csrf
                                                <div class="form-group ">

                                                </div>
                                                @if(Auth::user()->user_type !='User' && Auth::user()->user_type !='Support')
                                                <div class="form-group ">
                                                    @if(Auth::user()->user_type=='Admin')
                                                    <label for="cname" class="control-label col-lg-3"><?= ($device['user_id'] != '' ? 'Account' : 'Account') ?></label>
                                                    @elseif(Auth::user()->user_type=='Reseller')
                                                    <label for="cname" class="control-label col-lg-3"><?= ($device['user_id'] != Auth::user()->id ? 'Account' : 'Account') ?></label>
                                                    @elseif(Auth::user()->user_type=='User')
                                                    <label for="cname" class="control-label col-lg-3">User</label>
                                                    @else
                                                    <label for="cname" class="control-label col-lg-3"><?= ($device['user_id'] != '' ? 'Account' : 'Account') ?></label>
                                                    @endif
                                                    <div class="col-lg-6">
                                                        <select class="" id="editDeviceUsers" name="user_id">
                                                            @if(count($users) > 0)
                                                            @if(Auth::user()->user_type=='Admin')
                                                            <option value=""><?= ($device['user_id'] != '' ? 'Unassigned' : 'Unassigned') ?></option>
                                                            @elseif(Auth::user()->user_type=='Reseller')
                                                            <option value=""><?= ($device['user_id'] != Auth::user()->id ? 'Unassigned' : 'Unassigned') ?></option>
                                                            @endif
                                                            @foreach($users as $user)
                                                            <option value="{{$user->id}}" <?= ($uid == $user->id ? 'selected' : '') ?>>{{$user->name}}</option>
                                                            @endforeach
                                                            @else
                                                            <option value="">Unassigned</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                @else
                                                <input type="hidden" name="user_id" value="{{ $uid }}">
                                                @endif
                                                @if(Auth::user()->user_type !='Support')
                                                <div class="form-group ">
                                                    <label for="curl" class="control-label col-lg-3">Name (optional)<span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <input class="form-control" placeholder="Enter Device Name" id="name" type="text" name="name" value="{{ $device['name']}}">
                                                    </div>
                                                </div>
                                                @else
                                                <input class="form-control" placeholder="Enter Device Name" id="name" type="hidden" name="name" value="{{ $device['name']}}">
                                                @endif
                                                <div class="form-group " id="FirmwareInput">
                                                    <label for="firmware" class="control-label col-lg-3 " required>Firmware <span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <select id="firmware" name="configuration[firmware_id]" class="form-control" placeholder='Search and Select'>
                                                            @foreach($firmware as $firmwar)
                                                            <option value="{{ $firmwar->id }}" {{ isset($configurations['firmware_id']) && $configurations['firmware_id']['value'] == $firmwar->id ? 'selected' : '' }}>
                                                                {{ $firmwar->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group " id="modalInput">
                                                    <label for="firmware" class="control-label col-lg-3 " required>Model Name <span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <input type="text" class="form-control" name="configuration[modelName]" id="modelName" value="{{ $configurations['modelName']['value'] ?? '' }}" readonly="readonly" />
                                                        <div class="col-sm-12 alert alert-danger modelName_error" role="alert" style="display:none"></div>
                                                        <input type="hidden" id="user_type" name="user_type" value="{{$url_type}}" />
                                                    </div>
                                                </div>
                                                <div class="form-group " id="vendorInput">
                                                    <label for="firmware" class="control-label col-lg-3 " required>Vendor ID <span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <input type="text" class="form-control" name="configuration[vendorId]" id="vendorId" value="{{ $configurations['vendorId']['value'] ?? '' }}" readonly="readonly" />
                                                        <div class="col-sm-12 alert alert-danger vendor_error" role="alert" style="display:none"></div>
                                                        <input type="hidden" id="user_type" name="user_type" value="{{$url_type}}" />
                                                    </div>
                                                </div>
                                                @if(Auth::user()->user_type=='Admin')
                                                <div class="form-group">
                                                    <label for="cemail" class="control-label col-lg-3">IMEI <span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <div class="input-group  d-flex">
                                                            <input class="form-control col-lg-10" id="imei" type="text" maxlength="15" name="imei" placeholder="Enter IMEI Number" value="{{$device['imei']}}" readonly />
                                                            <div class="input-group-append col-lg-2">
                                                                <button class="btn btn-secondary margin-top-1" type="button" id="editImeiBtn">Edit</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(Auth::user()->user_type != "Support" )
                                                <div class="form-group ">
                                                    <label for="curl" class="control-label col-lg-3">Ping interval <span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <input class="form-control" placeholder="Enter Ping Interval" id="ping_interval" type="Number" name="configuration[ping_interval]" value="{{isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] :''}}" onkeypress="return blockSpecialCharTransmission(event)" required />
                                                    </div>
                                                </div>
                                                <div class="form-group ">
                                                    <label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>
                                                    <div class="col-lg-6">
                                                        <label>Enable</label>
                                                        <input {{(isset($configurations['is_editable']) && $configurations['is_editable']['value']=='1'?'checked':'')}} type="radio" name="configuration[is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>
                                                        <label>Disable</label>
                                                        <input {{isset($configurations['is_editable']) && $configurations['is_editable']['value']=='0'?'checked':''}} type="radio" name="configuration[is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>
                                                    </div>
                                                </div>
                                                @else
                                                <input type="hidden" name="configuration[ping_interval]" value="$configurations['ping_interval']['value']" />
                                                <input type="hidden" name="configuration[is_editable]" value="$configurations['is_editable']['value']" />
                                                @endif
                                                @endif
                                                <div class="col-sm-12 bg-margin-top text-right">
                                                    <input type="hidden" name="prev_uid" class="prev_uid" value="{{ $device['user_id'] }}">
                                                    <input type="hidden" id="device_id" name="device_id" value="{{$device['id']}}">
                                                    <input type="hidden" id="firmwareFileSize" name="configuration[firmwareFileSize]" value"">
                                                    <button type="submit" class="btn btn-primary updateDeviceName">Save</button>
                                                    <button type="button" class="btn btn-secondary cancel-device-info-btn" data-key="0">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="user-info">
                                        <h4><b>Device Configurations</b></h4>
                                        @empty($device['configurations'])
                                        <p class="col-md-12">No configurations found.</p>
                                        @else
                                        <?php echo CommonHelper::getDeviceConfigurationInput($device['device_category_id'], 0,  $configurations, $template_info, $url_type, $device); ?>
                                        @if(Auth::user()->user_type != "Support")
                                        @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-btn" onclick="toggleEdit('')">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        @else
                                        @if(Auth::user()->is_support_active)
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-btn" onclick="toggleEdit('')">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        @endif
                                        @endempty
                                    </div>
                                    @if(isset($configurations['can_interface']['value']) && $configurations['can_interface']['value'] == 1)
                                    @if($getCanEnableByDeviceCategory->is_can_protocol == 1)
                                    <div class="user-info">
                                        <h4><b>CAN Protocol Configurations</b></h4>
                                        @php $canConfigData = is_array($canConfigurations) ? $canConfigurations : json_decode($canConfigurations, true);
                                        @endphp
                                        @empty($device['can_configurations'])
                                        <?php echo CommonHelper::getCanProtocolConfigurationInput($device['device_category_id'], 0,  $canConfigData, $url_type, $device); ?>
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
                                        @else
                                        <?php echo CommonHelper::getCanProtocolConfigurationInput($device['device_category_id'], 0,  $canConfigData, $url_type, $device); ?>
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
                                    @endif
                                    <div class="user-info">
                                        <h4><b>Device Parameters</b> <small>(<b>Last updated on:</b> {{ isset($device->api_updated_at) ? CommonHelper::getDateAsTimeZone($device->api_updated_at) : '' }})</small></h4>
                                        @empty($device['parameters'])
                                        <div class="card padding-10 text-center">
                                            <p>No configurations found.</p>
                                        </div>
                                        @else
                                        <?php echo CommonHelper::getDeviceSettings($device['device_category_id'], 0, $parameters, $template_info, $url_type, $device); ?>
                                        @endempty
                                    </div>
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
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    function openCanModal() {
        $('#canModal').modal('show');
    }
    $(document).ready(function() {
        $('#protocolSelect').on('change', function() {
            const value = $(this).val();
            $('#j1979Fields').toggle(value === 'J1979');
            $('#j1939Fields').toggle(value === 'J1939');

            let selected = $(this).val();

            $('#j1979Fields input, #j1939Fields input').removeAttr('required');

            if (selected === 'J1979') {
                $('#j1979Fields input').attr('required', true);
            } else if (selected === 'J1939') {
                $('#j1939Fields input').attr('required', true);
            }
        });

        $('#canForm').on('submit', function(e) {
            e.preventDefault();

            const protocol = $('#protocolSelect').val();
            let json = {
                can_enabled: true,
                protocol
            };

            if (protocol === 'J1979') {
                json.can_protocol = $('[name="can_protocol"]').val();
                json.request_id = $('[name="request_id"]').val();
                json.response_ids = $('[name="response_ids"]').val().split(',').map(s => s.trim());
                json.baud_rate = parseInt($('[name="baud_rate"]').val());
                json.polling_interval_ms = parseInt($('[name="polling_interval_ms"]').val());
                json.supported_modes = $('[name="supported_modes"]').val().split(',').map(s => s.trim());
                json.supported_pids = $('[name="supported_pids"]').val().split(',').map(s => s.trim());
                json.extended_id = false;
            }

            if (protocol === 'J1939') {
                json.baud_rate = parseInt($('[name="baud_rate"]').val());
                json.source_address = $('[name="source_address"]').val();
                json.preferred_address = $('[name="preferred_address"]').val();
                json.pgns_to_poll = $('[name="pgns_to_poll"]').val().split(',').map(s => s.trim());
                json.use_tp = $('[name="use_tp"]').val() === 'true';
                json.can_channel = $('[name="can_channel"]').val();
                json.name = {
                    identity_number: parseInt($('[name="identity_number"]').val()),
                    manufacturer_code: parseInt($('[name="manufacturer_code"]').val()),
                    ecu_instance: parseInt($('[name="ecu_instance"]').val()),
                    function_instance: parseInt($('[name="function_instance"]').val()),
                    function: parseInt($('[name="function"]').val()),
                    vehicle_system: parseInt($('[name="vehicle_system"]').val()),
                    arbitrary_address_capable: $('[name="arbitrary_address_capable"]').val() === 'true'
                };
            }

            $('#outputJson').val(JSON.stringify(json, null, 2));
        });
    });

    function toggleEditDevice() {
        $('.edit-device-btn').hide();
        $('.view-device-configuration').hide();
        $('.edit-device-configuration').show();
    }

    function toggleEdit(key) {
        $('.edit-btn').hide();
        $('#config-0').hide();
        $('#form-0').show();
    }

    function canConfigToggleEdit(key) {
        $('.edit-config-btn').hide();
        $('#canConfig-0').hide();
        $('#canConfigForm-0').show();
    }

    function checkModalNameExist(userId, firmwareId, deviceId) {
        $('#modalInput').show();
        $('#vendorInput').show();
        let actionUrl = "{{ url(Auth::user()->user_type == 'Admin'? 'admin/get-model-name': (Auth::user()->user_type == 'Reseller'? 'reseller/get-model-name': 'user/get-model-name')) }}";
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                user_id: userId,
                firmware_id: firmwareId,
                device_id: deviceId
            },
            success: function(response) {
                let result = JSON.parse(response);
                console.log('Different AJAX result:', result);
                if (result.status == 200) {
                    if (result.modalList !== null && result.modalList !== undefined) {
                        let modal = JSON.parse(result.modalList);
                        console.log("modal ==>", result.firmwareFileSize);
                        if (modal != null) {
                            $('#modelName').val(modal.name);
                            //$('#fileSize').val(modal.)
                            $('#modelName').show();
                            $('#vendorId').show().val(modal.vendorId);
                            $(".vendor_error").hide();
                            $(".modelName_error").hide();
                            $('.updateDeviceName').attr('disabled', false);
                        } else {
                            // $('#modelName').hide();
                            // $('#vendorId').hide();
                            // $(".vendor_error").show().html('Vendor is not Assigned . Please contact with Administrator');;
                            // $(".modelName_error").show().html('Model Name is not Assigned . Please contact with Administrator');
                            // $('.updateDeviceName').attr('disabled', true);
                        }
                        $('#firmwareFileSize').val(result.firmwareFileSize);
                    } else {
                        $('#modelName').hide();
                        $(".modelName_error").show();

                        $('.updateDeviceName').attr('disabled', true);
                    }
                } else {
                    // $('.error_msg').append(result.message).show();
                }
            },
            error: function(xhr) {
                // Handle error
                console.error("Error:", xhr.responseText);
                $('.error_msg').append("An error occurred while processing your request.").show();
            },
            complete: function() {
                $('#loading').hide(); // Hide loading indicator
            }
        });
    }
    function getFirmwareWitModel(userId) {
        const firmwareDropdown = $('#firmware');
        let selectedFirmware = <?= json_encode($configurations['firmware_id']) ?>;
        let categoryId = "<?= $device['device_category_id'] ?>";

        firmwareDropdown.empty(); // clear previous options
        let actionUrl = "{{ url(Auth::user()->user_type == 'Admin' ? 'admin/get-firmware-with-models' : (Auth::user()->user_type == 'Reseller' ? 'reseller/get-firmware-with-models' : (Auth::user()->user_type == 'Support' ? 'support/get-firmware-with-models' : 'user/get-firmware-with-models'))) }}";
        if (userId && userId !== "No User Found") {
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: {
                    user_id: userId,
                    category_id: categoryId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 200 && response.firmwareList.length > 0) {
                        // firmwareDropdown.append('<option value="">Select Firmware</option>');
                        // response.firmwareList.forEach(firmware => {
                        //     firmwareDropdown.append(
                        //         `<option value="${firmware.id}" ${Number(firmware.id) == Number(selectedFirmware['value']) ? 'selected="selected"' : ''}>${firmware.name}</option>`
                        //     );
                        // });
                        firmwareDropdown.append('<option value="">Select Firmware</option>');
                        response.firmwareList.forEach(firmware => {
                            firmwareDropdown.append(
                                `<option value="${firmware.id}" ${Number(firmware.id) == Number(selectedFirmware['value']) ? 'selected="selected"' : ''}>${firmware.name}</option>`
                            );
                        });
                        $('#templateInput').show();
                        $('#modelName').show();
                        $('#VendorId').show();
                        $(".vendor_error").hide();
                        $(".modelName_error").hide();
                    } else {
                        firmwareDropdown.append('<option value="">No Firmware Found</option>');
                    }
                },
                error: function() {
                    firmwareDropdown.append('<option value="">Error Fetching Firmware</option>');
                }
            });
        } else {
            // firmwareDropdown.append('<option value="">Select User First</option>');
            let actionUrl1 = "{{ url(Auth::user()->user_type == 'Admin' ? 'admin/get-firmware-with-models' : (Auth::user()->user_type == 'Reseller' ? 'reseller/get-firmware-with-models' : (Auth::user()->user_type == 'Support' ? 'support/get-firmware-with-models' : 'user/get-firmware-with-models'))) }}";
            $.ajax({
                url: actionUrl1,
                type: 'POST',
                data: {
                    user_id: userId,
                    category_id: categoryId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 200 && response.firmwareList.length > 0) {
                        firmwareDropdown.append('<option value="">Select Firmware</option>');
                        response.firmwareList.forEach(firmware => {
                            firmwareDropdown.append(
                                `<option value="${firmware.id}" ${Number(firmware.id) == Number(selectedFirmware['value']) ? 'selected="selected"' : ''}>${firmware.name}</option>`
                            );
                        });
                    } else {
                        firmwareDropdown.append('<option value="">No Firmware Found</option>');
                    }
                },
                error: function() {
                    firmwareDropdown.append('<option value="">Error Fetching Firmware</option>');
                }
            });
        }
    }
    $(document).ready(function() {
        let userId1 = "{{ Auth::check() && Auth::user()->user_type !='Admin' && Auth::user()->user_type !='Support' ? Auth::user()->id : $device['user_id'] }}";
        getFirmwareWitModel(userId1);
        $('#editDeviceUsers').on('change', function() {
            const userId = $(this).val();
            getFirmwareWitModel(userId);
        });
        $('#editDeviceUsers, #firmware').on('change', function() {
            const path = window.location.pathname;
            const pathSegments = path.split("/");
            const deviceID = pathSegments[pathSegments.length - 1];
            var userId = $('#editDeviceUsers').val();
            var firmwareId = $('#firmware').val();
            var user_type = $("#user_type").val();
            if (userId == "No User Found" || userId == "") {
                // $("#vendorId").val(0);
                // $('#templateInput').show();
                // $('#modelName').show();
                // $('#vendorId').show();
                // $(".vendor_error").hide()
                // $(".modelName_error").hide();
                $("#VendorId").val(0);
                $('#templateInput').show();
                $('#modelName').show();
                $('#VendorId').show();
                $(".vendor_error").hide();
                $(".modelName_error").hide();
            } else {

                if (userId != "No User Found" || userId != "" && firmwareId) {
                    checkModalNameExist(userId, firmwareId, deviceID);
                }
            }
        });
        $('#editImeiBtn').click(function() {
            var imeiInput = $('#imei');
            if (imeiInput.prop('readonly')) {
                imeiInput.prop('readonly', false);
                $(this).text('Save'); // Change button text to "Save"
            } else {
                imeiInput.prop('readonly', true);
                $(this).text('Edit'); // Change button text back to "Edit"
            }
        });
        $('#templates').select2({
            'placeholder': 'Select and Search '
        })
        $('.cancel-btn').click(function() {
            var key = $(this).data('key');
            $('.edit-btn').show();
            $('#config-0').show();
            $('#form-0').hide();
        });
        $('.cancel-config-btn').click(function() {

            var key = $(this).data('key');
            $('.edit-config-btn').show();
            $('#canConfig-0').show();
            $('#canConfigForm-0').hide();
        });
        $('.cancel-device-info-btn').click(function() {
            $('.edit-device-btn').show();
            $('.view-device-configuration').show();
            $('.edit-device-configuration').hide();
        });

        function initMap(lat, lon) {
            // Initialize the map
            var map = L.map('map').setView([lat, lon], 13);

            // Add OpenStreetMap tiles as the base layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 24,
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Function to fetch location name from OpenStreetMap Nominatim API
            function getLocationName(latitude, longitude) {
                var apiUrl = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latitude + '&lon=' + longitude;

                return new Promise(function(resolve, reject) {
                    fetch(apiUrl)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                reject('Location not found');
                            } else {
                                resolve(data.display_name);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching location:', error);
                            reject('Unknown Location');
                        });
                });
            }

            // Fetch location name and add marker with popup
            getLocationName(lat, lon)
                .then(name => {
                    // Add marker with popup
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('<b style="font-size:13px;">' + name + '</b>')
                        .openPopup();
                })
                .catch(error => {
                    console.error('Error getting location name:', error);

                    // Add marker with default popup if location name fetch fails
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('Device Location')
                        .openPopup();
                });
        }

        let coordinates = parseCoordinates("<?php echo isset($configurations['lat']) ? $configurations['lat'] . $configurations['lat_d'] : '' ?>", "<?php echo isset($configurations['lon']) ? $configurations['lon'] . $configurations['lon'] : ''; ?>");
        console.log('coordinates', coordinates)

        // Initialize map when document is ready
        initMap(coordinates.latitude, coordinates.longitude);
    });

    function parseCoordinates(latitude, longitude) {
        // Parse latitude
        var latDirection = latitude.slice(-1); // N or S
        var latValue = parseFloat(latitude.slice(0, -1)); // Remove the last character and convert to float
        var latDegrees = Math.floor(latValue / 100); // Extract degrees
        var latDecimalMinutes = (latValue % 100) / 60; // Convert remainder to decimal minutes
        var lat = latDegrees + latDecimalMinutes;
        if (latDirection === 'S') {
            lat = -lat;
        }

        // Parse longitude
        var lonDirection = longitude.slice(-1); // E or W
        var lonValue = parseFloat(longitude.slice(0, -1)); // Remove the last character and convert to float
        var lonDegrees = Math.floor(lonValue / 100); // Extract degrees
        var lonDecimalMinutes = (lonValue % 100) / 60; // Convert remainder to decimal minutes
        var lon = lonDegrees + lonDecimalMinutes;
        if (lonDirection === 'W') {
            lon = -lon;
        }

        return {
            latitude: lat,
            longitude: lon
        };
    }
</script>