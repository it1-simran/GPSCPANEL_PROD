<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content');
<meta name="csrf-token" content="{{ csrf_token() }}">
<!--main content start-->
<div class="modal" id="imeiPreviewModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title"><strong>IMEI List</strong></h4>
            </div>
            <div class="bg-danger text-white padding-10" id="error_msg_imei"></div>
            <div class="modal-body">
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
                @if(Auth::user()->user_type=='Admin')
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
                @endif
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-primary btn-raised submit_sel_imei"><i class="fa fa-check"></i> Import Selected IMEI Button</button>
            </div>
        </div>
    </div>
</div>
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Device Management</a></li>
                        <li class="active"><a href="#">Assign Device</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Assign Device</h2>
                        <div class="clearfix"></div>
                    </div>
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
                        <form class="validator form-horizontal " id="commentForm" method="post" action="{{url('support/submit-Multipledevice')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="imeifields">
                            </div>
                            <div class="form-group ">
                                <label for="cname" class="control-label col-lg-3">Account <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    @if(Auth::user()->user_type == 'Support')
                                    <input type="hidden" class="selectDupImeiType" value="overwrite" name="dup_type">
                                    @endif
                                    <select class="" id="user_id" name="user_id" required>
                                        @if(count($users) > 0)
                                        <option value="">Select User</option>
                                        @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                        @endforeach
                                        @else
                                        <option value="">No User Found</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div id="isUserSelected" style="display:none;">
                                <div class="form-group ">
                                    <label for="curl" class="control-label col-lg-3 ">Device Category <span class="require">*</span></label>
                                    <div class="col-lg-6">
                                        <select id="s2example-2" class="form-control" name="deviceCategory" onChange="getSelectedDeviceCategory()" required="required">
                                            <option value=""> </option>
                                            @foreach($getDeviceCategory as $deviceCategory)
                                            <option value="{{$deviceCategory->id}}">{{$deviceCategory->device_category_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="cemail" class="control-label col-lg-3">Import Excel File <span class="require">*</span></label>
                                    <div class="col-lg-6">
                                        <div class="d-flex" style="align-items: anchor-center;">
                                            <input type="file" name="excel_file" id="excel_file" class="form-control reqfield bordered-1 padding-4 reqfield rounded-md" required />
                                            <a href="#" data-toggle="modal" data-target="#excelFormatModal" class="margin-left-4"> <i class="fa fa-info-circle"></i> </a>
                                        </div>
                                        <span class="req_error_file text-danger display-block"></span>
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
                                <div class="form-group " id="templateInput" style='display:none;'>
                                    <label for="curl" class="control-label col-lg-3 ">Templates <span class="require">*</span></label>
                                    <div class="col-lg-6">
                                        <select id="templates" name="templates" id="templates" onChange="getTemplateConfiguration()" required>
                                            <option value=""> </option>
                                        </select>
                                        <span class="req_error text-danger display-block"></span>
                                    </div>
                                </div>
                                <!-- @if(Auth::user()->user_type=='Admin')
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
                                @endif -->
                                <div id='deviceCategoryInputFields' style='display:none;'></div>
                                <div class="form-group">
                                    <div class="col-lg-offset-3 col-lg-6">
                                        <!-- <button class="btn btn-primary btn-flat submitMultipleDeviceSupport btn-disable-after-submit" type="button">Save</button> -->
                                        <button
                                            class="btn btn-primary btn-flat submitMultipleDeviceSupport btn-disable-after-submit"
                                            type="button">
                                            <span class="btn-text">Save</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
@stop
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        $("#user_id").select2({
            placeholder: 'Search and Select'
        });
        $("#templates").select2({
            placeholder: 'Search and Select'
        });
        $('#imei').bind('keyup paste', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        $('#user_id').on('change', function(e) {
            let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'Support' ? 'support' : 'reseller')) . '/getusers') }}";

            var user_id = this.value;
            if (user_id == '') {
                $('#isUserSelected').hide();
                return false;
            }
            $.ajax({
                url: actionUrl,
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
                        $('#isUserSelected').show();
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
        $('#user_id, #firmware').on('change', function() {
            var userId = $('#user_id').val();
            var firmwareId = $('#firmware').val();
            if (userId && firmwareId) {
                checkModalNameExist(userId, firmwareId);
            }
        });
    });

    function checkModalNameExist(userId, firmwareId) {
        let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'Support' ? 'support' : 'reseller')) . '/get-model-name') }}";


        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                user_id: userId,
                firmware_id: firmwareId
            },
            success: function(response) {
                let result = JSON.parse(response);
                console.log('Different AJAX result:', result);
                if (result.status == 200) {
                    if (result.modalList !== null && result.modalList !== undefined) {
                        let modal = JSON.parse(result.modalList);
                        console.log("modal", modal);
                        if (modal != null) {
                            $('#modelName').val(modal.name);
                            $('#VendorId').val(modal.vendorId);
                            $('#VendorId').show();
                            $(".vendor_error").hide()
                            $('#modelName').show();
                            $(".modelName_error").hide();
                            $('.btn-disable-after-submit').attr('disabled', false);
                        } else {
                            $('#modelName').hide();
                            $('#VendorId').hide();
                            $(".modelName_error").show().html('Model Name is not Assigned . Please contact with Administrator');
                            $(".vendor_error").show().html('Vendor ID is not Assigned . Please contact with Administrator');
                            $('.btn-disable-after-submit').attr('disabled', true);
                        }
                    } else {
                        $('#modelName').hide();
                        $(".modelName_error").show();

                        $('.btn-disable-after-submit').attr('disabled', true);
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

    function getSelectedDeviceCategory() {
        $('#loading').show();
        let actionUrl = "{{ url('support/get-device-category')}}";
        var selectedDeviceCategoryId = $('#s2example-2').val();
        var userId = $('#user_id').val();
        $('#deviceCategoryInputFields').html('');
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                id: selectedDeviceCategoryId,
                userId: userId
            },
            success: function(response) {
                let result = JSON.parse(response);
                let templates = JSON.parse(result.templates);
                let firmwares = JSON.parse(result.firmware);
                let dataFields = JSON.parse(result.dataFields);
                var selectedOptionText = $('#s2example-2 option:selected').text();
                $('#modelName').val(selectedOptionText);
                // if ($('#user_id').val() == "") {
                $('#templateInput').show();
                // }
                // $('#FirmwareInput').show();
                // $('#modalInput').show();
                // $('#VendorID').show();

                $('#templates').empty();
                $('#firmware').empty();

                firmwares.forEach(firmware => {
                    var option = new Option(firmware.name, firmware.id, firmware.is_default == 1, firmware.is_default == 1);
                    $('#firmware').append(option);
                });
                // if ($('#user_id').val() == "") {
                templates.forEach((template) => {
                    var option = new Option(template.template_name, template.id, template.default_template == 1, template.default_template == 1);
                    $('#templates').append(option);
                    if (templates.length > 0) {
                        if (template.id_user == userId) {
                            $('#templates').val(template.id).trigger('change.select2');
                        } else {
                            $('#templates').val(templates[0].id).trigger('change.select2');
                        }
                    }
                });
                $('#templates').trigger('change');

                // }
                let htmlContent = '';
                // if ($('#user_id').val() == "") {
                if (result.status == 200) {
                    let inputFields = JSON.parse(result.device_input);
                    console.log("inputFields ==>", inputFields);
                    inputFields.forEach((input, index) => {
                        htmlContent += '<input class="form-control" type="hidden"  name="idParameters[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="">';
                        if (input.type == 'select') {
                            let field = dataFields.filter((item) => item.fieldName.replace(/\s+/g, '_').toLowerCase() == input.key.replace(/\s+/g, '_').toLowerCase());
                            let config = JSON.parse(field[0].validationConfig);
                            htmlContent += '<div class="form-group">';
                            htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                            htmlContent += '<div class="col-lg-6">';
                            htmlContent += '<select class="form-control inputType" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                            if (config?.selectOptions && config?.selectValues) {
                                config.selectOptions.forEach((option, index) => {
                                    const value = config.selectValues[index] ?? '';
                                    htmlContent += `<option value="${value}">${option}</option>`;
                                });
                            }
                            htmlContent += '</select>';
                            htmlContent += '</div>';
                            htmlContent += '</div>';
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
                    // if ($('#user_id').val() == "") {
                    $('#deviceCategoryInputFields').append(htmlContent);
                    // }
                } else {
                    $('#loading').hide();
                    $('#deviceCategoryInputFields').empty();
                    alert(result.message);

                }
                // } else {
                //     $('#deviceCategoryInputFields').empty();
                // }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    }

    function getTemplateConfiguration() {
        let actionUrl = "{{ url(Auth::user()->user_type == 'Admin' ? 'admin/get-template-configuration' :(Auth::user()->user_type == 'Support' ? 'support/get-template-configuration' : 'reseller/get-template-configuration'))}}";
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
                    if (element.length > 0) {
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