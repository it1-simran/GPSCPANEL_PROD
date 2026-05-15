@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/imei-index.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/imei-index.css')) }}">
@endpush
@section('content')



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
