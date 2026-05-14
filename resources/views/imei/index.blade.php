@extends('layouts.apps')
@section('content')

<style>
    .imei-page {
        font-family: 'Inter', sans-serif;
    }

    #main-content.imei-page .wrapper {
        padding-top: 10px !important;
    }

    .imei-page .top-page-header {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }

    .imei-breadcrumb-wrap {
        padding: 14px 0 18px 0;
    }

    .imei-breadcrumb {
        display: inline-flex;
        align-items: center;
        background: #1e293b;
        border-radius: 50px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }

    .imei-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        background: #76CF1C;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .imei-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .imei-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.65);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }

    .imei-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .imei-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .imei-page .c_content {
        padding-top: 16px !important;
    }

    .imei-stats-row {
        margin-bottom: 22px;
    }

    .imei-stat-card {
        position: relative;
        background: #fff;
        border: 1px solid #e8edf5;
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        overflow: hidden;
    }

    .imei-stat-card::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 4px;
        border-radius: 12px 0 0 12px;
        background: #76CF1C;
    }

    .imei-stat-card.is-inactive::after {
        background: #f59e0b;
    }

    .imei-stat-title {
        margin: 0 0 3px 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .imei-stat-value {
        margin: 0;
        color: #0f172a;
        font-size: 32px;
        line-height: 1;
        font-weight: 800;
    }

    .imei-stat-sub {
        margin: 4px 0 0 0;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .imei-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        background: rgba(34, 197, 94, 0.12);
        color: #16a34a;
    }

    .imei-stat-card.is-inactive .imei-stat-icon {
        background: rgba(245, 158, 11, 0.14);
        color: #d97706;
    }

    .imei-page .imei-table-wrap {
        border: 1px solid #dbe4ef;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    #imeiTable {
        width: 100% !important;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        border: none !important;
    }

    #imeiTable thead th {
        background: linear-gradient(180deg, #132035 0%, #1f314f 100%) !important;
        color: #dbe9ff !important;
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        font-weight: 700 !important;
        padding: 12px 11px !important;
        border: none !important;
        white-space: nowrap;
        position: relative;
        padding-right: 24px !important;
    }

    #imeiTable thead th:first-child {
        min-width: 56px;
    }

    #imeiTable.dataTable thead .sorting:after,
    #imeiTable.dataTable thead .sorting_asc:after,
    #imeiTable.dataTable thead .sorting_desc:after,
    #imeiTable.dataTable thead .sorting_asc_disabled:after,
    #imeiTable.dataTable thead .sorting_desc_disabled:after {
        top: 50% !important;
        transform: translateY(-50%) !important;
        right: 8px !important;
        margin-top: 0 !important;
    }

    #imeiTable tbody tr {
        background: #fff;
        transition: background 0.2s;
    }

    #imeiTable tbody td {
        vertical-align: middle !important;
        padding: 12px 11px !important;
        background: #fff !important;
        color: #243447;
        font-size: 13px;
        border-top: 1px solid #edf2f8 !important;
        border-left: none !important;
        border-right: none !important;
    }

    #imeiTable tbody tr:nth-child(even) td {
        background: #fbfdff !important;
    }

    #imeiTable tbody tr:hover td {
        background: #f2f8ff !important;
    }

    .imei-page .imei-id-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        min-height: 28px;
        padding: 2px 10px;
        border-radius: 999px;
        background: #ecf3ff;
        color: #153865;
        border: 1px solid #d6e5fa;
        font-size: 12px;
        font-weight: 800;
    }

    .imei-page .imei-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .imei-page .imei-status-pill.on {
        background: #e7f8ea;
        border: 1px solid #bfe8c9;
        color: #1f7a37;
    }

    .imei-page .imei-status-pill.off {
        background: #fff4df;
        border: 1px solid #f3ddb3;
        color: #9a6300;
    }

    .imei-page .imei-status-pill.close {
        background: #ffe8e8;
        border: 1px solid #f3c4c4;
        color: #a73131;
    }

    .imei-page .imei-pending-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        min-height: 28px;
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid #dbe4ef;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
    }

    .imei-page .imei-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .imei-page .imei-actions form {
        margin: 0;
    }

    .imei-page .imei-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px !important;
        font-size: 14px !important;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none !important;
        transition: opacity 0.2s;
    }

    .imei-page .imei-action-btn:hover {
        opacity: 0.85;
    }

    .imei-page .imei-action-btn.btn-edit,
    .imei-page .imei-action-btn.btn-log {
        background: #1d283e !important;
        color: #fff !important;
    }

    .imei-page .imei-action-btn.btn-toggle {
        background: #f4b64f !important;
        color: #fff !important;
    }

    .imei-page .imei-action-btn.btn-delete {
        background: #e25552 !important;
        color: #fff !important;
    }

    /* ====== RESPONSIVE ====== */
    @media (max-width: 767px) {
        .imei-breadcrumb-wrap { padding: 8px 0 10px 0; }
        .imei-breadcrumb { padding: 4px 12px 4px 5px; box-shadow: 0 2px 8px rgba(30,41,59,.12); }
        .imei-breadcrumb .bc-home { width: 24px; height: 24px; margin-right: 6px; }
        .imei-breadcrumb .bc-home i { font-size: 11px; }
        .imei-breadcrumb .bc-item { font-size: 11px; }
        .imei-breadcrumb .bc-sep { margin: 0 5px; font-size: 10px; }

        .imei-page .c_title { margin-bottom: 6px !important; padding: 12px 14px !important; }

        .imei-page .bgx-title-container {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 8px !important;
        }
        .imei-page .bgx-title-container .col-lg-6 {
            width: 100% !important;
            padding: 0 !important;
            text-align: left !important;
        }
        .imei-page .bgx-title-container h2 {
            font-size: 14px !important;
            margin: 0 !important;
        }
        .imei-page .bgx-title-container .btn,
        .imei-page .bgx-title-container .btn-success {
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            width: auto !important;
            font-size: 11px !important;
            padding: 5px 12px !important;
            height: 30px !important;
            border-radius: 8px !important;
        }

        .imei-stat-card { padding: 12px 14px; border-radius: 10px; }
        .imei-stat-value { font-size: 24px; }
        .imei-stat-title { font-size: 11px; }
        .imei-stat-sub { font-size: 12px; }
        .imei-stat-icon { width: 40px; height: 40px; font-size: 20px; border-radius: 10px; }
        .imei-stats-row { margin-bottom: 14px; }
        .imei-stats-row .col-lg-4,
        .imei-stats-row .col-md-6,
        .imei-stats-row .col-sm-6 {
            width: 100% !important;
            float: none !important;
            margin-bottom: 8px;
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .imei-page .imei-table-wrap { border-radius: 10px; }
        #imeiTable thead th { font-size: 10px !important; padding: 10px 8px !important; }
        #imeiTable tbody td { font-size: 12px; padding: 10px 8px !important; }
        .imei-page .imei-id-pill { min-width: 34px; min-height: 24px; font-size: 11px; padding: 1px 8px; }
        .imei-page .imei-status-pill { font-size: 11px; padding: 3px 8px; }
        .imei-page .imei-pending-pill { min-width: 28px; min-height: 24px; font-size: 11px; }
        .imei-page .imei-action-btn { width: 28px; height: 28px; font-size: 12px !important; border-radius: 6px !important; }
        .imei-page .imei-actions { gap: 4px; }

        .imei-page .c_content { padding-top: 10px !important; }

        .imei-page .dataTables_wrapper .dataTables_length,
        .imei-page .dataTables_wrapper .dataTables_filter {
            float: none !important;
            text-align: left !important;
            margin-bottom: 6px !important;
        }
        .imei-page .dataTables_wrapper .dataTables_filter input {
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    @media (max-width: 480px) {
        .imei-breadcrumb { padding: 3px 10px 3px 4px; }
        .imei-breadcrumb .bc-home { width: 22px; height: 22px; }
        .imei-breadcrumb .bc-item { font-size: 10px; }
        .imei-page .bgx-title-container h2 { font-size: 13px !important; }
        .imei-page .bgx-title-container .btn,
        .imei-page .bgx-title-container .btn-success {
            font-size: 10px !important;
            padding: 4px 10px !important;
            height: 28px !important;
        }
        .imei-stat-value { font-size: 20px; }
        .imei-stat-icon { width: 36px; height: 36px; font-size: 18px; }
        .imei-page .imei-action-btn { width: 26px; height: 26px; font-size: 11px !important; }
    }
</style>

<section id="main-content" class="imei-page">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="imei-breadcrumb-wrap">
                <nav class="imei-breadcrumb">
                    <div class="bc-home"><i class="fa fa-home"></i></div>
                    <a href="{{ url('admin') }}" class="bc-item">Home</a>
                    <span class="bc-sep">›</span>
                    <span class="bc-item active">Manage Trackers</span>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title" style="margin-bottom: 10px;">
                        <div class="row bgx-title-container">
                            <div class="col-lg-6">
                                <h2>IMEI Recording</h2>
                            </div>
                            <div class="col-lg-6 text-right">
                                @php
                                    $routePrefix = auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.' : '';
                                @endphp
                                <a href="{{ route($routePrefix . 'imei-devices.create') }}" class="btn btn-success"><i class="fa fa-plus-circle"></i> Add IMEI Recording</a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="c_content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @php
                            $activeCount = $devices->where('status', \App\Models\ImeiDevice::STATUS_ON)->count();
                            $inactiveCount = $devices->where('status', \App\Models\ImeiDevice::STATUS_OFF)->count();
                        @endphp

                        <div class="row imei-stats-row">
                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="imei-stat-card">
                                    <div>
                                        <p class="imei-stat-title">Active Devices</p>
                                        <p class="imei-stat-value">{{ $activeCount }}</p>
                                        <p class="imei-stat-sub">Status: ON</p>
                                    </div>
                                    <div class="imei-stat-icon">
                                        <i class="fa fa-toggle-on"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-sm-6">
                                <div class="imei-stat-card is-inactive">
                                    <div>
                                        <p class="imei-stat-title">Inactive Devices</p>
                                        <p class="imei-stat-value">{{ $inactiveCount }}</p>
                                        <p class="imei-stat-sub">Status: OFF</p>
                                    </div>
                                    <div class="imei-stat-icon">
                                        <i class="fa fa-toggle-off"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive imei-table-wrap">
                            <table id="imeiTable" class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>IMEI</th>
                                        <th>Status</th>
                                        <th>Start Date &amp; Time</th>
                                        <th>End Date &amp; Time</th>
                                        <th>Pending Commands</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($devices as $device)
                                        <tr>
                                            <td><span class="imei-id-pill">{{ $device->id }}</span></td>
                                            <td><span style="font-weight: 600; font-size: 14px; color: #333;">{{ $device->imei }}</span></td>
                                            <td>
                                                @if($device->status === \App\Models\ImeiDevice::STATUS_ON)
                                                    <span class="imei-status-pill on"><i class="fa fa-check-circle"></i> ON</span>
                                                @elseif($device->status === \App\Models\ImeiDevice::STATUS_OFF)
                                                    <span class="imei-status-pill off"><i class="fa fa-power-off"></i> OFF</span>
                                                @else
                                                    <span class="imei-status-pill close"><i class="fa fa-times-circle"></i> CLOSE</span>
                                                @endif
                                            </td>
                                            <td>{{ $device->effective_start_at ? \App\Helper\CommonHelper::getDateAsTimeZone($device->effective_start_at, 'd-M-Y H:i:s') : 'N/A' }}</td>
                                            <td>{{ $device->effective_end_at ? \App\Helper\CommonHelper::getDateAsTimeZone($device->effective_end_at, 'd-M-Y H:i:s') : 'N/A' }}</td>
                                            <td><span class="imei-pending-pill">{{ $device->pending_commands_count ?? 0 }}</span></td>
                                            <td>
                                                <div class="imei-actions">
                                                    <a href="{{ route($routePrefix . 'imei-devices.edit', $device->id) }}" class="btn imei-action-btn btn-edit" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    
                                                    <a href="#" onclick="event.preventDefault(); document.getElementById('toggle-form-{{ $device->id }}').submit();" class="btn imei-action-btn btn-toggle" title="{{ $device->status === \App\Models\ImeiDevice::STATUS_ON ? 'Turn OFF' : 'Turn ON' }}">
                                                        <i class="fa {{ $device->status === \App\Models\ImeiDevice::STATUS_ON ? 'fa-power-off' : 'fa-plug' }}"></i>
                                                    </a>
                                                    <form id="toggle-form-{{ $device->id }}" action="{{ route($routePrefix . 'imei-devices.toggle-status', $device->id) }}" method="POST" style="display: none;">
                                                        @csrf @method('PATCH')
                                                    </form>

                                                    <a href="{{ route(auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.tracker.index' : 'admin.tracker.index', ['imei' => $device->imei]) }}" class="btn imei-action-btn btn-log" title="Logs View">
                                                        <i class="fa fa-list-alt"></i>
                                                    </a>

                                                    <a href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this IMEI recording?')) document.getElementById('delete-form-{{ $device->id }}').submit();" class="btn imei-action-btn btn-delete" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                    <form id="delete-form-{{ $device->id }}" action="{{ route($routePrefix . 'imei-devices.destroy', $device->id) }}" method="POST" style="display: none;">
                                                        @csrf @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
<script>
$(function () {
    $('#imeiTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']]
    });
});
</script>
@endsection
