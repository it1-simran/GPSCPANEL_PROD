<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <!--======== Page Title and Breadcrumbs Start ========-->
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Device</a></li>
                        <li class="active"><a href="#">View Uncategorized Settings</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <!--======== Page Title and Breadcrumbs End ========-->
        <!--======== Dynamic Datatable Content Start ========-->
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <div class="row bgx-title-container">
                            <div class="col-lg-6">
                                <h2>Show Uncategorized Settings</h2>
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
                        <table id="datatable1232" class="table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
                            <?php
                            $i = 1;

                            ?>
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
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>Link Account</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $contact)
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
                                    <td>{{$contact['total_devices']}}</td>
                                    <td>{{$contact['total_pings']}}</td>
                                    <td>{{$contact['today_pings']}}</td>
                                    <td style="text-align:center">
                                        <a href="/{{strtolower(Auth::user()->user_type)}}/view-configurations/{{$contact['id']}}" class="btn btn-info btn-sm viewConfigurations" onclick="openConfigurations({{$contact['id']}})">View Configuration</a>
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
                                    @if(Auth::user()->user_type =='Admin' )
                                    <td>
                                        <a href="/{{$url_type}}/edit-user/{{$contact['user_type']}}/{{$contact['id']}}" class="btn btn-primary btn-sm">Edit</a>
                                    </td>
                                    @endif
                                    <td>
                                        <button data-uid="{{$contact['id']}}" data-utype="{{$contact['user_type']}}" class="btn btn-danger btn-sm delUserReseller" type="button">Delete</button>
                                    </td>
                                    <td>
                                        @if($contact['user_type']=='Reseller' )
                                        <button data-uid="{{$contact['id']}}" data-cutype="{{$url_type}}" class="btn btn-primary btn-sm linkReseller" type="button">Link Account</button>
                                        @endif
                                    </td>
                                </tr>
                                <?php
                                $i++;
                                ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!--/.c_content-->
        </div><!--/.c_panels-->
        </div><!--/col-md-12-->
    </section>
</section>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#datatable1232").dataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,
            pageLength: 10,
            scrollX: true,
            scrollY: '500px',
            scrollCollapse: true,
            "aLengthMenu": [
                [25, 50, 100, 500, -1],
                [25, 50, 100, 500, "All"]
            ],
            "iDisplayLength": 25
        });
        $('.selectDevice').each(function() {
            var id = $(this).attr('id');
            $('#' + id).select2({
                placeholder: 'Select and Search'
            });
        });
    });

    function open_model(id, key) {
        $("#test_id").val(id);
        $("#modal-responsive-" + id).modal();
    }
</script>