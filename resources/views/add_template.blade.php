<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
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
                        <li><a href="#">Settings</a></li>
                        <li class="active"><a href="#">Add Settings</a></li>
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
                        <h2>Add Settings</h2>
                        <div class="clearfix"></div>
                    </div>
                    <!--/.c_title-->
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
                        <div class="col-sm-12 alert alert-success success_msg" role="alert" style="display:none"></div>
                        <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                        <form class="validator form-horizontal " id="settingForm" method="post" action=""
                            onsubmit="return false">
                            @csrf
                            @if(Auth::user()->user_type!=='Admin')
                            <input type="hidden" name="user_id" value="{{Auth()->id()}}">
                            @endif
                            <div class="form-group d-flex">
                                <label for="curl" class="control-label col-lg-3"><b>Mark as Default
                                        Template</b></label>
                                <div class="col-lg-6">
                                    <input type="checkbox" class='default_template_checkbnox' name="default_template"
                                        id="default_template">
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Template Name <span
                                        class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control " type="text" placeholder="Enter Template Name"
                                        name="template_name" required />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3 ">Device Category <span
                                        class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select class="" id="deviceCategory" name="deviceCategory"
                                        onChange="getSelectedDeviceCategory()">
                                        <option value=""> </option>
                                        @foreach($getDeviceCategory as $deviceCategory)
                                        <option value="{{$deviceCategory->id}}">
                                            {{$deviceCategory->device_category_name}}
                                        </option>
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
                                <label for="firmware" class="control-label col-lg-3 " required>Firmware <span
                                        class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="firmware" name="configuration[firmware_id]" class="form-control"
                                        placeholder='Search and Select'>
                                        <option value=""> </option>
                                    </select>
                                </div>
                            </div>
                            <div id='deviceCategoryInputFields'></div>
                            @if(Auth::user()->user_type=='Admin')
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Ping interval <span
                                        class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" placeholder="Enter Ping Interval" id="ping_interval"
                                        type="Number" name="configuration[ping_interval]" value="4"
                                        onkeypress="return blockSpecialCharTransmission(event)" required />
                                </div>
                            </div>
                            @endif
                            @if(Auth::user()->user_type=='Admin')
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Device Edit Permission</label>
                                <div class="col-lg-6">
                                    <label>Enable</label>
                                    <input checked type="radio" name="configuration[is_editable]" value="1"
                                        style="height:20px; width:20px; vertical-align: middle;">
                                    <label>Disable</label>
                                    <input type="radio" name="configuration[is_editable]" value="0"
                                        style="height:20px; width:20px; vertical-align: middle;">
                                </div>
                            </div>
                            @endif
                            <div id="loading" class="bgx-loading" style="display:none;">
                                <img src="/assets/icons/loader.gif" alt="Loading..." />
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-6">
                                    <button class="btn btn-primary btn-flat btn-disable-after-submit"
                                        type="submit">Save</button>
                                </div>
                            </div>
                        </form>
                        <hr>
                    </div>
                </div>
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
                                    <select id="can_channel" name="canConfiguration[canChannel]" required>
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
                        </form>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-success mt-4" onclick="generateJSON()">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javaScript">
    function openCanModal() {
      $('#canModal').modal('show');
    }
    function selectedCanProtocol() {
      let canProtocolValue = $('#can_protocol').val();
      if (!canProtocolValue) return;
      let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'Reseller' ? 'reseller' : (Auth::user()->user_type == 'Support' ? 'support' : 'user')))) }}/get-can-protocol-fields";
   
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
                  let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
                  let attr = `id="${fieldId}" name="canConfiguration[${fieldId}]" class="form-control ip-url-space"  placeholder="Enter ${field.fieldName}"`;
                  inputHtml += `<input type="hidden" name="CanParametersType[${fieldId }]" value="${ inputType}" />`;
                  
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
                    }  else if (inputType == 'multiselect') {
                        inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${fieldId}][]">`;
                
                        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                            validation.selectOptions.forEach((option,index) => {
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
                
                        // Apply Select2
                        setTimeout(() => {
                            var $select = $('#' + fieldId);
                        
                            if ($select.length) {
                                // Initialize Select2
                                $select.select2({
                                    placeholder: "Select up to 3 options",
                                    width: "100%"
                                });
                        
                                // Handle selection change
                                $select.on("change", function () {
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
                                "<input type='text' maxlength='8' id='" + fieldId + index + "' name='canConfiguration[" + fieldId + "][]' class='form-control text-array-space me-2' placeholder='Enter " + field.fieldName + "' value='" + val.trim() + "' />" +
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
   $(document).ready(function(){
    
      $('#can_protocol').select2({
          placeholder: "Search and Select",
      });
      $('#can_channel').select2({
            placeholder: "Search and Select",
      });
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
      $('#settingForm').submit(function(event){
          event.preventDefault();
          $('.error_msg').empty().hide();
          $('.success_msg').empty().hide();
          let deviceCategory = $('#deviceCategory').val();
   
          if (!deviceCategory) {
              alert("Please select a device category.");
              return; 
          }
          let formIsValid = true;
              $(this).find('input[required], select[required]').each(function() {
              let inputValue = $(this).val();
              let inputType = $(this).attr('type');
              let inputName = $(this).attr('name');
              let label = $(this).closest('.form-group').find('.control-label').text();
   
              if(inputValue == ""){
                  error_msg = 'all fields are required';
                  formIsValid = false;
                  return false;
              }
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
          if(formIsValid){
              let actionUrl = "/{{$url_type}}/store-template";
              let formData = $(this).serialize();
              $.ajax({
                  type: "POST",
                  url: actionUrl,
                  data: formData,
                  dataType: 'json',
                  success: function(response){
                      let result = response;
                      if(result.status == 200){
                          $('.btn-disable-after-submit').attr("disabled",true);
                          $('.success_msg').append(result.status_msg).show();
                          document.documentElement.scrollIntoView({
                              behavior: 'smooth',
                              block: 'start'
                          });
                          // window.location.reload();
                      }
                  },
                  error: function(xhr, status, error){
                      let errors = JSON.parse(xhr.responseText);  
                      $('.error_msg').append(errors.errors).show();
                      document.documentElement.scrollIntoView({
                              behavior: 'smooth',
                              block: 'start'
                          });
   
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
   
   function getSelectedDeviceCategory() {
      let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'Reseller' ? 'reseller' : (Auth::user()->user_type == 'Support' ? 'support' : 'user')))) }}/get-device-category";
      $('#loading').show();
      var selectedDeviceCategoryId = $('#deviceCategory').val();
      $('#deviceCategoryInputFields').html(''); 
      $.ajax({
          url: actionUrl,
          type: "POST",
          data: {
              id: selectedDeviceCategoryId
          },
          success: function(response) {
              let result = JSON.parse(response);
   
              let htmlContent = '';
                  if (result.status == 200) {
                      $('#FirmwareInput').show();
                      let inputFields = JSON.parse(result.device_input);
                      console.log("inputFields ===>", inputFields);
                      let firmwares = JSON.parse(result.firmware);
                      let dataFields = JSON.parse(result.dataFields);
                      let canEnable = result.canEnable == 1 ? true : false;
                      if (canEnable) {
                          $('.isCanEnable').show();
                      }
                      firmwares.forEach(firmware => {
                          var option = new Option(firmware.name, firmware.id, firmware.is_default == 1, firmware.is_default == 1);
                          $('#firmware').append(option);
                      });
                  inputFields.forEach((input, index) => {
                       htmlContent += '<input class="form-control" type="hidden"  name="idParameters[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="'+input.id+'">';
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
                                    placeholder: "Select up to "+config.maxSelectValue+" options",
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
                        if(input.key == "Password"){
                            htmlContent += '<div class="form-group">';
                            htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                            htmlContent += '<div class="col-lg-6">';
                            htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength ="' + input.numberRange?.min + '" maxlength="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + ' value="'+(input.default != null ? input.default : '' )+'">';
                            htmlContent += '</div>';
                            htmlContent += '</div>';
                        }else{
                            
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
                            htmlContent += '<input class="form-control inputType '+addClassTextArray +' '+addClassIpUrl+'" ' + 'type="' + (input.type === 'number' ? 'number' : 'text') + '" ' + (['text_array', 'text', 'IP/URL'].includes(input.type) && validationConfig.maxValueInput ? 'maxlength="' + validationConfig.maxValueInput + '" ' : '') + (input.type === 'number' && input.numberRange ? 'min="' + validationConfig.numberInput.min + '" max="' + validationConfig.numberInput.max + '" ' : '') + 'placeholder="Enter ' + input.key + '" ' + 'novalidate="novalidate" ' + 'id="' + input.key.replace(/\s+/g, '_').toLowerCase() + '" ' + 'name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +(input?.requiredFieldInput ? 'required ' : '') + 'value="' + (input.default != null ? input.default : '') + '">' ;
                            htmlContent += '</div>';
                            htmlContent += '</div>';  
                        }
                    }
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
   
   $(document).ready(function(){
      $('#container').on('input', '.inputType', function() {
          var value = $(this).val();
          $(this).val(value.replace(/\s/g, '')); // Remove all spaces
      });
      $('#deviceCategory').select2({
          placeholder: "Search and Select",
      }); 
   });
</script>
<!--======== Main Content End ========-->
@stop