 <?php

    use App\Helper\CommonHelper;

    $getDeviceCategory = CommonHelper::getDeviceCategory();
    ?>
 @extends('layouts.apps')
 @push('styles')
 <style>
    /* Premium Form Styling */
    .c_panel {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        padding: 25px;
    }
    .c_title h2 {
        font-weight: 700;
        color: #1e293b;
        font-size: 20px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }
    .control-label {
        color: #475569;
        font-weight: 600;
        font-size: 14px;
        padding-top: 10px;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 10px 15px;
        font-size: 14px;
        color: #1e293b;
        box-shadow: none;
        transition: all 0.3s;
        height: auto;
    }
    .form-control:focus {
        border-color: #7bc62d;
        box-shadow: 0 0 0 3px rgba(123, 198, 45, 0.15);
    }
    .select2-container--default .select2-selection--single {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        height: 42px;
        padding: 6px 15px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .btn-primary {
        background-color: #7bc62d !important;
        border-color: #7bc62d !important;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    .btn-primary:hover, .btn-primary:focus {
        background-color: #68a825 !important;
        border-color: #68a825 !important;
        box-shadow: 0 4px 6px rgba(123, 198, 45, 0.2);
    }
    .btn-secondary {
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 24px;
    }
    .add-btn-dark {
        background-color: #1e293b !important;
        border-color: #1e293b !important;
        color: white !important;
        border-radius: 8px;
        padding: 8px 16px;
        height: 42px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .add-btn-dark:hover {
        background-color: #0f172a !important;
        box-shadow: 0 4px 6px rgba(15, 23, 42, 0.2);
    }
    
    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        overflow: hidden;
    }
    .modal-header {
        background-color: #7bc62d;
        color: #000;
        border-bottom: none;
        padding: 20px 25px;
    }
    .modal-header .modal-title {
        font-weight: 700;
        font-size: 18px;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .modal-header .close {
        color: #000;
        opacity: 0.7;
        text-shadow: none;
        font-size: 24px;
        margin-top: -4px;
    }
    .modal-header .close:hover {
        opacity: 1;
    }
    .modal-body {
        padding: 25px;
    }
    .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 20px 25px;
    }
    .form-label {
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
        display: block;
    }
    .margin-bottom-10 {
        margin-bottom: 20px;
    }
 </style>
 @endpush
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
                         <li class="active"><a href="#">Add Firmware</a></li>
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
                         <h2>Add Firmware</h2>
                         <div class="clearfix"></div>
                     </div><!--/.c_title-->
                     <div class="c_content">
                         <div class="row" id="alert_msg">
                             @include('partials.gps-inline-alerts')
                         </div>
                         <div class="col-sm-12 alert alert-success success_msg" role="alert" style="display:none"></div>
                         <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                         <form class="validator form-horizontal " method="post" action="/admin/create-firmware" enctype="multipart/form-data">
                             @csrf
                             @if(Auth::user()->user_type!=='Admin')
                             <input type="hidden" name="user_id" value="{{Auth()->id()}}">
                             @endif
                             <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3">Firmware Name <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <input class="form-control " type="text" placeholder="Enter Firmware Name" name="name" required value="{{ old('name') }}" />
                                 </div>
                             </div>
                             <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3 ">Device Category <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <select class="" id="deviceCategory" name="deviceCategory">
                                         <option value=""> </option>
                                         @foreach($getDeviceCategory as $deviceCategory)
                                         <option {{ old('deviceCategory') == $deviceCategory->id ? 'selected' : '' }} value="{{$deviceCategory->id}}">{{$deviceCategory->device_category_name}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                             <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3">Country <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <select class="" id="country" name="country">
                                         <option value=""> </option>
                                         @foreach($countries as $country)
                                         <option {{ old('country') == $country->id ? 'selected' : '' }} value="{{$country->id}}">{{$country->name}}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                             <div class="form-group stateList" @if(old('state'))
                                 style="display:block;"
                                 @else
                                 style="display:none;"
                                 @endif>
                                 <label for="curl" class="control-label col-lg-3">States <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <select class="" id="state" name="state"></select>
                                 </div>
                             </div>
                             <div class="form-group " id="hide-esim-mode-not-active">
                                 <label for="curl" class="control-label col-lg-3">Esim <span class="require">*</span></label>
                                 <div class="col-lg-6 row">
                                     <div class="col-lg-11 padding-right-1">
                                     <select id="esim" name="esim">
                                         @foreach($esim as $sim)
                                         <option {{ old('esim') == $sim->id ? 'selected' : '' }} value="{{$sim->id}}">{{$sim->name}} ({{$sim->profile_1}} + {{$sim->profile_2}})</option>
                                         @endforeach
                                     </select>
                                     </div>
                                     <div class="col-lg-1">
                                     <button type="button" class="btn add-btn-dark margin-top-1 " onclick="openModel()">Add</button>
                                     </div>
                                 </div>
                             </div>
                             <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3">Backend <span class="require">*</span></label>
                                 <div class="col-lg-6 row">
                                     <div class="col-lg-11 padding-right-1">
                                         <select id="backend" name="backend">
                                             @foreach($backend as $back)
                                             <option {{ old('backend') == $back->id ? 'selected' : '' }} value="{{$back->id}}">{{$back->name}}</option>
                                             @endforeach
                                         </select>
                                     </div>
                                      <div class="col-lg-1">
                                     <button type="button" class="btn add-btn-dark margin-top-1 " onclick="openBackendModel()">Add</button>
                                     </div>
                                 </div>
                             </div>
                             <div class="form-group ">
                                 <label for="curl" class="control-label col-lg-3">Version <span class="require">*</span></label>
                                 <div class="col-lg-6">
                                     <input class="form-control " type="text" placeholder="Firmware version" name="firmware_version" required value="{{ old('firmware_version') }}" />
                                 </div>
                             </div>
                             <div class="form-group ">
                                 <label for="cemail" class="control-label col-lg-3">Firmware File <span class="require">*</span></label>
                                 <div class="col-lg-6 padding-top-6">
                                     <input type="file" name="firmwareFile" id="firmwareFile" accept=".bin" class="reqfield" />
                                 </div>
                             </div>
                             <div class="form-group ">
                                 <label for="releasingNotes" class="control-label col-lg-3">Releasing Notes</label>
                                 <div class="col-lg-6">
                                    <textarea class="form-control " id="releasingNotes" name="releasingNotes" rows="6" cols="63">
                                    </textarea>
                                 </div>
                             </div>
                             <div id="loading" class="bgx-loading" style="display:none;">
                                 <img src="/assets/icons/loader.gif" alt="Loading..." />
                             </div>
                             
                             <div class="form-group">
                                 <div class="col-lg-offset-3 col-lg-6">
                                     <input type="hidden" id="esimRequired" name="esimRequired" value ="1" />
                                     
                                     <input type="hidden" name="fileSize" id="fileSize" value = "" />
                                     <button class="btn btn-primary btn-flat btn-disable-after-submit" type="submit" @if ($message=Session::get('success')) disabled @endif>Save</button>
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
<div class="modal" id="addESIMModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h5 class="modal-title" id="addESIMModalLabel">Add eSIM</h5>
      </div>
      <form id="addESIMForm" onsubmit="return false" method="post">
        @csrf;
        <div class="modal-body">
          <!-- Form to Add eSIM -->
          <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
          <div class="margin-bottom-10" >
            <label for="esimName" class="form-label">eSIM Make </label>
            <input type="text" class="form-control" id="esimName" name="esimName" required>

          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider1" class="form-label">Profile 1</label>
            <select id="esimProvider1" name="esimProvider1" class="form-control esimProvider">
              <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option>
            </select>
          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider2" class="form-label">Profile 2</label>
            <select id="esimProvider2" name="esimProvider2" class="form-control esimProvider">
              <!-- <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option> -->
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary close" data-dismiss="modal" aria-hidden="true">Close</button>
          <button type="submit" id="submitESIMBtn" class="btn btn-primary" form="addESIMForm">Submit</button>
          <input type="hidden" name="esimId" id="esimId" value="" />
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal" id="addBackend" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h5 class="modal-title" id="addBackendLabel">Add Backend</h5>
      </div>
      <form id="addBackendform" onsubmit="return false" method="post">
        @csrf
        <div class="modal-body">
          <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>

          <!-- Form to Add eSIM -->
          <div class="margin-bottom-10">
            <label for="esimName" class="form-label">Backend Name </label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary close" data-dismiss="modal" aria-hidden="true">Close</button>
          <button type="submit" id="SubmitBackend" class="btn btn-primary" form="addESIMForm">Submit</button>
          <input type="hidden" name="backendId" id="backendId" value="" />
        </div>
      </form>
    </div>
  </div>
</div>
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script type="text/javaScript">
   function openModel() {
    $('#addESIMModalLabel').text("ADD Esim");
    $('#esimId').val('');
    $('#esimName').val('');
    $('#esimProvider1').val('Airtel').trigger('change');
    $('#esimProvider2').val('Airtel').trigger('change');
    $("#addESIMModal").modal();

  }
  function openBackendModel(){
    $('#addBackendLabel').text("ADD Backend");
    $('#backendId').val('');
    $("#addBackend").modal();
  }

  $(document).ready(function() {
    $("#firmwareFile").change(function (e) {
        const file = e.target.files[0];
        if (file) {
            const fileSizeInBytes = file.size;
            $('#fileSize').val(fileSizeInBytes);
            console.log("fileSizeInBytes =>", fileSizeInBytes);
        } else {
            console.log("No file selected.");
        }
    });
      
    $("#deviceCategory").change(function(){
      let categories = <?= $getDeviceCategory ?>;
      let selectedCategories = $(this).val();
      let esimCategoryIds = categories.filter(category => selectedCategories.includes(category.id.toString()));
      if(esimCategoryIds[0].is_esim == 0){
         $('#hide-esim-mode-not-active').css('display','none');
         $('#esimRequired').val(0);
      }
      console.log("esimCategoryIds ===>", esimCategoryIds);
    });
    // Object to keep track of removed options
    let removedOptions = [];

    $('#esimProvider1').on('change', function() {
      let totalValues = ['Airtel', 'Bsnl', 'Jio', 'VI']
      let selectedValue = $(this).val();
      let $secondSelect = $('#esimProvider2');
      totalValues = totalValues.filter(value => value !== selectedValue)
      let $html = "";
      totalValues.forEach((value) => {
        $html += '<option value="' + value + '">' + value + '</option>';
      })
      $('#esimProvider2').empty();
      $('#esimProvider2').append($html);
    });
  });
     let removedOptions = {};
    $('#submitESIMBtn').on('click', function() {
      function validateForm() {
        let isValid = true;
        let errorMessage = '';

        // Check if 'esimName' is empty
        if ($('#esimName').val().trim() === '') {
          isValid = false;
          errorMessage += 'eSIM Name is required.' + "</br>";
        }

        // Check if 'esimProvider1' is selected
        if ($('#esimProvider1').val() === null) {
          isValid = false;
          errorMessage += 'Profile 1 is required.' + "</br>";
        }

        // Check if 'esimProvider2' is selected
        if ($('#esimProvider2').val() === null) {
          isValid = false;
          errorMessage += 'Profile 2 is required.' + "</br>";
        }

        if (!isValid) {
          $('.error_msg').show();
          $('.error_msg').html(errorMessage);
          // alert(errorMessage); // Display error messages
        }

        return isValid;
      }
      if (validateForm()) {
        $('.error_msg').hide();
        var formData = new FormData($('#addESIMForm')[0]);

        $.ajax({
          url: '/admin/create-esim', // Replace with your server endpoint
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            let result = JSON.parse(response);
            if (result.status = 200) {
              alert(result.status_msg);
              $('#addESIMModal').modal('hide');
            //   window.location.reload();
            var $select = $('#esim');
            $select.empty(); // Clear the current options
    
            // Append new options from the response
            $.each(result.esims, function(index, esim) {
                $select.append($('<option>', {
                    value: esim.id,
                    text: esim.name + '(' + esim.profile_1 +'+'+esim.profile_2+')',
                    selected: esim.id == response.selected_backend_id // Optional: Mark selected backend
                }));
            });
            } else {
              alert('error Occured');
            }
          },
          error: function(xhr, status, error) {
            alert('An error occurred while adding the eSIM.');
          }
        });
      }
    });
    $('#SubmitBackend').on('click', function() {
      function validateForm() {
        let isValid = true;
        let errorMessage = '';

        // Check if 'esimName' is empty
        if ($('#name').val().trim() === '') {
          isValid = false;
          errorMessage += 'Backend Name is required.' + "</br>";
        }

        if (!isValid) {
          $('.error_msg').show();
          $('.error_msg').html(errorMessage);
          // alert(errorMessage); // Display error messages
        }

        return isValid;
      }
      if (validateForm()) {
        $('.error_msg').hide();
        var formData = new FormData($('#addBackendform')[0]);

        $.ajax({
          url: '/admin/create-backend', // Replace with your server endpoint
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            let result = JSON.parse(response);
            if (result.status = 200) {
              alert(result.status_msg);
              $('#addBackend').modal('hide');
                var $select = $('#backend');
                $select.empty(); // Clear the current options
        
                // Append new options from the response
                $.each(result.backend, function(index, backend) {
                    $select.append($('<option>', {
                        value: backend.id,
                        text: backend.name,
                        selected: backend.id == response.selected_backend_id // Optional: Mark selected backend
                    }));
                });
            //   window.location.reload();
            } else {
              alert('error Occured');
            }
          },
          error: function(xhr, status, error) {
            alert('An error occurred while adding the eSIM.');
          }
        });
      }
    });
     $(document).ready(function(){
    var oldCountry = @json(old('country')); // Get old country value if any
    var oldState = @json(old('state')); // Get old state value if any
        $("#btn-disable-after-submit").click(function(){
            $(this).attr('disabled',true);
        })
    // Function to load states based on selected country
    function loadStates(countryId) {
        $('#state').empty();
        let actionUrl = "/{{$url_type}}/state-list";
        $.ajax({
            type: "POST",
            url: actionUrl,
            data: { id: countryId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 200) {
                    $('.stateList').show();
                    let states = JSON.parse(response.states);
                    states.forEach(state => {
                        $('#state').append(new Option(state.name, state.id, false, false));
                    });
                    // Set the old state value if applicable
                    if (oldState) {
                        $('#state').val(oldState).trigger('change');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error fetching states:', xhr.responseText);
            }
        });
    }

    // Handle country change event
    $('#country').change(function() {
        let selectedCountryId = $(this).val();
        if (selectedCountryId) {
            loadStates(selectedCountryId);
        } else {
            $('.stateList').hide();
        }
    });

    // Trigger change event on page load if there is an old country value
    if (oldCountry) {
        $('#country').val(oldCountry).trigger('change');
    }

    // Initialize Select2
    $('#deviceCategory').select2({
        placeholder: "Search and Select",
    });
    $('#country').select2({
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
                let firmwares = JSON.parse(result.firmware);
                let htmlContent = '';
                if (result.status == 200) {
                    let inputFields = JSON.parse(result.device_input);
                    firmwares.forEach(firmware => {
                         var option = new Option(firmware.name, firmware.id, false, false);
                         $('#firmware').append(option);
                    });
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
        $('#country').select2({
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