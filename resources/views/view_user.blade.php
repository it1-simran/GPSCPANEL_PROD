@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-user') }}">
<style>
  /* Compact account table rows */
  #accountsTable.dataTable thead th,
  #accountsTable.dataTable tbody td {
    padding: 6px 10px;
    font-size: 13px;
    line-height: 1.3;
    vertical-align: middle;
    white-space: nowrap;
  }
  #accountsTable .pwd-pill { padding: 2px 6px; }
  #accountsTable .btn { padding: 3px 8px; font-size: 12px; }
</style>
@endpush
@section('content')
@include('modals.userEditDelOptions')



<form class="delUserResellerForm" data-action="/{{$url_type}}/delete-user/" action="" method="post">
  @csrf
  @method('DELETE')
  <div class="userAccCases">
  </div>
</form>
<div class="modal" id="linkResellerAccModal" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title"><strong>Link Account</strong></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <form action="" method="post">
              @csrf
              <div class="form-group">
                <label class="form-label">Single/Multiple Select Reseller</label>
                <input type="hidden" name="user_id" id="user_id" value="">
                <input type="hidden" name="cutype" id="cutype" value="">

                <input type="hidden" id="linkResellersList" name="resellers[]" multiple>
                <span class="resellers_error"></span>
              </div>

              <div class="modal-footer text-center">
                <button type="button" class="btn btn-primary btn-raised submitResellerLink"><i class="fa fa-check"></i> Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<section id="main-content">
  <section class="wrapper">
    {{-- BREADCRUMB --}}
    <div class="vu-breadcrumb-wrap">
      <nav class="vu-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="#" class="bc-item">Account Management</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View Accounts</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="vu-title-row">
              <h2>
                <span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;vertical-align:middle;"></span>
                Show Accounts
              </h2>
              <div style="display:flex;align-items:center;gap:8px;">
                @if(Auth::user()->user_type == "Admin")
                <a href="{{ route('writers.excel') }}" class="vu-btn-excel"><i class="fa fa-download"></i> Excel</a>
                <a href="{{ route('writers.csv') }}" class="vu-btn-csv"><i class="fa fa-download"></i> CSV</a>
                @endif
                @if ((Auth::user()->user_type == 'Admin') || (Auth::user()->user_type == 'Reseller' && Auth::user()->hasPermission('account_management.create')))
                <a href="/{{$url_type}}/add-user" class="vu-btn-primary"><i class="fa fa-plus"></i> Add Account</a>
                @endif
              </div>
            </div>
            <?php use App\Helper\CommonHelper; if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller') { ?>
            <div class="vu-stats">
              <span class="vu-stat-pill accounts"><i class="fa fa-users"></i> Total Accounts — <?= isset($totalUsers[0]->user_count) ? $totalUsers[0]->user_count : ''; ?></span>
              <span class="vu-stat-pill devices"><i class="fa fa-mobile"></i> Total Devices — <?= isset($totalDevices) ? $totalDevices : ''; ?></span>
              <span class="vu-stat-pill pings"><i class="fa fa-signal"></i> Total Pings — <?= isset($totalPings) ? $totalPings : ''; ?></span>
            </div>
            <?php } ?>
          </div><!--/.c_title-->
          <div class="c_content">
            <div class="row" id="alert_msg">
              @include('partials.gps-inline-alerts')
            </div>
            <div>
              @if(Auth::user()->user_type == "Admin")
              {{-- download buttons moved to header --}}
              @endif
            </div>
            <div class="table-responsive" style="overflow-x: auto; width: 100%;">
              {{-- Rows load one page at a time via /{url_type}/accounts-list-data --}}
              <table id="accountsTable" class="table"
                data-server-side="1"
                data-ajax-url="/{{ $url_type }}/accounts-list-data">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
