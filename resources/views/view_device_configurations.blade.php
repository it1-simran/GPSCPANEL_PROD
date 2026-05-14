<?php

use App\DeviceCategory;
use App\Helper\CommonHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$configurations = json_decode($device['configurations'], true);

$canConfigurations = $device['can_configurations'] != "" ? json_decode($device['can_configurations'], true) : [];
$getCanEnableByDeviceCategory = DeviceCategory::select('is_can_protocol')->where('id', $device['device_category_id'])->first();
$parameters = json_decode($device['parameters'], true);
$assignToIds = $device['assign_to_ids'];
$assignToIdsArray = array_filter(explode(',', $assignToIds));
$assignToIdsArray = array_values($assignToIdsArray); // ensure proper indexing
$loggedInUserId = auth()->id(); // or your user ID variable
$nextId = null;

if (!empty($assignToIdsArray)) {
    $index = array_search($loggedInUserId, $assignToIdsArray);
    
    // Bridge logic for Admin to bridge legacy Support assignments
    if ($index === false && Auth::user()->user_type == 'Admin') {
        $rootId = $assignToIdsArray[0];
        $rootWriter = DB::table('writers')->select('user_type')->where('id', $rootId)->first();
        if ($rootWriter && $rootWriter->user_type == 'Support') {
            $index = 0; // We resolve starting from the Support account's index
        }
    }

    if ($index !== false) {
        if (isset($assignToIdsArray[$index + 1])) {
            $nextId = $assignToIdsArray[$index + 1];
        } else {
            $nextId = $device['user_id']; 
        }
    } else {
        // Ultimate fallback for end users (like Dealers) or broken chains
        $nextId = $device['user_id'];
    }
} else {
    $nextId = $device['user_id'];
}

// Reseller view tweak: If the device is currently resting with the Reseller (not assigned further), 
// it should show "Unassigned" instead of their own name.
if ($nextId == auth()->id() && Auth::user()->user_type == 'Reseller') {
    $nextId = null;
}



$getFirmwareFromConfigurations = isset($configurations['firmware_id']['value']) ? DB::table('firmware')->where(['id' => $configurations['firmware_id']['value']])->first() : null;
if ($getFirmwareFromConfigurations != null) {
    $firmwareConfiguration = json_decode($getFirmwareFromConfigurations->configurations, true);
    $firmwareName = $getFirmwareFromConfigurations->name ?? '--';
    $filename = $firmwareConfiguration['filename'] ?? "--";
    $fileVersion = $firmwareConfiguration['version'] ?? "--";
    $fileSize = $firmwareConfiguration['fileSize'] ?? "--";
}

$errors = json_decode($device['errors'], true);
?>
@extends('layouts.apps')

