<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
<style>
  #main-content.view-backend-page .wrapper { padding-top: 8px !important; }
  #main-content.view-backend-page .top-page-header { margin: 0 0 10px !important; padding: 0 !important; background: transparent !important; }
  #main-content.view-backend-page .page-breadcrumb { padding: 0 !important; margin: 0 !important; }
  .view-backend-page .vb-breadcrumb {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    background: #13233d;
    border-radius: 999px;
    padding: 8px 18px 8px 8px;
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.22);
  }
  .view-backend-page .vb-breadcrumb.vb-breadcrumb--scroll {
    max-width: 100%;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }
  .view-backend-page .vb-breadcrumb.vb-breadcrumb--scroll .bc-home {
    flex-shrink: 0;
  }
  .view-backend-page .vb-breadcrumb .bc-home {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #76CF1C;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(118, 207, 28, 0.35);
    transition: filter 0.15s ease;
  }
  .view-backend-page .vb-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
  .view-backend-page .vb-breadcrumb a.bc-home:hover { filter: brightness(1.08); }
  .view-backend-page .vb-breadcrumb .bc-item {
    color: rgba(255, 255, 255, 0.65);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
  }
  .view-backend-page .vb-breadcrumb .bc-sep { color: rgba(255,255,255,.35); font-size: 12px; }
  .view-backend-page .vb-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
  .view-backend-page .vb-breadcrumb a.bc-item:hover { color: #e2e8f0; }
  .view-backend-page .vb-title-actions {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
  }
  .view-backend-page .vb-title-actions .btn {
    min-height: 34px;
    border-radius: 10px !important;
    padding: 8px 14px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .view-backend-page .vb-title-actions .btn-dl-excel {
    background: #76CF1C !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
  }
  .view-backend-page .vb-title-actions .btn-dl-csv {
    background: #ffffff !important;
    color: #1e293b !important;
    border: 1px solid #dbe4ef !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
  }
  .view-backend-page .vb-title-actions .btn-add-backend {
    background: #76CF1C !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
  }
  .view-backend-page .c_breadcrumbs { display: none !important; }
  #main-content.view-backend-page .c_breadcrumbs ul {
    margin: 0 !important;
  }
  .view-backend-page .vb-actions-inner {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: nowrap;
  }
  .view-backend-page .vb-actions-inner form { margin: 0; display: inline; }
  .view-backend-page .vb-icon-btn {
    width: 34px;
    height: 34px;
    padding: 0 !important;
    border: none !important;
    border-radius: 8px !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px !important;
    line-height: 1 !important;
    vertical-align: middle;
  }
  .view-backend-page .vb-icon-btn-edit {
    background: #1d283e !important;
    color: #fff !important;
  }
  .view-backend-page .vb-icon-btn-edit:hover { filter: brightness(1.08); color: #fff !important; }
  .view-backend-page .vb-icon-btn-delete {
    background: #e25552 !important;
    color: #fff !important;
  }
  .view-backend-page .vb-icon-btn-delete:hover { filter: brightness(1.06); color: #fff !important; }

  .view-backend-page .c_panel { overflow: visible !important; }
  .view-backend-page .vb-table-wrap {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    overflow: visible;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    margin-top: 2px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
  }
  .view-backend-page .dataTables_wrapper {
    width: 100% !important;
    max-width: 100%;
    box-sizing: border-box;
  }
  .view-backend-page .dataTables_wrapper > .row { margin-left: 0 !important; margin-right: 0 !important; }
  .view-backend-page .dataTables_wrapper .row:first-child { padding: 10px 0 12px; background: #f8fbff; border-bottom: 1px solid #e2e8f0; }
  .view-backend-page .dataTables_wrapper .row:first-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
  .view-backend-page .dataTables_wrapper > .row:nth-child(2) { overflow: visible !important; }
  .view-backend-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] { min-width: 0; padding-left: 0; padding-right: 0; }
  .view-backend-page .dataTables_scroll { clear: both; width: 100% !important; }
  .view-backend-page .dataTables_scrollHead { overflow: hidden !important; }
  .view-backend-page .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: auto !important;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
  }
  .view-backend-page .dataTables_scrollHead table.dataTable.vb-backend-table,
  .view-backend-page .dataTables_scrollBody table.dataTable.vb-backend-table {
    table-layout: auto !important;
    margin: 0 !important;
  }
  .view-backend-page table.dataTable.vb-backend-table {
    border-collapse: collapse !important;
    width: 100% !important;
    min-width: 920px;
  }
  .view-backend-page .vb-backend-table thead th {
    background: linear-gradient(180deg, #132035 0%, #1f314f 100%) !important;
    color: #dbe9ff !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 12px 10px !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
    border-bottom: none !important;
  }
  .view-backend-page .vb-backend-table tbody td {
    padding: 12px 10px !important;
    font-size: 13px;
    color: #243447;
    vertical-align: middle !important;
    border-top: 1px solid #edf2f8 !important;
  }
  .view-backend-page .vb-backend-table tbody tr:nth-child(even) td { background: #fbfdff !important; }
  .view-backend-page .dataTables_wrapper > .row:last-child { padding: 10px 0 8px !important; border-top: 1px solid #e8ecf1; background: #f8fbff; }

  @media (max-width: 991px) {
    .view-backend-page .c_content {
      padding-left: 12px !important;
      padding-right: 12px !important;
    }
    .view-backend-page .c_panel .c_title .bgx-title-container > .vb-title-actions-wrap {
      text-align: center !important;
    }
    .view-backend-page .vb-page-title-row.bgx-title-container {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 12px;
    }
    .view-backend-page .vb-title-actions {
      flex-direction: column !important;
      align-items: stretch !important;
      width: 100% !important;
      justify-content: stretch !important;
      gap: 10px !important;
    }
    .view-backend-page .vb-title-actions .btn {
      width: 100% !important;
      max-width: 100% !important;
      min-height: 44px !important;
      justify-content: center !important;
    }
    .view-backend-page .dataTables_wrapper .row:first-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: left !important;
      margin-bottom: 10px !important;
    }
    .view-backend-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }
    .view-backend-page .dataTables_wrapper .dataTables_filter {
      width: 100% !important;
    }
    .view-backend-page .dataTables_wrapper .dataTables_filter input {
      width: 100% !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      box-sizing: border-box !important;
    }
    .view-backend-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: center !important;
      margin-bottom: 8px !important;
    }
    .view-backend-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }
    .view-backend-page .vb-table-wrap {
      overflow-x: visible !important;
      overflow-y: visible !important;
    }
  }
</style>
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
                        <button type="submit" class="btn vb-icon-btn vb-icon-btn-delete" onclick="return confirm('Are you sure you want to delete this?');" title="Delete" aria-label="Delete"><i class="fa fa-trash"></i></button>
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
              alert(result.status_msg);
              $('#addBackend').modal('hide');
              window.location.reload();
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
  });
</script>
@stop