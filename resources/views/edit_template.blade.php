<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
$getDeviceInput = CommonHelper::getDeviceConfigurationInputs($template_info->device_category_id);
$configurations = json_decode($template_info->configurations);
?>
@extends('layouts.apps')
@section('content')

<style type="text/css">
    input[type="checkbox"] {

        width: 25px;
        /*Desired width*/

        height: 25px;
        /*Desired height*/

    }
</style>
<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!--======== Page Title and Breadcrumbs Start ========-->
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Settings</a></li>
                        <li><a href="/{{$url_type}}/view-template">View Settings</a></li>
                        <li class="active"><a href="#">Edit Settings</a></li>
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
                        <h2>Edit Template</h2>
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
                        <form class="validator form-horizontal " id="commentForm" method="post" action="/{{$url_type}}/update-template/{{$template_info->id}}">
                            @method('PATCH')
                            @csrf
                            @if(Auth::user()->user_type!=='Admin')
                            <input type="hidden" name="user_id" value="{{Auth()->id()}}">
                            @endif
                            <input type="hidden" name="id" value="{{$template_info->id}}">
                            @if(Auth::user()->user_type=='Admin')
                            @if($template_info->default_template=='0')
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3"><b>Mark as Default Template</b></label>
                                <div class="col-lg-6">
                                    <input type="checkbox" name="default_template" id="default_template" <?php echo ($template_info->default_template == '1' ? 'checked' : '') ?>>
                                </div>
                            </div>
                            @else($template_info->default_template=='1')
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3"><b>Mark as Default Template</b></label>
                                <div class="col-lg-6">
                                    <input type="checkbox" name="default_template" id="default_template" <?php echo ($template_info->default_template == '1' ? 'checked' : '') ?>>
                                </div>
                            </div>
                            @endif
                            @endif
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Template Name <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control " id="templates" type="text" name="template_name" value="{{$template_info->template_name}}" placeholder="Enter Template Name" required />
                                </div>
                            </div>
                            <!-- <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3 ">Device Category <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select class="" id="deviceCategory" name="deviceCategory" onChange="getSelectedDeviceCategory()">
                                        <option value="">Select an option</option>
                                        @foreach($getDeviceCategory as $deviceCategory)
                                        <option value="{{$deviceCategory->id}}" {{ $template_info->device_category_id == $deviceCategory->id ? 'selected' : '' }}>
                                            {{$deviceCategory->device_category_name}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> -->
                            @foreach($getDeviceInput as $inputs)
                            @if($inputs->type == 'select')
                            <div class="form-group">
                                <label class="control-label col-lg-3">{{$inputs->key}} @php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : ''; @endphp</label>
                                <div class="col-lg-6">
                                    <select class="form-control inputType" name="configuration[{{ strtolower(str_replace(' ', '_', $inputs->key)) }}]" @if(isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput)
                                        required
                                        @endif>
                                        <!-- <option value="">Please Select</option> -->
                                        @foreach($inputs->selectOptions as $option)
                                        <option <?php echo isset($configurations->{strtolower(str_replace(' ', '_', $inputs->key))}) && $configurations->{strtolower(str_replace(' ', '_', $inputs->key))} == strtolower($option->value) ? 'Selected' : ''; ?> value="{{ strtolower($option->value) }}">{{$option->option}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @else
                            @if($inputs->key == 'Password')
                            <div class="form-group">
                                <label for="{{$inputs->key}}" class="control-label col-lg-3">{{$inputs->key}} @php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : ''; @endphp</label>
                                <div class="col-lg-6">
                                    <input class="form-control"
                                        placeholder="Enter {{ $inputs->key }}"
                                        id="{{ strtolower(str_replace(' ', '_', $inputs->key)) }}"
                                        type="{{ isset($inputs->requiredFieldInput) && $inputs->type == 'number' ? 'number' : 'text' }}"
                                        name="configuration[{{ strtolower(str_replace(' ', '_', $inputs->key)) }}]"
                                        @if(isset($inputs->type) && $inputs->type == 'number')
                                    minlength="{{ $inputs->numberRange->min ?? '' }}"
                                    maxlength="{{ $inputs->numberRange->max ?? '' }}"
                                    @endif
                                    value="{{ isset($configurations->{strtolower(str_replace(' ', '_', $inputs->key))}) ? $configurations->{strtolower(str_replace(' ', '_', $inputs->key))} : '' }}"
                                    @if(isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput)
                                    required
                                    @endif
                                    />
                                </div>
                            </div>
                            @else
                            <div class="form-group">
                                <label for="{{$inputs->key}}" class="control-label col-lg-3">{{$inputs->key}} @php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : ''; @endphp</label>
                                <div class="col-lg-6">
                                    <input class="form-control"
                                        placeholder="Enter {{ $inputs->key }}"
                                        id="{{ strtolower(str_replace(' ', '_', $inputs->key)) }}"
                                        type="{{ isset($inputs->requiredFieldInput) && $inputs->type == 'number' ? 'number' : 'text' }}"
                                        name="configuration[{{ strtolower(str_replace(' ', '_', $inputs->key)) }}]"
                                        @if(isset($inputs->type) && $inputs->type == 'number')
                                    min="{{ $inputs->numberRange->min ?? '' }}"
                                    max="{{ $inputs->numberRange->max ?? '' }}"
                                    @endif
                                    value="{{ isset($configurations->{strtolower(str_replace(' ', '_', $inputs->key))}) ? $configurations->{strtolower(str_replace(' ', '_', $inputs->key))} : '' }}"
                                    @if(isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput)
                                    required
                                    @endif
                                    />
                                </div>
                            </div>
                            @endif
                            @endif

                            @endforeach

                            @if(Auth::user()->user_type=='Admin')
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Ping interval <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" placeholder="Enter Ping Interval" id="ping_interval" type="Number" name="configuration[ping_interval]" value="{{isset($configurations->ping_interval) ?$configurations->ping_interval : '4'}}" onkeypress="return blockSpecialCharTransmission(event)" required />
                                </div>
                            </div>
                            @endif
                            @if(Auth::user()->user_type=='Admin')
                            <div class="form-group ">
                                <label for="curl" class="control-label col-lg-3">Template Edit Permission</label>
                                <div class="col-lg-6">
                                    <label>Enable</label>
                                    <input
                                        type="radio"
                                        name="configuration[is_editable]"
                                        value="1"
                                        style="height:20px; width:20px; vertical-align: middle;"
                                        {{ isset($configurations->is_editable) && $configurations->is_editable == '1' ? 'checked' : '' }}>

                                    <label>Disable</label>
                                    <input
                                        type="radio"
                                        name="configuration[is_editable]"
                                        value="0"
                                        style="height:20px; width:20px; vertical-align: middle;"
                                        {{ isset($configurations->is_editable) &&  $configurations->is_editable == '0' ? 'checked' : '' }}>
                                </div>

                            </div>
                            @endif
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-6">
                                    <button class="btn btn-primary btn-flat" type="submit">Update</button>
                                    <input type="hidden" id="deviceCategory" name="deviceCategory" value="{{$template_info->device_category_id}}" />
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
<script>
    $(document).ready(function() {
        $('#deviceCategory').select2({
            placeholder: "Search and Select",
        });
    })
</script>