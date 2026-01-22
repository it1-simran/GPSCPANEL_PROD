<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content');
<meta name="csrf-token" content="{{ csrf_token() }}">
<!--main content start-->
<!-- <div class="modal" id="imeiPreviewModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title"><strong>IMEI List</strong></h4>
            </div>
            <div class="modal-body">
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
                                    <th>IP</th>
                                    <th>Port</th>
                                    <th>Logs interval</th>
                                    <th>Sleep Interval</th>
                                    <th>Transmission Interval</th>
                                    <th>Password</th>
                                    <th>Active Status</th>
                                    <th>FOTA</th>
                                    <th>Device Model</th>
                                    <th>Firmware Version</th>
                                    <th>CCID</th>
                                    <th>Lat</th>
                                    <th>Lat D</th>
                                    <th>Lon</th>
                                    <th>Lon D</th>
                                    <th>GSM Strength</th>
                                    <th>GPRS</th>
                                    <th>GPS Fix</th>
                                    <th>TTFF</th>
                                    <th>Stored</th>
                                    <th>Sent</th>
                                    <th>Ext Battery</th>
                                    <th>Ignition</th>
                                    <th>Panic</th>
                                    <th>Ext Battery Voltage</th>
                                    <th>Internal Battery Voltage</th>
                                    <th>Flash Error</th>
                                    <th>Accelerometer</th>
                                    <th>RA</th>
                                    <th>WA</th>
                                    <th>RBT</th>
                                    <th>ConFail</th>
                                    <th>NoACK</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="dup_action_row margin-top-15">
                    <div class="form-group margin-bottom-5">
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
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-primary btn-raised submit_sel_imei"><i class="fa fa-check"></i> Import Selected IMEI Button</button>
            </div>
        </div>
    </div>
</div> -->
<section id="main-content">
    <section class="wrapper">
        <!--======== Page Title and Breadcrumbs Start ========-->
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Device Management</a></li>
                        <li class="active"><a href="#">Add Setting Bulk</a></li>
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
                        <h2>Add Setting Bulk</h2>
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
                        <form class="validator form-horizontal " id="commentForm" method="post" action="{{ url($url_type . '/assign-template-bulk') }}" enctype="multipart/form-data">
                            @csrf
                            <!-- <div class="form-group ">
                                <label for="cemail" class="control-label col-lg-3">Import Excel File <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input type="file" name="excel_file" id="excel_file" class="reqfield" />
                                    <span>(SL NO, Name, IMEI)</span>
                                    <span class="req_error text-danger display-block"></span>
                                </div>
                            </div> -->
                            <div class="form-group ">
                                <label for="cemail" class="control-label col-lg-3">Import Excel File <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <div class="d-flex" style="align-items: anchor-center;">
                                        <input type="file" name="excel_file" id="excel_file" class="reqfield bordered-1 padding-4 reqfield rounded-md" />
                                        <a href="#" data-toggle="modal" data-target="#excelFormatModal" class="margin-left-4"> <i class="fa fa-info-circle"></i> </a>
                                    </div>
                                    <span class="req_error text-danger display-block"></span>

                                </div>
                            </div>
                            <div class="modal" id="excelFormatModal" tabindex="-1" role="dialog" aria-labelledby="formatModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header d-flex" style="justify-content: space-between;">
                                            <h5 class="modal-title" id="formatModalLabel">Excel File Format Instructions</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <p class="mb-2 text-primary font-weight-bold">
                                                    <i class="fa fa-info-circle"></i> Please ensure your Excel file follows the correct format:
                                                </p>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <strong>SL NO</strong>
                                                        <span class="badge badge-pill badge-secondary ml-2">e.g. 1, 2, 3...</span>
                                                        <br><small class="text-muted">Serial number of the record</small>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <strong>Name</strong>
                                                        <span class="badge badge-pill badge-secondary ml-2">e.g. John Doe, Device X</span>
                                                        <br><small class="text-muted">Name of the device</small>
                                                    </li>
                                                    <li class="list-group-item">
                                                        <strong>IMEI</strong>
                                                        <span class="badge badge-pill badge-secondary ml-2">15 digits</span>
                                                        <br><small class="text-muted">Valid IMEI number (numeric only)</small>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="alert alert-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                Please save your file as <code>.xlsx</code> or <code>.xls</code> before uploading.
                                            </div>

                                            <div class="text-right">
                                                <a href="{{ asset('assets/imeiDocument.xlsx') }}" target="_blank" class="btn btn-sm btn-success">
                                                    <i class="fa fa-download"></i> Download Sample File
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
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
                            <div class="form-group isCanEnable" style="display:none;">
                                <label for="firmware" class="control-label col-lg-3 " required>Can Configuration <span
                                        class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="canConfigurationArr"
                                        id="canConfigurationArr" value="" readonly="readonly" />
                                    <div class="col-sm-12 alert alert-danger modelName_error" role="alert"
                                        style="display:none"></div>
                                    <button type="button" class="btn btn-primary" onclick="openCanModal()">
                                        Configure CAN Protocol
                                    </button>
                                </div>
                            </div>
                            <div class="form-group " id="FirmwareInput" style='display:none;'>
                                <label for="firmware" class="control-label col-lg-3 " required>Firmware <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="firmware" name="configuration[firmware_id]" class="form-control" placeholder='Search and Select'>
                                        <option value=""> </option>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group " id="templateInput" style='display:none;'>
                                <label for="curl" class="control-label col-lg-3 ">Templates <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="templates" name="templates" onChange="getTemplateConfiguration()" required>
                                        <option value=""> </option>
                                    </select>
                                    <span class="req_error text-danger display-block"></span>
                                </div>
                            </div>
                            <div id="deviceCategoryInputFields"></div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-6">
                                    <button class="btn btn-primary btn-flat btn-disable-after-submit" type="submit">Save</button>
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

