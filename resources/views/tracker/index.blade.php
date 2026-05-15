@php
use App\Helper\CommonHelper;
@endphp

@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/tracker-index.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/tracker-index.css')) }}">
@endpush
@section('title', $device ? $device->imei . ' (Live Logs)' : 'Tracker Logs')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


<section id="main-content">
    <section class="wrapper">
        <div class="tracker-page">
        @php
            $trackerImeiIndexRoute = ($route_prefix ?? 'admin') === 'admin' ? 'imei-devices.index' : 'support.imei-devices.index';
        @endphp
        <div class="tracker-breadcrumb-wrap">
            <nav class="tracker-breadcrumb" aria-label="Breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ route($trackerImeiIndexRoute) }}" class="bc-item">Manage Trackers</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Logs View</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title tracker-console-c-title">
                        <h2 class="tracker-console-title">
                            <i class="fa fa-list"></i>
                            Tracker Logs Console
                            @if($device)
                                <span class="tracker-console-imei-pill">{{ $device->imei }}</span>
                            @endif
                        </h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="c_content">

                        <form method="GET" action="{{ route($route_prefix . '.tracker.index') }}" class="tracker-filter-form">
                            <div class="filter-container">
                                <!-- Row 1 -->
                                <div class="filter-row">
                                    <div class="form-group" style="min-width: 200px; flex: 2;">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">IMEI</label>
                                        <select name="imei" class="form-control" style="width:100%; border-radius:8px;">
                                            <option value="">Select IMEI</option>
                                            @foreach($allDevices as $d)
                                                <option value="{{ $d->imei }}" {{ $imei === $d->imei ? 'selected' : '' }}>{{ $d->imei }} ({{ $d->status_label }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group protocol-validation-toggle-wrap">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">Protocol Validation</label>
                                        <select name="protocol_id" id="protocolValidationToggle" class="form-control" style="width:100%; border-radius:8px;">
                                            <option value="" {{ empty($selectedProtocolId) ? 'selected' : '' }}>OFF</option>
                                            @foreach($protocols as $protocol)
                                                <option value="{{ $protocol->id }}" {{ (string) $selectedProtocolId === (string) $protocol->id ? 'selected' : '' }}>{{ $protocol->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group protocol-validation-toggle-wrap">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">Alert Validation</label>
                                        <select name="alert_validation" id="alertValidationToggle" class="form-control" style="width:100%; border-radius:8px;">
                                            <option value="0" {{ empty($alertValidationEnabled) ? 'selected' : '' }}>OFF</option>
                                            <option value="1" {{ !empty($alertValidationEnabled) ? 'selected' : '' }}>ON</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="filter-divider"></div>

                                <!-- Row 2 -->
                                <div class="filter-row">
                                    <div class="form-group">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">Start Date &amp; Time</label>
                                        <input type="text" name="start_at" value="{{ isset($filters['start_at']) && $filters['start_at'] ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'Y-m-d\TH:i:s') : '' }}" class="form-control flatpickr-datetime" style="border-radius:8px;">
                                    </div>
                                    <div class="form-group">
                                        <label style="text-transform:uppercase; letter-spacing:0.5px;">End Date &amp; Time</label>
                                        <input type="text" name="end_at" value="{{ isset($filters['end_at']) && $filters['end_at'] ? CommonHelper::getDateAsTimeZone($filters['end_at'], 'Y-m-d\TH:i:s') : '' }}" class="form-control flatpickr-datetime" @if($device && $device->effective_end_at) max="{{ CommonHelper::getDateAsTimeZone($device->effective_end_at, 'Y-m-d\TH:i:s') }}" @endif style="border-radius:8px;">
                                    </div>
                                    <div class="form-group action-group">
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
                            </div>

                        </form>

                        @if($device)
                            {{-- Status Info Strip --}}
                            <div class="tracker-status-strip" id="deviceStatusStrip">
                                <div class="tracker-stat-card">
                                    <span class="tracker-stat-label">IMEI</span>
                                    <span class="tracker-stat-value imei-val">{{ $device->imei }}</span>
                                </div>
                                <div class="tracker-stat-card">
                                    <span class="tracker-stat-label">Status</span>
                                    <span class="tracker-stat-value" id="deviceStatusAlert">
                                        <span class="status-dot {{ $status === 'active' ? 'active' : ($status === 'inactive' ? 'inactive' : 'closed') }}"></span>
                                        <span id="deviceStatusLabel">{{ $device->status_label }}</span>
                                    </span>
                                </div>
                                <div class="tracker-stat-card">
                                    <span class="tracker-stat-label">Start</span>
                                    <span class="tracker-stat-value">{{ isset($filters['start_at']) ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'd-M-Y H:i:s') : 'N/A' }}</span>
                                </div>
                                <div class="tracker-stat-card">
                                    <span class="tracker-stat-label">End</span>
                                    <span class="tracker-stat-value">{{ $device->effective_end_at ? CommonHelper::getDateAsTimeZone($device->effective_end_at, 'd-M-Y H:i:s') : 'N/A' }}</span>
                                </div>
                            </div>

                            {{-- Command + Controls Bar --}}
                            <div class="tracker-command-bar">
                                <form method="POST" action="{{ route($route_prefix . '.tracker.commands.store', $device->id) }}" class="cmd-section" style="margin:0;">
                                    @csrf
                                    <div class="cmd-input-wrap">
                                        <label>Send Command</label>
                                        <input type="text" name="command" class="form-control" placeholder="Enter command to queue">
                                    </div>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fa fa-paper-plane" style="margin-right:6px;"></i> Queue
                                    </button>
                                    <button type="button" class="btn btn-danger" id="clearLogsBtn">
                                        Clear
                                    </button>
                                </form>
                                <div class="cmd-divider"></div>
                                <div class="ctrl-section">
                                    <div class="ctrl-group">
                                        <label>Tracking</label>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <select id="streamToggle" class="form-control" style="width:140px;">
                                                <option value="ON" selected>LIVE STREAM</option>
                                                <option value="OFF">OFF</option>
                                            </select>
                                            <span id="streamStatus" style="font-size:11px; font-weight:800; color:#76CF1C; width:45px; text-align:center;">(INIT)</span>
                                            <div id="sseReconnectBanner" class="gps-sse-banner" role="status" aria-live="polite" style="display:none;">Connection interrupted — reconnecting…</div>
                                        </div>
                                    </div>
                                    <div class="ctrl-group" id="autoReloadGroup">
                                        <label>Interval</label>
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <select id="autoReloadSeconds" class="form-control" style="width:90px;">
                                                <option value="OFF" selected>OFF</option>
                                                <option value="10">10s</option>
                                                <option value="20">20s</option>
                                                <option value="30">30s</option>
                                                <option value="60">60s</option>
                                            </select>
                                            <span id="reloadCountdown" style="font-size:11px; font-weight:800; color:#e53e3e; min-width:30px;"></span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-info" id="refreshNowBtn">
                                        <i class="fa fa-refresh" style="margin-right:6px;"></i> Refresh
                                    </button>
                                </div>
                            </div>



                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading panel-heading-green"><strong><i class="fa fa-list-ul" style="margin-right:8px;"></i>History + Live Logs</strong></div>
                                        <div class="panel-body" id="logContainer" style="max-height:520px; overflow-y:auto; overflow-x:hidden; background:#111; color:#66ff66; font-family:monospace;">
                                            @php 
                                                $srNo = $totalLogsCount > 0 ? ($totalLogsCount - $initialLogs->count() + 1) : 1;
                                                $initialAlertCount = count($alertHistory);
                                            @endphp
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
                                                            @if(!empty($alertValidationEnabled))
                                                                @if(!empty($validation['alert_report']['has_alerts']))
                                                                    <span class="alert-badge-tracker {{ $validation['alert_report']['status'] === 'fail' ? 'alert-badge-triggered' : 'alert-badge-cleared' }}">
                                                                        <i class="fa fa-bell-o"></i> {{ $validation['alert_report']['status'] === 'fail' ? 'ALERT TRIGGERED' : 'ALERTS CLEARED' }}
                                                                    </span>
                                                                @else
                                                                    <span class="alert-badge-tracker" style="background:#4a5568; color:#cbd5e1; border:1px solid #718096; opacity:0.8;">
                                                                        <i class="fa fa-bell-slash-o"></i> NO RULES
                                                                    </span>
                                                                @endif
                                                            @endif
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

                            <div class="row" id="alertHistorySection" style="margin-top: 20px; {{ !empty($alertValidationEnabled) ? '' : 'display:none;' }}">
                                <div class="col-md-12">
                                    <div class="panel panel-danger" style="border: 1px solid #fecaca; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.05);">
                                        <div class="panel-heading" style="background: #fef2f2; color: #991b1b; padding: 12px 20px; border-bottom: 1px solid #fecaca; display: flex; align-items: center; justify-content: space-between;">
                                            <h4 style="margin: 0; font-size: 15px; font-weight: 800;"><i class="fa fa-bell"></i> Alert Validation History</h4>
                                            <span class="badge" id="alertHistoryCount" style="background: #ef4444;">0</span>
                                        </div>
                                        <div class="panel-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                                            <table class="table table-hover" id="alertHistoryTable" style="margin-bottom: 0;">
                                                <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 10;">
                                                    <tr>
                                                        <th style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 20px;">Alert Name</th>
                                                        <th style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">Status</th>
                                                        <th style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Description</th>
                                                        <th style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 20px;">Date & Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="alertHistoryBody">
                                                    @forelse($alertHistory as $alert)
                                                        @php $isFail = ($alert['status'] === 'fail' || $alert['status'] === 'triggered'); @endphp
                                                        <tr class="alert-entry" style="border-left: 4px solid {{ $isFail ? '#ef4444' : '#10b981' }}; background: {{ $isFail ? '#fff5f5' : '#f0fdf4' }};">
                                                            <td style="padding: 12px 20px; font-weight: 700; color: #1e293b;">{{ $alert['name'] }}</td>
                                                            <td style="text-align: center;">
                                                                <span class="badge" style="background: {{ $isFail ? '#ef4444' : '#10b981' }}; font-size: 10px; text-transform: uppercase;">{{ $isFail ? 'FAIL' : 'PASS' }}</span>
                                                            </td>
                                                            <td style="font-size: 12px; color: #475569;">{{ $alert['description'] }}</td>
                                                            <td style="padding: 12px 20px; color: #64748b; font-family: monospace; font-size: 12px;">{{ $alert['timestamp'] }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr id="noAlertsRow">
                                                            <td colspan="4" class="text-center" style="padding: 30px; color: #94a3b8; font-style: italic;">No alerts evaluated in the current window.</td>
                                                        </tr>
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
    let alertCount = {{ count($alertHistory ?? []) }};
    $('#alertHistoryCount').text(alertCount);
    let lastCommandTs = @json(now()->toDateTimeString());
    let sseSource = null;
    let reloadHandle = null;
    let reconnectHandle = null;
    let isLoadingLogs = false;
    let secondsLeft = 0;
    let validationEnabled = @json(!empty($protocolValidationEnabled));
    let alertValidationEnabled = @json(!empty($alertValidationEnabled));
    let selectedProtocolId = @json($selectedProtocolId ?? '');
    const addedLogIds = new Set();
    const validationByLogId = {};

    // Pre-populate with initial log IDs and process existing alerts
    $('.log-entry').each(function() {
        const id = $(this).data('log-id');
        if (id) {
            addedLogIds.add(Number(id));
            const rawValidation = $(this).attr('data-validation');
            if (rawValidation) {
                try { 
                    const v = JSON.parse(rawValidation);
                    validationByLogId[Number(id)] = v;
                    
                    // Extract timestamp from the UI text if needed, or just pass the object
                    // The log-entry has the info we need
                    const timestampStr = $(this).find('div[style*="color:#9ea7ad"]').text();
                    const tsMatch = timestampStr.match(/\[([^\]]+)\]\s+\[([^\]]+)\]\s+\[([^\]]+)\]/);
                    const timestamp = tsMatch ? tsMatch[3] : 'N/A';

                    processLogAlerts({
                        id: id,
                        validation: v,
                        logged_at_formatted: timestamp
                    });
                } catch (e) {}
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
        const alertReport = validation && validation.alert_report ? validation.alert_report : null;
        
        const rowClass = validationEnabled ? validationClasses.row : '';
        
        let validationMeta = validationEnabled
            ? `<span class="validation-badge ${validationClasses.badge}">${escapeHtml(validationClasses.label)}</span>${packetTypeName ? ' <span style="color:#cbd5e1;">' + escapeHtml(packetTypeName) + '</span>' : ''}`
            : '';

        if (alertValidationEnabled) {
            if (alertReport && alertReport.has_alerts) {
                const alertStatus = alertReport.status === 'fail' ? 'alert-badge-triggered' : 'alert-badge-cleared';
                const alertLabel = alertReport.status === 'fail' ? 'ALERT TRIGGERED' : 'ALERTS CLEARED';
                validationMeta += ` <span class="alert-badge-tracker ${alertStatus}"><i class="fa fa-bell-o"></i> ${alertLabel}</span>`;
            } else {
                validationMeta += ` <span class="alert-badge-tracker" style="background:#4a5568; color:#cbd5e1; border:1px solid #718096; opacity:0.8;"><i class="fa fa-bell-slash-o"></i> NO RULES</span>`;
            }
        }

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

        // Process Alerts for the new log
        processLogAlerts(log);
    }

    function processLogAlerts(log) {
        if (!alertValidationEnabled || !log || !log.validation || !log.validation.alert_report || !log.validation.alert_report.has_alerts) {
            return;
        }

        const report = log.validation.alert_report;
        const timestamp = log.logged_at_formatted || log.logged_at;

        report.alerts.forEach(function(alert) {
            addAlertToTable({
                name: alert.name,
                status: alert.status, // Use the actual status (pass/fail)
                description: alert.conditions.map(c => `${c.field} (${c.actual}) ${c.operator} ${c.expected}`).join('; '),
                timestamp: timestamp
            });
        });
    }

    function addAlertToTable(alert) {
        const isFail = (alert.status === 'fail' || alert.status === 'triggered' || alert.status === 'TRIGGERED');
        alertCount++;
        $('#noAlertsRow').remove();
        
        const rowBg = isFail ? '#fff5f5' : '#f0fdf4';
        const rowBorder = isFail ? '#ef4444' : '#10b981';
        const badgeBg = isFail ? '#ef4444' : '#10b981';
        const statusLabel = isFail ? 'FAIL' : 'PASS';

        $('#alertHistoryBody').prepend(`
            <tr class="alert-entry" style="border-left: 4px solid ${rowBorder}; background: ${rowBg};">
                <td style="padding: 12px 20px; font-weight: 700; color: #1e293b;">${escapeHtml(alert.name)}</td>
                <td style="text-align: center;">
                    <span class="badge" style="background: ${badgeBg}; font-size: 10px; text-transform: uppercase;">${statusLabel}</span>
                </td>
                <td style="font-size: 12px; color: #475569;">${escapeHtml(alert.description)}</td>
                <td style="padding: 12px 20px; color: #64748b; font-family: monospace; font-size: 12px;">${escapeHtml(alert.timestamp)}</td>
            </tr>
        `);
        
        $('#alertHistoryCount').text(alertCount);

        // Keep table size manageable
        const rows = $('#alertHistoryBody tr.alert-entry');
        if (rows.length > 100) {
            rows.last().remove();
        }
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
        
        let query = '?start_at=' + encodeURIComponent(currentStart) + '&end_at=' + encodeURIComponent(currentEnd) + '&alert_validation=' + (alertValidationEnabled ? '1' : '0');
        if (validationEnabled) {
            query += '&protocol_id=' + encodeURIComponent(selectedProtocolId || '');
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
        $('#sseReconnectBanner').hide();

        let streamUrl = `{{ route($route_prefix . '.tracker.stream') }}?imei=${imei}&last_id=${lastLogId}&last_command_ts=${encodeURIComponent(lastCommandTs)}&alert_validation=${alertValidationEnabled ? '1' : '0'}`;
        if (validationEnabled) {
            streamUrl += `&protocol_id=${encodeURIComponent(selectedProtocolId || '')}`;
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
                $('#sseReconnectBanner').hide();
                return;
            }

            $('#streamStatus').text('(RETRY)').css('color', '#d97706');
            $('#sseReconnectBanner').show();
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
        $('#sseReconnectBanner').hide();
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

        const $dot = $('#deviceStatusAlert .status-dot');
        $dot.removeClass('active inactive closed');
        $dot.addClass(status === 'active' ? 'active' : (status === 'inactive' ? 'inactive' : 'closed'));

        const isInactive = (status === 'inactive' || status === 'closed');
        
        const $controls = $('input[name="command"], button[type="submit"], #streamToggle, #autoReloadSeconds, #refreshNowBtn');
        $controls.prop('disabled', isInactive);
        
        $('.tracker-command-bar').css('opacity', isInactive ? '0.6' : '1');
        $('.tracker-command-bar').css('pointer-events', isInactive ? 'none' : 'auto');

        if (status === 'active') {
            // active
        } else if (status === 'inactive') {
            stopSSE();
            stopAutoReload();
        } else {
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

            const requestData = { 
                last_id: lastLogId, 
                alert_validation: alertValidationEnabled ? 1 : 0
            };
            if (validationEnabled) {
                requestData.protocol_id = selectedProtocolId || '';
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
                if (requestData.last_id === 0) {
                    totalLogsCounter = response.total_count - (response.logs || []).length;
                    // Reset alert history on full refresh
                    alertCount = 0;
                    $('#alertHistoryBody').empty();
                    $('#alertHistoryCount').text('0');

                    if (response.alert_history && response.alert_history.length > 0) {
                        response.alert_history.reverse().forEach(function(h) {
                            addAlertToTable(h);
                        });
                    }

                    if (alertCount === 0) {
                        $('#alertHistoryBody').html('<tr id="noAlertsRow"><td colspan="4" class="text-center" style="padding: 30px; color: #94a3b8; font-style: italic;">No historical alerts found for this device.</td></tr>');
                    }
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
        alertCount = 0;
        $("#packetValidationModal").modal("hide");
        $("#logContainer").html('<div id="emptyLogState" style="padding:20px; color:#ccc;">' + (validationEnabled ? 'Loading logs with selected protocol validation...' : 'Loading raw logs...') + '</div>');
        
        if (alertValidationEnabled) {
            $('#alertHistorySection').show();
            $('#alertHistoryBody').html('<tr id="noAlertsRow"><td colspan="4" class="text-center" style="padding: 30px; color: #94a3b8; font-style: italic;">Loading alerts...</td></tr>');
            $('#alertHistoryCount').text('0');
        } else {
            $('#alertHistorySection').hide();
        }
        
        loadLatestLogs({ resetTimer: true });
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
        
        let html = '<div class="packet-details-container" style="display:flex; flex-direction:column; gap:20px;">';
        
        // Raw Packet
        html += '<div class="raw-packet-wrapper">';
        html += '<p style="margin-bottom:8px; font-weight:700; color:#334155; font-size:13px;"><i class="fa fa-code"></i> Raw Packet:</p>';
        html += '<div class="raw-packet-box">' + escapeHtml(rawPacket) + '</div>';
        html += '</div>';

        if (!validation || validation.status === 'none') {
            html += '<div class="alert alert-info" style="margin-top:10px;">No protocol validation was applied to this packet.</div>';
            html += '</div>';
            $('#packetValidationModalBody').html(html);
            $('#packetValidationModal').modal('show');
            return;
        }

        const pass = validation.status === 'pass';
        
        // Stats Bar
        html += '<div class="packet-stats-bar">';
        html += '<div class="stat-item"><span class="stat-label">Status</span><span class="stat-value ' + (pass ? 'status-pass' : 'status-fail') + '">' + 
                (pass ? '<i class="fa fa-check-circle"></i> ' : '<i class="fa fa-times-circle"></i> ') + 
                escapeHtml(validation.label || (pass ? 'Pass' : 'Fail')) + '</span></div>';
        html += '<div class="stat-item"><span class="stat-label">Protocol</span><span class="stat-value">' + escapeHtml(validation.protocol_name || 'N/A') + '</span></div>';
        html += '<div class="stat-item"><span class="stat-label">Packet Type</span><span class="stat-value">' + escapeHtml(validation.packet_type_name || 'N/A') + '</span></div>';
        html += '</div>';

        // Alert Validation Report Section
        if (alertValidationEnabled && validation.alert_report && validation.alert_report.has_alerts) {
            const report = validation.alert_report;
            html += '<div class="alert-report-container" style="background:#f8fafc; border:1px solid #e2e8f0; padding:15px; border-radius:8px;">';
            html += '<div class="alert-report-header" style="margin-bottom:12px;">';
            html += '<h5 class="alert-report-title" style="margin:0;"><i class="fa fa-bell"></i> Alert Validation Report</h5>';
            html += '<span class="validation-badge ' + (report.status === 'fail' ? 'validation-badge-fail' : 'validation-badge-pass') + '">' + 
                    (report.status === 'fail' ? 'FAIL - ALERTS TRIGGERED' : 'PASS - ALL CLEAR') + '</span>';
            html += '</div>';
            
            report.alerts.forEach(function(alert) {
                html += '<div class="alert-card ' + (alert.triggered ? 'triggered' : 'cleared') + '" style="margin-bottom:10px;">';
                html += '<div class="alert-name">' + escapeHtml(alert.name) + 
                        '<span class="alert-badge-tracker ' + (alert.triggered ? 'alert-badge-triggered' : 'alert-badge-cleared') + '">' + 
                        (alert.triggered ? 'TRIGGERED' : 'CLEARED') + '</span></div>';
                
                alert.conditions.forEach(function(cond) {
                    html += '<div class="alert-condition-row">';
                    html += '<span class="cond-field">' + escapeHtml(cond.field) + '</span>';
                    html += '<span class="cond-op">' + escapeHtml(cond.operator) + '</span>';
                    html += '<span class="cond-exp">' + escapeHtml(cond.expected) + '</span>';
                    html += '<span class="cond-arrow"><i class="fa fa-long-arrow-right"></i></span>';
                    html += '<span class="cond-act ' + (cond.is_satisfied ? 'match' : 'mismatch') + '">' + 
                            escapeHtml(cond.actual) + '</span>';
                    html += '</div>';
                });
                html += '</div>';
            });
            html += '</div>';
        }

        // Global Errors (if any) - 3 Column Layout
        const errors = validation.errors || {};
        const errorKeys = Object.keys(errors);
        if (errorKeys.length) {
            html += '<div style="background:#fef2f2; border:1px solid #fecaca; padding:12px; border-radius:8px;">';
            html += '<h5 style="margin:0 0 10px 0; color:#991b1b; font-size:14px; font-weight:700;"><i class="fa fa-warning"></i> Global Validation Errors</h5>';
            html += '<ul class="validation-error-list">';
            errorKeys.forEach(function(key) { html += '<li><strong>' + escapeHtml(key) + ':</strong> ' + escapeHtml(errors[key]) + '</li>'; });
            html += '</ul></div>';
        }

        // Field Summary Section
        html += '<div>';
        html += '<h5 style="margin-bottom:12px; font-weight:700; color:#334155; font-size:14px;"><i class="fa fa-list"></i> Field Summary</h5>';
        html += '<div class="field-summary-grid">';
        
        (validation.field_summary || []).forEach(function(field) {
            const fieldPass = field.is_valid;
            html += '<div class="field-card ' + (fieldPass ? 'is-valid' : 'is-invalid') + '">';
            html += '<div class="field-header">';
            html += '<span class="field-name" title="' + escapeHtml(field.name) + '">' + escapeHtml(field.name) + '</span>';
            html += '<span class="field-status-icon ' + (fieldPass ? 'status-pass' : 'status-fail') + '"><i class="fa ' + (fieldPass ? 'fa-check' : 'fa-warning') + '"></i></span>';
            html += '</div>';
            html += '<div class="field-value-box"><span class="field-value">' + (field.value === "" ? "<em>empty</em>" : escapeHtml(field.value)) + '</span></div>';
            
            const metaStr = (field.data_type || '') + (field.validation_type && field.validation_type !== 'none' ? ' / ' + field.validation_type : '');
            html += '<div class="field-meta"><span>' + escapeHtml(metaStr) + '</span></div>';
            
            if (!fieldPass && field.error) {
                html += '<div class="error-text">' + escapeHtml(field.error) + '</div>';
            }
            html += '</div>';
        });
        
        html += '</div></div>'; // end grid and field summary div

        html += '</div>'; // end container

        $('#packetValidationModalBody').html(html);
        $('#packetValidationModal').modal('show');
    }


    function syncProtocolValidationUI(resetSelection) {
        const $alertToggle = $('#alertValidationToggle');

        if (!validationEnabled) {
            $alertToggle.prop('disabled', true);
            if (resetSelection) {
                $alertToggle.val('0');
                alertValidationEnabled = false;
            }
            
            $("#packetValidationModal").modal('hide');
            if (resetSelection) {
                selectedProtocolId = '';
            }
            return;
        }

        $alertToggle.prop('disabled', false);
    }

    $('#protocolValidationToggle').on('change', function() {
        selectedProtocolId = $(this).val();
        validationEnabled = !!selectedProtocolId;
        syncProtocolValidationUI(true);
        resetLogListForValidationChange();
    });

    $('#logContainer').on('click', '.log-entry', function() {
        if (!validationEnabled) {
            return;
        }
        showPacketValidationModal($(this).data('log-id'));
    });

    syncProtocolValidationUI(false);

    $('#alertValidationToggle').on('change', function() {
        alertValidationEnabled = $(this).val() === '1';
        resetLogListForValidationChange();
    });

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
                data: Object.assign({ 
                    start_at: currentStart, 
                    end_at: currentEnd, 
                    count_only: 1, 
                    alert_validation: alertValidationEnabled ? 1 : 0
                }, validationEnabled ? { protocol_id: selectedProtocolId || '' } : {}),
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



