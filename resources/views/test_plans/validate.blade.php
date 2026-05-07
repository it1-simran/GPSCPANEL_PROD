@extends('layouts.apps')

@section('title', 'Test Automation Console')

@section('content')
    <style>
        .panel-heading {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: white !important;
            padding: 20px 25px !important;
            border-radius: 12px 12px 0 0 !important;
            border: none !important;
        }

        .panel {
            border-radius: 12px !important;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        }

        .control-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e2e8f0;
        }

        .form-group label {
            font-weight: 700;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .form-control {
            height: 42px !important;
            border-radius: 8px !important;
            border-color: #e2e8f0 !important;
        }

        .btn-start {
            background: #96c93d !important;
            color: white !important;
            border: none;
            height: 45px;
            border-radius: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(150, 201, 61, 0.2);
            color: white !important;
        }

        .status-section {
            margin-top: 30px;
            padding: 20px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .console-panel {
            background: #1e1e1e;
            border-radius: 12px;
            height: 600px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .console-header {
            background: #252526;
            padding: 12px 20px;
            border-bottom: 1px solid #333;
            color: #888;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
        }

        .console-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #d4d4d4;
        }

        .log-entry {
            margin-bottom: 12px;
            padding-left: 15px;
            border-left: 2px solid #444;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .log-time {
            color: #6a9955;
            margin-right: 10px;
        }

        .log-step {
            color: #569cd6;
            font-weight: bold;
            margin-right: 10px;
        }

        .log-type {
            color: #ce9178;
            text-transform: uppercase;
        }

        .log-status-pass {
            color: #4ec9b0;
            font-weight: bold;
        }

        .log-status-fail {
            color: #f44747;
            font-weight: bold;
        }

        .log-status-info {
            color: #569cd6;
            font-weight: normal;
            font-style: italic;
        }

        /* Scrollbar */
        .console-body::-webkit-scrollbar {
            width: 8px;
        }

        .console-body::-webkit-scrollbar-track {
            background: #1e1e1e;
        }

        .console-body::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }

        .console-body::-webkit-scrollbar-thumb:hover {
            background: #444;
        }
    </style>

    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <section class="panel">
                        <header class="panel-heading">
                            <div style="display: flex; align-items: center;">
                                <i class="fa fa-terminal" style="margin-right: 12px; font-size: 20px; color: #76CF1C;"></i>
                                <strong style="font-size: 16px; letter-spacing: 0.5px;">TEST AUTOMATION CONSOLE</strong>
                            </div>
                        </header>
                        <div class="panel-body" style="padding: 30px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="control-card">
                                        <form id="execution-form">
                                            @csrf
                                            <div class="form-group">
                                                <label>Select Device (IMEI)</label>
                                                <select name="imei" class=" select2" id="imei-select" required>
                                                    <option value="">Search IMEI...</option>
                                                    @foreach($devices as $device)
                                                        <option value="{{ $device->imei }}" {{ request('imei') == $device->imei ? 'selected' : '' }}>{{ $device->imei }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group" style="margin-top: 20px;">
                                                <label>Select Test Plan</label>
                                                <select name="test_plan_id" class="form-control" id="plan-select" required>
                                                    <option value="">Select Plan...</option>
                                                    @foreach($testPlans as $plan)
                                                        <option value="{{ $plan->id }}" {{ request('test_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block btn-start"
                                                id="start-btn">
                                                <i class="fa fa-play-circle" style="margin-right: 8px;"></i> Start Test Plan
                                            </button>
                                            <button type="button" class="btn btn-danger btn-block btn-start" id="stop-btn"
                                                style="display:none; background: #ef4444;">
                                                <i class="fa fa-stop-circle" style="margin-right: 8px;"></i> Stop Test Plan
                                            </button>
                                        </form>

                                        <div id="execution-status" class="status-section" style="display:none;">
                                            <div
                                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                                <span
                                                    style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase;">Status</span>
                                                <span id="status-badge" class="badge"
                                                    style="padding: 6px 12px; border-radius: 6px;">PENDING</span>
                                            </div>
                                            <div class="progress progress-striped active"
                                                style="height:8px; border-radius: 10px; background: #e2e8f0; margin-bottom: 10px;">
                                                <div id="execution-progress" class="progress-bar progress-bar-info"
                                                    style="width: 0%; border-radius: 10px; transition: width 0.5s ease;">
                                                </div>
                                            </div>
                                            <p id="summary-text" class="text-muted small"
                                                style="margin-top: 10px; font-style: italic;"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="console-panel">
                                        <div class="console-header">
                                            <span><i class="fa fa-angle-right"></i> EXECUTION_CONSOLE</span>
                                            <span id="log-stats">READY</span>
                                        </div>
                                        <div class="console-body" id="logs-container">
                                            <div id="logs-list">
                                                <div style="text-align: center; color: #444; margin-top: 100px;">
                                                    <i class="fa fa-plug fa-4x"
                                                        style="display: block; margin-bottom: 20px; opacity: 0.1;"></i>
                                                    <p style="letter-spacing: 2px; font-size: 14px; color: #555;">INITIALIZE
                                                        SEQUENCE TO START MONITORING</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </section>

    <script>
        $(document).ready(function () {
            let eventSource = null;
            let executionId = null;
            @php
                $userType = auth()->check() ? strtolower(trim((string) auth()->user()->user_type)) : '';
                $routePrefix = $userType === 'support' ? 'support' : 'admin';
            @endphp
            const routePrefix = '{{ $routePrefix }}';

            $('#execution-form').submit(function (e) {
                e.preventDefault();

                const data = $(this).serialize();
                $('#start-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> INITIALIZING...');
                $('#logs-list').empty();
                $('#execution-status').fadeIn();
                $('#status-badge').css({ 'background': '#e0f2fe', 'color': '#0369a1' }).text('RUNNING');
                $('#log-stats').text('CONNECTING...');

                $.post(`/${routePrefix}/test-execute`, data, function (response) {
                    if (response.success) {
                        executionId = response.execution_id;
                        $('#start-btn').hide();
                        $('#stop-btn').show();
                        startStreaming(executionId);
                    } else {
                        $('#start-btn').prop('disabled', false).html('<i class="fa fa-play-circle"></i> Start Test Plan');
                        alert('Error: ' + response.message);
                    }
                });
            });

            $('#stop-btn').click(function () {
                if (confirm('Are you sure you want to stop the test execution?')) {
                    $.post(`/${routePrefix}/test-stop/${executionId}`, { _token: '{{ csrf_token() }}' }, function () {
                        if (eventSource) eventSource.close();
                        $('#status-badge').css({ 'background': '#fee2e2', 'color': '#991b1b' }).text('STOPPED');
                        $('#stop-btn').hide();
                        $('#start-btn').show().prop('disabled', false).html('<i class="fa fa-play-circle"></i> Start Test Plan');
                    });
                }
            });

            function startStreaming(id) {
                if (eventSource) eventSource.close();

                eventSource = new EventSource(`/${routePrefix}/test-stream/${id}`);
                $('#log-stats').text('STREAMING');

                eventSource.addEventListener('log', function (e) {
                    const data = JSON.parse(e.data);
                    const statusClass = 'log-status-' + data.status;
                    let borderCol = '#444';
                    if(data.status === 'pass') borderCol = '#4ec9b0';
                    if(data.status === 'fail') borderCol = '#f44747';
                    if(data.status === 'info') borderCol = '#569cd6';

                    const html = `
                    <div class="log-entry" style="border-left-color: ${borderCol}">
                        <div>
                            <span class="log-time">[${new Date().toLocaleTimeString()}]</span>
                            ${data.status !== 'info' ? `<span class="log-step">STEP ${data.sequence}</span>` : ''}
                            <span class="log-type">${data.type}</span>
                            ${data.duration > 0 ? `<span style="float: right; color: #555;">${data.duration}ms</span>` : ''}
                        </div>
                        <div style="margin-top: 5px;">
                            <span class="${statusClass}">[${data.status.toUpperCase()}]</span>
                            ${data.message ? `<span style="color: #bbb; margin-left: 10px;">${data.message}</span>` : ''}
                        </div>
                        ${data.output && data.output.raw_packet ? `
                            <div style="margin-top: 8px; background: #252526; padding: 10px; border-radius: 4px; border-left: 2px solid #569cd6; font-size: 11px; overflow-x: auto; white-space: nowrap;">
                                <span style="color: #569cd6; font-weight: bold; margin-right: 8px;">REFLECTED_PACKET:</span>
                                <span style="color: #aaa;">${data.output.raw_packet}</span>
                            </div>
                        ` : ''}
                    </div>
                `;
                    $('#logs-list').append(html);
                    $('#logs-container').scrollTop($('#logs-container')[0].scrollHeight);

                    // Update progress (rough estimate)
                    const totalSteps = $('#plan-select option:selected').text().match(/\d+/) || [10];
                    const currentProgress = Math.min((data.sequence / totalSteps[0]) * 100, 95);
                    $('#execution-progress').css('width', currentProgress + '%');
                });

                eventSource.addEventListener('complete', function (e) {
                    const data = JSON.parse(e.data);
                    eventSource.close();

                    const isPass = data.status === 'passed';
                    const badgeBg = isPass ? '#dcfce7' : '#fee2e2';
                    const badgeColor = isPass ? '#15803d' : '#991b1b';

                    $('#status-badge').css({ 'background': badgeBg, 'color': badgeColor }).text(data.status.toUpperCase());
                    $('#summary-text').text(data.summary);
                    $('#execution-progress').css('width', '100%').removeClass('progress-bar-info').addClass(isPass ? 'progress-bar-success' : 'progress-bar-danger');
                    $('#stop-btn').hide();
                    $('#start-btn').show().prop('disabled', false).html('<i class="fa fa-play-circle"></i> Start Test Plan');
                    $('#log-stats').text('COMPLETED');

                    const finalLog = `
                    <div style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px; text-align: center;">
                        <div style="font-size: 18px; font-weight: bold; color: ${isPass ? '#4ec9b0' : '#f44747'}; letter-spacing: 2px;">
                            SEQUENCE ${data.status.toUpperCase()}
                        </div>
                        <div style="color: #666; font-size: 11px; margin-top: 5px;">COMPLETED AT: ${data.completed_at}</div>
                        <div style="margin-top: 15px; color: #aaa; background: #252526; padding: 15px; border-radius: 8px;">${data.summary}</div>
                    </div>
                `;
                    $('#logs-list').append(finalLog);
                    $('#logs-container').scrollTop($('#logs-container')[0].scrollHeight);
                });

                eventSource.onerror = function () {
                    console.error('SSE connection lost');
                    $('#log-stats').text('CONNECTION_LOST');
                };
            }
        });
    </script>
@endsection