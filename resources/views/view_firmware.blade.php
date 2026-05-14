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
  #main-content.view-firmware-page .wrapper { padding-top: 8px !important; }
  .view-firmware-page .fw-breadcrumb-wrap { padding: 4px 0 10px 0; margin: 0; }
  .view-firmware-page .fw-breadcrumb {
    display: inline-flex; align-items: center; flex-wrap: wrap; row-gap: 6px;
    background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
  }
  .view-firmware-page .fw-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center; margin-right: 10px;
    flex-shrink: 0; color: #1e293b; text-decoration: none;
  }
  .view-firmware-page .fw-breadcrumb .bc-home i { font-size: 13px; }
  .view-firmware-page .fw-breadcrumb .bc-item {
    color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;
  }
  .view-firmware-page .fw-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
  .view-firmware-page .fw-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
  .view-firmware-page .fw-breadcrumb a.bc-item:hover { color: #e2e8f0; }

  .view-firmware-page .fw-breadcrumb.fw-breadcrumb--scroll {
    max-width: 100%;
    flex-wrap: nowrap !important;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }

  .view-firmware-page .fw-breadcrumb.fw-breadcrumb--scroll .bc-home {
    flex-shrink: 0;
  }

  .view-firmware-page .fw-breadcrumb-wrap + .row { margin-top: 2px; }
  .view-firmware-page .c_panel { margin-top: 0 !important; overflow: visible !important; }
  .view-firmware-page .c_title h2::before { content: none !important; display: none !important; }
  .view-firmware-page .fw-panel-title {
    display: inline-flex !important; align-items: center; gap: 8px; margin: 0;
    color: #fff !important; font-size: 15px !important; font-weight: 800 !important;
    letter-spacing: .5px; text-transform: uppercase;
  }
  .view-firmware-page .fw-panel-title > i { color: #76CF1C; font-size: 14px; }
  .view-firmware-page .fw-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0 12px; }
  .view-firmware-page .fw-tabs .tablinks {
    border: 1px solid #cbd5e1; background: #f8fafc; color: #334155;
    padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px;
  }
  .view-firmware-page .fw-tabs .tablinks.active { background: #76CF1C !important; color: #fff !important; border-color: #76CF1C !important; }
  .view-firmware-page .fw-tab-actions { text-align: right; margin: 0 0 10px; }
  .view-firmware-page .fw-tab-actions .btn {
    border-radius: 8px; font-weight: 700; font-size: 12px; min-height: 34px;
    display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px !important;
  }
  .view-firmware-page .fw-tab-actions .btn-dl-excel { background: #1d283e !important; border: none !important; color: #fff !important; }
  .view-firmware-page .fw-tab-actions .btn-dl-csv { background: #76CF1C !important; border: none !important; color: #fff !important; }
  .view-firmware-page .fw-title-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .view-firmware-page .fw-title-actions .btn {
    min-height: 34px;
    border-radius: 8px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px !important;
    border: none !important;
  }
  .view-firmware-page .fw-title-actions .btn-dl-excel {
    background: #76CF1C !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
  }
  .view-firmware-page .fw-title-actions .btn-dl-csv {
    background: #ffffff !important;
    color: #1e293b !important;
    border: 1px solid #dbe4ef !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
  }
  .view-firmware-page .fw-title-actions .btn-add-firmware {
    background: #76CF1C !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
  }

  /* Modals — header matches datatable / panel navy theme */
  .view-firmware-page .modal .modal-content .fw-panel-modal-header {
    background: linear-gradient(180deg, #132035 0%, #1f314f 100%);
    color: #dbe9ff;
    border-bottom: 1px solid rgba(148, 163, 184, 0.28);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-direction: row-reverse;
  }
  .view-firmware-page .modal .modal-content .fw-panel-modal-header .modal-title {
    color: #dbe9ff !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin: 0;
    flex: 1;
    min-width: 0;
    text-align: left;
  }
  .view-firmware-page .modal .modal-content .fw-panel-modal-header .close {
    color: #dbe9ff !important;
    opacity: 0.92;
    text-shadow: none;
    margin: 0;
    padding: 4px 8px;
    font-size: 22px;
    line-height: 1;
    flex-shrink: 0;
  }
  .view-firmware-page .modal .modal-content .fw-panel-modal-header .close:hover,
  .view-firmware-page .modal .modal-content .fw-panel-modal-header .close:focus {
    opacity: 1;
    color: #ffffff !important;
  }

  /* overflow visible — horizontal scroll lives in DataTables .dataTables_scrollBody (thead/body stay aligned) */
  .view-firmware-page .fw-table-wrap {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    overflow: visible;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    margin-top: 2px;
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
  }
  .view-firmware-page .dataTables_wrapper {
    width: 100% !important;
    max-width: 100%;
    box-sizing: border-box;
  }
  .view-firmware-page .dataTables_wrapper > .row { margin-left: 0 !important; margin-right: 0 !important; }
  .view-firmware-page .dataTables_wrapper .row:first-child { padding: 10px 0 12px; background: #f8fbff; border-bottom: 1px solid #e2e8f0; }
  .view-firmware-page .dataTables_wrapper .row:first-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
  .view-firmware-page .dataTables_wrapper > .row:nth-child(2) { overflow: visible !important; }
  .view-firmware-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] { min-width: 0; padding-left: 0; padding-right: 0; }

  .view-firmware-page .dataTables_scroll { clear: both; width: 100% !important; }
  .view-firmware-page .dataTables_scrollHead { overflow: hidden !important; }
  .view-firmware-page .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: auto !important;
    scrollbar-width: thin;
    -webkit-overflow-scrolling: touch;
  }
  .view-firmware-page .dataTables_scrollHead table.dataTable.firmware-datatable,
  .view-firmware-page .dataTables_scrollBody table.dataTable.firmware-datatable {
    table-layout: auto !important;
    margin: 0 !important;
  }

  .view-firmware-page .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar {
    height: 10px;
    width: 10px;
  }
  .view-firmware-page .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 999px;
  }
  .view-firmware-page .dataTables_wrapper .dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 999px;
  }
  .view-firmware-page .dataTables_wrapper > .row:last-child { padding: 10px 0 8px !important; border-top: 1px solid #e8ecf1; background: #f8fbff; }
  /* Better for many columns: avoid squeeze, allow horizontal scroll */
  .view-firmware-page table.dataTable.firmware-datatable {
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    width: 100% !important;
    min-width: 1780px;
  }
  .view-firmware-page .firmware-datatable thead th,
  .view-firmware-page .firmware-datatable tbody td {
    box-sizing: border-box;
    white-space: nowrap;
  }
  .view-firmware-page .firmware-datatable thead th {
    background: linear-gradient(180deg, #132035 0%, #1f314f 100%) !important;
    color: #dbe9ff !important;
    font-weight: 700 !important;
    font-size: 11px !important;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: none !important;
    padding: 13px 10px !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
  }
  .view-firmware-page .firmware-datatable tbody td {
    font-size: 13px;
    color: #243447;
    padding: 13px 10px !important;
    border-top: 1px solid #edf2f8 !important;
    vertical-align: middle !important;
    line-height: 1.35;
  }
  .view-firmware-page .firmware-datatable tbody tr:nth-child(even) td { background: #fbfdff !important; }
  .view-firmware-page .firmware-datatable tbody tr:hover td { background: #f2f8ff !important; }
  .view-firmware-page .firmware-datatable thead th.sorting,
  .view-firmware-page .firmware-datatable thead th.sorting_asc,
  .view-firmware-page .firmware-datatable thead th.sorting_desc {
    padding-right: 24px !important;
  }
  .view-firmware-page .firmware-datatable thead th:first-child,
  .view-firmware-page .firmware-datatable tbody td:first-child {
    text-align: center;
    width: 86px;
    min-width: 86px;
    white-space: nowrap !important;
  }
  .view-firmware-page .firmware-datatable thead th:first-child.sorting,
  .view-firmware-page .firmware-datatable thead th:first-child.sorting_asc,
  .view-firmware-page .firmware-datatable thead th:first-child.sorting_desc {
    padding-right: 22px !important;
  }
  .view-firmware-page .firmware-datatable tbody td .btn {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }
  .view-firmware-page .firmware-datatable tbody td .fw-chip {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid #dbe4ef;
    background: #ffffff;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    max-width: 100%;
  }
  .view-firmware-page .firmware-datatable tbody td .fw-file-chip {
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .view-firmware-page .firmware-datatable tbody td .fw-name-cell {
    font-weight: 700;
    color: #10243f;
  }
  .view-firmware-page .firmware-datatable tbody td .fw-date-cell {
    color: #425b76;
    font-size: 12px;
    font-weight: 600;
  }
  .view-firmware-page .firmware-datatable tbody td .fw-id-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    min-height: 28px;
    padding: 2px 10px;
    border-radius: 999px;
    background: #ecf3ff;
    color: #153865;
    border: 1px solid #d6e5fa;
    font-size: 12px;
    font-weight: 800;
  }
  .view-firmware-page .fw-action-btn {
    min-height: 34px;
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    padding: 7px 12px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }
  .view-firmware-page .fw-action-btn.fw-btn-model { background: #1d283e !important; border: none !important; color: #fff !important; }
  .view-firmware-page .fw-action-btn.fw-btn-edit { background: #1d283e !important; border: none !important; color: #fff !important; }
  .view-firmware-page .fw-action-btn.fw-btn-delete { background: #e25552 !important; border: none !important; color: #fff !important; }
  .view-firmware-page .fw-default-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    min-width: 46px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #f4b64f;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
  }
  .view-firmware-page .fw-model-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    min-height: 28px;
    padding: 2px 8px;
    border-radius: 999px;
    border: 1px solid #dbe4ef;
    background: #fff;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
  }
  .view-firmware-page .fw-actions-cluster {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: nowrap;
  }
  .view-firmware-page .fw-actions-cluster form { margin: 0; }
  .view-firmware-page .firmware-datatable tbody td:nth-child(8),
  .view-firmware-page .firmware-datatable tbody td:nth-child(14),
  .view-firmware-page .firmware-datatable tbody td:nth-child(15) {
    white-space: normal !important;
    word-break: break-word;
  }

  @media (max-width: 991px) {
    .view-firmware-page .fw-breadcrumb-wrap {
      padding: 2px 0 6px 0;
    }

    .view-firmware-page .c_content {
      padding-left: 12px !important;
      padding-right: 12px !important;
    }

    .view-firmware-page .c_panel .c_title .bgx-title-container > .fw-title-actions-wrap {
      text-align: center !important;
    }

    .view-firmware-page .fw-page-title-row.bgx-title-container {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 12px;
    }

    .view-firmware-page .fw-title-actions-wrap {
      text-align: center !important;
    }

    .view-firmware-page .fw-title-actions {
      flex-direction: column !important;
      align-items: stretch !important;
      width: 100% !important;
      justify-content: stretch !important;
      gap: 10px !important;
    }

    .view-firmware-page .fw-title-actions .btn {
      width: 100% !important;
      max-width: 100% !important;
      min-height: 44px !important;
      justify-content: center !important;
      font-size: 13px !important;
    }

    .view-firmware-page .fw-tabs {
      flex-wrap: nowrap !important;
      overflow-x: auto !important;
      overflow-y: visible !important;
      -webkit-overflow-scrolling: touch;
      gap: 8px !important;
      padding-bottom: 6px !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      scrollbar-width: thin;
    }

    .view-firmware-page .fw-tabs .tablinks {
      flex-shrink: 0 !important;
      white-space: nowrap !important;
    }

    .view-firmware-page .dataTables_wrapper .row:first-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: left !important;
      margin-bottom: 10px !important;
    }

    .view-firmware-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }

    .view-firmware-page .dataTables_wrapper .dataTables_filter {
      width: 100% !important;
    }

    .view-firmware-page .dataTables_wrapper .dataTables_filter input {
      width: 100% !important;
      max-width: 100% !important;
      margin-left: 0 !important;
      box-sizing: border-box !important;
    }

    .view-firmware-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      text-align: center !important;
      margin-bottom: 8px !important;
    }

    .view-firmware-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
      margin-bottom: 0 !important;
    }

    .view-firmware-page .fw-table-wrap {
      overflow-x: visible !important;
      overflow-y: visible !important;
    }
  }
</style>
<section id="main-content" class="view-firmware-page">
  <section class="wrapper">
    <div class="fw-breadcrumb-wrap">
      <nav class="fw-breadcrumb fw-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Firmware Management</span>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View Firmware</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container fw-page-title-row">
              <div class="col-xs-12 col-lg-6">
                <h2 class="fw-panel-title"><i class="fa fa-list-alt"></i> View Firmware</h2>
              </div>
              <div class="col-xs-12 col-lg-6 text-right fw-title-actions-wrap">
                <div class="fw-title-actions">
                  @if(Auth::user()->user_type == "Admin")
                  <a href="{{ route('firmware.excel') }}" class="btn btn-dl-excel"><i class="fa fa-file-excel-o"></i>Download Excel</a>
                  <a href="{{ route('firmware.csv') }}" class="btn btn-dl-csv"><i class="fa fa-file-text-o"></i>Download CSV</a>
                  @endif
                  <a href="/{{$url_type}}/add-firmware" class="btn btn-add-firmware"><i class="fa fa-plus"></i>Add FirmWare</a>
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
            <div class="tabs fw-tabs">
              @foreach ($getDeviceCategory as $key => $category)
              @if( Session::get('device_category_id'))
              <button class="tablinks {{Session::get('device_category_id') == $category->id ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')">
                {{ $category->device_category_name }}
              </button>
              @else
              <button class="tablinks {{ $key==0 ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')">
                {{ $category->device_category_name }}
              </button>
              @endif
              @endforeach


            </div>
            
            @foreach ($getDeviceCategory as $category)
            @php
            $templateInfo = CommonHelper::getTemplatesInfo($category->id);
            $users = CommonHelper::getUsersByDeviceCategory($category->id);

            @endphp
            <?php
              $i = 1;

              ?>
            <div id="tab{{ $category->id }}" class="tabcontent">
              <div class="fw-table-wrap">
              <table id="firmware{{ $category->id }}" class="firmwareData table cf firmware-datatable" style="border-spacing: 0; width: 100%; font-size: 13px;">
                <thead>
                  <tr>
                    <th style="min-width: 60px;">Sr. No.</th>
                    <th style="min-width: 60px;">U ID</th>
                    <th style="min-width: 150px;">Firmware Name</th>
                    <th style="min-width: 100px;">Country</th>
                    <th style="min-width: 100px;">State</th>
                    <th style="min-width: 60px;">ESIM</th>
                    <th style="min-width: 80px;">Backend</th>
                    <th style="min-width: 180px;">Firmware File</th>
                    <th style="min-width: 100px;">Firmware File Size</th>
                    <th style="min-width: 80px;">Version</th>
                    <th style="min-width: 120px;">Add Firmware</th>
                    <th style="min-width: 100px;">Default Firmware</th>
                    <th style="min-width: 100px;">No of Models</th>
                    <th style="min-width: 150px;">Created at</th>
                    <th style="min-width: 150px;">Last Edit</th>
                    <th style="min-width: 160px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($firmwares as $firmware)
                  @if($firmware->device_category_id == $category->id)
                  @php $config = json_decode($firmware->configurations); @endphp
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td><span class="fw-id-pill">{{$firmware->id}}</span></td>
                    <td><span class="fw-name-cell">{{$firmware->name}}</span></td>
                    <?php /* <td>{{CommonHelper::getCountryName($config->country)}}</td> <?php */ ?>
                    <td>{{ isset($config->country) ? CommonHelper::getCountryName($config->country) : '-' }}</td>
                    <?php /* <td>{{CommonHelper::getStateName($config->state)}} </td> <?php */ ?>
                    <td>{{ isset($config->state) ? CommonHelper::getStateName($config->state) : '-' }}</td>
                    <?php /* <td>{{ $category->is_esim == 1 ? CommonHelper::getEsim($config->esim) : $config->esim }}</td> */ ?>
                    <td>
                      {{ isset($config->esim) 
                          ? ($category->is_esim == 1 
                              ? CommonHelper::getEsim($config->esim) 
                              : $config->esim) 
                          : '-' 
                      }}
                    </td>
                    <?php /* <td>{{CommonHelper::getBackend($config->backend)}}</td> */ ?>
                    <td>{{ isset($config->backend) ? CommonHelper::getBackend($config->backend) : '-' }}</td>
                    <?php /*
                    <td>{{$config->filename??0}}</td>
                    <td>{{$config->fileSize?? 0}}</td>
                    <td>{{$config->version}}</td>  */ ?>

                    <td><span class="fw-chip fw-file-chip">{{ $config->filename ?? '-' }}</span></td>
                    <td><span class="fw-chip">{{ $config->fileSize ?? '-' }}</span></td>
                    <td><span class="fw-chip">{{ $config->version ?? '-' }}</span></td>

                    <td>
                       <a href="/admin/view-firmware-models/{{$firmware->id}}" class="btn fw-action-btn fw-btn-model"><i class="fa fa-eye"></i>View Model</a>
                    </td>
                    <td class="text-center">
                      @if($firmware->is_default == 1)
                        <span class="fw-default-pill">Yes</span>
                      @endif
                    </td>
                    <td class="text-center">
                        <span class="fw-model-count">{{$firmware->model_count}}</span>
                    </td>
                    <td><span class="fw-date-cell">{{CommonHelper::getDateAsTimeZone($firmware->created_at)}}</span></td>
                    <td><span class="fw-date-cell">{{CommonHelper::getDateAsTimeZone($firmware->updated_at)}}</span></td>
                    <td class="text-center">
                      <div class="fw-actions-cluster">
                        <button type="button" class="btn fw-action-btn fw-btn-edit" onclick="openEditModel({{$firmware->id}})"><i class="fa fa-pencil"></i>Edit</button>
                        <form id="deleteForm-{{$firmware->id}}" action="" method="post">
                          @csrf
                          @method('DELETE')
                          <button type="button" class="btn fw-action-btn fw-btn-delete" onclick="showDeleteModal({{$firmware->id}})">
                            <i class="fa fa-trash"></i>Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  <div class="modal" id="deleteFirmwareModal{{$firmware->id}}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header fw-panel-modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                          <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        </div>
                        <div class="modal-body">
                          Are you sure you want to delete this firmware from All Devices ?
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                          <button type="button" class="btn btn-warning" onclick="confirmDelete({{$firmware->id}},false)">No</button>
                          <button type="button" class="btn btn-danger" onclick="confirmDelete({{$firmware->id}},true)">Yes</button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal" id="addModel{{$firmware->id}}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header fw-panel-modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                          <h5 class="modal-title" id="addModellLabel">Add Model</h5>
                        </div>
                        <form id="addModalForm" onsubmit="return false" method="post">
                          @csrf
                          <div class="modal-body">
                            <!-- Form to Add eSIM -->
                            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                            <div class="margin-bottom-10">
                              <label for="modalname" class="form-label col-12">Model Name</label>
                              <input type="text" class="form-control" id="modalName" name="modalName" required>
                            </div>
                            <div class="margin-bottom-10">
                              <label for="vendorId" class="form-label col-12">Vendor Id</label>
                              <input type="text" class="form-control" id="vendorId" name="vendorId" required>
                            </div>
                            <div class="margin-bottom-10">
                              <label for="userAssign" class="form-label col-12">Assign Account</label>
                              <select id="userAssign" name="userAssign" class="form-control" class="userAssign" onChange="getModelById({{$firmware->id}})">
                                  <option value="">Please Select</option>
                                @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
                            <button type="submit" class="btn btn-primary addModalFormBtn">Submit</button>
                            <input type="hidden" name="modalId" id="modalId" value =""/>
                            <input type="hidden" name="firmwareId" id="firmwareId{{$firmware->id}}" value="" />
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div class="modal" id="editFirmware{{$firmware->id}}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header fw-panel-modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                          <h5 class="modal-title" id="editFirmwareModalLabel">Edit Firmware</h5>
                        </div>
                        <form id="editFirmwareForm" onsubmit="return false" method="post">
                          @csrf
                          <div class="modal-body">
                            <!-- Form to Add eSIM -->
                            <div class="col-sm-12 alert alert-danger error_msg_firmware" role="alert" style="display:none"></div>
                            <div class="margin-bottom-10">
                              <label for="modalname" class="form-label col-12">Firmware File</label>
                              @if(isset($config->filename))
                              <!-- Display the existing file -->
                              <div>
                                <p>Current file: <a href="{{ asset('fw/' . $config->filename) }}" target="_blank">{{ basename($config->filename) }}</a></p>
                              </div>
                              @endif
                              <input type="file" name="firmwareFile" id="firmwareFile" accept=".bin" class="reqfield" required/>
                            </div>
                            <div class="margin-bottom-10">
                              <label for="userAssign" class="form-label col-12">Firmware Version</label>
                              <input class="form-control " type="text" placeholder="Firmware version" name="firmware_version" value="{{ $config->version ?? '-' }}" required />
                            </div>
                            <div class="margin-bottom-10">
                                 <label for="releasingNotes" class="form-label">Releasing Notes</label>
                                 <div>
                                    <textarea class="form-control " id="releasingNotes" name="releasingNotes" rows="6" cols="63">
                                      {{isset($config->releasingNotes)? $config->releasingNotes :''}}
                                    </textarea>
                                 </div>
                             </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
                            <button type="submit" class="btn btn-primary editFirmwareFormBtn">Update</button>
                            <input type="hidden" name="firmwareIdEdit" id="firmwareIdEdit{{$firmware->id}}" value="" />
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <?php $i++; ?>
                  @endif
                  @endforeach
                 
                </tbody>
              </table>
              </div>
            </div>
            @endforeach

          </div>

        </div><!--/.c_content-->
      </div><!--/.c_panels-->
    </div><!--/col-md-12-->
    </div><!--/row-->

    <!--======= Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop
@section('scripts')
<script>
  function showDeleteModal(id) {
    $('#deleteFirmwareModal' + id).modal('show');
  }
  function confirmDelete(id,response) {
      
    const form = document.getElementById('deleteForm-' + id);
    form.action = `/{{$url_type}}/delete-firmware/${id}/${response}`;
    form.submit();
  }

  function getFirmwareDataTableOptions() {
    return {
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      autoWidth: false,
      responsive: false,
      pageLength: 25,
      scrollX: true,
      scrollCollapse: false,
      "aLengthMenu": [
        [25, 50, 100, 500, -1],
        [25, 50, 100, 500, "All"]
      ],
      "iDisplayLength": 25,
      columnDefs: [
        { targets: 0, searchable: false }
      ],
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
      initComplete: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      },
      drawCallback: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      }
    };
  }

  /**
   * Lazy-init DataTables only when a category tab is visible. Initializing scrollX while the panel
   * is display:none collapses the scroll body — rows stay invisible until tab switch (filter bug).
   */
  function ensureFirmwareDataTable($table) {
    if (!$table || !$table.length || !$.fn.DataTable) {
      return null;
    }
    var elementId = $table.attr('id');
    if (!elementId) {
      return null;
    }

    $table.find('tbody > .modal').each(function () {
      $('body').append(this);
    });

    if ($.fn.DataTable.isDataTable('#' + elementId)) {
      var dtExisting = $table.DataTable();
      try { dtExisting.columns.adjust(); } catch (e) { /* noop */ }
      try { dtExisting.draw(false); } catch (e) { /* noop */ }
      return dtExisting;
    }

    var dt = $table.DataTable(getFirmwareDataTableOptions());
    try { dt.columns.adjust().draw(false); } catch (e) { /* noop */ }
    return dt;
  }

  $(document).ready(function () {
    var $page = $('#main-content.view-firmware-page');

    $page.find('.tabcontent').hide();

    var $activeBtn = $page.find('.tablinks.active').first();
    if (!$activeBtn.length) {
      $activeBtn = $page.find('.tablinks').first();
    }
    $page.find('.tablinks').removeClass('active');
    if ($activeBtn.length) {
      $activeBtn.addClass('active');
    }

    var tabId = null;
    if ($activeBtn.length) {
      var oc = $activeBtn.attr('onclick') || '';
      var tm = oc.match(/,\s*'([^']+)'\s*\)/);
      if (tm) {
        tabId = tm[1];
      }
    }

    if (tabId && $('#' + tabId).length) {
      $('#' + tabId).show();
      ensureFirmwareDataTable($('#' + tabId).find('table.firmwareData'));
    } else {
      $page.find('.tabcontent:first').show();
      ensureFirmwareDataTable($page.find('.tabcontent:visible').find('table.firmwareData'));
    }

    $('#loading').hide();

    function adjustFirmwareDataTables() {
      $page.find('.firmwareData').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
          try { $(this).DataTable().columns.adjust(); } catch (e) { /* noop */ }
        }
      });
    }
    $(window).on('resize orientationchange', function () {
      adjustFirmwareDataTables();
    });
  });


  function openModel(id) {
    $('.error_msg').hide().text();
    $('#firmwareId' + id).val(id);
    $('#addModel' + id).modal('show');

  }
  
  function openEditModel(id) {
    $('.error_msg_firmware').hide().text();
    $('#firmwareIdEdit' + id).val(id);
    $('#editFirmware' + id).modal('show');
  }
  $(document).ready(function() {
    $('.editFirmwareFormBtn').click(function() {
      var $modal = $(this).closest('.modal');
      var $form = $modal.find('form');
      let isValid = true;

      $form.find('[required]').each(function() {
        var value = $(this).val();

        // Handle null, array, undefined safely
        if (value === null || value === undefined || value === '' ||
          (Array.isArray(value) && value.length === 0)) {
          isValid = false;
          return false; // break loop
        }
      });

      if (!isValid) {
        e.preventDefault(); // stop action
          return;
      }
      var $errorMsg = $modal.find('.error_msg_firmware');
      var formData = new FormData($form[0]);
      $.ajax({
        url: '/admin/edit-firmware',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          let result = JSON.parse(response);
          if (result.status == 200) {
            alert(result.status_msg);
            $modal.modal('hide');
            window.location.reload();
          } else {
            isValid = false;
            let errorMessage = result.message;
            $errorMsg.show();
            $errorMsg.html(errorMessage);
            // resolve(isValid);
          }
        },
        error: function(xhr, status, error) {
           let errorMessage =xhr.responseJSON.message;
            $errorMsg.show();
            $errorMsg.html(errorMessage);
          // reject(error);
        }
      });

    });
  
  });

  function open_model(id, key) {
    $("#test_id").val(id);
    $("#modal-responsive-" + id).modal();
  };
  $(document).ready(function() {
    $('.selectDevice').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');
      console.log("dsjdssd", id);
      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });
  })

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      var $page = $('#main-content.view-firmware-page');
      $page.find('.tabcontent').hide();
      $page.find('.tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $page.find('.tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      setTimeout(function () {
          var $tbl = $('#' + tabName).find('table.firmwareData');
          $tbl.each(function () {
              ensureFirmwareDataTable($(this));
          });
      }, 50);

      return false;
  }
</script>
@endsection

<!-- nav-collapse md-box-shadowed hide-left-bar show-left-bar-mobile -->









