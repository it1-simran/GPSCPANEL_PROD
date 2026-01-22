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
            <li><a href="#">Firmware Management</a></li>
            <li class="active"><a href="#">View ESIM Masters</a></li>
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
                <h2>View ESIM Masters</h2>
              </div>
              <div class="col-lg-6 text-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal">
                  Upload ESIM Masters
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
                  <th>CCID</th>
                  <th>Customer Name</th>
                  <th>ESIM Make</th>
                  <th style="width: 12px;">Created at</th>
                  <th>Last Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <?php
              $i =  1;
              ?>
              <tbody>
                @foreach ($esimCustomer as $customer)
                <tr>
                  <td><?php echo $i; ?></td>
                  <td>{{$customer->ccid}}</td>
                  <td>{{$customer->customer_name}}</td>
                  <td>{{CommonHelper::getEsim($customer->esim)}}</td>
                  <td>{{$customer->created_at}}</td>
                  <td>{{$customer->updated_at}}</td>
                  <td>
                    <form action="/{{$url_type}}/delete-esim-customer/{{$customer->id}}" method="post">
                      @csrf
                      @method('DELETE')
                      <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm margin-top-1" type="submit">Delete</button>

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
    <div class="modal" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="uploadModalLabel">Upload ESIM</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="/admin/upload-esim" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="form-group">
                <label for="csv_file">Choose CSV file:</label>
                <input type="file" class="form-control-file" name="csv_file" id="csv_file" accept=".csv">
              </div>
              <button type="submit" class="btn btn-primary">Upload CSV</button>
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