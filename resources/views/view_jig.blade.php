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
                        <li><a href="#">JIG Management</a></li>
                        <li class="active"><a href="#">View JIG</a></li>
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
                                <h2>View JIG</h2>
                            </div>
                            <div class="col-lg-6 text-right">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadJigModal" onclick="openAddModal()">
                                    ADD JIG
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
                                    <th>JIG ID</th>
                                    <th>Imei </th>
                                    <th style="width: 12px;">Created at</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <?php
                            $i =  1;
                            ?>
                            <tbody>
                                @foreach ($jigs as $jig)
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td>{{$jig->jigId}}</td>
                                    <td>{{$jig->imei}}</td>
                                    <td>{{ CommonHelper::getDateAsTimeZone($jig->created_at) ?: '--'  }}</td>
                                    <td class="d-flex align-items-center gap-2">

                                        <!-- Edit Button -->
                                        <button class="btn btn-primary btn-sm margin-top-14"
                                            data-toggle="modal"
                                            data-target="#uploadJigModal"
                                            onclick="openEditModal('{{ $jig->id }}', '{{ $jig->jigId }}', '{{ $jig->imei }}')"
                                            title="Edit" style="height: 25px;width: 31px;">
                                            <i class="fa fa-edit"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="/{{$url_type}}/delete-jig/{{$jig->id}}" method="post" class="padding-4">
                                            @csrf
                                            @method('DELETE')
                                            <button onClick="return confirm('Are you sure you want to delete this?');"
                                                class="btn btn-danger btn-sm"
                                                type="submit"
                                                title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>

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
        <div class="modal" id="uploadJigModal" tabindex="-1" role="dialog" aria-labelledby="uploadJigModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadJigModalLabel">Add Jig</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="jigForm" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="jig_id_hidden"> <!-- used for Edit -->

                            <div class="form-group">
                                <label for="jig_id">Jig ID:</label>
                                <input type="text" class="form-control" name="jig_id" id="jig_id" required>
                            </div>

                            <div class="form-group">
                                <label for="imei">IMEI:</label>
                                <input type="text" class="form-control" name="imei" id="imei" maxlength="15" required>
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
    function openAddModal() {
        $('#uploadJigModalLabel').text('Add Jig');
        $('#jigForm').attr('action', '/admin/submit-jig');
        $('#jig_id_hidden').val('');
        $('#jig_id').val('');
        $('#imei').val('');
        $('#submitBtn').text('Save');
    }

    function openEditModal(id, jigId, imei) {
        $('#uploadJigModalLabel').text('Edit Jig');
        $('#jigForm').attr('action', '/admin/update-jig/' + id);
        $('#jig_id_hidden').val(id);
        $('#jig_id').val(jigId);
        $('#imei').val(imei);
        $('#submitBtn').text('Update');
    }
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