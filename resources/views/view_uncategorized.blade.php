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
                        <li class="active"><a href="#">View Uncategorized Device</a></li>
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
                                <h2>Show Uncategorized Devices</h2>
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
                                    <th>Sr. No</th>
                                    <th>User Name</th>
                                    <th>Device Category</th>
                                    <th>Name</th>
                                    <th>IMEI</th>
                                    <th>Total Pings</th>
                                    <th>Ping Interval</th>
                                    <th>Added On</th>
                                    <th>Last Settings Update</th>
                                    <th>Editable</th>
                                    <th>Default Configurations</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($device as $contact)
                                <tr>
                                    <td><?php echo $i ?></td>
                                    <td>{{ !empty($contact->username) ? $contact->username : 'Unassigned' }}</td>
                                    <td><?php echo  $contact->device_category_name ?></td>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->imei }}</td>
                                    <td>{{ $contact->total_pings }}</td>
                                    <td>{{ isset($config['ping_interval']) ? $config['ping_interval'] : '' }}</td>
                                    <td>{{ $contact->created_at }}</td>
                                    <td>{{ $contact->updated_at }}</td>
                                    <td>
                                        @if ($contact->is_editable == '1')
                                            <button class="btn btn-success btn-sm">Yes</button>
                                        @else
                                            <button class="btn btn-danger btn-sm">No</button>
                                        @endif
                                    </td>
                                    <td><a href="{{ url(strtolower(Auth::user()->user_type) . '/view-device-configurations/' . $contact->id) }}" class="btn btn-primary btn-info">View Configuration</a></td>
                                    <td>
                                        <form action="#" method="POST">
                                            @csrf
                                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                        </form>
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
