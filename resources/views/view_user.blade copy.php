@extends('layouts.apps')
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
              <div class="col-lg-6 text-right">
                <a href="/{{$url_type}}/add-user" class="btn btn-danger"> Add Account </a>
              </div>
            </div>
            <div class="clearfix"></div>
            <?php

            use App\Helper\CommonHelper;

            if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Reseller') {
            ?>
              <span id="span1" class="btn btn-primary">Total Accounts - <?= isset($totalUsers[0]->user_count) ? $totalUsers[0]->user_count : ''; ?></span>|
              <span id="span2" class="btn btn-success">Total Devices - <?= isset($totalDevices) ? $totalDevices : ''; ?></span>|
              <span id="span3" class="btn btn-info">Total Pings - <?= isset($totalPings) ? $totalPings : ''; ?></span>
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
            <div style="height: 500px;overflow: scroll;">
              <table id="example" class="view_user_table table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:14px;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Account Type</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Login Password</th>
                    <th>Default Settings</th>
                    <th>Assign devices</th>
                    <th>Edit</th>
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
                    <td>{{$contact['user_type']}}</td>
                    <td>{{$contact['name']}}</td>
                    <td>{{$contact['mobile']}}</td>
                    <td>{{$contact['email']}}</td>
                    <td>
                      <div id="showpassword-{{$contact['id']}}" hidden>
                        {{$contact['showLoginPassword']}}
                      </div>
                      <button id="hide-{{$contact['id']}}" onclick="togglePasswordShow({{$contact['id']}})">show</button>
                    </td>

                    <td style="text-align:center">
                      <a href="/{{strtolower(Auth::user()->user_type)}}/view-configurations/{{$contact['id']}}" class="btn btn-info btn-sm viewConfigurations" onclick="openConfigurations({{$contact['id']}})">View Settings</a>
                    </td>
                    <td> <button class="btn btn-green btn-raised rippler rippler-default" onclick="open_asign({{$contact['id']}})">Assign
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
                                          <option>Choose device</option>
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
                    <td>
                      <a href="/{{$url_type}}/edit-user/{{$contact['user_type']}}/{{$contact['id']}}" class="btn btn-primary btn-sm">Edit</a>
                    </td>
                    <td>
                      <button data-uid="{{$contact['id']}}" data-utype="{{$contact['user_type']}}" class="btn btn-danger btn-sm delUserReseller" type="button">Delete</button>
                    </td>
                    <td>
                      @if($contact['user_type']=='Reseller' )
                      <button data-uid="{{$contact['id']}}" data-cutype="{{$url_type}}" class="btn btn-primary btn-sm linkReseller" type="button">Link Account</button>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
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

    $('.assignDevices').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');

      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });

  });

  function togglePasswordShow(id) {
    $("#hide-"+id).hide();
    $("#showpassword-"+id).show();
  }
</script>