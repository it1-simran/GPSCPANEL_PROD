<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
<section id="main-content" class="view-settings-page">
  <section class="wrapper">
    <div class="protocol-breadcrumb-wrap">
      <nav class="protocol-breadcrumb protocol-breadcrumb--scroll" aria-label="Breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url($url_type . '/view-template') }}" class="bc-item">Settings</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View Settings</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel view-settings-c-panel">
          <div class="c_title">
            <div class="row bgx-title-container view-settings-title-row">
              <div class="col-xs-12 col-lg-6 view-settings-title-col">
                <h2 class="view-settings-panel-title">
                  <i class="fa fa-table"></i>
                  Show Settings
                </h2>
              </div>
              <div class="col-xs-12 col-lg-6 text-right view-settings-actions-col">
                <a href="{{ url($url_type . '/add-template') }}" class="btn btn-success btn-settings-add">
                  <i class="fa fa-plus"></i> Add Setting
                </a>
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
            <div class="settings-view-toolbar">
              <div class="tabs settings-category-tabs" role="tablist">
              @foreach ($getDeviceCategory as $key => $category)
              @if( Session::get('device_category_id'))
              <button class="tablinks {{Session::get('device_category_id') == $category->id ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')" role="tab">
                {{ $category->device_category_name }}
              </button>
              @else
                <button class="tablinks {{ $key==0 ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')" role="tab">
                {{ $category->device_category_name }}
              </button>
              @endif
              @endforeach
              </div>
              @if(Auth::user()->user_type == "Admin")
              <div class="settings-export-actions">
                <a href="{{ route('export.excel') }}" class="btn btn-default btn-export btn-export-excel"><i class="fa fa-file-excel-o"></i> Excel</a>
                <a href="{{ route('export.csv') }}" class="btn btn-success btn-export btn-export-csv"><i class="fa fa-file-text-o"></i> CSV</a>
              </div>
              @endif
            </div>
            @foreach ($getDeviceCategory as $category)
            @php
            $templateInfo = CommonHelper::getTemplatesInfo($category->id);
            @endphp

            <div id="tab{{ $category->id }}" class="tabcontent settings-tab-panel">
              <?php $i = 1; ?>
              <div class="settings-table-wrap">
              <table id="datable{{ $category->id }}" class="example table table-striped settings-template-table settings-data-grid no-global-table-ui" cellspacing="0" style="border-spacing: 0; font-size: 13px;">
                <thead>
                  <tr>
                    <th style="min-width: 60px;">Sr. No.</th>
                    <th style="min-width: 180px;">Template Name</th>
                    <th style="min-width: 150px;">Device Category</th>
                    <th style="min-width: 150px;">Created at</th>
                    <th style="min-width: 150px;">Last Edit</th>
                    <th style="min-width: 120px;">Default Template</th>
                    <th style="min-width: 120px;">View</th>
                    @if(Auth::user()->user_type == "Admin")
                    <th style="min-width: 120px;">Apply Setting</th>
                    @endif
                    <th style="min-width: 80px;">Delete</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($templateInfo as $contactValue)
                  <?php

                  $config = json_decode($contactValue->configurations, true);
                  ?>
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$contactValue->template_name}}</td>
                    <td><?php echo CommonHelper::getDeviceCategoryName($contactValue->device_category_id); ?> </td>
                    <td>{{CommonHelper::getDateAsTimeZone($contactValue->created_at) ?? 'N/A'}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($contactValue->updated_at) ?? 'N/A'}} </td>

                    <td><?php if ($contactValue->default_template == '1') { ?>
                        <span class="label label-warning settings-default-badge"><i class="fa fa-star"></i> Yes</span>
                      <?php } else { ?>
                        <span class="text-muted settings-default-no">—</span>
                      <?php } ?>
                    </td>
                    <td>
                      <a href="{{ url($url_type . '/view-template-configurations/' . $contactValue->id) }}" class="btn btn-info btn-sm btn-settings-view"><i class="fa fa-eye"></i> View</a>
                    </td>
                     @if(Auth::user()->user_type == "Admin")
                    <td>
                      <button type="button" class="btn btn-success btn-sm btn-settings-apply margin-top-1" onclick="open_model(<?php echo $contactValue->id; ?>)"><i class="fa fa-check"></i> Apply</button>
                      @if(isset($contactValue))
                      <div class="modal view-settings-assign-modal" id="modal-responsive-{{$contactValue->id}}" tabindex="-1" role="dialog" aria-labelledby="assign-template-title-{{ $contactValue->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                          <div class="modal-content view-settings-modal-shell">
                            <div class="modal-header view-settings-modal-header">
                              <button type="button" class="close view-settings-modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title view-settings-modal-title" id="assign-template-title-{{ $contactValue->id }}"><i class="fa fa-link"></i> Assign template</h4>
                            </div>
                            <div class="modal-body view-settings-modal-body">
                              <div class="row">
                                <div class="col-md-12">
                                  <form action="/{{$url_type}}/assign-template/{{$contactValue->id}}" method="post">
                                    @csrf
                                    <div class="form-group view-settings-modal-field">
                                      <input type="hidden" name="test_id" id="test_id_{{ $contactValue->id }}" value="">
                                      <label class="control-label view-settings-modal-label" for="devices-{{ $contactValue->id }}">Single / multiple select device</label>
                                      <select class="selectDevice view-settings-device-select" id="devices-{{ $contactValue->id}}" name="devices[]" multiple>
                                        <option></option>
                                        <optgroup label="Assigned / unassigned devices">
                                          <?php echo CommonHelper::unassignDevices($contactValue->device_category_id); ?>
                                        </optgroup>
                                      </select>
                                      <p class="help-block view-settings-modal-hint">Choose one or more devices to apply this setting template.</p>
                                    </div>
                                    <div class="view-settings-modal-footer">
                                      <button type="submit" class="btn btn-settings-modal-assign"><i class="fa fa-check"></i> Assign</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      @endif
                    </td>
                    @endif
                    <!-- <td>
                      @if(Auth::user()->user_type=='Admin')
                      <a href="<?php echo url('admin/edit-template/' . $contactValue->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                      @elseif(Auth::user()->user_type!=='Admin')
                      <a href="/{{$url_type}}/edit-template/{{$contactValue->id}}" class="btn btn-primary btn-sm">Edit</a>
                      @endif
                    </td> -->
                    <td>
                      <form action="/{{$url_type}}/delete-template/{{$contactValue->id}}" method="post">
                        @csrf
                        @method('DELETE')
                        @if($contactValue->default_template=='0')
                        <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm margin-top-1" type="submit"><i class="fa fa-trash"></i> Delete</button>
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
            @endforeach

          </div>

        </div><!--/.c_panel-->
      </div><!--/col-md-12-->
    </div><!--/row-->

    {{-- Modals moved here via JS: outside table overflow + avoids document.body focus/ARIA edge cases --}}
    <div id="view-settings-modal-root" class="view-settings-modal-root"></div>

  </section>
