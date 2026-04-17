@php
use App\Helper\CommonHelper;
@endphp

@extends('layouts.apps')
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
.filter-container {
    background: #fbfbfc;
    border: 1px solid #eef0f2;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}
.filter-container .form-group { margin-bottom: 0; }

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
    .action-container .form-inline {
        flex-direction: column;
        align-items: stretch;
    }
    
    #main-content .tracker-page .btn { width: 100%; }
    #main-content .tracker-page input,
    #main-content .tracker-page select,
    #main-content .tracker-page .form-group {
        width: 100% !important;
        min-width: 100% !important;
        flex: 1 1 100% !important;
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
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="GET" action="{{ route('tracker.index') }}" class="filter-container">
                            <div class="form-group">
                                <label>IMEI</label>
                                <select name="imei" class="form-control" style="min-width:240px;">
                                    <option value="">Select IMEI</option>
                                    @foreach($allDevices as $d)
                                        <option value="{{ $d->imei }}" {{ $imei === $d->imei ? 'selected' : '' }}>{{ $d->imei }} ({{ $d->status_label }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Date &amp; Time</label>
                                <input type="text" name="start_at" value="{{ isset($filters['start_at']) && $filters['start_at'] ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'Y-m-d\TH:i') : '' }}" class="form-control flatpickr-datetime">
                            </div>
                            <div class="form-group">
                                <label>End Date &amp; Time</label>
                                <input type="text" name="end_at" value="{{ isset($filters['end_at']) && $filters['end_at'] ? CommonHelper::getDateAsTimeZone($filters['end_at'], 'Y-m-d\TH:i') : '' }}" class="form-control flatpickr-datetime" @if($device && $device->effective_end_at) max="{{ CommonHelper::getDateAsTimeZone($device->effective_end_at, 'Y-m-d\TH:i') }}" @endif>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                            @if($device)
                            <div class="form-group">
                                <a id="downloadLogsBtn" href="{{ route('tracker.logs.download', ['device' => $device->id, 'start_at' => request()->has('start_at') && request('start_at') ? CommonHelper::getDateAsTimeZone($filters['start_at'], 'Y-m-d\TH:i') : '', 'end_at' => request()->has('end_at') && request('end_at') ? CommonHelper::getDateAsTimeZone($filters['end_at'], 'Y-m-d\TH:i') : '']) }}" class="btn btn-success">Download Logs ({{ $totalLogsCount }})</a>
                            </div>
                            @endif
                        </form>

                        @if($device)
                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-md-3"><div class="alert alert-info"><strong>IMEI:</strong> {{ $device->imei }}</div></div>
                                <div class="col-md-3"><div class="alert {{ $device->status === 'active' ? 'alert-success' : ($device->status === 'inactive' ? 'alert-warning' : 'alert-danger') }}"><strong>Status:</strong> {{ $device->status_label }}</div></div>


                                <div class="col-md-3">
                                    <div class="alert alert-default">
                                        <strong>Start:</strong> 
                                        {{ $device->effective_start_at ? CommonHelper::getDateAsTimeZone($device->effective_start_at, 'd-M-Y H:i:s') : 'N/A' }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="alert alert-default">
                                        <strong>End:</strong> 
                                        {{ $device->effective_end_at ? CommonHelper::getDateAsTimeZone($device->effective_end_at, 'd-M-Y H:i:s') : 'N/A' }}
                                    </div>
                                </div>

                            </div>

                            <div class="row" style="margin-bottom:20px; display:flex; flex-wrap:wrap; align-items:stretch;">
                                <div class="col-md-7 col-lg-8" style="margin-bottom: 10px;">
                                    <div class="action-container" style="height: 100%; display: flex; align-items: center;">
                                        <form method="POST" action="{{ route('tracker.commands.store', $device->id) }}" class="form-inline tracker-command-form" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; width: 100%; margin: 0;">
                                            @csrf
                                            <div class="form-group command-input-group" style="flex-grow:1; min-width: 200px; margin: 0;">
                                                <label>Send Command</label>
                                                <input type="text" name="command" class="form-control" style="width:100%;" placeholder="Enter command to queue">
                                            </div>
                                            <button type="submit" class="btn btn-warning">Queue Command</button>
                                            <button type="button" class="btn btn-danger" id="clearLogsBtn">Clear Logs (UI)</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-5 col-lg-4" style="margin-bottom: 10px;">
                                    <div class="action-container" style="height: 100%; display: flex; align-items: center; justify-content: flex-end;">
                                        <div class="form-inline auto-reload-inner" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; margin: 0; width: 100%; justify-content: flex-end;">
                                            <div class="form-group" style="text-align:left; margin:0;">
                                                <label>Auto Reload <span id="reloadCountdown" style="color:#dc3545; font-size:12px; font-weight:bold;"></span></label>
                                                <select id="autoReloadSeconds" class="form-control" style="min-width:110px;">
                                                    <option value="OFF" selected>OFF</option>
                                                    <option value="10">10 sec</option>
                                                    <option value="20">20 sec</option>
                                                    <option value="30">30 sec</option>
                                                    <option value="60">60 sec</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-info" id="refreshNowBtn">Refresh Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>History + Live Logs</strong></div>
                                        <div class="panel-body" id="logContainer" style="max-height:520px; overflow-y:auto; overflow-x:hidden; background:#111; color:#66ff66; font-family:monospace;">
                                            @forelse($initialLogs as $log)
                                                <div class="log-entry" data-log-id="{{ $log->id }}" style="padding:4px 0; border-bottom:1px dashed #333;">
                                                    <div style="color:#9ea7ad; font-size:12px;">[#{{ $log->id }}] [{{ $log->logged_at ? CommonHelper::getDateAsTimeZone($log->logged_at, 'Y-m-d H:i:s') : 'N/A' }}] IP: {{ $log->source_ip ?? 'N/A' }}</div>
                                                    <div>{{ $log->raw_packet }}</div>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script>
$(document).ready(function() {
    const imei = @json($device->imei);
    const pusherKey = @json(config('broadcasting.connections.pusher.key'));
    const pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const urlParams = new URLSearchParams(window.location.search);
    const startAt = urlParams.get('start_at') || @json($filters['start_at'] ?? '');
    const endAt = urlParams.get('end_at') || '';
    let lastLogId = {{ $initialLogs->max('id') ?? 0 }};
    let totalLogsCounter = {{ $totalLogsCount ?? 0 }};
    let reloadHandle = null;
    let valStr = $('#autoReloadSeconds').val();
    let secondsLeft = valStr === 'OFF' ? 0 : (Number(valStr) || 10);
    
    // Quick initialize right away so it doesn't stay blank
    updateCountdownText();

    function appendLog(log) {
        if ($('#logContainer').find('[data-log-id="' + log.id + '"]').length) {
            return;
        }
        $('#emptyLogState').remove();
        $('#logContainer').append(`
            <div class="log-entry" data-log-id="${log.id}" style="padding:4px 0; border-bottom:1px dashed #333;">
                <div style="color:#9ea7ad; font-size:12px;">[#${log.id}] [${log.logged_at_formatted || log.logged_at}] IP: ${log.source_ip || 'N/A'}</div>
                <div>${$('<div>').text(log.raw_packet).html()}</div>
            </div>
        `);
        lastLogId = Math.max(lastLogId, Number(log.id || 0));
        const logContainer = document.getElementById('logContainer');
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    function loadLatestLogs() {
        $('#reloadCountdown').text('(loading...)');
        $.ajax({
            url: `/api/tracker/logs/${imei}`,
            data: {
                last_id: lastLogId,
                start_at: startAt,
                end_at: endAt
            },
            cache: false
        }).done(function(response) {
            let newlyAddedCount = 0;
            (response.logs || []).forEach(log => {
                let exists = $('#logContainer').find('[data-log-id="' + log.id + '"]').length > 0;
                if (!exists) {
                    appendLog(log);
                    newlyAddedCount++;
                }
            });
            if (newlyAddedCount > 0) {
                totalLogsCounter += newlyAddedCount;
                $('a.btn-success:contains("Download Logs")').text('Download Logs (' + totalLogsCounter + ')');
            }
        }).always(function() {
            let val = $('#autoReloadSeconds').val();
            if (val !== 'OFF') {
                secondsLeft = Number(val) || 10;
            }
            updateCountdownText();
        });

        // Use jQuery's load method to refresh just the commands table section in the background
        const currentUrl = window.location.href.split('#')[0];
        $('#commandsTableContainer').load(currentUrl + ' #commandsTableContainer > *');
    }

    function updateCountdownText() {
        if ($('#autoReloadSeconds').val() === 'OFF') {
            $('#reloadCountdown').text('(OFF)');
        } else {
            $('#reloadCountdown').text('(in ' + secondsLeft + 's)');
        }
    }

    function resetAutoReload() {
        if (reloadHandle) {
            clearInterval(reloadHandle);
            reloadHandle = null;
        }
        let val = $('#autoReloadSeconds').val();
        if (val === 'OFF') {
            updateCountdownText();
            return;
        }
        secondsLeft = Number(val) || 10;
        updateCountdownText();
        
        reloadHandle = setInterval(function() {
            secondsLeft--;
            if (secondsLeft <= 0) {
                loadLatestLogs();
            } else {
                updateCountdownText();
            }
        }, 1000);
    }

    $('#refreshNowBtn').on('click', loadLatestLogs);
    $('#autoReloadSeconds').on('change', resetAutoReload);
    $('#clearLogsBtn').on('click', function() {
        $('#logContainer').html('<div id="emptyLogState" style="padding:20px; color:#ccc;">Logs cleared from browser only.</div>');
    });

    if (pusherKey) {
        const pusher = new Pusher(pusherKey, { cluster: pusherCluster, forceTLS: true });
        const channel = pusher.subscribe('tracker.' + imei);
        channel.bind('ImeiLogReceived', function(data) {
            if (data && data.log) {
                let exists = $('#logContainer').find('[data-log-id="' + data.log.id + '"]').length > 0;
                if (!exists) {
                    appendLog(data.log);
                    totalLogsCounter++;
                    $('a.btn-success:contains("Download Logs")').text('Download Logs (' + totalLogsCounter + ')');
                }
            }
        });
    }

    resetAutoReload();
});
</script>
@endif
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr-datetime', {
        enableTime: true,
        dateFormat: "Y-m-d\\TH:i",
        altInput: true,
        altFormat: "Y-m-d H:i",
        time_24hr: true
    });
});
</script>
@endsection