<div class="modal" id="canModal" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-times"></i></button>
                <h5 class="modal-title">CAN Protocol Configuration</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="canForm">
                            <!-- Protocol Selection -->
                            <div class="isCanEnable" style="display:none;">
                                <div style="margin:10px 0px;">
                                    <label for="curl" class="control-label padding-left-3">Can Channel<span class="require">*</span></label>
                                    <select class="form-control" id="can_channel" name="canConfiguration[canChannel]" required>
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
                                        <option value="">-- Select Baud Rate --</option>
                                        <option value="500">500 kbps</option>
                                        <option value="250">250 kbps</option>
                                    </select>
                                </div>
                                <div style="margin:10px 0px;">
                                    <label class="control-label">Can ID Type <span class="require">*</span></label>
                                    <select id="can_id_type" name="canConfiguration[can_id_type]" class="form-control" required>
                                        <option value="">-- Select Can ID --</option>
                                        <option value="0">Standard</option>
                                        <option value="1">Extended</option>
                                    </select>
                                </div>
                                <div style="margin:10px 0px;">
                                    <label for="curl" class="control-label padding-left-3">Can Protocol<span class="require">*</span></label>
                                    <select class="" id="can_protocol" name="canConfiguration[can_protocol]" onChange="selectedCanProtocol()" required>
                                        <option value="">-- Select Can Protocol -- </option>
                                        <option value="1">J1979</option>
                                        <option value="2">J1939</option>
                                        <option value="3">Custom CAN</option>
                                    </select>
                                </div>
                            </div>
                            <div id="dynamicCanFields"></div>
                            <!-- Submit -->
                            <!-- <button type="button" class="btn btn-success mt-4">Generate JSON</button> -->
                        </form>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-success mt-4" onclick="generateJSON()">Submit</button>
                    </div>
                </div>
                <!-- Output JSON -->
                <!-- <div class="mt-4">
               <label class="form-label">Generated JSON:</label>
               <textarea class="form-control" id="outputJson" rows="10" readonly></textarea>
               </div> -->
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
                        inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${fieldId}][]">`;

                        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                            validation.selectOptions.forEach((option, index1) => {
                                inputHtml += `<option value="${validation.selectValues[index1]}">${option}</option>`;
                            });
                        } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                            Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                inputHtml += `<option value="${validation.selectValues[index1]}">${value}</option>`;
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
                                    var selected = $(this).val();
                                    if (selected && selected.length > validation.maxSelectValue) {
                                        selected.splice(validation.maxSelectValue);
                                        $(this).select2("val", selected);
                                        alert("You can only select up to " + validation.maxSelectValue + " options.");
                                    }
                                });
                            }
                        }, 100);
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
        // $('#s2example-2').select2({
        //     // Configuration options
        // });
        $('#can_protocol').select2({
            placeholder: "Search and Select",
        });
        $("#user_id").select2({
            placeholder: 'Search and Select'
        });
        $("#s2example-2").select2({
            placeholder: 'Search and Select'
        });
        $("#templates").select2({
            placeholder: 'Search and Select'
        });
        //     $('#imei').bind('keyup paste', function() {
        //         this.value = this.value.replace(/[^0-9]/g, '');
        //     });
        //     $('#user_id').on('change', function(e) {
        //         var user_id = this.value;
        //         $.ajax({
        //             url: "{{url('admin/getusers')}}",
        //             type: 'POST',
        //             headers: {
        //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             },
        //             data: {
        //                 id: user_id
        //             },
        //             success: function(data) {
        //                 if (data['success']) {
        //                     $("#ip").val(data.userinfo.ip);
        //                     $("#port").val(data.userinfo.port);
        //                     $("#logs_interval").val(data.userinfo.logs_interval);
        //                     $("#sleep_interval").val(data.userinfo.sleep_interval);
        //                     $("#trans_interval").val(data.userinfo.transmission_interval);
        //                     $("#password").val(data.userinfo.LoginPassword);
        //                     if (data.userinfo.FOTA == 1) {
        //                         $('#fota option[value="1"]').attr("selected", "selected");
        //                     } else {
        //                         $('#fota option[value="0"]').attr("selected", "selected");
        //                     }
        //                     if (data.userinfo.Active_Status == 1) {
        //                         $('#Active_Status option[value="1"]').attr("selected", "selected");
        //                     } else {
        //                         $('#Active_Status option[value="0"]').attr("selected", "selected");

        //                     }
        //                 } else if (data['error']) {
        //                     alert(data['error']);
        //                 } else {
        //                     alert('Whoops Something went wrong!!');
        //                 }
        //             },
        //             error: function(data) {
        //                 alert(data.responseText);
        //             }

        //         });
        //     });
        //     $('#user_id, #firmware').on('change', function() {
        //         var userId = $('#user_id').val();
        //         var firmwareId = $('#firmware').val();
        //         if (userId && firmwareId) {
        //             checkModalNameExist(userId, firmwareId);
        //         }
        //     });
    });

    function getSelectedDeviceCategory() {
        let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-device-category') }}";
        $('#deviceCategoryInputFields').hide();
        $('#loading').show();
        var selectedDeviceCategoryId = $('#s2example-2').val();
        $('#deviceCategoryInputFields').html('');
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                id: selectedDeviceCategoryId
            },
            success: function(response) {
                let result = JSON.parse(response);
                let templates = JSON.parse(result.templates);
                let firmwares = JSON.parse(result.firmware);
                let dataFields = JSON.parse(result.dataFields);
                let canEnable = result.canEnable == 1 ? true : false;
                if (canEnable) {
                    $('.isCanEnable').show();
                }
                $('#templateInput').show();
                $('#FirmwareInput').show();
                $('#modalInput').show();
                $('#templates').empty();
                $('#firmware').empty();
                var selectedOptionText = $('#s2example-2 option:selected').text();
                $('#modelName').val(selectedOptionText);
                firmwares.forEach(firmware => {
                    var option = new Option(firmware.name, firmware.id, firmware.is_default == 1, firmware.is_default == 1);
                    $('#firmware').append(option);
                });
                templates.forEach((template) => {
                    var option = new Option(template.template_name, template.id, template.default_template == 1, template.default_template == 1);
                    $('#templates').append(option);
                });

                // Trigger change event after all options are appended
                $('#templates').trigger('change');
                let htmlContent = '';
                if (result.status == 200) {
                    let inputFields = JSON.parse(result.device_input);
                    inputFields.forEach((input, index) => {
                        htmlContent += '<input class="form-control" type="hidden"  name="idParameters[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '">';
                        if (input.type == 'select') {
                            let field = dataFields.filter((item) => item.fieldName.replace(/\s+/g, '_').toLowerCase() == input.key.replace(/\s+/g, '_').toLowerCase());
                            let config = JSON.parse(field[0].validationConfig);
                            htmlContent += '<div class="form-group">';
                            htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                            htmlContent += '<div class="col-lg-6">';
                            htmlContent += '<select class="form-control inputType" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? ' required' : '') + '>';
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
                                config.selectOptions.forEach((option, index) => {
                                    htmlContent += `<option value="${config.selectValues[index]}">${option}</option>`;
                                });
                            } else if (config.selectOptions && typeof config.selectOptions === 'object') {
                                Object.entries(config.selectOptions).forEach(([value, index1]) => {
                                    htmlContent += `<option value="${config.selectValues[index1]}">${value}</option>`;
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
                                        placeholder: "Select up to " + config.maxSelectValue + " options",
                                        width: "100%"
                                    });
                                    $select.on("change", function() {
                                        var selected = $(this).select2("val");
                                        if (selected && selected.length > config.maxSelectValue) {
                                            selected.splice(config.maxSelectValue);
                                            $(this).select2("val", selected);
                                            alert("You can only select up to " + config.maxSelectValue + " options.");
                                        }
                                    });
                                });
                            }, 100);
                        } else {
                            const validationConfig = dataFields[index].validationConfig ? JSON.parse(dataFields[index].validationConfig) : {};
                            if (input.key == "Password") {
                                htmlContent += '<div class="form-group">';
                                htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                                htmlContent += '<div class="col-lg-6">';
                                htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength ="' + input.numberRange?.min + '" maxlength="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + ' value="' + (input.default != null ? input.default : '') + '">';
                                htmlContent += '</div>';
                                htmlContent += '</div>';
                            } else {

                                let addClassTextArray = input?.type === 'text_array' ? 'text-array-space' : '';
                                let addClassIpUrl = input?.type === 'IP/URL' ? 'ip-url-space' : '';
                                htmlContent += '<div class="form-group">';
                                htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                                htmlContent += '<div class="col-lg-6">';
                                // if (inputs.type === 'number') {
                                //   if (validationConfig.numberInput?.min !== undefined) {
                                //     minValue = ` min="${validationConfig.numberInput.min}"`;
                                //   }
                                //   if (validationConfig.numberInput?.max !== undefined) {
                                //     maxValue = ` max="${validationConfig.numberInput.max}"`;
                                //   }
                                // //   html += minValue + maxValue;
                                // }

                                // // Text-based types: apply maxlength
                                // if (['text_array', 'text', 'IP/URL'].includes(inputs.type)) {
                                //   if (validationConfig.maxValueInput !== undefined) {
                                //     maxLength = ` maxlength="${validationConfig.maxValueInput}"`;
                                //   }
                                // //   html += maxLength;
                                // }
                                //htmlContent += '<input class="form-control inputType" type="'+ (input.type == 'text' ? 'maxlength="' + input['maxValueInput']['maxLength'] + '") + (input.type == 'number' ? 'number' : 'text') + '"  ' + (input.type == 'number' ? 'min ="' + input.numberRange?.min + '" max="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '"novalidate="novalidate"  id="' + input.key.replace(/\s+/g, '_').toLowerCase() + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input?.requiredFieldInput ?'required' : '') + ' value="'+(input.default != null ? input.default : '' )+'">';
                                htmlContent += '<input class="form-control inputType ' + addClassTextArray + ' ' + addClassIpUrl + '" ' + 'type="' + (input.type === 'number' ? 'number' : 'text') + '" ' + (['text_array', 'text', 'IP/URL'].includes(input.type) && validationConfig.maxValueInput ? 'maxlength="' + validationConfig.maxValueInput + '" ' : '') + (input.type === 'number' && input.numberRange ? 'min="' + validationConfig.numberInput.min + '" max="' + validationConfig.numberInput.max + '" ' : '') + 'placeholder="Enter ' + input.key + '" ' + 'novalidate="novalidate" ' + 'id="' + input.key.replace(/\s+/g, '_').toLowerCase() + '" ' + 'name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input?.requiredFieldInput ? 'required ' : '') + 'value="' + (input.default != null ? input.default : '') + '">';
                                htmlContent += '</div>';
                                htmlContent += '</div>';
                            }
                        }
                        // if (input.type == 'select') {
                        //     htmlContent += '<div class="form-group">';
                        //     htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        //     htmlContent += '<div class="col-lg-6">';
                        //     htmlContent += '<select class="form-control inputType" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + input.key + (input.requiredFieldInput ? 'requried' : '') + '>';
                        //     // htmlContent += '<option value="">Please Select</option>';
                        //     input?.selectOptions?.map((option) => {
                        //         htmlContent += '<option value="' + option.toLowerCase() + '">' + option + '</option>';
                        //     });
                        //     htmlContent += '</select>';
                        //     htmlContent += '</div>';
                        //     htmlContent += '</div>';
                        // } else {
                        //     if (input.key == "Password") {
                        //         htmlContent += '<div class="form-group">';
                        //         htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        //         htmlContent += '<div class="col-lg-6">';
                        //         htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength ="' + input.numberRange?.min + '" maxlength="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'requried' : '') + '>';
                        //         htmlContent += '</div>';
                        //         htmlContent += '</div>';
                        //     } else {
                        //         htmlContent += '<div class="form-group">';
                        //         htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        //         htmlContent += '<div class="col-lg-6">';
                        //         htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'min ="' + input.numberRange?.min + '" max="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'requried' : '') + '>';
                        //         htmlContent += '</div>';
                        //         htmlContent += '</div>';
                        //     }
                        // }
                    });

                    $('#deviceCategoryInputFields').append(htmlContent);
                } else {
                    $('#loading').hide();
                    $('#deviceCategoryInputFields').empty().append(htmlContent);
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

                    // Check if the element exists
                    if (element.length > 0) {
                        // Determine the type of element (input or select) and set the value
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