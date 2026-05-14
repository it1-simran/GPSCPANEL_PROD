@extends('layouts.apps')
@section('content')
<?php

use Illuminate\Support\Facades\Auth;
use App\Helper\CommonHelper;

$idsArray = [1];

$currentEmail = Auth::user()->email;
?>
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  /* ─── Toast Notification ─── */
  .gps-toast-container {
    position: fixed; top: 24px; right: 24px; z-index: 99999;
    display: flex; flex-direction: column; gap: 10px;
    pointer-events: none;
  }
  .gps-toast {
    pointer-events: auto;
    display: flex; align-items: center; gap: 12px;
    min-width: 300px; max-width: 420px;
    padding: 14px 20px; border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 0 0 1px rgba(255,255,255,0.06);
    transform: translateX(120%); opacity: 0;
    animation: gpsToastIn 0.4s cubic-bezier(0.34,1.56,0.64,1) forwards;
    font-family: inherit;
  }
  .gps-toast.removing {
    animation: gpsToastOut 0.3s ease forwards;
  }
  @keyframes gpsToastIn {
    to { transform: translateX(0); opacity: 1; }
  }
  @keyframes gpsToastOut {
    to { transform: translateX(120%); opacity: 0; }
  }
  .gps-toast-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
  }
  .gps-toast-body { flex: 1; }
  .gps-toast-title {
    font-size: 13px; font-weight: 800; margin: 0 0 2px;
    letter-spacing: 0.2px;
  }
  .gps-toast-msg {
    font-size: 12.5px; font-weight: 500; margin: 0;
    opacity: 0.85; line-height: 1.4;
  }
  .gps-toast-close {
    background: none; border: none; font-size: 16px;
    cursor: pointer; opacity: 0.5; transition: opacity 0.2s;
    padding: 0; line-height: 1; flex-shrink: 0;
  }
  .gps-toast-close:hover { opacity: 1; }
  .gps-toast-progress {
    position: absolute; bottom: 0; left: 0; height: 3px;
    border-radius: 0 0 12px 12px;
    animation: gpsToastProgress 3s linear forwards;
  }
  @keyframes gpsToastProgress {
    from { width: 100%; }
    to { width: 0%; }
  }
  /* Warning variant */
  .gps-toast.toast-warning {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #fff; position: relative; overflow: hidden;
  }
  .gps-toast.toast-warning .gps-toast-icon {
    background: rgba(251,191,36,0.15); color: #fbbf24;
  }
  .gps-toast.toast-warning .gps-toast-close { color: rgba(255,255,255,0.5); }
  .gps-toast.toast-warning .gps-toast-progress { background: #fbbf24; }
  /* Success variant */
  .gps-toast.toast-success {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #fff; position: relative; overflow: hidden;
  }
  .gps-toast.toast-success .gps-toast-icon {
    background: rgba(118,207,28,0.15); color: #76CF1C;
  }
  .gps-toast.toast-success .gps-toast-close { color: rgba(255,255,255,0.5); }
  .gps-toast.toast-success .gps-toast-progress { background: #76CF1C; }
  /* Error variant */
  .gps-toast.toast-error {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #fff; position: relative; overflow: hidden;
  }
  .gps-toast.toast-error .gps-toast-icon {
    background: rgba(239,68,68,0.15); color: #ef4444;
  }
  .gps-toast.toast-error .gps-toast-close { color: rgba(255,255,255,0.5); }
  .gps-toast.toast-error .gps-toast-progress { background: #ef4444; }

  #main-content .wrapper { padding-top: 10px !important; }

  /* Breadcrumb (reference style) */
  .vd-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .vd-breadcrumb {
    display: inline-flex;
    align-items: center;
    background: #1e293b;
    border-radius: 50px;
    padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
    gap: 0;
  }
  .vd-breadcrumb .bc-home {
    width: 30px; height: 30px; border-radius: 50%;
    background: #76CF1C; color: #1e293b; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
  }
  .vd-breadcrumb .bc-item {
    color: rgba(255,255,255,0.65);
    font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;
  }
  .vd-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
  .vd-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

  #main-content .c_panel {
    border: 0 !important;
    border-radius: 12px !important;
    box-shadow: 0 6px 24px rgba(15,23,42,0.08) !important;
    overflow: hidden;
  }
  #main-content .c_title {
    background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%) !important;
    padding: 16px 22px !important;
    border-bottom: 1px solid rgba(118,207,28,0.2) !important;
    margin-bottom: 0 !important;
  }
  #main-content .c_title h2 {
    margin: 0 !important;
    color: #fff !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    text-align: left !important;
    float: none !important;
  }
  #main-content .c_content.tabs { padding: 16px 16px 10px !important; }

  /* Tabs + actions (approval-request reference style) */
  #main-content .tabs .tablinks {
    border: 1px solid #e2e8f0 !important;
    background: #f8fafc !important;
    color: #64748b !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    margin-right: 4px !important;
    margin-bottom: 10px !important;
    transition: all 0.2s ease !important;
  }
  #main-content .tabs .tablinks.active {
    background: #76CF1C !important;
    border-color: #76CF1C !important;
    color: #1e293b !important;
    box-shadow: 0 2px 8px rgba(118,207,28,0.25) !important;
  }
  #main-content .tabs .tablinks:hover {
    border-color: #cbd5e1 !important;
    background: #f1f5f9 !important;
  }

  #main-content .tabs .tabcontent > div[style*="margin-bottom:15px"] {
    margin-bottom: 12px !important;
    display: flex;
    flex-wrap: wrap;
    gap: 8px 10px;
    align-items: center;
  }
  #main-content .tabs .tabcontent .vdc-tab-toolbar {
    margin-bottom: 12px !important;
  }
  #main-content .tabs .vdc-tab-toolbar .form-control.input-sm {
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
  }
  /* Keep Account dropdown + Filter button on one row (Bootstrap .form-control is width:100% by default) */
  #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form {
    flex-wrap: nowrap !important;
    align-items: center !important;
  }
  #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form select.form-control {
    width: auto !important;
    max-width: min(280px, 42vw);
    min-width: 160px;
    display: inline-block;
    vertical-align: middle;
  }
  #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form .btn {
    flex-shrink: 0;
    align-self: center;
    white-space: nowrap;
  }
  #main-content .tabs .delete_all,
  #main-content .tabs .user-responsive,
  #main-content .tabs .template-responsive {
    margin-left: 0 !important;
    border: none !important;
    border-radius: 7px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 7px 14px !important;
    box-shadow: 0 2px 8px rgba(15,23,42,0.14) !important;
  }
  #main-content .tabs .delete_all { background: linear-gradient(135deg,#ef4444,#dc2626) !important; }
  #main-content .tabs .user-responsive { background: linear-gradient(135deg,#1e293b,#2d3f55) !important; }
  #main-content .tabs .template-responsive { background: linear-gradient(135deg,#1e293b,#2d3f55) !important; }

  /* Datatable look — defer to global design system in custom.css,
     only add page-specific overrides here */
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 {
    margin-bottom: 10px !important;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn {
    margin: 0 0 0 8px !important;
    border-radius: 7px !important;
    padding: 7px 14px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(15,23,42,0.14) !important;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn.btn-success:first-child {
    background: linear-gradient(135deg,#1e293b,#2d3f55) !important;
    color: #fff !important;
  }
  #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn.btn-success:last-child {
    background: linear-gradient(135deg,#76CF1C,#67bb19) !important;
    color: #0f172a !important;
  }

  /* DataTables scroll wrapper — let the global design system handle header/body styles,
     just ensure the scroll containers don't break the layout */
  #main-content .tabs .dataTables_wrapper .dataTables_scroll {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_scrollHead {
    background: #1e293b !important;
    border-bottom: none !important;
  }
  #main-content .tabs .dataTables_wrapper .dataTables_scrollBody {
    border-top: none !important;
  }

  /* Checkbox column sizing */
  #main-content .tabs table.example thead th:first-child {
    min-width: 110px !important;
    white-space: nowrap !important;
  }
  #main-content .tabs table.example thead th:first-child input[type="checkbox"] {
    width: 15px;
    height: 15px;
    margin-left: 6px;
    vertical-align: middle;
    position: relative;
    top: -1px;
    cursor: pointer;
  }
  #main-content .tabs table.example tbody td:first-child input[type="checkbox"] {
    width: 15px;
    height: 15px;
    vertical-align: middle;
    cursor: pointer;
  }

  /* Action buttons inside table cells — compact row actions */
  #main-content .tabs table.example .btn,
  #main-content .tabs table.example a.btn {
    padding: 5px 10px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    line-height: 1.35 !important;
    min-height: 0 !important;
    vertical-align: middle;
  }
  #main-content .tabs table.example .btn i,
  #main-content .tabs table.example a.btn i {
    font-size: 11px !important;
    margin-right: 4px !important;
  }
  #main-content .tabs table.example .btn-carrot {
    background: linear-gradient(135deg, #f97316, #ea580c) !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 5px 10px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #fff !important;
    box-shadow: 0 2px 5px rgba(249,115,22,0.22) !important;
  }
  #main-content .tabs table.example .btn-carrot a {
    color: #fff !important;
    text-decoration: none !important;
  }
  #main-content .tabs table.example .btn-info,
  #main-content .tabs table.example a.btn-info {
    background: linear-gradient(135deg, #1e293b, #334155) !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 5px 10px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #fff !important;
    box-shadow: 0 2px 5px rgba(30,41,59,0.18) !important;
  }
  #main-content .tabs table.example .btn-success {
    box-shadow: 0 1px 4px rgba(22, 101, 52, 0.2) !important;
  }
  #main-content .tabs table.example .btn-danger {
    box-shadow: 0 1px 4px rgba(185, 28, 28, 0.2) !important;
  }

  /* Assign Account/Template modals */
  .modal[id^="user-responsive"],
  .modal[id^="template-responsive"] {
    background: rgba(15, 23, 42, 0.45);
  }
  .modal[id^="user-responsive"] .modal-dialog,
  .modal[id^="template-responsive"] .modal-dialog {
    margin-top: 90px;
  }
  .modal[id^="user-responsive"] .modal-content,
  .modal[id^="template-responsive"] .modal-content {
    border: 0 !important;
    border-radius: 12px !important;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(2, 8, 23, 0.32);
  }
  .modal[id^="user-responsive"] .modal-header,
  .modal[id^="template-responsive"] .modal-header {
    background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%);
    border-bottom: 1px solid rgba(118, 207, 28, 0.2);
    padding: 14px 18px;
  }
  .modal[id^="user-responsive"] .modal-header .modal-title,
  .modal[id^="template-responsive"] .modal-header .modal-title {
    color: #ffffff !important;
    font-size: 16px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .2px;
  }
  .modal[id^="user-responsive"] .modal-header .close,
  .modal[id^="template-responsive"] .modal-header .close {
    color: #cbd5e1 !important;
    opacity: 0.9;
    text-shadow: none;
    margin-top: 4px !important;
    position: relative;
    top: 2px;
  }
  .modal[id^="user-responsive"] .modal-body,
  .modal[id^="template-responsive"] .modal-body {
    padding: 16px 18px 8px;
    background: #f8fafc;
  }
  .modal[id^="user-responsive"] .form-label,
  .modal[id^="template-responsive"] .form-label {
    display: block;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: .35px;
  }
  .modal[id^="user-responsive"] select.form-control,
  .modal[id^="template-responsive"] select.form-control {
    min-height: 38px;
    border: 1px solid #d5deea !important;
    border-radius: 8px !important;
    color: #1e293b !important;
    background: #ffffff !important;
    box-shadow: none !important;
  }
  .modal[id^="user-responsive"] .text-center[style*="font-size: 14px"],
  .modal[id^="template-responsive"] .text-center[style*="font-size: 14px"] {
    margin-top: 12px !important;
    font-size: 13px !important;
    line-height: 1.5;
    color: #475569;
    text-align: left !important;
    background: #eef2f7;
    border: 1px solid #dde6f1;
    border-radius: 8px;
    padding: 10px 12px;
  }
  .modal[id^="user-responsive"] .modal-footer,
  .modal[id^="template-responsive"] .modal-footer {
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 10px 18px 14px;
    text-align: center;
  }
  .modal[id^="user-responsive"] .modal-footer .btn,
  .modal[id^="template-responsive"] .modal-footer .btn {
    min-width: 130px;
    border: none !important;
    border-radius: 8px !important;
    background: linear-gradient(135deg, #1e293b, #2d3f55) !important;
    color: #ffffff !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    padding: 9px 14px !important;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.22);
  }

  /* ========== RESPONSIVE — Tablet ========== */
  @media (max-width: 991px) {
    #main-content { padding-top: 62px !important; }
    #main-content .wrapper { padding: 0 8px !important; }

    .vd-breadcrumb {
      flex-wrap: wrap;
      border-radius: 14px;
      padding: 6px 12px 6px 8px;
    }
    .vd-breadcrumb .bc-item { white-space: normal; }

    #main-content .c_title .row.bgx-title-container {
      flex-direction: column !important;
    }
    #main-content .c_title .row.bgx-title-container > div {
      width: 100% !important;
      max-width: 100% !important;
      flex: 0 0 100% !important;
      text-align: left !important;
    }
    .dc-title-actions {
      justify-content: flex-start !important;
      flex-wrap: wrap !important;
      gap: 6px !important;
      margin-top: 8px !important;
    }

    #main-content .tabs .tablinks {
      padding: 5px 10px !important;
      font-size: 11px !important;
      margin-bottom: 6px !important;
    }

    #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form {
      flex-wrap: wrap !important;
    }
    #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form select.form-control {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
    }

    .filter-container, .vd-filter-form {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 6px !important;
    }
    .filter-container select, .filter-container .btn {
      width: 100% !important;
    }
  }

  /* ========== RESPONSIVE — Mobile ========== */
  @media (max-width: 767px) {
    #main-content { padding-top: 56px !important; }
    #main-content .wrapper { padding: 0 4px !important; }

    .gps-toast-container { right: 10px !important; left: 10px !important; max-width: none !important; }
    .gps-toast { min-width: auto !important; max-width: none !important; font-size: 13px !important; }

    .vd-breadcrumb-wrap { padding: 8px 0 10px !important; }
    .vd-breadcrumb {
      flex-wrap: wrap;
      font-size: 11px;
      padding: 5px 10px 5px 6px;
      gap: 2px;
      border-radius: 12px;
    }
    .vd-breadcrumb .bc-home { width: 24px; height: 24px; margin-right: 6px; }
    .vd-breadcrumb .bc-home i { font-size: 11px; }
    .vd-breadcrumb .bc-item { font-size: 11px; white-space: normal; }
    .vd-breadcrumb .bc-sep { margin: 0 4px; font-size: 10px; }

    #main-content .c_title { padding: 12px 14px !important; }
    #main-content .c_title h2 { font-size: 13px !important; }
    #main-content .c_title .row.bgx-title-container {
      flex-direction: column !important;
    }
    #main-content .c_title .row.bgx-title-container > div {
      width: 100% !important;
      max-width: 100% !important;
      flex: 0 0 100% !important;
      text-align: left !important;
    }
    /* Add Device button in title */
    #main-content .c_title .btn.btn-success {
      font-size: 11px !important;
      padding: 5px 12px !important;
      border-radius: 6px !important;
      margin-top: 6px !important;
      display: inline-block !important;
    }
    .dc-title-actions {
      justify-content: flex-start !important;
      flex-wrap: wrap !important;
      gap: 6px !important;
      margin-top: 8px !important;
    }

    #main-content .c_content.tabs { padding: 10px 8px !important; }

    #main-content .tabs .tablinks {
      padding: 4px 8px !important;
      font-size: 10px !important;
      border-radius: 6px !important;
      margin-right: 3px !important;
      margin-bottom: 5px !important;
    }

    /* All action buttons inside tab content */
    #main-content .tabs .delete_all,
    #main-content .tabs .user-responsive,
    #main-content .tabs .template-responsive,
    #main-content .tabs .btn {
      font-size: 11px !important;
      padding: 5px 10px !important;
      border-radius: 6px !important;
    }

    #main-content .tabs .tabcontent > div[style*="margin-bottom"] {
      gap: 5px 6px !important;
    }

    #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 {
      flex-wrap: wrap !important;
      justify-content: flex-start !important;
      gap: 5px !important;
    }
    #main-content .tabs .tabcontent > .col-lg-12.text-right.margin-bottom-10 .btn {
      font-size: 11px !important;
      padding: 5px 10px !important;
      margin: 0 !important;
    }

    .filter-container, .vd-filter-form {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 6px !important;
    }
    .filter-container select, .filter-container .btn {
      width: 100% !important;
    }

    /* Filter form: keep inline, compact */
    #main-content .tabs .vdc-tab-toolbar-right {
      width: 100% !important;
      margin-left: 0 !important;
    }
    #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form {
      flex-wrap: nowrap !important;
      width: 100% !important;
      gap: 6px !important;
    }
    #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form select.form-control {
      flex: 1 1 0% !important;
      width: auto !important;
      max-width: none !important;
      min-width: 0 !important;
      font-size: 11px !important;
      height: 32px !important;
    }
    #main-content .tabs .vdc-tab-toolbar-right .vdc-filter-form .btn {
      width: auto !important;
      flex-shrink: 0 !important;
      font-size: 11px !important;
      padding: 5px 14px !important;
      height: 32px !important;
    }

    /* Pagination: compact and wrap properly */
    .dataTables_wrapper .dataTables_paginate {
      text-align: center !important;
      float: none !important;
      max-width: 100% !important;
      padding-top: 8px !important;
    }
    .dataTables_wrapper .dataTables_paginate .pagination {
      display: flex !important;
      flex-wrap: wrap !important;
      justify-content: center !important;
      gap: 3px !important;
    }
    .dataTables_wrapper .dataTables_paginate .pagination > li > a,
    .dataTables_wrapper .dataTables_paginate .pagination > li > span {
      min-width: 28px !important;
      height: 28px !important;
      padding: 0 6px !important;
      font-size: 11px !important;
      border-radius: 6px !important;
    }
    .dataTables_wrapper .dataTables_info {
      float: none !important;
      text-align: center !important;
      max-width: 100% !important;
      font-size: 11px !important;
      padding-top: 6px !important;
    }
  }

  /* ========== RESPONSIVE — Small phone ========== */
  @media (max-width: 480px) {
    #main-content { padding-top: 52px !important; }

    .vd-breadcrumb { padding: 4px 8px 4px 5px; }
    .vd-breadcrumb .bc-home { width: 22px; height: 22px; }
    .vd-breadcrumb .bc-item { font-size: 10px; }

    #main-content .c_title { padding: 10px 10px !important; }
    #main-content .c_title h2 { font-size: 12px !important; }

    #main-content .tabs .tablinks { padding: 3px 6px !important; font-size: 9px !important; }

    #main-content .tabs .delete_all,
    #main-content .tabs .user-responsive,
    #main-content .tabs .template-responsive {
      font-size: 10px !important;
      padding: 4px 8px !important;
    }
  }
