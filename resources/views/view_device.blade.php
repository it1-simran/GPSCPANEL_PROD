@extends('layouts.apps')
@section('content')
<?php

use Illuminate\Support\Facades\Auth;
use App\Helper\CommonHelper;

$idsArray = [1];

$currentEmail = Auth::user()->email;
?>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  #main-content .wrapper { padding-top: 10px !important; }

  /* Breadcrumb (reference style) */
  .vd-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .vd-breadcrumb {
    display: inline-flex;
    align-items: center;
    background: #1e293b;
    border-radius: 50px;
    padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
    gap: 0;
  }
  .vd-breadcrumb .bc-home {
    width: 30px; height: 30px; border-radius: 50%;
    background: #76CF1C; color: #1e293b; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
  }
  .vd-breadcrumb .bc-item {
    color: rgba(255,255,255,0.65);
    font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;
  }
  .vd-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
  .vd-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

  #main-content .c_panel {
    border: 0 !important;
    border-radius: 12px !important;
    box-shadow: 0 6px 24px rgba(15,23,42,0.08) !important;
    overflow: hidden;
  }
  #main-content .c_title {
    background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%) !important;
    padding: 16px 22px !important;
    border-bottom: 1px solid rgba(118,207,28,0.2) !important;
    margin-bottom: 0 !important;
  }
  #main-content .c_title h2 {
    margin: 0 !important;
    color: #fff !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
  }
  #main-content .c_content.tabs { padding: 16px 16px 10px !important; }

  /* Tabs + actions (approval-request reference style) */
  #main-content .tabs .tablinks {
    border: 1px solid #e2e8f0 !important;
    background: #f8fafc !important;
    color: #64748b !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    margin-right: 4px !important;
    margin-bottom: 10px !important;
    transition: all 0.2s ease !important;
  }
  #main-content .tabs .tablinks.active {
    background: #76CF1C !important;
    border-color: #76CF1C !important;
    color: #1e293b !important;
    box-shadow: 0 2px 8px rgba(118,207,28,0.25) !important;
  }
  #main-content .tabs .tablinks:hover {
    border-color: #cbd5e1 !important;
    background: #f1f5f9 !important;
  }

  #main-content .tabs .tabcontent > div[style*="margin-bottom:15px"] {
    margin-bottom: 12px !important;
    display: flex;
    flex-wrap: wrap;
    gap: 8px 10px;
    align-items: center;
  }
  #main-content .tabs .tabcontent .vdc-tab-toolbar {
    margin-bottom: 12px !important;
  }
  #main-content .tabs .vdc-tab-toolbar .form-control.input-sm {
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
  }
  /* Keep Account dropdown + Filter button on one row (Bootstrap .form-control is width:100% by default) */
  #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form {
    flex-wrap: nowrap !important;
    align-items: center !important;
  }
  #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form select.form-control {
    width: auto !important;
    max-width: min(280px, 42vw);
    min-width: 160px;
    display: inline-block;
    vertical-align: middle;
  }
  #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form .btn {
    flex-shrink: 0;
    align-self: center;
    white-space: nowrap;
  }
  #main-content .tabs .delete_all,
  #main-content .tabs .user-responsive,
  #main-content .tabs .template-responsive {
    margin-left: 0 !important;
    border: none !important;
    border-radius: 7px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 7px 14px !important;
    box-shadow: 0 2px 8px rgba(15,23,42,0.14) !important;
  }
  #main-content .tabs .delete_all { background: linear-gradient(135deg,#ef4444,#dc2626) !important; }
  #main-content .tabs .user-responsive { background: linear-gradient(135deg,#1e293b,#2d3f55) !important; }
  #main-content .tabs .template-responsive { background: linear-gradient(135deg,#1e293b,#2d3f55) !important; }

  /* Datatable look */
  #main-content .tabs .dataTables_wrapper .dataTables_length label,
  #main-content .tabs .dataTables_wrapper .dataTables_filter label {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_length select,
  #main-content .tabs .dataTables_wrapper .dataTables_filter input {
    height: 32px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0 10px;
    font-size: 12px;
    color: #334155;
    background: #f8fafc;
    box-shadow: none !important;
    text-indent: 0 !important;
    opacity: 1 !important;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_length select {
    min-width: 78px;
    line-height: 30px;
    padding: 0 26px 0 10px !important;
    appearance: auto;
    -webkit-appearance: menulist;
    -moz-appearance: menulist;
    color: #1e293b !important;
    font-weight: 600 !important;
    background-color: #fff !important;
    -webkit-text-fill-color: #1e293b !important;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_length select option {
    color: #1e293b !important;
    background: #fff !important;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_length {
    margin-bottom: 8px;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_filter input {
    min-width: 190px;
    background: #fff !important;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 {
    margin-bottom: 10px !important;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn {
    margin: 0 0 0 8px !important;
    border-radius: 7px !important;
    padding: 7px 14px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(15,23,42,0.14) !important;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn.btn-success:first-child {
    background: linear-gradient(135deg,#1e293b,#2d3f55) !important;
    color: #fff !important;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn.btn-success:last-child {
    background: linear-gradient(135deg,#76CF1C,#67bb19) !important;
    color: #0f172a !important;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_scrollHead,
  #main-content .tabs .dataTables_wrapper .dataTables_scrollBody {
    border-color: #e5e7eb !important;
  }
  #main-content .tabs table.example thead th {
    background: transparent !important;
    color: #64748b !important;
    font-size: 11px !important;
    text-transform: uppercase;
    font-weight: 700 !important;
    border-bottom: 2px solid #f1f5f9 !important;
  }
  #main-content .tabs table.example tbody td {
    background: #fff !important;
    font-size: 12px !important;
    color: #334155 !important;
  }
  #main-content .tabs table.example thead th:first-child {
    min-width: 110px !important;
    white-space: nowrap !important;
  }
  #main-content .tabs table.example thead th:first-child input[type="checkbox"] {
    width: 14px;
    height: 14px;
    margin-left: 6px;
    vertical-align: middle;
    position: relative;
    top: -1px;
  }
  #main-content .tabs table.example tbody td:first-child input[type="checkbox"] {
    width: 14px;
    height: 14px;
    vertical-align: middle;
  }

  /* Assign Account/Template modals */
  .modal[id^="user-responsive"],
  .modal[id^="template-responsive"] {
    background: rgba(15, 23, 42, 0.45);
  }
  .modal[id^="user-responsive"] .modal-dialog,
  .modal[id^="template-responsive"] .modal-dialog {
    margin-top: 90px;
  }
  .modal[id^="user-responsive"] .modal-content,
  .modal[id^="template-responsive"] .modal-content {
    border: 0 !important;
    border-radius: 12px !important;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(2, 8, 23, 0.32);
  }
  .modal[id^="user-responsive"] .modal-header,
  .modal[id^="template-responsive"] .modal-header {
    background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%);
    border-bottom: 1px solid rgba(118, 207, 28, 0.2);
    padding: 14px 18px;
  }
  .modal[id^="user-responsive"] .modal-header .modal-title,
  .modal[id^="template-responsive"] .modal-header .modal-title {
    color: #ffffff !important;
    font-size: 16px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .2px;
  }
  .modal[id^="user-responsive"] .modal-header .close,
  .modal[id^="template-responsive"] .modal-header .close {
    color: #cbd5e1 !important;
    opacity: 0.9;
    text-shadow: none;
    margin-top: 4px !important;
    position: relative;
    top: 2px;
  }
  .modal[id^="user-responsive"] .modal-body,
  .modal[id^="template-responsive"] .modal-body {
    padding: 16px 18px 8px;
    background: #f8fafc;
  }
  .modal[id^="user-responsive"] .form-label,
  .modal[id^="template-responsive"] .form-label {
    display: block;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .35px;
  }
  .modal[id^="user-responsive"] select.form-control,
  .modal[id^="template-responsive"] select.form-control {
    min-height: 38px;
    border: 1px solid #d5deea !important;
    border-radius: 8px !important;
    color: #1e293b !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }
  .modal[id^="user-responsive"] .text-center[style*="font-size: 14px"],
  .modal[id^="template-responsive"] .text-center[style*="font-size: 14px"] {
    margin-top: 12px !important;
    font-size: 13px !important;
    line-height: 1.5;
    color: #475569;
    text-align: left !important;
    background: #eef2f7;
    border: 1px solid #dde6f1;
    border-radius: 8px;
    padding: 10px 12px;
  }
  .modal[id^="user-responsive"] .modal-footer,
  .modal[id^="template-responsive"] .modal-footer {
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 10px 18px 14px;
    text-align: center;
  }
  .modal[id^="user-responsive"] .modal-footer .btn,
  .modal[id^="template-responsive"] .modal-footer .btn {
    min-width: 130px;
    border: none !important;
    border-radius: 8px !important;
    background: linear-gradient(135deg, #1e293b, #2d3f55) !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    padding: 9px 14px !important;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.22);
  }
</style>
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="vd-breadcrumb-wrap">
      <nav class="vd-breadcrumb">
        <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Device Management</span>
        <span class="bc-sep">›</span>
        @if(Auth::user()->user_type=='Admin' and url('admin/view-device-assign')==url()->current())
          <span class="bc-item active">View Assigned Devices</span>
        @elseif(Auth::user()->user_type=='Admin' and url('admin/view-device-unassign')==url()->current())
          <span class="bc-item active">View Unassigned Devices</span>
        @else
          <span class="bc-item active">View Devices</span>
        @endif
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title" style="margin-bottom: 10px;">
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                @if(Auth::user()->user_type=='Admin' and url('admin/view-device-assign')==url()->current())
                <h2>Show Assigned Devices</h2>
                @elseif(Auth::user()->user_type=='Admin' and url('admin/view-device-unassign')==url()->current())
                <h2>Show Unassigned Devices</h2>
                @else
                <h2>Show Device</h2>
                @endif
              </div>
              @if (Auth::user()->user_type == 'Admin')
              <div class="col-lg-6 text-right">
                <a href="/{{$url_type}}/add-device" class="btn btn-success"> Add Device </a>
              </div>
              @endif
            </div>
            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content tabs">
            <div class="row" id="alert_msg">
              <div class="col-sm-12 alert alert-success alert-success-error" role="alert" style="display:none;"></div>
              <div class="col-sm-12 alert alert-danger alert-danger-error" role="alert" style="display:none;"></div>
              <div class="col-sm-12 alert alert-success" id="demo" role="alert" style="display: none"></div>
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
              <?php echo CommonHelper::getDeviceCategoryTabs($device, $show_acc_wise, $url_type, Session::get('device_category_id')); ?>
              <div id="loading" class="bgx-loading" style="display:none;">
                <img src="/assets/icons/loader.gif" alt="Loading..." />
              </div>
            </div>
            <div style="text-align: center;"></div>
          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->
    </div><!--/row-->
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>

@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="modal" id="certificateModal" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title"><strong>Download Certificate</strong></h4>
      </div>
      <form id="certificateForm" method="post" action="#">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group"><label class="form-label">Certificate Holder Name</label><input type="text" class="form-control" name="holder_name" required></div>
              <div class="form-group"><label class="form-label">Authority City</label><input type="text" class="form-control" name="authority_city" required></div>
              <div class="form-group"><label class="form-label">Fitment Date</label><input type="date" class="form-control" name="fitment_date" required></div>
              <div class="form-group"><label class="form-label">Vehicle Registration No</label><input type="text" class="form-control" name="vehicle_registration_no" required></div>
              <div class="form-group"><label class="form-label">VLTD Serial No</label><input type="text" class="form-control" name="vltd_serial_no" required></div>
              <div class="form-group"><label class="form-label">VLTD Make</label><input type="text" class="form-control" name="vltd_make" required></div>
              <div class="form-group"><label class="form-label">VLTD Model</label><input type="text" class="form-control" name="vltd_model" required></div>
              <div class="form-group"><label class="form-label">Chassis No</label><input type="text" class="form-control" name="chassis_no" required></div>
              <div class="form-group"><label class="form-label">Engine No</label><input type="text" class="form-control" name="engine_no" required></div>
              <div class="form-group"><label class="form-label">Color</label><input type="text" class="form-control" name="color" required></div>
              <div class="form-group"><label class="form-label">Vehicle Model</label><input type="text" class="form-control" name="vehicle_model" required></div>
              <div class="form-group"><label class="form-label">ARAI TAC/COP No</label><input type="text" class="form-control" name="arai_tac" required></div>
              <div class="form-group"><label class="form-label">ARAI Date</label><input type="date" class="form-control" name="arai_date" required></div>
              <div class="form-group"><label class="form-label">Service Provider</label><input type="text" class="form-control" name="service_provider" required></div>
            </div>
          </div>
        </div>
        <div class="modal-footer text-center">
          <button type="button" class="btn btn-info btn-raised rippler rippler-default" id="certificatePreviewBtn"><i class="fa fa-eye"></i> Preview</button>
          <button type="submit" class="btn btn-primary btn-raised rippler rippler-default"><i class="fa fa-download"></i> Download</button>
        </div>
      </form>
    </div>
  </div>
  </div>
<style>
  .example th, .example td {
      white-space: nowrap !important;
  }
</style>
<script>
  $(document).ready(function() {
    function initializeDataTables() {
      $('.example').each(function() {
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
          autoWidth: false,
          scrollCollapse: true,
          "aLengthMenu": [
            [25, 50, 100, 500, -1],
            [25, 50, 100, 500, "All"]
          ],
          "iDisplayLength": 25
        });
      });
      $('#loading').hide();
    }

    // Initialize tabs: hide all, show first
    $('.tabcontent').hide();
    let firstTab = $('.tablinks').first();
    let activeTab = $('.tablinks.active').first(); 
    if(activeTab.length == 0 && firstTab.length) {
        firstTab.addClass('active');
        activeTab = firstTab;
    }
    if (activeTab.length) {
        let onclick = activeTab.attr('onclick');
        if(onclick) {
            let tabMatch = onclick.match(/'([^']+)'/);
            if(tabMatch) {
                $('#' + tabMatch[1]).show();
            }
        }
    }

    // Initialize datatables AFTER making the active tab visible!
    // This allows DataTables to correctly calculate columns width for the visible tab.
    initializeDataTables();

    // Explicitly adjust columns just in case
    setTimeout(function() {
        if ($.fn.DataTable) {
            var dtTables = $.fn.dataTable.tables({ visible: true, api: true });
            if (dtTables && dtTables.columns && typeof dtTables.columns.adjust === 'function') {
              dtTables.columns.adjust();
            } else if (dtTables && typeof dtTables.columns === 'function') {
              dtTables.columns().adjust();
            }
        }
    }, 100);

    $('.dataTables_filter input').attr("placeholder", "Zoeken...");
    $('.dataTables_length select').each(function() {
      if (!$(this).val()) {
        $(this).val('25');
      }
      this.style.color = '#1e293b';
      this.style.backgroundColor = '#fff';
    });
    
    $('#certificatePreviewBtn').on('click', function() {
      var deviceId = $('#certificateForm').data('deviceId');
      if (!deviceId) return;
      var previewUrl = '/user/device/' + deviceId + '/certificate/preview';
      var form = $('#certificateForm');
      var originalAction = form.attr('action');
      form.attr('action', previewUrl);
      form.attr('target', '_blank');
      form.trigger('submit');
      form.attr('action', originalAction);
      form.removeAttr('target');
    });
    $('.user-responsive').on('click', function(e) {
      var allVals = []; 
      
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        alert("Please select Device.");
      } else {
        var categoryId = $(this).data('category-id');
        $("#user-responsive" + categoryId).modal('show');
      }

    });
    $('.template-responsive').on('click', function(e) {
      var allVals = [];
       let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        alert("Please select Device.");
      } else {
        var categoryId = $(this).data('category-id');
        $("#template-responsive" + categoryId).modal('show');
      }
    });
    $('.delete_all').on('click', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        alert("Please select Device.");
      } else {
        var check = confirm("Are you sure want to delete these Device?");
        if (check == true) {
          var join_selected_values = allVals.join(",");
          $.ajax({
            url: $(this).data('url'),
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: 'ids=' + join_selected_values,
            success: function(data) {
              if (data['success']) {
                $(".sub_chk:checked").each(function() {
                  $(this).parents("tr").remove();
                });
                alert(data['success']);
                location.reload();
              } else if (data['error']) {
                alert(data['error']);
              } else {
                // alert('Whoops Something went wrong!!');
              }
            },
            error: function(data) {
              alert(data.responseText);
            }
          });
        }
      }
    });
    $('.user_assign_all').on('click', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        alert("Please select Device.");
      } else {
        var join_selected_values = allVals.join(",");
        var id = $(this).data('attr');
        // var user_id = $("#assignDeviceUser").val();
        var user_id = $(this).closest('.modal-body').find('.assignDeviceUser').val();
        var a_url = $('body').find('button.user-responsive').attr('data-url');

        $.ajax({
          url: a_url,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: join_selected_values,
            user_id: user_id
          },
          success: function(data) {
            if (data['success']) {
              // $(".sub_chk:checked").each(function() {
              //   $(this).parents("tr").remove();
              // });
              $('#user-responsive' + id).modal('hide');
              if (data['success']) {
                $('.alert-success-error').append(data['success']).show();
              }
              if (data['error']) {
                $('.alert-danger-error').append(data['error']).show();
              }
              // location.reload();
            } else if (data['error']) {
              $('#user-responsive' + id).modal('hide');
              $('.alert-danger-error').html(data['error']).show();
            } else {
              alert('Whoops Something went wrong!!');
            }
          },
          error: function(data) {
            alert(data.responseText);
          }
        });
      }
    });
    $('.temp_assign_all').on('click', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        alert("Please select Device.");
      } else {
        var join_selected_values = allVals.join(",");
        var temp_id = $(this).closest('.modal-body').find('.assignDeviceTemp').val();
        var id = $(this).data('attr');
        var a_url = $('body').find('button.template-responsive').attr('data-url');
        $.ajax({
          url: a_url,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: join_selected_values,
            temp_id: temp_id
          },
          success: function(data) {
            if (data['success']) {
              // $(".sub_chk:checked").each(function() {
              //   $(this).parents(l"tr").remove();
              // });

              $('#template-responsive' + id).modal('hide');
              if (data['success']) {
                $('.alert-success-error').append(data['success']).show();
              }
              if (data['error']) {
                $('.alert-danger-error').append(data['error']).show();
              }
              // alert(data['error']);
              // location.reload();
            } else if (data['error']) {
              $('#template-responsive' + id).modal('hide');
              $('.alert-danger-error').html(data['error']).show();

            } else {
              alert('Whoops Something went wrong!!');
            }
          },
          error: function(data) {
            alert(data.responseText);
          }
        });
      }
    });

    $("#temp_id").select2();
    $(".select2").select2();
  });

  function dataTableCheckAll(dataId) {
    if ($('#master'+ dataId).is(':checked', true)) {
      $(".sub_chk"+ dataId).prop('checked', true);
    } else {
      $(".sub_chk"+ dataId).prop('checked', false);
    }
  }

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      $('.tabcontent').hide();
      $('.tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $('.tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      if ($.fn.DataTable) {
          var dtTables = $.fn.dataTable.tables({ visible: true, api: true });
          if (dtTables && dtTables.columns && typeof dtTables.columns.adjust === 'function') {
              dtTables.columns.adjust();
          } else if (dtTables && typeof dtTables.columns === 'function') {
              dtTables.columns().adjust();
          }
      }

      return false;
  }
</script>




