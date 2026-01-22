@extends('layouts.apps')
@section('content')
@include('modals.userEditDelOptions')
<form class="delUserResellerForm" data-action="/{{$url_type}}/delete-user/" action="" method="post">
  @csrf
  @method('DELETE')
  <div class="userAccCases">
  </div>
</form>
<div class="modal" id="addDeviceField" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title data-field-title"><strong>ADD Data Field</strong></h4>
      </div>
      <form id="deviceFieldForm" onsubmit="return false;">
        @csrf
        <div class="modal-body">
          <div class="card p-4">
            <div class="form-group">
              <!-- Field Type -->
              <div class=" row mb-3 margin-bottom-10 mb-3 row">
                <label class="col-lg-4 col-form-label font-weight-bold">Field Type <span class="text-danger">*</span></label>
                <div class="col-lg-8">
                  <select name="field_type" id="field_type" class="form-control">
                    <!--<option value="">Select Field Type</option>-->
                    <option value="0">Configurations</option>
                    <option value="1">Parameters</option>
                  </select>
                </div>
              </div>
              <!-- Field Name -->
              <div class="row mb-3 margin-bottom-10 mb-3 show-field-name" style="display:none;">
                <label class="col-lg-4 col-form-label font-weight-bold">Field Name <span class="text-danger">*</span></label>
                <div class="col-lg-8">
                  <input type="text" name="field_name" id="field_name" class="form-control" placeholder="Enter field name">
                </div>
              </div>
              <!-- Input Type -->
              <div class="show-input-type" style="display:none;">
                <div class="row mb-3 margin-bottom-10 mb-3 row ">
                  <label class="col-lg-4 col-form-label font-weight-bold">Input Type <span class="text-danger">*</span></label>
                  <div class="col-lg-8">
                    <select name="input_type" id="input_type" class="form-control inputType">
                      <!--<option value="">Select Input Type</option>-->
                      <option value="text">Text</option>
                      <option value="number">Number</option>
                      <option value="select">Select</option>
                      <option value="IP/URL">IP/URL</option>
                      <option value="multiselect">MultiSelect</option>
                      <option value="text_array">Text Array</option>
                      <option value="hex">Hex</option>
                    </select>
                  </div>
                </div>
                <div class="append-number-options border p-3 rounded bg-light mt-3 margin-bottom-3" style="display:none;">
                  <label class="font-weight-bold mb-2 d-block">Number Range <span class="text-danger">*</span></label>
                  <div class="form-row align-items-center">
                    <div class="col-lg-6">
                      <input type="number" class="form-control" placeholder="Min" name="numberInput[min]" />
                    </div>
                    <div class="col-lg-6">
                      <input type="number" class="form-control" placeholder="Max" name="numberInput[max]" />
                    </div>
                  </div>
                </div>
                <div class="max-selected-values form-group" style="display:none;"></div>

                <div class="append-maxValue-options border p-3 rounded bg-light mt-3" style="display:none;">
                  <div class="form-group mb-0">
                    <div class="form-row align-items-center">
                      <div class="col-lg-4 padding-1">
                        <label class="font-weight-bold">Max Length <span class="text-danger">*</span></label>
                      </div>
                      <div class="col-lg-6">
                        <input type="number" class="form-control" placeholder="Enter maximum length" name="maxValueInput[0][]" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="append-select-options border p-3 rounded bg-light mt-3" style="display:none;">
                  <label class="font-weight-bold">Select Options <span class="text-danger">*</span></label>
                  <div class="select-options-container">
                    <div class="form-row align-items-center mb-2  margin-bottom-10">
                      <div class="options-row">
                        <div class="col-lg-5">
                          <input type="text" class="form-control" placeholder="Enter Option" name="selectOptions[0][]" />
                        </div>
                        <div class="col-lg-5">
                          <input type="text" class="form-control" placeholder="Enter Value" name="selectValues[0][]" />
                        </div>
                        <div class="col-lg-2">
                          <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i class="fa fa-times"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="text-right margin-bottom-15">
                    <button type="button" class="btn btn-outline-success btn-sm add-option"><i class="fa fa-plus"></i> Add Option</button>
                  </div>
                </div>


                <!-- Text Array Options (Conditional) -->
                <div class="form-group mt-3" id="selectOptionsGroup" style="display:none;">
                  <div class="options-row">
                    <label class="font-weight-bold">Options</label>
                    <div id="selectOptionsContainer">
                      <div class="input-group mb-2">
                        <input type="text" name="options[]" class="form-control" placeholder="Option">
                        <div class="input-group-append">
                          <button type="button" class="btn btn-outline-danger remove-option"><i class="fa fa-times"></i></button>
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addOptionBtn"><i class="fa fa-plus"></i> Add Option</button>
                  </div>
                </div>

              </div>
              <div class="margin-bottom-10 mb-3 show-on-select" style="display:none;">
                <div class="show-on-select1">
                  <label class="col-lg-4 col-form-label font-weight-bold">Common Field <span class="text-danger">*</span></label>
                  <div class="col-lg-8" style="font-size: 16px !important;width: 50px;height: 13px;display: flex;justify-content: center;align-items: center;">
                    <input type="checkbox" name="is_common" id="is_common" class="form-control is_common" placeholder="">
                  </div>
                  <label class="col-lg-4 col-form-label font-weight-bold">Can Protocol Field <span class="text-danger">*</span></label>
                  <div class="col-lg-8" style="font-size: 16px !important;width: 50px;height: 13px;display: flex;justify-content: center;align-items: center;">
                    <input type="checkbox" name="is_can_protocol" id="is_can_protocol" class="form-control is_can_protocol" placeholder="">
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer row bgx-custom-modal-footer">
          <button type="button" data-dismiss="modal" aria-hidden="true" class="col btn btn-primary btn-flat">Back</button>
          <button class="col btn btn-primary btn-flat submitDataErr" type="submit">Submit</button>
          <input type="hidden" name="dataFieldId" id="dataFieldId" value="" />
        </div>
      </form>
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
            <li><a href="#">Device Category</a></li>
            <li class="active"><a href="#">View Data Fields</a></li>
          </ul>
        </nav>
      </div>
    </div>
    <!--======== Page Title and Breadcrumbs End ========-->
    <!--======== Dynamic Datatable Content Start End ========-->
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2>Data Fields</h2>
              </div>
              @if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller')
              <div class="col-lg-6 text-right">
                <p class="btn btn-success" onclick="openAddDeviceFieldModel()"> Add Data Fields </p>
              </div>
              @endif
            </div>
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
            <div class="tabs">
              <button class="tablinks active" onclick="openTab(event, 'all')">ALL</button>
              <button class="tablinks" onclick="openTab(event, 'cat')">Configurations</button>
              <button class="tablinks" onclick="openTab(event, 'param')">Parameters</button>
            </div>

            <div id="all" class="tab-content active">
              <div>
                <table id="example" class="example view_user_table table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:14px;">
                  <thead>
                    <tr>
                      <th>Sr. No.</th>
                      <th>Field ID</th>
                      <th>Field Type</th>
                      <th>Field Name</th>
                      <th>Input Type</th>
                      <th>Validation Rule</th>
                      <th>Common Field</th>
                      <th>Can Protocol</th>
                      @if(Auth::user()->user_type =='Admin' )
                      <th>Edit</th>
                      @endif
                      <th>Delete</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($dataFields as $i => $dataField)
                    <tr>
                      <td>{{$i+ 1}}</td>
                      <td>{{$dataField->id}}</td>
                      <td>{{$dataField->fieldType == 0 ? 'Configurations' : 'Parameters'}}</td>
                      <td>{{$dataField->fieldName}}</td>
                      <td>{{$dataField->inputType}}</td>
                      <td>{{$dataField->validationConfig}}</td>
                      <td>
                        @if ($dataField->is_common)
                        <span class="badge bg-success padding-10">True</span>
                        @else
                        <span class="badge bg-danger padding-10">False</span>
                        @endif
                      </td>
                      <td>
                        @if ($dataField->is_can_protocol)
                        <span class="badge bg-success padding-10">True</span>
                        @else
                        <span class="badge bg-danger padding-10">False</span>
                        @endif
                      </td>
                      <td><button type="button" data-id="{{ $dataField->id }}"
                          data-field-type="{{ $dataField->fieldType }}"
                          data-field-name="{{ $dataField->fieldName }}"
                          data-input-type="{{ $dataField->inputType }}"
                          data-config="{{ $dataField->validationConfig }}"
                          data-is_common="{{$dataField->is_common}}"
                          data-is_can_protocol="{{$dataField->is_can_protocol}}" class="btn btn-primary" onclick="openEditModel(this)">Edit</button></td>
                      <td>
                        <form id="deleteForm-{{$dataField->id}}" action="" method="post">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="btn btn-danger btn-sm" onclick="showDeleteModal({{$dataField->id}})">Delete</button>
                      </td>
                      </form>
                    </tr>
                    <div class="modal" id="deleteModal{{$dataField->id}}" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header d-flex" style="justify-content: space-between;">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            Are you sure you want to delete this?
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{$dataField->id}},true)">Yes</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div id="cat" class="tab-content">
              <p>This is Tab 2 content.</p>
            </div>
            <div id="param" class="tab-content">
              <p>This is Tab 3 content.</p>
            </div>


          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->
    </div><!--/row-->
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>