</style>
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="vd-breadcrumb-wrap">
      <nav class="vd-breadcrumb">
        <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Device Management</span>
        <span class="bc-sep">›</span>
        @if(Auth::user()->user_type=='Admin' and url('admin/view-device-assign')==url()->current())
          <span class="bc-item active">View Assigned Devices</span>
        @elseif(Auth::user()->user_type=='Admin' and url('admin/view-device-unassign')==url()->current())
          <span class="bc-item active">View Unassigned Devices</span>
        @else
          <span class="bc-item active">View Devices</span>
        @endif
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title" style="margin-bottom: 10px;">
            <div class="row bgx-title-container">
              <div class="{{ Auth::user()->user_type == 'Admin' ? 'col-lg-6' : 'col-lg-12' }}">
                @if(Auth::user()->user_type=='Admin' and url('admin/view-device-assign')==url()->current())
                <h2>Show Assigned Devices</h2>
                @elseif(Auth::user()->user_type=='Admin' and url('admin/view-device-unassign')==url()->current())
                <h2>Show Unassigned Devices</h2>
                @else
                <h2>Show Device</h2>
                @endif
              </div>
              @if (Auth::user()->user_type == 'Admin')
              <div class="col-lg-6 text-right">
                <a href="/{{$url_type}}/add-device" class="btn btn-success"> Add Device </a>
              </div>
              @endif
            </div>
            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content tabs">
            <div class="row" id="alert_msg">
              <div class="col-sm-12 alert alert-success alert-success-error" role="alert" style="display:none;"></div>
              <div class="col-sm-12 alert alert-danger alert-danger-error" role="alert" style="display:none;"></div>
              <div class="col-sm-12 alert alert-success" id="demo" role="alert" style="display: none"></div>
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
            <div class="tabs">
              <?php echo CommonHelper::getDeviceCategoryTabs($device, $show_acc_wise, $url_type, Session::get('device_category_id')); ?>
              <div id="loading" class="bgx-loading" style="display:none;">
                <img src="/assets/icons/loader.gif" alt="Loading..." />
              </div>
            </div>
            <div style="text-align: center;"></div>
          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->
    </div><!--/row-->
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>

