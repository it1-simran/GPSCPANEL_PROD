@extends('layouts.apps')
@section('content')
@include('modals.userEditDelOptions')

<style>
  @media (max-width: 768px) {
      .stats-buttons .stat-btn {
          display: block;
          width: 90%;
          max-width: 320px;
          margin: 8px auto;
          text-align: center;
      }
  }

  /* Premium Password Toggle Pill */
  .pwd-pill {
      display: inline-flex;
      align-items: center;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 30px;
      padding: 4px 5px 4px 15px;
      gap: 12px;
      min-width: 145px;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
      transition: all 0.3s ease;
  }
  .pwd-pill:hover {
      border-color: #cbd5e0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  }
  .pwd-text {
      font-weight: 600;
      color: #2d3748;
      font-family: monospace;
      font-size: 14px;
      letter-spacing: 0.5px;
      user-select: all;
  }
  .pwd-hidden {
      color: #a0aec0;
      font-size: 16px;
      letter-spacing: 3px;
      margin-top: 3px;
  }
  .pwd-btn {
      background: rgba(118, 207, 28, 0.15);
      border: none;
      border-radius: 50%;
      width: 28px;
      height: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #5fa616;
      cursor: pointer;
      transition: all 0.2s;
      outline: none;
  }
  .pwd-btn:hover {
      background: #76CF1C;
      color: #fff;
      transform: scale(1.05);
  }
  .pwd-btn:active {
      transform: scale(0.95);
  }

  /* ===== PAGE SPACING ===== */
  #main-content .wrapper { padding-top: 10px !important; }

  /* ===== BREADCRUMB ===== */
  .vu-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .vu-breadcrumb {
    display: inline-flex; align-items: center;
    background: #1e293b; border-radius: 50px;
    padding: 6px 18px 6px 8px; gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
  }
  .vu-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
  }
  .vu-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
  .vu-breadcrumb .bc-item { color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
  .vu-breadcrumb .bc-sep  { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
  .vu-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

  /* ===== PANEL ===== */
  .c_panel { border: none !important; border-radius: 12px !important; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important; }
  .c_title  { background: #1e293b !important; padding: 14px 20px !important; border-bottom: none !important; }
  .vu-title-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
  .vu-title-row h2 { margin: 0; color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; }

  /* Stat pills */
  .vu-stats { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
  .vu-stat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
    border: none; cursor: default;
  }
  .vu-stat-pill.accounts { background: rgba(255,255,255,0.1); color: #fff; }
  .vu-stat-pill.devices  { background: #76CF1C; color: #1e293b; }
  .vu-stat-pill.pings    { background: rgba(99,179,237,0.25); color: #bfdbfe; }

  /* Header buttons */
  .vu-btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816);
    border: none; border-radius: 7px; padding: 0 16px; height: 34px;
    color: #1e293b; font-size: 13px; font-weight: 800;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 12px rgba(118,207,28,0.3);
    cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap;
  }
  .vu-btn-primary:hover { transform: translateY(-1px); color: #1e293b; text-decoration: none; filter: brightness(1.08); }

  /* Download buttons */
  .vu-btn-excel {
    background: linear-gradient(135deg, #166534, #15803d);
    border: none; border-radius: 7px; padding: 7px 14px;
    color: #fff; font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    cursor: pointer; transition: all 0.2s; text-decoration: none;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
  }
  .vu-btn-csv {
    background: linear-gradient(135deg, #1e3a5f, #1e40af);
    border: none; border-radius: 7px; padding: 7px 14px;
    color: #fff; font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    cursor: pointer; transition: all 0.2s; text-decoration: none;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
  }
  .vu-btn-excel:hover, .vu-btn-csv:hover { transform: translateY(-1px); color: #fff; text-decoration: none; filter: brightness(1.08); }
  .vu-dl-row { display: flex; justify-content: flex-end; gap: 8px; padding: 10px 0 4px; }

  /* ===== TABLE ===== */
  #example {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 5px !important;
    border: none !important;
  }
  #example.table { border: none !important; }
  #example > thead > tr > th,
  #example > tbody > tr > td { border: none !important; }
  #example > tbody > tr:nth-child(odd) > td,
  #example > tbody > tr:nth-child(even) > td { background-color: transparent !important; }
  #example thead th {
    background: transparent !important; color: #64748b !important;
    font-size: 11px !important; text-transform: uppercase !important;
    letter-spacing: 0.8px !important; font-weight: 700 !important;
    padding: 8px 12px !important; border-bottom: 2px solid #f1f5f9 !important;
    white-space: nowrap;
  }
  #example tbody tr { background: #fff; transition: box-shadow 0.2s, background 0.2s; }
  #example tbody tr:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
  #example tbody td {
    vertical-align: middle !important; padding: 11px 12px !important;
    background: #fff !important; color: #334155; font-size: 12px;
    border-top: 1px solid #e9ecef !important; border-bottom: 1px solid #e9ecef !important;
    border-left: none !important; border-right: none !important;
  }
  #example tbody tr:hover td { background: #f8faff !important; }
  #example tbody td:first-child {
    border-left: 1px solid #e9ecef !important;
    border-top-left-radius: 8px !important; border-bottom-left-radius: 8px !important;
    color: #94a3b8; font-weight: 700;
  }
  #example tbody td:last-child {
    border-right: 1px solid #e9ecef !important;
    border-top-right-radius: 8px !important; border-bottom-right-radius: 8px !important;
  }

  /* Action buttons */
  .vu-btn-edit, .vu-btn-delete, .vu-btn-assign, .vu-btn-link, .vu-btn-view {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600;
    border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap;
  }
  .vu-btn-edit   { background: linear-gradient(135deg,#1e293b,#2d3f55); color:#fff; box-shadow:0 2px 6px rgba(30,41,59,0.2); }
  .vu-btn-delete { background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 2px 6px rgba(239,68,68,0.2); }
  .vu-btn-assign { background: linear-gradient(135deg,#76CF1C,#5fa816); color:#1e293b; box-shadow:0 2px 6px rgba(118,207,28,0.25); }
  .vu-btn-link   { background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; box-shadow:0 2px 6px rgba(124,58,237,0.2); }
  .vu-btn-view   { background: linear-gradient(135deg,#0369a1,#0284c7); color:#fff; box-shadow:0 2px 6px rgba(3,105,161,0.2); }
  .vu-btn-edit:hover, .vu-btn-delete:hover, .vu-btn-assign:hover,
  .vu-btn-link:hover, .vu-btn-view:hover { transform: translateY(-1px); filter: brightness(1.08); color: inherit; text-decoration: none; }
  .vu-btn-delete:hover, .vu-btn-edit:hover { color: #fff; }
  .vu-btn-assign:hover { color: #1e293b; }

  /* DataTable footer */
  .dataTables_wrapper .row:last-child {
    display: flex !important; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px; margin-top: 12px; padding: 8px 2px;
  }
  .dataTables_wrapper .row:last-child > div {
    float: none !important; width: auto !important; padding: 0 !important;
    flex: 0 0 auto;
  }
  .dataTables_wrapper .row:last-child > div:first-child { flex: 1 1 auto !important; }
  .dataTables_wrapper .row:last-child > div:last-child { flex: 0 0 auto !important; margin-left: auto; }
  .dataTables_wrapper .dataTables_info { color:#64748b; font-size:13px; font-weight:500; padding:6px 0 !important; float:none !important; }
  .dataTables_wrapper .dataTables_paginate { display:flex !important; align-items:center; gap:4px; float:none !important; flex-wrap:wrap; }
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    background:transparent !important; border:none !important; color:#64748b !important;
    border-radius:4px !important; padding:2px 6px !important; font-size:10px !important;
    font-weight:600 !important; cursor:pointer; transition:all 0.2s; box-shadow:none !important;
    min-width:20px; text-align:center; line-height:1.5; display:inline-block;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background:#f1f5f9 !important; color:#76CF1C !important; }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current,
  .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background:#76CF1C !important; border:none !important; color:#1e293b !important; font-weight:800 !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color:#cbd5e1 !important; cursor:not-allowed !important; }
  .dataTables_paginate span > span { display:none !important; }
</style>


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
                @if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller')
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
            <div>
              @if(Auth::user()->user_type == "Admin")
              {{-- download buttons moved to header --}}
              @endif
            </div>
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
                    <th>Default Configurations</th>
                    <th>Assign devices</th>
                    @if(Auth::user()->user_type !='User' )
                    <th>Edit</th>
                    @endif
                    <th>Delete</th>
                    <th>Link Account</th>
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
                    <td>
                      <a href="/{{strtolower(Auth::user()->user_type)}}/view-configurations/{{$contact['id']}}" class="vu-btn-view" onclick="openConfigurations({{$contact['id']}})"><i class="fa fa-eye"></i> View Config</a>
                    </td>
                    <td><button style="margin-top:1px" class="vu-btn-assign" onclick="open_asign({{$contact['id']}})"><i class="fa fa-link"></i> Assign</button>
                      <!--****** Start Modal Responsive******-->
                      <div class="modal" id="modal-responsive{{ $contact['id']}}" aria-hidden="true">
                        <div class="modal-dialog modal-md">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                              <h4 class="modal-title"><strong>Assign Device</strong></h4>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-md-12">
                                  <form action="/{{$url_type}}/assign-device" method="post">
                                    @csrf
                                    <div class="form-group">
                                      <label class="form-label">Single/Multiple Select Device</label>
                                      <input type="hidden" name="user_id" id="auser_id" value="">
                                      <select class="assignDevices" id="s2example-2{{$contact['id']}}" name="devices[]" multiple>
                                        @if(count($unassign_device) > 0)
                                        <option></option>
                                        <optgroup label="Unassigned Devices">
                                          <!-- <option>Choose device</option> -->
                                          @foreach($unassign_device as $user)
                                          @if(in_array($user->device_category_id,explode(',',$contact->device_category_id)))
                                          <option value="{{$user->id}}">{{$user->imei}}</option>
                                          @endif
                                          @endforeach
                                          @endif
                                        </optgroup>
                                      </select>
                                    </div>
                                    <div class="modal-footer text-center">
                                      <button  type="submit" class="btn btn-primary btn-raised rippler rippler-default"><i class="fa fa-check"></i> Assign
                                      </button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                    </td>
                    @if(Auth::user()->user_type !='User' )
                    <td>
                      <a href="/{{$url_type}}/edit-user/{{$contact['user_type']}}/{{$contact['id']}}" class="vu-btn-edit"><i class="fa fa-edit"></i> Edit</a>
                    </td>
                    @endif
                    <td>
                      <button style="margin-top:1px" data-uid="{{$contact['id']}}" data-utype="{{$contact['user_type']}}" class="vu-btn-delete delUserReseller" type="button"><i class="fa fa-trash"></i> Delete</button>
                    </td>
                    <td>
                      @if($contact['user_type']=='Reseller' )
                      <button style="margin-top:1px" data-uid="{{$contact['id']}}" data-cutype="{{$url_type}}" class="vu-btn-link linkReseller" type="button"><i class="fa fa-chain"></i> Link</button>
                      @endif
                    </td>
                  </tr>
                  <?php $i++; ?>
                  @endforeach
                  @else
                  <!-- <td colspan="7">No Data Found</td> -->
                  @endif
                </tbody>
              </table>
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
<script>
  function open_asign(id) {
    $("#auser_id").val(id);
    $("#modal-responsive" + id).modal('show');
  };

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
        responsive: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        lengthMenu: [
            [25, 50, 100, 500, -1],
            [25, 50, 100, 500, "All"]
        ],
        pageLength: 25
    });

    $('.assignDevices').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');

      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
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