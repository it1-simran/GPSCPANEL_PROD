<?php

use App\Helper\CommonHelper;

?>
@extends('layouts.apps')
@section('content')
@php
    $routePrefix = $url_type ?? 'admin';
    $dcIsAdmin = Auth::check() && strcasecmp(trim((string) Auth::user()->user_type), 'admin') === 0;
@endphp
<style>
    #main-content.view-device-category-page .wrapper {
        padding-top: 8px !important;
    }

    .view-device-category-page .dc-breadcrumb-wrap {
        padding: 4px 0 10px 0;
        margin: 0;
    }

    .view-device-category-page .dc-breadcrumb {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        row-gap: 6px;
        background: #1e293b;
        border-radius: 50px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }

    .view-device-category-page .dc-breadcrumb .bc-home {
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

    .view-device-category-page .dc-breadcrumb .bc-home i { font-size: 13px; }

    .view-device-category-page .dc-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.65);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }

    .view-device-category-page .dc-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .view-device-category-page .dc-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .view-device-category-page .dc-breadcrumb a.bc-item:hover { color: #e2e8f0; }

    .view-device-category-page .dc-breadcrumb.dc-breadcrumb--scroll {
        max-width: 100%;
        flex-wrap: nowrap !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .view-device-category-page .dc-breadcrumb.dc-breadcrumb--scroll .bc-home {
        flex-shrink: 0;
    }

    .view-device-category-page .dc-breadcrumb-wrap + .row { margin-top: 2px; }

    .view-device-category-page .c_panel { margin-top: 0 !important; }

    .view-device-category-page .c_title h2::before {
        content: none !important;
        display: none !important;
    }

    .view-device-category-page .dc-panel-title {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        text-align: left !important;
        float: none !important;
    }

    .view-device-category-page .dc-panel-title > i { color: #76CF1C; font-size: 14px; }

    .view-device-category-page .dc-title-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
        align-items: center;
    }

    .view-device-category-page .dc-title-actions .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }

    /* Excel: high contrast on dark title bar (avoid same-as-header fill) */
    .view-device-category-page .dc-title-actions .btn-dc-excel {
        background: #f8fafc !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12) !important;
    }

    .view-device-category-page .dc-title-actions .btn-dc-excel:hover {
        background: #ffffff !important;
        color: #0f172a !important;
        border-color: #76CF1C !important;
    }

    /*
      Avoid two horizontal scrollbars (one under thead / one at bottom):
      - No overflow on .dc-table-wrap (outer was stacking with inner).
      - Single scroll on the DT row that wraps the table only.
      - If scrollX ever creates .dataTables_scroll*, head must not scroll horizontally.
    */
    .view-device-category-page .dc-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        margin-top: 4px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .view-device-category-page .c_panel {
        overflow: visible !important;
    }

    .view-device-category-page .dataTables_wrapper {
        padding: 0 0 12px;
        box-sizing: border-box;
        width: 100% !important;
        max-width: 100%;
    }

    .view-device-category-page .dataTables_wrapper > .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .view-device-category-page .dataTables_wrapper .row:first-child {
        padding: 10px 0 12px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        background: #fafbfc;
        border-bottom: 1px solid #e2e8f0;
    }
    .view-device-category-page .dataTables_wrapper .row:first-child > [class*="col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* Table row: fixed layout keeps columns inside panel; scroll only on very small widths */
    .view-device-category-page .dataTables_wrapper > .row:nth-child(2) {
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .view-device-category-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] {
        min-width: 0;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .view-device-category-page .dataTables_wrapper > .row:last-child {
        margin-top: 0 !important;
        padding: 12px 0 8px !important;
        border-top: 1px solid #e8ecf1;
        background: #fafbfc;
        border-radius: 0 0 10px 10px;
    }
    .view-device-category-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* No table-bordered: removes vertical grid between columns; only light row rules */
    .view-device-category-page table.dataTable.dc-datatable-table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100%;
        border: 1px solid #e8ecf1 !important;
        border-radius: 0;
    }
    .view-device-category-page table.dataTable.dc-datatable-table thead th {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
    }
    .view-device-category-page table.dataTable.dc-datatable-table tbody td {
        border-left: none !important;
        border-right: none !important;
        border-top: none !important;
        border-bottom: 1px solid #eef2f6 !important;
    }
    .view-device-category-page table.dataTable.dc-datatable-table tbody tr:last-child td {
        border-bottom: none !important;
    }
    .view-device-category-page .dc-datatable-table thead th,
    .view-device-category-page .dc-datatable-table tbody td,
    .view-device-category-page table.dataTable.dc-datatable-table thead th,
    .view-device-category-page table.dataTable.dc-datatable-table tbody td {
        min-width: 0;
        box-sizing: border-box;
        overflow-wrap: break-word;
        word-break: break-word;
    }
    /* Site primary navy — ONLY thead (matches custom.css .btn-primary #1d283e) */
    .view-device-category-page table#deviceCategoryTable.dataTable.dc-datatable-table.no-global-table-ui thead th,
    .view-device-category-page .dc-datatable-table thead th {
        white-space: normal;
        line-height: 1.25;
        padding: 12px 10px !important;
        background: linear-gradient(180deg, #1d283e 0%, #243652 100%) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 2px solid #141c2c !important;
        vertical-align: middle !important;
    }

    .view-device-category-page table.dataTable.dc-datatable-table thead th.sorting:after,
    .view-device-category-page table.dataTable.dc-datatable-table thead th.sorting_asc:after,
    .view-device-category-page table.dataTable.dc-datatable-table thead th.sorting_desc:after {
        color: rgba(255, 255, 255, 0.92) !important;
        opacity: 1 !important;
    }

    .view-device-category-page table.dataTable.dc-datatable-table thead th.sorting:before,
    .view-device-category-page table.dataTable.dc-datatable-table thead th.sorting_asc:before,
    .view-device-category-page table.dataTable.dc-datatable-table thead th.sorting_desc:before {
        color: rgba(255, 255, 255, 0.92) !important;
        opacity: 1 !important;
    }
    .view-device-category-page .dc-datatable-table col.dc-w-sr { width: 5%; }
    .view-device-category-page .dc-datatable-table col.dc-w-name { width: 22%; }
    .view-device-category-page .dc-datatable-table col.dc-w-num { width: 9%; }
    .view-device-category-page .dc-datatable-table col.dc-w-date { width: 12%; }
    .view-device-category-page .dc-datatable-table col.dc-w-actions { width: 13%; }
    .view-device-category-page .dc-datatable-table col.dc-w-sr-sm { width: 10%; }
    .view-device-category-page .dc-datatable-table col.dc-w-name-sm { width: 32%; }
    .view-device-category-page .dc-datatable-table col.dc-w-num-sm { width: 14.5%; }

    .view-device-category-page .dataTables_scrollHead {
        overflow: hidden !important;
    }

    .view-device-category-page .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: visible !important;
    }

    .view-device-category-page .dataTables_scroll {
        overflow-x: visible !important;
    }

    .view-device-category-page table.dataTable thead th.sorting,
    .view-device-category-page table.dataTable thead th.sorting_asc,
    .view-device-category-page table.dataTable thead th.sorting_desc {
        padding-right: 22px !important;
    }

    .view-device-category-page .dc-datatable-table tbody td {
        vertical-align: top !important;
        font-size: 13px;
        color: #334155;
        padding: 12px 10px !important;
    }

    .view-device-category-page .btn-dc-edit,
    .view-device-category-page .btn-dc-delete {
        width: 34px;
        height: 30px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-weight: 600;
        white-space: nowrap;
    }
    .view-device-category-page .btn-dc-edit i,
    .view-device-category-page .btn-dc-delete i {
        margin: 0;
        font-size: 14px;
        line-height: 1;
    }
    .view-device-category-page a.btn-dc-edit { text-decoration: none !important; }
    .view-device-category-page .dc-actions-inner {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .view-device-category-page .dataTables_wrapper .dataTables_length,
    .view-device-category-page .dataTables_wrapper .dataTables_filter {
        padding: 0 2px 0 0;
    }

    /* Pagination on this page: no heavy box borders (lines between page #s) */
    .view-device-category-page .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
        margin: 0 5px !important;
    }
    .view-device-category-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .view-device-category-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        border: none !important;
        box-shadow: 0 2px 8px rgba(118, 207, 28, 0.3) !important;
    }
    .view-device-category-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .view-device-category-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        border: none !important;
        background: #f8fafc !important;
    }
    .view-device-category-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border: none !important;
    }

    .view-device-category-page .dataTables_wrapper .dataTables_length select {
        min-width: 72px;
        padding: 4px 8px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }

    /* —— Narrow screens: stack header actions; scrollable wide table (avoid fixed-layout squeeze) —— */
    @media (max-width: 991px) {
        .view-device-category-page .dc-breadcrumb-wrap {
            padding: 2px 0 6px 0;
        }

        .view-device-category-page .dc-breadcrumb {
            padding: 5px 14px 5px 6px;
        }

        .view-device-category-page .c_content {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .view-device-category-page .dc-device-category-title-row.bgx-title-container {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px;
        }

        .view-device-category-page .dc-device-category-actions-wrap {
            text-align: center !important;
        }

        .view-device-category-page .dc-title-actions {
            flex-direction: column !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            justify-content: stretch !important;
            width: 100% !important;
            gap: 10px !important;
        }

        .view-device-category-page .dc-title-actions .btn {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 44px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            font-size: 13px !important;
        }

        .view-device-category-page .dataTables_wrapper .row:first-child > [class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: left !important;
            margin-bottom: 10px !important;
        }

        .view-device-category-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
            margin-bottom: 0 !important;
        }

        .view-device-category-page .dataTables_wrapper .dataTables_filter {
            width: 100% !important;
        }

        .view-device-category-page .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            box-sizing: border-box !important;
        }

        .view-device-category-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: center !important;
            margin-bottom: 8px !important;
        }

        .view-device-category-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
            margin-bottom: 0 !important;
        }

        .view-device-category-page .dc-table-wrap {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }

        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui {
            table-layout: auto !important;
            width: auto !important;
            max-width: none !important;
        }

        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui.dc-datatable--admin {
            min-width: 1020px !important;
        }

        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui.dc-datatable--reseller {
            min-width: 720px !important;
        }

        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui thead th,
        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui tbody td {
            white-space: nowrap;
        }

        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui thead th:nth-child(2),
        .view-device-category-page table.dataTable.dc-datatable-table.no-global-table-ui tbody td:nth-child(2) {
            white-space: normal !important;
            max-width: 220px;
            min-width: 140px;
        }

        .view-device-category-page .dataTables_wrapper > .row:nth-child(2) {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<section id="main-content" class="view-device-category-page">
  <section class="wrapper">
    <div class="dc-breadcrumb-wrap">
      <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Device category</span>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View device category</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container dc-device-category-title-row">
              <div class="{{ $dcIsAdmin ? 'col-xs-12 col-lg-5' : 'col-xs-12 col-lg-12' }} col-md-12">
                <h2 class="dc-panel-title"><i class="fa fa-table"></i> Show device categories</h2>
              </div>
              @if($dcIsAdmin)
              <div class="col-xs-12 col-lg-7 col-md-12 text-right dc-device-category-actions-wrap">
                <div class="dc-title-actions">
                  <a href="{{ url($routePrefix . '/add-device-category') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add device category</a>
                  <a href="{{ route('deviceCategory.excel') }}" class="btn btn-default btn-dc-excel"><i class="fa fa-file-excel-o"></i> Download Excel</a>
                  <a href="{{ route('deviceCategory.csv') }}" class="btn btn-success"><i class="fa fa-download"></i> Download CSV</a>
                </div>
              </div>
              @endif
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
            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none;"></div>
            <div class="dc-table-wrap">
              <table id="deviceCategoryTable" class="table table-striped cf dc-datatable-table no-global-table-ui {{ $dcIsAdmin ? 'dc-datatable--admin' : 'dc-datatable--reseller' }}" style="font-size:14px;">
                @if($dcIsAdmin)
                <colgroup>
                  <col class="dc-w-sr" />
                  <col class="dc-w-name" />
                  <col class="dc-w-num" /><col class="dc-w-num" /><col class="dc-w-num" /><col class="dc-w-num" />
                  <col class="dc-w-date" /><col class="dc-w-date" />
                  <col class="dc-w-actions" />
                </colgroup>
                @else
                <colgroup>
                  <col class="dc-w-sr-sm" />
                  <col class="dc-w-name-sm" />
                  <col class="dc-w-num-sm" /><col class="dc-w-num-sm" /><col class="dc-w-num-sm" /><col class="dc-w-num-sm" />
                </colgroup>
                @endif
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Device Category Name</th>
                    <th>No of Devices</th>
                    <th>No of Templates</th>
                    <th>No of Users</th>
                    <th>No of Firmwares</th>
                    @if($dcIsAdmin)
                    <th>Created at</th>
                    <th>Last Edit</th>
                    <th class="text-center">Actions</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @if(count($device_categories) > 0)
                  <?php
                  $i = 1;
                  ?>
                  @foreach($device_categories as $device_category)
                  @php
                  $countDevices = CommonHelper::countNoOfDevices($device_category->id);
                  @endphp
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$device_category->device_category_name}}</td>
                    <td>{{$device_category->devices_count}}</td>
                    <td>{{$device_category->templates_count}}</td>
                    <td>{{$device_category->writers_count}}</td>
                    <td>{{$device_category->firmware_count}}</td>
                    @if($dcIsAdmin)
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->created_at)}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->updated_at)}}</td>
                    <td class="text-center dc-actions-cell">
                      <div class="dc-actions-inner">
                        <a href="{{ url($routePrefix . '/edit-device-category/' . $device_category->id) }}" class="btn btn-primary btn-sm btn-dc-edit" title="Edit" aria-label="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        <button style="margin-top: 0px;" class="btn btn-danger btn-sm btn-dc-delete" onclick="toggleModalDelDeviceCategory(<?php echo $device_category->id; ?>)" type="button" title="Delete" aria-label="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
                      </div>
                      <div class="modal" id="deviceCategoryDelOptionModal{{$device_category->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-md">
                          <div class="modal-content">
                            <form action="{{ url('admin/delete-device-category/' . $device_category->id) }}" id="deleteDeviceCategory"
                              onsubmit="return false;" method="post">
                              @csrf
                              @method('DELETE')


                              <div class="modal-header">
                                <button type="button" class="close closeEditDelOptionsModal hide" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                                <h4 class="modal-title"><strong>Confirmation</strong></h4>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" class="action_type">
                                <input type="hidden" class="change_type">
                                <div class="steps_area">
                                  <div class="step1">
                                    @if($device_category->devices_count >0)
                                    <div class="">
                                      <label for="curl" class="control-label col-lg-12 ">Choose another Device Category <span class="require">*</span></label>
                                      <div class="col-lg-6">
                                        <select id="s2example-2{{$device_category->id }}" classs="examplereser" name="deviceCategory" >
                                          <option value=""> </option>
                                          @foreach($device_categories as $deviceCategory)
                                          @if($device_category->id != $deviceCategory->id)
                                          <option value="{{$deviceCategory->id}}">{{$deviceCategory->device_category_name}}</option>
                                          @endif
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                    @else
                                    <div>
                                      <p> Are you sure you want to delete this?</p>
                                    </div>

                                    @endif
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer row bgx-custom-modal-footer">
                                <button class="col btn btn-primary btn-flat" type="button" onclick="closeDeviceCategoryDeleteModal(<?php echo $device_category->id; ?>)"><i class="fa fa-arrow-left"></i> Back</button>
                                <button class="col btn btn-primary btn-flat submitDataErr{{$device_category->id}}" type="button" onclick="submitDelCategoryForm(<?php echo $device_category->id; ?>)" data-count="<?php echo $device_category->devices_count;?>"><i class="fa fa-check"></i> Submit</button>
                                <input type="hidden" id="d_device_ctaegory_id" name="d_device_Category_id" value="{{$device_category->id}}" />
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                    @endif
                  </tr>
                  <?php $i++; ?>
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>

