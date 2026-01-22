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
            <li class="active"><a href="#">@if(isset($firmware_id)) View Firmware Models @else View Models @endif</a></li>
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
                <h2>@if(isset($firmware_id)) View Firmware Models @else View Models @endif</h2>
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
            @if(Auth::user()->user_type == "Admin" && isset($firmware_id))
            <div class="col-lg-12 text-right margin-bottom-10">
              <button type="button" class="btn btn-primary" onclick="openModel({{$firmware_id}})">Add Model</button>
              <div class="modal text-left" id="addModel{{$firmware_id}}" aria-hidden="true">
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
                          <label for="userAssign" class="form-label col-12">Assign Account</label>
                          <select id="userAssign" name="userAssign" class="form-control" class="userAssign" onChange="getModelById({{$firmware_id}})">
                              <option value="">Please Select</option>
                            @foreach($users as $user)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="margin-bottom-10 hide-field padding-1" style="display:none;">
                          <label for="modalname" class="form-label col-12">Model Name</label>
                          <input type="text" class="form-control" id="modalName" name="modalName" required>
                        </div>
                        <div class="margin-bottom-10 hide-field padding-1" style="display:none;">
                          <label for="vendorId" class="form-label col-12">Vendor Id</label>
                          <input type="text" class="form-control" id="vendorId" name="vendorId" required>
                        </div>
                      
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button type="submit" class="btn btn-primary addModalFormBtn">Submit</button>
                        <input type="hidden" name="modalId" id="modalId" value =""/>
                        <input type="hidden" name="firmwareId" id="firmwareId{{$firmware_id}}" value="" />
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <!-- <a href="{{ route('model.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('model.csv') }}" class="btn btn-success">Download CSV</a> -->
            </div>
            @endif
            <table id="esim" class="example table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Model Name</th>
                  <th>Vendor ID</th>
                  <th>Assigned Account</th>
                  <th>Assigned Firmware</th>
                  <th style="width: 12px;">Created at</th>
                  <th>Last Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <?php
              $i =  1;
              ?>
              <tbody>
                @foreach ($modalList as $modal)
                <tr>
                  <td><?php echo $i; ?></td>
                  <td>{{$modal->name}}</td>
                  <td>{{$modal->vendorId}}</td>
                  <td>{{CommonHelper::getUserName($modal->user_id)}}</td>
                  <td>{{CommonHelper::getFirmwareName($modal->firmware_id)}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($modal->created_at)}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($modal->updated_at)}}</td>
                  <td>
                      <form id="deleteForm-{{$modal->id}}" action="" method="post">
                      @csrf
                      @method('DELETE')
                      <button type="button" class="btn btn-danger btn-sm" onclick="showDeleteModal({{$modal->id}})">
                        Delete
                      </button>
                      </form>
                  </td>
                </tr>
                <div class="modal" id="deleteModal{{$modal->id}}" aria-hidden="true">
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
                          <button type="button" class="btn btn-warning" onclick="confirmDelete({{$modal->id}},false)">No</button>
                          <button type="button" class="btn btn-danger" onclick="confirmDelete({{$modal->id}},true)">Yes</button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php
                $i++;
                ?>
                @endforeach
              </tbody>
            </table>
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
    $('#deleteModal' + id).modal('show');
  }
  function confirmDelete(id, response) {
      alert(id);
    const urlType = `{{ $url_type }}`; // Capture Blade variable in JavaScript
    const form = document.getElementById('deleteForm-' + id);
    form.action = `/${urlType}/delete-modal/${id}/${response}`;
    form.submit();
  }
  $(document).ready(function() {
    $("#esim").dataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      // pageLength: 10,
      // scrollX: true,
      // scrollY: '500px',
      scrollCollapse: true,
      "aLengthMenu": [
        [25, 50, 100, 500, -1],
        [25, 50, 100, 500, "All"]
      ],
      "iDisplayLength": 25
    });

  });

  function openModel(id) {
    $('.error_msg').hide().text();
    $('#firmwareId' + id).val(id);
    $('#addModel' + id).modal('show');

  }
  function getModelById(firmwareId){
    $('.hide-field').hide();
    $("#modalName").val("");
    $("#vendorId").val("");
    $("#modalId").val("");
    let id = $('#userAssign').val();
    $.ajax({
    url: `/admin/getModelById/`+id +`/`+firmwareId,
    type: 'GET',
    processData: false,
    contentType: false,
    success: function(response) {
      let result = JSON.parse(response);
      if (result.status == 200 && result.modal != null) {
        if(result.modal){
        $('.hide-field').show();
        $("#modalName").val(result.modal.name);
        $("#vendorId").val(result.modal.vendorId);
         $("#modalId").val(result.modal.id);
        }
      }else{
           $('.hide-field').show();
      }
    },
  });
  }
  $(document).ready(function() {
      $(document).on('click', '.addModalFormBtn', function() {
      // Identify the specific modal and its form
      var $modal = $(this).closest('.modal');
      var $form = $modal.find('form');
      var $errorMsg = $modal.find('.error_msg');
      var $modalName = $modal.find('#modalName');
      var $user = $modal.find('#userAssign');

      function validateForm() {
        let isValid = true;
        let errorMessage = '';

        // Check if 'modalName' is empty
        if ($modalName.val().trim() === '') {
          isValid = false;
          errorMessage += 'Modal Name is required.<br>';
        }
        if ($user.val() === '' || $user.val() == null) {
          isValid = false;
          errorMessage += 'User is required.<br>';
        }
        if (!isValid) {
          $errorMsg.show();
          $errorMsg.html(errorMessage);
        } else {
          $errorMsg.hide();
        }
        return isValid;
      }

      function checkModalNameUnique() {
        return new Promise((resolve, reject) => {
          let isValid = true;
          var formData = new FormData($form[0]);
          $.ajax({
            url: '/admin/check-modal-name',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
              let result = response;
              if (result.status == 200) {
                resolve(isValid);
              } else {
                isValid = false;
                let errorMessage = result.message;
                $errorMsg.show();
                $errorMsg.html(errorMessage);
                resolve(isValid);
              }
            },
            error: function(xhr, status, error) {
              alert('An error occurred while checking the modal name.');
              reject(error);
            }
          });
        });
      }

      async function submitForm() {
        if (validateForm()) {
          try {
            let isUnique = await checkModalNameUnique();
            let modalId = $("#modalId").val();
            if (isUnique && modalId == "" ) {
              var formData = new FormData($form[0]);
              $.ajax({
                url: '/admin/create-modal',
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
                    alert('An error occurred.');
                  }
                },
                error: function(xhr, status, error) {
                  alert('An error occurred while adding the modal.');
                }
              });
            }else{
                var formData = new FormData($form[0]);
              $.ajax({
                url: '/admin/update-modal',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                  let result = JSON.parse(response);
                  if (result.status == 200) {
                    alert(result.message);
                    $modal.modal('hide');
                    window.location.reload();
                  } else {
                    alert('An error occurred.');
                  }
                },
                error: function(xhr, status, error) {
                  alert('An error occurred while adding the modal.');
                }
              });
            }
          } catch (error) {
            console.error('Error submitting the form:', error);
          }
        }
      }

      submitForm();
    });


  });
</script>