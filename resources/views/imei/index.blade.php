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
                    <div class="c_title">
                        <h2>IMEI Recording</h2>
                        <div class="pull-right">
                            <a href="{{ route('imei-devices.create') }}" class="btn btn-success">Add IMEI Recording</a>
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
                            $closedCount = $devices->where('status', \App\Models\ImeiDevice::STATUS_CLOSE)->count();
                        @endphp

                        <div class="row" style="margin-bottom:20px;">
                            <div class="col-md-4"><div class="alert alert-success">ON: {{ $activeCount }}</div></div>
                            <div class="col-md-4"><div class="alert alert-warning">OFF: {{ $inactiveCount }}</div></div>
                            <div class="col-md-4"><div class="alert alert-danger">CLOSE: {{ $closedCount }}</div></div>
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
                                            <td><code>{{ $device->imei }}</code></td>
                                            <td>
                                                @if($device->status === \App\Models\ImeiDevice::STATUS_ON)
                                                    <span class="label label-success">ON</span>
                                                @elseif($device->status === \App\Models\ImeiDevice::STATUS_OFF)
                                                    <span class="label label-warning">OFF</span>
                                                @else
                                                    <span class="label label-danger">CLOSE</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($device->effective_start_at)->format('d-M-Y H:i:s') ?? 'N/A' }}</td>
                                            <td>{{ optional($device->effective_end_at)->format('d-M-Y H:i:s') ?? 'N/A' }}</td>
                                            <td>{{ $device->pending_commands_count ?? 0 }}</td>
                                            <!-- <td>
                                                <a href="{{ route('imei-devices.edit', $device->id) }}" class="btn btn-info btn-sm">Edit</a>
                                                <form action="{{ route('imei-devices.toggle-status', $device->id) }}" method="POST" style="display:inline;">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-warning btn-sm">{{ $device->status === \App\Models\ImeiDevice::STATUS_ON ? 'Turn OFF' : 'Turn ON' }}</button>
                                                </form>
                                                <a href="{{ route('tracker.index', ['imei' => $device->imei]) }}" class="btn btn-primary btn-sm">Logs View</a>
                                                <form action="{{ route('imei-devices.destroy', $device->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this IMEI recording?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td> -->
                                            <td style="min-width:320px;">
                                                <div style="align-items:center; gap:6px; white-space:nowrap;">
                                                    <a href="{{ route('imei-devices.edit', $device->id) }}" class="btn btn-info btn-sm">
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('imei-devices.toggle-status', $device->id) }}" method="POST" style="display:inline-flex; margin:0;">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="btn btn-warning btn-sm">
                                                            {{ $device->status === \App\Models\ImeiDevice::STATUS_ON ? 'Turn OFF' : 'Turn ON' }}
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('tracker.index', ['imei' => $device->imei]) }}" class="btn btn-primary btn-sm">
                                                        Logs View
                                                    </a>
                                                    <form action="{{ route('imei-devices.destroy', $device->id) }}" method="POST" style="display:inline-flex; margin:0;" onsubmit="return confirm('Delete this IMEI recording?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            Delete
                                                        </button>
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