<!--****** End Modal Responsive******-->
@stop
<style>
  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  .tablinks.active {
    background-color: #007bff;
    color: white;
  }
</style>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
  function openTab(evt, tabName) {
    console.log("tabName ===>", tabName);
    const tabContents = document.querySelectorAll(".tab-content");
    tabContents.forEach(content => content.classList.remove("active"));

    const tabLinks = document.querySelectorAll(".tablinks");
    tabLinks.forEach(link => link.classList.remove("active"));

    document.getElementById(tabName).classList.add("active");

    evt.currentTarget.classList.add("active");
  }

  function showDeleteModal(id) {
    $('#deleteModal' + id).modal('show');
  }

  function openEditModel(button) {
    $('#field_type').val("");
    $('#field_name').val("");
    $('#is_common').prop("checked", false);
    $('#input_type').val('').trigger('change');
    const $btn = $(button);
    const fieldData = {
      id: $btn.data('id'),
      field_type: $btn.data('field-type'),
      field_name: $btn.data('field-name'),
      input_type: $btn.data('input-type'),
      config: $btn.data('config') || {},
      is_common: $btn.data('is_common'),
      is_can_protocol: $btn.data('is_can_protocol'),
    };
    console.log("fieldData.is_common -->", fieldData.is_common == 1);
    const $form = $('#deviceFieldForm');
    $('.data-field-title').text("Edit Data Field");
    // Reset form and show modal
    $('#deviceFieldForm')[0].reset();
    $('#deviceFieldForm').attr('data-mode', 'edit');
    $('#formModeTitle').text('EDIT Data Field');
    $('#dataFieldId').val(fieldData.id);
    $('#addDeviceField').modal('show');
    $('.show-field-name').show();
    // Show base fields
    $('.show-on-select').show();
    if (fieldData.field_type == "0") {
      //     $('.show-field-name').show();
      // } else{
      $('.show-input-type').show();
    } else {
      $('.show-input-type').hide();
    }
    $('.max-selected-values').hide();
    // Set basic values
    $('#field_type').val(fieldData.field_type);
    $('#field_name').val(fieldData.field_name);
    $('#is_common').prop("checked", fieldData.is_common == 1);
    $('#is_can_protocol').prop("checked", fieldData.is_can_protocol == 1);
    $('#input_type').val(fieldData.input_type).trigger('change');

    // Hide all dynamic input sections
    $('.append-number-options, .append-maxValue-options, .append-select-options, #selectOptionsGroup').hide();

    // Load configuration values based on input_type
    const config = fieldData.config;

    switch (fieldData.input_type) {
      case 'select':
        $('.select-options-container').empty();
        const options = config.selectOptions || [];
        const values = config.selectValues || [];

        options.forEach((option, index) => {
          $('.select-options-container').append(`
                        <div class="options-row">
                        <div class="form-row align-items-center mb-2 ">
                            <div class="col-lg-5">
                                <input type="text" class="form-control" name="selectOptions[0][]" value="${option}" />
                            </div>
                            <div class="col-lg-5">
                                <input type="text" class="form-control" name="selectValues[0][]" value="${values[index] || ''}" />
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        </div>
                    `);
        });
        $('.append-select-options').show();
        break;
      case 'multiselect':
        $('.select-options-container').empty();
        $('.max-selected-values').empty();
        const options1 = config.selectOptions || [];
        const values1 = config.selectValues || [];

        options1.forEach((option, index) => {
          $('.select-options-container').append(`
              <div class="options-row">
                <div class="form-row align-items-center mb-2 ">
                    <div class="col-lg-5">
                        <input type="text" class="form-control" name="selectOptions[0][]" value="${option}" />
                    </div>
                    <div class="col-lg-5">
                        <input type="text" class="form-control" name="selectValues[0][]" value="${values1[index] || ''}" />
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i class="fa fa-times"></i></button>
                    </div>
                </div>
              </div>
          `);
        });

        $('.append-select-options').show();
        $('.max-selected-values').append(`<div class="row mb-3 margin-bottom-10 mb-3 show-field-name">
          <label class="col-lg-4 col-form-label font-weight-bold">Max Length <span class="text-danger">*</span></label>
          <div class="col-lg-8">
            <input type="number" name="maxSelectValue[0]" value="${config.maxSelectValue}" id="field_name" class="form-control">
          </div>
        </div>`).show();
        break;
      case 'number':
        $('[name="numberInput[min]"]').val(config.numberInput?.min ?? '');
        $('[name="numberInput[max]"]').val(config.numberInput?.max ?? '');
        $('.append-number-options').show();
        break;

      case 'text':
      case 'IP/URL':
      case 'text_array':
        $('[name="maxValueInput[0][]"]').val(config.maxValueInput ?? '');
        $('.append-maxValue-options').show();
        break;
    }
  }

  function confirmDelete(id) {
    const form = document.getElementById('deleteForm-' + id);
    form.action = `/{{$url_type}}/delete-category-fields/${id}`;
    form.submit();
  }
  $(document).ready(function() {
    $(document).on("change", '#field_type', function() {
      const value = $(this).val();
      $(".show-field-name").show();
      $(".show-on-select").show();
      if (value == 1) {
        $(".show-input-type").hide();
      } else {
        $(".show-input-type").show();
      }
    });
    $("#deviceFieldForm").submit(function() {
      var form = $(this);
      $.ajax({
        url: '/admin/device-data-field',
        type: 'POST',
        data: form.serialize(),
        success: function(response) {
          let result = response;
          console.log("result", result);
          if (result.status == 200) {
            $('#addDeviceField').modal('hide');
            //   $(".error_msg").append(result.message).show();
            //   $('#deviceCategoryDelOptionModal' + id).modal("hide");
            //   document.documentElement.scrollIntoView({
            //     behavior: 'smooth',
            //     block: 'start'
            //   });
            window.location.reload();
          }
          return false
        },
        error: function(xhr, status, error) {
          alert("An error occurred: " + error);
        }
      });
    });
    $(document).on("change", ".inputType", function() {
      const selectedType = $(this).val();
      const $formGroup = $(this).closest(".form-group");
      var maxvalOptions = $(this).closest(".form-group").find('.append-maxValue-options');


      const $appendSelectOptions = $formGroup.find(".append-select-options");
      $appendSelectOptions.show();
      const $inputOptions = $formGroup.find('.append-number-options');
      const $defaultValue = $formGroup.find('input[name^="default["]');
      var defaultVal = $(this).closest(".form-group").find(".default-val");
      $defaultValue.attr('type', 'text');
      $inputOptions.find('input').attr('required', false);
      $appendSelectOptions.find('input').attr('required', false);

      if (selectedType === "select") {
        $appendSelectOptions.show();
        $appendSelectOptions.find('input').attr('required', true);
        $inputOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
        maxvalOptions.hide();
      } else if (selectedType == "multiselect") {
        $appendSelectOptions.show();
        $appendSelectOptions.find('input').attr('required', true);
        $inputOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
        maxvalOptions.hide();
      } else if (selectedType === 'number') {
        $defaultValue.attr('type', 'number');
        $inputOptions.show();
        $inputOptions.find('input').attr('required', true);
        $appendSelectOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
        maxvalOptions.hide();
      } else if (selectedType == 'IP/URL') {
        $appendSelectOptions.hide();
        defaultVal.addClass("ip-url-space no-space-allowed");
        maxvalOptions.show();
        $inputOptions.hide();
      } else if (selectedType == 'text_array') {
        $appendSelectOptions.hide();
        defaultVal.addClass("text-array-space");
        maxvalOptions.show();
        $inputOptions.hide();
      } else {
        $appendSelectOptions.hide();
        maxvalOptions.show();
        $inputOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
      }
    });
    $(".no-space-allowed").on("keydown", function(event) {
      if (event.key === " ") {
        event.preventDefault(); // Prevent space
      }
    });
    $(document).on("keydown", ".ip-url-space", function(event) {
      const key = event.key;
      console.log("Pressed key:", key);

      if (key === " ") {
        event.preventDefault();
        return false;
      }

      if (
        event.ctrlKey || event.metaKey ||
        key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" ||
        key === "Delete" || key === "Tab" || key === "Enter"
      ) {
        return;
      }

      const allowed = /^[a-zA-Z0-9.]$/;
      if (!allowed.test(key)) {
        event.preventDefault();
        return false;
      }
    });
    $(document).on("paste", ".ip-url-space", function(event) {
      const pastedData = event.originalEvent.clipboardData.getData('text');
      const allowed = /^[a-zA-Z0-9.]*$/;

      if (!allowed.test(pastedData)) {
        event.preventDefault();
        alert("Pasted content contains invalid characters.");
      }
    });
    $(document).on("click", ".remove-option", function() {
      $(this).closest(".options-row").remove();
    });
    $(document).on("keydown", ".text-array-space", validateTextArrayInput);

    function validateTextArrayInput(event) {
      const key = event.key;
      console.log("Pressed key in text-array-space:", key);

      // Block space
      if (key === " ") {
        event.preventDefault();
        return;
      }

      const allowed = /^[a-zA-Z0-9.,{}]$/;



      // Allow control/navigation keys
      if (
        event.ctrlKey || event.metaKey ||
        key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" ||
        key === "Delete" || key === "Tab" || key === "Enter"
      ) {
        return;
      }

      // Block any key not in the allowed set
      if (!allowed.test(key)) {
        event.preventDefault();
      }
    }
    $(document).on("paste", ".text-array-space", function(event) {
      const pastedData = event.originalEvent.clipboardData.getData('text');
      const allowed = /^[a-zA-Z0-9.,{}]*$/;
      if (event.key === " ") {
        event.preventDefault();
        return;
      }
      if (!allowed.test(pastedData)) {
        event.preventDefault();
        alert("Pasted content contains invalid characters.");
      }
    });
  });

  function openAddDeviceFieldModel() {
    $('#addDeviceField').modal('show');
    $('.data-field-title').text("ADD Data Field");
    $('#field_type').val("");
    $('#field_name').val("");
    $('#input_type').val("").trigger('change');
    $('.append-maxValue-options').hide();
    $(".append-select-options").hide();
    $('.append-number-options').hide();
  }
  document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-target');

      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });

      // Remove active class from all buttons
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });

      // Show the clicked tab
      document.getElementById(targetId).classList.add('active');
      button.classList.add('active');
    });
  });

  function open_asign(id) {
    $("#auser_id").val(id);
    $("#modal-responsive" + id).modal('show');
  };

  function openConfigurations(id) {
    $("#view-Configurations" + id).modal('show');
    $("#configuration" + id).dataTable();
    $("#configuration" + id + "_wrapper").css({
      'text-align': 'left'
    });
    $('.select2').select();
  }

  $(document).on("click", ".add-option", function() {
    var inputCount = $(this).data("inputcount");

    var optionsHtml =
      '<div class="form-row align-items-center  margin-bottom-10">' +
      '<div class="options-row">' +
      '<div class="col-lg-5">' +
      '<input type="text" class="form-control" placeholder="Enter Option" name="selectOptions[0][]">' +
      '</div>' +
      '<div class="col-lg-5">' +
      '<input type="text" class="form-control" placeholder="Enter Value" name="selectValues[0][]">' +
      '</div>' +
      '<div class="col-lg-2">' +
      '<button type="button" class="btn btn-outline-danger btn-sm remove-option">' +
      '<i class="fa fa-times"></i>' +
      '</button>' +
      '</div>' +
      '</div>' +
      '</div>';
    $(this).closest(".form-group").find(".select-options-container").append(optionsHtml);
  });
  $(document).ready(function() {

    $('.assignDevices').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');

      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });


  });

  function togglePasswordShow(id) {
    $("#hide-" + id).hide();
    $("#showpassword-" + id).show();
  }
</script>
<style>
  .show-on-select1 {
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>