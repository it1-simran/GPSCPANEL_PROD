@extends('layouts.apps')
@section('content')
<?php

use Illuminate\Support\Facades\Auth;
use App\Helper\CommonHelper;

$idsArray = [1];

$currentEmail = Auth::user()->email;
?>
<meta name="csrf-token" content="{{ csrf_token() }}">
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
          <li><a href="#">Device Management</a></li>
            @if(Auth::user()->user_type=='Admin' and url('admin/view-device-assign')==url()->current())
            <li class="active"><a href="#">View Assigned Devices</a></li>
            @elseif(Auth::user()->user_type=='Admin' and url('admin/view-device-unassign')==url()->current())
            <li class="active"><a href="#">View Unassigned Devices</a></li>
            @endif
          </ul>
        </nav>
      </div>
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

    $('.tablinks').on('click', function() {
      $('#loading').show();
      initializeDataTables();
    });
    $('.dataTables_filter input').attr("placeholder", "Zoeken...");
    //$(document).on('click', '.certificate-button', function() {
      //var deviceId = $(this).data('device-id');
      //window.open('/user/device/' + deviceId + '/certificate', '_blank');
    //});
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
  });

  function dataTableCheckAll(dataId) {
    if ($('#master'+ dataId).is(':checked', true)) {
      $(".sub_chk"+ dataId).prop('checked', true);
    } else {
      $(".sub_chk"+ dataId).prop('checked', false);
    }
  }
  $(document).ready(function() {
    // Initialize select2
    // $('.assignDeviceUser').each(function() {
    //   // Get the ID of each element
    //   var id = $(this).attr('id');

    //   $('#' + id).select2({
    //     'placeholder': 'Select and Search ',
    //     'allowClear': true,
    //   })
    // });
    // $('.assignDeviceTemp').each(function() {
    //   // Get the ID of each element
    //   var id = $(this).attr('id');

    //   $('#' + id).select2({
    //     'placeholder': 'Select and Search ',
    //     'allowClear': true,

    //   })
    // });
    $("#temp_id").select2();
    $(".select2").select2();
  });
</script>
