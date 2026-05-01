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
                        <li class="active"><a href="#">Packet Types</a></li>
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
                                <h2>Packet Types: <span>{{ $protocol->name }}</span></h2>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 text-right">
                                <a href="{{ route($routePrefix . '.packet-types.create', $protocol->id) }}" class="btn btn-success protocol-add-btn">
                                    <i class="fa fa-plus-circle"></i> Add Packet Type
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="c_content">

                        @php
                            $totalPacketTypes = $packetTypes->count();
                            $totalFields = $packetTypes->sum('fields_count');
                        @endphp

                        <div class="row protocol-stats-row">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $totalPacketTypes }}</h3>
                                        <p>Packet Types</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-cyan"><i class="fa fa-list-ul"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $totalFields }}</h3>
                                        <p>Total Parameters</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-blue"><i class="fa fa-cogs"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $protocol->name }}</h3>
                                        <p>Selected Protocol</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-green"><i class="fa fa-code-fork"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="protocol-table-wrap">
                            <table id="packet_table" class="table table-bordered table-striped protocol-table">
                                <thead>
                                    <tr>
                                        <th>Packet Name</th>
                                        <th class="text-center">Header Identifier</th>
                                        <th class="text-center">Fields Count</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($packetTypes as $type) 
                                        <tr class="p-0">
                                            <td>
                                                <div class="protocol-name-cell">
                                                    <span class="protocol-row-icon"><i class="fa fa-cube"></i></span>
                                                    <span>
                                                        <strong>{{ $type->name }}</strong>
                                                        @if(!$type->is_active)
                                                            <span class="badge badge-secondary" style="font-size: 0.7em; background-color: #6c757d; margin-left: 5px;">Disabled</span>
                                                        @endif
                                                        <small>Packet configuration</small>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="protocol-code-badge">{{ $type->header_identifier ?: 'N/A' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge protocol-field-badge">{{ $type->fields_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="protocol-actions">
                                                    <a href="{{ route($routePrefix . '.packet-types.alerts', $type->id) }}" class="btn btn-warning btn-sm protocol-manage-btn">
                                                        <i class="fa fa-bell"></i> Alerts
                                                    </a>
                                                    <a href="{{ route($routePrefix . '.fields', $type->id) }}" class="btn btn-primary btn-sm protocol-manage-btn">
                                                        <i class="fa fa-cogs"></i> Manage Parameters
                                                    </a>
                                                    <form action="{{ route($routePrefix . '.packet-types.destroy', $type->id) }}" method="POST" style="display:inline-flex; margin: 0; padding: 0;" onsubmit="return confirm('Are you sure you want to delete this packet type? This will also delete all its associated parameters.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger protocol-delete-btn" style="margin-top: -2px;">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                    <form action="{{ route($routePrefix . '.packet-types.toggle-status', $type->id) }}" method="POST" style="display:inline-flex; margin: 0; padding: 0;">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($type->is_active)
                                                        <button type="submit" class="btn btn-secondary protocol-disable-btn" style="margin-top: -2px; margin-left: 5px;">
                                                            <i class="fa fa-ban"></i> Disable
                                                        </button>
                                                        @else
                                                        <button type="submit" class="btn btn-success protocol-enable-btn" style="margin-top: -2px; margin-left: 5px;">
                                                            <i class="fa fa-check-circle"></i> Enable
                                                        </button>
                                                        @endif
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
        var table = $('#packet_table').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });
        
        // Wrap table in a responsive div to enable simple horizontal scrolling without breaking headers
        $('#packet_table').wrap('<div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;"></div>');
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        @if(session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{!! session('success') !!}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#fff',
            color: '#1e293b',
            iconColor: '#10b981',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        @endif
    });
</script>
@endsection