@stop

@section('scripts')
<script type="text/javascript">
  function submitDelCategoryForm(id) {

    let deviceCount = $('.submitDataErr'+id).attr("data-count"); 
    let choosenDeviceCategory = $("#s2example-2" + id).val();
    if(deviceCount > 0 && choosenDeviceCategory == ''){
      alert("Please Chosse Device Category ")
      return false;
    }
    $(".error_msg").html('').hide();

    $.ajax({
      url: "{{ url('admin/delete-device-category') }}/" + id,
      type: 'DELETE',
      data: {
        'choosenDeviceCategory': choosenDeviceCategory
      },
      success: function(response) {
        let result = JSON.parse(response)
        console.log("result", result);
        if (result.status == 200) {
          $(".error_msg").append(result.message).show();
          $('#deviceCategoryDelOptionModal' + id).modal("hide");
          document.documentElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          window.location.reload();
        }
        return false
      },
      error: function(xhr, status, error) {
        alert("An error occurred: " + error);
      }
    });

  }
  function closeDeviceCategoryDeleteModal(id) {
    $('#deviceCategoryDelOptionModal' + id).modal("hide");

  }

  function toggleModalDelDeviceCategory(id) {
    $('#deviceCategoryDelOptionModal' + id).modal("show");
    $('#s2example-2' + id).select2({
      placeholder: "Search and Select",
    });
  }

  $(document).ready(function() {
    var $tbl = $('#deviceCategoryTable');
    if (!$tbl.length) {
      return;
    }
    if (!$.fn.DataTable) {
      return;
    }
    if ($.fn.dataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }
    var lengthMenu = [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']];
    var dcCategoryTable = $tbl.DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      scrollX: false,
      autoWidth: false,
      pageLength: 10,
      stripeClasses: [],
      lengthMenu: lengthMenu,
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
      initComplete: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      }
    });
    setTimeout(function() {
      $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search device categories...');
      try { dcCategoryTable.columns.adjust(); } catch (e) { /* noop */ }
    }, 100);
  });
</script>
@endsection