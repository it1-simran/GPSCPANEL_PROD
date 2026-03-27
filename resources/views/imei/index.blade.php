@extends('layouts.apps')
@section('content')

<style>
    /* ===== IMEI Tracker Page - Enhanced UI ===== */
    .imei-page-wrap {
        padding: 20px 24px 30px;
    }

    /* --- Page Header --- */
    .imei-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .imei-header-left h1 {
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 4px;
        letter-spacing: -0.3px;
    }
    .imei-breadcrumb {
        font-size: 12px;
        color: #95a5a6;
        margin: 0;
    }
    .imei-breadcrumb span {
        color: #3498db;
        font-weight: 600;
    }
    .btn-add-tracker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: #fff !important;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: 8px;
        border: none;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 3px 10px rgba(46, 204, 113, 0.35);
    }
    .btn-add-tracker:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(46, 204, 113, 0.45);
        color: #fff !important;
        text-decoration: none;
    }
    .btn-add-tracker i { font-size: 14px; }

    /* --- Stat Cards --- */
    .imei-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }
    @media (max-width: 768px) {
        .imei-stats { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .imei-stats { grid-template-columns: 1fr; }
    }
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.10);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .stat-icon.green  { background: #eafaf1; color: #27ae60; }
    .stat-icon.orange { background: #fef9e7; color: #f39c12; }
    .stat-icon.red    { background: #fdf2f2; color: #e74c3c; }
    .stat-info .stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }
    .stat-info .stat-label {
        font-size: 12px;
        color: #95a5a6;
        margin-top: 3px;
        font-weight: 500;
    }

    /* --- Flash Alert --- */
    .imei-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, #eafaf1, #d5f5e3);
        border-left: 4px solid #27ae60;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 18px;
        font-size: 13px;
        font-weight: 600;
        color: #1e8449;
        animation: slideDown 0.35s ease;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* --- Table Card --- */
    .imei-table-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0,0,0,0.07);
        border: 1px solid #f0f0f0;
        overflow: hidden;
    }
    .imei-table-card .card-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid #eee;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-header-bar .header-title {
        font-size: 15px;
        font-weight: 700;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card-header-bar .header-title i {
        color: #3498db;
        font-size: 16px;
    }

    /* --- DataTable overrides --- */
    #imeiTable_wrapper .dataTables_filter input {
        border: 1.5px solid #dee2e6;
        border-radius: 7px;
        padding: 5px 10px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
    }
    #imeiTable_wrapper .dataTables_filter input:focus {
        border-color: #3498db;
    }
    #imeiTable_wrapper .dataTables_length select {
        border: 1.5px solid #dee2e6;
        border-radius: 7px;
        padding: 4px 8px;
        font-size: 13px;
    }
    #imeiTable_wrapper .dataTables_info,
    #imeiTable_wrapper .dataTables_filter label,
    #imeiTable_wrapper .dataTables_length label {
        font-size: 12.5px;
        color: #7f8c8d;
    }

    /* --- Table Styles --- */
    #imeiTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
    }
    #imeiTable thead tr th {
        background: #f4f6f8;
        color: #5d6d7e;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 14px;
        border: none;
        border-bottom: 2px solid #e9ecef;
        white-space: nowrap;
    }
    #imeiTable tbody tr {
        transition: background 0.15s, box-shadow 0.15s;
    }
    #imeiTable tbody tr:hover {
        background: #f7fbff !important;
    }
    #imeiTable tbody tr td {
        padding: 12px 14px;
        font-size: 13px;
        color: #2c3e50;
        border-top: 1px solid #f0f2f5;
        vertical-align: middle;
    }
    .imei-id-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: #eaf3fb;
        color: #2980b9;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
    }
    .imei-code {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        font-weight: 600;
        color: #2c3e50;
        letter-spacing: 0.5px;
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 5px;
        border: 1px solid #e9ecef;
    }

    /* --- Status Badges --- */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .status-pill .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-pill.active  { background: #eafaf1; color: #1e8449; }
    .status-pill.active .dot { background: #27ae60; animation: pulse 1.4s infinite; }
    .status-pill.inactive{ background: #fef9e7; color: #9a7d0a; }
    .status-pill.inactive .dot { background: #f39c12; }
    .status-pill.close   { background: #fdf2f2; color: #c0392b; }
    .status-pill.close .dot { background: #e74c3c; }
    @keyframes pulse {
        0%   { opacity: 1; }
        50%  { opacity: 0.35; }
        100% { opacity: 1; }
    }

    /* --- Schedule display --- */
    .schedule-val {
        font-size: 12.5px;
        color: #7f8c8d;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .schedule-val i { color: #bdc3c7; font-size: 12px; }
    .schedule-na {
        font-size: 12px;
        color: #bdc3c7;
        font-style: italic;
    }

    /* --- Action Buttons --- */
    .action-group {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: nowrap;
    }
    .btn-act {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 5px 10px;
        border-radius: 7px;
        font-size: 11.5px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.18s ease;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-act:hover { transform: translateY(-1px); opacity: 0.9; text-decoration: none; }
    .btn-act i { font-size: 11px; }
    .btn-act.edit    { background: #eaf3fb; color: #2980b9; border: 1px solid #d4e9f7; }
    .btn-act.toggle  { background: #fef9e7; color: #d68910; border: 1px solid #fce8a6; }
    .btn-act.delete  { background: #fdf2f2; color: #c0392b; border: 1px solid #f5c6cb; }
    .btn-act.live    { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; }
    .btn-act.edit:hover   { background: #2980b9; color: #fff; border-color: #2980b9; }
    .btn-act.toggle:hover { background: #f39c12; color: #fff; border-color: #f39c12; }
    .btn-act.delete:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }
    .btn-act.live:hover   { background: #27ae60; color: #fff; border-color: #27ae60; }

    /* --- Pagination --- */
    #imeiTable_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        border: 1px solid #dee2e6 !important;
        font-size: 12.5px;
        padding: 4px 9px !important;
    }
    #imeiTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3498db !important;
        border-color: #3498db !important;
        color: #fff !important;
    }
    #imeiTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eaf3fb !important;
        border-color: #3498db !important;
        color: #2980b9 !important;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="imei-page-wrap">

            {{-- ===== Page Header ===== --}}
            <div class="imei-header">
                <div class="imei-header-left">
                    <h1><i class="fa fa-map-marker" style="color:#3498db;margin-right:8px;"></i>IMEI Live Trackers</h1>
                    <p class="imei-breadcrumb">Live Tracking &rsaquo; <span>Manage Trackers</span></p>
                </div>
                <a href="{{ route('imei-devices.create') }}" class="btn-add-tracker">
                    <i class="fa fa-plus"></i> Add Tracker
                </a>
            </div>

            {{-- ===== Stat Cards ===== --}}
            @php
                $totalCount    = $devices->total();
                $activeCount   = \App\Models\ImeiDevice::where('status','active')->count();
                $inactiveCount = \App\Models\ImeiDevice::where('status','inactive')->count();
                $closedCount   = \App\Models\ImeiDevice::where('status','close')->count();
            @endphp
            <div class="imei-stats">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa fa-check-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-value">{{ $activeCount }}</div>
                        <div class="stat-label">Active Trackers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fa fa-pause-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-value">{{ $inactiveCount }}</div>
                        <div class="stat-label">Inactive Trackers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fa fa-times-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-value">{{ $closedCount }}</div>
                        <div class="stat-label">Closed Trackers</div>
                    </div>
                </div>
            </div>

            {{-- ===== Flash Message ===== --}}
            @if (session('success'))
                <div class="imei-alert">
                    <i class="fa fa-check-circle" style="font-size:16px;"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- ===== Table Card ===== --}}
            <div class="imei-table-card">
                <div class="card-header-bar">
                    <div class="header-title">
                        <i class="fa fa-list-ul"></i>
                        All Trackers
                        <span style="background:#eaf3fb;color:#2980b9;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;margin-left:6px;">
                            {{ $totalCount }} total
                        </span>
                    </div>
                    <div style="font-size:12px;color:#bdc3c7;display:flex;align-items:center;gap:6px;">
                        <i class="fa fa-info-circle"></i>
                        Toggle cycles: Active → Inactive → Closed → Active
                    </div>
                </div>

                <div style="padding: 16px 20px;">
                    <div class="table-responsive">
                        <table id="imeiTable" class="table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>IMEI Number</th>
                                    <th>Status</th>
                                    <th>Schedule Start</th>
                                    <th>Schedule End</th>
                                    <th style="min-width:210px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devices as $device)
                                <tr>
                                    <td>
                                        <span class="imei-id-badge">{{ $device->id }}</span>
                                    </td>
                                    <td>
                                        <span class="imei-code">{{ $device->imei }}</span>
                                    </td>
                                    <td>
                                        @if($device->status === 'active')
                                            <span class="status-pill active">
                                                <span class="dot"></span> Active
                                            </span>
                                        @elseif($device->status === 'inactive')
                                            <span class="status-pill inactive">
                                                <span class="dot"></span> Inactive
                                            </span>
                                        @else
                                            <span class="status-pill close">
                                                <span class="dot"></span> Closed
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->schedule_start)
                                            <span class="schedule-val">
                                                <i class="fa fa-calendar"></i>
                                                {{ $device->schedule_start->format('d M Y, H:i') }}
                                            </span>
                                        @else
                                            <span class="schedule-na">— Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->schedule_end)
                                            <span class="schedule-val">
                                                <i class="fa fa-calendar-check-o"></i>
                                                {{ $device->schedule_end->format('d M Y, H:i') }}
                                            </span>
                                        @else
                                            <span class="schedule-na">— Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('imei-devices.edit', $device->id) }}" class="btn-act edit" title="Edit">
                                                <i class="fa fa-pencil"></i> Edit
                                            </a>

                                            <form action="{{ route('imei-devices.toggle-status', $device->id) }}" method="POST" style="display:inline;">
                                                @csrf @method('PATCH')
                                                @php
                                                    $nextLabel = $device->status === 'active' ? 'Set Inactive' : ($device->status === 'inactive' ? 'Set Closed' : 'Activate');
                                                @endphp
                                                <button type="submit" class="btn-act toggle" title="{{ $nextLabel }}">
                                                    <i class="fa fa-exchange"></i> {{ $nextLabel }}
                                                </button>
                                            </form>

                                            <a href="/tracker?imei={{ $device->imei }}" class="btn-act live" target="_blank" title="Live View">
                                                <i class="fa fa-map-marker"></i> Live
                                            </a>

                                            <form action="{{ route('imei-devices.destroy', $device->id) }}" method="POST"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Delete tracker {{ $device->imei }}? This cannot be undone.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-act delete" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:40px 20px;color:#95a5a6;">
                                        <i class="fa fa-map-o" style="font-size:36px;display:block;margin-bottom:10px;color:#dee2e6;"></i>
                                        No trackers found. <a href="{{ route('imei-devices.create') }}" style="color:#3498db;font-weight:600;">Add your first tracker</a>.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<script>
$(document).ready(function () {
    $('#imeiTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        lengthChange: true,
        pageLength: 25,
        scrollX: false,
        language: {
            search: '<i class="fa fa-search" style="margin-right:5px;color:#bdc3c7;"></i> Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ trackers',
            paginate: {
                previous: '<i class="fa fa-chevron-left"></i>',
                next: '<i class="fa fa-chevron-right"></i>'
            },
            emptyTable: 'No trackers available.'
        },
        columnDefs: [
            { orderable: false, targets: [5] }
        ],
        order: [[0, 'asc']]
    });
});
</script>
@endsection
