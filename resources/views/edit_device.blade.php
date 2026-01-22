 <?php

    use App\Helper\CommonHelper;

    $getDeviceCategory = CommonHelper::getDeviceCategory();
    $getDeviceInput = CommonHelper::getDeviceConfigurationInputs($device_info->device_category_id);
    $configurations = json_decode($device_info->configurations);



    ?>
 @extends('layouts.apps')
 @section('content')
 <!--main content start-->
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
 <section id="main-content">
     <section class="wrapper">
         <!--======== Page Title and Breadcrumbs Start ========-->
         <div class="top-page-header">
             <div class="page-breadcrumb">
                 <nav class="c_breadcrumbs">
                     <ul>
                         <li><a href="#">Device Management</a></li>
                         <li class="active"><a href="#">Edit Device</a></li>
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
                         <h2>Edit Device</h2>
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
                         <form class="validator form-horizontal " id="commentForm" method="post" action="/{{$url_type}}/update-device/{{$device_info->id}}">
                             @method('PATCH')
                             @csrf
                             <input type="hidden" name="id" value="{{$device_info->id}}">
                             <input type="hidden" name="prev_uid" class="prev_uid" value="{{ $uid }}">
                             @if(Auth::user()->user_type !='User')
                             <div class="form-group ">
                                 @if(Auth::user()->user_type=='Admin')
                                 <label for="cname" class="control-label col-lg-3"><?= ($device_info->user_id != '' ? 'Account' : 'Account') ?></label>
                                 @elseif(Auth::user()->user_type=='Reseller')
                                 <label for="cname" class="control-label col-lg-3"><?= ($device_info->user_id != Auth::user()->id ? 'Account' : 'Account') ?></label>
                                 @elseif(Auth::user()->user_type=='User')
                                 <label for="cname" class="control-label col-lg-3">User</label>
                                 @endif
                                 <div class="col-lg-6">
                                     <select class="" id="editDeviceUsers" name="user_id">
                                         @if(count($users) > 0)
                                         <!-- @if(Auth::user()->user_type=='Admin')
                                         <option value=""><?= ($device_info->user_id != '' ? 'Unassigned' : 'Unassigned') ?></option>
                                         @elseif(Auth::user()->user_type=='Reseller')
                                         <option value=""><?= ($device_info->user_id != Auth::user()->id ? 'Unassigned' : 'Unassigned') ?></option>
                                         @endif -->
                                         @foreach($users as $user)
                                         <option value="{{$user->id}}" <?= ($uid == $user->id ? 'selected' : '') ?>>{{$user->name}}</option>
                                         @endforeach
                                         @else
                                         <option>No User Found</option>
                                         @endif
                                     </select>
                                 </div>

                             </div>
                             @endif
                             <!-- <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3 " required>Templates <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <select id="templates" name="configuration[template]" placeholder='Search and Select' id="templates" onChange="getTemplateConfiguration()">
                                         <option value=""> </option>

                                         @foreach($template_info as $template)
                                         @if($template->device_category_id == $device_info->device_category_id)
                                         <option {{ isset($configurations->template) && $configurations->template == $template->id ? 'selected' : '' }} value='{{ $template->id }}'>{{ $template->template_name }}</option>
                                         @endif
                                         @endforeach

                                     </select>
                                 </div>
                             </div> -->
                             <div class="form-group ">
                                 <label for="cemail" class="control-label col-lg-3">Name (optional)</label>
                                 <div class="col-lg-6">
                                     <input class="form-control " id="name" type="text" name="name" value="{{$device_info->name}}" placeholder="Enter Name">
                                 </div>
                             </div>
                             @if(Auth::user()->user_type=='Admin')
                             <div class="form-group">
                                 <label for="cemail" class="control-label col-lg-3">IMEI <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <div class="input-group  d-flex">
                                         <input class="form-control col-lg-10" id="imei" type="text" maxlength="15" name="imei" placeholder="Enter IMEI Number" value="{{$device_info->imei}}" readonly />
                                         <div class="input-group-append col-lg-2">
                                             <button class="btn btn-secondary" type="button" id="editImeiBtn">Edit</button>
                                         </div>
                                     </div>
                                 </div>
                             </div>

                             @else(Auth::user()->user_type=='user' || Auth::user()->user_type=='Reseller')
                             <div class="form-group ">
                                 <label for="cemail" class="control-label col-lg-3">IMEI <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <input class="form-control " id="imei" type="text" maxlength="15" name="imei" placeholder="Enter IMEI Number" value="{{$device_info->imei}}" onkeypress="return blockSpecialChar(event)" readonly />
                                 </div>
                             </div>
                             @endif
                             @foreach($getDeviceInput as $inputs)
                             @if($inputs->type == 'select')
                             <div class="form-group">
                                 <label class="control-label col-lg-3">{{$inputs->key}} @php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : ''; @endphp</label>
                                 <div class="col-lg-6">
                                     <select class="form-control inputType" name="configuration[{{ strtolower(str_replace(' ', '_', $inputs->key)) }}]"
                                         <?php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? 'required' : ''; ?>>
                                         <!-- <option value="">Please Select</option> -->
                                         @foreach($inputs->selectOptions as $option)
                                         <option <?php echo isset($configurations->{strtolower(str_replace(' ', '_', $inputs->key))}) && $configurations->{strtolower(str_replace(' ', '_', $inputs->key))} == strtolower($option) ? 'Selected' : ''; ?> value="{{ strtolower($option) }}">{{$option}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                             @else
                             @if($inputs->key == "Password")
                             <div class="form-group">
                                 <label for="ip" class="control-label col-lg-3">{{$inputs->key}} @php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : ''; @endphp</label>
                                 <div class="col-lg-6">
                                     <input class="form-control"
                                         placeholder="Enter {{ $inputs->key }}"
                                         id="{{ strtolower(str_replace(' ', '_', $inputs->key)) }}"
                                         type="{{ isset($inputs->requiredFieldInput) && $inputs->type == 'number' ? 'number' : 'text' }}"
                                         name="configuration[{{ strtolower(str_replace(' ', '_', $inputs->key)) }}]"
                                         {{ isset($inputs->type) && $inputs->type == 'number' ? 'minlength=' . ($inputs->numberRange->min ?? '') . ' maxlength=' . ($inputs->numberRange->max ?? '') : '' }}
                                         value="{{ isset($configurations->{strtolower(str_replace(' ', '_', $inputs->key))}) ? $configurations->{strtolower(str_replace(' ', '_', $inputs->key))} : '' }}"
                                         <?php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? 'required' : ''; ?> />

                                 </div>
                             </div>
                             @else
                             <div class="form-group">
                                 <label for="ip" class="control-label col-lg-3">{{$inputs->key}} @php echo isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput ? '<span class="require">*</span>' : ''; @endphp</label>
                                 <div class="col-lg-6">
                                     <input
                                         class="form-control"
                                         placeholder="Enter {{ $inputs->key }}"
                                         id="{{ strtolower(str_replace(' ', '_', $inputs->key)) }}"
                                         type="{{ isset($inputs->type) && $inputs->type === 'number' ? 'number' : 'text' }}"
                                         name="configuration[{{ strtolower(str_replace(' ', '_', $inputs->key)) }}]"
                                         @if (isset($inputs->numberRange->min))
                                     min="{{ $inputs->numberRange->min}}"
                                     max="{{ $inputs->numberRange->max}}"
                                     @endif
                                     value="{{ isset($configurations->{strtolower(str_replace(' ', '_', $inputs->key))}) ? $configurations->{strtolower(str_replace(' ', '_', $inputs->key))} : '' }}"
                                     @if (isset($inputs->requiredFieldInput) && $inputs->requiredFieldInput)
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
                                     <input class="form-control" placeholder="Enter Ping Interval" id="ping_interval" type="Number" name="configuration[ping_interval]" value="{{$configurations->ping_interval}}" onkeypress="return blockSpecialCharTransmission(event)" required />
                                 </div>
                             </div>
                             @endif
                             @if(Auth::user()->user_type=='User')
                             <input class="form-control" id="ping_interval" type="hidden" name="configuration[ping_interval]" value="{{$configurations->ping_interval}}" />
                             @endif
                             @if(Auth::user()->user_type=='Admin')
                             <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3">Device Edit Permission</label>
                                 <div class="col-lg-6">
                                     <label>Enable</label>
                                     <input type="radio" name="configuration[is_editable]" value="1" {{($configurations->is_editable=='1'?'checked':'')}} style="height:20px; width:20px; vertical-align: middle;">
                                     <label>Disable</label>
                                     <input type="radio" name="configuration[is_editable]" value="0" {{($configurations->is_editable=='0'?'checked':'')}} style="height:20px; width:20px; vertical-align: middle;">
                                 </div>
                             </div>
                             @endif
                             <div class="form-group">
                                 <div class="col-lg-offset-3 col-lg-6">
                                     <button class="btn btn-primary btn-flat updateDeviceBtn" type="submit">Update</button>
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
 <script type='text/javascript'>
     $(document).ready(function() {
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
         $('#commentForm').submit(function(event) {
             $('.error_msg').html('').hide();
             let imei = $("#imei").val();
             // Prevent form submission if validation fails
             if (!isValidIMEI(imei)) {
                 event.preventDefault();
                 $('.error_msg').append('Imei No is Invalid').show();
                 document.documentElement.scrollIntoView({
                     behavior: 'smooth',
                     block: 'start'
                 });
                 return;
             }
         });
     });

     function isValidIMEI(imei) {
         // Clean the IMEI by removing any non-digit characters
         imei = imei.replace(/[^\d]/g, '');

         // Check if IMEI is exactly 15 digits long
         if (imei.length !== 15) {
             return false;
         }

         // Double every second digit from right to left, and sum the digits
         let sum = 0;
         for (let i = 0; i < 15; i++) {
             let digit = parseInt(imei.charAt(i));
             if (i % 2 === 1) {
                 digit *= 2;
                 if (digit > 9) {
                     digit = digit.toString();
                     digit = parseInt(digit.charAt(0)) + parseInt(digit.charAt(1));
                 }
             }
             sum += digit;
         }

         // Validate IMEI
         return sum % 10 === 0;
     }


     function getTemplateConfiguration() {
         let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : (Auth::user()->user_type == 'Reseller' ? 'reseller' : 'user'))) }}/get-template-configuration";
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
                             element.val(template[key]);
                         } else if (element.is('select')) {
                             element.val(template[key]);
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