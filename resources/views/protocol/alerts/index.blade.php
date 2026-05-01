@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper protocol-page">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        @php
                            $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
                        @endphp
                        <li><a href="{{ route($routePrefix . '.index') }}">Protocol Management</a></li>
                        <li><a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}">Packet Types</a></li>
                        <li class="active"><a href="#">Alerts: {{ $packetType->name }}</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel protocol-panel">
                    <div class="c_title" style="margin-bottom: 10px;">
                        <div class="row bgx-title-container">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <h2>Alerts for: <span>{{ $packetType->name }}</span></h2>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 text-right">
                                <a href="{{ route($routePrefix . '.packet-types.alerts.create', $packetType->id) }}" class="btn btn-success protocol-add-btn">
                                    <i class="fa fa-plus-circle"></i> Add Alert
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="c_content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="protocol-table-wrap">
                            <table id="alert_table" class="table table-bordered table-striped protocol-table">
                                <thead>
                                    <tr>
                                        <th>Alert Name</th>
                                        <th>Conditions</th>
                                        <th style="width:100px;" class="text-center">Status</th>
                                        <th style="width:190px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($alerts as $alert)
                                        <tr>
                                            <td>
                                                <div class="protocol-name-cell">
                                                    <span class="protocol-row-icon" style="color: #f39c12; background: rgba(243, 156, 18, 0.1);"><i class="fa fa-bell"></i></span>
                                                    <span>
                                                        <strong>{{ $alert->name }}</strong>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @foreach($alert->conditions as $cond)
                                                    <span class="badge bg-info" style="margin-bottom: 2px;">
                                                        {{ $cond->field->name }} {{ $cond->operator }} {{ $cond->value }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $alert->is_active ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $alert->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="protocol-actions">
                                                    <a href="{{ route($routePrefix . '.packet-alerts.edit', $alert->id) }}" class="btn btn-primary btn-sm protocol-manage-btn" style="min-width: 80px;">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </a>
                                                    <form action="{{ route($routePrefix . '.packet-alerts.destroy', $alert->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this alert?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm protocol-delete-btn" style="margin-top: -2px;">
                                                            <i class="fa fa-trash"></i> Delete
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

@include('protocol.partials.protocol_styles')

<script>
$(function () {
    var table = $('#alert_table').DataTable({
        pageLength: 10,
        autoWidth: false,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [1, 3] }
        ]
    });
    // Wrap table in a responsive div to enable simple horizontal scrolling without breaking headers
    $('#alert_table').wrap('<div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;"></div>');
});
</script>
@endsection