</section>
<style>
    .view-settings-page .wrapper {
        padding-top: 8px !important;
    }

    .view-settings-page .protocol-breadcrumb-wrap {
        padding: 4px 0 12px 0 !important;
        margin: 0 !important;
    }

    .view-settings-page .protocol-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        flex-wrap: wrap;
        row-gap: 6px;
        background: #1e293b !important;
        border-radius: 50px !important;
        padding: 6px 18px 6px 8px !important;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18) !important;
    }

    .view-settings-page .protocol-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #76CF1C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .view-settings-page .protocol-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .view-settings-page .protocol-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }

    .view-settings-page .protocol-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .view-settings-page .protocol-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .view-settings-page .protocol-breadcrumb a.bc-item:hover {
        color: #e2e8f0;
    }

    /* Single-line breadcrumb on narrow screens (horizontal scroll) */
    .view-settings-page .protocol-breadcrumb.protocol-breadcrumb--scroll {
        max-width: 100%;
        flex-wrap: nowrap !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .view-settings-page .protocol-breadcrumb.protocol-breadcrumb--scroll .bc-home {
        flex-shrink: 0;
    }

    /* Prevent panel clipping overflow from horizontal table scroll */
    .view-settings-page .view-settings-c-panel.c_panel {
        overflow: visible !important;
    }

    .view-settings-page .c_title {
        margin-top: 4px !important;
    }

    .view-settings-page .c_title h2::before {
        content: none !important;
        display: none !important;
    }

    .view-settings-page .view-settings-panel-title {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }

    .view-settings-page .view-settings-panel-title > i {
        color: #76CF1C;
        font-size: 14px;
        width: 22px;
        text-align: center;
    }

    /* Override custom.css .bgx-title-container .btn-success (small 12px / 7px padding) */
    .view-settings-page .c_title .bgx-title-container .btn-success.btn-settings-add,
    .view-settings-page .c_title .btn-settings-add.btn-success {
        background: linear-gradient(135deg, #81e025 0%, #76CF1C 100%) !important;
        border: none !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        letter-spacing: 0.02em !important;
        padding: 10px 20px !important;
        min-height: 42px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        white-space: nowrap !important;
        box-shadow: 0 4px 12px rgba(118, 207, 28, 0.28) !important;
    }

    .view-settings-page .c_title .btn-settings-add .fa {
        font-size: 14px !important;
    }

    .view-settings-page .c_title .bgx-title-container .btn-success.btn-settings-add:hover,
    .view-settings-page .c_title .btn-settings-add.btn-success:hover {
        background: linear-gradient(135deg, #65b515 0%, #5dab13 100%) !important;
        box-shadow: 0 5px 14px rgba(118, 207, 28, 0.38) !important;
    }

    .view-settings-page .settings-view-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .view-settings-page .settings-category-tabs {
        overflow: visible !important;
        border-bottom: none !important;
        display: flex !important;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        flex: 1 1 auto;
        min-width: 0;
    }

    .view-settings-page .settings-category-tabs .tablinks {
        float: none !important;
        border-radius: 999px !important;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        color: #475569 !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        margin: 0 !important;
        transition: all 0.2s ease !important;
    }

    .view-settings-page .settings-category-tabs .tablinks:hover {
        background: #f0fce8 !important;
        border-color: rgba(118, 207, 28, 0.45) !important;
        color: #1d283e !important;
    }

    .view-settings-page .settings-category-tabs .tablinks.active {
        background: linear-gradient(135deg, #76CF1C 0%, #5dab13 100%) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(118, 207, 28, 0.28) !important;
    }

    .view-settings-page .settings-export-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }

    .view-settings-page .btn-export {
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        padding: 7px 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }

    .view-settings-page .btn-export-csv {
        background: linear-gradient(135deg, #76CF1C 0%, #5dab13 100%) !important;
        border: none !important;
        color: #fff !important;
        box-shadow: 0 3px 10px rgba(118, 207, 28, 0.28) !important;
    }

    .view-settings-page .btn-export-csv:hover {
        background: linear-gradient(135deg, #65b515 0%, #4d9a0f 100%) !important;
        color: #fff !important;
    }

    .view-settings-page .btn-export-excel {
        background: #1e293b !important;
        color: #fff !important;
        border: 1px solid #334155 !important;
    }

    .view-settings-page .btn-export-excel:hover {
        background: #334155 !important;
        color: #fff !important;
    }

    .view-settings-page .settings-tab-panel.tabcontent {
        border: none !important;
        padding: 0 !important;
    }

    .view-settings-page .settings-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow-x: auto;
        overflow-y: visible;
        background: #fff;
        -webkit-overflow-scrolling: touch;
    }

    /* Single-table DataTables (no scrollX): keep header/body aligned */
    .view-settings-page .settings-table-wrap .dataTables_wrapper {
        width: 100% !important;
        clear: both;
    }

    .view-settings-page .settings-table-wrap table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        table-layout: auto !important;
    }

    .view-settings-page .settings-table-wrap table.dataTable thead th,
    .view-settings-page .settings-table-wrap table.dataTable tbody td {
        box-sizing: border-box !important;
    }

    .view-settings-page .settings-table-wrap .dataTables_scroll,
    .view-settings-page .settings-table-wrap .dataTables_scrollHead {
        overflow: visible !important;
    }

    /* Table header — same slate/navy as breadcrumb, card title, global DataTables */
    .view-settings-page .settings-template-table thead th {
        background: #1e293b !important;
        color: #fff !important;
        font-weight: 800 !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: none !important;
        border-bottom: none !important;
        padding: 13px 16px !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
    }

    .view-settings-page .settings-template-table thead th:first-child {
        border-top-left-radius: 10px !important;
    }

    .view-settings-page .settings-template-table thead th:last-child {
        border-top-right-radius: 10px !important;
    }

    .view-settings-page .settings-template-table thead .sorting:after,
    .view-settings-page .settings-template-table thead .sorting_asc:after,
    .view-settings-page .settings-template-table thead .sorting_desc:after {
        color: rgba(255, 255, 255, 0.55) !important;
        opacity: 1 !important;
    }

    .view-settings-page .settings-template-table thead .sorting:after {
        opacity: 0.35 !important;
    }

    .view-settings-page .settings-default-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 6px;
    }

    .view-settings-page .btn-settings-view,
    .view-settings-page .btn-settings-apply {
        border-radius: 6px !important;
        font-weight: 600 !important;
    }

    .view-settings-page .settings-template-table td:nth-child(4),
    .view-settings-page .settings-template-table td:nth-child(5) {
        white-space: nowrap;
    }

    /* —— Assign template modal (site navy + brand green) —— */
    .view-settings-page .view-settings-modal-shell {
        border: none !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.22) !important;
    }

    .view-settings-page .view-settings-modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
        border-bottom: 2px solid #76CF1C !important;
        padding: 16px 20px !important;
        border-radius: 0 !important;
    }

    .view-settings-page .view-settings-modal-title {
        margin: 0 !important;
        padding: 0 !important;
        color: #fff !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0.55px !important;
        text-transform: uppercase !important;
        line-height: 1.3 !important;
        display: flex !important;
        align-items: center;
        gap: 10px;
    }

    .view-settings-page .view-settings-modal-title > i {
        color: #76CF1C !important;
        font-size: 15px !important;
    }

    .view-settings-page .view-settings-modal-close {
        margin-top: -4px !important;
        opacity: 0.85 !important;
        color: #fff !important;
        text-shadow: none !important;
        font-size: 26px !important;
        font-weight: 300 !important;
        line-height: 1 !important;
    }

    .view-settings-page .view-settings-modal-close:hover,
    .view-settings-page .view-settings-modal-close:focus {
        opacity: 1 !important;
        color: #76CF1C !important;
    }

    .view-settings-page .view-settings-modal-body {
        padding: 22px 24px 20px !important;
        background: #fff !important;
    }

    .view-settings-page .view-settings-modal-label {
        display: block !important;
        margin-bottom: 8px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #334155 !important;
        text-transform: none !important;
    }

    .view-settings-page .view-settings-modal-hint {
        margin: 10px 0 0 0 !important;
        font-size: 12px !important;
        color: #64748b !important;
    }

    .view-settings-page .view-settings-modal-footer {
        margin-top: 22px !important;
        padding-top: 18px !important;
        border-top: 1px solid #e2e8f0 !important;
        text-align: center !important;
    }

    .view-settings-page .btn-settings-modal-assign {
        background: linear-gradient(135deg, #76CF1C 0%, #5dab13 100%) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        letter-spacing: 0.35px !important;
        padding: 10px 28px !important;
        box-shadow: 0 4px 14px rgba(118, 207, 28, 0.35) !important;
        text-transform: uppercase !important;
    }

    .view-settings-page .btn-settings-modal-assign:hover,
    .view-settings-page .btn-settings-modal-assign:focus {
        background: linear-gradient(135deg, #65b515 0%, #4d9a0f 100%) !important;
        color: #fff !important;
        box-shadow: 0 6px 18px rgba(118, 207, 28, 0.42) !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-container {
        width: 100% !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-container--default .select2-selection--multiple {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        background: #fff !important;
        min-height: 46px !important;
        padding: 4px 8px !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #76CF1C !important;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.15) !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #76CF1C !important;
        border: none !important;
        border-radius: 6px !important;
        color: #1e293b !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        padding: 3px 8px !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #1e293b !important;
        margin-right: 4px !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-dropdown {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12) !important;
    }

    .view-settings-page .view-settings-assign-modal .select2-results__option--highlighted {
        background-color: #76CF1C !important;
        color: #1e293b !important;
    }

    /* Portal for modals: stacking above table without touching document.body */
    .view-settings-modal-root {
        position: relative;
        z-index: 1060;
        min-height: 0;
    }

    /* —— Narrow / tablet–mobile (aligns with custom.css @991 stacking) —— */
    @media (max-width: 991px) {
        .view-settings-page .view-settings-title-row.bgx-title-container {
            align-items: stretch !important;
        }

        .view-settings-page .view-settings-actions-col.text-right {
            text-align: center !important;
        }

        .view-settings-page .c_title .bgx-title-container .btn-success.btn-settings-add,
        .view-settings-page .c_title .btn-settings-add.btn-success {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 48px !important;
            font-size: 14px !important;
            padding: 12px 18px !important;
        }

        .view-settings-page .settings-view-toolbar {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 14px !important;
            margin-bottom: 16px !important;
        }

        .view-settings-page .settings-category-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
            gap: 8px !important;
            padding-bottom: 6px !important;
            margin: 0 !important;
            scrollbar-width: thin;
        }

        .view-settings-page .settings-category-tabs .tablinks {
            flex-shrink: 0 !important;
            white-space: nowrap !important;
        }

        .view-settings-page .settings-export-actions {
            width: 100% !important;
            justify-content: stretch !important;
            gap: 10px !important;
        }

        .view-settings-page .settings-export-actions .btn-export {
            flex: 1 1 0 !important;
            min-height: 44px !important;
            font-size: 13px !important;
            padding: 10px 12px !important;
        }

        .view-settings-page .settings-table-wrap .dataTables_wrapper > .row:first-child > div[class*="col-"],
        .view-settings-page .settings-table-wrap .dataTables_wrapper > .row:last-child > div[class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: left !important;
            margin-bottom: 10px !important;
        }

        .view-settings-page .settings-table-wrap .dataTables_filter input {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            box-sizing: border-box !important;
        }
    }
</style>
<script>
  function viewSettingsDataTableOpts() {
    return {
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      responsive: false,
      autoWidth: true,
      scrollX: false,
      scrollCollapse: false,
      lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
      pageLength: 25,
      deferRender: true
    };
  }

  function initViewSettingsDataTable($table) {
    if (!$table.length || typeof $.fn.DataTable === 'undefined') {
      return;
    }
    var el = $table.get(0);
    if ($.fn.DataTable.isDataTable(el)) {
      $table.DataTable().columns.adjust();
      return;
    }
    $table.DataTable(viewSettingsDataTableOpts());
  }

   $(document).ready(function() {

    $('.view-settings-page .tabcontent').hide();
    var activeTabBtn = $('.view-settings-page .tablinks.active').first();
    var tabBtn = activeTabBtn.length ? activeTabBtn : $('.view-settings-page .tablinks').first();
    if (tabBtn.length) {
      tabBtn.addClass('active');
      var onclickInit = tabBtn.attr('onclick');
      if (onclickInit) {
        var tabMatchInit = onclickInit.match(/'([^']+)'/);
        if (tabMatchInit) {
          $('#' + tabMatchInit[1]).show();
        }
      }
    }

    $('.view-settings-page .settings-tab-panel:visible').find('table.example').each(function() {
      initViewSettingsDataTable($(this));
    });

    $(window).on('resize', function() {
      $('.view-settings-page table.example').each(function() {
        if ($.fn.DataTable.isDataTable(this)) {
          $(this).DataTable().columns.adjust();
        }
      });
    });

    /* Chrome: avoid "Blocked aria-hidden" when closing while focus is still inside the modal */
    $(document).on('hide.bs.modal', '.view-settings-assign-modal', function() {
      var ae = document.activeElement;
      if (ae && this.contains(ae)) {
        try { ae.blur(); } catch (err) {}
      }
    });
  });

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      $('.view-settings-page .tabcontent').hide();
      $('.view-settings-page .tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $('.view-settings-page .tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      var $tbl = $('#' + tabName).find('table.example');
      initViewSettingsDataTable($tbl);
      setTimeout(function() {
        if ($tbl.length && $.fn.DataTable.isDataTable($tbl.get(0))) {
          $tbl.DataTable().columns.adjust();
        }
      }, 0);

      return false;
  }

  function open_model(id, key) {
    var $modal = $('#modal-responsive-' + id);
    if (!$modal.length) return;
    $modal.find('input[name="test_id"]').val(id);
    var $host = $('#view-settings-modal-root');
    if (!$host.length) {
      $host = $('body');
    }
    if (!$modal.parent().is($host)) {
      $modal.appendTo($host);
    }
    $modal.off('shown.bs.modal.vsSelect2').one('shown.bs.modal.vsSelect2', function() {
      var $sel = $(this).find('.selectDevice');
      if (!$sel.length || $sel.data('select2')) return;
      try {
        $sel.select2({ width: 'resolve', placeholder: 'Search and select devices' });
      } catch (e) {
        try { $sel.select2(); } catch (e2) { /* ignore */ }
      }
    });
    $modal.modal('show');
  }
</script>
@stop









