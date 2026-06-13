@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-user') }}">
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
              <table id="example" class="table">
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
                <tbody>
                  @if(count($contacts) > 0)
                  <?php
                  $i = 1;
                  ?>
                  @foreach($contacts as $contact)
                  <tr>

                    <td><?php echo $i; ?></td>
                    <td>
                      {{ $contact['user_type'] === 'Reseller' ? 'Manufacturer' : ($contact['user_type'] === 'Support' ? 'Support' : 'Dealer') }}
                    </td>

                    <td>{{$contact['name']}}</td>
                    <td>{{$contact['mobile']}}</td>
                    <td>{{$contact['email']}}</td>
                    <td>
                      <div class="pwd-pill">
                        <div id="showpassword-{{$contact['id']}}" class="pwd-text" style="display: none;">
                          {{$contact['showLoginPassword']}}
                        </div>
                        <div id="hiddenpassword-{{$contact['id']}}" class="pwd-hidden">
                          ••••••••
                        </div>
                        <button type="button" style="margin-top:1px" id="hide-{{$contact['id']}}" class="pwd-btn" onclick="togglePasswordShow({{$contact['id']}})" title="Toggle Password">
                          <i class="fa fa-eye" id="eye-icon-{{$contact['id']}}"></i>
                        </button>
                      </div>
                    </td>
                    <td>{{$contact['device_count']}}</td>
<td>{{$contact['total_pings']}}</td>
<td>{{$contact['today_pings']}}</td>
<td>{{$contact['last_ip'] ?? 'N/A'}}</td>
<td>{{$contact['last_device'] ?? 'N/A'}}</td>
<td>
  @if(Auth::user()->user_type == 'Admin' || Auth::user()->hasPermission('account_management.edit'))
    <a href="/{{strtolower(Auth::user()->user_type)}}/view-configurations/{{$contact['id']}}" class="vu-btn-view" onclick="openConfigurations({{$contact['id']}})"><i class="fa fa-eye"></i> View Config</a>
  @endif
</td>
<td>
  @if(Auth::user()->user_type == 'Admin' || Auth::user()->hasPermission('account_management.edit'))
    <button style="margin-top:1px" class="vu-btn-assign" onclick="open_asign({{$contact['id']}})"><i class="fa fa-link"></i> Assign</button>
  @endif
</td>

                    </td>
                    @if(Auth::user()->user_type !='User' && (Auth::user()->user_type == 'Admin' || Auth::user()->hasPermission('account_management.edit')))
                    <td>
                      <a href="/{{$url_type}}/edit-user/{{$contact['user_type']}}/{{$contact['id']}}" class="vu-btn-edit"><i class="fa fa-edit"></i> Edit</a>
                    </td>
                    @endif
                    @if(Auth::user()->user_type == 'Admin' || Auth::user()->hasPermission('account_management.delete'))
                    <td>
                      <button style="margin-top:1px" data-uid="{{$contact['id']}}" data-utype="{{$contact['user_type']}}" class="vu-btn-delete delUserReseller" type="button"><i class="fa fa-trash"></i> Delete</button>
                    </td>
                    @endif
                    <td>
                      @if($contact['user_type']=='Reseller' )
                      <button style="margin-top:1px" data-uid="{{$contact['id']}}" data-cutype="{{$url_type}}" class="vu-btn-link linkReseller" type="button"><i class="fa fa-chain"></i> Link</button>
                      @endif
                    </td>
                    @if(Auth::user()->user_type == 'Admin' || (Auth::user()->user_type == 'Reseller' && Auth::user()->hasPermission('account_management.view')))
                    <td>
                      @if(Auth::user()->user_type == 'Admin' && $contact['user_type'] == 'Reseller')
                        <a href="/admin/manage-permissions?reseller_id={{$contact['id']}}" class="vu-btn-permission" style="margin-top:1px"><i class="fa fa-lock"></i> Manage</a>
                      @elseif(Auth::user()->user_type == 'Admin' && $contact['user_type'] == 'User')
                        <a href="/admin/manage-user-permissions?user_id={{$contact['id']}}" class="vu-btn-permission" style="margin-top:1px"><i class="fa fa-lock"></i> Manage</a>
                      @elseif(Auth::user()->user_type == 'Reseller' && in_array($contact['user_type'], ['User', 'Reseller'], true))
                        <a href="/reseller/manage-child-permissions?user_id={{$contact['id']}}" class="vu-btn-permission" style="margin-top:1px"><i class="fa fa-lock"></i> Manage</a>
                      @endif
                    </td>
                    @endif
                  </tr>
                  <?php $i++; ?>
                  @endforeach
                  @else
                  <!-- <td colspan="7">No Data Found</td> -->
                  @endif
                </tbody>
              </table>
            </div>

            {{-- Render Modals Outside Table to avoid Hover Glitches --}}
            @if(count($contacts) > 0)
              @foreach($contacts as $contact)
                <!--****** Start Modal Responsive******-->
                <div class="modal assign-device-modal" id="modal-responsive{{ $contact['id']}}" aria-hidden="true">
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
                            <select class="assignDevices" id="s2example-2{{$contact['id']}}" name="devices[]" multiple>
                              @if(count($unassign_device) > 0)
                              <option></option>
                              <optgroup label="Unassigned Devices">
                                @foreach($unassign_device as $user)
                                @if(in_array($user->device_category_id,explode(',',$contact->device_category_id)))
                                <option value="{{$user->id}}">{{$user->imei}}</option>
                                @endif
                                @endforeach
                                @endif
                              </optgroup>
                            </select>
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
              @endforeach
            @endif

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
<script>
  function initAssignDeviceSelect($modal) {
    var $select = $modal.find('.assignDevices');
    if (!$select.length) {
      return;
    }

    if ($select.hasClass('select2-hidden-accessible')) {
      $select.select2('destroy');
    }

    $select.val(null);
    $select.select2({
      placeholder: 'Select and Search',
      width: '100%',
      dropdownParent: $modal
    });
  }

  function open_asign(id) {
    var $modal = $('#modal-responsive' + id);
    $modal.find('.assign-user-id').val(id);
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
    $('#example').DataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        lengthChange: true,
        autoWidth: false,
        lengthMenu: [
            [25, 50, 100, 500, -1],
            [25, 50, 100, 500, "All"]
        ],
        pageLength: 25
    });

    $('.assign-device-modal').on('shown.bs.modal', function() {
      initAssignDeviceSelect($(this));
    }).on('hidden.bs.modal', function() {
      var $select = $(this).find('.assignDevices');
      if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
      }
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