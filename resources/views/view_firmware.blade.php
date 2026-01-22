<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Firmware Management</a></li>
            <li class="active"><a href="#">View FirmWare</a></li>
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
                <h2>View Firmware</h2>
              </div>
              <div class="col-lg-6 text-right">
                <a href="/{{$url_type}}/add-firmware" class="btn btn-success"> Add FirmWare </a>
              </div>
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
              @foreach ($getDeviceCategory as $key => $category)
              @if( Session::get('device_category_id'))
              <button class="tablinks {{Session::get('device_category_id') == $category->id ? 'active' : '' }}" onclick="openTab(event, 'tab{{ $category->id }}')">
                {{ $category->device_category_name }}
              </button>
              @else
              <button class="tablinks {{ $key==0 ? 'active' : '' }}" onclick="openTab(event, 'tab{{ $category->id }}')">
                {{ $category->device_category_name }}
              </button>
              @endif
              @endforeach


            </div>
            
            @foreach ($getDeviceCategory as $category)
            @php
            $templateInfo = CommonHelper::getTemplatesInfo($category->id);
            $users = CommonHelper::getUsersByDeviceCategory($category->id);

            @endphp
            <?php
              $i = 1;

              ?>
            <div id="tab{{ $category->id }}" class="tabcontent">

              @if(Auth::user()->user_type == "Admin")
              <div class="col-lg-12 text-right margin-bottom-10">
                <a href="{{ route('firmware.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('firmware.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
              @endif
              <table id="firmware{{ $category->id }}" class="firmwareData table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>U ID</th>
                    <th>Firmware Name</th>
                    <th>Country</th>
                    <th>State</th>
                    <th>ESIM</th>
                    <th>Backend</th>
                    <th>Firmware File</th>
                    <th>Firmware File Size</th>
                    <th>Version</th>
                    <th>Add Firmware</th>
                    <th>Default Firmware</th>
                    <th>No of Models</th>
                    <th style="width: 12px;">Created at</th>
                    <th>Last Edit</th>
                    <th>Edit </th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($firmwares as $firmware)
                  @if($firmware->device_category_id == $category->id)
                  @php $config = json_decode($firmware->configurations); @endphp
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$firmware->id}}</td>
                    <td>{{$firmware->name}}</td>
                    <td>{{CommonHelper::getCountryName($config->country)}}</td>
                    <td>{{CommonHelper::getStateName($config->state)}}</td>
                    <td>{{ $category->is_esim == 1 ? CommonHelper::getEsim($config->esim) : $config->esim }}</td>
                    <td>{{CommonHelper::getBackend($config->backend)}}</td>
                    <td>{{$config->filename??0}}</td>
                    <td>{{$config->fileSize?? 0}}</td>
                    <td>{{$config->version}}</td>
                    <td>
                       <a href="/admin/view-firmware-models/{{$firmware->id}}"  class="btn btn-primary" >View Modal</a>
                    </td>
                    <td class="text-center">
                      <?php echo $firmware->is_default == 1 ? '<button type="button" class="btn btn-warning">Yes</button>' : ''; ?>
                    </td>
                    <td class="text-center">
                        {{$firmware->model_count}}
                    </td>
                    <td>{{CommonHelper::getDateAsTimeZone($firmware->created_at)}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($firmware->updated_at)}}</td>
                    <td class="text-center">
                      <button type="button" class="btn btn-primary" onclick="openEditModel({{$firmware->id}})">Edit</button>
                    </td>
                    <td class="text-center">
                        <form id="deleteForm-{{$firmware->id}}" action="" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-sm" onclick="showDeleteModal({{$firmware->id}})">
                          Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                  <div class="modal" id="deleteFirmwareModal{{$firmware->id}}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          Are you sure you want to delete this firmware from All Devices ?
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                          <button type="button" class="btn btn-warning" onclick="confirmDelete({{$firmware->id}},false)">No</button>
                          <button type="button" class="btn btn-danger" onclick="confirmDelete({{$firmware->id}},true)">Yes</button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal" id="addModel{{$firmware->id}}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                          <h5 class="modal-title" id="addModellLabel">Add Model</h5>
                        </div>
                        <form id="addModalForm" onsubmit="return false" method="post">
                          @csrf
                          <div class="modal-body">
                            <!-- Form to Add eSIM -->
                            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                            <div class="margin-bottom-10">
                              <label for="modalname" class="form-label col-12">Model Name</label>
                              <input type="text" class="form-control" id="modalName" name="modalName" required>
                            </div>
                            <div class="margin-bottom-10">
                              <label for="vendorId" class="form-label col-12">Vendor Id</label>
                              <input type="text" class="form-control" id="vendorId" name="vendorId" required>
                            </div>
                            <div class="margin-bottom-10">
                              <label for="userAssign" class="form-label col-12">Assign Account</label>
                              <select id="userAssign" name="userAssign" class="form-control" class="userAssign" onChange="getModelById({{$firmware->id}})">
                                  <option value="">Please Select</option>
                                @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
                            <button type="submit" class="btn btn-primary addModalFormBtn">Submit</button>
                            <input type="hidden" name="modalId" id="modalId" value =""/>
                            <input type="hidden" name="firmwareId" id="firmwareId{{$firmware->id}}" value="" />
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div class="modal" id="editFirmware{{$firmware->id}}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                          <h5 class="modal-title" id="addModellLabel">Edit Firmware</h5>
                        </div>
                        <form id="editFirmwareForm" onsubmit="return false" method="post">
                          @csrf
                          <div class="modal-body">
                            <!-- Form to Add eSIM -->
                            <div class="col-sm-12 alert alert-danger error_msg_firmware" role="alert" style="display:none"></div>
                            <div class="margin-bottom-10">
                              <label for="modalname" class="form-label col-12">Firmware File</label>
                              @if(isset($config->filename))
                              <!-- Display the existing file -->
                              <div>
                                <p>Current file: <a href="{{ asset('fw/' . $config->filename) }}" target="_blank">{{ basename($config->filename) }}</a></p>
                              </div>
                              @endif
                              <input type="file" name="firmwareFile" id="firmwareFile" accept=".bin" class="reqfield" required/>
                            </div>
                            <div class="margin-bottom-10">
                              <label for="userAssign" class="form-label col-12">Firmware Version</label>
                              <input class="form-control " type="text" placeholder="Firmware version" name="firmware_version" value="{{$config->version}}" required />
                            </div>
                            <div class="margin-bottom-10">
                                 <label for="releasingNotes" class="form-label">Releasing Notes</label>
                                 <div>
                                    <textarea class="form-control " id="releasingNotes" name="releasingNotes" rows="6" cols="63">
                                      {{isset($config->releasingNotes)? $config->releasingNotes :''}}
                                    </textarea>
                                 </div>
                             </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
                            <button type="submit" class="btn btn-primary editFirmwareFormBtn">Update</button>
                            <input type="hidden" name="firmwareIdEdit" id="firmwareIdEdit{{$firmware->id}}" value="" />
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  @endif
                
                  <?php
                  $i++;
                  ?>
                  @endforeach
                 
                </tbody>
              </table>
            </div>
            @endforeach

          </div>

        </div><!--/.c_content-->
      </div><!--/.c_panels-->
    </div><!--/col-md-12-->
    </div><!--/row-->

    <!--======= Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function showDeleteModal(id) {
    $('#deleteFirmwareModal' + id).modal('show');
  }
  function confirmDelete(id,response) {
      
    const form = document.getElementById('deleteForm-' + id);
    form.action = `/{{$url_type}}/delete-firmware/${id}/${response}`;
    form.submit();
  }
  $(document).ready(function (){
       
    $('.tablinks').on('click', function() {
      $('#loading').show();
      initializeDataTables();
    });
      function initializeDataTables() {
      $('.firmwareData').each(function() {
        var elementId = $(this).attr('id');
        if ($.fn.DataTable.isDataTable("#" + elementId)) {
          $("#" + elementId).DataTable().destroy();
        }
        $("#" + elementId).DataTable({
          paging: true,
          searching: true,
          ordering: true,
          lengthChange: true,
          pageLength: 10,
          scrollX: true,
          scrollY: '500px',
          "aLengthMenu": [
            [25, 50, 100, 500, -1],
            [25, 50, 100, 500, "All"]
          ],
          "iDisplayLength": 25
        });
      });
      $('#loading').hide();
    }
    initializeDataTables();
  });
  function openModel(id) {
    $('.error_msg').hide().text();
    $('#firmwareId' + id).val(id);
    $('#addModel' + id).modal('show');

  }
  
  function openEditModel(id) {
    $('.error_msg_firmware').hide().text();
    $('#firmwareIdEdit' + id).val(id);
    $('#editFirmware' + id).modal('show');
  }
  $(document).ready(function() {
    $('.editFirmwareFormBtn').click(function() {
      var $modal = $(this).closest('.modal');
      var $form = $modal.find('form');
      let isValid = true;

      $form.find('[required]').each(function() {
        var value = $(this).val();

        // Handle null, array, undefined safely
        if (value === null || value === undefined || value === '' ||
          (Array.isArray(value) && value.length === 0)) {
          isValid = false;
          return false; // break loop
        }
      });

      if (!isValid) {
        e.preventDefault(); // stop action
        alert("hello");
        return;
      }
      var $errorMsg = $modal.find('.error_msg_firmware');
      var formData = new FormData($form[0]);
      $.ajax({
        url: '/admin/edit-firmware',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          let result = JSON.parse(response);
          if (result.status == 200) {
            alert(result.status_msg);
            $modal.modal('hide');
            window.location.reload();
          } else {
            isValid = false;
            let errorMessage = result.message;
            $errorMsg.show();
            $errorMsg.html(errorMessage);
            // resolve(isValid);
          }
        },
        error: function(xhr, status, error) {
           let errorMessage =xhr.responseJSON.message;
            $errorMsg.show();
            $errorMsg.html(errorMessage);
          // reject(error);
        }
      });

    });
  
  });

  $(document).ready(function() {
    $('.example').each(function() {
      var elementId = $(this).attr('id');
      $("#" + elementId).dataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        lengthChange: true,
        pageLength: 10,
        scrollX: true,
        scrollY: '500px',
        scrollCollapse: true,
        "aLengthMenu": [
          [25, 50, 100, 500, -1],
          [25, 50, 100, 500, "All"]
        ],
        "iDisplayLength": 25
      });
    });
  });

  function open_model(id, key) {
    $("#test_id").val(id);
    $("#modal-responsive-" + id).modal();
  };
  $(document).ready(function() {
    $('.selectDevice').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');
      console.log("dsjdssd", id);
      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });
  })
</script>