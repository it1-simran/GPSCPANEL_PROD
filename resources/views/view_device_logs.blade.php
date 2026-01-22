<?php

use App\Helper\CommonHelper;



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
            <li><a href="#">Device Management</a></li>
            <li class="active"><a href="#">View Device Logs</a></li>
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
                <h2>View Device Logs</h2>
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
                <a href="{{ route('devicelog.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('devicelog.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
            @endif
            <table id="deviceLog" class="example table table-bordered table-striped table-condensed cf no-footer dataTable" style="border-spacing: 0; width: 100%; font-size: 14px;">
              <thead>
                <tr>
                  <th style="width:10%;">Sr. No.</th>
                  <th style="width: 15%;">Created By</th>
                  <th style="width: 15%;">Log</th>
                  <th style="width: 15%;">Action</th>
                  <th style="width: 15%;">Date</th>
                </tr>
              </thead>
              <?php
                $i =  1;
              ?>
              <tbody>
                  @foreach ($deviceLogs as $logs)
                  <tr>
                      <td>{{$i}}</td>
                      <td>{{isset($logs->user_id) ? CommonHelper::getUserName($logs->user_id) : '' }}</td>
                      <td><p  style="display: block;
                    <!--width: fit-content;-->
                    <!--max-width: 34%;-->
                    /* overflow: auto; */
                    white-space: break-spaces;
                    overflow: hidden;
                    text-overflow: ellipsis;">{{$logs->log}}</p></td>
                      <td>{{$logs->action}}</td>
                      <td>{{CommonHelper::getDateAsTimeZone($logs->created_at)}}</td>
                  </tr>
                  <?php  $i++; ?>
                  @endforeach
              </tbody>
            </table>
          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->

    <!--======= Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script>
    $(document).ready(function() {
    $("#deviceLog").dataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: false,
      pageLength: 10,
    //   scrollX: true,
      scrollY: '500px',
      scrollCollapse: true,
      "aLengthMenu": [
        [25, 50, 100, 500, -1],
        [25, 50, 100, 500, "All"]
      ],
      "iDisplayLength": 25
    });

  });
 </script>