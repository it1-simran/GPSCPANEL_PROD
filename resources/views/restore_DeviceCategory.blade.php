<?php

use App\Helper\CommonHelper;

?>
@extends('layouts.apps')
@section('content')
@php
    $routePrefix = $url_type ?? 'admin';
@endphp
<style>
    #main-content.restore-device-category-page .wrapper {
        padding-top: 8px !important;
    }

    .restore-device-category-page .dc-breadcrumb-wrap {
        padding: 4px 0 10px 0;
        margin: 0;
    }

    .restore-device-category-page .dc-breadcrumb {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        row-gap: 6px;
        background: #1e293b;
        border-radius: 50px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }

    .restore-device-category-page .dc-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        background: #76CF1C;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
        color: #1e293b;
        text-decoration: none;
    }

    .restore-device-category-page .dc-breadcrumb .bc-home i { font-size: 13px; }

    .restore-device-category-page .dc-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.65);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }

    .restore-device-category-page .dc-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .restore-device-category-page .dc-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .restore-device-category-page .dc-breadcrumb a.bc-item:hover { color: #e2e8f0; }

    .restore-device-category-page .dc-breadcrumb.dc-breadcrumb--scroll {
        max-width: 100%;
        flex-wrap: nowrap !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .restore-device-category-page .dc-breadcrumb.dc-breadcrumb--scroll .bc-home {
        flex-shrink: 0;
    }

    .restore-device-category-page .dc-breadcrumb-wrap + .row { margin-top: 2px; }

    .restore-device-category-page .c_panel { margin-top: 0 !important; }

    .restore-device-category-page .c_title h2::before {
        content: none !important;
        display: none !important;
    }

    .restore-device-category-page .dc-panel-title {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .restore-device-category-page .dc-panel-title > i { color: #76CF1C; font-size: 14px; }

    .restore-device-category-page .dc-title-actions .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }

    .restore-device-category-page .dc-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow-x: visible;
        overflow-y: visible;
        background: #fff;
        margin-top: 4px;
    }

    .restore-device-category-page .c_panel {
        overflow: visible !important;
    }

    .restore-device-category-page .dataTables_wrapper {
        padding: 0 4px 12px;
        box-sizing: border-box;
        width: 100% !important;
        max-width: 100%;
    }

    .restore-device-category-page .dataTables_wrapper > .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .restore-device-category-page .dataTables_wrapper .row:first-child {
        padding: 8px 4px 4px;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
    }

    .restore-device-category-page .dataTables_wrapper .row:first-child > [class*="col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .restore-device-category-page .dataTables_wrapper > .row:nth-child(2) {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .restore-device-category-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] {
        min-width: 0;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .restore-device-category-page .dataTables_scrollHead {
        overflow: hidden !important;
    }

    .restore-device-category-page .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    .restore-device-category-page .dataTables_scroll {
        overflow-x: visible !important;
    }

    .restore-device-category-page .dc-datatable-table thead th {
        background: #f8fafc !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 2px solid #e2e8f0 !important;
        vertical-align: middle !important;
        white-space: nowrap;
        padding-right: 28px !important;
    }

    .restore-device-category-page table.dataTable thead th.sorting,
    .restore-device-category-page table.dataTable thead th.sorting_asc,
    .restore-device-category-page table.dataTable thead th.sorting_desc {
        padding-right: 30px !important;
    }

    .restore-device-category-page .dc-datatable-table tbody td {
        vertical-align: middle !important;
        font-size: 13px;
        color: #334155;
    }

    .restore-device-category-page .btn-restore-cat {
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        white-space: nowrap;
    }

    .restore-device-category-page .btn-restore-cat i {
        margin-right: 6px;
    }

    .restore-device-category-page .dataTables_wrapper .dataTables_length,
    .restore-device-category-page .dataTables_wrapper .dataTables_filter {
        padding: 10px 12px 6px;
    }

    .restore-device-category-page .dataTables_wrapper .dataTables_length select {
        min-width: 72px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }

    /* Narrow screens: layout only — colors unchanged */
    @media (max-width: 991px) {
        .restore-device-category-page .dc-breadcrumb-wrap {
            padding: 2px 0 6px 0;
        }

        .restore-device-category-page .c_content {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .restore-device-category-page .dc-device-category-title-row.bgx-title-container {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px;
        }

        .restore-device-category-page .dc-device-category-actions-wrap {
            text-align: center !important;
        }

        .restore-device-category-page .dc-title-actions .btn {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 44px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            font-size: 13px !important;
        }

        .restore-device-category-page .dataTables_wrapper .row:first-child > [class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: left !important;
            margin-bottom: 10px !important;
        }

        .restore-device-category-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
            margin-bottom: 0 !important;
        }

        .restore-device-category-page .dataTables_wrapper .dataTables_filter {
            width: 100% !important;
        }

        .restore-device-category-page .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            box-sizing: border-box !important;
        }

        .restore-device-category-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: center !important;
            margin-bottom: 8px !important;
        }

        .restore-device-category-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
            margin-bottom: 0 !important;
        }

        .restore-device-category-page .dc-table-wrap {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }

        .restore-device-category-page #restoreDeviceCategoryTable {
            width: auto !important;
            min-width: 720px !important;
            max-width: none !important;
        }

        .restore-device-category-page .dataTables_wrapper > .row:nth-child(2) {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<section id="main-content" class="restore-device-category-page">
  <section class="wrapper">
    <div class="dc-breadcrumb-wrap">
      <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="{{ url($routePrefix . '/view-device-category') }}" class="bc-item">Device category</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Restore device category</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container dc-device-category-title-row">
              <div class="col-xs-12 col-lg-7 col-md-12">
                <h2 class="dc-panel-title"><i class="fa fa-trash-o"></i> Deleted device categories</h2>
              </div>
              <div class="col-xs-12 col-lg-5 col-md-12 text-right dc-device-category-actions-wrap">
                <div class="dc-title-actions">
                  <a href="{{ url($routePrefix . '/add-device-category') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add device category</a>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
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
              <table id="restoreDeviceCategoryTable" class="table table-bordered table-striped table-condensed cf dc-datatable-table" style="border-spacing:0px; width:100%; font-size:14px;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Device category name</th>
                    <th>No of devices</th>
                    <th>Created at</th>
                    <th>Last edit</th>
                    <th>Restore</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($device_categories) > 0)
                  @php $i = 1; @endphp
                  @foreach($device_categories as $device_category)
                  <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $device_category->device_category_name }}</td>
                    <td>{{ CommonHelper::countNoOfDevices($device_category->id) }}</td>
                    <td>{{ CommonHelper::getDateAsTimeZone($device_category->created_at) }}</td>
                    <td>{{ CommonHelper::getDateAsTimeZone($device_category->updated_at) }}</td>
                    <td>
                      <form action="{{ url('admin/restore-device-category/' . $device_category->id) }}" method="post" class="form-inline" onsubmit="return confirm('Restore this device category?');">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success btn-sm btn-restore-cat" type="submit"><i class="fa fa-undo"></i> Restore</button>
                      </form>
                    </td>
                  </tr>
                  @php $i++; @endphp
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>

@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    var $tbl = $('#restoreDeviceCategoryTable');
    if (!$tbl.length || !$.fn.DataTable) {
      return;
    }
    if ($.fn.dataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }
    var lengthMenu = [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']];
    $tbl.DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      scrollX: false,
      autoWidth: true,
      pageLength: 10,
      lengthMenu: lengthMenu,
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>"
    });
    setTimeout(function() {
      $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search deleted categories...');
    }, 100);
  });
</script>
@endsection
