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
                        <li><a href="#">Version Management</a></li>
                        <li class="active"><a href="#">View Version</a></li>
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
                                <h2>View Verison</h2>
                            </div>
                            <div class="col-lg-6 text-right">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateVersionModal">
                                    <i class="fa fa-upload"></i> Update Version
                                </button>

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
                        @if(Auth::user()->user_type == "Admin")
                        <div class="col-lg-12 text-right margin-bottom-10">
                            <a href="{{ route('esimMasters.excel') }}" class="btn btn-success">Download Excel</a>
                            <a href="{{ route('esimMasters.csv') }}" class="btn btn-success">Download CSV</a>
                        </div>
                        @endif
                        <table id="esim" class="example table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Version</th>
                                    <th style="width: 12px;">Created at</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <?php
                            $i =  1;
                            ?>
                            <tbody>
                                @foreach ($version as $ver)
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td>{{$ver->version}}</td>
                                    <td>{{isset($ver->created_at) ? CommonHelper::getDateAsTimeZone($ver->created_at) : ''}}</td>
                                    <td class="d-flex align-items-center gap-2">
                                        <!-- Edit Button -->
                                        <button class="btn btn-primary btn-sm margin-top-14"
                                            title="View Release Notes"
                                            style="height: 25px;width: 31px;"
                                            data-toggle="modal"
                                            data-target="#viewReleaseNotesModal{{$ver->id}}">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <!-- Release Notes Modal -->
                                        <div class="modal" id="viewReleaseNotesModal{{$ver->id}}" tabindex="-1" role="dialog" aria-labelledby="viewReleaseNotesLabel{{$ver->id}}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header d-flex" style="justify-content:space-between;align-items:center;">
                                                        <h5 class="modal-title" id="viewReleaseNotesLabel">Release Notes</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body" id="releaseNotesContent">
                                                        {{$ver->release_notes}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                                <?php
                                $i++;
                                ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div><!--/.c_content-->
                </div><!--/.c_panels-->
            </div><!--/col-md-12-->
        </div><!--/row-->
        <div class="modal" id="updateVersionModal" tabindex="-1" role="dialog" aria-labelledby="updateVersionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateVersionModalLabel">Update Version</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <form id="versionForm" action="{{ route('admin.updateVersion') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="version_id_hidden">

                            <div class="form-group">
                                <label for="version">Version:</label>
                                <input type="text" class="form-control" name="version" id="version" placeholder="e.g., 1.0.1" required>
                            </div>

                            <div class="form-group">
                                <label for="release_notes">Release Notes:</label>
                                <textarea class="form-control" name="release_notes" id="release_notes" rows="5"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--======= Dynamic Datatable Content Start End ========-->
    </section>
</section>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#esim").dataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,
            // pageLength: 10,
            // scrollX: true,
            // scrollY: '500px',
            scrollCollapse: true,
            "aLengthMenu": [
                [25, 50, 100, 500, -1],
                [25, 50, 100, 500, "All"]
            ],
            "iDisplayLength": 25
        });

    });
</script>