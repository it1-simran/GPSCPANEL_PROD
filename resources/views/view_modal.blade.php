<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-modal') }}">
@endpush
@section('content')
@php
  $routePrefix = $url_type ?? 'admin';
@endphp

<section id="main-content" class="view-models-page">
  <section class="wrapper">
    <div class="dc-breadcrumb-wrap">
      <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Firmware Management</span>
        <span class="bc-sep">›</span>
        <span class="bc-item active">@if(isset($firmware_id)) View Firmware Models @else View Models @endif</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container vm-page-title-row">
              <div class="col-xs-12 col-lg-6">
                <h2 class="vm-panel-title"><i class="fa fa-list-alt"></i> @if(isset($firmware_id)) View Firmware Models @else View Models @endif</h2>
              </div>
              @if(Auth::user()->user_type == "Admin" && isset($firmware_id))
              <div class="col-xs-12 col-lg-6 text-right vm-title-actions-wrap">
                <button type="button" class="btn vm-btn-add-model" onclick="openModel({{ $firmware_id }})"><i class="fa fa-plus"></i> Add Model</button>
              </div>
              @endif
            </div>

            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content">
            <div class="row" id="alert_msg">
              @include('partials.gps-inline-alerts')
            </div>
            @if(Auth::user()->user_type == "Admin" && isset($firmware_id))
            <div class="modal vm-add-model-modal" id="addModel{{$firmware_id}}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addModellLabel">Add Model</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="addModalForm" onsubmit="return false" method="post">
                      @csrf
                      <div class="modal-body">
                        <!-- Form to Add eSIM -->
                        <div class="vm-modal-alert alert alert-danger error_msg" role="alert" style="display:none"></div>
                        <div class="vm-modal-field">
                          <label for="userAssign" class="vm-modal-label">Assign Account</label>
                          <select id="userAssign" name="userAssign" class="form-control userAssign" onchange="getModelById({{$firmware_id}})">
                              <option value="">Please Select</option>
                            @foreach($users as $user)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="vm-modal-field hide-field" style="display:none;">
                          <label for="modalName" class="vm-modal-label">Model Name</label>
                          <input type="text" class="form-control" id="modalName" name="modalName" required>
                        </div>
                        <div class="vm-modal-field hide-field" style="display:none;">
                          <label for="vendorId" class="vm-modal-label">Vendor Id</label>
                          <input type="text" class="form-control" id="vendorId" name="vendorId" required>
                        </div>
                      
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn vm-modal-btn-close" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn vm-modal-btn-submit addModalFormBtn">Submit</button>
                        <input type="hidden" name="modalId" id="modalId" value =""/>
                        <input type="hidden" name="firmwareId" id="firmwareId{{$firmware_id}}" value="" />
                      </div>
                    </form>
                  </div>
                </div>
            </div>
            @endif
            <div class="vm-table-wrap">
            {{-- Rows load one page at a time via /{url_type}/models-list-data --}}
            <table id="esim" class="example table table-bordered table-striped cf vm-datatable-table"
              data-server-side="1"
              data-ajax-url="/{{ $url_type }}/models-list-data"
              data-firmware-id="{{ $firmware_id ?? '' }}"
              style="border-spacing: 0; width: 100%; font-size: 14px;">
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
              <tbody></tbody>
            </table>
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
    Swal.fire({
      title: 'Confirm Deletion',
      text: 'Are you sure you want to delete this firmware from All Devices?',
      icon: 'warning',
      showCancelButton: true,
      showDenyButton: true,
      confirmButtonColor: '#3085d6',
      denyButtonColor: '#dc2626',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes',
      denyButtonText: 'No',
      cancelButtonText: 'Cancel',
      background: '#1e293b',
      color: '#f8fafc'
    }).then((result) => {
      if (result.isConfirmed) {
        confirmDelete(id, true);
      } else if (result.isDenied) {
        confirmDelete(id, false);
      }
    });
  }
  function confirmDelete(id, response) {
    const urlType = `{{ $url_type }}`; // Capture Blade variable in JavaScript
    const form = document.getElementById('deleteForm-' + id);
    form.action = `/${urlType}/delete-modal/${id}/${response}`;
    form.submit();
  }


  // $(document).ready(function() {
  //   $("#esim").dataTable({
  //     paging: true,
  //     searching: true,
  //     info: true,
  //     ordering: true,
  //     lengthChange: true,
  //     // pageLength: 10,
  //     // scrollX: true,
  //     // scrollY: '500px',
  //     scrollCollapse: true,
  //     "aLengthMenu": [
  //       [25, 50, 100, 500, -1],
  //       [25, 50, 100, 500, "All"]
  //     ],
  //     "iDisplayLength": 25
  //   });
  // });

  $(document).ready(function () {
      var $tbl = $('#esim');
      if (!$tbl.length || !$.fn.DataTable) return;

      var dt = $tbl.DataTable({
          serverSide: true,
          processing: true,
          paging: true,
          searching: true,
          searchDelay: 400,
          info: true,
          ordering: true,
          lengthChange: true,

          responsive: false,
          autoWidth: false,
          scrollX: true,
          scrollCollapse: false,

          order: [],
          ajax: {
              url: $tbl.data('ajax-url'),
              data: function (d) {
                  d.firmware_id = $tbl.data('firmware-id') || '';
              }
          },
          columnDefs: [
              { targets: [1, 2, 3, 4, 5, 6], orderable: true },
              { targets: '_all', orderable: false }
          ],
          lengthMenu: [
              [25, 50, 100, 500],
              [25, 50, 100, 500]
          ],
          pageLength: 25,
          dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
          initComplete: function () {
            try { this.api().columns.adjust(); } catch (e) {}
          },
          drawCallback: function () {
            try { this.api().columns.adjust(); } catch (e) {}
          }
      });

      $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search models...');

      $(window).on('resize orientationchange', function () {
        try { dt.columns.adjust(); } catch (e) {}
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