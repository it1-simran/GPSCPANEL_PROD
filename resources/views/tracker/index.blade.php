@extends('layouts.apps')
@section('content')
<style>
/* ===== FULL PAGE WIDTH FIX ===== */
#main-content .tracker-page {
    width: 100%;
    padding: 10px;
    box-sizing: border-box;
}

/* Remove side gaps */
#main-content .tracker-page .row {
    margin-left: 0;
    margin-right: 0;
}

#main-content .tracker-page [class*="col-"] {
    padding-left: 5px;
    padding-right: 5px;
}

/* Panels full width */
#main-content .tracker-page .c_panel,
#main-content .tracker-page .panel {
    width: 100%;
}

/* ===== TABLE RESPONSIVE ===== */
#main-content .tracker-page .panel-body {
    overflow-x: auto;
}

#main-content .tracker-page table {
    width: 100%;
    min-width: 600px;
}

/* ===== FORM RESPONSIVE ===== */
#main-content .tracker-page .form-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* ===== MOBILE FIX ===== */
@media (max-width: 768px) {

    /* Full width layout */
    #main-content .tracker-page {
        padding: 5px;
    }

    /* Stack columns */
    #main-content .tracker-page [class*="col-"] {
        width: 100% !important;
        max-width: 100%;
        flex: 100%;
    }

    /* Forms stack */
    #main-content .tracker-page .form-inline {
        flex-direction: column;
        align-items: stretch;
    }

    /* Buttons full width */
    #main-content .tracker-page .btn {
        width: 100%;
    }

    /* Inputs and Form Groups full width */
    #main-content .tracker-page input,
    #main-content .tracker-page select,
    #main-content .tracker-page .form-group {
        width: 100% !important;
        min-width: 100% !important;
        flex: 1 1 100% !important;
    }

    /* Logs height fix */
    #main-content .tracker-page #logContainer {
        max-height: 300px;
    }
}
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

                        <form method="GET" action="{{ route('tracker.index') }}" class="form-inline" style="margin-bottom:20px; display:flex; flex-wrap:wrap; gap:10px; align-items:end;">
                            <div class="form-group">
                                <label>IMEI</label><br>
                                <select name="imei" class="form-control" style="min-width:240px;">
                                    <option value="">Select IMEI</option>
                                    @foreach($allDevices as $d)
                                        <option value="{{ $d->imei }}" {{ $imei === $d->imei ? 'selected' : '' }}>{{ $d->imei }} ({{ $d->status_label }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Start Date &amp; Time</label><br>
                                <input type="datetime-local" name="start_at" value="{{ $filters['start_at'] ?? '' }}" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>End Date &amp; Time</label><br>
                                <input type="datetime-local" name="end_at" value="{{ $filters['end_at'] ?? '' }}" class="form-control" @if($device && $device->effective_end_at) max="{{ $device->effective_end_at->format('Y-m-d\\TH:i') }}" @endif>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                            </div>
                        </form>

                        @if($device)
                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-md-3"><div class="alert alert-info"><strong>IMEI:</strong> {{ $device->imei }}</div></div>
                                <div class="col-md-3"><div class="alert {{ $device->status === 'active' ? 'alert-success' : ($device->status === 'inactive' ? 'alert-warning' : 'alert-danger') }}"><strong>Status:</strong> {{ $device->status_label }}</div></div>
                                <div class="col-md-3"><div class="alert alert-default"><strong>Start:</strong> {{ optional($device->effective_start_at)->format('d-M-Y H:i:s') ?? 'N/A' }}</div></div>
                                <div class="col-md-3"><div class="alert alert-default"><strong>End:</strong> {{ optional($device->effective_end_at)->format('d-M-Y H:i:s') ?? 'N/A' }}</div></div>
                            </div>

                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-md-8">
                                    <form method="POST" action="{{ route('tracker.commands.store', $device->id) }}" class="form-inline tracker-command-form" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
                                        @csrf
                                        <div class="form-group command-input-group">
                                            <label>Send Command</label>
                                            <input type="text" name="command" class="form-control" style="width:100%;" placeholder="Enter command to queue">
                                        </div>
                                        <button type="submit" class="btn btn-warning">Queue Command</button>
                                        <a href="{{ route('tracker.logs.download', ['device' => $device->id, 'start_at' => $filters['start_at'], 'end_at' => $filters['end_at']]) }}" class="btn btn-success">Download Logs</a>
                                        <button type="button" class="btn btn-default" id="clearLogsBtn">Clear Logs (Front End)</button>
                                    </form>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="form-inline" style="display:flex; justify-content:end; gap:10px; align-items:end; flex-wrap:wrap;">
                                        <div class="form-group">
                                            <label>Auto Reload</label><br>
                                            <select id="autoReloadSeconds" class="form-control">
                                                <option value="10" selected>10 sec</option>
                                                <option value="20">20 sec</option>
                                                <option value="30">30 sec</option>
                                                <option value="60">60 sec</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-info" id="refreshNowBtn">Refresh Now</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><strong>History + Live Logs</strong></div>
                                        <div class="panel-body" id="logContainer" style="max-height:520px; overflow:auto; background:#111; color:#66ff66; font-family:monospace;">
                                            @forelse($initialLogs as $log)
                                                <div class="log-entry" data-log-id="{{ $log->id }}" style="padding:8px 0; border-bottom:1px dashed #333;">
                                                    <div style="color:#9ea7ad; font-size:12px;">[#{{ $log->id }}] [{{ optional($log->logged_at)->format('Y-m-d H:i:s') }}] IP: {{ $log->source_ip ?? 'N/A' }}</div>
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
                                        <div class="panel-body">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Command</th>
                                                        <th>Status</th>
                                                        <th>Sent At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($device->commands->take(20) as $command)
                                                        <tr>
                                                            <td>{{ $command->id }}</td>
                                                            <td><code>{{ $command->command }}</code></td>
                                                            <td>{{ $command->status == 0 ? 'Pending' : 'Sent' }}</td>
                                                            <td>{{ optional($command->sent_at)->format('d-M-Y H:i:s') ?? 'N/A' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center">No commands queued yet.</td></tr>
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
(function() {
    const imei = @json($device->imei);
    const pusherKey = @json(config('broadcasting.connections.pusher.key'));
    const pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    const startAt = @json($filters['start_at']);
    const endAt = @json($filters['end_at']);
    let lastLogId = {{ $initialLogs->max('id') ?? 0 }};
    let reloadHandle = null;

    function appendLog(log) {
        if ($('#logContainer').find('[data-log-id="' + log.id + '"]').length) {
            return;
        }
        $('#emptyLogState').remove();
        $('#logContainer').append(`
            <div class="log-entry" data-log-id="${log.id}" style="padding:8px 0; border-bottom:1px dashed #333;">
                <div style="color:#9ea7ad; font-size:12px;">[#${log.id}] [${log.logged_at}] IP: ${log.source_ip || 'N/A'}</div>
                <div>${$('<div>').text(log.raw_packet).html()}</div>
            </div>
        `);
        lastLogId = Math.max(lastLogId, Number(log.id || 0));
        const logContainer = document.getElementById('logContainer');
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    function loadLatestLogs() {
        $.get(`/api/tracker/logs/${imei}`, {
            last_id: lastLogId,
            start_at: startAt,
            end_at: endAt
        }).done(function(response) {
            (response.logs || []).forEach(appendLog);
        });
    }

    function resetAutoReload() {
        if (reloadHandle) {
            clearInterval(reloadHandle);
        }
        reloadHandle = setInterval(loadLatestLogs, Number($('#autoReloadSeconds').val()) * 1000);
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
                appendLog(data.log);
            }
        });
    }

    resetAutoReload();
})();
</script>
@endif
@endsection
