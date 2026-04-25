@php
use App\Helper\CommonHelper;
@endphp

@extends('layouts.apps')
@section('title', $device ? $device->imei . ' (Live Logs)' : 'Tracker Logs')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
/* ===== FULL PAGE WIDTH FIX ===== */
#main-content .tracker-page {
    width: 100%;
    padding: 10px;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
}

/* Remove side gaps */
#main-content .tracker-page .row {
    margin-left: -5px;
    margin-right: -5px;
}

#main-content .tracker-page [class*="col-"] {
    padding-left: 5px;
    padding-right: 5px;
}

/* Panels */
#main-content .tracker-page .c_panel,
#main-content .tracker-page .panel {
    width: 100%;
    border-radius: 8px;
    border: 1px solid #eaeaea;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    background: #fff;
    overflow: hidden;
}

#main-content .tracker-page .c_panel {
    border-top: 3px solid #0d6efd;
}

.tracker-page .c_title {
    padding: 15px 20px;
    border-bottom: 1px solid #eaeaea;
    background: #fdfdfd;
}
.tracker-page .c_title h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

/* Form Elements */
#main-content .tracker-page label {
    font-weight: 600;
    color: #444;
    margin-bottom: 6px;
    display: inline-block;
    font-size: 13px;
}

#main-content .tracker-page .form-control {
    border-radius: 5px;
    border: 1px solid #d1d5db;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
    height: 38px;
    padding: 6px 12px;
    font-size: 14px;
    transition: all 0.2s ease;
}

#main-content .tracker-page .form-control:focus {
    border-color: #8bb4f6;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    outline: none;
}

/* Enhancing Buttons */
#main-content .tracker-page .btn {
    border-radius: 5px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    font-size: 14px;
    padding: 0 16px;
    transition: all 0.2s ease;
    border: none;
}

#main-content .tracker-page .btn-info { background-color: #0dcaf0; color: #fff; box-shadow: 0 2px 4px rgba(13, 202, 240, 0.25); }
#main-content .tracker-page .btn-info:hover { background-color: #0bacce; box-shadow: 0 4px 6px rgba(13, 202, 240, 0.35); transform: translateY(-1px); }
#main-content .tracker-page .btn-primary { background-color: #0d6efd; color: #fff; box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25); }
#main-content .tracker-page .btn-primary:hover { background-color: #0b5ed7; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.35); transform: translateY(-1px); }
#main-content .tracker-page .btn-success { background-color: #198754; color: #fff; box-shadow: 0 2px 4px rgba(25, 135, 84, 0.25); }
#main-content .tracker-page .btn-success:hover { background-color: #157347; box-shadow: 0 4px 6px rgba(25, 135, 84, 0.35); transform: translateY(-1px); }
#main-content .tracker-page .btn-warning { background-color: #ffc107; color: #000; box-shadow: 0 2px 4px rgba(255, 193, 7, 0.25); }
#main-content .tracker-page .btn-warning:hover { background-color: #ffca2c; box-shadow: 0 4px 6px rgba(255, 193, 7, 0.35); transform: translateY(-1px); }
#main-content .tracker-page .btn-danger { background-color: #dc3545; color: #fff; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.25); }
#main-content .tracker-page .btn-danger:hover { background-color: #bb2d3b; box-shadow: 0 4px 6px rgba(220, 53, 69, 0.35); transform: translateY(-1px); }

/* Action Container Module */
.action-container {
    background: #fbfbfc;
    border: 1px solid #eef0f2;
    border-radius: 8px;
    padding: 15px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}

/* Filter Box Module */
.tracker-filter-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 20px;
}
.filter-container {
    background: #fff;
    border: 1px solid #eaeaea;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    display: flex;
    flex-wrap: nowrap;
    gap: 15px;
    align-items: flex-end;
}
.filter-container .form-group { margin-bottom: 0; flex: 1; }
.filter-container .form-group.action-group { flex: 0 0 auto; display: flex; gap: 10px; }
.protocol-settings-panel {
    background: linear-gradient(180deg, #fbfdff 0%, #f7fbff 100%);
    border: 1px solid #dbeafe;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 4px 15px rgba(13, 110, 253, 0.05);
}
.protocol-settings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}
.protocol-settings-title {
    font-size: 14px;
    font-weight: 700;
    color: #1d4ed8;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.protocol-settings-subtitle {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}
.protocol-settings-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-start;
}
.protocol-settings-grid .form-group {
    margin-bottom: 0;
    flex: 1;
    min-width: 220px;
}
.protocol-settings-panel .form-control {
    border: 1px solid #d1d5db;
    transition: all 0.2s ease;
}
.protocol-settings-panel .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}


/* Status Alerts */
.tracker-page .alert {
    border-radius: 6px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    padding: 12px 15px;
    font-size: 14px;
}

/* ===== TABLE RESPONSIVE ===== */
#main-content .tracker-page .panel-body { overflow-x: auto; }
#main-content .tracker-page table { width: 100%; min-width: 600px; margin-bottom: 0; }
#main-content .tracker-page th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #555; }
#main-content .tracker-page table td { vertical-align: middle; }

/* ===== MOBILE FIX ===== */
@media (max-width: 768px) {
    #main-content .tracker-page { padding: 5px; }
    #main-content .tracker-page [class*="col-"] { width: 100% !important; max-width: 100%; flex: 100%; margin-bottom: 10px; }
    
    .filter-container,
    .protocol-settings-grid {
        flex-direction: column;
        align-items: stretch;
    }
    .action-container, 
    .action-container > form, 
    .action-container > div:not([style*="width:1px"]) {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 15px !important;
    }

    .action-container > div[style*="width:1px"] {
        display: none !important;
    }
    
    #main-content .tracker-page .btn { width: 100%; }
    
    #main-content .tracker-page input,
    #main-content .tracker-page select,
    #main-content .tracker-page .form-group {
        width: 100% !important;
        min-width: 100% !important;
        flex: 1 1 100% !important;
    }

    .action-container select {
        flex: 1 1 auto !important;
        width: auto !important;
        min-width: 0 !important;
    }

    #main-content .tracker-page #logContainer { max-height: 350px; }
}

