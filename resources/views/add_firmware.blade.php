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
                                     <button type="button" class="btn btn-primary margin-top-1 " onclick="openModel()">Add</button>
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
                                     <button type="button" class="btn btn-primary margin-top-1 " onclick="openBackendModel()">Add</button>
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
            <select id="esimProvider1" name="esimProvider1" class="form-control" class="esimProvider">
              <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option>
            </select>
          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider2" class="form-label">Profile 2</label>
            <select id="esimProvider2" name="esimProvider2" class="form-control" class="esimProvider">
              <!-- <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option> -->
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
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
          <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
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