<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content')
@php
  $routePrefix = $url_type ?? 'admin';
@endphp
<style>
  #main-content.view-models-page .wrapper { padding-top: 8px !important; }
  .view-models-page .dc-breadcrumb-wrap { padding: 4px 0 10px 0; margin: 0; }
  .view-models-page .dc-breadcrumb {
    display: inline-flex; align-items: center; flex-wrap: wrap; row-gap: 6px;
    background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
  }
  .view-models-page .dc-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0; color: #1e293b; text-decoration: none;
  }
  .view-models-page .dc-breadcrumb .bc-home i { font-size: 13px; }
  .view-models-page .dc-breadcrumb .bc-item {
    color: rgba(255,255,255,.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;
  }
  .view-models-page .dc-breadcrumb .bc-sep { color: rgba(255,255,255,.35); margin: 0 8px; font-size: 12px; }
  .view-models-page .dc-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
  .view-models-page .dc-breadcrumb a.bc-item:hover { color: #e2e8f0; }

  .view-models-page .dc-breadcrumb.dc-breadcrumb--scroll {
    max-width: 100%;
    flex-wrap: nowrap !important;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }

  .view-models-page .dc-breadcrumb.dc-breadcrumb--scroll .bc-home {
    flex-shrink: 0;
  }

  .view-models-page .dc-breadcrumb-wrap + .row { margin-top: 2px; }
  .view-models-page .c_panel { margin-top: 0 !important; overflow: visible !important; }
  .view-models-page .c_title h2::before { content: none !important; display: none !important; }
  .view-models-page .vm-panel-title {
    display: inline-flex !important; align-items: center; gap: 8px; margin: 0;
    color: #fff !important; font-size: 15px !important; font-weight: 800 !important;
    letter-spacing: 0.5px; text-transform: uppercase;
    justify-content: flex-start;
    text-align: left !important;
  }
  .view-models-page .vm-panel-title > i { color: #76CF1C; font-size: 14px; }
  .view-models-page .bgx-title-container > [class*="col-"] { text-align: left !important; }
  /* overflow visible: .dataTables_scroll inner body handles horizontal scroll (thead + columns aligned) */
  .view-models-page .vm-table-wrap { border: 1px solid #e2e8f0; border-radius: 10px; overflow: visible; background: #fff; margin-top: 4px; }
  .view-models-page .dataTables_wrapper {
    width: 100% !important;
    max-width: 100%;
    box-sizing: border-box;
  }
  .view-models-page .dataTables_wrapper > .row { margin-left: 0 !important; margin-right: 0 !important; }
  .view-models-page .dataTables_wrapper .row:first-child { padding: 10px 0 12px; background: #fafbfc; border-bottom: 1px solid #e2e8f0; }
  .view-models-page .dataTables_wrapper .row:first-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
  .view-models-page .dataTables_wrapper > .row:nth-child(2) { overflow: visible !important; }
  .view-models-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] { min-width: 0; padding-left: 0; padding-right: 0; }

  .view-models-page .dataTables_scroll { clear: both; width: 100% !important; }
  .view-models-page .dataTables_scrollHead { overflow: hidden !important; }
  .view-models-page .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: visible !important;
    -webkit-overflow-scrolling: touch;
  }

  .view-models-page .dataTables_scrollHead table.dataTable,
  .view-models-page .dataTables_scrollBody table.dataTable {
    table-layout: auto !important;
    margin: 0 !important;
  }

  .view-models-page .dataTables_wrapper > .row:last-child { padding: 10px 0 8px !important; border-top: 1px solid #e8ecf1; background: #fafbfc; }
  .view-models-page .dataTables_wrapper > .row:last-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
  .view-models-page table.dataTable.vm-datatable-table {
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    width: 100% !important;
    table-layout: fixed !important;
  }
  .view-models-page .vm-datatable-table thead th,
  .view-models-page .vm-datatable-table tbody td {
    min-width: 0;
    box-sizing: border-box;
    overflow-wrap: break-word;
    word-break: break-word;
  }
  .view-models-page .vm-datatable-table thead th {
    background: #f8fafc !important;
    color: #1e293b !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    text-transform: uppercase;
    letter-spacing: .04em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 12px 10px !important;
    white-space: normal;
    line-height: 1.25;
  }
  .view-models-page .vm-datatable-table tbody td {
    font-size: 13px;
    color: #334155;
    padding: 12px 10px !important;
    border-top: none !important;
  }
  .view-models-page .vm-datatable-table tbody tr:hover td { background: #fafbfc !important; }
  .view-models-page .btn-vm-delete {
    width: 34px;
    height: 30px;
    padding: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
  }
  .view-models-page .btn-vm-delete i { margin: 0; }

  @media (max-width: 991px) {
    .view-models-page .dc-breadcrumb-wrap {
      padding: 2px 0 6px 0;
    }

    .view-models-page .c_content {
      padding-left: 12px !important;
      padding-right: 12px !important;
    }

    .view-models-page .col-lg-12.text-right.margin-bottom-10 {
      text-align: center !important;
    }

    .view-models-page .col-lg-12.text-right.margin-bottom-10 .btn {
      width: 100% !important;
      max-width: 100% !important;
      min-height: 44px !important;
    }

    .view-models-page .dataTables_wrapper .row:first-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: left !important;
      margin-bottom: 10px !important;
    }

    .view-models-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }

    .view-models-page .dataTables_wrapper .dataTables_filter {
      width: 100% !important;
    }

    .view-models-page .dataTables_wrapper .dataTables_filter input {
      width: 100% !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      box-sizing: border-box !important;
    }

    .view-models-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: center !important;
      margin-bottom: 8px !important;
    }

    .view-models-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }

    .view-models-page .vm-table-wrap {
      overflow-x: visible !important;
      overflow-y: visible !important;
    }
  }
</style>
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
            <div class="row bgx-title-container">
              <div class="col-lg-12 col-md-12">
                <h2 class="vm-panel-title"><i class="fa fa-list-alt"></i> @if(isset($firmware_id)) View Firmware Models @else View Models @endif</h2>
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
            <div class="vm-table-wrap">
            <table id="esim" class="example table table-bordered table-striped cf vm-datatable-table" style="border-spacing: 0; width: 100%; font-size: 14px;">
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
                      <button type="button" class="btn btn-danger btn-sm btn-vm-delete" title="Delete" aria-label="Delete" onclick="showDeleteModal({{$modal->id}})">
                        <i class="fa fa-trash" aria-hidden="true"></i>
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
    $('#deleteModal' + id).modal('show');
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
          paging: true,
          searching: true,
          info: true,
          ordering: true,
          lengthChange: true,

          responsive: false,
          autoWidth: false,
          scrollX: true,
          scrollCollapse: false,

          lengthMenu: [
              [25, 50, 100, 500, -1],
              [25, 50, 100, 500, "All"]
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