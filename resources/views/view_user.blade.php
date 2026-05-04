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
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Account</a></li>
            <li class="active"><a href="#">View Accounts</a></li>
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
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2>Show Accounts</h2>
              </div>
              @if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller')
              <div class="col-lg-6 text-right">
                <a href="/{{$url_type}}/add-user" class="btn btn-success"> Add Account </a>
              </div>
              @endif
            </div>
            <div class="clearfix"></div>
            <?php

            use App\Helper\CommonHelper;

            if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller') {
            ?>
              <div class="stats-buttons">
                  <span id="span1" class="btn btn-primary stat-btn">
                      Total Accounts - <?= isset($totalUsers[0]->user_count) ? $totalUsers[0]->user_count : ''; ?>
                  </span>

                  <span id="span2" class="btn btn-success stat-btn">
                      Total Devices - <?= isset($totalDevices) ? $totalDevices : ''; ?>
                  </span>

                  <span id="span3" class="btn btn-info stat-btn">
                      Total Pings - <?= isset($totalPings) ? $totalPings : ''; ?>
                  </span>
              </div>
            <?php
            }
            ?>
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
              <div class="col-lg-12 text-right margin-bottom-10">
                <a href="{{ route('writers.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('writers.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
              @endif
            <div>
              <table id="example" class="example view_user_table table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:14px;">
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
                        <button type="button" id="hide-{{$contact['id']}}" class="pwd-btn" onclick="togglePasswordShow({{$contact['id']}})" title="Toggle Password">
                          <i class="fa fa-eye" id="eye-icon-{{$contact['id']}}"></i>
                        </button>
                      </div>
                    </td>
                    <td>{{$contact['device_count']}}</td>
                    <td>{{$contact['total_pings']}}</td>
                    <td>{{$contact['today_pings']}}</td>
                    <td style="text-align:center">
                      <a href="/{{strtolower(Auth::user()->user_type)}}/view-configurations/{{$contact['id']}}" class="btn btn-info btn-sm viewConfigurations" onclick="openConfigurations({{$contact['id']}})">View Configuration</a>
                    </td>
                    <td> <button class="btn btn-green btn-raised rippler rippler-default margin-top-1" onclick="open_asign({{$contact['id']}})">Assign
                      </button>
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
                                      <button type="submit" class="btn btn-primary btn-raised rippler rippler-default"><i class="fa fa-check"></i> Assign
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
                      <a href="/{{$url_type}}/edit-user/{{$contact['user_type']}}/{{$contact['id']}}" class="btn btn-primary btn-sm">Edit</a>
                    </td>
                    @endif
                    <td>
                      <button data-uid="{{$contact['id']}}" data-utype="{{$contact['user_type']}}" class="btn btn-danger btn-sm delUserReseller margin-top-1" type="button">Delete</button>
                    </td>
                    <td>
                      @if($contact['user_type']=='Reseller' )
                      <button data-uid="{{$contact['id']}}" data-cutype="{{$url_type}}" class="btn btn-primary btn-sm linkReseller margin-top-1" type="button">Link Account</button>
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