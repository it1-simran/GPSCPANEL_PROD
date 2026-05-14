<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
@php
  $routePrefix = $url_type ?? 'admin';
@endphp
<style>
  #main-content.view-esim-customers-page .wrapper { padding-top: 8px !important; }
  .view-esim-customers-page .dc-breadcrumb-wrap { padding: 4px 0 10px 0; margin: 0; }
  .view-esim-customers-page .dc-breadcrumb {
    display: inline-flex; align-items: center; flex-wrap: wrap; row-gap: 6px;
    background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
  }
  .view-esim-customers-page .dc-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center; margin-right: 10px;
    flex-shrink: 0; color: #1e293b; text-decoration: none;
  }
  .view-esim-customers-page .dc-breadcrumb .bc-home i { font-size: 13px; }
  .view-esim-customers-page .dc-breadcrumb .bc-item {
    color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;
  }
  .view-esim-customers-page .dc-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
  .view-esim-customers-page .dc-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
  .view-esim-customers-page .dc-breadcrumb a.bc-item:hover { color: #e2e8f0; }

  .view-esim-customers-page .dc-breadcrumb.dc-breadcrumb--scroll {
    max-width: 100%;
    flex-wrap: nowrap !important;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }

  .view-esim-customers-page .dc-breadcrumb.dc-breadcrumb--scroll .bc-home {
    flex-shrink: 0;
  }

  .view-esim-customers-page .dc-breadcrumb-wrap + .row { margin-top: 2px; }
  .view-esim-customers-page .c_panel { margin-top: 0 !important; overflow: visible !important; }
  .view-esim-customers-page .c_title h2::before { content: none !important; display: none !important; }
  .view-esim-customers-page .dc-panel-title {
    display: inline-flex !important; align-items: center; gap: 8px; margin: 0;
    color: #fff !important; font-size: 15px !important; font-weight: 800 !important;
    letter-spacing: 0.5px; text-transform: uppercase;
  }
  .view-esim-customers-page .dc-panel-title > i { color: #76CF1C; font-size: 14px; }
  .view-esim-customers-page .dc-title-actions { display: flex; justify-content: flex-end; align-items: center; gap: 8px; flex-wrap: wrap; }
  .view-esim-customers-page .dc-title-actions .btn { border-radius: 8px; font-weight: 700; font-size: 12px; }
  .view-esim-customers-page .dc-title-actions .btn-upload { background: #76CF1C !important; border: 1px solid #76CF1C !important; color: #fff !important; }
  .view-esim-customers-page .dc-title-actions .btn-dl {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #0f172a !important;
    box-shadow: 0 3px 10px rgba(15, 23, 42, 0.18) !important;
  }
  .view-esim-customers-page .dc-title-actions .btn-dl:hover {
    background: #ffffff !important;
    border-color: #76CF1C !important;
    color: #0f172a !important;
  }
  .view-esim-customers-page .dc-title-actions .btn-dl-csv {
    background: #76CF1C !important;
    border: 1px solid #76CF1C !important;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35) !important;
  }
  .view-esim-customers-page .dc-title-actions .btn-dl-csv:hover {
    background: #65b515 !important;
    border-color: #65b515 !important;
    color: #fff !important;
  }
  /* overflow visible: nested .dataTables_scroll handles horizontal scroll (thead + tbody stay aligned) */
  .view-esim-customers-page .dc-table-wrap { border: 1px solid #e2e8f0; border-radius: 10px; overflow: visible; background: #fff; margin-top: 4px; }
  .view-esim-customers-page .dataTables_wrapper {
    width: 100% !important;
    max-width: 100%;
    box-sizing: border-box;
  }
  .view-esim-customers-page .dataTables_wrapper > .row { margin-left: 0 !important; margin-right: 0 !important; }
  .view-esim-customers-page .dataTables_wrapper .row:first-child { padding: 10px 0 12px; background: #fafbfc; border-bottom: 1px solid #e2e8f0; }
  .view-esim-customers-page .dataTables_wrapper .row:first-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
  .view-esim-customers-page .dataTables_wrapper > .row:nth-child(2) { overflow: visible !important; }
  .view-esim-customers-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] { min-width: 0; padding-left: 0; padding-right: 0; }

  /* scrollX: thead clone + body — single synced horizontal scroll */
  .view-esim-customers-page .dataTables_scroll { clear: both; width: 100% !important; }
  .view-esim-customers-page .dataTables_scrollHead {
    overflow: hidden !important;
  }
  .view-esim-customers-page .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: visible !important;
    -webkit-overflow-scrolling: touch;
  }

  .view-esim-customers-page .dataTables_scrollHead table.dataTable,
  .view-esim-customers-page .dataTables_scrollBody table.dataTable {
    table-layout: auto !important;
    margin: 0 !important;
  }
  .view-esim-customers-page .dataTables_wrapper > .row:last-child { padding: 10px 0 8px !important; border-top: 1px solid #e8ecf1; background: #fafbfc; }
  .view-esim-customers-page .dataTables_wrapper > .row:last-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
  .view-esim-customers-page table.dataTable.esim-datatable-table { border-collapse: collapse !important; border-spacing: 0 !important; width: 100% !important; table-layout: fixed !important; }
  .view-esim-customers-page .esim-datatable-table thead th,
  .view-esim-customers-page .esim-datatable-table tbody td { min-width: 0; box-sizing: border-box; overflow-wrap: anywhere; word-break: break-word; }
  .view-esim-customers-page .esim-datatable-table thead th {
    background: #f8fafc !important; color: #1e293b !important; font-weight: 700 !important; font-size: 11px !important;
    text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #e2e8f0 !important; padding: 12px 10px !important;
  }
  .view-esim-customers-page .esim-datatable-table tbody td { font-size: 13px; color: #334155; padding: 12px 10px !important; border-top: none !important; }
  .view-esim-customers-page .btn-esim-delete { border-radius: 6px; font-weight: 700; font-size: 12px; padding: 6px 12px !important; }

  @media (max-width: 991px) {
    .view-esim-customers-page .dc-breadcrumb-wrap {
      padding: 2px 0 6px 0;
    }

    .view-esim-customers-page .c_content {
      padding-left: 12px !important;
      padding-right: 12px !important;
    }

    .view-esim-customers-page .dc-esim-title-row.bgx-title-container {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 12px;
    }

    .view-esim-customers-page .dc-esim-actions-wrap {
      text-align: center !important;
    }

    .view-esim-customers-page .dc-title-actions {
      flex-direction: column !important;
      align-items: stretch !important;
      width: 100% !important;
      gap: 10px !important;
    }

    .view-esim-customers-page .dc-title-actions .btn {
      width: 100% !important;
      max-width: 100% !important;
      min-height: 44px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 8px !important;
      font-size: 13px !important;
    }

    .view-esim-customers-page .dataTables_wrapper .row:first-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: left !important;
      margin-bottom: 10px !important;
    }

    .view-esim-customers-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }

    .view-esim-customers-page .dataTables_wrapper .dataTables_filter {
      width: 100% !important;
    }

    .view-esim-customers-page .dataTables_wrapper .dataTables_filter input {
      width: 100% !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      box-sizing: border-box !important;
    }

    .view-esim-customers-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: center !important;
      margin-bottom: 8px !important;
    }

    .view-esim-customers-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }

    /* Horizontal scroll lives inside .dataTables_scrollBody (scrollX) — do not add a second scrollbar on wrappers */
    .view-esim-customers-page .dc-table-wrap {
      overflow-x: visible !important;
      overflow-y: visible !important;
    }
  }

  /* Upload modal */
  #uploadModal .modal-dialog { max-width: 540px; margin-top: 80px; }
  #uploadModal .modal-content { border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 24px 48px rgba(15,23,42,.22); }
  #uploadModal .modal-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-bottom: 3px solid #76CF1C; padding: 14px 18px;
  }
  #uploadModal .modal-title { color: #fff !important; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; font-size: 14px; }
  #uploadModal .close { color: #fff; opacity: .85; text-shadow: none; font-size: 24px; }
  #uploadModal .close:hover { color: #76CF1C; opacity: 1; }
  #uploadModal .modal-body { padding: 18px; background: #fff; }
  #uploadModal .form-group label { color: #334155; font-weight: 600; font-size: 13px; }
  #uploadModal .form-control-file { border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; width: 100%; }
  #uploadModal .btn-upload-csv { background: #76CF1C !important; border: none !important; color: #fff !important; border-radius: 8px !important; font-weight: 700; }
</style>
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