@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="modal" id="certificateModal" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h4 class="modal-title"><strong>Download Certificate</strong></h4>
      </div>
      <form id="certificateForm" method="post" action="#">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group"><label class="form-label">Certificate Holder Name</label><input type="text" class="form-control" name="holder_name" required></div>
              <div class="form-group"><label class="form-label">Authority City</label><input type="text" class="form-control" name="authority_city" required></div>
              <div class="form-group"><label class="form-label">Fitment Date</label><input type="date" class="form-control" name="fitment_date" required></div>
              <div class="form-group"><label class="form-label">Vehicle Registration No</label><input type="text" class="form-control" name="vehicle_registration_no" required></div>
              <div class="form-group"><label class="form-label">VLTD Serial No</label><input type="text" class="form-control" name="vltd_serial_no" required></div>
              <div class="form-group"><label class="form-label">VLTD Make</label><input type="text" class="form-control" name="vltd_make" required></div>
              <div class="form-group"><label class="form-label">VLTD Model</label><input type="text" class="form-control" name="vltd_model" required></div>
              <div class="form-group"><label class="form-label">Chassis No</label><input type="text" class="form-control" name="chassis_no" required></div>
              <div class="form-group"><label class="form-label">Engine No</label><input type="text" class="form-control" name="engine_no" required></div>
              <div class="form-group"><label class="form-label">Color</label><input type="text" class="form-control" name="color" required></div>
              <div class="form-group"><label class="form-label">Vehicle Model</label><input type="text" class="form-control" name="vehicle_model" required></div>
              <div class="form-group"><label class="form-label">ARAI TAC/COP No</label><input type="text" class="form-control" name="arai_tac" required></div>
              <div class="form-group"><label class="form-label">ARAI Date</label><input type="date" class="form-control" name="arai_date" required></div>
              <div class="form-group"><label class="form-label">Service Provider</label><input type="text" class="form-control" name="service_provider" required></div>
            </div>
          </div>
        </div>
        <div class="modal-footer text-center">
          <button type="button" class="btn btn-info btn-raised rippler rippler-default" id="certificatePreviewBtn"><i class="fa fa-eye"></i> Preview</button>
          <button type="submit" class="btn btn-primary btn-raised rippler rippler-default"><i class="fa fa-download"></i> Download</button>
        </div>
      </form>
    </div>
  </div>
  </div>