@push('styles')
<style>
    #main-content { padding-top: 70px !important; margin-top: 0 !important; }
    #main-content .wrapper { padding-top: 0 !important; }

    .vdc-shell { padding: 0 8px 16px; }
    .vdc-breadcrumb-wrap { margin: 6px 0 18px; }
    .vdc-breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%);
        border: 1px solid rgba(118, 207, 28, 0.24);
        border-radius: 999px;
        padding: 7px 14px 7px 8px;
        box-shadow: 0 8px 20px rgba(2, 8, 23, 0.2);
    }
    .vdc-breadcrumb .bc-home {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #76CF1C;
        color: #0f172a;
        text-decoration: none;
        font-size: 12px;
        box-shadow: 0 0 0 4px rgba(118, 207, 28, 0.14);
    }
    .vdc-breadcrumb .bc-item,
    .vdc-breadcrumb .bc-item a {
        font-size: 12.5px;
        color: rgba(226, 232, 240, 0.84);
        font-weight: 700;
        text-decoration: none;
        line-height: 1;
    }
    .vdc-breadcrumb .bc-item.active { color: #76CF1C; }
    .vdc-breadcrumb .bc-sep {
        color: rgba(148, 163, 184, 0.9);
        font-size: 16px;
        line-height: 1;
    }

    .vdc-main-card {
        border: 0 !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.1) !important;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .vdc-main-card .header-custom {
        background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%) !important;
        border: none !important;
        padding: 16px 24px !important;
    }
    .vdc-main-card .header-custom h4 {
        margin: 0 !important;
        color: #fff !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        letter-spacing: .2px;
    }
    .vdc-main-card .body-custom { padding: 18px 20px !important; background: #f8fafc; }

    .vdc-main-card .user-info {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 14px 10px;
        margin-bottom: 16px;
        box-shadow: 0 3px 14px rgba(15, 23, 42, 0.04);
    }
    .vdc-main-card .user-info h4 {
        margin: 0 0 12px !important;
        font-size: 14px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .vdc-main-card .user-info h4::before {
        content: "";
        width: 4px;
        height: 18px;
        background: linear-gradient(180deg, #76CF1C, #4fa812);
        border-radius: 4px;
        display: inline-block;
    }

    .vdc-main-card .bgx-table-cell {
        border: 1px solid #edf1f6 !important;
        background: #f8fafc !important;
        border-radius: 8px !important;
        color: #334155 !important;
        font-size: 12.5px !important;
        padding: 10px 12px !important;
    }
    .vdc-main-card .bgx-table-cell strong {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #0f172a;
        margin-bottom: 3px;
    }

    .vdc-main-card .btn {
        border-radius: 8px !important;
        font-weight: 700 !important;
        letter-spacing: .1px;
    }
    .vdc-main-card .btn-primary {
        background: linear-gradient(135deg, #1e293b, #2d3f55) !important;
        border: none !important;
    }
    .vdc-main-card .btn-secondary {
        background: #e2e8f0 !important;
        border: 1px solid #cbd5e1 !important;
        color: #1e293b !important;
    }
    .vdc-main-card #span1,
    .vdc-main-card #span2,
    .vdc-main-card #span3 {
        border-radius: 8px !important;
        border: 0 !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
        padding: 7px 12px !important;
        font-weight: 700;
    }
    .vdc-main-card #map {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(15,23,42,.08);
    }

    .vdc-main-card .form-control,
    .vdc-main-card select {
        border: 1px solid #d5deea !important;
        border-radius: 7px !important;
        min-height: 38px !important;
        box-shadow: none !important;
    }
    .vdc-main-card .form-control:focus,
    .vdc-main-card select:focus {
        border-color: #76CF1C !important;
        box-shadow: 0 0 0 3px rgba(118,207,28,.12) !important;
    }

    @media (max-width: 768px) {
        .vdc-shell { padding-left: 2px; padding-right: 2px; }
        .vdc-breadcrumb-wrap { margin-top: 2px; }
        .vdc-breadcrumb {
            width: 100%;
            border-radius: 12px;
            overflow-x: auto;
            white-space: nowrap;
        }
    }

    /* ===== Premium Pass (higher visual depth) ===== */
    .vdc-shell {
        background:
            radial-gradient(1200px 420px at -5% -20%, rgba(118, 207, 28, 0.08), transparent 55%),
            radial-gradient(800px 280px at 115% -10%, rgba(37, 99, 235, 0.08), transparent 48%);
        border-radius: 16px;
    }

    .vdc-main-card .body-custom {
        background:
            linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
    }

    .vdc-main-card .user-info {
        position: relative;
        border: 1px solid rgba(148, 163, 184, 0.26);
        box-shadow:
            0 10px 30px rgba(15, 23, 42, 0.07),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
        overflow: hidden;
    }
    .vdc-main-card .user-info::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(120deg, rgba(255,255,255,0.3), rgba(255,255,255,0) 40%);
    }

    .vdc-main-card .bgx-table-row {
        gap: 10px !important;
        margin-bottom: 8px !important;
    }
    .vdc-main-card .bgx-table-cell {
        background: #ffffff !important;
        border: 1px solid #e8edf3 !important;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.03);
    }

    .vdc-main-card .view_user_table,
    .vdc-main-card .table {
        border-radius: 10px;
        overflow: hidden;
    }
    .vdc-main-card .view_user_table thead th,
    .vdc-main-card .table thead th {
        background: linear-gradient(90deg, #0f172a, #1d283e) !important;
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: .35px;
        border-color: #243246 !important;
    }
    .vdc-main-card .view_user_table tbody td,
    .vdc-main-card .table tbody td {
        border-color: #eef2f7 !important;
        color: #334155 !important;
        font-size: 12.5px;
        background: #fff !important;
    }
    .vdc-main-card .view_user_table tbody tr:hover td,
    .vdc-main-card .table tbody tr:hover td {
        background: rgba(118, 207, 28, 0.05) !important;
    }

    .vdc-main-card .btn-primary,
    .vdc-main-card .btn-info,
    .vdc-main-card .btn-success {
        border: none !important;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.18);
    }
    .vdc-main-card .btn-primary:hover,
    .vdc-main-card .btn-info:hover,
    .vdc-main-card .btn-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.22);
    }
    .vdc-main-card .btn-success {
        background: linear-gradient(135deg, #76CF1C, #5eb214) !important;
        color: #0f172a !important;
    }
    .vdc-main-card .btn-info {
        background: linear-gradient(135deg, #1e293b, #2d3f55) !important;
    }

    .vdc-main-card .edit-device-btn,
    .vdc-main-card .edit-btn,
    .vdc-main-card .edit-config-btn {
        min-width: 120px;
        padding: 9px 16px !important;
    }

    .vdc-main-card .form-group > .control-label {
        color: #0f172a !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        letter-spacing: .2px;
    }
    .vdc-main-card .form-group {
        margin-bottom: 11px !important;
    }
    .vdc-main-card .bg-margin-top {
        border-top: 1px solid #e8edf3 !important;
        margin-top: 12px !important;
        padding-top: 12px !important;
    }

    .vdc-main-card .select2-container .select2-selection--single,
    .vdc-main-card .select2-container .select2-selection--multiple {
        border: 1px solid #d5deea !important;
        border-radius: 7px !important;
        min-height: 38px !important;
    }

    .vdc-main-card .alert {
        border-radius: 9px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(2, 8, 23, 0.08);
    }

    /* ===== New Way: Segmented + Enterprise Dashboard look ===== */
    .vdc-shell {
        background: #eef3f9;
        border: 1px solid #dde5ef;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }
    .vdc-breadcrumb {
        border-radius: 14px;
        padding: 8px 10px;
        gap: 6px;
        background: #0f172a;
        border: 1px solid #223045;
    }
    .vdc-breadcrumb .bc-item,
    .vdc-breadcrumb .bc-item a,
    .vdc-breadcrumb .bc-item.active {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 8px;
    }
    .vdc-breadcrumb .bc-item,
    .vdc-breadcrumb .bc-item a {
        background: rgba(255,255,255,0.05);
        color: rgba(226,232,240,.9);
    }
    .vdc-breadcrumb .bc-item.active {
        background: linear-gradient(135deg, #76CF1C, #67bb19);
        color: #0f172a;
        font-weight: 800;
    }
    .vdc-breadcrumb .bc-sep { opacity: .55; }

    .vdc-main-card {
        border-radius: 14px !important;
        border: 1px solid #dbe5ef !important;
        box-shadow: 0 12px 30px rgba(15,23,42,.09) !important;
    }
    .vdc-main-card .header-custom {
        padding: 14px 18px !important;
        position: relative;
    }
    .vdc-main-card .header-custom::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 3px;
        background: linear-gradient(90deg, #76CF1C, #a7e95d, #76CF1C);
    }
    .vdc-main-card .body-custom {
        padding: 14px !important;
        background: #f6f9fc !important;
    }

    .vdc-main-card .user-info {
        border-radius: 12px;
        border: 1px solid #dfe7f1;
        background: #ffffff;
        padding: 12px;
        box-shadow: 0 3px 14px rgba(15,23,42,.06);
    }
    .vdc-main-card .user-info h4 {
        background: linear-gradient(90deg, #f8fbff, #f2f7fd);
        border: 1px solid #e3ebf4;
        border-radius: 9px;
        padding: 8px 10px;
        margin-bottom: 12px !important;
        font-size: 13px !important;
        text-transform: uppercase;
        letter-spacing: .35px;
    }
    .vdc-main-card .user-info h4::before {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #76CF1C;
    }
    .vdc-main-card .user-info::after { display: none; }

    .vdc-main-card .bgx-table-cell {
        border-radius: 10px !important;
        border: 1px solid #e4ebf4 !important;
        background: linear-gradient(180deg, #ffffff, #fbfdff) !important;
        box-shadow: none;
    }
    .vdc-main-card .bgx-table-cell strong {
        color: #1e293b;
        font-size: 10.5px;
        letter-spacing: .45px;
    }

    .vdc-main-card .view_user_table,
    .vdc-main-card .table {
        border: 1px solid #e2eaf3;
        border-radius: 10px;
    }
    .vdc-main-card .view_user_table thead th,
    .vdc-main-card .table thead th {
        background: #f3f7fc !important;
        color: #475569 !important;
        border-bottom: 1px solid #dce6f0 !important;
    }
    .vdc-main-card .view_user_table tbody tr:nth-child(even) td,
    .vdc-main-card .table tbody tr:nth-child(even) td {
        background: #fcfdff !important;
    }
    .vdc-main-card .view_user_table tbody tr:hover td,
    .vdc-main-card .table tbody tr:hover td {
        background: rgba(118, 207, 28, 0.09) !important;
    }

    .vdc-main-card .btn {
        border-radius: 9px !important;
        box-shadow: none !important;
        transition: all .2s ease;
    }
    .vdc-main-card .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 16px rgba(15,23,42,.16) !important;
    }
    .vdc-main-card .btn-primary {
        background: linear-gradient(135deg, #1f2f46, #2d4567) !important;
    }
    .vdc-main-card .btn-secondary {
        background: #f1f5f9 !important;
        border-color: #d9e2ec !important;
    }

    .vdc-main-card #span1,
    .vdc-main-card #span2,
    .vdc-main-card #span3 {
        border-radius: 999px !important;
        padding: 7px 14px !important;
        font-size: 11.5px !important;
    }

    .vdc-main-card .form-control,
    .vdc-main-card select {
        background: #fbfdff !important;
        border-color: #d8e2ee !important;
    }
    .vdc-main-card .form-group > .control-label {
        text-transform: uppercase;
        font-size: 10.5px !important;
        letter-spacing: .45px;
        color: #475569 !important;
    }
    .vdc-main-card .device-actions-compact {
        margin-top: 10px;
        margin-bottom: 2px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .vdc-main-card .device-actions-compact .status-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .vdc-main-card .device-actions-compact .status-row .btn {
        min-width: 146px;
        padding: 11px 18px !important;
        font-size: 13.5px !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        letter-spacing: .2px;
        border: 1px solid rgba(255,255,255,0.16) !important;
        box-shadow: 0 8px 16px rgba(15,23,42,0.2) !important;
        text-align: center;
    }
    .vdc-main-card .device-actions-compact #span1 {
        background: linear-gradient(135deg, #1e293b, #314968) !important;
        color: #fff !important;
    }
    .vdc-main-card .device-actions-compact #span2 {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        color: #fff !important;
    }
    .vdc-main-card .device-actions-compact #span2.btn-success {
        background: linear-gradient(135deg, #76CF1C, #5eb214) !important;
        color: #0f172a !important;
        border-color: rgba(15,23,42,0.08) !important;
    }
    .vdc-main-card .device-actions-compact #span3 {
        background: linear-gradient(135deg, #24324a, #1e293b) !important;
        color: #fff !important;
    }
    .vdc-main-card .device-actions-compact .status-row .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(15,23,42,0.24) !important;
    }

    /* Device/CAN configuration boxes (match upper card style) */
    .vdc-main-card .configuration-item {
        background: #ffffff;
        border: 1px solid #dfe7f1;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    .vdc-main-card .configuration-item h6 {
        margin: 0 0 12px !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    .vdc-main-card .configuration-item .col-lg-3.mb-3 {
        margin-bottom: 10px !important;
        display: flex;
    }
    .vdc-main-card .configuration-item .bgx-table-container {
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        width: 100%;
        height: 100%;
    }
    .vdc-main-card .configuration-item .bgx-table-row {
        margin: 0 !important;
        gap: 0 !important;
        display: block !important;
        width: 100%;
        height: 100%;
    }
    .vdc-main-card .configuration-item .bgx-table-cell {
        min-height: 76px;
        height: 100%;
        width: 100%;
        border: 1px solid #e7eef6 !important;
        border-left: 4px solid #76CF1C !important;
        border-radius: 10px !important;
        background: #ffffff !important;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
        padding: 11px 12px !important;
    }
    .vdc-main-card .configuration-item .card-text {
        margin: 0 !important;
        font-size: 13px !important;
        color: #334155 !important;
        line-height: 1.4;
        word-break: break-word;
    }
    .vdc-main-card .configuration-item .card-text strong {
        display: block;
        font-size: 10.5px !important;
        text-transform: uppercase;
        letter-spacing: .35px;
        color: #0f172a !important;
        margin-bottom: 4px;
    }

    /* Final cleanup: heading alignment */
    .vdc-main-card .user-info.mb-4 > .col-lg-12 h4 {
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        margin-left: 0 !important;
        padding-left: 12px !important;
    }
    .vdc-main-card .user-info.mb-4 > .col-lg-12 h4::before {
        width: 7px !important;
        height: 7px !important;
        border-radius: 999px !important;
        background: #76CF1C !important;
        display: inline-block !important;
        margin: 0 !important;
        box-shadow: none !important;
    }

    .vdc-main-card .view-device-configuration > .col-lg-12:first-child {
        align-items: flex-end;
    }

    @media (max-width: 1199px) {
        .vdc-main-card .view-device-configuration > .col-lg-12:first-child {
            align-items: stretch;
        }
    }
    .vdc-main-card .device-actions-compact .edit-row {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    .vdc-main-card .device-actions-compact .btn {
        margin: 0 !important;
    }
    /* Status pills + Edit: below map, centered */
    .vdc-main-card .device-actions-compact.vdc-actions-below-map {
        width: 100%;
        max-width: 100%;
        margin-top: 14px;
        margin-bottom: 4px;
        gap: 12px;
        align-items: center;
        align-self: stretch;
    }
    .vdc-main-card .device-actions-compact.vdc-actions-below-map .status-row {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
    }
    .vdc-main-card .device-actions-compact.vdc-actions-below-map .edit-row {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .vdc-main-card .user-info.mb-4 {
        margin-bottom: 10px !important;
        padding-bottom: 8px !important;
    }

    /* ===== Screenshot alignment fixes ===== */
    .vdc-main-card .view-device-configuration {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        margin-top: 6px;
        margin-left: -8px !important;
        margin-right: -8px !important;
    }
    .vdc-main-card .view-device-configuration > .col-lg-5,
    .vdc-main-card .view-device-configuration > .col-lg-7,
    .vdc-main-card .view-device-configuration > .col-lg-12 {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 100%;
        flex: 0 0 100%;
        padding-left: 8px !important;
        padding-right: 8px !important;
    }

    /* Prominent "Device and Configurations" title bar */
    .vdc-main-card .header-custom {
        padding: 16px 20px !important;
        background: linear-gradient(90deg, #0b1325 0%, #1b2b45 65%, #22395c 100%) !important;
        border-bottom: 2px solid rgba(118, 207, 28, 0.55) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 8px 20px rgba(2, 8, 23, 0.28);
    }
    .vdc-main-card .header-custom h4 {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 17px !important;
        font-weight: 800 !important;
        letter-spacing: .25px;
        text-transform: uppercase;
    }
    .vdc-main-card .header-custom h4::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #76CF1C;
        box-shadow: 0 0 0 6px rgba(118, 207, 28, 0.18);
    }

    /* Device Information heading: make bar bigger and cleaner */
    .vdc-main-card .user-info.mb-4 > .col-lg-12 h4 {
        width: 100%;
        min-height: 44px;
        display: flex;
        align-items: center;
        padding: 10px 14px;
        border-radius: 10px;
        background: linear-gradient(90deg, #f8fbff, #edf4fc);
        border: 1px solid #dbe7f2;
    }

    /* Left information block larger and better readable */
    .vdc-main-card .view-device-configuration .bgx-table-container {
        flex: 1;
        width: 100%;
        border: 0 !important;
        border-radius: 14px;
        padding: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }
    .vdc-main-card .view-device-configuration .bgx-table-row {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px !important;
        margin-bottom: 10px !important;
    }
    .vdc-main-card .view-device-configuration .bgx-table-cell {
        min-height: 76px;
        padding: 12px 13px !important;
        font-size: 13px !important;
        border: 1px solid #e7eef6 !important;
        border-left: 4px solid #76CF1C !important;
        background: #ffffff !important;
        border-radius: 10px !important;
        box-shadow: 0 3px 10px rgba(15,23,42,0.04);
    }
    .vdc-main-card .view-device-configuration .bgx-table-cell strong {
        font-size: 11px;
        margin-bottom: 4px;
    }

    /* Map area: keep balanced with table height */
    .vdc-main-card .bgx-map-configurations {
        flex: 0 0 auto;
        width: 100%;
        border: 0 !important;
        border-radius: 14px;
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
        padding: 10px;
        display: block;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.24);
        min-height: 0;
        align-self: flex-start;
        margin-bottom: 12px;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .vdc-main-card .bgx-map-configurations > .col-lg-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .vdc-main-card #map {
        width: 100% !important;
        height: 420px !important;
        min-height: 420px !important;
        max-height: 420px !important;
        border: 2px solid rgba(255,255,255,0.14);
        border-radius: 12px;
        overflow: hidden;
    }

    @media (max-width: 1199px) {
        .vdc-main-card #map {
            height: 300px !important;
            min-height: 300px !important;
            max-height: 300px !important;
        }
        .vdc-main-card .view-device-configuration .bgx-table-cell {
            min-height: 64px;
        }
        .vdc-main-card .view-device-configuration .bgx-table-row {
            grid-template-columns: 1fr;
        }
    }

    /* Card title: dark navy bar + green list icon + white uppercase (IMEI-style) */
    .vdc-main-card .header-custom {
        background: #0b1324 !important;
        border: 0 !important;
        border-bottom: 0 !important;
        border-radius: 14px 14px 0 0 !important;
        padding: 13px 18px 13px 16px !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06) !important;
    }
    .vdc-main-card .header-custom::after {
        display: none !important;
        content: none !important;
    }
    .vdc-main-card .header-custom h4 {
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: 0.08em !important;
        text-transform: uppercase !important;
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        margin: 0 !important;
    }
    .vdc-main-card .header-custom h4::before {
        display: none !important;
        content: none !important;
    }
    .vdc-main-card .header-custom .vdc-header-icon {
        color: #76cf1c !important;
        font-size: 17px !important;
        line-height: 1;
        width: 1.25em;
        text-align: center;
        flex-shrink: 0;
    }

    .vdc-main-card .view-device-configuration > .col-lg-5 {
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
    }
    .vdc-main-card .view-device-configuration > .col-lg-7 {
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
        min-width: 0;
    }
    .vdc-main-card .bgx-map-configurations {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box;
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 6px !important;
        padding: 4px !important;
        box-shadow: none !important;
    }
    .vdc-main-card .view-device-configuration > .col-lg-7 > .row.bgx-map-configurations {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    .vdc-main-card .bgx-map-configurations > .col-lg-12 {
        width: 100% !important;
        max-width: 100% !important;
        float: none;
    }
    .vdc-main-card #map {
        width: 100% !important;
        max-width: 100% !important;
        aspect-ratio: 1 / 1 !important;
        height: auto !important;
        min-height: unset !important;
        max-height: 480px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        display: block;
        border: 0 !important;
        border-radius: 4px !important;
        box-shadow: none !important;
    }

    @media (max-width: 1199px) {
        .vdc-main-card .view-device-configuration > .col-lg-5,
        .vdc-main-card .view-device-configuration > .col-lg-7 {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }
        .vdc-main-card #map {
            max-height: 320px !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="modal" id="deviceUserPreviewModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><strong>Confirmation</strong></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center" style="font-size: 14px;margin-bottom: 15px;">
                            "You are trying to change the assigned Account. Device will be NO more visible in the current
                            Account and its "Reseller" or "User" Accounts. Do you want to proceed?
                        </div>
                        <div class="col-md-12 text-center">
                            <button type="button" data-type="yes"
                                class="btn btn-primary btn-raised selectDeviceUserChange">Yes</button>
                            <button type="button" data-type="no"
                                class="btn btn-primary btn-raised selectDeviceUserChange">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <section id="main-content">
        <section class="wrapper">
            <div class="vdc-shell">
            <div class="vdc-breadcrumb-wrap">
                <div class="vdc-breadcrumb">
                    <a href="/{{ $url_type }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
                    <span class="bc-item"><a href="/{{ $url_type }}">Home</a></span>
                    <span class="bc-sep"><i class="fa fa-angle-right"></i></span>
                    <span class="bc-item"><a href="javascript:history.back()">View Devices</a></span>
                    <span class="bc-sep"><i class="fa fa-angle-right"></i></span>
                    <span class="bc-item active">View Configurations</span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="container bgx-custom-page">
                        <div class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card vdc-main-card">
                                    <div class="card-header header-custom">
                                        <h4>
                                            <i class="fa fa-bars vdc-header-icon" aria-hidden="true"></i>
                                            Device and Configurations
                                        </h4>
                                    </div>
                                    <div class="card-body body-custom">
                                        @if($errors && count($errors) > 0)
                                            @foreach($errors as $error)
                                                <div class="col-sm-12 alert alert-danger" role="alert">
                                                    {{$error}}
                                                </div>
                                            @endforeach
                                        @endif
                                        {{-- Display User Information --}}
                                        <div class="user-info mb-4">
                                            <div class='col-lg-12'>
                                                <h4><b>Device Information</b></h4>
                                            </div>
                                            <div class='row  bgx-configurations view-device-configuration'>
                                                <div class='col-lg-5'>
                                                    <div class="bgx-table-container">
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell"><strong>Device
                                                                    Name:</strong>{{ $device['name'] ?? '' }} </div>
                                                            <div class="bgx-table-cell"><strong>Device
                                                                    Model:</strong>{{ $configurations['modelName']['value'] ?? '' }}
                                                            </div>
                                                        </div>
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell"><strong>Vendor ID:</strong>
                                                                {{ $configurations['vendorId']['value'] ?? '' }}</div>
                                                            <div class="bgx-table-cell"><strong>Device
                                                                    Category:</strong>{{ CommonHelper::getDeviceCategoryName($device['device_category_id']) ?? '' }}
                                                            </div>
                                                        </div>
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell">
                                                                <strong>Account Assigned: </strong>
                                                                {{ isset($nextId) && $nextId != null ? CommonHelper::getDeviceUserName($nextId) : 'Unassigned' }}
                                                            </div>
                                                            <div class="bgx-table-cell">
                                                                <strong>IMEI:</strong>{{$device['imei'] ?? ''}}</div>
                                                        </div>
                                                        @if(Auth::user()->user_type == 'Admin' || Auth::user()->user_type == "Support")
                                                            <div class="bgx-table-row">
                                                                <div class="bgx-table-cell">
                                                                    <strong>Firmware ID:</strong>
                                                                    {{ isset($configurations['firmware_id']) ? $configurations['firmware_id']['value'] : 'Not Available' }}
                                                                </div>
                                                                <div class="bgx-table-cell">
                                                                    <strong>Firmware Name:</strong>
                                                                    {{$firmwareName ?? "--"}}
                                                                    <!--{{ isset($configurations['firmware_id']) ? CommonHelper::getFirmwareName($configurations['firmware_id']['value']) : 'Not Available' }}-->
                                                                </div>
                                                            </div>
                                                            <div class="bgx-table-row">
                                                                @if(Auth::user()->user_type == 'Admin')
                                                                    <div class="bgx-table-cell"><strong>Firmware File:</strong>
                                                                        @if(isset($filename))
                                                                            @if(Auth::user()->user_type == 'Admin')
                                                                                <a href="{{ asset('fw/' . $filename) }}"
                                                                                    target="_blank">{{$filename}}</a>
                                                                            @else
                                                                                {{$filename}}
                                                                            @endif
                                                                        @else
                                                                            Not Available
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                                <div class="bgx-table-cell">
                                                                    <strong>Firmware Version:</strong>
                                                                    {{$fileVersion ?? "--"}}
                                                                    <!--{{ isset($configurations['firmware_version']) ? $configurations['firmware_version']['value'] : 'Not Available' }}-->
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="bgx-table-row">
                                                            @if(Auth::user()->user_type == 'Admin')
                                                                <div class="bgx-table-cell">
                                                                    <strong>Firmware Filesize:</strong>
                                                                    {{$fileSize ?? "--"}}
                                                                    <!--{{ isset($configurations['firmware_version']) ? $configurations['firmware_version']['value'] : 'Not Available' }}-->
                                                                </div>
                                                            @endif
                                                            <div class="bgx-table-cell">
                                                                <strong>Configuration Status:</strong>
                                                                @php
                                                                    $status = $device['deviceStatus'] ?? '';
                                                                    $color = match ($status) {
                                                                        'Pending' => 'text-warning font-bold',
                                                                        'Completed' => 'text-success font-bold',
                                                                        default => 'text-gray-600',
                                                                    };
                                                                @endphp
                                                                <span class="{{ $color }}">
                                                                    @if($status == 'Completed')
                                                                        {{ $status }} on
                                                                        {{ CommonHelper::getDateAsTimeZone($device->api_updated_at) }}
                                                                    @else
                                                                        {{ $status }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell"><strong>Activation Date:</strong>
                                                                {{ isset($configurations['activationDate']) ? CommonHelper::getDateAsTimeZone($configurations['activationDate']) : '' }}
                                                            </div>
                                                            <div class="bgx-table-cell"><strong>Created at:</strong>
                                                                {{ isset($device['created_at']) ? CommonHelper::getDateAsTimeZone($device['created_at']) : '' }}
                                                            </div>
                                                        </div>
                                                        <div class="bgx-table-row">
                                                            <div class="bgx-table-cell"><strong>Last Edit:</strong>
                                                                {{ isset($device['updated_at']) ? CommonHelper::getDateAsTimeZone($device['updated_at']) : '' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-7'>
                                                    <div class='row bgx-map-configurations'>
                                                        <div class='col-lg-12'>
                                                            <div id="map"></div>
                                                        </div>
                                                    </div>
                                                    <div class="device-actions-compact vdc-actions-below-map">
                                                        <div class="status-row">
                                                            @if(Auth::user()->user_type == "Admin")
                                                                <div id="span2"
                                                                    class="btn {{ $device['is_editable'] == 1 ? 'btn-success active' : 'btn-danger' }}">
                                                                    Editable -
                                                                    {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'Yes' : 'No' }}
                                                                </div>
                                                                <div id="span1" class="btn btn-primary">Ping
                                                                    Interval - {{$configurations['ping_interval']['value'] ?? 0}}
                                                                </div>
                                                            @endif
                                                            <div id="span3" class="btn btn-info">Total Pings -
                                                                {{ $configurations['total_pings'] ?? 0}}</div>
                                                        </div>
                                                        <div class="edit-row">
                                                            @if(Auth::user()->user_type != "Support")
                                                                @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                                                    <button type="button" class="btn btn-primary edit-device-btn"
                                                                        onclick="toggleEditDevice()">
                                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                    </button>
                                                                @endif
                                                            @else
                                                                @if(Auth::user()->is_support_active)
                                                                    <button type="button" class="btn btn-primary edit-btn"
                                                                        onclick="toggleEditDevice('')">
                                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                    </button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='row  bgx-configurations edit-device-configuration'
                                                style="display:none;">
                                                <form class="validator form-horizontal " id="updateDeviceInfoConfiguration"
                                                    method="post"
                                                    action="/{{$url_type}}/update-device-info-configurations/{{$device['id']}}">
                                                    @method('PATCH')
                                                    @csrf
                                                    <div class="form-group ">

                                                    </div>
                                                    @if(Auth::user()->user_type != 'User' && Auth::user()->user_type != 'Support')
                                                        <div class="form-group ">
                                                            @if(Auth::user()->user_type == 'Admin')
                                                                <label for="cname"
                                                                    class="control-label col-lg-3"><?= ($device['user_id'] != '' ? 'Account' : 'Account') ?></label>
                                                            @elseif(Auth::user()->user_type == 'Reseller')
                                                                <label for="cname"
                                                                    class="control-label col-lg-3"><?= ($device['user_id'] != Auth::user()->id ? 'Account' : 'Account') ?></label>
                                                            @elseif(Auth::user()->user_type == 'User')
                                                                <label for="cname" class="control-label col-lg-3">User</label>
                                                            @else
                                                                <label for="cname"
                                                                    class="control-label col-lg-3"><?= ($device['user_id'] != '' ? 'Account' : 'Account') ?></label>
                                                            @endif
                                                            <div class="col-lg-6">
                                                                <select class="" id="editDeviceUsers" name="user_id">
                                                                    @if(count($users) > 0)
                                                                        @if(Auth::user()->user_type == 'Admin')
                                                                            <option value="">
                                                                                <?= ($device['user_id'] != '' ? 'Unassigned' : 'Unassigned') ?>
                                                                            </option>
                                                                        @elseif(Auth::user()->user_type == 'Reseller')
                                                                            <option value="">
                                                                                <?= ($device['user_id'] != Auth::user()->id ? 'Unassigned' : 'Unassigned') ?>
                                                                            </option>
                                                                        @endif
                                                                        @foreach($users as $user)
                                                                            <option value="{{$user->id}}" <?= ($uid == $user->id ? 'selected' : '') ?>>{{$user->name}}</option>
                                                                        @endforeach
                                                                    @else
                                                                        <option value="">Unassigned</option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <input type="hidden" name="user_id" value="{{ $uid }}">
                                                    @endif
                                                    @if(Auth::user()->user_type != 'Support')
                                                        <div class="form-group ">
                                                            <label for="curl" class="control-label col-lg-3">Name
                                                                (optional)<span class="require">*</span></label>
                                                            <div class="col-lg-6">
                                                                <input class="form-control" placeholder="Enter Device Name"
                                                                    id="name" type="text" name="name"
                                                                    value="{{ $device['name']}}">
                                                            </div>
                                                        </div>
                                                    @else
                                                        <input class="form-control" placeholder="Enter Device Name" id="name"
                                                            type="hidden" name="name" value="{{ $device['name']}}">
                                                    @endif
                                                    <div class="form-group " id="FirmwareInput">
                                                        <label for="firmware" class="control-label col-lg-3 "
                                                            required>Firmware <span class="require">*</span></label>
                                                        <div class="col-lg-6">
                                                            <select id="firmware" name="configuration[firmware_id]"
                                                                class="form-control" placeholder='Search and Select'>
                                                                @foreach($firmware as $firmwar)
                                                                    <option value="{{ $firmwar->id }}" {{ isset($configurations['firmware_id']) && $configurations['firmware_id']['value'] == $firmwar->id ? 'selected' : '' }}>
                                                                        {{ $firmwar->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group " id="modalInput">
                                                        <label for="firmware" class="control-label col-lg-3 " required>Model
                                                            Name <span class="require">*</span></label>
                                                        <div class="col-lg-6">
                                                            <input type="text" class="form-control"
                                                                name="configuration[modelName]" id="modelName"
                                                                value="{{ $configurations['modelName']['value'] ?? '' }}"
                                                                readonly="readonly" />
                                                            <div class="col-sm-12 alert alert-danger modelName_error"
                                                                role="alert" style="display:none"></div>
                                                            <input type="hidden" id="user_type" name="user_type"
                                                                value="{{$url_type}}" />
                                                        </div>
                                                    </div>
                                                    <div class="form-group " id="vendorInput">
                                                        <label for="firmware" class="control-label col-lg-3 "
                                                            required>Vendor ID <span class="require">*</span></label>
                                                        <div class="col-lg-6">
                                                            <input type="text" class="form-control"
                                                                name="configuration[vendorId]" id="vendorId"
                                                                value="{{ $configurations['vendorId']['value'] ?? '' }}"
                                                                readonly="readonly" />
                                                            <div class="col-sm-12 alert alert-danger vendor_error"
                                                                role="alert" style="display:none"></div>
                                                            <input type="hidden" id="user_type" name="user_type"
                                                                value="{{$url_type}}" />
                                                        </div>
                                                    </div>
                                                    @if(Auth::user()->user_type == 'Admin')
                                                        <div class="form-group">
                                                            <label for="cemail" class="control-label col-lg-3">IMEI <span
                                                                    class="require">*</span></label>
                                                            <div class="col-lg-6">
                                                                <div class="input-group  d-flex">
                                                                    <input class="form-control col-lg-10" id="imei" type="text"
                                                                        maxlength="15" name="imei"
                                                                        placeholder="Enter IMEI Number"
                                                                        value="{{$device['imei']}}" readonly />
                                                                    <div class="input-group-append col-lg-2">
                                                                        <button class="btn btn-secondary margin-top-1"
                                                                            type="button" id="editImeiBtn">Edit</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(Auth::user()->user_type != "Support")
                                                            <div class="form-group ">
                                                                <label for="curl" class="control-label col-lg-3">Ping interval <span
                                                                        class="require">*</span></label>
                                                                <div class="col-lg-6">
                                                                    <input class="form-control" placeholder="Enter Ping Interval"
                                                                        id="ping_interval" type="Number"
                                                                        name="configuration[ping_interval]"
                                                                        value="{{isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : ''}}"
                                                                        onkeypress="return blockSpecialCharTransmission(event)"
                                                                        required />
                                                                </div>
                                                            </div>
                                                            <div class="form-group ">
                                                                <label for="curl" class="control-label col-lg-3">Device Edit
                                                                    Permission<span class="require">*</span></label>
                                                                <div class="col-lg-6">
                                                                    <label>Enable</label>
                                                                    <input {{(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == '1' ? 'checked' : '')}}
                                                                        type="radio" name="configuration[is_editable]" value="1"
                                                                        style="height:20px; width:20px; vertical-align: middle;"
                                                                        required>
                                                                    <label>Disable</label>
                                                                    <input {{isset($configurations['is_editable']) && $configurations['is_editable']['value'] == '0' ? 'checked' : ''}}
                                                                        type="radio" name="configuration[is_editable]" value="0"
                                                                        style="height:20px; width:20px; vertical-align: middle;"
                                                                        required>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <input type="hidden" name="configuration[ping_interval]"
                                                                value="$configurations['ping_interval']['value']" />
                                                            <input type="hidden" name="configuration[is_editable]"
                                                                value="$configurations['is_editable']['value']" />
                                                        @endif
                                                    @endif
                                                    <div class="col-sm-12 bg-margin-top text-right">
                                                        <input type="hidden" name="prev_uid" class="prev_uid"
                                                            value="{{ $device['user_id'] }}">
                                                        <input type="hidden" id="device_id" name="device_id"
                                                            value="{{$device['id']}}">
                                                        <input type="hidden" id="firmwareFileSize"
                                                            name="configuration[firmwareFileSize]" value"">
                                                        <button type="submit"
                                                            class="btn btn-primary updateDeviceName">Save</button>
                                                        <button type="button"
                                                            class="btn btn-secondary cancel-device-info-btn"
                                                            data-key="0">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="user-info">
                                            <h4><b>Device Configurations</b></h4>
                                            @empty($device['configurations'])
                                                <p class="col-md-12">No configurations found.</p>
                                            @else
                                                <?php    echo CommonHelper::getDeviceConfigurationInput($device['device_category_id'], 0, $configurations, $template_info, $url_type, $device); ?>
                                                @if(Auth::user()->user_type != "Support")
                                                    @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12 text-center">
                                                                <button type="button" class="btn btn-primary edit-btn"
                                                                    onclick="toggleEdit('')">
                                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    @if(Auth::user()->is_support_active)
                                                        <div class="row mt-3">
                                                            <div class="col-lg-12 text-center">
                                                                <button type="button" class="btn btn-primary edit-btn"
                                                                    onclick="toggleEdit('')">
                                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endempty
                                        </div>
                                        @if(isset($configurations['can_interface']['value']) && $configurations['can_interface']['value'] == 1)
                                            @if($getCanEnableByDeviceCategory->is_can_protocol == 1)
                                                <div class="user-info">
                                                    <h4><b>CAN Protocol Configurations</b></h4>
                                                    @php $canConfigData = is_array($canConfigurations) ? $canConfigurations : json_decode($canConfigurations, true);
                                                    @endphp
                                                    @empty($device['can_configurations'])
                                                        <?php            echo CommonHelper::getCanProtocolConfigurationInput($device['device_category_id'], 0, $canConfigData, $url_type, $device); ?>
                                                        @if(Auth::user()->user_type != "Support")
                                                            @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                                                <div class="row mt-3">
                                                                    <div class="col-lg-12 text-center">

                                                                          <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @else
                                                            @if(Auth::user()->is_support_active)
                                                                <div class="row mt-3">
                                                                    <div class="col-lg-12 text-center">

                                                                                                            <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @else
                                                        <?php            echo CommonHelper::getCanProtocolConfigurationInput($device['device_category_id'], 0, $canConfigData, $url_type, $device); ?>
                                                        @if(Auth::user()->user_type != "Support")
                                                            @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                                                <div class="row mt-3">
                                                                    <div class="col-lg-12 text-center">

                                                                                                                           <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @else
                                                            @if(Auth::user()->is_support_active)
                                                                <div class="row mt-3">
                                                                    <div class="col-lg-12 text-center">

                                                                                                                           <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')">
                                                                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endempty
                                                </div>
                                            @endif
                                        @endif
                                        <div class="user-info">


                                                                                    <h4><b>Device Parameters</b> <small>(<b>Last updated on:</b> {{ isset($device->api_updated_at) ? CommonHelper::getDateAsTimeZone($device->api_updated_at) : '' }})</small></h4>
                                            @empty($device['parameters'])
                                                <div class="card padding-10 text-center">
                                                    <p>No configurations found.</p>
                                                </div>
                                            @else
                                                <?php    echo CommonHelper::getDeviceSettings($device['device_category_id'], 0, $parameters); ?>
                                            @endempty
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>
        </section>
    </section>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    function openCanModal() {
        $('#canModal').modal('show');
    }
     $(document).ready(function() {
         $('#updateDeviceInfoConfiguration').on('submit', function(e) {
            let firmware = $('#firmware').val();
            if (!firmware || firmware == "") {
                alert("Please select a firmware");
                e.preventDefault();
                return false;
            }
        });
         $('#protocolSelect').on('change', function() {
            const value = $(this).val();
            $('#j1979Fields').toggle(value === 'J1979');
            $('#j1939Fields').toggle(value === 'J1939');

            let selected = $(this).val();

            $('#j1979Fields input, #j1939Fields input').removeAttr('required');

            if (selected === 'J1979') {
                $('#j1979Fields input').attr('required', true);
            } else if (selected === 'J1939') {
                $('#j1939Fields input').attr('required', true);
            }
        });

         $('#canForm').on('submit', function(e) {
            e.preventDefault();

            const protocol = $('#protocolSelect').val();
            let json = {
                can_enabled: true,
                protocol
            };

            if (protocol === 'J1979') {
                json.can_protocol = $('[name="can_protocol"]').val();
                json.request_id = $('[name="request_id"]').val();
                json.response_ids = $('[name="response_ids"]').val().split(',').map(s => s.trim());
                json.baud_rate = parseInt($('[name="baud_rate"]').val());
                json.polling_interval_ms = parseInt($('[name="polling_interval_ms"]').val());
                json.supported_modes = $('[name="supported_modes"]').val().split(',').map(s => s.trim());
                json.supported_pids = $('[name="supported_pids"]').val().split(',').map(s => s.trim());
                json.extended_id = false;
            }

            if (protocol === 'J1939') {
                json.baud_rate = parseInt($('[name="baud_rate"]').val());
                json.source_address = $('[name="source_address"]').val();
                json.preferred_address = $('[name="preferred_address"]').val();
                json.pgns_to_poll = $('[name="pgns_to_poll"]').val().split(',').map(s => s.trim());
                json.use_tp = $('[name="use_tp"]').val() === 'true';
                json.can_channel = $('[name="can_channel"]').val();
                json.name = {
                    identity_number: parseInt($('[name="identity_number"]').val()),
                    manufacturer_code: parseInt($('[name="manufacturer_code"]').val()),
                    ecu_instance: parseInt($('[name="ecu_instance"]').val()),
                    function_instance: parseInt($('[name="function_instance"]').val()),
                    function: parseInt($('[name="function"]').val()),
                    vehicle_system: parseInt($('[name="vehicle_system"]').val()),
                    arbitrary_address_capable: $('[name="arbitrary_address_capable"]').val() === 'true'
                };
            }

            $('#outputJson').val(JSON.stringify(json, null, 2));
        });
    });

    function toggleEditDevice() {
        $('.edit-device-btn').hide();
        $('.view-device-configuration').hide();
        $('.edit-device-configuration').show();
    }

    function toggleEdit(key) {
        $('.edit-btn').hide();
        $('#config-0').hide();
        $('#form-0').show();
    }

    function canConfigToggleEdit(key) {
        $('.edit-config-btn').hide();
        $('#canConfig-0').hide();
        $('#canConfigForm-0').show();
    }

    function checkModalNameExist(userId, firmwareId, deviceId) {
        $('#modalInput').show();
        $('#vendorInput').show();
        let actionUrl = "{{ url(Auth::user()->user_type == 'Admin' ? 'admin/get-model-name' : (Auth::user()->user_type == 'Reseller' ? 'reseller/get-model-name' : 'user/get-model-name')) }}";
        $.ajax({
            url: actionUrl,
            type: "POST",
            data: {
                user_id: userId,
                firmware_id: firmwareId,
                device_id: deviceId,
                category_id: <?= isset($device['device_category_id']) ? $device['device_category_id'] : 'null' ?>,
                _token: '{{ csrf_token() }}'
            },
             success: function(response) {
                let result = (typeof response === 'string') ? JSON.parse(response) : response;
                if (result.status == 200) {
                    let modal = result.modal;
                    if (modal != null) {
                        $('#modelName').show().val(modal.name);
$('#vendorId').show().val(modal.vendorId);
                        
                        $(".modelName_error").hide();
                        $(".vendor_error").hide();
                        $('.updateDeviceName').attr('disabled', false);

                         if(result.firmwareFileSize) {
                            $('#firmwareFileSize').val(result.firmwareFileSize);
                        }
                    } else {
                        $('#modelName').hide();
                        $('#vendorId').hide();
                        $(".modelName_error").show().html('Model Name is not Assigned. Please contact with Administrator');
                        $(".vendor_error").show().html('Vendor ID is not Assigned. Please contact with Administrator');
                        $('.updateDeviceName').attr('disabled', true);
                    }
                } else {
                    $('#modelName').hide();
                    $('#vendorId').hide();
                    $(".modelName_error").show().html(result.message || 'Model and Firmware combination does not exist.');
                    $(".vendor_error").show().html(result.message || 'Model and Firmware combination does not exist.');
                    $('.updateDeviceName').attr('disabled', true);
                }
            },
             error: function(xhr) {
                // Handle error
                console.error("Error:", xhr.responseText);
                $('.error_msg').append("An error occurred while processing your request.").show();
            },
             complete: function() {
                $('#loading').hide(); // Hide loading indicator
            }
        });
    }
    function getFirmwareWitModel(userId) {
        const firmwareDropdown = $('#firmware');
        let selectedFirmware = <?= json_encode($configurations['firmware_id']) ?>;
        let categoryId = "<?= $device['device_category_id'] ?>";

        firmwareDropdown.empty(); // clear previous options
        let actionUrl = "{{ url(Auth::user()->user_type == 'Admin' ? 'admin/get-firmware-with-models' : (Auth::user()->user_type == 'Reseller' ? 'reseller/get-firmware-with-models' : (Auth::user()->user_type == 'Support' ? 'support/get-firmware-with-models' : 'user/get-firmware-with-models'))) }}";
        if (userId && userId !== "No User Found") {
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: {
                    user_id: userId,
                    category_id: categoryId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) {
                    if (response.status === 200 && response.firmwareList.length > 0) {
                        // firmwareDropdown.append('<option value="">Select Firmware</option>');
                        // response.firmwareList.forEach(firmware => {
                        //     firmwareDropdown.append(
                        //         `<option value="${firmware.id}" ${Number(firmware.id) == Number(selectedFirmware['value']) ? 'selected="selected"' : ''}>${firmware.name}</option>`
                        //     );
                        // });
                        firmwareDropdown.append('<option value="">Select Firmware</option>');
                        response.firmwareList.forEach(firmware => {
                            firmwareDropdown.append(
                                `<option value="${firmware.id}" ${Number(firmware.id) == Number(selectedFirmware['value']) ? 'selected="selected"' : ''}>${firmware.name}</option>`
                            );
                        });
                        $('#templateInput').show();
                        $('#modelName').show();
                        $('#VendorId').show();
                        $(".vendor_error").hide();
                        $(".modelName_error").hide();
                    } else {
                        firmwareDropdown.append('<option value="">No Firmware Found</option>');
                    }
                },
                 error: function() {
                    firmwareDropdown.append('<option value="">Error Fetching Firmware</option>');
                }
            });
        } else {
            // firmwareDropdown.append('<option value="">Select User First</option>');
            let actionUrl1 = "{{ url(Auth::user()->user_type == 'Admin' ? 'admin/get-firmware-with-models' : (Auth::user()->user_type == 'Reseller' ? 'reseller/get-firmware-with-models' : (Auth::user()->user_type == 'Support' ? 'support/get-firmware-with-models' : 'user/get-firmware-with-models'))) }}";
            $.ajax({
                url: actionUrl1,
                type: 'POST',
                data: {
                    user_id: userId,
                    category_id: categoryId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                 success: function(response) {
                    if (response.status === 200 && response.firmwareList.length > 0) {
                        firmwareDropdown.append('<option value="">Select Firmware</option>');
                        response.firmwareList.forEach(firmware => {
                            firmwareDropdown.append(
                                `<option value="${firmware.id}" ${Number(firmware.id) == Number(selectedFirmware['value']) ? 'selected="selected"' : ''}>${firmware.name}</option>`
                            );
                        });
                    } else {
                        firmwareDropdown.append('<option value="">No Firmware Found</option>');
                    }
                },
                 error: function() {
                    firmwareDropdown.append('<option value="">Error Fetching Firmware</option>');
                }
            });
        }
    }
     $(document).ready(function() {
        let userId1 = "{{ Auth::check() && Auth::user()->user_type != 'Admin' && Auth::user()->user_type != 'Support' ? Auth::user()->id : $device['user_id'] }}";
        let initialFirmwareId = "{{ isset($configurations['firmware_id']) ? $configurations['firmware_id']['value'] : '' }}";
        let initialDeviceId = "{{ $device['id'] }}";
        let deviceCategoryName = "{{ CommonHelper::getDeviceCategoryName($device['device_category_id']) }}";
        const DEFAULT_VENDOR_ID = 'JSD';
        let authUserId = "{{ Auth::user()->id ?? '' }}"; // Current logged-in user

        // Consolidated userId resolution
        function resolveUserId(selectedUserId) {
            return selectedUserId || userId1 || authUserId || null;
        }

        // Central function: resolves model/vendor based on account + firmware state
        function resolveModelAndVendor(userId, firmwareId, deviceId) {
            userId = resolveUserId(userId); // Apply fallback chain
            
            if (!userId) {
                // NO ACCOUNT — use category name + JSD
                $('#modelName').val(deviceCategoryName).show();
                $('#vendorId').val(DEFAULT_VENDOR_ID).show();
                $('.modelName_error').hide();
                $('.vendor_error').hide();
                $('.updateDeviceName').attr('disabled', false);
            } else if (userId && firmwareId) {
                // ACCOUNT + FIRMWARE — fetch from modal table
                checkModalNameExist(userId, firmwareId, deviceId);
            } else if (userId && !firmwareId) {
                // ACCOUNT but NO firmware
                $('#modelName').val('').show();
                $('#vendorId').val('').show();
                $('.modelName_error').show().html('Please select a Firmware.');
                $('.vendor_error').show().html('Please select a Firmware.');
                $('.updateDeviceName').attr('disabled', true);
            }
        }

        getFirmwareWitModel(userId1);

        // On page load: af ter firmware list settles, resolve model/vendor
        setTimeout(function() {
            var firmwareId = $('#firmware').val() || initialFirmwareId;
            var userId = $('#editDeviceUsers').val() || userId1;
            resolveModelAndVendor(userId, firmwareId, initialDeviceId);
        }, 600);

        // ACCOUNT CHANGE
         $('#editDeviceUsers').on('change', function() {
            const userId = $(this).val();
            getFirmwareWitModel(userId);
            const path = window.location.pathname;
            const deviceID = path.split("/").pop();
            // Delay slightly t o let firmware dropdown finish reloading
            setTimeout(function() {
                const firmwareId = $('#firmware').val();
                resolveModelAndVendor(userId, firmwareId, deviceID);
            }, 400);
        });

        // FIRMWARE CHANGE
         $('#firmware').on('change', function() {
            const path = window.location.pathname;
            const deviceID = path.split("/").pop();
            var userId = $('#editDeviceUsers').val() || userId1; // Use current user as fallback for unassigned devices
            var firmwareId = $(this).val();
            resolveModelAndVendor(userId, firmwareId, deviceID);
        });

         $('#editImeiBtn').click(function() {
            var imeiInput = $('#imei');
            if (imeiInput.prop('readonly')) {
                imeiInput.prop('readonly', false);
                $(this).text('Save'); // Change button text to "Save"
            } else {
                imeiInput.prop('readonly', true);
                $(this).text('Edit'); // Change button text back to "Edit"
            }
        });
        $('#templates').select2({
            'placeholder': 'Select and Search '
        })
         $('.cancel-btn').click(function() {
            var key = $(this).data('key');
            $('.edit-btn').show();
            $('#config-0').show();
            $('#form-0').hide();
        });
         $('.cancel-config-btn').click(function() {

            var key = $(this).data('key');
            $('.edit-config-btn').show();
            $('#canConfig-0').show();
            $('#canConfigForm-0').hide();
        });
         $('.cancel-device-info-btn').click(function() {
            $('.edit-device-btn').show();
            $('.view-device-configuration').show();
            $('.edit-device-configuration').hide();
        });

        function initMap(lat, lon) {
            // Initialize the map
            var map = L.map('map').setView([lat, lon], 13);

            // Add OpenStreetMap tiles as the base layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 24,
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Function to fetch location name from OpenStreetMap Nominatim API
            function getLocationName(latitude, longitude) {
                var apiUrl = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latitude + '&lon=' + longitude;

                 return new Promise(function(resolve, reject) {
                    fetch(apiUrl)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                reject('Location not found');
                            } else {
                                resolve(data.display_name);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching location:', error);
                            reject('Unknown Location');
                        });
                });
            }

            // Fetch location name and add marker with popup
            getLocationName(lat, lon)
                .then(name => {
                    // Add marker with popup
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('<b style="font-size:13px;">' + name + '</b>')
                        .openPopup();
                })
                .catch(error => {
                    console.error('Error getting location name:', error);

                    // Add marker with default popup if location name fetch fails
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('Device Location')
                        .openPopup();
                });

            // Recalculate Leaflet tiles after CSS/layout settling.
            function refreshMapSize() {
                try { map.invalidateSize(true); } catch (e) {}
            }
            setTimeout(refreshMapSize, 180);
            setTimeout(refreshMapSize, 500);
            setTimeout(refreshMapSize, 1100);
            window.addEventListener('load', refreshMapSize);
            window.addEventListener('resize', refreshMapSize);
        }

        function toDecimal(dm, dir) {
            if (dm == null || dir == null) return NaN;
            var v = parseFloat(String(dm).trim());
            if (!isFinite(v)) return NaN;
            var d = String(dir).trim().toUpperCase();
            var hemi = d.charAt(0); // N/S/E/W even if full word like 'North'
            var deg = Math.floor(v / 100);
            var min = v - deg * 100;
            var dec = deg + (min / 60);
            if (hemi === 'S' || hemi === 'W') dec = -dec;
            return Number(dec.toFixed(6));
        }

        var latVal = "<?= isset($parameters['latitude']['value']) ? $parameters['latitude']['value'] : '' ?>";
        var latDir = "<?= isset($parameters['latitude_direction']['value']) ? $parameters['latitude_direction']['value'] : '' ?>";
        var lonVal = "<?= isset($parameters['longitude']['value']) ? $parameters['longitude']['value'] : '' ?>";
        var lonDir = "<?= isset($parameters['longitude_direction']['value']) ? $parameters['longitude_direction']['value'] : '' ?>";

        console.log('raw lat/lon', { latVal, latDir, lonVal, lonDir });
        let coordinates = {
            latitude: toDecimal(latVal, latDir),
            longitude: toDecimal(lonVal, lonDir)
        };

        if (!isFinite(coordinates.latitude) || !isFinite(coordinates.longitude)) {
            var latCombined = (String(latVal || '').trim() + String(latDir || '').trim()).trim();
            var lonCombined = (String(lonVal || '').trim() + String(lonDir || '').trim()).trim();
            coordinates = parseCoordinates(latCombined, lonCombined);
        }
        console.log('coordinates', coordinates)

        // Initialize map when document is ready
        if (isFinite(coordinates.latitude) && isFinite(coordinates.longitude)) {
            initMap(coordinates.latitude, coordinates.longitude);
        } else {
            initMap(0, 0);
        }
    });

    function parseCoordinates(latitude, longitude) {
        latitude = String(latitude || '').trim();
        longitude = String(longitude || '').trim();
        // Extract last hemisphere letter if present; tolerate full words
        var latDirection = latitude.replace(/.*?([NnSs])$/, '$1').toUpperCase();
        var lonDirection = longitude.replace(/.*?([EeWw])$/, '$1').toUpperCase();
        var latNum = latitude.replace(/[^0-9.]/g, '');
        var lonNum = longitude.replace(/[^0-9.]/g, '');
        var latValue = parseFloat(latNum);
        var lonValue = parseFloat(lonNum);
        if (!isFinite(latValue) || !isFinite(lonValue)) {
            return { latitude: NaN, longitude: NaN };
        }
        // Parse latitude
        var latDegrees = Math.floor(latValue / 100); // Extract degrees
        var latDecimalMinutes = (latValue % 100) / 60; // Convert remainder to decimal minutes
        var lat = latDegrees + latDecimalMinutes;
        if (latDirection === 'S') {
            lat = -lat;
        }

        // Parse longitude
        var lonDegrees = Math.floor(lonValue / 100); // Extract degrees
        var lonDecimalMinutes = (lonValue % 100) / 60; // Convert remainder to decimal minutes
        var lon = lonDegrees + lonDecimalMinutes;
        if (lonDirection === 'W') {
            lon = -lon;
        }

        return {
            latitude: Number(lat.toFixed(6)),
            longitude: Number(lon.toFixed(6))
        };
    }
</script>
