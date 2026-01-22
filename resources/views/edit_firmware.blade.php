<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
$config = json_decode($firmware->configurations,true);   
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
                        <li><a href="#">Firmware</a></li>
                        <li><a href="#">View Firmware</a></li>
                        <li class="active"><a href="#">Edit Firmware</a></li>
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
                        <h2>Edit Firmware</h2>
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
                        <div class="col-sm-12 alert alert-success success_msg" role="alert" style="display:none"></div>
                        <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                        <form class="validator form-horizontal " method="post" action="/admin/update-firmware/{{$firmware->id}}" enctype="multipart/form-data">
                            @csrf
                            @if(Auth::user()->user_type!=='Admin')
                            <input type="hidden" name="user_id" value="{{Auth()->id()}}">
                            @endif
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Firmware Name <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control " type="text" placeholder="Enter Firmware Name" name="name" required value="{{isset($firmware->name)? $firmware->name : ''}}" />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3 ">Device Category <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select class="" id="deviceCategory" name="deviceCategory">
                                        <option value=""> </option>
                                        @foreach($getDeviceCategory as $deviceCategory)
                                        <option value="{{ $deviceCategory->id }}" {{ isset($firmware->device_category_id) && $firmware->device_category_id == $deviceCategory->id ? 'selected' : '' }}>
                                            {{ $deviceCategory->device_category_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">State <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select class="" id="state" name="state">
                                        
                                    <option value=""> </option>
                
                                        @foreach($states as $state)
                                        <option value="{{$state->name}}" {{ isset($config['state']) && $config['state'] === $state->name ? 'Selected' : '' }}>{{$state->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Esim <span class="require">*</span></label>
                                <div class="col-lg-6">

                                    <select id="esim" name="esim">
                                        @foreach($esim as $sim)
                                        <optgroup  label="{{$sim->name}}">
                                            <option {{ isset($config['esim']) && $config['esim'] == $sim->profile_1? 'selected' : '' }} value="{{$sim->profile_1}}">{{$sim->profile_1}}</option>
                                            <option {{ isset($config['esim']) && $config['esim'] == $sim->profile_2? 'selected' : '' }}  value="{{$sim->profile_2}}">{{$sim->profile_2}}</option>
                                        </optgroup>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Backend <span class="require">*</span></label>  
                                <div class="col-lg-6">
                                    <select id="backend" name="backend">
                                        @foreach($backend as $back)
                                        <option {{ isset($config['backend']) && $config['backend'] == $back->id? 'selected' : '' }} value="{{$back->id}}">{{$back->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="cemail" class="control-label col-lg-3">Firmware File <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    @if(isset($config['filename']))
                                        <!-- Display the existing file -->
                                        <div>
                                            <p>Current file: <a href="{{ asset('fw/' . $config['filename']) }}" target="_blank">{{ basename($config['filename']) }}</a></p>
                                        </div>
                                    @endif
                                    <input type="file" name="firmwareFile" id="firmwareFile" accept=".bin" class="reqfield" />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Version <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control " type="text" placeholder="Firmware version" name="firmware_version" value="{{ isset($config['version'])? $config['version'] : '' }}" required />
                                </div>
                            </div>
                            <div id="loading" class="bgx-loading" style="display:none;">
                                <img src="/assets/icons/loader.gif" alt="Loading..." />
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-6">
                                    <button class="btn btn-primary btn-flat btn-disable-after-submit" type="submit">Save</button>
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javaScript">
    $(document).ready(function(){
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
        let inputValue = $(this).val().trim();
        let inputType = $(this).attr('type');
        let inputName = $(this).attr('name');
        let label = $(this).closest('.form-group').find('.control-label').text().trim();

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
    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'Reseller' ? 'reseller' : 'user'))) }}/get-device-category";
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
                let inputFields = JSON.parse(result.device_input);
                inputFields.forEach((input, index) => {
                    if (input.type == 'select') {
                        htmlContent += '<div class="form-group">';
                        htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                        htmlContent += '<div class="col-lg-6">';
                        htmlContent += '<select class="form-control inputType" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? ' required' : '') + '>';
                        // htmlContent += '<option value="">Please Select</option>';

                        input?.selectOptions?.map((option) => {
                            htmlContent +=`<option ${input?.default?.toLowerCase() === option['value'] ? "selected" : ""} value="${option['value']}">${option['option']}</option>`;
                        });
                        htmlContent += '</select>';
                        htmlContent += '</div>';
                        htmlContent += '</div>';
                    } else {
                        if(input.key == "Password"){
                            htmlContent += '<div class="form-group">';
                            htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                            htmlContent += '<div class="col-lg-6">';
                            htmlContent += '<input class="form-control" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength ="' + input.numberRange?.min + '" maxlength="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + ' value="'+(input.default != null ? input.default : '' )+'">';
                            htmlContent += '</div>';
                            htmlContent += '</div>';
                        }else{
                            htmlContent += '<div class="form-group">';
                            htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                            htmlContent += '<div class="col-lg-6">';
                            htmlContent += '<input class="form-control inputType" type="' + (input.type == 'number' ? 'number' : 'text') + '"  ' + (input.type == 'number' ? 'min ="' + input.numberRange?.min + '" max="' + input.numberRange?.max + '"' : '') + '  placeholder="Enter ' + input.key + '"novalidate="novalidate"  id="' + input.key.replace(/\s+/g, '_').toLowerCase() + '" name="configuration[' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input?.requiredFieldInput ?'required' : '') + ' value="'+(input.default != null ? input.default : '' )+'">';
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
    $('#state').select2({
        placeholder: "Search and Select",
    });
    $('#esim').select2({
        placeholder: "Search and Select",
    });
    $('#backend').select2({
        placeholder: "Search and Select",
    });

    
});
</script>
<!--======== Main Content End ========-->
@stop