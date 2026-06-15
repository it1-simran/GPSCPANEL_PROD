<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-backend') }}">
@endpush
@section('content')

<section id="main-content" class="view-backend-page">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="vb-breadcrumb vb-breadcrumb--scroll" aria-label="Breadcrumb">
          <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
          <a href="{{ url('admin') }}" class="bc-item">Home</a>
          <span class="bc-sep">›</span>
          <a href="#" class="bc-item">Firmware Management</a>
          <span class="bc-sep">›</span>
          <span class="bc-item active">View Backend</span>
        </nav>
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Firmware Management</a></li>
            <li class="active"><a href="#">View Backend</a></li>
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
            <div class="row bgx-title-container vb-page-title-row">
              <div class="col-xs-12 col-lg-6">
                <h2>View Backend</h2>
              </div>
              <div class="col-xs-12 col-lg-6 text-right vb-title-actions-wrap">
                <div class="vb-title-actions">
                  @if(Auth::user()->user_type == "Admin")
                  <a href="{{ route('backend.excel') }}" class="btn btn-dl-excel"><i class="fa fa-file-excel-o"></i>Download Excel</a>
                  <a href="{{ route('backend.csv') }}" class="btn btn-dl-csv"><i class="fa fa-file-text-o"></i>Download CSV</a>
                  @endif
                  <button type="button" class="btn btn-add-backend" onclick="openModel()" style="margin-top: 1px;">
                    <i class="fa fa-plus"></i>Add Backend
                  </button>
                </div>
              </div>
            </div>

            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content">
            <div class="row" id="alert_msg">
              @include('partials.gps-inline-alerts')
            </div>
            <div class="vb-table-wrap">
            <table id="esim" class="example table table-bordered table-condensed cf vb-backend-table" style="border-spacing: 0; width: 100%; font-size: 14px;">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Name</th>
                  <th>No of Firmware</th>
                  <th style="width: 12px;">Created at</th>
                  <th>Last Edit</th>
                  <th style="min-width: 96px; text-align: center;">Actions</th>
                </tr>
              </thead>
              <?php
              $i =  1;
              ?>
              <tbody>
                @foreach ($backend as $back)
                <tr>
                  <td><?php echo $i; ?></td>
                  <td>{{$back->name}}</td>
                  <td>{{$back->firmwares_count}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($back->created_at)}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($back->updated_at)}}</td>
                  <td class="text-center">
                    <div class="vb-actions-inner">
                      <button type="button" class="btn vb-icon-btn vb-icon-btn-edit" onclick='editBackend(@json($back))' title="Edit" aria-label="Edit"><i class="fa fa-pencil"></i></button>
                      <form action="/{{$url_type}}/delete-backend/{{ $back->id }}" method="post" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn vb-icon-btn vb-icon-btn-delete swal-confirm" data-confirm-msg="Are you sure you want to delete this?" title="Delete" aria-label="Delete"><i class="fa fa-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
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
<div class="modal gp-managed-modal" id="addBackend" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addBackendform" onsubmit="return false" method="post">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addBackendLabel"><i class="fa fa-server gp-man-modal-title-icon" aria-hidden="true"></i><span id="addBackendModalTitleText">Add Backend</span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
          <div class="margin-bottom-10">
            <label for="name" class="form-label">Backend Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          <input type="hidden" name="backendId" id="backendId" value="" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn gp-modal-btn-close" data-dismiss="modal">Close</button>
          <button type="submit" id="SubmitBackend" class="btn gp-modal-btn-submit" form="addBackendform">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  function editBackend(backendData) {
    $('#addBackendModalTitleText').text('Edit Backend');
    $('#name').val(backendData.name);
    $('#backendId').val(backendData.id);
    $("#addBackend").modal();
  }

  function openModel() {
    $('#addBackendModalTitleText').text('Add Backend');
    $('#backendId').val('');
    $("#addBackend").modal();
  }
  
  $(document).ready(function() {

      $('.example').each(function () {
        var elementId = $(this).attr('id');
        var $t = $('#' + elementId);
        if ($.fn.DataTable.isDataTable('#' + elementId)) {
          $t.DataTable().destroy();
        }
        var dt = $t.DataTable({
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
              try { this.api().columns.adjust(); } catch (e) { /* noop */ }
            },
            drawCallback: function () {
              try { this.api().columns.adjust(); } catch (e) { /* noop */ }
            }
        });
        try { dt.columns.adjust().draw(false); } catch (e) { /* noop */ }
    });

    $(window).on('resize orientationchange', function () {
      $('.example').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
          try { $(this).DataTable().columns.adjust(); } catch (e) { /* noop */ }
        }
      });
    });

    // $('.example').each(function() {
    //   var elementId = $(this).attr('id');
    //   $("#" + elementId).dataTable({
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
            if (result.status == 200) {
              if (typeof window.showGpsToast === 'function') {
                window.showGpsToast('success', 'Success', result.status_msg);
              } else {
                alert(result.status_msg);
              }
              $('#addBackend').modal('hide');
              setTimeout(function() {
                window.location.reload();
              }, 1500);
            } else {
              $('.error_msg').show();
              $('.error_msg').html(result.status_msg || 'error Occured');
            }
          },
          error: function(xhr, status, error) {
            alert('An error occurred while adding the eSIM.');
          }
        });
      }
    });
  });
</script>
@stop