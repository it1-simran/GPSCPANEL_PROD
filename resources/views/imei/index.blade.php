@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active"><a href="#">Manage Trackers</a></li>
                    </ul>
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

                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="widget-content bg-white" style="padding: 15px; border-radius: 6px; border: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <h3 class="font-bold" style="margin: 0 0 4px 0; font-size: 26px; color: #333;">{{ $activeCount }}</h3>
                                            <p style="margin: 0; font-size: 13px; color: #777; font-weight: 600;">Active (ON)</p>
                                        </div>
                                        <div>
                                            <i class="fa fa-toggle-on" style="color: #2ecc71; font-size: 36px;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="widget-content bg-white" style="padding: 15px; border-radius: 6px; border: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <h3 class="font-bold" style="margin: 0 0 4px 0; font-size: 26px; color: #333;">{{ $inactiveCount }}</h3>
                                            <p style="margin: 0; font-size: 13px; color: #777; font-weight: 600;">Inactive (OFF)</p>
                                        </div>
                                        <div>
                                            <i class="fa fa-toggle-off" style="color: #f1c40f; font-size: 36px;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="imeiTable" class="table table-bordered table-striped">
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
                                            <td>{{ $device->id }}</td>
                                            <td><span style="font-weight: 600; font-size: 14px; color: #333;">{{ $device->imei }}</span></td>
                                            <td>
                                                @if($device->status === \App\Models\ImeiDevice::STATUS_ON)
                                                    <span class="label label-success" style="padding: 5px 10px; font-size: 12px;"><i class="fa fa-check-circle"></i> ON</span>
                                                @elseif($device->status === \App\Models\ImeiDevice::STATUS_OFF)
                                                    <span class="label label-warning" style="padding: 5px 10px; font-size: 12px;"><i class="fa fa-power-off"></i> OFF</span>
                                                @else
                                                    <span class="label label-danger" style="padding: 5px 10px; font-size: 12px;"><i class="fa fa-times-circle"></i> CLOSE</span>
                                                @endif
                                            </td>
                                            <td>{{ $device->effective_start_at ? \App\Helper\CommonHelper::getDateAsTimeZone($device->effective_start_at, 'd-M-Y H:i:s') : 'N/A' }}</td>
                                            <td>{{ $device->effective_end_at ? \App\Helper\CommonHelper::getDateAsTimeZone($device->effective_end_at, 'd-M-Y H:i:s') : 'N/A' }}</td>
                                            <td><span class="badge" style="background-color: #3498db; padding: 5px 10px; font-size: 12px;">{{ $device->pending_commands_count ?? 0 }}</span></td>
                                            <td style="min-width:340px;">
                                                <div style="white-space:nowrap;">
                                                    <a href="{{ route($routePrefix . 'imei-devices.edit', $device->id) }}" class="btn btn-info btn-sm" style="margin-right: 4px;">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </a>
                                                    
                                                    <a href="#" onclick="event.preventDefault(); document.getElementById('toggle-form-{{ $device->id }}').submit();" class="btn btn-warning btn-sm" style="margin-right: 4px;">
                                                        <i class="fa {{ $device->status === \App\Models\ImeiDevice::STATUS_ON ? 'fa-power-off' : 'fa-plug' }}"></i> 
                                                        {{ $device->status === \App\Models\ImeiDevice::STATUS_ON ? 'Turn OFF' : 'Turn ON' }}
                                                    </a>
                                                    <form id="toggle-form-{{ $device->id }}" action="{{ route($routePrefix . 'imei-devices.toggle-status', $device->id) }}" method="POST" style="display: none;">
                                                        @csrf @method('PATCH')
                                                    </form>

                                                    <a href="{{ route(auth()->check() && strtolower(auth()->user()->user_type) === 'support' ? 'support.tracker.index' : 'admin.tracker.index', ['imei' => $device->imei]) }}" class="btn btn-primary btn-sm" style="margin-right: 4px;">
                                                        <i class="fa fa-list-alt"></i> Logs View
                                                    </a>

                                                    <a href="#" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this IMEI recording?')) document.getElementById('delete-form-{{ $device->id }}').submit();" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-trash"></i> Delete
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