<th>Account Type</th>
<th>Name</th>
<th>Mobile</th>
<th>Email</th>
<th>Login Password</th>
<th>Total Devices</th>
<th>Total Pings</th>
<th>Today Pings</th>
<th>Login IP</th>
<th>Device</th>
<th>Default Configurations</th>
<th>Assign devices</th>
@if(Auth::user()->user_type !='User' && (Auth::user()->user_type == 'Admin' || Auth::user()->hasPermission('account_management.edit')))
<th>Edit</th>
@endif
@if(Auth::user()->user_type == 'Admin' || Auth::user()->hasPermission('account_management.delete'))
<th>Delete</th>
@endif
<th>Link Account</th>
@if(Auth::user()->user_type == 'Admin' || (Auth::user()->user_type == 'Reseller' && Auth::user()->hasPermission('account_management.view')))
<th>Manage Permissions</th>
@endif
                    
                    </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            {{-- Shared Assign Device modal: devices load via AJAX select2 --}}
            <div class="modal assign-device-modal" id="assignDeviceModal" aria-hidden="true">
              <div class="modal-dialog modal-md">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title"><strong>Assign Device</strong></h4>
                  </div>
                  <form action="/{{$url_type}}/assign-device" method="post">
                    @csrf
                    <div class="modal-body">
                      <div class="form-group">
                        <label class="form-label">Single/Multiple Select Device</label>
                        <input type="hidden" name="user_id" class="assign-user-id" value="">
                        <select class="assignDevices" id="assignDeviceSelect" name="devices[]" multiple style="width:100%;"></select>
                      </div>
                    </div>
                    <div class="modal-footer text-center">
                      <button type="submit" class="btn btn-primary btn-raised rippler rippler-default">
                        <i class="fa fa-check"></i> Assign
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

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
@section('scripts')
<script>
  // Shared assign modal: devices are searched/paged via AJAX select2, scoped
  // to the target account's device categories.
  function open_asign(btn) {
    var $btn = $(btn);
    var userId = $btn.data('user-id');
    var categories = String($btn.data('categories') || '');
    var $modal = $('#assignDeviceModal');

    $modal.find('.assign-user-id').val(userId);

    var $select = $('#assignDeviceSelect');
    if ($select.data('select2')) {
      $select.val(null).trigger('change');
      $select.select2('destroy');
    }
    $select.empty();
    $select.select2({
      placeholder: 'Select and Search',
      width: '100%',
      dropdownParent: $modal,
      ajax: {
        url: '/{{ $url_type }}/devices-select',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return { q: params.term || '', page: params.page || 1, category_id: categories, mode: 'assignable' };
        },
        cache: true
      }
    });

    $modal.modal('show');
  }

  function openConfigurations(id) {
    $("#view-Configurations" + id).modal('show');
    $("#configuration" + id).dataTable();
    $("#configuration" + id + "_wrapper").css({
      'text-align': 'left'
    });
    $('.select2').select();
  }
  $(document).ready(function() {
    var $table = $('#accountsTable');
    $table.DataTable({
        serverSide: true,
        processing: true,
        paging: true,
        searching: true,
        searchDelay: 400,
        info: true,
        ordering: true,
        lengthChange: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        order: [],
        ajax: { url: $table.data('ajax-url') },
        columnDefs: [
            { targets: [1, 2, 3, 4, 7, 8], orderable: true },
            { targets: '_all', orderable: false }
        ],
        lengthMenu: [
            [25, 50, 100, 500],
            [25, 50, 100, 500]
        ],
        pageLength: 25
    });
  });

  function togglePasswordShow(id) {
    var pwdField = $("#showpassword-" + id);
    var hiddenField = $("#hiddenpassword-" + id);
    var icon = $("#eye-icon-" + id);

    if (pwdField.is(":hidden")) {
      pwdField.show();
      hiddenField.hide();
      icon.removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
      pwdField.hide();
      hiddenField.show();
      icon.removeClass("fa-eye-slash").addClass("fa-eye");
    }
  }
</script>
@endsection