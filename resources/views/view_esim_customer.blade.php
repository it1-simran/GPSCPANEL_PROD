<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/view-esim-customer.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/view-esim-customer.css')) }}">
@endpush
@section('content')
@php
  $routePrefix = $url_type ?? 'admin';
@endphp

<section id="main-content" class="view-esim-customers-page">
  <section class="wrapper">
    <div class="dc-breadcrumb-wrap">
      <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Firmware Management</span>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View ESIM Masters</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container dc-esim-title-row">
              <div class="col-xs-12 col-lg-6 col-md-12">
                <h2 class="dc-panel-title"><i class="fa fa-list-alt"></i> View ESIM Masters</h2>
              </div>
              <div class="col-xs-12 col-lg-6 col-md-12 text-right dc-esim-actions-wrap">
                <div class="dc-title-actions">
                <button type="button" class="btn btn-upload" data-toggle="modal" data-target="#uploadModal" style="margin-top:1px">
                  <i class="fa fa-upload"></i> Upload ESIM Masters
                </button>
                @if(Auth::user()->user_type == "Admin")
                  <a href="{{ route('esimMasters.excel') }}" class="btn btn-dl"><i class="fa fa-file-excel-o"></i> Download Excel</a>
                  <a href="{{ route('esimMasters.csv') }}" class="btn btn-dl btn-dl-csv"><i class="fa fa-file-text-o"></i> Download CSV</a>
                @endif
                </div>
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
            <div class="dc-table-wrap">
            <table id="esim" class="table table-bordered table-striped esim-datatable-table" style="width: 100%; font-size: 14px;">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>CCID</th>
                  <th>Customer Name</th>
                  <th>ESIM Make</th>
                  <th>Created at</th>
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
                      <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm btn-esim-delete" type="submit">Delete</button>

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
              <button type="submit" class="btn btn-upload-csv">Upload CSV</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@stop
@section('scripts')
<script>
  // $(document).ready(function() {
  //   $("#esim").dataTable({
  //     paging: true,
  //     searching: true,
  //     info: true,
  //     ordering: true,
  //     lengthChange: true,
  //     // pageLength: 10,
  //     // scrollX: true,
  //     // scrollY: '500px',
  //     scrollCollapse: true,
  //     "aLengthMenu": [
  //       [25, 50, 100, 500, -1],
  //       [25, 50, 100, 500, "All"]
  //     ],
  //     "iDisplayLength": 25
  //   });
  // });

  $(document).ready(function () {
      var $tbl = $('#esim');
      if (!$tbl.length || !$.fn.DataTable) return;

      var dt = $tbl.DataTable({
          paging: true,
          searching: true,
          info: true,
          ordering: true,
          lengthChange: true,
          responsive: false,
          autoWidth: false,
          scrollX: true,
          scrollCollapse: false,

          lengthMenu: [
              [25, 50, 100, 500, -1],
              [25, 50, 100, 500, "All"]
          ],
          pageLength: 25,
          dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
          initComplete: function () {
            try { this.api().columns.adjust(); } catch (e) {}
          },
          drawCallback: function () {
            try { this.api().columns.adjust(); } catch (e) {}
          }
      });

      $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search ESIM masters...');

      $(window).on('resize orientationchange', function () {
        try { dt.columns.adjust(); } catch (e) {}
      });
  });
</script>
@endsection