<style>
  .example th, .example td {
      white-space: nowrap !important;
  }
</style>
<div class="gps-toast-container" id="gpsToastContainer"></div>
<script>
  function showToast(msg, type, title) {
    type = type || 'warning';
    if (typeof window.showGpsToast === 'function') {
      var map = { warning: 'warning', success: 'success', error: 'error', danger: 'error', info: 'info' };
      var t = map[type] || 'warning';
      var titles = { warning: 'Warning', success: 'Success', error: 'Error', info: 'Information' };
      var resolvedTitle = title || titles[type] || titles[t] || 'Notice';
      window.showGpsToast(t, resolvedTitle, msg, { durationMs: 5000 });
      return;
    }
    var icons = { warning: 'fa-exclamation-triangle', success: 'fa-check-circle', error: 'fa-times-circle' };
    var titles = { warning: 'Warning', success: 'Success', error: 'Error' };
    title = title || titles[type];
    var container = document.getElementById('gpsToastContainer');
    var toast = document.createElement('div');
    toast.className = 'gps-toast toast-' + type;
    toast.innerHTML =
      '<div class="gps-toast-icon"><i class="fa ' + icons[type] + '"></i></div>' +
      '<div class="gps-toast-body"><p class="gps-toast-title">' + title + '</p><p class="gps-toast-msg">' + msg + '</p></div>' +
      '<button class="gps-toast-close">&times;</button>' +
      '<div class="gps-toast-progress"></div>';
    toast.querySelector('.gps-toast-close').addEventListener('click', function() {
      toast.classList.add('removing');
      setTimeout(function() { toast.remove(); }, 300);
    });
    container.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) {
        toast.classList.add('removing');
        setTimeout(function() { toast.remove(); }, 300);
      }
    }, 3000);
  }

  $(document).ready(function() {
    function initializeDataTables() {
      $('.example').each(function() {
        var elementId = $(this).attr('id');
        if ($.fn.DataTable.isDataTable("#" + elementId)) {
          $("#" + elementId).DataTable().destroy();
        }
        $("#" + elementId).DataTable({
          paging: true,
          searching: true,
          ordering: true,
          lengthChange: true,
          pageLength: 10,
          scrollX: true,
          scrollY: '500px',
          autoWidth: false,
          scrollCollapse: true,
          "aLengthMenu": [
            [25, 50, 100, 500, -1],
            [25, 50, 100, 500, "All"]
          ],
          "iDisplayLength": 25
        });
      });
      $('#loading').hide();
    }

    // Initialize tabs: hide all, show first
    $('.tabcontent').hide();
    let firstTab = $('.tablinks').first();
    let activeTab = $('.tablinks.active').first(); 
    if(activeTab.length == 0 && firstTab.length) {
        firstTab.addClass('active');
        activeTab = firstTab;
    }
    if (activeTab.length) {
        let onclick = activeTab.attr('onclick');
        if(onclick) {
            let tabMatch = onclick.match(/'([^']+)'/);
            if(tabMatch) {
                $('#' + tabMatch[1]).show();
            }
        }
    }

    // Initialize datatables AFTER making the active tab visible!
    // This allows DataTables to correctly calculate columns width for the visible tab.
    initializeDataTables();

    // Explicitly adjust columns just in case
    setTimeout(function() {
        if ($.fn.DataTable) {
            var dtTables = $.fn.dataTable.tables({ visible: true, api: true });
            if (dtTables && dtTables.columns && typeof dtTables.columns.adjust === 'function') {
              dtTables.columns.adjust();
            } else if (dtTables && typeof dtTables.columns === 'function') {
              dtTables.columns().adjust();
            }
        }
    }, 100);

    $('.dataTables_filter input').attr("placeholder", "Zoeken...");
    $('.dataTables_length select').each(function() {
      if (!$(this).val()) {
        $(this).val('25');
      }
      this.style.color = '#1e293b';
      this.style.backgroundColor = '#fff';
    });
    
    $('#certificatePreviewBtn').on('click', function() {
      var deviceId = $('#certificateForm').data('deviceId');
      if (!deviceId) return;
      var previewUrl = '/user/device/' + deviceId + '/certificate/preview';
      var form = $('#certificateForm');
      var originalAction = form.attr('action');
      form.attr('action', previewUrl);
      form.attr('target', '_blank');
      form.trigger('submit');
      form.attr('action', originalAction);
      form.removeAttr('target');
    });
    $('.user-responsive').on('click', function(e) {
      var allVals = []; 
      
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var categoryId = $(this).data('category-id');
        $("#user-responsive" + categoryId).modal('show');
      }

    });
    $('.template-responsive').on('click', function(e) {
      var allVals = [];
       let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var categoryId = $(this).data('category-id');
        $("#template-responsive" + categoryId).modal('show');
      }
    });
    $('.delete_all').on('click', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var check = confirm("Are you sure want to delete these Device?");
        if (check == true) {
          var join_selected_values = allVals.join(",");
          $.ajax({
            url: $(this).data('url'),
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: 'ids=' + join_selected_values,
            success: function(data) {
              if (data['success']) {
                $(".sub_chk:checked").each(function() {
                  $(this).parents("tr").remove();
                });
                alert(data['success']);
                location.reload();
              } else if (data['error']) {
                alert(data['error']);
              } else {
                // alert('Whoops Something went wrong!!');
              }
            },
            error: function(data) {
              alert(data.responseText);
            }
          });
        }
      }
    });
    $('.user_assign_all').on('click', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var join_selected_values = allVals.join(",");
        var id = $(this).data('attr');
        // var user_id = $("#assignDeviceUser").val();
        var user_id = $(this).closest('.modal-body').find('.assignDeviceUser').val();
        var a_url = $('body').find('button.user-responsive').attr('data-url');

        $.ajax({
          url: a_url,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: join_selected_values,
            user_id: user_id
          },
          success: function(data) {
            if (data['success']) {
              // $(".sub_chk:checked").each(function() {
              //   $(this).parents("tr").remove();
              // });
              $('#user-responsive' + id).modal('hide');
              if (data['success']) {
                $('.alert-success-error').append(data['success']).show();
              }
              if (data['error']) {
                $('.alert-danger-error').append(data['error']).show();
              }
              // location.reload();
            } else if (data['error']) {
              $('#user-responsive' + id).modal('hide');
              $('.alert-danger-error').html(data['error']).show();
            } else {
              alert('Whoops Something went wrong!!');
            }
          },
          error: function(data) {
            alert(data.responseText);
          }
        });
      }
    });
    $('.temp_assign_all').on('click', function(e) {
      var allVals = [];
      let categoryID = $(this).attr('data-category-id');
      $(".sub_chk"+categoryID+":checked").each(function() {
        allVals.push($(this).attr('data-id'));
      });
      if (allVals.length <= 0) {
        showToast("Please select at least one device first.", "warning", "No Device Selected");
      } else {
        var join_selected_values = allVals.join(",");
        var temp_id = $(this).closest('.modal-body').find('.assignDeviceTemp').val();
        var id = $(this).data('attr');
        var a_url = $('body').find('button.template-responsive').attr('data-url');
        $.ajax({
          url: a_url,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: join_selected_values,
            temp_id: temp_id
          },
          success: function(data) {
            if (data['success']) {
              // $(".sub_chk:checked").each(function() {
              //   $(this).parents(l"tr").remove();
              // });

              $('#template-responsive' + id).modal('hide');
              if (data['success']) {
                $('.alert-success-error').append(data['success']).show();
              }
              if (data['error']) {
                $('.alert-danger-error').append(data['error']).show();
              }
              // alert(data['error']);
              // location.reload();
            } else if (data['error']) {
              $('#template-responsive' + id).modal('hide');
              $('.alert-danger-error').html(data['error']).show();

            } else {
              alert('Whoops Something went wrong!!');
            }
          },
          error: function(data) {
            alert(data.responseText);
          }
        });
      }
    });

    $("#temp_id").select2();
    $(".select2").select2();
  });

  function dataTableCheckAll(dataId) {
    if ($('#master'+ dataId).is(':checked', true)) {
      $(".sub_chk"+ dataId).prop('checked', true);
    } else {
      $(".sub_chk"+ dataId).prop('checked', false);
    }
  }

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      $('.tabcontent').hide();
      $('.tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $('.tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      if ($.fn.DataTable) {
          var dtTables = $.fn.dataTable.tables({ visible: true, api: true });
          if (dtTables && dtTables.columns && typeof dtTables.columns.adjust === 'function') {
              dtTables.columns.adjust();
          } else if (dtTables && typeof dtTables.columns === 'function') {
              dtTables.columns().adjust();
          }
      }

      return false;
  }
</script>




