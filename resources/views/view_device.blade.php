@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-device') }}">
@endpush
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
              <div class="{{ Auth::user()->user_type == 'Admin' ? 'col-lg-6' : 'col-lg-12' }}">
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
              @include('partials.gps-inline-alerts')
            </div>
            <div class="tabs">
              <?php echo CommonHelper::getDeviceCategoryTabs($device, $show_acc_wise, $url_type, Session::get('device_category_id'), !empty($server_side), $list_mode ?? 'assigned'); ?>
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

<div class="gps-toast-container" id="gpsToastContainer"></div>
<script>
  function showToast(msg, type, title) {
    type = type || 'warning';
    if (typeof window.showGpsToast === 'function') {
      var map = { warning: 'warning', success: 'success', error: 'error', danger: 'error', info: 'info' };
      var t = map[type] || 'warning';
      var titles = { warning: 'Warning', success: 'Success', error: 'Error', info: 'Information' };
      var resolvedTitle = title || titles[type] || titles[t] || 'Notice';
      window.showGpsToast(t, resolvedTitle, msg, { durationMs: 5000 });
      return;
    }
    var icons = { warning: 'fa-exclamation-triangle', success: 'fa-check-circle', error: 'fa-times-circle' };
    var titles = { warning: 'Warning', success: 'Success', error: 'Error' };
    title = title || titles[type];
    var container = document.getElementById('gpsToastContainer');
    var toast = document.createElement('div');
    toast.className = 'gps-toast toast-' + type;
    toast.innerHTML =
      '<div class="gps-toast-icon"><i class="fa ' + icons[type] + '"></i></div>' +
      '<div class="gps-toast-body"><p class="gps-toast-title">' + title + '</p><p class="gps-toast-msg">' + msg + '</p></div>' +
      '<button class="gps-toast-close">&times;</button>' +
      '<div class="gps-toast-progress"></div>';
    toast.querySelector('.gps-toast-close').addEventListener('click', function() {
      toast.classList.add('removing');
      setTimeout(function() { toast.remove(); }, 300);
    });
    container.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) {
        toast.classList.add('removing');
        setTimeout(function() { toast.remove(); }, 300);
      }
    }, 3000);
  }

  // Initialize a server-side (AJAX) DataTable once, on demand.
  function initServerSideTable($table) {
    if (!$table.length || $table.data('dt-initialized')) {
      return;
    }
    $table.data('dt-initialized', 1);
    var orderableCols = $table.data('orderable-cols') || [];
    $table.DataTable({
      serverSide: true,
      processing: true,
      paging: true,
      searching: true,
      ordering: true,
      lengthChange: true,
      searchDelay: 400,
      pageLength: 25,
      scrollX: true,
      scrollY: '500px',
      autoWidth: false,
      scrollCollapse: true,
      order: [],
      lengthMenu: [
        [25, 50, 100, 500],
        [25, 50, 100, 500]
      ],
      ajax: {
        url: $table.data('ajax-url'),
        data: function(d) {
          d.category_id = $table.data('category-id');
          d.mode = $table.data('list-mode') || 'assigned';
          d.username = $('#searchUser' + $table.data('category-id')).val() || '0';
        }
      },
      columnDefs: [
        { targets: orderableCols, orderable: true },
        { targets: '_all', orderable: false }
      ]
    });
  }

  // Initialize any server-side tables inside a container (e.g. a tab pane).
  function initServerTablesIn(selector) {
    $(selector).find('table[data-server-side="1"]').each(function() {
      initServerSideTable($(this));
    });
  }

  $(document).ready(function() {
    function initializeDataTables() {
      $('.example').not('[data-server-side="1"]').each(function() {
        var elementId = $(this).attr('id');
        if ($.fn.DataTable.isDataTable("#" + elementId)) {
          $("#" + elementId).DataTable().destroy();
        }
        $("#" + elementId).DataTable({
          paging: true,
          searching: true,
          ordering: true,
          lengthChange: true,
          pageLength: 25,
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
                // Server-side tables load lazily: only the visible tab fetches data.
                initServerTablesIn('#' + tabMatch[1]);
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

    $(document).on('submit', '.vdc-filter-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();
        
        $('#loading').show();
        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function(response) {
                var newContent = $(response).find('.c_content > .tabs').html();
                $('.c_content > .tabs').html(newContent);
                initializeDataTables();
                
                let activeTabId = form.closest('.tabcontent').attr('id');
                $('.tabcontent').hide();
                $('.tablinks').removeClass('active');
                if (activeTabId) {
                    $('#' + activeTabId).show();
                    $('.tablinks[onclick*="' + activeTabId + '"]').addClass('active');
                    initServerTablesIn('#' + activeTabId);
                } else {
                    let firstTab = $('.tablinks').first();
                    if (firstTab.length) {
                        firstTab.addClass('active');
                        let onclick = firstTab.attr('onclick');
                        if (onclick) {
                            let tabMatch = onclick.match(/'([^']+)'/);
                            if (tabMatch) {
                                $('#' + tabMatch[1]).show();
                                initServerTablesIn('#' + tabMatch[1]);
                            }
                        }
                    }
                }
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
            },
            error: function(xhr) {
                $('#loading').hide();
                showToast('Failed to filter data.', 'error');
            }
        });
    });

    $(document).on('click', '.user-responsive', function(e) {
      var allVals = []; 
      
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var categoryId = $(this).data('category-id');
        $("#user-responsive" + categoryId).modal('show');
      }

    });
    $(document).on('click', '.template-responsive', function(e) {
      var allVals = [];
       let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var categoryId = $(this).data('category-id');
        $("#template-responsive" + categoryId).modal('show');
      }
    });
    $(document).on('click', '.delete_all', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure want to delete these ' + allVals.length + ' Devices?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            background: '#1e293b',
            color: '#f8fafc'
        }).then((result) => {
            if (result.isConfirmed) {
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
                    showToast(data['success'], "success", "Success");
                    setTimeout(function(){ location.reload(); }, 1500);
                  } else if (data['error']) {
                    showToast(data['error'], "error", "Error");
                  } else {
                    // alert('Whoops Something went wrong!!');
                  }
                },
                error: function(data) {
                  showToast(data.responseText, "error", "Error");
                }
              });
            }
        });
      }
    });
    $(document).on('click', '.user_assign_all', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
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
    $(document).on('click', '.temp_assign_all', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
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

      // First time this tab is opened: initialize its server-side table (lazy load).
      if (typeof initServerTablesIn === 'function') {
          initServerTablesIn('#' + tabName);
      }

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



