@extends('layouts.apps')
@section('content')
<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;
use App\Models\TimezoneModel;

$timeZones = TimezoneModel::all();

use App\Template;
use App\DataFields;

$deviceCategoryIds = explode(',', $contact->device_category_id);

$configurations = json_decode($contact->configurations, true);
$canConfigurations = json_decode($contact->can_configurations, true);

$getDeviceCategory = CommonHelper::getDeviceCategory();
$getDeviceCategoryconfig = DeviceCategory::select("*")->orWhereIn('id', $deviceCategoryIds)->get();
// dd($getDeviceCategoryconfig);

$default_template = DB::table('templates')
  ->select('templates.*')
  ->where('templates.active_status', '1')
  ->get();
$get_default_template = DB::table('templates')
  ->select('templates.*')
  ->where('templates.default_template', '1')
  ->first();
?>
<style>
  #main-content .wrapper { padding-top: 10px !important; }
  .top-page-header { margin-bottom: 18px !important; }

  /* ─── Breadcrumb ─── */
  .eu-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .eu-breadcrumb {
    display: inline-flex; align-items: center;
    background: #1e293b; border-radius: 50px;
    padding: 6px 18px 6px 8px; gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
  }
  .eu-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
  }
  .eu-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
  .eu-breadcrumb .bc-item {
    color: rgba(255,255,255,0.65); font-size: 13px;
    font-weight: 500; text-decoration: none; white-space: nowrap;
  }
  .eu-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
  .eu-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

  /* ─── Main Card ─── */
  #main-content .c_panel {
    border-radius: 14px !important;
    box-shadow: 0 8px 32px rgba(15,23,42,0.10) !important;
    border: 0 !important; overflow: hidden !important;
    margin-bottom: 28px !important; background: #fff !important;
  }
  #main-content .c_panel .c_title {
    background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%) !important;
    padding: 18px 24px !important;
    border-bottom: 3px solid #76CF1C !important;
    display: flex; align-items: center;
  }
  #main-content .c_panel .c_title::before {
    content: '\f007'; font-family: 'FontAwesome';
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(118,207,28,0.15); display: flex;
    align-items: center; justify-content: center;
    color: #76CF1C; font-size: 16px; margin-right: 14px; flex-shrink: 0;
    line-height: 36px; text-align: center;
  }
  #main-content .c_panel .c_title h2 {
    color: #ffffff !important; font-size: 18px !important;
    font-weight: 700 !important; margin: 0 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
  }
  #main-content .c_panel .c_content { padding: 24px 28px 8px !important; }
  #main-content #alert_msg { margin: 0 !important; padding: 0 28px 22px !important; }

  /* ─── Alert Styling ─── */
  #main-content .alert-success {
    background: linear-gradient(135deg, rgba(118,207,28,0.08), rgba(118,207,28,0.04)) !important;
    border: 1px solid rgba(118,207,28,0.25) !important; color: #2d6a0e !important;
    border-radius: 10px !important; font-weight: 600 !important; font-size: 13px !important;
  }
  #main-content .alert-danger {
    background: linear-gradient(135deg, rgba(239,68,68,0.06), rgba(239,68,68,0.03)) !important;
    border: 1px solid rgba(239,68,68,0.2) !important; color: #b91c1c !important;
    border-radius: 10px !important; font-weight: 600 !important; font-size: 13px !important;
  }

  /* ─── Form Layout ─── */
  #main-content .form-horizontal .form-group {
    margin-left: 0 !important; margin-right: 0 !important;
    margin-bottom: 18px !important; padding: 4px 0 !important;
  }
  #main-content .form-horizontal .control-label,
  #main-content .control-label {
    color: #334155 !important; font-size: 13px !important;
    font-weight: 700 !important; text-align: left !important;
    padding-top: 10px !important; letter-spacing: 0.1px !important;
  }
  .require { color: #ef4444 !important; font-weight: 700 !important; }
  #main-content .form-control,
  #main-content select.form-control,
  #main-content .select2-selection--single,
  #main-content .select2-selection--multiple {
    min-height: 42px !important; border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important; box-shadow: none !important;
    font-size: 13px !important; color: #334155 !important;
    background: #f8fafc !important; transition: all 0.2s ease !important;
  }
  #main-content .form-control:focus,
  #main-content select.form-control:focus,
  #main-content .select2-container--focus .select2-selection--single,
  #main-content .select2-container--focus .select2-selection--multiple {
    border-color: #76CF1C !important;
    box-shadow: 0 0 0 3px rgba(118,207,28,0.12) !important;
    background: #fff !important;
  }

  /* ─── Checkbox toggle (2FA etc.) ─── */
  #main-content input[type="checkbox"].form-check-input,
  #main-content input[type="checkbox"] {
    accent-color: #76CF1C !important; cursor: pointer !important;
  }

  /* ─── Device Category Checkboxes ─── */
  .bgx-label-category {
    font-weight: 600 !important; color: #475569 !important;
    font-size: 13px !important;
  }
  .bgx-checkbox-category {
    width: 18px !important; height: 18px !important;
    accent-color: #76CF1C !important; cursor: pointer !important;
  }

  /* ─── Device Category Section Cards ─── */
  #main-content .device-category-fields {
    border: 1px solid #e2e8f0 !important; border-radius: 12px !important;
    overflow: hidden !important; margin-bottom: 20px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
  }
  #main-content .device-category-fields .card-title {
    background: linear-gradient(135deg, #0f172a, #1e293b) !important;
    border-radius: 0 !important; padding: 14px 20px !important;
    display: flex; align-items: center;
  }
  #main-content .device-category-fields .card-title::before {
    content: '\f1b3'; font-family: 'FontAwesome';
    color: #76CF1C; font-size: 15px; margin-right: 10px;
  }
  #main-content .device-category-fields .card-title h4 {
    color: #ffffff !important; margin: 0 !important;
    font-size: 14px !important; font-weight: 700 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
  }
  #main-content .device-category-fields .card-details {
    padding: 20px 16px !important; background: #fff !important;
  }

  /* ─── CAN Configuration Area ─── */
  .can-config-box {
    background: #f8fafc; border: 1.5px solid #e2e8f0;
    border-radius: 12px; padding: 18px 20px !important;
    margin: 12px 0 8px; position: relative;
  }
  .can-config-label {
    display: block; font-size: 13px !important; font-weight: 700 !important;
    color: #334155 !important; margin-bottom: 8px !important;
  }
  .can-config-label i { color: #76CF1C; margin-right: 6px; }
  .can-config-input-wrap {
    display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
  }
  .can-config-input {
    min-height: 34px !important; height: 34px !important;
    font-size: 11.5px !important; color: #64748b !important;
    background: #fff !important; padding: 4px 12px !important;
    font-family: 'Courier New', monospace !important;
    flex: 1;
  }
  .can-copy-btn {
    width: 34px; height: 34px; flex-shrink: 0;
    background: #e2e8f0 !important; border: none !important;
    border-radius: 8px !important; color: #475569 !important;
    font-size: 14px; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center;
    margin-top: 1px;
  }
  .can-copy-btn:hover {
    background: #76CF1C !important; color: #0f172a !important;
  }
  .can-copy-btn.copied {
    background: #76CF1C !important; color: #0f172a !important;
  }
  .can-config-btn {
    background: linear-gradient(135deg, #1e293b, #334155) !important;
    border: 0 !important; color: #fff !important;
    border-radius: 8px !important; padding: 9px 20px !important;
    font-weight: 700 !important; font-size: 13px !important;
    transition: all 0.2s !important;
  }
  .can-config-btn:hover {
    background: linear-gradient(135deg, #334155, #475569) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(30,41,59,0.2) !important;
  }

  /* ─── Radio Buttons (Enable/Disable) ─── */
  #main-content input[type="radio"] {
    accent-color: #76CF1C !important; cursor: pointer !important;
  }

  /* ─── Primary Action Buttons ─── */
  #main-content .bgx-save-button .btn.btn-primary,
  #main-content .btn.btn-primary.btn-flat[type="submit"] {
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    border: 0 !important; color: #0f172a !important;
    border-radius: 10px !important; padding: 12px 36px !important;
    font-weight: 800 !important; font-size: 14px !important;
    box-shadow: 0 6px 20px rgba(118,207,28,0.30) !important;
    letter-spacing: 0.3px !important; transition: all 0.2s !important;
  }
  #main-content .bgx-save-button .btn.btn-primary:hover,
  #main-content .btn.btn-primary.btn-flat[type="submit"]:hover {
    filter: brightness(1.05); transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(118,207,28,0.35) !important;
  }

  /* ─── HR Divider ─── */
  #main-content hr {
    border-top: 1px solid #e2e8f0 !important; margin: 0 28px !important;
  }

  /* ═══════════════════════════════════════════════
     CAN PROTOCOL CONFIGURATION MODAL — Premium
     ═══════════════════════════════════════════════ */
  .can-modal .modal-dialog { max-width: 560px; }
  .can-modal .can-modal-content {
    border: none !important; border-radius: 20px !important;
    overflow: hidden !important; position: relative;
    box-shadow: 0 30px 80px rgba(0,0,0,0.22), 0 0 0 1px rgba(255,255,255,0.05) !important;
    background: #fff !important;
  }
  .can-modal .can-accent-bar {
    height: 5px; width: 100%;
    background: linear-gradient(90deg, #76CF1C, #1e293b, #76CF1C);
  }
  .can-modal .can-close {
    position: absolute; top: 18px; right: 20px; z-index: 10;
    background: #f1f5f9 !important; border: none !important;
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #64748b !important; font-size: 18px; cursor: pointer;
    transition: all 0.2s; line-height: 1; padding: 0;
  }
  .can-modal .can-close:hover {
    background: #e2e8f0 !important; color: #1e293b !important;
    transform: rotate(90deg);
  }
  .can-modal .can-body { padding: 32px 32px 24px; }
  .can-modal .can-hero { text-align: center; margin-bottom: 24px; }
  .can-modal .can-icon-ring {
    width: 56px; height: 56px; border-radius: 14px; margin: 0 auto 12px;
    background: linear-gradient(135deg, rgba(118,207,28,0.12), rgba(118,207,28,0.05));
    border: 2px solid rgba(118,207,28,0.2);
    display: flex; align-items: center; justify-content: center;
  }
  .can-modal .can-icon-ring i { color: #76CF1C; font-size: 20px; }
  .can-modal .can-title {
    font-size: 19px; font-weight: 800; color: #0f172a; margin: 0 0 4px;
    letter-spacing: -0.3px;
  }
  .can-modal .can-subtitle {
    font-size: 12.5px; color: #94a3b8; margin: 0; font-weight: 500; line-height: 1.5;
  }
  .can-modal .can-field-group { margin-bottom: 16px; }
  .can-modal .can-label {
    display: block; font-size: 12.5px; font-weight: 700;
    color: #334155; margin-bottom: 6px; letter-spacing: 0.2px;
  }
  .can-modal .can-label i { color: #76CF1C; margin-right: 6px; font-size: 12px; }
  .can-modal .can-label .require { color: #ef4444; margin-left: 3px; }
  .can-modal .can-field-group .form-control,
  .can-modal .can-field-group select.form-control {
    min-height: 42px !important; border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important; font-size: 13px !important;
    color: #334155 !important; background: #f8fafc !important;
    transition: all 0.2s !important; padding: 8px 14px !important;
    width: 100% !important;
  }
  .can-modal .can-field-group .form-control:focus,
  .can-modal .can-field-group select.form-control:focus {
    border-color: #76CF1C !important;
    box-shadow: 0 0 0 3px rgba(118,207,28,0.12) !important;
    background: #fff !important;
  }
  .can-modal .can-dynamic-fields {
    margin-top: 20px; padding-top: 16px;
    border-top: 1px dashed #e2e8f0;
  }
  .can-modal .can-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9;
  }
  .can-modal .can-btn-cancel {
    background: #f1f5f9 !important; color: #64748b !important;
    border: none !important; border-radius: 10px !important;
    padding: 10px 22px !important; font-weight: 700 !important;
    font-size: 13px !important; transition: all 0.2s !important;
  }
  .can-modal .can-btn-cancel:hover { background: #e2e8f0 !important; }
  .can-modal .can-btn-submit {
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    color: #0f172a !important; border: none !important;
    border-radius: 10px !important; padding: 10px 24px !important;
    font-weight: 800 !important; font-size: 13px !important;
    box-shadow: 0 4px 14px rgba(118,207,28,0.3) !important;
    transition: all 0.2s !important;
  }
  .can-modal .can-btn-submit:hover {
    filter: brightness(1.06); transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(118,207,28,0.35) !important;
  }
  .can-modal .can-btn-submit i { margin-right: 6px; }

  /* ═══════════════════════════════════════════════
     CONFIRMATION MODAL — Premium
     ═══════════════════════════════════════════════ */
  #userEditDelOptionsModal .modal-dialog { max-width: 520px; }
  #userEditDelOptionsModal .modal-content {
    border: none !important; border-radius: 20px !important;
    overflow: hidden !important;
    box-shadow: 0 30px 80px rgba(0,0,0,0.22), 0 0 0 1px rgba(255,255,255,0.05) !important;
    background: #fff !important;
  }
  #userEditDelOptionsModal .modal-header {
    background: linear-gradient(135deg, #0f172a, #1e293b) !important;
    border-bottom: 3px solid #76CF1C !important;
    padding: 18px 24px !important;
  }
  #userEditDelOptionsModal .modal-header .modal-title {
    color: #fff !important; font-size: 16px !important;
    font-weight: 700 !important; text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
  }
  #userEditDelOptionsModal .modal-header .modal-title strong {
    color: #fff !important;
  }
  #userEditDelOptionsModal .modal-header .close {
    color: rgba(255,255,255,0.6) !important; opacity: 1 !important;
    text-shadow: none !important; font-size: 18px !important;
  }
  #userEditDelOptionsModal .modal-header .close:hover { color: #fff !important; }
  #userEditDelOptionsModal .modal-body {
    padding: 28px 28px 12px !important;
  }
  #userEditDelOptionsModal .modal-body .form-group {
    margin-bottom: 12px !important; padding: 12px 16px !important;
    background: #f8fafc !important; border-radius: 10px !important;
    border: 1.5px solid #e2e8f0 !important;
    transition: all 0.2s !important; cursor: pointer;
  }
  #userEditDelOptionsModal .modal-body .form-group:hover {
    border-color: #76CF1C !important;
    background: rgba(118,207,28,0.04) !important;
  }
  #userEditDelOptionsModal .modal-body .control-label {
    color: #334155 !important; font-size: 13px !important;
    font-weight: 600 !important; cursor: pointer !important;
    display: flex !important; align-items: center !important; gap: 10px !important;
  }
  #userEditDelOptionsModal .modal-body input[type="radio"] {
    accent-color: #76CF1C !important; width: 18px !important;
    height: 18px !important; flex-shrink: 0 !important;
  }
  #userEditDelOptionsModal .just_confirm {
    font-size: 15px !important; font-weight: 600 !important;
    color: #475569 !important; padding: 20px 0 !important;
  }
  #userEditDelOptionsModal .modal-footer {
    border-top: 1px solid #f1f5f9 !important; padding: 16px 24px !important;
  }
  #userEditDelOptionsModal .modal-footer .btn {
    border-radius: 10px !important; padding: 10px 24px !important;
    font-weight: 700 !important; font-size: 13px !important;
    border: none !important;
  }
  #userEditDelOptionsModal .modal-footer .btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    color: #0f172a !important;
    box-shadow: 0 4px 14px rgba(118,207,28,0.3) !important;
  }
  #userEditDelOptionsModal .modal-footer .btn-primary:hover {
    filter: brightness(1.06); transform: translateY(-1px);
  }

  /* ========== RESPONSIVE — Tablet ========== */
  @media (max-width: 991px) {
    #main-content { padding-top: 62px !important; }
    #main-content .wrapper { padding: 0 8px !important; }

    #main-content .c_panel .c_content { padding: 18px 16px 8px !important; }
    #main-content #alert_msg { padding: 0 16px 14px !important; }
    #main-content hr { margin: 0 16px !important; }

    #main-content .form-horizontal .control-label,
    #main-content .control-label {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
      flex: 0 0 100% !important;
      text-align: left !important;
      padding-top: 0 !important;
      padding-bottom: 4px !important;
    }
    #main-content .form-horizontal .form-group > div[class*="col-"] {
      float: none !important;
      width: 100% !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    .col-lg-offset-3 { margin-left: 0 !important; }

    .device-category-fields .card-details { padding: 14px 12px !important; }
    .device-category-fields .card-details .form-group > .control-label,
    .device-category-fields .card-details .form-group > div[class*="col-"] {
      float: none !important;
      width: 100% !important;
    }
  }

  /* ========== RESPONSIVE — Mobile ========== */
  @media (max-width: 767px) {
    #main-content { padding-top: 56px !important; }
    #main-content .wrapper { padding: 0 4px !important; }

    .eu-breadcrumb-wrap { padding: 8px 0 10px !important; }
    .eu-breadcrumb {
      flex-wrap: wrap !important;
      font-size: 11px !important;
      padding: 5px 12px 5px 6px !important;
      gap: 2px !important;
      border-radius: 14px !important;
    }
    .eu-breadcrumb .bc-home { width: 24px; height: 24px; margin-right: 6px; }
    .eu-breadcrumb .bc-home i { font-size: 11px; }
    .eu-breadcrumb .bc-item { font-size: 11px; }
    .eu-breadcrumb .bc-sep { margin: 0 4px; font-size: 10px; }

    #main-content .c_panel .c_title {
      padding: 12px 14px !important;
      flex-wrap: wrap !important;
    }
    #main-content .c_panel .c_title::before {
      width: 30px; height: 30px; font-size: 13px; margin-right: 10px;
      line-height: 30px;
    }
    #main-content .c_panel .c_title h2 { font-size: 14px !important; }
    #main-content .c_panel .c_content { padding: 12px 10px !important; }
    #main-content #alert_msg { padding: 0 10px 12px !important; }
    #main-content hr { margin: 0 10px !important; }

    #main-content .form-horizontal .form-group {
      flex-direction: column !important;
      margin-bottom: 12px !important;
    }
    #main-content .form-horizontal .control-label,
    #main-content .control-label {
      text-align: left !important;
      width: 100% !important;
      max-width: 100% !important;
      flex: 0 0 100% !important;
      padding-top: 0 !important;
      padding-bottom: 4px !important;
      font-size: 12px !important;
    }
    #main-content .form-horizontal .form-group > div[class*="col-"] {
      float: none !important;
      width: 100% !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    .col-lg-offset-3 { margin-left: 0 !important; }

    #main-content .form-control,
    #main-content select.form-control {
      min-height: 38px !important;
      font-size: 12px !important;
      border-radius: 8px !important;
    }

    #main-content .bgx-save-button .btn.btn-primary,
    #main-content .btn.btn-primary.btn-flat[type="submit"] {
      padding: 10px 24px !important;
      font-size: 13px !important;
      width: 100% !important;
      border-radius: 8px !important;
    }

    /* Device Categories — checkbox list compact */
    .form-group.bgx-margin-bottom > .col-lg-6.bgx-margin-top {
      padding-left: 0 !important;
      padding-right: 0 !important;
    }
    .form-group.bgx-margin-bottom .row.col-md-6 {
      width: 100% !important;
      float: none !important;
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      padding: 4px 0 !important;
      margin: 0 !important;
    }
    .form-group.bgx-margin-bottom .row.col-md-6 > .col-xs-6 {
      width: auto !important;
      float: none !important;
      padding: 0 !important;
      flex: 1 !important;
    }
    .form-group.bgx-margin-bottom .row.col-md-6 > .col-xs-6.text-right {
      flex: 0 0 auto !important;
      text-align: right !important;
    }
    .bgx-label-category { font-size: 12px !important; }
    .bgx-checkbox-category { width: 16px !important; height: 16px !important; }

    /* CAN Configuration — full width, no side gaps */
    .can-config-box {
      padding: 12px !important;
      border-radius: 10px;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
    .can-config-input-wrap { flex-direction: column !important; gap: 6px !important; }
    .can-config-input { font-size: 11px !important; width: 100% !important; }
    .can-config-btn {
      width: 100% !important;
      text-align: center !important;
      font-size: 12px !important;
      padding: 8px 12px !important;
      white-space: nowrap !important;
    }
    .can-config-label { font-size: 12px !important; }

    .can-modal .modal-dialog { max-width: calc(100vw - 24px) !important; margin: 12px !important; }
    .can-modal .can-body { padding: 16px !important; }
    .can-modal .can-hero { padding: 20px 16px 14px !important; }
    .can-modal .can-title { font-size: 16px; }
    .can-modal .can-actions { flex-direction: column !important; }
    .can-modal .can-actions .btn { width: 100% !important; }

    #userEditDelOptionsModal .modal-dialog { max-width: calc(100vw - 24px) !important; margin: 12px !important; }

    .device-category-fields { margin-bottom: 14px !important; }
    .device-category-fields .card-title { padding: 10px 14px !important; }
    .device-category-fields .card-title h4 { font-size: 12px !important; }
    .device-category-fields .card-details { padding: 12px 8px !important; }
    .device-category-fields .card-details .form-group > .control-label,
    .device-category-fields .card-details .form-group > div[class*="col-"] {
      float: none !important;
      width: 100% !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    /* Nested row padding fix for device category fields */
    .device-category-fields .card-details .row {
      margin-left: 0 !important;
      margin-right: 0 !important;
      padding: 0 !important;
    }
    .device-category-fields .card-details [class*="col-"] {
      padding-left: 4px !important;
      padding-right: 4px !important;
    }
  }

  /* ========== RESPONSIVE — Small phone ========== */
  @media (max-width: 480px) {
    #main-content { padding-top: 52px !important; }

    .eu-breadcrumb { padding: 4px 10px 4px 5px !important; }
    .eu-breadcrumb .bc-home { width: 22px; height: 22px; }
    .eu-breadcrumb .bc-item { font-size: 10px; }

    #main-content .c_panel .c_title { padding: 10px 10px !important; }
    #main-content .c_panel .c_title::before { width: 26px; height: 26px; font-size: 12px; margin-right: 8px; line-height: 26px; }
    #main-content .c_panel .c_title h2 { font-size: 13px !important; }
    #main-content .c_panel .c_content { padding: 10px 8px !important; }

    #main-content .form-control,
    #main-content select.form-control { min-height: 36px !important; font-size: 11.5px !important; }

    .can-modal .can-body { padding: 12px !important; }
    .can-modal .can-icon-ring { width: 44px; height: 44px; border-radius: 10px; }
    .can-modal .can-title { font-size: 15px; }
    .can-modal .can-subtitle { font-size: 11px; }
  }
</style>
<!--main content start-->
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="eu-breadcrumb-wrap">
      <nav class="eu-breadcrumb">
        <a href="/{{$url_type}}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
        <a href="/{{$url_type}}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Account</span>
        <span class="bc-sep">›</span>
        <a href="/{{$url_type}}/view-user" class="bc-item">View Account</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Update Account</span>
      </nav>
    </div>
    <!--======== Page Title and Breadcrumbs End ========-->
    <!--======== Form Validation Content Start End ========-->
    <div class="row">
      <div class="col-md-12">
        <!--=========== START TAGS INPUT ===========-->
        <div class="c_panel">
          <div class="c_title">
            <h2>Update User</h2>
            <div class="clearfix"></div>
          </div>
          <!--/.c_title-->
          <div class="c_content">
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
          <div class="row" id="alert_msg" style='padding: 0px 20px;'>
            @if ($message = Session::get('success'))
            <div class="col-sm-12 alert alert-success" role="alert">
              {{ $message }}
            </div>
            @endif
            <div class="col-sm-12 alert alert-success success_msg" role="alert" style="display:none"></div>

            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
            <form class="validator form-horizontal userResellerEditForm" id="commentForm" method="post" action="/{{$url_type}}/update-user/{{$contact->id}}/{{$contact->user_type}}" onsubmit="return false;">
              @method('PATCH')
              @csrf
              <input type="hidden" class="current_utype" value="{{ $contact->user_type }}">
              @if($contact->user_type == 'Admin')
              <div class="form-group ">
                <label for="cname" class="control-label col-lg-3">Name <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class=" form-control" id="cname" name="name" type="text" value="{{ $contact->name }}" placeholder="Enter Name" required />
                </div>
              </div>
              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">E-Mail <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-control " id="cemail" type="email" name="email" value="{{ $contact->email }}" placeholder="Enter E-Mail" required />
                </div>
              </div>
              <div class="form-group">
                <label for="timezone" class="control-label col-lg-3">TimeZones <span class="require">*</span></label>
                <div class="col-lg-6">
                  <select name="timezone" class="select2" placeholder="Enter Time Zone" id="timezone">
                    <option value="">Please Select Time Zone</option>
                    @foreach($timeZones as $timezone)
                    <option value="{{ $timezone->name }}" {{ isset($user) && $contact->timezone == $timezone->name ? 'selected' : '' }}>
                      {{ $timezone->name }} ({{ $timezone->utc_offset }})
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group ">
                <label for="curl" class="control-label col-lg-3">Login Password </label>
                <div class="col-lg-6">
                  <input class="form-control" type="password" placeholder="Enter 4 digit device password" name="password" value="{{$contact->LoginPassword}}" required>
                </div>
              </div>
              <div class="form-group">
                <div class="col-lg-offset-3 col-lg-6">
                  <button class="btn btn-primary btn-flat" type="submit">Update</button>
                </div>
              </div>
              @else
              <input type="hidden" class="userNewAccType" name="acc_type_changed">
              <div class="userAccCases"></div>
              <!-- For dynamic fields -->
              @if(Auth::user()->user_type !='User')
              <div class="form-group ">
                <label for="curl" class="control-label col-lg-3">Account Type</label>
                <div class="col-lg-6">
                  <select data-prev="{{$contact->user_type}}" id="userType" class="form-control userAccType" name="user_type">
                    <option <?php echo (($contact->user_type == 'Reseller') ? 'selected' : '') ?> value="Reseller">Manufacturer</option>
                    <option <?php echo (($contact->user_type == 'User') ? 'selected' : '') ?> value="User">Dealer</option>
                    @if($currentUser->user_type =='Admin')
                    <option <?php echo (($contact->user_type == 'Support') ? 'selected' : '') ?> value="Support">Support</option>
                    @endif
                  </select>
                </div>
              </div>
              @endif
              <div class="form-group ">
                <label for="cname" class="control-label col-lg-3">Name <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class=" form-control" id="cname" name="name" type="text" value="{{ $contact->name }}" placeholder="Enter Name" required />
                </div>
              </div>
              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">Mobile <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-control " id="cmobile" type="text" name="mobile" value="{{ $contact->mobile }}" placeholder="Enter Mobile Number" maxlength="10" required />
                </div>
              </div>
              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">E-Mail <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-control " id="cemail" type="email" name="email" value="{{ $contact->email }}" placeholder="Enter E-Mail" required />
                </div>
              </div>
              <div class="form-group">
                <label for="timezone" class="control-label col-lg-3">TimeZones <span class="require">*</span></label>
                <div class="col-lg-6">
                  <select name="timezone" class="select2" id="timezone">
                    <option value="">Please Select Time Zone</option>
                    @foreach($timeZones as $timezone)
                    @php
                    $tzValue = $timezone->name . ' (' . $timezone->utc_offset . ')';
                    @endphp
                    <option value="{{ $timezone->name }}"
                      {{ isset($contact) && $contact->timezone == $timezone->name ? 'selected' : '' }}>
                      {{ $tzValue }}
                    </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-group ">
                <label for="cemail" class="control-label col-lg-3">2 Factor Authentication <span class="require">*</span></label>
                <div class="col-lg-6">
                  <input class="form-check-input" id="twoFactorAuthentication" type="checkbox"
                    name="twoFactorAuthentication"
                    {{ $contact->twoFactorAuthentication ? 'checked' : '' }}
                    style="width: 52px;margin-top: 0;height: 20px;" />
                </div>
              </div>
              <div class="is-support-active" style="display: none;"></div>
              <div class="form-group bgx-margin-bottom row">
                <label for="curl" class="control-label col-lg-3">Device Categories<span class="require">*</span></label>
                <div class="col-lg-6 bgx-margin-top row ">
                  @foreach($getDeviceCategory as $deviceCategory)
                  <div class="row col-md-6">
                    <div class="col-xs-6 col-sm-6 col-md-4">
                      <label class='bgx-label-category'>{{$deviceCategory->device_category_name}}</label>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-4 text-right">
                      <input type="checkbox" {{ in_array($deviceCategory->id, $deviceCategoryIds) ? 'checked' : '' }} class="bgx-checkbox-category" name="deviceCategory[]" value="{{ $deviceCategory->id }}" onclick="getDeviceCategoryInput({{$contact->id}},{{$deviceCategory->id}})">
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
              @foreach($getDeviceCategoryconfig as $key => $category)
              @if(in_array($category->id,$deviceCategoryIds))

              <div class="device-category-fields card device-category-block-{{ $category->id }}">
                <div class="card-title">
                  <h4>{{ CommonHelper::getDeviceCategoryName($category->id) }}</h4>
                </div>
                <div class="card-details">
                  @php
                  $inputs = json_decode($category->inputs, true);
                  $totalInputs = count($inputs);
                  $inputIds = collect($inputs)->pluck('id')->toArray();
                  $dataFields = DataFields::whereIn('id', $inputIds)->get()->keyBy('id');
                  $user = Auth::user();

                  $templates = Template::where('device_category_id', $category->id)
                  ->where(function ($query) use ($user) {
                  if ($user->user_type == 'Admin') {
                  $query->whereNull('id_user');
                  } else {
                  $query->where('id_user', $user->id);
                  }
                  })
                  ->get();
                  $enhancedInputs = collect($inputs)->map(function ($input) use ($dataFields) {
                  $input['validationConfig'] = $dataFields[$input['id']]->validationConfig ?? null;
                  return $input;
                  });
                  @endphp
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="templates<?= $key ?>" class="control-label col-lg-3">
                          Templates <span class="require">*</span>
                        </label>
                        <div class="col-lg-8">
                          <select class="userAccType form-control"
                            id="templates<?= $category->id ?>"
                            name="configuration[<?= $category->id ?>][template]"
                            onchange="changeTemplate(<?= $category->id ?>)">
                            <?php if (!empty($templates)): ?>
                              <?php foreach ($templates as $temp): ?>
                                <option value="<?= $temp['id'] ?>">
                                  <?= htmlspecialchars($temp['template_name']) ?>
                                  <?= $temp['default_template'] == 1 ? ' (Default)' : '' ?>
                                </option>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <option>No Template Found</option>
                            <?php endif; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                  @foreach($enhancedInputs as $index => $input)
                  @php
                  $configurationValue = isset($configurations[$key]) ? $configurations[$key]: null;
                  @endphp

                  @if($index % 2 === 0)
                  <div class="row">
                    @endif
                    <div class="col-lg-6">
                      <input class="form-control inputType" type="hidden" placeholder="Enter {{$input['key']}}" name="idParameters[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" value="{{$input['id']}}" />
                      @if ($input['type'] == 'select')
                      @php
                      $validationConfig = json_decode($input['validationConfig'],true);
                      @endphp
                      <div class="form-group">
                        <label class="control-label col-lg-3">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}</label>
                        <div class="col-lg-8">
                          <select class="form-control inputType" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]" {{ $input['requiredFieldInput'] ? 'required' : '' }}>
                            <!-- <option value="">Please Select</option> -->
                            @foreach($validationConfig['selectOptions'] as $configkey => $option)
                            <option value="{{ $validationConfig['selectValues'][$configkey] }}" {{ isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]) && $configurationValue && strtolower($validationConfig['selectValues'][$configkey]) == $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      @elseif ($input['type'] == 'multiselect')
                      @php
                      $validationConfig = json_decode($input['validationConfig'],true);
                      @endphp
                      <div class="form-group">
                        <label class="control-label col-lg-3">
                          {{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}
                        </label>
                        <div class="col-lg-8">
                          <select class="inputType" id="configval{{$category->id}}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}][]" multiple {{ $input['requiredFieldInput'] ? 'required' : '' }}>
                            @foreach($validationConfig['selectOptions'] as $configkey => $option)
                            @php
                            $inputKey = str_replace(' ', '_', strtolower($input['key']));
                            $rawValue = $configurationValue[$inputKey]['value'] ?? [];
                            if (is_string($rawValue)) {
                            $decoded = json_decode($rawValue, true);
                            $selectedValues = is_array($decoded) ? $decoded : explode(',', $rawValue);
                            } elseif (is_array($rawValue)) {
                            $selectedValues = $rawValue;
                            } else {
                            $selectedValues = [];
                            }
                            $selectedValues = array_map('strval', $selectedValues);
                            @endphp
                            <option value="{{ $validationConfig['selectValues'][$configkey] }}"
                              {{ in_array($validationConfig['selectValues'][$configkey], $selectedValues) ? 'selected' : '' }}>
                              {{ $option }}
                            </option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <script>
                        $(document).ready(function() {
                          console.log("$validationConfig['maxSelectValue'] ==>", <?= $validationConfig['maxSelectValue'] ?>);
                          var $select = $("#configval{{$category->id}}");

                          $select.select2({
                            placeholder: "Select up to 3 options",
                            width: "100%"
                          });
                          $select.on("change", function() {
                            var selected = $(this).select2("val");
                            if (selected && selected.length > <?= $validationConfig['maxSelectValue'] ?>) {
                              selected.splice(<?= $validationConfig['maxSelectValue'] ?>);
                              $(this).select2("val", selected);
                              alert("You can only select up to {{$validationConfig['maxSelectValue']}} options.");
                            }
                          });
                        });
                        // $(document).ready(function() {
                        //   $("#configval{{$category->id}}").select2({
                        //     placeholder: "Select options",
                        //     allowClear: true,
                        //     width: "100%"
                        //   });
                        // });
                      </script>

                      @else
                      <div class="form-group">
                        @php
                        $addClassTextArray = isset($input['type']) && $input['type'] == 'text' ? "text-array-space": '';
                        $addClassIpUrl = isset($input['type']) && $input['type'] == 'IP/URL' ? "ip-url-space" : '';
                        @endphp
                        <label class="control-label col-lg-3">{{ $input['key'] }}{!! $input['requiredFieldInput'] ? ' <span class="require">*</span>' : '' !!}</label>
                        <div class="col-lg-8">
                          <input class="form-control {{$addClassTextArray}} {{$addClassIpUrl}}" type="{{ $input['type'] == 'number' ? 'number' : 'text' }}"
                            {!! $input['type']=='number' ? 'min="' . ($input['numberRange']['min'] ?? '' ) . '" max="' . ($input['numberRange']['max'] ?? '' ) . '"' : '' !!}
                            placeholder="Enter {{ isset($input['key']) ? $input['key'] :''  }}" name="configuration[{{ $category->id }}][{{ str_replace(' ', '_', strtolower($input['key'])) }}]"
                            value="{{ isset($configurationValue) && isset($configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'])  ? $configurationValue[str_replace(' ', '_', strtolower($input['key']))]['value'] : '' }}"
                            {{ $input['requiredFieldInput'] ? 'required' : '' }}>
                        </div>
                      </div>
                      @endif
                    </div>
                    @if ($index % 2 === 1 || $index === $totalInputs - 1)
                  </div>
                  @endif
                  @endforeach
                  @if(Auth::user()->user_type =='Admin')
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label>
                        <div class="col-lg-8">
                          <input type="number" name="configuration[{{ $category->id }}][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['ping_interval']['value'])  ? $configurationValue['ping_interval']['value'] : '' }}" />
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>
                        <div class="col-lg-6">
                          <label class="padding-10">Enable</label>
                          <input checked type="radio" name="configuration[{{ $category->id }}][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>
                          <label class="padding-10">Disable</label>
                          <input type="radio" name="configuration[{{ $category->id }}][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>
                        </div>
                      </div>
                    </div>

                  </div>
                  @else
                  <input type="hidden" name="configuration[{{ $category->id }}][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['ping_interval']['value'])  ? $configurationValue['ping_interval']['value'] : '' }}" />
                  <input type="hidden" name="configuration[{{ $category->id }}][is_editable]" class="form-control inputType" placeholder="Ping Interval" value="{{ isset($configurationValue) && isset($configurationValue['is_editable']['value'])  ? $configurationValue['is_editable']['value'] : '' }}" />
                  @endif
                  @if( $category->is_can_protocol == 1 )
                  <div class="row" style="padding: 0 15px;">
                    <div class="col-lg-12">
                      <div class="can-config-box isCanEnable{{$category->id}}">
                        <label class="can-config-label"><i class="fa fa-cogs"></i> CAN Configuration <span class="require">*</span></label>
                        @php
                        $value = isset($canConfigurations[$category->id] ) ?$canConfigurations[$category->id]: [];
                        $result = is_array($value) ? json_encode($value) : $value;
                        @endphp
                        <div class="can-config-input-wrap">
                          <input type="text" class="form-control can-config-input" name="canConfigurationArr[{{$category->id}}]" id="canConfigurationArr{{$category->id}}" value="{{$result}}" readonly />
                          <button type="button" class="can-copy-btn" onclick="copyCanConfig('canConfigurationArr{{$category->id}}')" title="Copy to clipboard">
                            <i class="fa fa-copy"></i>
                          </button>
                        </div>
                        <div class="alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                        <button type="button" class="btn btn-primary can-config-btn" onclick="openCanModal('{{ $category->id }}')">
                          <i class="fa fa-sliders" style="margin-right:6px;"></i> Configure CAN Protocol
                        </button>
                      </div>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="modal can-modal" id="canModal{{$category->id}}">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content can-modal-content">
                      <div class="can-accent-bar"></div>
                      <button type="button" class="can-close" data-dismiss="modal">&times;</button>
                      <div class="can-body">
                        <div class="can-hero">
                          <div class="can-icon-ring"><i class="fa fa-sliders"></i></div>
                          <h3 class="can-title">CAN Protocol Configuration</h3>
                          <p class="can-subtitle">Configure CAN bus parameters for {{ CommonHelper::getDeviceCategoryName($category->id) }}</p>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-plug"></i> CAN Channel <span class="require">*</span></label>
                          <select id="can_channel{{$category->id}}" name="canConfiguration[{{$category->id}}][can_channel]" class="form-control">
                            <option value="">-- Select CAN Channel --</option>
                            <option value="1">CAN 1</option>
                            <option value="2">CAN 2</option>
                            <option value="3">CAN 3</option>
                            <option value="4">CAN 4</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-tachometer"></i> CAN Baud Rate <span class="require">*</span></label>
                          <select id="can_baud_rate{{$category->id}}" name="canConfiguration[{{$category->id}}][can_baud_rate]" class="form-control">
                            <option value="">-- Select Baud Rate --</option>
                            <option value="500">500 kbps</option>
                            <option value="250">250 kbps</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-tag"></i> CAN ID Type <span class="require">*</span></label>
                          <select id="can_id_type{{$category->id}}" name="canConfiguration[{{$category->id}}][can_id_type]" class="form-control">
                            <option value="">-- Select CAN ID --</option>
                            <option value="0">Standard</option>
                            <option value="1">Extended</option>
                          </select>
                        </div>
                        <div class="can-field-group">
                          <label class="can-label"><i class="fa fa-cogs"></i> CAN Protocol <span class="require">*</span></label>
                          <select id="can_protocol{{$category->id}}" name="canConfiguration[{{$category->id}}][can_protocol]" class="form-control" onchange="selectedCanProtocol('{{$category->id}}')">
                            <option value="">-- Select Protocol --</option>
                            <option value="1">J1979</option>
                            <option value="2">J1939</option>
                            <option value="3">Custom CAN</option>
                          </select>
                        </div>
                        <div class="can-dynamic-fields" id="dynamicCanFields{{$category->id}}"></div>
                        <div class="can-actions">
                          <button type="button" class="btn can-btn-cancel" data-dismiss="modal">Cancel</button>
                          <button type="button" class="btn can-btn-submit" onclick="generateJSON('{{$category->id}}')">
                            <i class="fa fa-check"></i> Submit
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endif
              @endforeach
              <div id="deviceCategoryInputFields"></div>
              <div class="form-group">
                <div class="bgx-save-button  col-lg-11">
                  <button class="btn btn-primary btn-flat" type="submit">Update</button>
                </div>
              </div>
              @endif
            </form>
            <hr>
          </div>
          <!--/.c_content-->
        </div>
        <!--/.c_panels-->
      </div>
    </div>
    <!--======== Form Validation Content Start End ========-->
  </section>
</section>
<!--======== Main Content End ========-->
@include('modals.userEditDelOptions')
@stop
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript">
  function copyCanConfig(inputId) {
    var input = document.getElementById(inputId);
    var text = input.value;
    navigator.clipboard.writeText(text).then(function() {
      var btn = input.closest('.can-config-input-wrap').querySelector('.can-copy-btn');
      btn.classList.add('copied');
      btn.innerHTML = '<i class="fa fa-check"></i>';
      setTimeout(function() {
        btn.classList.remove('copied');
        btn.innerHTML = '<i class="fa fa-copy"></i>';
      }, 1500);
    });
  }

  function openCanModal(index) {
    $('#canModal' + index).modal('show');

    const config = JSON.parse(document.getElementById(`canConfigurationArr${index}`).value);
    // const config1 = JSON.parse(config);
    const canProtocolEl = $('#can_protocol' + index);
    canProtocolEl.one('change', function() {
      for (let field in config) {
        const value = config[field]?.value;
        if (document.getElementById(field)) {
          document.getElementById(field).value = value;
        }
      }
    });
    canProtocolEl.val(config['can_protocol' + index]['value']).trigger('change');
  }

  function openCanModal1(index) {
    $('#canModal1' + index).modal('show');
  }


  // function openCanModal(index) {
  //   $('#canModal' + index).modal('show');
  //   $('#can_protocol' + index).trigger('change').val;
  //   const config = JSON.parse(document.getElementById(`canConfigurationArr${index}`).value);

  //   // Loop through each field and set values
  //   for (let field in config) {
  //     const value = config[field]?.value;

  //     if (document.getElementById(field)) {
  //       document.getElementById(field).value = value;
  //     }
  //   }
  // }

  function selectedCanProtocol(index) {
    let canProtocolValue = $('#can_protocol' + index).val();
    if (!canProtocolValue) return;

    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-can-protocol-fields') }}";

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: {
        protocol: canProtocolValue,
        _token: '{{ csrf_token() }}'
      },
      success: function(fields) {
        let html = '<div class="row">';

        fields.forEach(field => {
          const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
          const inputType = field.inputType;
          let config = {};
          try {
            config = JSON.parse($("#canConfigurationArr" + index).val());
          } catch (e) {
            console.warn("Invalid JSON, using empty config.");
          }
          let validation = {};
          console.log("config ==>", config);
          try {
            validation = JSON.parse(field.validationConfig || '{}');
          } catch (e) {
            console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
          }
          let value = config[fieldId]?.value ?? '';

          // Escape for input fields
          let escapedValue = String(value).replace(/"/g, '&quot;');
          let inputHtml = `<input type="hidden" name="idCanParameters[${index}][${fieldId}]" value="${field.id}" />`;
          inputHtml += `<input type="hidden" name="CanParametersType[${index}][${fieldId}]" value="${inputType}" />`;
          let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control"  placeholder="Enter ${field.fieldName}" value="${escapedValue}"`;
          if (inputType === 'number') {
            if (validation.numberInput) {
              attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
            }
            inputHtml += `<input type="number" ${attr} />`;
          } else if (inputType === 'select') {
            inputHtml += `<select ${attr}>`;

            const selectedValue = config[fieldId]?.value ?? '';

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach(option => {
                const isSelected = option === selectedValue ? 'selected' : '';
                inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = key == selectedValue ? 'selected' : '';
                inputHtml += `<option value="${key}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;
          } else if (inputType === 'multiselect') {
            inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${index}][${fieldId}][]">`;

            const selectedValue = config[fieldId]?.value ?? [];
            // Ensure selectedValue is always an array
            const selectedArray = Array.isArray(selectedValue) ? selectedValue : [selectedValue];

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach((option, key) => {
                const isSelected = selectedArray.includes(option) ? 'selected' : '';
                inputHtml += `<option value="${validation.selectValues[key]}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = selectedArray.includes(key) ? 'selected' : '';
                inputHtml += `<option value="${key}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;

            // Apply Select2
            setTimeout(() => {
              $(document).ready(function() {
                var $select = $('#' + fieldId);
                $select.select2({
                  placeholder: "Select up to 3 options",
                  width: "100%"
                });
                $select.on("change", function() {
                  var selected = $(this).select2("val");
                  if (selected && selected.length > validation.maxSelectValue) {
                    selected.splice(validation.maxSelectValue);
                    $(this).select2("val", selected);
                    alert("You can only select up to " + validation.maxSelectValue + " options.");
                  }
                });
              });
            }, 100);
          } else if (inputType === 'text_array') {
            let values = [""];
            let maxValue = validation.maxValueInput || 0;
            console.log("maxValue ==>", maxValue);
            inputHtml += `
              <div id="${fieldId}_wrapper_${index}" class="text-array-wrapper">
                ${values.map((val, i) => `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text"
                      maxlength='8'
                      id="${fieldId}${index}${i}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" 
                      value="${val.trim()}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `).join('')}
                <button type="button" class="btn btn-sm btn-primary add-text-input mt-1">
                  <i class="fa fa-plus"></i> Add
                </button>
              </div>
            `;
            inputHtml += `
              <input type="hidden" 
                id="${fieldId}" 
                name="canConfiguration[${index}][${fieldId}]" />
            `;
            setTimeout(function() {
              const wrapper = $("#" + fieldId + "_wrapper_" + index);
              const addButton = wrapper.find(".add-text-input");
              console.log("maxValue ==>", maxValue);
              wrapper.on("click", ".add-text-input", function() {
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count >= maxValue) {
                  alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                  addButton.prop("disabled", true);
                  return;
                }

                const newInput = `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text" 
                      id="${fieldId}${index}${count}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `;
                $(this).before(newInput);
                const newCount = wrapper.find(".text-array-item").length;
                if (maxValue && newCount >= maxValue) {
                  addButton.prop("disabled", true);
                }
                updateHiddenValue();
              });
              wrapper.on("click", ".remove-text-input", function() {
                $(this).closest(".text-array-item").remove();
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count < maxValue) {
                  addButton.prop("disabled", false);
                }
                updateHiddenValue();
              });
              wrapper.on("input", "input[type=text]", function() {
                updateHiddenValue();
              });

              function updateHiddenValue() {
                const values = [];
                wrapper.find("input[type=text]").each(function() {
                  const val = $(this).val().trim();
                  if (val) values.push(val);
                });
                $("#" + fieldId).val("{" + values.join(",") + "}");
              }
              updateHiddenValue();
            }, 100);
          } else if (inputType === 'hex') {
            
            let attr1 = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control text-array-space me-2"`;
            let maxValue = validation.maxValueInput || 0;
            if (validation.maxValueInput) {
              attr1 += `maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr1} value="${escapedValue}"/>`;

          } else {
            if (validation.maxValueInput) {
              attr += ` maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr} />`;
          }

          html += `<div class="col-md-12">
                    <div class="form-group" id="modalInput">
                        <label for="${fieldId}" class="control-label padding-left-14">
                            ${field.fieldName} <span class="require">*</span>
                        </label>
                        <div class="col-lg-12">
                            ${inputHtml}
                            <div class="col-sm-12 alert alert-danger ${fieldId}_error" role="alert" style="display:none"></div>
                        </div>
                    </div></div>`;
        });
        html += '</div>';
        $('#dynamicCanFields' + index).html(html).show();
      },
      error: function(xhr) {
        console.error("Error fetching CAN protocol fields", xhr);
      }
    });
  }

  function selectedCanProtocol1(index) {
    let canProtocolValue = $('#can_protocol' + index).val();
    if (!canProtocolValue) return;

    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-can-protocol-fields') }}";

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: {
        protocol: canProtocolValue,
        _token: '{{ csrf_token() }}'
      },
      success: function(fields) {
        let html = '<div class="row">';

        fields.forEach(field => {
          const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
          const inputType = field.inputType;
          let config = {};
          try {
            config = JSON.parse($("#canConfigurationArr" + index).val());
          } catch (e) {
            console.warn("Invalid JSON, using empty config.");
          }
          let validation = {};
          console.log("config ==>", config);
          try {
            validation = JSON.parse(field.validationConfig || '{}');
          } catch (e) {
            console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
          }
          let value = config[fieldId]?.value ?? '';

          // Escape for input fields
          let escapedValue = String(value).replace(/"/g, '&quot;');
          let inputHtml = `<input type="hidden" name="idCanParameters[${index}][${fieldId}]" value="${field.id}" />`;
          inputHtml += `<input type="hidden" name="CanParametersType[${index}][${fieldId}]" value="${inputType}" />`;
          let attr = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control"  placeholder="Enter ${field.fieldName}" value="${escapedValue}"`;
          console.log('inputType ==>', field.fieldName, inputType);
          if (inputType === 'number') {
            if (validation.numberInput) {
              attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
            }
            inputHtml += `<input type="number" ${attr} />`;
          } else if (inputType === 'select') {
            inputHtml += `<select ${attr}>`;
            const selectedValue = config[fieldId]?.value ?? '';

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach((option, key) => {
                const isSelected = option === selectedValue ? 'selected' : '';
                inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = key == selectedValue ? 'selected' : '';
                inputHtml += `<option value="${validation.selectValues[key]}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;
          } else if (inputType === 'multiselect') {
            inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${index}][${fieldId}][]">`;

            const selectedValue = config[fieldId]?.value ?? [];

            console.log("selectedValue =>", selectedValue);

            // Ensure selectedValue is always an array
            const selectedArray = Array.isArray(selectedValue) ? selectedValue : [selectedValue];

            if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
              validation.selectOptions.forEach(option => {
                const isSelected = selectedArray.includes(option) ? 'selected' : '';
                inputHtml += `<option value="${option}" ${isSelected}>${option}</option>`;
              });
            } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
              Object.entries(validation.selectOptions).forEach(([key, value]) => {
                const isSelected = selectedArray.includes(key) ? 'selected' : '';
                inputHtml += `<option value="${key}" ${isSelected}>${value}</option>`;
              });
            } else {
              inputHtml += `<option value="">-- Select --</option>`;
            }

            inputHtml += `</select>`;

            // Apply Select2

            setTimeout(() => {

              $(document).ready(function() {
                var $select = $('#' + fieldId);
                $select.select2({
                  placeholder: "Select up to 3 options",
                  width: "100%"
                });
                $select.on("change", function() {
                  var selected = $(this).select2("val");
                  if (selected && selected.length > validation.maxSelectValue) {
                    selected.splice(validation.maxSelectValue);
                    $(this).select2("val", selected);
                    alert("You can only select up to " + validation.maxSelectValue + " options.");
                  }
                });
              });
            }, 100);
          } else if (inputType === 'text_array') {
            let values = [""];
            let maxValue = validation.maxValueInput || 0;
            console.log("maxValue ==>", maxValue);
            inputHtml += `
              <div id="${fieldId}_wrapper_${index}" class="text-array-wrapper">
                ${values.map((val, i) => `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text"
                      maxlength='8'
                      id="${fieldId}${index}${i}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" 
                      value="${val.trim()}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `).join('')}
                <button type="button" class="btn btn-sm btn-primary add-text-input mt-1">
                  <i class="fa fa-plus"></i> Add
                </button>
              </div>
            `;
            inputHtml += `
              <input type="hidden" 
                id="${fieldId}" 
                name="canConfiguration[${index}][${fieldId}]" />
            `;
            setTimeout(function() {
              const wrapper = $("#" + fieldId + "_wrapper_" + index);
              const addButton = wrapper.find(".add-text-input");
              console.log("maxValue ==>", maxValue);
              wrapper.on("click", ".add-text-input", function() {
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count >= maxValue) {
                  alert("You can only add up to " + maxValue + " inputs for " + field.fieldName + ".");
                  addButton.prop("disabled", true);
                  return;
                }

                const newInput = `
                  <div class="text-array-item d-flex align-items-center mb-2">
                    <input type="text" 
                      id="${fieldId}${index}${count}" 
                      name="canConfiguration[${index}][${fieldId}][]" 
                      class="form-control text-array-space me-2" 
                      placeholder="Enter ${field.fieldName}" />
                    <button type="button" class="btn btn-sm btn-danger remove-text-input">
                      <i class="fa fa-minus"></i>
                    </button>
                  </div>
                `;
                $(this).before(newInput);
                const newCount = wrapper.find(".text-array-item").length;
                if (maxValue && newCount >= maxValue) {
                  addButton.prop("disabled", true);
                }
                updateHiddenValue();
              });
              wrapper.on("click", ".remove-text-input", function() {
                $(this).closest(".text-array-item").remove();
                const count = wrapper.find(".text-array-item").length;
                if (maxValue && count < maxValue) {
                  addButton.prop("disabled", false);
                }
                updateHiddenValue();
              });
              wrapper.on("input", "input[type=text]", function() {
                updateHiddenValue();
              });

              function updateHiddenValue() {
                const values = [];
                wrapper.find("input[type=text]").each(function() {
                  const val = $(this).val().trim();
                  if (val) values.push(val);
                });
                $("#" + fieldId).val("{" + values.join(",") + "}");
              }
              updateHiddenValue();
            }, 100);
          } else if (inputType === 'hex') {
            let attr1 = `id="${fieldId}" name="canConfiguration[${index}][${fieldId}]" class="form-control text-array-space me-2"`;
            let maxValue = validation.maxValueInput || 0;
            if (validation.maxValueInput) {
              attr1 += `maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr1}  value="${escapedValue}"/>`;

          } else {
            if (validation.maxValueInput) {
              attr += ` maxlength="${validation.maxValueInput}"`;
            }
            inputHtml += `<input type="text" ${attr} />`;
          }

          html += `<div class="col-md-12 padding-3 padding-top-10">
                    <div class="form-group" id="modalInput">
                        <label for="${fieldId}" class="control-label padding-left-14">
                            ${field.fieldName} <span class="require">*</span>
                        </label>
                        <div class="col-lg-12">
                            ${inputHtml}
                            <div class="col-sm-12 alert alert-danger ${fieldId}_error" role="alert" style="display:none"></div>
                        </div>
                    </div></div>`;
        });
        html += '</div>';
        $('#dynamicCanFields1' + index).html(html).show();
      },
      error: function(xhr) {
        console.error("Error fetching CAN protocol fields", xhr);
      }
    });
  }

  function generateJSON(index) {
    let canConfigData = {};

    $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
      let fieldId = $(this).attr('id');
      let value = $(this).val(); // could be string or array
      console.log("fieldId ==>", fieldId, " ====", value);
      // Handle can_protocol separately
      if (fieldId === `can_protocol${index}`) {
        canConfigData[fieldId] = {
          id: 97,
          value: value
        };
      } else if (fieldId == `can_channel${index}`) {
        canConfigData[fieldId] = {
          id: 94,
          value: value
        };
      } else if (fieldId == `can_baud_rate${index}`) {
        canConfigData[fieldId] = {
          id: 96,
          value: value
        };
      } else if (fieldId == `can_id_type${index}`) {
        canConfigData[fieldId] = {
          id: 95,
          value: value
        };
      } else {
        let hiddenInput = $(`input[name="idCanParameters[${index}][${fieldId}]"]`);
        let canParametersType = $(`input[name="CanParametersType[${index}][${fieldId}]"]`).val();
        let id = hiddenInput.val();

        if (id && value !== "") {
            if (canParametersType == 'multiselect') {
                const formattedMultiValue = `{${value.join(',')}}`;
                canConfigData[fieldId] = {
                  id: parseInt(id),
                  value: formattedMultiValue
                };
            } else {
                canConfigData[fieldId] = {
                    id: parseInt(id),
                    value: value // keep array as-is
                };
            }
        }
      }
    });

    // Set final JSON outside the loop
    $('#canConfigurationArr' + index).val(JSON.stringify(canConfigData));
    $('#canModal' + index).modal('hide');
  }


  function generateJSON1(index) {
    let canConfigData = {};

    $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
      let fieldId = $(this).attr('id');
      let value = $(this).val();

      if (fieldId === `can_protocol${index}`) {
        canConfigData[fieldId] = {
          id: 97,
          value: value
        };
      } else if (fieldId == `can_channel${index}`) {
        canConfigData[fieldId] = {
          id: 94,
          value: value
        };
      } else if (fieldId == `can_baud_rate${index}`) {
        canConfigData[fieldId] = {
          id: 96,
          value: value
        };
      } else if (fieldId == `can_id_type${index}`) {
        canConfigData[fieldId] = {
          id: 95,
          value: value
        };
      } else {
        let hiddenInput = $(`input[name="idCanParameters[${index}][${fieldId}]"]`);
        let canParametersType = $(`input[name="CanParametersType[${index}][${fieldId}]"]`).val();
        let id = hiddenInput.val();

        if (id && value !== "") {
          if (canParametersType == 'multiselect') {
              const formattedMultiValue = `{${value.join(',')}}`;
              canConfigData[fieldId] = {
                id: parseInt(id),
                value: formattedMultiValue
              };
          } else {
              canConfigData[fieldId] = {
                id: parseInt(id),
                value: value
              };
          }
        }
      }
      $('#canConfigurationArr' + index).val(JSON.stringify(canConfigData));
      $('#canModal1' + index).modal('hide');
    });
  }

  let existingCheckedValues = <?= json_encode($deviceCategoryIds) ?>;

  function handleSupportActiveVisibility() {
    let val = $('.userAccType').val();
    let support = $('.is-support-active');

    // Clear existing content
    support.empty();

    if (val === 'Support') {
      // Determine checked state from PHP variable
      let checkValue = "{{ $contact->is_support_active == 1 ? 'checked' : '' }}";

      let html = `
        <div class="form-group">
            <label for="is_support_active" class="control-label col-lg-3">Configuration Edit Permission</label>
            <div class="col-lg-6" style="position: absolute; left: 4%;">
                <input 
                    type="checkbox" 
                    class="form-control" 
                    name="is_support_active" 
                    style="height: 20px;"
                    ${checkValue}
                >
            </div>
        </div>`;
      support.html(html).show();
    } else {
      support.hide();
    }
  }

  $(document).on('change', '.userAccType', function() {
    handleSupportActiveVisibility();
  });
  $(window).on('load', function() {
    handleSupportActiveVisibility();
  });

  $(document).ready(function() {


    $('.templates').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');
      // ids.push(id);
      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });
    $('input[type="text"][name^="configuration"]').on('keypress', function(event) {
      if (event.which === 32) { // 32 is the ASCII code for space
        event.preventDefault(); // Prevent the space from being entered
      }
    });
  })
  $(document).ready(function() {
    $('#commentForm').submit(function(event) {
      //event.preventDefault();
      $('.error_msg').html('').hide();
      $('.success_msg').html('').hide();
      let error_msg = "";
      let formIsValid = true;


      $(this).find('input[required], select[required]').each(function() {
        let inputValue = $(this).val();
        let inputType = $(this).attr('type');
        let inputName = $(this).attr('name');
        let label = $(this).closest('.form-group').find('.control-label').text();


        if (inputType === 'number') {
          let minVal = parseFloat($(this).attr('min'));
          let maxVal = parseFloat($(this).attr('max'));
          let numericValue = parseFloat(inputValue);

          if (!isNaN(minVal) && numericValue < minVal) {
            error_msg = 'Validation Error: ' + label + ' should be greater than or equal to ' + minVal;
            formIsValid = false;
            return false;
          }


          if (!isNaN(maxVal) && numericValue > maxVal) {
            error_msg = 'Validation Error: ' + label + ' should be less than or equal to ' + maxVal;
            formIsValid = false;
            return false;
          }
        }


        if (inputValue === '') {
          error_msg = 'Validation Error: ' + label + ' is required';
          formIsValid = false;
          return false;
        }
      });

      if (formIsValid) {
        let selectedUserType = $('#userType').length ? $('#userType').val() : "{{$contact->user_type}}";
        let actionUrl = "/{{$url_type}}/update-user/{{$contact->id}}/" + selectedUserType;
        let formData = $(this).serialize();

        $.ajax({
          url: actionUrl,
          type: "POST",
          data: formData,
          success: function(response) {
            $(".success_msg").text("updated Successfully!").show();
            document.documentElement.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
            // window.location.reload();
          },
          error: function(xhr) {
            let errors = JSON.parse(xhr.responseText);
            $('.error_msg').empty();
            if (errors && errors.errors) {
              $.each(errors.errors, function(key, value) {
                $('.error_msg').append(value[0] + '<br>').show();
              });
            }
            document.documentElement.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          },
          complete: function() {
            $('#loading').hide();
          }
        });
      } else {
        $('.error_msg').text(error_msg).show();
        document.documentElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  function getDeviceCategoryInput(userId, deviceCategoryId) {
    // Get all checked checkbox values
    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-multiple-categories') }}";

    const isChecked = $(`input.bgx-checkbox-category[value="${deviceCategoryId}"]`).is(':checked');

    if (!isChecked) {
      existingCheckedValues = existingCheckedValues.filter(val => val != deviceCategoryId);
      $(`.device-category-block-${deviceCategoryId}`).remove(); // Remove section
    } else {


      let checkedValues = $('input.bgx-checkbox-category:checked').map(function() {
        return this.value;
      }).get();

      let newCheckedValues = checkedValues.filter(val => !existingCheckedValues.includes(val));
      // Send all selected device category ids to the server
      $.ajax({
        url: actionUrl,
        type: "POST",
        data: {
          ids: newCheckedValues,
          userId: userId
        },
        success: function(response) {
          let result = JSON.parse(response);
          let htmlContent = '';
          if (result.status == 200) {
            $("#templates").select2({
              placeholder: 'Select your country...',
              allowClear: true
            });

            let inputFields = JSON.parse(result.device);
            let templates = JSON.parse(result.templates);
            let configValue = <?= json_encode($enhancedInputs) ?>;
            const offset = existingCheckedValues.length;
            inputFields.forEach((data, adjustedIndex) => {
              const index = offset + 1;
              let input = JSON.parse(data.inputs);
              let canEnable = data.is_can_protocol == 1 ? true : false;
              htmlContent += '<div class="device-category-fields card device-category-block-' + deviceCategoryId + '">';
              htmlContent += '<div class="card-title"><h4 >' + data.device_category_name + '</h4></div>';
              htmlContent += '<div class="card-details">';
              htmlContent += '<div class="row">';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Templates <span class="require">*</span></label><div class="col-lg-8"><select class="form-control userAccType" id="templates' + deviceCategoryId + '" name="configuration[' + deviceCategoryId + '][template]" class="select2" onchange="changeTemplate(' + index + ')">';
              if (templates[adjustedIndex].length > 0) {
                templates[adjustedIndex].forEach((temp) => {
                  // if (temp.default_template == 1) {
                  //   changeTemplate(index, temp.id)
                  // }
                  htmlContent += '<option ' + (temp.default_template == 1 ? "selected" : "") + '  value="' + temp.id + '">' + temp.template_name + '' + (temp.default_template == 1 ? ' (Default)' : '') + '</option>';
                });
              }
              // htmlContent += '<option>No Template Found</option>';
              htmlContent += '</select></div></div></div></div>';

              input.forEach((input, index1) => {
                let validation = JSON.parse(input.validationConfig);
                if (index1 % 2 === 0) {
                  htmlContent += '<div class="row">';
                }
                htmlContent += '<div class="col-lg-6">';
                htmlContent += '<input class="form-control inputType" type="hidden" placeholder="Enter ' + input.key + '" name="idParameters[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" value="' + input.id + '" />';
                if (input.type == 'select') {
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<select class="form-control inputType" name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']["value"]" ' + (input.requiredFieldInput ? '' : '') + '>';
                  // htmlContent += '<option value="">Please Select</option>';

                  validation?.selectOptions.forEach((option, optIndex) => {
                    htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                  });

                  htmlContent += '</select>';
                  htmlContent += '</div>';
                  htmlContent += '</div>';
                } else if (input.type == 'multiselect') {
                  htmlContent += '<div class="form-group">';
                  htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                  htmlContent += '<div class="col-lg-8">';
                  htmlContent += '<select class="inputType" id="configval' + deviceCategoryId + '" name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + '][]" ' + (input.requiredFieldInput ? '' : '') + ' multiple>';
                  // htmlContent += '<option value="">Please Select</option>';

                  validation?.selectOptions.forEach((option, optIndex) => {
                    htmlContent += '<option  value="' + validation?.selectValues[optIndex] + '">' + option + '</option>';
                  });

                  htmlContent += '</select>';
                  htmlContent += '</div>';
                  htmlContent += '</div>';
                  setTimeout(() => {
                    $('#configval' + deviceCategoryId).select2({
                      placeholder: 'Select options',
                      allowClear: true,
                      width: '100%'
                    });
                  }, 100);
                } else {
                  if (input.key == 'Password') {
                    htmlContent += '<div class="form-group">';
                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div class="col-lg-8">';
                    htmlContent += '<input class="form-control passwordInputValidation" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'minlength="' + validation?.numberInput?.min + '" maxlength="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '') + '>';
                    htmlContent += '</div>';
                    htmlContent += '</div>';
                  } else {
                    let addClassTextArray = input?.type === 'text_array' ? 'text-array-space' : '';
                    let addClassIpUrl = input?.type === 'IP/URL' ? 'ip-url-space' : '';
                    htmlContent += '<div class="form-group">';
                    htmlContent += '<label class="control-label col-lg-3">' + input.key + (input.requiredFieldInput ? ' <span class="require">*</span>' : '') + '</label>';
                    htmlContent += '<div class="col-lg-8">';
                    // htmlContent += '<input class="form-control inputType" type="' + (input.type == 'number' ? 'number' : 'text') + '" ' + (input.type == 'number' ? 'min="' + validation?.numberInput?.min + '" max="' + validation?.numberInput?.max + '"' : '') + ' placeholder="Enter ' + input.key + '" name="configuration[' + index + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' + (input.requiredFieldInput ? 'required' : '" maxlength="' + validation?.maxValueInput') + '>';
                    htmlContent +=
                      '<input class="form-control inputType ' + addClassTextArray + ' ' + addClassIpUrl + '" type="' +
                      (input.type === 'number' ? 'number' : 'text') + '" ' +
                      (input.type === 'number' && validation?.numberInput ?
                        'min="' + validation.numberInput.min + '" max="' + validation.numberInput.max + '" ' :
                        '') +
                      'placeholder="Enter ' + input.key + '" ' +
                      'name="configuration[' + deviceCategoryId + '][' + input.key.replace(/\s+/g, '_').toLowerCase() + ']" ' +
                      (input.requiredFieldInput ?
                        'required' :
                        (validation?.maxValueInput ? 'maxlength="' + validation.maxValueInput + '"' : '')) +
                      '>';

                    htmlContent += '</div>';
                    htmlContent += '</div>';
                  }
                }
                htmlContent += '</div>'; // Close col-lg-6
                // Close the row for every second iteration or when it's the last item
                if (index1 % 2 === 1 || index1 === input.length - 1) {
                  htmlContent += '</div>'; // Close row
                }
              });
              //   htmlContent += '<div class="form-group"><label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label><div class="col-lg-8">
              //   htmlContent += '<input type="number" name="configuration[`ping_interval`][`value`]" place holder="Ping Inteval" value=""/>';
              //   htmlContent +='</div></div>';
              htmlContent += '<div class="row">';
              htmlContent += '<div class="col-lg-6"><div class="form-group">';
              htmlContent += '<label for="curl" class="control-label col-lg-3">Ping Interval <span class="require">*</span></label>';
              htmlContent += '<div class="col-lg-8">';
              htmlContent += '<input type="number" name="configuration[' + deviceCategoryId + '][ping_interval]" class="form-control inputType" placeholder="Ping Interval" value=""/>';
              htmlContent += '</div></div></div>';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<div class="form-group">';
              htmlContent += '<label for="curl" class="control-label col-lg-3">Device Edit Permission<span class="require">*</span></label>';
              htmlContent += '<div class="col-lg-6">';
              htmlContent += '<label class="padding-10">Enable</label><input checked type="radio" name="configuration[' + deviceCategoryId + '][is_editable]" value="1" style="height:20px; width:20px; vertical-align: middle;" required>';
              htmlContent += '<label class="padding-10">Disable</label><input type="radio" name="configuration[' + deviceCategoryId + '][is_editable]" value="0" style="height:20px; width:20px; vertical-align: middle;" required>';

              htmlContent += '</div></div></div>';
              if (canEnable) {
                htmlContent += `
                <div class="row" style="padding: 0 15px;">
                  <div class="col-lg-12">
                    <div class="can-config-box isCanEnable${deviceCategoryId}">
                      <label class="can-config-label"><i class="fa fa-cogs"></i> CAN Configuration <span class="require">*</span></label>
                      <div class="can-config-input-wrap">
                        <input type="text" class="form-control can-config-input" name="canConfigurationArr[${deviceCategoryId}]" id="canConfigurationArr${deviceCategoryId}" value="" readonly />
                        <button type="button" class="can-copy-btn" onclick="copyCanConfig('canConfigurationArr${deviceCategoryId}')" title="Copy to clipboard">
                          <i class="fa fa-copy"></i>
                        </button>
                      </div>
                      <div class="alert alert-danger modelName_error" role="alert" style="display: none;"></div>
                      <button type="button" class="btn btn-primary can-config-btn" onclick="openCanModal1(${deviceCategoryId})">
                        <i class="fa fa-sliders" style="margin-right:6px;"></i> Configure CAN Protocol
                      </button>
                    </div>
                  </div>
                </div>`;
                htmlContent += `
                    <div class="modal can-modal" id="canModal1${deviceCategoryId}" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content can-modal-content">
                          <div class="can-accent-bar"></div>
                          <button type="button" class="can-close" data-dismiss="modal">&times;</button>
                          <div class="can-body">
                            <div class="can-hero">
                              <div class="can-icon-ring"><i class="fa fa-sliders"></i></div>
                              <h3 class="can-title">CAN Protocol Configuration</h3>
                              <p class="can-subtitle">Configure CAN bus parameters for ${data.device_category_name}</p>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-plug"></i> CAN Channel <span class="require">*</span></label>
                              <select class="form-control" id="can_channel${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_channel]" required>
                                <option value="">-- Select CAN Channel --</option>
                                <option value="1">CAN 1</option>
                                <option value="2">CAN 2</option>
                                <option value="3">CAN 3</option>
                                <option value="4">CAN 4</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-tachometer"></i> CAN Baud Rate <span class="require">*</span></label>
                              <select id="can_baud_rate${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_baud_rate]" class="form-control" required>
                                <option value="">-- Select Baud Rate --</option>
                                <option value="500">500 kbps</option>
                                <option value="250">250 kbps</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-tag"></i> CAN ID Type <span class="require">*</span></label>
                              <select id="can_id_type${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_id_type]" class="form-control" required>
                                <option value="">-- Select CAN ID --</option>
                                <option value="0">Standard</option>
                                <option value="1">Extended</option>
                              </select>
                            </div>
                            <div class="can-field-group">
                              <label class="can-label"><i class="fa fa-cogs"></i> CAN Protocol <span class="require">*</span></label>
                              <select id="can_protocol${deviceCategoryId}" name="canConfiguration[${deviceCategoryId}][can_protocol]" class="form-control" onchange="selectedCanProtocol1(${deviceCategoryId})">
                                <option value="">-- Select Protocol --</option>
                                <option value="1">J1979</option>
                                <option value="2">J1939</option>
                                <option value="3">Custom CAN</option>
                              </select>
                            </div>
                            <div class="can-dynamic-fields" id="dynamicCanFields1${deviceCategoryId}"></div>
                            <div class="can-actions">
                              <button type="button" class="btn can-btn-cancel" data-dismiss="modal">Cancel</button>
                              <button type="button" class="btn can-btn-submit" onclick="generateJSON1(${deviceCategoryId})">
                                <i class="fa fa-check"></i> Submit
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  `;

              }
              htmlContent += '</div>';

              htmlContent += '</div></div></div>';

            });

            $('#deviceCategoryInputFields').html(htmlContent);
          } else {
            $('#deviceCategoryInputFields').html('<p>No input fields found.</p>');
            alert(result.message);
          }
        },
        error: function(xhr) {
          console.log(xhr.responseText);
          $('#deviceCategoryInputFields').html('<p>Error retrieving data.</p>').show();
        },
        complete: function() {
          $('#loading').hide();
        }
      });
    }
  }

  // Event handler for checkbox change
  $('.bgx-checkbox-category').change(function() {
    getDeviceCategoryInput();
  });

  function changeTemplate(index, id = '') {
    let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-template') }}";
    if (id == '') {
      let value = $("#templates" + index).val();
      id = value;
    }
    $.ajax({
      url: actionUrl,
      type: "POST",
      data: {
        id: id
      },
      success: function(response) {
        let result = JSON.parse(response);
        if (result.status == 200) {
          let template = JSON.parse(result.template);
          Object.keys(template).forEach(function(key) {
            let element = $("input[name='configuration[" + index + "][" + key + "]'], select[name='configuration[" + index + "][" + key + "]']");
            if (element.is('input')) {
              element.val(template[key]['value']);
            } else if (element.is('select') && key != 'template') {
              element.val(template[key]['value']);
            }
          });
          // Handle the response data as needed
        } else {
          console.error(result.message);
        }
      },
      error: function(xhr) {
        console.log('Error:', xhr.responseText);
        // Handle the error if AJAX request fails
      },
      complete: function() {
        $('#loading').hide(); // Hide loading indicator regardless of success or error
      }
    });
  }
</script>
