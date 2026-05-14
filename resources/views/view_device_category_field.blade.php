@extends('layouts.apps')
@section('content')
@php
    $routePrefix = $url_type ?? 'admin';
    $vdfIsAdmin = Auth::check() && strcasecmp(trim((string) Auth::user()->user_type), 'admin') === 0;
@endphp
<style>
    #main-content.view-data-fields-page .wrapper { padding-top: 8px !important; }

    .view-data-fields-page .vdf-breadcrumb-wrap { padding: 4px 0 10px 0; margin: 0; }
    .view-data-fields-page .vdf-breadcrumb {
        display: inline-flex; align-items: center; flex-wrap: wrap; row-gap: 6px;
        background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }
    .view-data-fields-page .vdf-breadcrumb .bc-home {
        width: 30px; height: 30px; background: #76CF1C; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        margin-right: 10px; flex-shrink: 0; color: #1e293b; text-decoration: none;
    }
    .view-data-fields-page .vdf-breadcrumb .bc-home i { font-size: 13px; }
    .view-data-fields-page .vdf-breadcrumb .bc-item {
        color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap;
    }
    .view-data-fields-page .vdf-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
    .view-data-fields-page .vdf-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
    .view-data-fields-page .vdf-breadcrumb a.bc-item:hover { color: #e2e8f0; }

    .view-data-fields-page .vdf-breadcrumb.vdf-breadcrumb--scroll {
        max-width: 100%;
        flex-wrap: nowrap !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }

    .view-data-fields-page .vdf-breadcrumb.vdf-breadcrumb--scroll .bc-home {
        flex-shrink: 0;
    }

    .view-data-fields-page .vdf-breadcrumb-wrap + .row { margin-top: 2px; }
    .view-data-fields-page .c_panel { margin-top: 0 !important; overflow: visible !important; }
    .view-data-fields-page .c_title h2::before { content: none !important; display: none !important; }
    .view-data-fields-page .vdf-panel-title {
        display: inline-flex !important; align-items: center; gap: 8px; margin: 0;
        color: #fff !important; font-size: 15px !important; font-weight: 800 !important;
        letter-spacing: 0.5px; text-transform: uppercase;
    }
    .view-data-fields-page .vdf-panel-title > i { color: #76CF1C; font-size: 14px; }

    .view-data-fields-page .vdf-tabs {
        display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 14px 0;
    }
    .view-data-fields-page .vdf-tabs .tablinks {
        border: 1px solid #cbd5e1; background: #f8fafc; color: #334155;
        padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;
        transition: background .15s, color .15s, border-color .15s;
    }
    .view-data-fields-page .vdf-tabs .tablinks:hover { border-color: #76CF1C; color: #0f172a; }
    .view-data-fields-page .vdf-tabs .tablinks.active {
        background: #76CF1C !important; color: #fff !important; border-color: #76CF1C !important;
    }

    /* Match usable width to tabs row: full width of panel body, cancel inner col padding */
    /* theme .tab-content { padding: 25px } narrows table vs tabs — align list with tab row */
    .view-data-fields-page .c_content .tab-content.active {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
        padding-bottom: 18px !important;
    }
    .view-data-fields-page .vdf-table-wrap,
    .view-data-fields-page .vdf-table-wrap .dataTables_wrapper {
        width: 100% !important;
        max-width: 100%;
        box-sizing: border-box;
    }
    .view-data-fields-page .vdf-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        margin-top: 4px;
    }
    .view-data-fields-page .dataTables_wrapper > .row { margin-left: 0 !important; margin-right: 0 !important; }
    .view-data-fields-page .dataTables_wrapper > .row:nth-child(2) {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .view-data-fields-page .dataTables_wrapper > .row:nth-child(2) > [class*="col-"] { min-width: 0; padding-left: 0; padding-right: 0; }
    .view-data-fields-page .dataTables_wrapper .row:first-child {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding: 10px 0 12px;
        background: #fafbfc;
        border-bottom: 1px solid #e2e8f0;
    }
    .view-data-fields-page .dataTables_wrapper .row:first-child > [class*="col-"] { padding-left: 0; padding-right: 0; }
    .view-data-fields-page .dataTables_wrapper .row:first-child .dataTables_length { padding-left: 2px; }
    .view-data-fields-page .dataTables_wrapper .row:first-child .dataTables_filter { padding-right: 2px; }

    /* Bootstrap + DT use border-collapse: separate on bordered tables — breaks thead/body column alignment */
    .view-data-fields-page table.dataTable.table-bordered {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        table-layout: fixed !important;
        width: 100% !important;
    }
    .view-data-fields-page .vdf-datatable-table thead th,
    .view-data-fields-page .vdf-datatable-table tbody td,
    .view-data-fields-page table.dataTable.vdf-datatable-table thead th,
    .view-data-fields-page table.dataTable.vdf-datatable-table tbody td {
        min-width: 0;
        box-sizing: border-box;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .view-data-fields-page .vdf-datatable-table thead th {
        background: #f8fafc !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e2e8f0 !important;
        vertical-align: middle !important;
        white-space: normal;
        line-height: 1.25;
        padding: 12px 10px !important;
    }
    .view-data-fields-page .vdf-datatable-table tbody td {
        vertical-align: top !important;
        font-size: 13px;
        color: #334155;
        padding: 12px 10px !important;
        border-top: none !important;
    }
    .view-data-fields-page table.dataTable thead > tr > th {
        border-top: none !important;
    }
    .view-data-fields-page .vdf-datatable-table .vdf-badge,
    .view-data-fields-page .vdf-datatable-table .btn {
        white-space: nowrap;
    }
    .view-data-fields-page .vdf-actions-cell .vdf-actions-inner {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-sr { width: 4%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-id { width: 5%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-type { width: 10%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-name { width: 12%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-input { width: 14%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-val { width: 28%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-common { width: 8%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-can { width: 8%; }
    .view-data-fields-page .vdf-datatable-table col.vdf-w-actions { width: 11%; }
    .view-data-fields-page .vdf-badge {
        display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;
    }
    .view-data-fields-page .vdf-badge-true { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .view-data-fields-page .vdf-badge-false { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .view-data-fields-page .vdf-btn-edit,
    .view-data-fields-page .vdf-btn-delete {
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
    .view-data-fields-page .vdf-btn-edit i,
    .view-data-fields-page .vdf-btn-delete i {
        margin: 0;
        font-size: 14px;
        line-height: 1;
    }

    @media (max-width: 991px) {
        .view-data-fields-page .vdf-breadcrumb-wrap {
            padding: 2px 0 6px 0;
        }

        .view-data-fields-page .c_content {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .view-data-fields-page .vdf-page-title-row.bgx-title-container {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 12px;
        }

        .view-data-fields-page .vdf-title-actions-wrap {
            text-align: center !important;
        }

        .view-data-fields-page .vdf-title-actions-wrap .btn.btn-success {
            width: 100% !important;
            max-width: 100% !important;
            min-height: 44px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            font-size: 13px !important;
        }

        .view-data-fields-page .vdf-tabs {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
            gap: 8px !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding-bottom: 6px !important;
            scrollbar-width: thin;
        }

        .view-data-fields-page .vdf-tabs .tablinks {
            flex-shrink: 0 !important;
            white-space: nowrap !important;
        }

        .view-data-fields-page .dataTables_wrapper .row:first-child > [class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: left !important;
            margin-bottom: 10px !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .view-data-fields-page .dataTables_wrapper .row:first-child > [class*="col-"]:last-child {
            margin-bottom: 0 !important;
        }

        .view-data-fields-page .dataTables_wrapper .dataTables_filter {
            width: 100% !important;
        }

        .view-data-fields-page .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            box-sizing: border-box !important;
        }

        .view-data-fields-page .dataTables_wrapper > .row:last-child > [class*="col-"] {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            text-align: center !important;
            margin-bottom: 8px !important;
        }

        .view-data-fields-page .dataTables_wrapper > .row:last-child > [class*="col-"]:last-child {
            margin-bottom: 0 !important;
        }

        .view-data-fields-page .vdf-table-wrap {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }

        .view-data-fields-page #dataFieldsTable {
            table-layout: auto !important;
            width: auto !important;
            min-width: 1020px !important;
            max-width: none !important;
        }

        .view-data-fields-page .dataTables_wrapper > .row:nth-child(2) {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
        }
    }

    /* —— Add / Edit data field modal (site: navy + #76CF1C) —— */
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    #addDeviceField.vdf-data-field-modal .modal-dialog {
        max-width: 560px;
    }
    #addDeviceField.vdf-data-field-modal .modal-content {
        border: none;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.22);
    }
    #addDeviceField.vdf-data-field-modal .modal-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-bottom: 3px solid #76CF1C;
        padding: 16px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    #addDeviceField.vdf-data-field-modal .modal-header .modal-title,
    #addDeviceField.vdf-data-field-modal .modal-header .data-field-title {
        margin: 0;
        order: 1;
        flex: 1;
        text-align: left;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #fff !important;
    }
    #addDeviceField.vdf-data-field-modal .modal-header .modal-title strong,
    #addDeviceField.vdf-data-field-modal .modal-header .data-field-title strong {
        color: #fff !important;
        font-weight: 800 !important;
    }
    #addDeviceField.vdf-data-field-modal .modal-header .close {
        float: none;
        order: 2;
        margin: 0 0 0 12px;
        color: rgba(255, 255, 255, 0.85);
        opacity: 1;
        text-shadow: none;
        font-size: 26px;
        font-weight: 300;
        line-height: 1;
    }
    #addDeviceField.vdf-data-field-modal .modal-header .close:hover {
        color: #76CF1C;
    }
    #addDeviceField.vdf-data-field-modal .modal-body {
        padding: 0;
        background: #fff;
    }
    #addDeviceField.vdf-data-field-modal .modal-body .card {
        border: none;
        box-shadow: none;
        background: transparent;
        padding: 20px 22px 8px !important;
    }
    #addDeviceField.vdf-data-field-modal .modal-body .form-control,
    #addDeviceField.vdf-data-field-modal .modal-body select.form-control {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        font-size: 13px;
        min-height: 40px;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    #addDeviceField.vdf-data-field-modal .modal-body .form-control:focus,
    #addDeviceField.vdf-data-field-modal .modal-body select.form-control:focus {
        border-color: #76CF1C;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.15);
        outline: none;
    }
    /* Remove numeric spinners/inner divider look in modal number fields */
    #addDeviceField.vdf-data-field-modal input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }
    #addDeviceField.vdf-data-field-modal input[type=number]::-webkit-outer-spin-button,
    #addDeviceField.vdf-data-field-modal input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #addDeviceField.vdf-data-field-modal .modal-body .col-form-label {
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        padding-top: 10px;
    }
    #addDeviceField.vdf-data-field-modal .modal-body .border.rounded.bg-light {
        border-color: #e8ecf1 !important;
        background: #f8fafc !important;
        border-radius: 10px !important;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options {
        padding: 12px !important;
        margin-top: 10px !important;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options > label {
        display: block;
        margin-bottom: 8px;
        color: #334155;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .select-options-container {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 6px;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .form-row {
        margin: 0 !important;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .options-row {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .options-row > [class*="col-"] {
        float: none;
        width: auto;
        max-width: none;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .options-row > .col-lg-5 {
        flex: 1 1 0;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .options-row > .col-lg-2 {
        flex: 0 0 44px;
        width: 44px;
        display: flex;
        justify-content: center;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .remove-option {
        width: 36px;
        height: 36px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .remove-option i {
        margin: 0;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .text-right {
        margin-top: 10px;
        margin-bottom: 0 !important;
    }
    #addDeviceField.vdf-data-field-modal .append-select-options .add-option {
        border-radius: 8px;
        font-weight: 700;
        padding: 7px 12px !important;
    }
    #addDeviceField.vdf-data-field-modal .append-number-options {
        padding: 10px 12px !important;
        margin-top: 10px !important;
        margin-bottom: 8px !important;
    }
    #addDeviceField.vdf-data-field-modal .append-number-options > label {
        display: block;
        margin-bottom: 8px !important;
        color: #475569;
        font-weight: 700 !important;
        font-size: 13px;
    }
    #addDeviceField.vdf-data-field-modal .append-number-options .form-row {
        display: flex;
        margin: 0 !important;
        gap: 10px;
    }
    #addDeviceField.vdf-data-field-modal .append-number-options .form-row > [class*="col-"] {
        float: none;
        width: auto;
        max-width: none;
        flex: 1 1 0;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    #addDeviceField.vdf-data-field-modal .append-number-options .form-control {
        width: 100%;
    }
    #addDeviceField.vdf-data-field-modal .append-maxValue-options {
        padding: 10px 12px !important;
        margin-top: 10px !important;
    }
    #addDeviceField.vdf-data-field-modal .vdf-max-length-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }
    #addDeviceField.vdf-data-field-modal .vdf-max-length-row label {
        margin: 0;
        min-width: 110px;
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        white-space: nowrap;
    }
    #addDeviceField.vdf-data-field-modal .vdf-max-length-row .vdf-max-length-input {
        flex: 1 1 auto;
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-check-row {
        margin-top: 8px;
        margin-left: -4px;
        margin-right: -4px;
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-check-row > [class*="col-"] {
        padding-left: 4px !important;
        padding-right: 4px !important;
    }
    #addDeviceField.vdf-data-field-modal .vdf-check-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 8px 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        min-height: 40px;
    }
    #addDeviceField.vdf-data-field-modal .vdf-check-label {
        margin: 0;
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        flex: 1;
        line-height: 1.35;
    }
    #addDeviceField.vdf-data-field-modal .vdf-check-input {
        width: 20px;
        height: 20px;
        margin: 0 0 0 auto !important;
        display: block;
        align-self: center;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        border: 2px solid #cbd5e1;
        border-radius: 4px;
        background: #fff;
        flex-shrink: 0;
        position: relative;
        outline: none !important;
    }
    #addDeviceField.vdf-data-field-modal .vdf-check-input:checked {
        background: #76CF1C;
        border-color: #76CF1C;
    }
    #addDeviceField.vdf-data-field-modal .vdf-check-input:checked::after {
        content: "";
        position: absolute;
        left: 5px;
        top: 1px;
        width: 5px;
        height: 10px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        padding: 14px 22px 18px;
        margin: 0;
        border-top: 1px solid #e8ecf1;
        background: #fafbfc;
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-btn-back {
        background: #fff !important;
        color: #334155 !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 8px !important;
        font-weight: 600;
        font-size: 13px;
        padding: 9px 20px;
        min-width: 100px;
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-btn-back:hover {
        border-color: #76CF1C !important;
        color: #0f172a !important;
        background: #f0fce8 !important;
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-btn-submit {
        background: #76CF1C !important;
        border: none !important;
        color: #fff !important;
        border-radius: 8px !important;
        font-weight: 700;
        font-size: 13px;
        padding: 9px 22px;
        min-width: 110px;
        box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
    }
    #addDeviceField.vdf-data-field-modal .vdf-modal-btn-submit:hover {
        background: #65b515 !important;
        color: #fff !important;
    }
</style>
@include('modals.userEditDelOptions')
<form class="delUserResellerForm" data-action="/{{$url_type}}/delete-user/" action="" method="post">
  @csrf
  @method('DELETE')
  <div class="userAccCases">
  </div>
</form>
<div class="modal vdf-data-field-modal" id="addDeviceField" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title data-field-title"><strong>ADD Data Field</strong></h4>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="deviceFieldForm" onsubmit="return false;">
        @csrf
        <div class="modal-body">
          <div class="card p-4">
            <div class="form-group">
              <!-- Field Type -->
              <div class=" row mb-3 margin-bottom-10 mb-3 row">
                <label class="col-lg-4 col-form-label font-weight-bold">Field Type <span
                    class="text-danger">*</span></label>
                <div class="col-lg-8">
                  <select name="field_type" id="field_type" class="form-control">
                    <!--<option value="">Select Field Type</option>-->
                    <option value="0">Configurations</option>
                    <option value="1">Parameters</option>
                  </select>
                </div>
              </div>
              <!-- Field Name -->
              <div class="row mb-3 margin-bottom-10 mb-3 show-field-name" style="display:none;">
                <label class="col-lg-4 col-form-label font-weight-bold">Field Name <span
                    class="text-danger">*</span></label>
                <div class="col-lg-8">
                  <input type="text" name="field_name" id="field_name" class="form-control"
                    placeholder="Enter field name">
                </div>
              </div>
              <!-- Input Type -->
              <div class="show-input-type" style="display:none;">
                <div class="row mb-3 margin-bottom-10 mb-3 row ">
                  <label class="col-lg-4 col-form-label font-weight-bold">Input Type <span
                      class="text-danger">*</span></label>
                  <div class="col-lg-8">
                    <select name="input_type" id="input_type" class="form-control inputType">
                      <!--<option value="">Select Input Type</option>-->
                      <option value="text">Text</option>
                      <option value="number">Number</option>
                      <option value="select">Select</option>
                      <option value="IP/URL">IP/URL</option>
                      <option value="multiselect">MultiSelect</option>
                      <option value="text_array">Text Array</option>
                      <option value="hex">Hex</option>
                    </select>
                  </div>
                </div>
                <div class="append-number-options border p-3 rounded bg-light mt-3 margin-bottom-3"
                  style="display:none;">
                  <label class="font-weight-bold mb-2 d-block">Number Range <span class="text-danger">*</span></label>
                  <div class="form-row align-items-center">
                    <div class="col-lg-6">
                      <input type="number" class="form-control" placeholder="Min" name="numberInput[min]" />
                    </div>
                    <div class="col-lg-6">
                      <input type="number" class="form-control" placeholder="Max" name="numberInput[max]" />
                    </div>
                  </div>
                </div>
                <div class="max-selected-values form-group" style="display:none;"></div>

                <div class="append-maxValue-options border p-3 rounded bg-light mt-3" style="display:none;">
                  <div class="form-group mb-0 vdf-max-length-row">
                    <label class="font-weight-bold">Max Length <span class="text-danger">*</span></label>
                    <input type="number" class="form-control vdf-max-length-input" placeholder="Enter maximum length"
                      name="maxValueInput[0][]" />
                  </div>
                </div>

                <div class="append-select-options border p-3 rounded bg-light mt-3" style="display:none;">
                  <label class="font-weight-bold">Select Options <span class="text-danger">*</span></label>
                  <div class="select-options-container">
                    <div class="form-row align-items-center mb-2  margin-bottom-10">
                      <div class="options-row">
                        <div class="col-lg-5">
                          <input type="text" class="form-control" placeholder="Enter Option"
                            name="selectOptions[0][]" />
                        </div>
                        <div class="col-lg-5">
                          <input type="text" class="form-control" placeholder="Enter Value" name="selectValues[0][]" />
                        </div>
                        <div class="col-lg-2">
                          <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i
                              class="fa fa-times"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="text-right margin-bottom-15">
                    <button type="button" class="btn btn-outline-success btn-sm add-option"><i class="fa fa-plus"></i>
                      Add Option</button>
                  </div>
                </div>


                <!-- Text Array Options (Conditional) -->
                <div class="form-group mt-3" id="selectOptionsGroup" style="display:none;">
                  <div class="options-row">
                    <label class="font-weight-bold">Options</label>
                    <div id="selectOptionsContainer">
                      <div class="input-group mb-2">
                        <input type="text" name="options[]" class="form-control" placeholder="Option">
                        <div class="input-group-append">
                          <button type="button" class="btn btn-outline-danger remove-option"><i
                              class="fa fa-times"></i></button>
                        </div>
                      </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addOptionBtn"><i
                        class="fa fa-plus"></i> Add Option</button>
                  </div>
                </div>

              </div>
              <div class="margin-bottom-10 mb-3 show-on-select" style="display:none;">
                <div class="row vdf-modal-check-row">
                  <div class="col-sm-6 col-xs-12 mb-2 mb-sm-0">
                    <div class="vdf-check-wrap">
                      <label class="vdf-check-label" for="is_common">Common Field <span class="text-danger">*</span></label>
                      <input type="checkbox" name="is_common" id="is_common" class="vdf-check-input is_common" value="1">
                    </div>
                  </div>
                  <div class="col-sm-6 col-xs-12">
                    <div class="vdf-check-wrap">
                      <label class="vdf-check-label" for="is_can_protocol">Can Protocol Field <span class="text-danger">*</span></label>
                      <input type="checkbox" name="is_can_protocol" id="is_can_protocol" class="vdf-check-input is_can_protocol" value="1">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer vdf-modal-footer">
          <button type="button" data-dismiss="modal" aria-hidden="true" class="btn vdf-modal-btn-back">Back</button>
          <button class="btn vdf-modal-btn-submit submitDataErr" type="submit">Submit</button>
          <input type="hidden" name="dataFieldId" id="dataFieldId" value="" />
        </div>
      </form>
    </div>
  </div>
</div>
<section id="main-content" class="view-data-fields-page">
  <section class="wrapper">
    <div class="vdf-breadcrumb-wrap">
      <nav class="vdf-breadcrumb vdf-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="{{ url($routePrefix . '/view-device-category') }}" class="bc-item">Device category</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Data fields</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container vdf-page-title-row">
              <div class="col-xs-12 col-lg-6 col-md-12">
                <h2 class="vdf-panel-title"><i class="fa fa-list-alt"></i> Data fields</h2>
              </div>
              @if ($vdfIsAdmin || (Auth::check() && strcasecmp(trim((string) Auth::user()->user_type), 'reseller') === 0))
                <div class="col-xs-12 col-lg-6 col-md-12 text-right vdf-title-actions-wrap">
                  <button type="button" class="btn btn-success" onclick="openAddDeviceFieldModel()"><i class="fa fa-plus"></i> Add data fields</button>
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

            <!-- <div class="tabs">
              <button class="tablinks active" onclick="openTab(event, 'all')">ALL</button>
              <button class="tablinks" onclick="openTab(event, 'cat')">Configurations</button>
              <button class="tablinks" onclick="openTab(event, 'param')">Parameters</button>
            </div> -->

            <div class="tabs vdf-tabs">
              <button type="button" class="tablinks active" onclick="reloadPage(this)" data-type="all">All</button>
              <button type="button" class="tablinks" data-type="Configurations">Configurations</button>
              <button type="button" class="tablinks" data-type="Parameters">Parameters</button>
            </div>

            <div id="all" class="tab-content active">
              <div class="vdf-table-wrap">
                    <table id="dataFieldsTable"
                        class="table table-bordered table-striped vdf-datatable-table"
                        style="width:100%; font-size:14px;">
                  <colgroup>
                    <col class="vdf-w-sr" />
                    <col class="vdf-w-id" />
                    <col class="vdf-w-type" />
                    <col class="vdf-w-name" />
                    <col class="vdf-w-input" />
                    <col class="vdf-w-val" />
                    <col class="vdf-w-common" />
                    <col class="vdf-w-can" />
                    <col class="vdf-w-actions" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th>Sr. No.</th>
                      <th>Field ID</th>
                      <th>Field Type</th>
                      <th>Field Name</th>
                      <th>Input Type</th>
                      <th>Validation Rule</th>
                      <th>Common Field</th>
                      <th>Can Protocol</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($dataFields as $i => $dataField)
                      <tr>
                        <td>{{$i + 1}}</td>
                        <td>{{$dataField->id}}</td>
                        <!-- <td>{{$dataField->fieldType == 0 ? 'Configurations' : 'Parameters'}}</td> -->
                        <td class="field-type">{{$dataField->fieldType == 0 ? 'Configurations' : 'Parameters'}}</td>
                        <td>{{$dataField->fieldName}}</td>
                        <td>{{$dataField->inputType}}</td>
                        <td>{{$dataField->validationConfig}}</td>
                        <td>
                          @if ($dataField->is_common)
                            <span class="vdf-badge vdf-badge-true">True</span>
                          @else
                            <span class="vdf-badge vdf-badge-false">False</span>
                          @endif
                        </td>
                        <td>
                          @if ($dataField->is_can_protocol)
                            <span class="vdf-badge vdf-badge-true">True</span>
                          @else
                            <span class="vdf-badge vdf-badge-false">False</span>
                          @endif
                        </td>
                        <td class="vdf-actions-cell text-center">
                          <div class="vdf-actions-inner">
                            @if($vdfIsAdmin)
                              <button type="button" class="btn btn-primary btn-sm vdf-btn-edit" data-id="{{ $dataField->id }}"
                                data-field-type="{{ $dataField->fieldType }}" data-field-name="{{ $dataField->fieldName }}"
                                data-input-type="{{ $dataField->inputType }}" data-config='@json($dataField->validationConfig)'
                                data-is_common="{{$dataField->is_common}}"
                                data-is_can_protocol="{{$dataField->is_can_protocol}}"
                                title="Edit" aria-label="Edit"
                                onclick="openEditModel(this)"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                            @endif
                            <form id="deleteForm-{{$dataField->id}}" action="" method="post" class="form-inline" style="display:inline;">
                              @csrf
                              @method('DELETE')
                              <button type="button" class="btn btn-danger btn-sm vdf-btn-delete"
                                title="Delete" aria-label="Delete"
                                onclick="showDeleteModal({{$dataField->id}})"><i class="fa fa-trash" aria-hidden="true"></i></button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @foreach ($dataFields as $dataField)
              <div class="modal" id="deleteModal{{$dataField->id}}" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header d-flex" style="justify-content: space-between;">
                      <h5 class="modal-title" id="deleteModalLabel">Confirm delete</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      Are you sure you want to delete this data field?
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                      <button type="button" class="btn btn-danger"
                        onclick="confirmDelete({{$dataField->id}},true)">Delete</button>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            <div id="cat" class="tab-content">
              <p>This is Tab 2 content.</p>
            </div>
            <div id="param" class="tab-content">
              <p>This is Tab 3 content.</p>
            </div>


          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>




<!--****** End Modal Responsive******-->
@stop

@section('scripts')
<script>
  // function openTab(evt, tabName) {
  //   console.log("tabName ===>", tabName);
  //   const tabContents = document.querySelectorAll(".tab-content");
  //   tabContents.forEach(content => content.classList.remove("active"));

  //   const tabLinks = document.querySelectorAll(".tablinks");
  //   tabLinks.forEach(link => link.classList.remove("active"));

  //   document.getElementById(tabName).classList.add("active");

  //   evt.currentTarget.classList.add("active");
  // }

  function showDeleteModal(id) {
    $('#deleteModal' + id).modal('show');
  }

  function openEditModel(button) {
    $('#field_type').val("");
    $('#field_name').val("");
    $('#is_common').prop("checked", false);
    $('#input_type').val('').trigger('change');
    const $btn = $(button);
    const fieldData = {
      id: $btn.data('id'),
      field_type: $btn.data('field-type'),
      field_name: $btn.data('field-name'),
      input_type: $btn.data('input-type'),
      config: $btn.data('config') || {},
      is_common: $btn.data('is_common'),
      is_can_protocol: $btn.data('is_can_protocol'),
    };
    if (typeof fieldData.config === 'string') {
      try { fieldData.config = JSON.parse(fieldData.config); } catch (e) { fieldData.config = {}; }
    }
    if (!fieldData.config || typeof fieldData.config !== 'object') {
      fieldData.config = {};
    }
    const $form = $('#deviceFieldForm');
    $('.data-field-title').text("Edit Data Field");
    // Reset form and show modal
    $('#deviceFieldForm')[0].reset();
    $('#deviceFieldForm').attr('data-mode', 'edit');
    $('#formModeTitle').text('EDIT Data Field');
    $('#dataFieldId').val(fieldData.id);
    $('#addDeviceField').modal('show');
    $('.show-field-name').show();
    // Show base fields
    $('.show-on-select').show();
    if (fieldData.field_type == "0") {
      //     $('.show-field-name').show();
      // } else{
      $('.show-input-type').show();
    } else {
      $('.show-input-type').hide();
    }
    $('.max-selected-values').hide();
    // Set basic values
    $('#field_type').val(fieldData.field_type);
    $('#field_name').val(fieldData.field_name);
    $('#is_common').prop("checked", fieldData.is_common == 1);
    $('#is_can_protocol').prop("checked", fieldData.is_can_protocol == 1);
    $('#input_type').val(fieldData.input_type).trigger('change');

    // Hide all dynamic input sections
    $('.append-number-options, .append-maxValue-options, .append-select-options, #selectOptionsGroup').hide();

    // Load configuration values based on input_type
    const config = fieldData.config;

    switch (fieldData.input_type) {
      case 'select':
        $('.select-options-container').empty();
        const options = config.selectOptions || [];
        const values = config.selectValues || [];

        options.forEach((option, index) => {
          $('.select-options-container').append(`
                        <div class="options-row">
                        <div class="form-row align-items-center mb-2 ">
                            <div class="col-lg-5">
                                <input type="text" class="form-control" name="selectOptions[0][]" value="${option}" />
                            </div>
                            <div class="col-lg-5">
                                <input type="text" class="form-control" name="selectValues[0][]" value="${values[index] || ''}" />
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        </div>
                    `);
        });
        $('.append-select-options').show();
        break;
      case 'multiselect':
        $('.select-options-container').empty();
        $('.max-selected-values').empty();
        const options1 = config.selectOptions || [];
        const values1 = config.selectValues || [];

        options1.forEach((option, index) => {
          $('.select-options-container').append(`
              <div class="options-row">
                <div class="form-row align-items-center mb-2 ">
                    <div class="col-lg-5">
                        <input type="text" class="form-control" name="selectOptions[0][]" value="${option}" />
                    </div>
                    <div class="col-lg-5">
                        <input type="text" class="form-control" name="selectValues[0][]" value="${values1[index] || ''}" />
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option"><i class="fa fa-times"></i></button>
                    </div>
                </div>
              </div>
          `);
        });

        $('.append-select-options').show();
        $('.max-selected-values').append(`<div class="row mb-3 margin-bottom-10 mb-3 show-field-name">
          <label class="col-lg-4 col-form-label font-weight-bold">Max Length <span class="text-danger">*</span></label>
          <div class="col-lg-8">
            <input type="number" name="maxSelectValue[0]" value="${config.maxSelectValue}" id="field_name" class="form-control">
          </div>
        </div>`).show();
        break;
      case 'number':
        $('[name="numberInput[min]"]').val(config.numberInput?.min ?? '');
        $('[name="numberInput[max]"]').val(config.numberInput?.max ?? '');
        $('.append-number-options').show();
        break;

      case 'text':
      case 'IP/URL':
      case 'text_array':
        $('[name="maxValueInput[0][]"]').val(config.maxValueInput ?? '');
        $('.append-maxValue-options').show();
        break;
    }
  }

  function confirmDelete(id) {
    const form = document.getElementById('deleteForm-' + id);
    form.action = "{{ url($routePrefix . '/delete-category-fields') }}/" + id;
    form.submit();
  }

  $(document).ready(function () {
    $(document).on("change", '#field_type', function () {
      const value = $(this).val();
      $(".show-field-name").show();
      $(".show-on-select").show();
      if (value == 1) {
        $(".show-input-type").hide();
      } else {
        $(".show-input-type").show();
      }
    });
    $("#deviceFieldForm").submit(function () {
      var form = $(this);
      $.ajax({
        url: '/admin/device-data-field',
        type: 'POST',
        data: form.serialize(),
        success: function (response) {
          let result = response;
          console.log("result", result);
          if (result.status == 200) {
            $('#addDeviceField').modal('hide');
            //   $(".error_msg").append(result.message).show();
            //   $('#deviceCategoryDelOptionModal' + id).modal("hide");
            //   document.documentElement.scrollIntoView({
            //     behavior: 'smooth',
            //     block: 'start'
            //   });
            window.location.reload();
          }
          return false
        },
        error: function (xhr, status, error) {
          alert("An error occurred: " + error);
        }
      });
    });
    $(document).on("change", ".inputType", function () {
      const selectedType = $(this).val();
      const $formGroup = $(this).closest(".form-group");
      var maxvalOptions = $(this).closest(".form-group").find('.append-maxValue-options');


      const $appendSelectOptions = $formGroup.find(".append-select-options");
      $appendSelectOptions.show();
      const $inputOptions = $formGroup.find('.append-number-options');
      const $defaultValue = $formGroup.find('input[name^="default["]');
      var defaultVal = $(this).closest(".form-group").find(".default-val");
      $defaultValue.attr('type', 'text');
      $inputOptions.find('input').attr('required', false);
      $appendSelectOptions.find('input').attr('required', false);

      if (selectedType === "select") {
        $appendSelectOptions.show();
        $appendSelectOptions.find('input').attr('required', true);
        $inputOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
        maxvalOptions.hide();
      } else if (selectedType == "multiselect") {
        $appendSelectOptions.show();
        $appendSelectOptions.find('input').attr('required', true);
        $inputOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
        maxvalOptions.hide();
      } else if (selectedType === 'number') {
        $defaultValue.attr('type', 'number');
        $inputOptions.show();
        $inputOptions.find('input').attr('required', true);
        $appendSelectOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
        maxvalOptions.hide();
      } else if (selectedType == 'IP/URL') {
        $appendSelectOptions.hide();
        defaultVal.addClass("ip-url-space no-space-allowed");
        maxvalOptions.show();
        $inputOptions.hide();
      } else if (selectedType == 'text_array') {
        $appendSelectOptions.hide();
        defaultVal.addClass("text-array-space");
        maxvalOptions.show();
        $inputOptions.hide();
      } else {
        $appendSelectOptions.hide();
        maxvalOptions.show();
        $inputOptions.hide();
        defaultVal.removeClass("ip-url-space");
        defaultVal.removeClass("text-array-space");
      }
    });
    $(".no-space-allowed").on("keydown", function (event) {
      if (event.key === " ") {
        event.preventDefault(); // Prevent space
      }
    });
    $(document).on("keydown", ".ip-url-space", function (event) {
      const key = event.key;
      console.log("Pressed key:", key);

      if (key === " ") {
        event.preventDefault();
        return false;
      }

      if (
        event.ctrlKey || event.metaKey ||
        key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" ||
        key === "Delete" || key === "Tab" || key === "Enter"
      ) {
        return;
      }

      const allowed = /^[a-zA-Z0-9.]$/;
      if (!allowed.test(key)) {
        event.preventDefault();
        return false;
      }
    });
    $(document).on("paste", ".ip-url-space", function (event) {
      const pastedData = event.originalEvent.clipboardData.getData('text');
      const allowed = /^[a-zA-Z0-9.]*$/;

      if (!allowed.test(pastedData)) {
        event.preventDefault();
        alert("Pasted content contains invalid characters.");
      }
    });
    $(document).on("click", ".remove-option", function () {
      $(this).closest(".options-row").remove();
    });
    $(document).on("keydown", ".text-array-space", validateTextArrayInput);

    function validateTextArrayInput(event) {
      const key = event.key;
      console.log("Pressed key in text-array-space:", key);

      // Block space
      if (key === " ") {
        event.preventDefault();
        return;
      }

      const allowed = /^[a-zA-Z0-9.,{}]$/;



      // Allow control/navigation keys
      if (
        event.ctrlKey || event.metaKey ||
        key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" ||
        key === "Delete" || key === "Tab" || key === "Enter"
      ) {
        return;
      }

      // Block any key not in the allowed set
      if (!allowed.test(key)) {
        event.preventDefault();
      }
    }
    $(document).on("paste", ".text-array-space", function (event) {
      const pastedData = event.originalEvent.clipboardData.getData('text');
      const allowed = /^[a-zA-Z0-9.,{}]*$/;
      if (event.key === " ") {
        event.preventDefault();
        return;
      }
      if (!allowed.test(pastedData)) {
        event.preventDefault();
        alert("Pasted content contains invalid characters.");
      }
    });
  });

  function openAddDeviceFieldModel() {
    $('#addDeviceField').modal('show');
    $('.data-field-title').text("ADD Data Field");
    $('#field_type').val("0").trigger('change');
    $('#field_name').val("");
    $('#input_type').val("text").trigger('change');
    $('#is_common').prop("checked", false);
    $('#is_can_protocol').prop("checked", false);
    $('.append-maxValue-options').hide();
    $(".append-select-options").hide();
    $('.append-number-options').hide();
    $('.max-selected-values').hide().empty();
  }
  document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-target');

      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });

      // Remove active class from all buttons
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });

      // Show the clicked tab
      document.getElementById(targetId).classList.add('active');
      button.classList.add('active');
    });
  });

  function open_asign(id) {
    $("#auser_id").val(id);
    $("#modal-responsive" + id).modal('show');
  };

  function openConfigurations(id) {
    $("#view-Configurations" + id).modal('show');
    $("#configuration" + id).dataTable();
    $("#configuration" + id + "_wrapper").css({
      'text-align': 'left'
    });
    $('.select2').select();
  }

  $(document).on("click", ".add-option", function () {
    var inputCount = $(this).data("inputcount");

    var optionsHtml =
      '<div class="form-row align-items-center  margin-bottom-10">' +
      '<div class="options-row">' +
      '<div class="col-lg-5">' +
      '<input type="text" class="form-control" placeholder="Enter Option" name="selectOptions[0][]">' +
      '</div>' +
      '<div class="col-lg-5">' +
      '<input type="text" class="form-control" placeholder="Enter Value" name="selectValues[0][]">' +
      '</div>' +
      '<div class="col-lg-2">' +
      '<button type="button" class="btn btn-outline-danger btn-sm remove-option">' +
      '<i class="fa fa-times"></i>' +
      '</button>' +
      '</div>' +
      '</div>' +
      '</div>';
    $(this).closest(".form-group").find(".select-options-container").append(optionsHtml);
  });
  $(document).ready(function () {

    $('.assignDevices').each(function () {
      // Get the ID of each element
      var id = $(this).attr('id');

      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });
  });

  function togglePasswordShow(id) {
    $("#hide-" + id).hide();
    $("#showpassword-" + id).show();
  }


  var vdfDataTable;
  $(document).ready(function () {
    if (!window.jQuery || !$.fn.DataTable) return;
    var $dt = $('#dataFieldsTable');
    if (!$dt.length) return;
    if ($.fn.dataTable.isDataTable($dt)) {
      $dt.DataTable().destroy();
    }
    $.fn.dataTable.ext.errMode = 'none';
    vdfDataTable = $dt.DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      scrollX: false,
      autoWidth: false,
      pageLength: 10,
      stripeClasses: [],
      lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
      initComplete: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      }
    });
    setTimeout(function () {
      $dt.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search data fields...');
      if (vdfDataTable) {
        try { vdfDataTable.columns.adjust(); } catch (e) { /* noop */ }
      }
    }, 150);
  });

  $(document).on('click', '.vdf-tabs .tablinks', function () {
    if ($(this).attr('onclick')) {
      return;
    }
    var type = $(this).data('type');
    $('.vdf-tabs .tablinks').removeClass('active');
    $(this).addClass('active');
    if (!vdfDataTable) return;
    if (!type || type === 'all') {
      vdfDataTable.column(2).search('').draw();
    } else {
      vdfDataTable.column(2).search('^' + type + '$', true, false).draw();
    }
  });

  function reloadPage(el) {
    $('.vdf-tabs .tablinks').removeClass('active');
    $(el).addClass('active');
    location.reload();
  }
</script>
@endsection

