<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('add-multipledevice') }}">
@endpush
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!--main content start-->
<div class="modal" id="imeiPreviewModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content amd-can-modal-content">
            <div class="modal-header amd-can-modal-header" style="display: flex; align-items: center; justify-content: space-between;">
                <h4 class="modal-title"><strong>IMEI LIST</strong></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body amd-can-modal-body">
                <div class="row">
                    <div class="col-md-6">
                        Total New IMEI : <span class="total_new_imei"></span>
                    </div>
                    <div class="col-md-6">
                        Total Duplicate IMEI : <span class="total_dup_imei"></span>
                    </div>
                </div>
                <div class="row margin-top-15">
                    <div class="col-md-12">
                        <table id="new_imei_table" class="table table-bordered new_imei_table">
                            <thead>
                                <tr>
                                    <th>Check All &nbsp; <input checked="checked" value="1" name="select_all" type="checkbox" id="new_imei_checkall"></th>
                                    <th>SL NO</th>
                                    <th>Name</th>
                                    <th>IMEI</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row margin-top-15">
                    <div class="col-md-12" style="overflow: scroll;">
                        <table id="dup_imei_table" class="dup_imei_table table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:13px;">
                            <thead>
                                <tr>
                                    <th>Check All &nbsp; <input type="checkbox" id="dup_imei_checkall"> </th>
                                    <th>Sr. No.</th>
                                    <th>User Name</th>
                                    <th>Name</th>
                                    <th>IMEI</th>
                                    <th>Added On</th>
                                    <th>Last Settings Update</th>
                                    <th>Last Ping</th>
                                    <th>Total Pings</th>
                                    <th>Ping Interval</th>
                                    <th>Editable</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="dup_action_row">
                    <div class="form-group">
                        <label class="control-label">
                            <input checked="checked" type="radio" class="selectDupImeiType" value="overwrite" name="dup_type">
                            Overwrite Duplicate IMEI
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            <input type="radio" class="selectDupImeiType" value="skip" name="dup_type">
                            Skip Duplicate IMEI
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer amd-can-modal-footer text-center">
                <button type="button" class="btn btn-primary btn-raised submit_sel_imei"><i class="fa fa-check"></i> Import Selected IMEI Button</button>
            </div>
        </div>
    </div>
