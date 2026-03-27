@extends('layouts.apps')
@section('content')
<style>
    .terminal-window {
        background-color: #1e1e1e;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        overflow: hidden;
        border: 1px solid #333;
        margin-top: 15px;
    }
    .terminal-header {
        background-color: #333;
        padding: 10px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #000;
    }
    .terminal-buttons {
        display: flex;
        gap: 6px;
    }
    .terminal-button {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .btn-close { background-color: #ff5f56; }
    .btn-minimize { background-color: #ffbd2e; }
    .btn-maximize { background-color: #27c93f; }
    .terminal-title {
        color: #aaa;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    .pulse-dot {
        width: 10px;
        height: 10px;
        background-color: #27c93f;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 8px #27c93f;
        animation: pulse 1.5s infinite;
        margin-right: 8px;
    }
    .pulse-dot.paused {
        background-color: #ffbd2e;
        box-shadow: 0 0 8px #ffbd2e;
        animation: none;
    }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(39, 201, 63, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(39, 201, 63, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(39, 201, 63, 0); }
    }
    .log-box {
        color: #4af626;
        font-family: 'Courier New', Courier, monospace;
        padding: 15px;
        height: 500px;
        overflow-y: auto;
        font-size: 13px;
    }
    .log-entry {
        border-bottom: 1px dashed #333;
        padding: 8px 0;
        word-break: break-all;
        line-height: 1.5;
        transition: background 0.2s ease;
    }
    .log-entry:hover {
        background-color: #2a2a2a;
    }
    .log-meta {
        color: #888;
        font-size: 11px;
        display: block;
        margin-bottom: 4px;
    }
    .device-info-card {
        background: #fdfdfd;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        border: 1px solid #eaeaea;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .device-stat {
        font-size: 13px;
        color: #777;
    }
    .device-stat strong {
        color: #222;
        font-size: 18px;
        display: block;
        margin-top: 5px;
    }
    .select2-container--default .select2-selection--single {
        border-radius: 6px !important;
        height: 40px !important;
        border: 1px solid #ddd !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active"><a href="#">Live Map / Logs</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Live Device Tracker</h2>
                        <div class="clearfix"></div>
                    </div>
                    
                    <div class="c_content">
                        <!-- Top Control Bar -->
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-md-5">
                                <form action="{{ route('tracker.index') }}" method="GET">
                                    <label style="font-weight: 600; color: #555;">Search & Select Device IMEI</label>
                                    <div class="input-group" style="width: 100%;">
                                        <select name="imei" class="select2" onchange="this.form.submit()" style="width: 100%;">
                                            <option value="">-- Choose a Device --</option>
                                            @foreach($allDevices as $d)
                                                <option value="{{ $d->imei }}" {{ $imei == $d->imei ? 'selected' : '' }}>
                                                    {{ $d->imei }} ({{ ucfirst($d->status) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                            
                            @if($device)
                                <div class="col-md-7 text-right">
                                    @if($device)
                                    <button class="btn btn-info" onclick="testConnection()">
                                        <i class="fa fa-plug"></i> Test Connection
                                    </button>
                                    <button id="btn-close" class="btn btn-danger" onclick="closeConnection()">
                                        <i class="fa fa-times-circle"></i> Close Connection
                                    </button>
                                    <button id="btn-toggle" class="btn btn-warning" onclick="toggleTracking()">
                                        <i class="fa fa-pause"></i> Pause Tracking
                                    </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Main Tracking View -->
                        @if($device)
                            <div class="device-info-card margin-top-20" style="margin-top: 20px;">
                                <div class="device-stat">
                                    Tracking IMEI
                                    <strong>{{ $device->imei }}</strong>
                                </div>
                                <div class="device-stat">
                                    Database Status
                                    <strong>
                                        <span class="label label-{{ $device->status == 'active' ? 'success' : 'danger' }}" style="font-size: 13px; padding: 4px 8px;">
                                            {{ ucfirst($device->status) }}
                                        </span>
                                    </strong>
                                </div>
                                <div class="device-stat">
                                    Active Schedule
                                    <strong>
                                        @if($device->schedule_start || $device->schedule_end)
                                            {{ $device->schedule_start ? $device->schedule_start->format('M d, g:i A') : 'Anytime' }} 
                                            - 
                                            {{ $device->schedule_end ? $device->schedule_end->format('M d, g:i A') : 'Forever' }}
                                        @else
                                            24/7 Monitoring
                                        @endif
                                    </strong>
                                </div>
                            </div>

                            <div class="terminal-window">
                                <div class="terminal-header">
                                    <div class="terminal-buttons">
                                        <div class="terminal-button btn-close"></div>
                                        <div class="terminal-button btn-minimize"></div>
                                        <div class="terminal-button btn-maximize"></div>
                                    </div>
                                    <div class="terminal-title">
                                        <span class="pulse-dot" id="status-dot"></span> <span id="status-text">Live TCP/IP Packet Stream</span>
                                    </div>
                                    <div style="width: 42px;"></div> <!-- Spacer for flex balance -->
                                </div>
                                
                                <div class="log-box" id="log-container">
                                    <div class="text-center" style="color:#555; margin-top: 180px;" id="loading-msg">
                                        <i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw" style="margin-bottom: 15px;"></i><br>
                                        Waiting for incoming hardware packets...
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info" style="margin-top: 20px; border-radius: 8px; border-left: 5px solid #31708f;">
                                <i class="fa fa-info-circle fa-lg margin-right-10"></i> Please select a device from the dropdown above to start live tracking its incoming data packets.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

@if($device)
<!-- Real-time Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<!-- Pure Pusher Logic (Echo Removed for Troubleshooting) -->

<script>
    let isTracking = true;
    const pusherKey = "{{ config('broadcasting.connections.pusher.key') }}";
    const pusherCluster = "{{ config('broadcasting.connections.pusher.options.cluster') }}";
    const imei = "{{ $device->imei }}";

    // Initialize Pure Pusher
    Pusher.logToConsole = true;
    const pusher = new Pusher(pusherKey, {
        cluster: pusherCluster,
        forceTLS: true
    });

    const channel = pusher.subscribe('tracker.' + imei);

    function testConnection() {
        $.post("{{ route('tracker.test', $device->id ?? 0) }}", {
            _token: "{{ csrf_token() }}"
        }, function(res) {
            console.log("Test pulse sent!");
        });
    }

    channel.bind('pusher:subscription_succeeded', function() {
        console.log("Successfully SUBSCRIBED to tracker." + imei);
    });

    channel.bind('ImeiLogReceived', function(data) {
        if (!isTracking) return;
        
        console.log("EVENT RECEIVED:", data);
        $('#loading-msg').hide();
        
        let log = data.log;
        let html = `
            <div class="log-entry">
                <span class="log-meta">[${log.logged_at}] <i class="fa fa-globe" style="margin: 0 3px;"></i> IP: ${log.source_ip || 'N/A'}</span>
                ${log.raw_packet}
            </div>
        `;
        $('#log-container').prepend(html);
    });

    function closeConnection() {
        if (!confirm('Are you sure you want to CLOSE the connection for this IMEI? This will stop all logging and broadcasting.')) {
            return;
        }

        $.ajax({
            url: "{{ route('tracker.close', $device->id ?? 0) }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                isTracking = false;
                pusher.unsubscribe('tracker.' + imei);
                
                const dot = document.getElementById('status-dot');
                const text = document.getElementById('status-text');
                const logContainer = document.getElementById('log-container');

                dot.style.background = '#e74c3c';
                text.innerText = "Connection Closed";
                
                $(logContainer).prepend('<div class="log-entry" style="color: #e74c3c; font-weight: bold;">[SYSTEM] Connection closed by user. Device status updated to "Close".</div>');
                
                // Hide buttons
                document.getElementById('btn-toggle').style.display = 'none';
                document.getElementById('btn-close').style.display = 'none';
            },
            error: function() {
                alert('Error closing connection. Please try again.');
            }
        });
    }

    function toggleTracking() {
        isTracking = !isTracking;
        const btn = document.getElementById('btn-toggle');
        const dot = document.getElementById('status-dot');
        const text = document.getElementById('status-text');
        
        if (isTracking) {
            btn.innerHTML = '<i class="fa fa-pause"></i> Pause Tracking';
            btn.classList.replace('btn-success', 'btn-warning');
            
            dot.classList.remove('paused');
            text.innerText = "Live TCP/IP Packet Stream";
        } else {
            btn.innerHTML = '<i class="fa fa-play"></i> Resume Tracking';
            btn.classList.replace('btn-warning', 'btn-success');
            
            dot.classList.add('paused');
            text.innerText = "Stream Paused";
        }
    }

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ placeholder: "-- Search and Select IMEI --" });
        }
    });
</script>
@endif
@endsection
