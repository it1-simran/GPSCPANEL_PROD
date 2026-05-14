@extends('layouts.apps')

@section('title', 'Test Plan Report')

@section('content')
@php
    $userType = auth()->check() ? strtolower(trim((string) auth()->user()->user_type)) : '';
    $routePrefix = $userType === 'support' ? 'support' : 'admin';
@endphp
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Test Execution Report #{{ $execution->id }}</strong>
                            </div>
                            <div class="col-md-6 text-right">
                                <span class="badge {{ $execution->status === 'passed' ? 'bg-success' : 'bg-danger' }}">
                                    {{ strtoupper($execution->status) }}
                                </span>
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr><th>Test Plan</th><td>{{ $execution->testPlan->name }}</td></tr>
                                    <tr><th>Device IMEI</th><td>{{ $execution->device->imei }}</td></tr>
                                    <tr><th>Started At</th><td>{{ $execution->started_at }}</td></tr>
                                    <tr><th>Completed At</th><td>{{ $execution->completed_at }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="well">
                                    <h5>Summary</h5>
                                    <p>{{ $execution->summary ?: 'No summary available.' }}</p>
                                </div>
                            </div>
                        </div>

                        <h4>Step Details</h4>
                        <div class="adv-table">
                            <table class="table table-striped table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Step</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($execution->logs as $log)
                                        <tr>
                                            <td>Step {{ $log->step->sequence }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $log->step->step_type)) }}</td>
                                            <td>
                                                <span class="badge {{ $log->status === 'pass' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ strtoupper($log->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $log->duration_ms }}ms</td>
                                            <td>
                                                @if($log->error_message)
                                                    <div class="text-danger small">{{ $log->error_message }}</div>
                                                @endif
                                                
                                                @if(!empty($log->output_data))
                                                    <button class="btn btn-xs btn-info" onclick="$(this).next().toggle()">View Data</button>
                                                    <pre style="display:none; font-size: 10px; margin-top: 5px;">{{ json_encode($log->output_data, JSON_PRETTY_PRINT) }}</pre>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="{{ route($routePrefix . '.test-validate.index') }}" class="btn btn-default">Run Another Test</a>
                            <a href="{{ route($routePrefix . '.test-plans.index') }}" class="btn btn-primary">Back to Plans</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>
@endsection
