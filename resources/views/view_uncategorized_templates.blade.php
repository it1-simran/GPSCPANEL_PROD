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
                                    <th>Template Name</th>
                                    <th>Device Category</th>
                                    <th style="width: 12px;">Created at</th>
                                    <th>Last Edit</th>
                                    <th>Default Template</th>
                                    <th>View</th>
                                    <th>Apply Setting</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td>{{$template->template_name}}</td>
                                    <td><?php echo CommonHelper::getDeviceCategoryName($template->device_category_id); ?> </td>
                                    <td>{{$template->created_at}}</td>
                                    <td>{{$template->updated_at}}</td>
                                    @if(Auth::user()->user_type=='Admin')
                                    <td><?php if ($template->default_template == '1') { ?>
                                            <button class="bt btn-warning">Yes</button>
                                        <?php } ?>
                                    </td>
                                    @endif
                                    <td>
                                        <a href="/{{$url_type}}/view-template-configurations/{{$template->id}}" class="btn btn-info btn-raised rippler rippler-default">View Settings
                                        </a>
                                    </td>
                                    <td>
                                        <button class="btn btn-green btn-raised rippler rippler-default" onclick="open_model(<?php echo $template->id; ?>)">Apply
                                        </button>
                                        @if(isset($template))
                                        <div class="modal" id="modal-responsive-{{$template->id}}" aria-hidden="true">
                                            <div class="modal-dialog modal-md">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                                                        <h4 class="modal-title"><strong>Assign Template</strong></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <form action="/{{$url_type}}/assign-template/{{$template->id}}" method="post">
                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <input type="hidden" name="test_id" id="test_id" value="">
                                                                        <label class="form-label">Single/Multiple Select Device</label>

                                                                        <select class="selectDevice" id="s2example-{{ $template->id}}" name="devices[]" multiple>
                                                                            <option></option>

                                                                            <optgroup label="Assigned/Unassigned Devices">

                                                                                <?php echo CommonHelper::unassignDevices($template->device_category_id); ?>
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
                                            </div><!--/row-->
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(Auth::user()->user_type=='Admin')
                                        <a href="<?php echo url('admin/edit-template/' . $template->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                        @elseif(Auth::user()->user_type!=='Admin')
                                        <a href="/{{$url_type}}/edit-template/{{$template->id}}" class="btn btn-primary btn-sm">Edit</a>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="/{{$url_type}}/delete-template/{{$template->id}}" method="post">
                                            @csrf
                                             @method('DELETE')
                                            @if($template->default_template=='0')
                                            <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm" type="submit">Delete</button>
                                            @else($template->default_template=='1')
                                            @endif
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