/* Logs Wrapping Fix */
.log-entry { overflow-wrap: break-word; }
#logContainer {
    overflow-x: hidden !important;
    border-radius: 0 0 8px 8px;
    padding: 10px;
}
.log-entry:last-child { border-bottom: none !important; }

.validation-pass { color:#22c55e !important; border-left:4px solid #22c55e !important; padding-left:8px !important; }
.validation-fail { color:#f87171 !important; border-left:4px solid #ef4444 !important; padding-left:8px !important; }
.validation-neutral { border-left:4px solid #6b7280 !important; padding-left:8px !important; }
.validation-badge { display:inline-flex; align-items:center; gap:4px; border-radius:999px; padding:2px 8px; font-size:11px; font-weight:800; margin-left:8px; }
.validation-badge-pass { background:#dcfce7; color:#15803d; }
.validation-badge-fail { background:#fee2e2; color:#b91c1c; }
.validation-badge-none { background:#e5e7eb; color:#374151; }
.log-entry { cursor:pointer; }
.validation-modal-table th { width:34%; background:#f8fafc; }
.validation-error-list { margin:0; padding-left:18px; color:#b91c1c; }
.protocol-fields-hint { color:#64748b; font-size:11px; margin-top:4px; min-height:14px; }
.protocol-validation-group.is-hidden { display:none !important; }
.protocol-validation-toggle-wrap { min-width: 160px; }

</style>

<section id="main-content">
    <section class="wrapper">
        <div class="tracker-page">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active"><a href="#">Logs View</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Tracker Logs Console</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="c_content">

                        <form method="GET" action="{{ route($route_prefix . '.tracker.index') }}" class="tracker-filter-form">
                            <div class="filter-container">
                                <div class="form-group" style="min-width: 200px;">
                                    <label style="text-transform:uppercase; letter-spacing:0.5px;">IMEI</label>
                                    <select name="imei" class="form-control" style="width:100%; border-radius:8px;">
                                        <option value="">Select IMEI</option>
                                        @foreach($allDevices as $d)
                                            <option value="{{ $d->imei }}" {{ $imei === $d->imei ? 'selected' : '' }}>{{ $d->imei }} ({{ $d->status_label }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label style="text-transform:uppercase; letter-spacing:0.5px;">Start Date &amp; Time</label>
                                    <input type="text" name="start_at" value="{{ isset($filters['start_at']) && $filters['start_at'] ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'Y-m-d\TH:i:s') : '' }}" class="form-control flatpickr-datetime" style="border-radius:8px;">
                                </div>
                                <div class="form-group">
                                    <label style="text-transform:uppercase; letter-spacing:0.5px;">End Date &amp; Time</label>
                                    <input type="text" name="end_at" value="{{ isset($filters['end_at']) && $filters['end_at'] ? CommonHelper::getDateAsTimeZone($filters['end_at'], 'Y-m-d\TH:i:s') : '' }}" class="form-control flatpickr-datetime" @if($device && $device->effective_end_at) max="{{ CommonHelper::getDateAsTimeZone($device->effective_end_at, 'Y-m-d\TH:i:s') }}" @endif style="border-radius:8px;">
                                </div>
                                <div class="form-group protocol-validation-toggle-wrap">
                                    <label style="text-transform:uppercase; letter-spacing:0.5px;">Protocol Validation</label>
                                    <select name="protocol_validation" id="protocolValidationToggle" class="form-control" style="width:100%; border-radius:8px;">
                                        <option value="0" {{ empty($protocolValidationEnabled) ? 'selected' : '' }}>OFF</option>
                                        <option value="1" {{ !empty($protocolValidationEnabled) ? 'selected' : '' }}>ON</option>
                                    </select>
                                </div>
                                <div class="form-group action-group" style="display:flex; flex-direction:column; gap:6px;">
                                    <label style="display:block; visibility:hidden; font-size:12px; margin:0;">Actions</label>
                                    <div style="display:flex; gap:10px; align-items:center;">
                                        <button type="submit" class="btn btn-primary" style="height:38px; border-radius:8px; font-weight:700; padding:0 20px; box-shadow:0 4px 12px rgba(13, 110, 253, 0.2); display:inline-flex; align-items:center; justify-content:center; margin:0;">
                                            <i class="fa fa-filter" style="margin-right:8px;"></i> Apply
                                        </button>
                                        @if($device)
                                        <a id="downloadLogsBtn" href="{{ route($route_prefix . '.tracker.logs.download', ['device' => $device->id, 'start_at' => request()->has('start_at') && request('start_at') ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'Y-m-d\TH:i') : '', 'end_at' => request()->has('end_at') && request('end_at') ? CommonHelper::getDateAsTimeZone($filters['end_at'], 'Y-m-d\TH:i') : '']) }}" class="btn btn-success" style="height:38px; border-radius:8px; font-weight:700; padding:0 20px; background:#198754; border:none; box-shadow:0 4px 12px rgba(25, 135, 84, 0.2); display:inline-flex; align-items:center; justify-content:center; margin:0;">
                                            <i class="fa fa-download" style="margin-right:8px;"></i> Download ({{ $totalLogsCount }})
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div id="protocolValidationPanel" class="protocol-settings-panel protocol-validation-group {{ empty($protocolValidationEnabled) ? 'is-hidden' : '' }}">
                                <div class="protocol-settings-header">
                                    <div>
                                        <p class="protocol-settings-title">Protocol Validation Settings</p>
                                        <p class="protocol-settings-subtitle">Choose the protocol and packet type here when validation mode is enabled.</p>
                                    </div>
                                </div>
                                <div class="protocol-settings-grid">
                                    <div class="form-group">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">Protocol</label>
                                        <select name="protocol_id" id="protocolSelect" class="form-control" style="width:100%; border-radius:8px;" {{ !empty($protocolValidationEnabled) ? '' : 'disabled' }}>
                                            <option value="">Select Protocol</option>
                                            @foreach($protocols as $protocol)
                                                <option value="{{ $protocol->id }}" {{ (string) $selectedProtocolId === (string) $protocol->id ? 'selected' : '' }}>{{ $protocol->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">Packet Type</label>
                                        <select name="packet_type_id" id="packetTypeSelect" class="form-control" style="width:100%; border-radius:8px;" {{ (!empty($protocolValidationEnabled) && $selectedProtocolId) ? '' : 'disabled' }}>
                                            <option value="">Auto Detect</option>
                                            @foreach($packetTypes as $packetType)
                                                <option value="{{ $packetType->id }}" {{ (string) $selectedPacketTypeId === (string) $packetType->id ? 'selected' : '' }}>{{ $packetType->name }}{{ $packetType->header_identifier ? ' (' . $packetType->header_identifier . ')' : '' }}</option>
                                            @endforeach
                                        </select>
                                        <div id="packetFieldsHint" class="protocol-fields-hint"></div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if($device)
                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-md-3"><div class="alert alert-info"><strong>IMEI:</strong> {{ $device->imei }}</div></div>
                                <div class="col-md-3"><div id="deviceStatusAlert" class="alert {{ $status === 'active' ? 'alert-success' : ($status === 'inactive' ? 'alert-warning' : 'alert-danger') }}"><strong>Status:</strong> <span id="deviceStatusLabel">{{ $device->status_label }}</span></div></div>


                                <div class="col-md-3">
                                    <div class="alert alert-default">
                                        <strong>Start:</strong> 
                                        {{ isset($filters['start_at']) ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'd-M-Y H:i:s') : 'N/A' }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="alert alert-default">
                                        <strong>End:</strong> 
                                        {{ isset($filters['end_at']) ? CommonHelper::getDateAsTimeZone($filters['end_at'], 'd-M-Y H:i:s') : 'N/A' }}
                                    </div>
                                </div>

                            </div>

                            <div class="row" style="margin-bottom:20px;">
                                <div class="col-md-12">
                                    <div class="action-container" style="background:#fff; border:1px solid #eaeaea; border-radius:12px; padding:15px; box-shadow:0 4px 15px rgba(0,0,0,0.03); display:flex; gap:20px; align-items:flex-end; flex-wrap:wrap;">
                                        
                                        <!-- Command Section -->
                                        <form method="POST" action="{{ route($route_prefix . '.tracker.commands.store', $device->id) }}" style="display:flex; flex-grow:2; gap:10px; align-items:flex-end; margin:0;">
                                            @csrf
                                            <div style="flex-grow:1; min-width:200px;">
                                                <label style="display:block; font-size:12px; font-weight:700; color:#4a5568; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;">Send Command</label>
                                                <input type="text" name="command" class="form-control" style="width:100%; border-radius:8px; border:1px solid #cbd5e0;" placeholder="Enter command to queue">
                                            </div>
                                            <button type="submit" class="btn btn-warning" style="height:38px; border-radius:8px; font-weight:600; padding:0 15px; background:#f6ad55; border:none; color:#fff;">
                                                <i class="fa fa-paper-plane" style="margin-right:6px;"></i> Queue
                                            </button>
                                            <button type="button" class="btn btn-danger" id="clearLogsBtn" style="height:38px; border-radius:8px; font-weight:600; padding:0 15px; background:transparent; border:1px solid #feb2b2; color:#c53030;">
                                                Clear
                                            </button>
                                        </form>
                                         <div style="width:1px; height:40px; background:#edf2f7; margin:0 5px;"></div>

                                         <!-- Controls Section -->
                                         <div style="display:flex; gap:20px; align-items:flex-end; flex-grow:1; justify-content:flex-end;">
                                             <div style="display:flex; flex-direction:column; gap:6px;">
                                                 <label style="font-size:12px; font-weight:700; color:#4a5568; text-transform:uppercase; letter-spacing:0.5px;">Tracking</label>
                                                 <div style="display:flex; align-items:center; gap:10px;">
                                                     <select id="streamToggle" class="form-control" style="width:140px; border-radius:8px; border:1px solid #cbd5e0; font-weight:600;">
                                                         <option value="ON" selected>LIVE STREAM</option>
                                                         <option value="OFF">OFF</option>
                                                     </select>
                                                     <span id="streamStatus" style="font-size:11px; font-weight:800; color:#718096; width:45px; text-align:center;">(INIT)</span>
                                                 </div>
                                             </div>

                                             <div id="autoReloadGroup" style="display:flex; flex-direction:column; gap:6px;">
                                                 <label style="font-size:12px; font-weight:700; color:#4a5568; text-transform:uppercase; letter-spacing:0.5px;">Interval</label>
                                                 <div style="display:flex; align-items:center; gap:8px;">
                                                     <select id="autoReloadSeconds" class="form-control" style="width:90px; border-radius:8px; border:1px solid #cbd5e0;">
                                                         <option value="OFF" selected>OFF</option>
                                                         <option value="10">10s</option>
                                                         <option value="20">20s</option>
                                                         <option value="30">30s</option>
                                                         <option value="60">60s</option>
                                                     </select>
                                                     <span id="reloadCountdown" style="font-size:11px; font-weight:800; color:#e53e3e; min-width:30px;"></span>
                                                 </div>
                                             </div>

                                             <button type="button" class="btn btn-info" id="refreshNowBtn" style="height:38px; border-radius:8px; font-weight:700; padding:0 20px; background:#4fd1c5; border:none; color:#fff; box-shadow:0 4px 10px rgba(79,209,197,0.3);">
                                                 <i class="fa fa-refresh" style="margin-right:6px;"></i> REFRESH
                                             </button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>History + Live Logs</strong></div>
                                        <div class="panel-body" id="logContainer" style="max-height:520px; overflow-y:auto; overflow-x:hidden; background:#111; color:#66ff66; font-family:monospace;">
                                            @php $srNo = $totalLogsCount > 0 ? ($totalLogsCount - $initialLogs->count() + 1) : 1; @endphp
                                                    @forelse($initialLogs as $log)
                                                @php
                                                    $displayPacket = $log->raw_packet;
                                                    $clientIp = '';
                                                    if (preg_match('/&client_ip=\/?([^"&}\s,]+)/', $displayPacket, $matches)) {
                                                        $clientIp = ltrim($matches[1], '\/');
                                                        $displayPacket = preg_replace('/&client_ip=\/?[^"&}\s,]+/', '', $displayPacket);
                                                    }
                                                @endphp
                                                @php
                                                    $validation = $log->validation ?? ['status' => 'none', 'label' => 'Not validated'];
                                                    $validationStatus = $validation['status'] ?? 'none';
                                                    $validationLabel = $validation['label'] ?? 'Not validated';
                                                    $validationClass = !empty($protocolValidationEnabled) ? ($validationStatus === 'pass' ? 'validation-pass' : ($validationStatus === 'fail' ? 'validation-fail' : 'validation-neutral')) : '';
                                                    $badgeClass = $validationStatus === 'pass' ? 'validation-badge-pass' : ($validationStatus === 'fail' ? 'validation-badge-fail' : 'validation-badge-none');
                                                @endphp
                                                <div class="log-entry {{ $validationClass }}" data-log-id="{{ $log->id }}" data-validation='@json($validation)' style="padding:4px 0; border-bottom:1px dashed #333;">
                                                    <div style="color:#9ea7ad; font-size:12px;">
                                                        [#{{ $srNo++ }}] [#{{ $log->id }}] [{{ $log->logged_at ? CommonHelper::getDateAsTimeZone($log->logged_at, 'Y-m-d H:i:s') : 'N/A' }}] 
                                                        Server IP: {{ $log->source_ip ?? 'N/A' }} 
                                                        @if($clientIp) | Client IP: {{ $clientIp }} @endif
                                                        @if(!empty($protocolValidationEnabled))
                                                            <span class="validation-badge {{ $badgeClass }}">{{ $validationLabel }}</span>
                                                            @if(!empty($validation['packet_type_name'])) <span style="color:#cbd5e1;">{{ $validation['packet_type_name'] }}</span> @endif
                                                        @endif
                                                    </div>
                                                    <div class="log-raw-packet">{{ $displayPacket }}</div>
                                                </div>
                                            @empty
                                                <div id="emptyLogState" style="padding:20px; color:#ccc;">No logs found for the selected filter.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>Queued / Sent Commands</strong></div>
                                        <div class="panel-body" id="commandsTableContainer">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Queued At</th>
                                                        <th>Command</th>
                                                        <th>Status</th>
                                                        <th>Updated At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($device->commands->take(20) as $command)
                                                        <tr>
                                                            <td>{{ $command->id }}</td>
                                                            <td>{{ $command->created_at ? CommonHelper::getDateAsTimeZone($command->created_at, 'd-M-Y H:i:s') : 'N/A' }}</td>
                                                            <td><code>{{ $command->command }}</code></td>
                                                            <td>{{ $command->status == 0 ? 'Pending' : 'Sent' }}</td>
                                                            <!-- <td>{{ $command->sent_at ? CommonHelper::getDateAsTimeZone($command->sent_at, 'd-M-Y H:i:s') : 'N/A' }}</td> -->
                                                            <td>{{ $command->status == 1 && $command->updated_at ? CommonHelper::getDateAsTimeZone($command->updated_at, 'd-M-Y H:i:s') : 'N/A' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="5" class="text-center">No commands queued yet.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">Select an IMEI to view saved and live logs.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        </div>
    </section>
</section>


@if($device)
<div class="modal" id="packetValidationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Packet Validation Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="packetValidationModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($device)
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const imei = @json($device->imei);
    const urlParams = new URLSearchParams(window.location.search);
    const startAt = urlParams.get('start_at') || @json($filters['start_at'] ?? '');
    const endAt = urlParams.get('end_at') || @json($filters['end_at'] ?? '');
    let lastLogId = {{ $initialLogs->max('id') ?? 0 }};
    let totalLogsCounter = {{ $totalLogsCount ?? 0 }};
    let lastCommandTs = @json(now()->toDateTimeString());
    let sseSource = null;
    let reloadHandle = null;
    let reconnectHandle = null;
    let isLoadingLogs = false;
    let secondsLeft = 0;
    let validationEnabled = @json(!empty($protocolValidationEnabled));
    let selectedProtocolId = @json($selectedProtocolId ?? '');
    let selectedPacketTypeId = @json($selectedPacketTypeId ?? '');
    const packetTypesUrlTemplate = @json(route($route_prefix . '.tracker.protocol.packet-types', ['protocol' => '__PROTOCOL__']));
    const addedLogIds = new Set();
    const validationByLogId = {};
    const packetTypeFieldsById = {};

    // Pre-populate with initial log IDs
    $('.log-entry').each(function() {
        const id = $(this).data('log-id');
        if (id) {
            addedLogIds.add(Number(id));
            const rawValidation = $(this).attr('data-validation');
            if (rawValidation) {
                try { validationByLogId[Number(id)] = JSON.parse(rawValidation); } catch (e) {}
            }
        }
    });

    function getValidationClasses(validation) {
        const status = validation && validation.status ? validation.status : 'none';
        return {
            row: status === 'pass' ? 'validation-pass' : (status === 'fail' ? 'validation-fail' : 'validation-neutral'),
            badge: status === 'pass' ? 'validation-badge-pass' : (status === 'fail' ? 'validation-badge-fail' : 'validation-badge-none'),
            label: validation && validation.label ? validation.label : 'Not validated'
        };
    }

    function appendLog(log) {
        if (!log || !log.id || addedLogIds.has(Number(log.id))) {
            return;
        }
        addedLogIds.add(Number(log.id));
        validationByLogId[Number(log.id)] = log.validation || null;

        totalLogsCounter++;
        $('#emptyLogState').remove();
        let displayPacket = log.raw_packet || '';
        let clientIp = '';
        const ipMatch = displayPacket.match(/&client_ip=\/?([^"&}\s,]+)/);
        if (ipMatch) {
            clientIp = ipMatch[1].replace(/^[\\\/]+/, '');
            displayPacket = displayPacket.replace(/&client_ip=\/?[^"&}\s,]+/, '');
        }

        const validation = validationEnabled ? (log.validation || null) : null;
        const validationClasses = getValidationClasses(validation);
        const packetTypeName = validation && validation.packet_type_name ? validation.packet_type_name : '';
        const rowClass = validationEnabled ? validationClasses.row : '';
        const validationMeta = validationEnabled
            ? `<span class="validation-badge ${validationClasses.badge}">${escapeHtml(validationClasses.label)}</span>${packetTypeName ? ' <span style="color:#cbd5e1;">' + escapeHtml(packetTypeName) + '</span>' : ''}`
            : '';

        $("#logContainer").append(`
            <div class="log-entry ${rowClass}" data-log-id="${log.id}" style="padding:4px 0; border-bottom:1px dashed #333;">
                <div style="color:#9ea7ad; font-size:12px;">
                    [#${totalLogsCounter}] [#${log.id}] [${log.logged_at_formatted || log.logged_at}] 
                    Server IP: ${log.source_ip || 'N/A'} ${clientIp ? '| Client IP: ' + clientIp : ''}
                    ${validationMeta}
                </div>
                <div class="log-raw-packet">${$("<div>").text(displayPacket).html()}</div>
            </div>
        `);
        lastLogId = Math.max(lastLogId, Number(log.id || 0));

        const logEntries = $('#logContainer .log-entry');
        if (logEntries.length > 500) {
            logEntries.first().remove();
        }

        const logContainer = document.getElementById('logContainer');
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    function updateDownloadUrl() {
        const $btn = $('#downloadLogsBtn');
        if (!$btn.length) return;

        const currentStart = $('input[name="start_at"]').val();
        const currentEnd = $('input[name="end_at"]').val();
        
        let href = $btn.attr('href');
        if (href.indexOf('?') !== -1) {
            href = href.split('?')[0];
        }
        
        let query = '?start_at=' + encodeURIComponent(currentStart) + '&end_at=' + encodeURIComponent(currentEnd) + '&protocol_validation=' + (validationEnabled ? '1' : '0');
        if (validationEnabled) {
            query += '&protocol_id=' + encodeURIComponent(selectedProtocolId || '') + '&packet_type_id=' + encodeURIComponent(selectedPacketTypeId || '');
        }
        $btn.attr('href', href + query);
    }

    function updateDownloadCount(serverTotal) {
        if (serverTotal !== undefined) {
            totalLogsCounter = serverTotal;
        }
        $("#downloadLogsBtn").html('<i class="fa fa-download" style="margin-right:8px;"></i> Download (' + totalLogsCounter + ')');
        updateDownloadUrl();
    }

    function getCurrentFilterWindow() {
        return {
            startAt: $('input[name="start_at"]').val() || startAt,
            endAt: $('input[name="end_at"]').val() || endAt
        };
    }

    function refreshCommandsTable() {
        const currentUrl = window.location.href.split('#')[0];
        $('#commandsTableContainer').load(currentUrl + ' #commandsTableContainer > *');
    }

    function clearReconnect() {
        if (reconnectHandle) {
            clearTimeout(reconnectHandle);
            reconnectHandle = null;
        }
    }

    function startSSE() {
        clearReconnect();
        stopAutoReload();

        if (sseSource) {
            sseSource.close();
        }

        $('#autoReloadGroup').css('opacity', '0.5').find('select').attr('disabled', true);
        $('#streamStatus').text('(LIVE)').css('color', '#198754');

        let streamUrl = `{{ route($route_prefix . '.tracker.stream') }}?imei=${imei}&last_id=${lastLogId}&last_command_ts=${encodeURIComponent(lastCommandTs)}&protocol_validation=${validationEnabled ? '1' : '0'}`;
        if (validationEnabled) {
            streamUrl += `&protocol_id=${encodeURIComponent(selectedProtocolId || '')}&packet_type_id=${encodeURIComponent(selectedPacketTypeId || '')}`;
        }
        sseSource = new EventSource(streamUrl);

        sseSource.addEventListener('log', function(e) {
            try {
                appendLog(JSON.parse(e.data));
                updateDownloadCount();
            } catch (err) {
                console.error('SSE Log Parse Error', err);
            }
        });

        sseSource.addEventListener('command_update', function(e) {
            try {
                const data = JSON.parse(e.data);
                lastCommandTs = data.ts;
                refreshCommandsTable();
            } catch (err) {
                console.error('SSE Command Parse Error', err);
            }
        });

        sseSource.onerror = function() {
            if (sseSource) {
                sseSource.close();
                sseSource = null;
            }

            if ($('#streamToggle').val() !== 'ON') {
                $('#streamStatus').text('(OFF)').css('color', '#6c757d');
                return;
            }

            $('#streamStatus').text('(RETRY)').css('color', '#d97706');
            clearReconnect();
            reconnectHandle = setTimeout(function() {
                if ($('#streamToggle').val() === 'ON') {
                    startSSE();
                }
            }, 3000);
        };
    }

    function stopSSE() {
        clearReconnect();

        if (sseSource) {
            sseSource.close();
        }

        sseSource = null;
        $('#streamStatus').text('(OFF)').css('color', '#718096');
        $('#autoReloadGroup').css('opacity', '1').find('select').attr('disabled', false);
    }

    function updateCountdownText() {
        if ($('#autoReloadSeconds').val() === 'OFF' || $('#streamToggle').val() === 'ON') {
            $('#reloadCountdown').text('');
            return;
        }

        $('#reloadCountdown').text('(in ' + secondsLeft + 's)');
    }

    function stopAutoReload() {
        if (reloadHandle) {
            clearInterval(reloadHandle);
        }

        reloadHandle = null;
        $('#reloadCountdown').text('');
    }

    function resetAutoReload() {
        stopAutoReload();

        const intervalValue = $('#autoReloadSeconds').val();
        if (intervalValue === 'OFF' || $('#streamToggle').val() === 'ON') {
            updateCountdownText();
            return;
        }

        secondsLeft = Number(intervalValue);
        updateCountdownText();

        reloadHandle = setInterval(function() {
            if ($('#streamToggle').val() === 'ON') {
                stopAutoReload();
                return;
            }

            secondsLeft--;

            if (secondsLeft <= 0) {
                loadLatestLogs({ resetTimer: false });
                secondsLeft = Number($('#autoReloadSeconds').val() || 0);
            }

            updateCountdownText();
        }, 1000);
    }

    function syncStatusBadge(status, statusLabel) {
        if (statusLabel) {
            $('#deviceStatusLabel').text(statusLabel);
        }

        if (!status) {
            return;
        }

        const $statusAlert = $('#deviceStatusAlert');
        $statusAlert.removeClass('alert-success alert-warning alert-danger');

        const isInactive = (status === 'inactive' || status === 'closed');
        
        // Disable/Enable command and stream controls
        const $controls = $('input[name="command"], button[type="submit"], #streamToggle, #autoReloadSeconds, #refreshNowBtn');
        $controls.prop('disabled', isInactive);
        
        // Visual feedback for disabled state
        $('.action-container').css('opacity', isInactive ? '0.6' : '1');
        $('.action-container').css('pointer-events', isInactive ? 'none' : 'auto');

        if (status === 'active') {
            $statusAlert.addClass('alert-success');
        } else if (status === 'inactive') {
            $statusAlert.addClass('alert-warning');
            stopSSE();
            stopAutoReload();
        } else {
            $statusAlert.addClass('alert-danger');
            stopSSE();
            stopAutoReload();
        }
    }

    function loadLatestLogs(options) {
        options = options || {};
        if (isLoadingLogs) {
            return;
        }

        let wasStreamOn = false;
        if ($('#streamToggle').val() === 'ON' && sseSource) {
            wasStreamOn = true;
            sseSource.close();
            sseSource = null;
            $('#streamStatus').text('(PAUSED)').css('color', '#d97706');
        }

        setTimeout(function() {
            isLoadingLogs = true;
            const $refreshBtn = $('#refreshNowBtn');
            const originalHtml = $refreshBtn.html();
            $refreshBtn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin" style="margin-right:6px;"></i> REFRESHING...');

            const requestData = { last_id: lastLogId, protocol_validation: validationEnabled ? 1 : 0 };
            if (validationEnabled) {
                requestData.protocol_id = selectedProtocolId || '';
                requestData.packet_type_id = selectedPacketTypeId || '';
            }
            
            // Auto-extend endAt if it's in the past (only for Today)
            const $endInput = $('input[name="end_at"]');
            const endPicker = $endInput[0] ? $endInput[0]._flatpickr : null;
            
            if (endPicker && endPicker.selectedDates[0]) {
                const endD = endPicker.selectedDates[0];
                const nowD = new Date();
                
                // If end date is today and more than 30 seconds in the past, bump it
                if (nowD - endD > 30000 && endD.toDateString() === nowD.toDateString()) {
                    endPicker.setDate(nowD);
                }
            }

            const currentWindow = getCurrentFilterWindow();
            requestData.start_at = currentWindow.startAt;
            requestData.end_at = currentWindow.endAt;

            $.ajax({
                url: `{{ route($route_prefix . '.tracker.logs.fetch', ['imei' => '__IMEI__']) }}`.replace('__IMEI__', encodeURIComponent(imei)),
                data: requestData,
                cache: false
            }).done(function(response) {
                if (requestData.last_id === 0 && response.total_count !== undefined) {
                    totalLogsCounter = response.total_count - (response.logs || []).length;
                }
                (response.logs || []).forEach(function(log) {
                    appendLog(log);
                });

                if (response.last_id) {
                    lastLogId = Math.max(lastLogId, Number(response.last_id));
                }

                syncStatusBadge(response.status, response.status_label);
                updateDownloadCount(response.total_count);
                refreshCommandsTable();
            }).fail(function(xhr) {
                console.error('Tracker refresh failed', xhr);
            }).always(function() {
                isLoadingLogs = false;
                $refreshBtn.prop('disabled', false).html(originalHtml);

                if (wasStreamOn && $('#streamToggle').val() === 'ON') {
                    startSSE();
                }

                if (options.resetTimer !== false && $('#streamToggle').val() !== 'ON') {
                    resetAutoReload();
                }
            });
        }, wasStreamOn ? 500 : 0);
    }


    function resetLogListForValidationChange() {
        addedLogIds.clear();
        Object.keys(validationByLogId).forEach(key => delete validationByLogId[key]);
        lastLogId = 0;
        totalLogsCounter = 0;
        $("#packetValidationModal").modal("hide");
        $("#logContainer").html('<div id="emptyLogState" style="padding:20px; color:#ccc;">' + (validationEnabled ? 'Loading logs with selected protocol validation...' : 'Loading raw logs...') + '</div>');
        loadLatestLogs({ resetTimer: true });
    }

    function loadPacketTypes(protocolId, selectedId) {
        const $packetSelect = $('#packetTypeSelect');
        $('#packetFieldsHint').text('');
        $packetSelect.html('<option value="">Auto Detect</option>');
        selectedPacketTypeId = selectedId || '';

        if (!protocolId) {
            $packetSelect.prop('disabled', true);
            return;
        }

        $packetSelect.prop('disabled', true).append('<option value="" disabled>Loading...</option>');
        $.ajax({
            url: packetTypesUrlTemplate.replace('__PROTOCOL__', encodeURIComponent(protocolId)),
            cache: false
        }).done(function(response) {
            $packetSelect.html('<option value="">Auto Detect</option>');
            (response.packet_types || []).forEach(function(type) {
                const label = type.name + (type.header_identifier ? ' (' + type.header_identifier + ')' : '');
                packetTypeFieldsById[type.id] = type.fields || [];
                const option = $('<option>').val(type.id).text(label).attr('data-fields-count', type.fields_count || 0);
                if (String(selectedPacketTypeId) === String(type.id)) option.prop('selected', true);
                $packetSelect.append(option);
            });
            $packetSelect.prop('disabled', false);
            updatePacketFieldsHint();
        }).fail(function() {
            $packetSelect.html('<option value="">Failed to load packet types</option>').prop('disabled', true);
        });
    }

    function updatePacketFieldsHint() {
        const selected = $('#packetTypeSelect option:selected');
        const count = selected.attr('data-fields-count');
        if ($('#protocolSelect').val() && $('#packetTypeSelect').val()) {
            const fields = packetTypeFieldsById[$('#packetTypeSelect').val()] || [];
            const preview = fields.slice(0, 6).map(field => field.name).join(', ');
            $('#packetFieldsHint').html((count || 0) + ' configured fields' + (preview ? ': ' + escapeHtml(preview) + (fields.length > 6 ? '...' : '') : '') + '.');
        } else if ($('#protocolSelect').val()) {
            $('#packetFieldsHint').text('Auto Detect will match by packet header.');
        } else {
            $('#packetFieldsHint').text('Select a protocol to enable validation.');
        }
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    }

    function showPacketValidationModal(logId) {
        if (!validationEnabled) {
            return;
        }
        const $row = $('.log-entry[data-log-id="' + logId + '"]');
        const validation = validationByLogId[Number(logId)] || null;
        const rawPacket = $row.find('.log-raw-packet').text();
        let html = '<p><strong>Raw Packet:</strong></p><pre style="white-space:pre-wrap; background:#f8fafc; border:1px solid #e5e7eb; padding:10px;">' + escapeHtml(rawPacket) + '</pre>';

        if (!validation || validation.status === 'none') {
            html += '<div class="alert alert-info">No protocol validation was applied to this packet.</div>';
            $('#packetValidationModalBody').html(html);
            $('#packetValidationModal').modal('show');
            return;
        }

        const pass = validation.status === 'pass';
        html += '<div class="alert ' + (pass ? 'alert-success' : 'alert-danger') + '"><strong>Status:</strong> ' + escapeHtml(validation.label || (pass ? 'Pass' : 'Fail')) + '</div>';
        html += '<table class="table table-bordered validation-modal-table"><tbody>';
        html += '<tr><th>Protocol</th><td>' + escapeHtml(validation.protocol_name || 'N/A') + '</td></tr>';
        html += '<tr><th>Packet Type</th><td>' + escapeHtml(validation.packet_type_name || 'N/A') + '</td></tr>';
        html += '</tbody></table>';

        const errors = validation.errors || {};
        const errorKeys = Object.keys(errors);
        if (errorKeys.length) {
            html += '<h5>Errors</h5><ul class="validation-error-list">';
            errorKeys.forEach(function(key) { html += '<li><strong>' + escapeHtml(key) + ':</strong> ' + escapeHtml(errors[key]) + '</li>'; });
            html += '</ul>';
        }

        html += '<h5 style="margin-top:15px;">Field Summary</h5><table class="table table-bordered table-striped"><thead><tr><th>Field</th><th>Value</th><th>Rule</th><th>Status</th><th>Error</th></tr></thead><tbody>';
        (validation.field_summary || []).forEach(function(field) {
            html += '<tr>' +
                '<td>' + escapeHtml(field.name) + '</td>' +
                '<td><code>' + escapeHtml(field.value) + '</code></td>' +
                '<td>' + escapeHtml((field.data_type || '') + (field.validation_type && field.validation_type !== 'none' ? ' / ' + field.validation_type : '')) + '</td>' +
                '<td><span class="label ' + (field.is_valid ? 'label-success' : 'label-danger') + '">' + (field.is_valid ? 'Pass' : 'Fail') + '</span></td>' +
                '<td>' + escapeHtml(field.error || '') + '</td>' +
            '</tr>';
        });
        html += '</tbody></table>';

        $('#packetValidationModalBody').html(html);
        $('#packetValidationModal').modal('show');
    }

    function syncProtocolValidationUI(resetSelection) {
        const $groups = $('.protocol-validation-group');
        const $protocolSelect = $('#protocolSelect');
        const $packetSelect = $('#packetTypeSelect');
        if (!validationEnabled) {
            $groups.addClass('is-hidden');
            $protocolSelect.prop('disabled', true);
            $packetSelect.prop('disabled', true).html('<option value="">Auto Detect</option>');
            $("#packetFieldsHint").text('Protocol validation is disabled.');
            $("#packetValidationModal").modal('hide');
            if (resetSelection) {
                selectedProtocolId = '';
                selectedPacketTypeId = '';
                $protocolSelect.val('');
                $packetSelect.val('');
            }
            return;
        }

        $groups.removeClass('is-hidden');
        $protocolSelect.prop('disabled', false);

        if (selectedProtocolId) {
            loadPacketTypes(selectedProtocolId, selectedPacketTypeId);
        } else {
            $packetSelect.prop('disabled', true).html('<option value="">Auto Detect</option>');
            updatePacketFieldsHint();
        }
    }

    $('#protocolValidationToggle').on('change', function() {
        validationEnabled = $(this).val() === '1';
        syncProtocolValidationUI(true);
        resetLogListForValidationChange();
    });

    $('#protocolSelect').on('change', function() {
        selectedProtocolId = $(this).val();
        selectedPacketTypeId = '';
        loadPacketTypes(selectedProtocolId, '');
        if (validationEnabled) {
            resetLogListForValidationChange();
        }
    });

    $('#packetTypeSelect').on('change', function() {
        selectedPacketTypeId = $(this).val();
        updatePacketFieldsHint();
        if (validationEnabled) {
            resetLogListForValidationChange();
        }
    });

    $('#logContainer').on('click', '.log-entry', function() {
        if (!validationEnabled) {
            return;
        }
        showPacketValidationModal($(this).data('log-id'));
    });

    syncProtocolValidationUI(false);

    $('#streamToggle').on('change', function() {
        if ($(this).val() === 'ON') {
            startSSE();
            return;
        }

        stopSSE();
        // Automatically set a default interval if currently OFF
        if ($('#autoReloadSeconds').val() === 'OFF') {
            $('#autoReloadSeconds').val('10');
        }
        resetAutoReload();
    });

    $('#autoReloadSeconds').on('change', function() {
        if ($('#streamToggle').val() !== 'ON') {
            resetAutoReload();
        }
    });

    let fetchCountXhr = null;
    let countUpdateTimeout = null;
    $('input[name="start_at"], input[name="end_at"]').on('change', function() {
        updateDownloadUrl();
        
        // Reset tracking state if filter changes
        lastLogId = 0;
        addedLogIds.clear();
        $('#logContainer').html('<div id="emptyLogState" style="padding:20px; color:#ccc;">Filters changed. Click REFRESH or APPLY to update logs.</div>');
        
        // Dynamically fetch log count for the new filter without reloading logs
        clearTimeout(countUpdateTimeout);
        countUpdateTimeout = setTimeout(function() {
            const currentStart = $('input[name="start_at"]').val();
            const currentEnd = $('input[name="end_at"]').val();
            if (!currentStart || !currentEnd) return;
            
            if (fetchCountXhr) fetchCountXhr.abort();
            
            // Show a temporary loading text
            $('#downloadLogsBtn').html('<i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i> Download (...)');
            
            fetchCountXhr = $.ajax({
                url: `{{ route($route_prefix . '.tracker.logs.fetch', ['imei' => '__IMEI__']) }}`.replace('__IMEI__', encodeURIComponent(imei)),
                data: Object.assign({ start_at: currentStart, end_at: currentEnd, count_only: 1, protocol_validation: validationEnabled ? 1 : 0 }, validationEnabled ? { protocol_id: selectedProtocolId || '', packet_type_id: selectedPacketTypeId || '' } : {}),
                cache: false
            }).done(function(response) {
                if (response.total_count !== undefined) {
                    updateDownloadCount(response.total_count);
                }
            }).fail(function() {
                // Return to original value on failure
                updateDownloadCount();
            });
        }, 300);
    });

    $('#refreshNowBtn').on('click', function() {
        loadLatestLogs({ resetTimer: true });
    });

    $('#clearLogsBtn').on('click', function() {
        $('#logContainer').html('<div id="emptyLogState" style="padding:20px; color:#ccc;">Logs cleared from browser only.</div>');
    });

    updateDownloadCount();

    if ($('#streamToggle').val() === 'ON') {
        startSSE();
    } else {
        resetAutoReload();
    }
    // AJAX Form Submission for Commands
    $('form[action="{{ route($route_prefix . ".tracker.commands.store", $device->id) }}"]').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Queueing...');
        
        let wasStreamOn = false;
        if ($('#streamToggle').val() === 'ON' && sseSource) {
            wasStreamOn = true;
            sseSource.close();
            sseSource = null;
            $('#streamStatus').text('(PAUSED)').css('color', '#d97706');
        }

        // Execute AJAX immediately. The backend uses micro-sleep to quickly release the stream worker.
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                form.find('input[name="command"]').val('');
            
            // Show global styled success message
            let globalSuccess = $('#ajaxSuccessMessage');
            globalSuccess.find('.message-text').text('Command queued successfully!');
            globalSuccess.css('display', 'flex').hide().fadeIn();
            
            if (window.ajaxSuccessTimeout) {
                clearTimeout(window.ajaxSuccessTimeout);
            }
            
            window.ajaxSuccessTimeout = setTimeout(function() {
                globalSuccess.fadeOut();
            }, 3000);
            
            // Reload commands table section
            const currentUrl = window.location.href.split('#')[0];
            $('#commandsTableContainer').load(currentUrl + ' #commandsTableContainer > *');
        },
        error: function(xhr) {
            alert('Failed to queue command.');
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
            if (wasStreamOn && $('#streamToggle').val() === 'ON') {
                startSSE();
            }
        }
        });
    });

    // Initial status sync
    syncStatusBadge(@json($status), @json($device ? $device->status_label : 'OFF'));

    // Asynchronously fetch the total count and latest logs to avoid blocking initial page load
    if (typeof imei !== 'undefined' && imei) {
        setTimeout(function() {
            loadLatestLogs({ resetTimer: true });
        }, 300);
    }
});
</script>
@endif
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr-datetime', {
        enableTime: true,
        enableSeconds: true,
        dateFormat: "Y-m-d\\TH:i:s",
        altInput: true,
        altFormat: "Y-m-d H:i:s",
        time_24hr: true
    });
});
</script>
@endsection