</div>
<section id="main-content" class="add-multiple-device-page">
    <section class="wrapper">
        <!--======== Page Title and Breadcrumbs Start ========-->
        <div class="amd-breadcrumb-wrap">
            <nav class="amd-breadcrumb">
                <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
                <a href="{{ url('admin') }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <span class="bc-item">Device Management</span>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Add Multiple Device</span>
            </nav>
        </div>
        <!--======== Page Title and Breadcrumbs End ========-->
        <!--======== Form Validation Content Start End ========-->
        <div class="row">
            <div class="col-md-12">
                <!--=========== START TAGS INPUT ===========-->
                <div class="c_panel">
                    <div class="c_title">
                        <h2 class="amd-panel-title"><i class="fa fa-upload"></i> Add Multiple Device</h2>
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
                            <div class="col-sm-12 alert alert-danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                            @endif
                        </div>
                        <form class="validator form-horizontal amd-form" id="commentForm" method="post" action="{{url('/admin/submit-Multipledevice')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="imeifields"></div>
                            <div class="amd-form-card amd-form-card--import">
                                <h3 class="amd-section-title"><i class="fa fa-file-excel-o"></i> Import</h3>
                            <div class="form-group amd-form-row">
                                <label for="cname" class="control-label col-lg-3">Account (optional)</label>
                                <div class="col-lg-6">
                                    <select class="" id="user_id" name="user_id">
                                        @if(count($users) > 0)
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                        @endforeach
                                        @else
                                        <option>No User Found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="form-group amd-form-row">
                                <label for="cemail" class="control-label col-lg-3">Import Excel File <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <div class="amd-file-row">
                                        <input type="file" name="excel_file" id="excel_file" class="reqfield amd-file-input" accept=".xlsx,.xls" />
                                        <a href="#" data-toggle="modal" data-target="#excelFormatModal" class="amd-file-info" title="Excel format instructions" aria-label="Excel format instructions">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </div>
                                    <span class="req_error text-danger display-block"></span>

                                </div>
                            </div>
                            <div class="modal" id="excelFormatModal" tabindex="-1" role="dialog" aria-labelledby="formatModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-md" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="formatModalLabel">
                                                <i class="fa fa-file-excel-o"></i> EXCEL FILE FORMAT INSTRUCTIONS
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="instruction-intro">
                                                <i class="fa fa-info-circle"></i> Please ensure your Excel file follows the correct format:
                                            </div>

                                            <div class="format-cards">
                                                <div class="format-card">
                                                    <div class="format-icon">
                                                        <i class="fa fa-list-ol"></i>
                                                    </div>
                                                    <div class="format-content">
                                                        <div class="format-title">
                                                            SL NO
                                                            <span class="format-badge">e.g. 1, 2, 3...</span>
                                                        </div>
                                                        <p class="format-desc">Serial number of the record to maintain order.</p>
                                                    </div>
                                                </div>

                                                <div class="format-card">
                                                    <div class="format-icon">
                                                        <i class="fa fa-tag"></i>
                                                    </div>
                                                    <div class="format-content">
                                                        <div class="format-title">
                                                            Name
                                                            <span class="format-badge">e.g. John Doe, Device X</span>
                                                        </div>
                                                        <p class="format-desc">Assign a unique and recognizable name to each device.</p>
                                                    </div>
                                                </div>

                                                <div class="format-card">
                                                    <div class="format-icon">
                                                        <i class="fa fa-barcode"></i>
                                                    </div>
                                                    <div class="format-content">
                                                        <div class="format-title">
                                                            IMEI
                                                            <span class="format-badge">15 digits</span>
                                                        </div>
                                                        <p class="format-desc">Valid IMEI number. Must consist of numeric characters only.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="premium-alert">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <div>
                                                    Please ensure to save your file in <code>.xlsx</code> or <code>.xls</code> format before proceeding with the upload.
                                                </div>
                                            </div>

                                            <div class="modal-footer-custom">
                                                <a href="{{ asset('assets/imeiDocument.xlsx') }}" target="_blank" class="btn-download">
                                                    <i class="fa fa-download"></i> Download Sample File
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <div class="amd-form-card amd-form-card--device">
                                <h3 class="amd-section-title"><i class="fa fa-microchip"></i> Device Configuration</h3>
                            <div class="form-group amd-form-row">
                                <label for="curl" class="control-label col-lg-3 ">Device Category <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="s2example-2" name="deviceCategory" onChange="getSelectedDeviceCategory()" required="required">
                                        <option value=""> </option>
                                        @foreach($getDeviceCategory as $deviceCategory)
                                        <option value="{{$deviceCategory->id}}">{{$deviceCategory->device_category_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group amd-form-row isCanEnable" style="display:none;">
                                <label for="firmware" class="control-label col-lg-3 " required>Can Configuration <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <div class="amd-can-inline">
                                        <input type="text" class="form-control amd-can-readonly" name="canConfigurationArr" id="canConfigurationArr" value="" readonly="readonly" placeholder="Not configured" />
                                        <button type="button" class="btn amd-btn-can" onclick="openCanModal()">
                                            <i class="fa fa-cog"></i> Configure CAN Protocol
                                        </button>
                                    </div>
                                    <div class="col-sm-12 alert alert-danger canConfiguration_error" role="alert" style="display:none"></div>
                                </div>
                            </div>
                            <div class="form-group " id="templateInput" style='display:none;'>
                                <label for="curl" class="control-label col-lg-3 ">Templates <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="templates" name="templates" id="templates" onChange="getTemplateConfiguration()" required>
                                        <option value=""> </option>
                                    </select>
                                    <span class="req_error text-danger display-block"></span>
                                </div>
                            </div>
                            <div class="form-group " id="FirmwareInput" style='display:none;'>
                                <label for="firmware" class="control-label col-lg-3 " required>Firmware <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="firmware" name="firmware" class="form-control" placeholder='Search and Select'>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group " id="modalInput" style='display:none;'>
                                <label for="firmware" class="control-label col-lg-3 " required>Model Name <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="configuration[modelName]" id="modelName" value="" readonly="readonly" />
                                    <div class="col-sm-12 alert alert-danger modelName_error" role="alert" style="display:none"></div>
                                </div>
                            </div>
                            <div class="form-group " id="VendorID" style='display:none;'>
                                <label for="firmware" class="control-label col-lg-3 " required>Vendor ID <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="configuration[vendorId]" id="VendorId" value="0" readonly="readonly" />
                                    <div class="col-sm-12 alert alert-danger vendor_error" role="alert" style="display:none"></div>
                                </div>
                            </div>
                            <div id='deviceCategoryInputFields' style='display:none;'></div>
                            </div>

                            <div class="amd-form-card amd-form-card--settings">
                                <h3 class="amd-section-title"><i class="fa fa-sliders"></i> Settings</h3>
                            <div class="form-group amd-form-row">
                                <label for="curl" class="control-label col-lg-3">Ping interval <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control reqfield" placeholder="Enter Ping Interval" id="ping_interval" type="Number" name="configuration[ping_interval]" value="4" onkeypress="return blockSpecialCharTransmission(event)" />
                                    <span class="req_error text-danger"></span>
                                </div>
                            </div>
                            <div class="form-group amd-form-row">
                                <label for="curl" class="control-label col-lg-3">Device Edit Permission</label>
                                <div class="col-lg-6">
                                    <div class="amd-radio-pills" role="radiogroup" aria-label="Device edit permission">
                                        <label class="amd-radio-pill">
                                            <input type="radio" name="configuration[is_editable]" value="1" checked="checked">
                                            <span>Enable</span>
                                        </label>
                                        <label class="amd-radio-pill">
                                            <input type="radio" name="configuration[is_editable]" value="0">
                                            <span>Disable</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            </div>

                            <div class="amd-form-actions">
                                <button class="btn amd-btn-save submitMultipleDevice btn-disable-after-submit" type="button">
                                    <i class="fa fa-check"></i> Save
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

<!-- Modal: CAN Protocol (scoped styling #canModal) -->
<div class="modal amd-can-modal-root" id="canModal" tabindex="-1" role="dialog" aria-labelledby="canModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-md amd-can-modal-dialog" role="document">
        <div class="modal-content amd-can-modal-content">

            <div class="modal-header amd-can-modal-header">
                <h5 class="modal-title" id="canModalTitle">CAN Protocol Configuration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body amd-can-modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="canForm">
                            <!-- Protocol Selection -->
                            <div class="isCanEnable" style="display:none;">
                                <div style="margin:10px 0px;">
                                    <label for="curl" class="control-label padding-left-3">Can Channel<span class="require">*</span></label>
                                    <select class="form-control" id="can_channel" name="canConfiguration[can_channel]" required>
                                        <option value="">-- Select CAN Channel --</option>
                                        <option value="1">CAN 1</option>
                                        <option value="2">CAN 2</option>
                                        <option value="3">CAN 3</option>
                                        <option value="4">CAN 4</option>
                                    </select>
                                </div>
                                <div style="margin:10px 0px;">
                                    <label class="control-label">Can Baud Rate <span class="require">*</span></label>
                                    <select id="can_baud_rate" name="canConfiguration[can_baud_rate]" class="form-control" required>
                                        <option value="">-- Select Protocol --</option>
                                        <option value="500">500 kbps</option>
                                        <option value="250">250 kbps</option>
                                    </select>
                                </div>
                                <div style="margin:10px 0px;">
                                    <label class="control-label">Can ID Type <span class="require">*</span></label>
                                    <select id="can_id_type" name="canConfiguration[can_id_type]" class="form-control" required>
                                        <option value="">-- Select Protocol --</option>
                                        <option value="0">Standard</option>
                                        <option value="1">Extended</option>
                                    </select>
                                </div>
                                <div style="margin:10px 0px;">
                                    <label for="curl" class="control-label padding-left-3">Can Protocol<span class="require">*</span></label>
                                    <select class="" id="can_protocol" name="canConfiguration[can_protocol]" onChange="selectedCanProtocol()" required>
                                        <option value=""> </option>
                                        <option value="1">J1979</option>
                                        <option value="2">J1939</option>
                                        <option value="3">Custom CAN</option>
                                    </select>
                                </div>
                                <div id="dynamicCanFields"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer amd-can-modal-footer">
                <button type="button" class="btn btn-success amd-can-submit-btn" onclick="generateJSON()">Submit</button>
            </div>

        </div>
    </div>
</div>
<!--======== Main Content End ========-->
@stop
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script>
    function openCanModal() {
        $('#canModal').modal('show');
    }

    function selectedCanProtocol() {
        let canProtocolValue = $('#can_protocol').val();
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
                    // console.log("field ==>", field);
                    // const id = field.id;
                    const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
                    const inputType = field.inputType;
                    let validation = {};

                    try {
                        validation = JSON.parse(field.validationConfig || '{}');
                    } catch (e) {
                        console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
                    }

                    let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
                    inputHtml += `<input type="hidden" name="CanParametersType[${fieldId}]" value="${inputType}" />`;
                    let attr = `id="${fieldId}" name="canConfiguration[${fieldId}]" class="form-control ip-url-space"  placeholder="Enter ${field.fieldName}"`;

                    if (inputType === 'number') {
                        if (validation.numberInput) {
                            attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
                        }
                        inputHtml += `<input type="number" ${attr} />`;
                    } else if (inputType == 'multiselect') {
                        inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${fieldId}][]">`;

                        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                            validation.selectOptions.forEach((option, index) => {

                                inputHtml += `<option value="${validation.selectValues[index]}">${option}</option>`;
                            });
                        } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                            Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                inputHtml += `<option value="${key}">${value}</option>`;
                            });
                        } else {
                            inputHtml += `<option value="">-- Select --</option>`;
                        }

                        inputHtml += `</select>`;
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
                    } else if (inputType === "text_array") {
                        var values = [""];
                        var maxValue = validation.maxValueInput || 0;
                        inputHtml += "<div id='" + fieldId + "_wrapper' class='text-array-wrapper'>" +
                        values.map(function(val, index) {
                            return "<div class='text-array-item d-flex align-items-center mb-2'>" +
                                "<input type='text' id='" + fieldId + index + "' name='canConfiguration[" + fieldId + "][]' class='form-control text-array-space me-2' placeholder='Enter " + field.fieldName + "' value='" + val.trim() + "' />" +
                                "<button type='button' class='btn btn-sm btn-danger remove-text-input'><i class='fa fa-minus'></i></button>" +
                                "</div>";
                        }).join("") +
                        "<button type='button' class='btn btn-sm btn-primary add-text-input mt-1'><i class='fa fa-plus'></i> Add</button>" +
                        "</div>";
                        inputHtml += "<input type='hidden' id='" + fieldId + "' name='canConfiguration[" + fieldId + "]' />";
                        setTimeout(function() {
                            var wrapper = $("#" + fieldId + "_wrapper");
                            wrapper.on("click", ".add-text-input", function() {
                                var count = wrapper.find(".text-array-item").length;
                                if (maxValue && count >= maxValue) {
                                    alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                                    return;
                                }
                                var newInput = "<div class='text-array-item d-flex align-items-center mb-2'>" +
                                    "<input type='text' id='" + fieldId + "_" + count + "' name='canConfiguration[" + fieldId + "][]' class='form-control text-array-space me-2' placeholder='Enter " + field.fieldName + "' />" +
                                    "<button type='button' class='btn btn-sm btn-danger remove-text-input'><i class='fa fa-minus'></i></button>" +
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
                                $("#" + fieldId).val("{" + values.join(",") + "}");
                            }
                            updateHiddenValue();
                        }, 100);
                    } else if (inputType === 'hex') {
                        let attr1 = `id="${fieldId}" name="canConfiguration[${fieldId}]" class="form-control text-array-space me-2"`;
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
                $('#dynamicCanFields').html(html).show();
            },
            error: function(xhr) {
                console.error("Error fetching CAN protocol fields", xhr);
            }
        });
    }

    function generateJSON() {
        let canConfigData = {};

        $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
            let fieldId = $(this).attr('id');
            console.log("fieldId ==>", fieldId);
            let value = $(this).val();
            if (fieldId === 'can_protocol') {
                canConfigData[fieldId] = {
                    id: 97,
                    value: value
                };
            } else if (fieldId == 'can_channel') {
                canConfigData[fieldId] = {
                    id: 94,
                    value: value
                };
            } else if (fieldId == 'can_baud_rate') {
                canConfigData[fieldId] = {
                    id: 96,
                    value: value
                };
            } else if (fieldId == 'can_id_type') {
                canConfigData[fieldId] = {
                    id: 95,
                    value: value
                };
            } else {
                let hiddenInput = $(`input[name="idCanParameters[${fieldId}]"]`);
                let canParametersType = $(`input[name="CanParametersType[${fieldId}]"]`).val();
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
        $('#canConfigurationArr').val(JSON.stringify(canConfigData));
        $('#canModal').modal('hide');
    }
    $(document).ready(function() {
        $('#can_protocol').select2({
            placeholder: "Search and Select",
        });
        $('#s2example-2').select2({
            // Configuration options
        });
        $("#user_id").select2({
            placeholder: 'Search and Select'
        });
        // $("#s2example-2").select2({
        //     placeholder: 'Search and Select'
        // });
        $("#templates").select2({
            placeholder: 'Search and Select'
        });
        $('#imei').bind('keyup paste', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        $('#user_id').on('change', function(e) {
            var user_id = this.value;
            $.ajax({
                url: "{{url('admin/getusers')}}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: user_id
                },
                success: function(data) {
                    if (data['success']) {
                        $("#ip").val(data.userinfo.ip);
                        $("#port").val(data.userinfo.port);
                        $("#logs_interval").val(data.userinfo.logs_interval);
                        $("#sleep_interval").val(data.userinfo.sleep_interval);
                        $("#trans_interval").val(data.userinfo.transmission_interval);
                        $("#password").val(data.userinfo.LoginPassword);
                        if (data.userinfo.FOTA == 1) {
                            $('#fota option[value="1"]').attr("selected", "selected");
                        } else {
                            $('#fota option[value="0"]').attr("selected", "selected");
                        }
                        if (data.userinfo.Active_Status == 1) {
                            $('#Active_Status option[value="1"]').attr("selected", "selected");
                        } else {
                            $('#Active_Status option[value="0"]').attr("selected", "selected");

                        }
                    } else if (data['error']) {
                        alert(data['error']);
                    } else {
                        alert('Whoops Something went wrong!!');
                    }
                },
                error: function(data) {
                    alert(data.responseText);
                }

            });
        });
        $('#user_id').on('change', function() {
            var userId = $(this).val();

            if (userId == "" || userId == "No User Found") {
                // Admin mode or no user selected
                $("#VendorId").val("JSD");
                $('#templateInput').show();
                $('#modelName').show();
                $('#VendorId').show();
                $(".vendor_error").hide()
                $(".modelName_error").hide();
                $('#deviceCategoryInputFields').show();
                getSelectedDeviceCategory(userId);
            } else {
                $('#templateInput').hide();
                $('#deviceCategoryInputFields').empty().hide();
            }
        });

        $('#firmware').on('change', function() {
            var userId = $('#user_id').val();
            var firmwareId = $(this).val();
            var categoryId = $('#s2example-2').val();

            if (firmwareId) {
                checkModalNameExist(userId, firmwareId, categoryId);
            }
        });
    });

    function checkModalNameExist(userId, firmwareId, categoryId) {
        let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-model-name') }}";
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                user_id: userId,
                firmware_id: firmwareId,
                category_id: categoryId
            },
            success: function(response) {
                let result = (typeof response === 'string') ? JSON.parse(response) : response;
                if (result.status == 200) {
                    let modal = result.modal;
                    if (modal != null) {
                        $('#modelName').val(modal.name);
                        $('#VendorId').val(modal.vendorId);
                        $('#modelName').show();
                        $('#VendorId').show();
                        $(".vendor_error").hide()
                        $(".modelName_error").hide();
                        $('.btn-disable-after-submit').attr('disabled', false);
                    } else {
                        $('#modelName').hide();
                        $('#VendorId').hide();
                        $(".modelName_error").show().html('Model Name is not Assigned. Please contact with Administrator');
                        $(".vendor_error").show().html('Vendor ID is not Assigned. Please contact with Administrator');
                        $('.btn-disable-after-submit').attr('disabled', true);
                    }
                } else {
                    $('#modelName').val('').hide();
                    $('#VendorId').val('').hide();
                    $(".modelName_error").show().html(result.message || 'Model and Firmware combination does not exist.');
                    $(".vendor_error").show().html(result.message || 'Model and Firmware combination does not exist.');
                    $('.btn-disable-after-submit').attr('disabled', true);
                }
            },
            error: function(xhr) {
                console.error("Error:", xhr.responseText);
                $(".modelName_error").show().html("An error occurred while processing your request.");
                $('.btn-disable-after-submit').attr('disabled', true);
            }
        });
    }

    function getSelectedDeviceCategory(userId) {
        $('#loading').show();
        let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-device-category') }}";
        var selectedDeviceCategoryId = $('#s2example-2').val();

        $('#deviceCategoryInputFields').html('');
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                id: selectedDeviceCategoryId,
                user_id: userId
            },
            success: function(response) {
                let result = JSON.parse(response);
                let templates = JSON.parse(result.templates);
                let templateConfig = templates.configurations;
                let firmwares = JSON.parse(result.firmware);
                let dataFields = JSON.parse(result.dataFields);
                let canEnable = result.canEnable == 1 ? true : false;
                if (canEnable) {
                    $('.isCanEnable').show();
                }
                var selectedOptionText = $('#s2example-2 option:selected').text();
                $('#modelName').val(selectedOptionText);
                $('#VendorId').val("JSD");
                console.log('firmwares', firmwares);
                if ($('#user_id').val() == "") {
                    $('#templateInput').show();
                }
                $('#FirmwareInput').show();
                $('#modalInput').show();
                $('#VendorID').show();

                $('#templates').empty();
                $('#firmware').empty();

                firmwares.forEach(firmware => {
                    var option = new Option(firmware.name, firmware.id, firmware.is_default == 1, firmware.is_default == 1);
                    $('#firmware').append(option);
                });
                if ($('#user_id').val() == "") {
                    templates.forEach((template) => {
                        var option = new Option(template.template_name, template.id, template.default_template == 1, template.default_template == 1);
                        $('#templates').append(option);
                    });

                    // Trigger change event after all options are appended
                    $('#templates').trigger('change');

                    // Select the option using Select2's API
                    if (templates.length > 0) {
                        $('#templates').val(templates[0].id).trigger('change.select2');
                    }
                }
                $('#firmware').trigger('change');
                let htmlContent = '';
                if ($('#user_id').val() == "") {
                    if (result.status == 200) {
                        let inputFields = JSON.parse(result.device_input);
                        inputFields.forEach((input, index) => {

                            htmlContent += '<input class="form-control" type="hidden"  name="idParameters[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="">';
                            if (input.type == 'select') {
                                let field = dataFields.filter((item) => item.fieldName.replace(/\s+/g, '_').toLowerCase() == input.key.replace(/\s+/g, '_').toLowerCase());
                                let config = JSON.parse(field[0].validationConfig);
                                htmlContent += '<div class="form-group">';
                                htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                                htmlContent += '<div class="col-lg-6">';
                                htmlContent += '<select class="form-control inputType" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                                // htmlContent += '<option value="">Please Select</option>';

                                if (config?.selectOptions && config?.selectValues) {
                                    config.selectOptions.forEach((option, index) => {
                                        const value = config.selectValues[index] ?? '';
                                        htmlContent += `<option value="${value}">${option}</option>`;
                                    });
                                }

                                htmlContent += '</select>';
                                htmlContent += '</div>';
                                htmlContent += '</div>';
                            } else if (input.type == 'multiselect') {
                                htmlContent += '<div class="form-group">';
                                htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                                let field = dataFields.filter((item) => item.fieldName.replace(/\s+/g, '_').toLowerCase() == input.key.replace(/\s+/g, '_').toLowerCase());
                                htmlContent += '<div class="col-lg-6">';
                                htmlContent += `<select id="${input.key.replace(/\s+/g, '_').toLowerCase() }" " multiple name="configuration[` + input.key.replace(/\s+/g, '_').toLowerCase() + `][]" ` + (input.requiredFieldInput ? 'required' : '') + `>`;
                                let config = JSON.parse(field[0].validationConfig);
                                if (config.selectOptions && Array.isArray(config.selectOptions)) {
                                    config.selectOptions.forEach(option => {
                                        htmlContent += `<option value="${option}">${option}</option>`;
                                    });
                                } else if (config.selectOptions && typeof config.selectOptions === 'object') {
                                    Object.entries(config.selectOptions).forEach(([key, value]) => {
                                        htmlContent += `<option value="${key}">${value}</option>`;
                                    });
                                } else {
                                    htmlContent += `<option value="">-- Select --</option>`;
                                }

                                htmlContent += `</select></div>`;
                                htmlContent += `</div>`;


                                // Apply Select2

                                setTimeout(() => {
                                    $(document).ready(function() {
                                        var $select = $('#' + input.key.replace(/\s+/g, '_').toLowerCase());

                                        $select.select2({
                                            placeholder: "Select up to 3 options",
                                            width: "100%"
                                        });

                                        $select.on("change", function() {
                                            var selected = $(this).select2("val");

                                            if (selected && selected.length > config.maxSelectValue) {
                                                // Remove the last selected item
                                                selected.splice(config.maxSelectValue);
                                                $(this).select2("val", selected);
                                                alert("You can only select up to " + config.maxSelectValue + " options.");
                                            }
                                        });
                                    });
                                    // $('#' + input.key.replace(/\s+/g, '_').toLowerCase()).select2({
                                    //     placeholder: 'Select options',
                                    //     allowClear: true,
                                    //     width: '100%'
                                    // });
                                }, 100);
                            } else {

                                if (input.key == "Password") {
                                    htmlContent += '<div class="form-group">';
                                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                                    htmlContent += '<div class="col-lg-6">';
                                    htmlContent += '<input class="form-control inputType" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength ="' + input.numberRange[0]?.min + '" maxlength="' + input.numberRange[0]?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                                    htmlContent += '</div>';
                                    htmlContent += '</div>';
                                } else {
                                    htmlContent += '<div class="form-group">';
                                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                                    htmlContent += '<div class="col-lg-6">';
                                    htmlContent += '<input class="form-control inputType" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'min ="' + input.numberRange?.min + '" max="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';

                                    htmlContent += '</div>';
                                    htmlContent += '</div>';
                                }
                            }
                        });
                        if ($('#user_id').val() == "") {
                            $('#deviceCategoryInputFields').append(htmlContent);
                        }
                    } else {
                        $('#loading').hide();
                        $('#deviceCategoryInputFields').empty();
                        alert(result.message);

                    }
                } else {
                    $('#deviceCategoryInputFields').empty();
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

    // function getSelectedDeviceCategory() {
    //     let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-device-category') }}";
    //     $('#deviceCategoryInputFields').hide();
    //     $('#loading').show();
    //     var selectedDeviceCategoryId = $('#s2example-2').val();
    //     $('#deviceCategoryInputFields').html('');
    //     $.ajax({
    //         url: actionUrl,
    //         type: "POST",
    //         data: {
    //             id: selectedDeviceCategoryId
    //         },
    //         success: function(response) {
    //             let result = JSON.parse(response);
    //             console.log("result ==> ", result);
    //             let templates = JSON.parse(result.templates);
    //             let firmwares = JSON.parse(result.firmware);
    //             $('#templateInput').show();
    //             $('#FirmwareInput').show();
    //             $('#modalInput').show();
    //             $('#templates').empty();
    //             $('#firmware').empty();
    //             var selectedOptionText = $('#s2example-2 option:selected').text();
    //             $('#modelName').val(selectedOptionText);
    //             firmwares.forEach(firmware => {
    //                 var option = new Option(firmware.name, firmware.id, firmware.is_default == 1, firmware.is_default == 1);
    //                 $('#firmware').append(option);
    //             });
    //             templates.forEach((template) => {
    //                 var option = new Option(template.template_name, template.id, template.default_template == 1, template.default_template == 1);
    //                 $('#templates').append(option);
    //             });

    //             // Trigger change event after all options are appended
    //             $('#templates').trigger('change');
    //             let htmlContent = '';
    //             if (result.status == 200) {
    //                 let inputFields = JSON.parse(result.device_input);
    //                 inputFields.forEach((input, index) => {
    //                     if (input.type == 'select') {
    //                         htmlContent += '<div class="form-group">';
    //                         htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
    //                         htmlContent += '<div class="col-lg-6">';
    //                         htmlContent += '<select class="form-control inputType" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + input.key + (input.requiredFieldInput ? 'requried' : '') + '>';
    //                         // htmlContent += '<option value="">Please Select</option>';
    //                         input?.selectOptions?.map((option) => {
    //                             htmlContent += '<option value="' + option.toLowerCase() + '">' + option + '</option>';
    //                         });
    //                         htmlContent += '</select>';
    //                         htmlContent += '</div>';
    //                         htmlContent += '</div>';
    //                     } else {
    //                         if(input.key == "Password"){
    //                             htmlContent += '<div class="form-group">';
    //                             htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
    //                             htmlContent += '<div class="col-lg-6">';
    //                             htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength ="' + input.numberRange?.min + '" maxlength="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'requried' : '') + '>';
    //                             htmlContent += '</div>';
    //                             htmlContent += '</div>';
    //                         }else{
    //                             htmlContent += '<div class="form-group">';
    //                             htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
    //                             htmlContent += '<div class="col-lg-6">';
    //                             htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'min ="' + input.numberRange?.min + '" max="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'requried' : '') + '>';
    //                             htmlContent += '</div>';
    //                             htmlContent += '</div>';
    //                         }
    //                     }
    //                 });

    //                 $('#deviceCategoryInputFields').append(htmlContent);
    //             } else {
    //                 $('#loading').hide();
    //                 $('#deviceCategoryInputFields').empty().append(htmlContent);
    //                 alert(result.message);

    //             }
    //         },
    //         error: function(xhr) {
    //             console.log(xhr.responseText); // Handle error
    //         },
    //         complete: function() {
    //             $('#loading').hide(); // Hide loading indicator regardless of success or error
    //         }
    //     });
    // }

    function getTemplateConfiguration() {
        let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-template-configuration') }}";
        $('#loading').show();
        var selectedTemplateId = $('#templates').val();
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                id: selectedTemplateId
            },
            success: function(response) {
                $('#loading').hide();
                let result = JSON.parse(response);
                let template = JSON.parse(JSON.parse(result.template))
                Object.keys(template).forEach(function(key) {
                    let element = $("input[name='configuration[" + key + "]'], select[name='configuration[" + key + "]']");
                    let hiddenelement = $(`input[name="idParameters\\[${key}\\]"]`);
                    // Check if the element exists
                    if (element.length > 0) {
                        // Determine the type of element (input or select) and set the value
                        hiddenelement.val(template[key].id)
                        if (element.is('input')) {
                            element.val(template[key]['value']);
                        } else if (element.is('select')) {
                            element.val(template[key]['value']);
                        }
                    } else {
                        console.log("Element not found for key:", key);
                    }
                });
                $('#deviceCategoryInputFields').show();
            },
            error: function(data) {
                alert(data.responseText);
            }
        });
    }
</script>