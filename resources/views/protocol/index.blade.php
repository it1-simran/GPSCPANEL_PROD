@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper protocol-page">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Protocol Management</a></li>
                        <li class="active"><a href="#">Protocols</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel protocol-panel">
                        @php
                            $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
                        @endphp
                        <div class="row bgx-title-container">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <h2>Protocol Management</h2>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 text-right">
                                <a href="{{ route($routePrefix . '.create') }}" class="btn btn-success protocol-add-btn">
                                    <i class="fa fa-plus-circle"></i> Add New Protocol
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="c_content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @php
                            $totalProtocols = $protocols->count();
                            $activeProtocols = $protocols->where('is_active', true)->count();
                            $packetCount = $protocols->sum('packet_types_count');
                        @endphp

                        <div class="row protocol-stats-row">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $totalProtocols }}</h3>
                                        <p>Total Protocols</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-blue"><i class="fa fa-code-fork"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $activeProtocols }}</h3>
                                        <p>Active Protocols</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-green"><i class="fa fa-check-circle"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $packetCount }}</h3>
                                        <p>Packet Types</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-cyan"><i class="fa fa-list-ul"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="protocol-table-wrap">
                            <table id="protocol_table" class="table table-bordered table-striped protocol-table">
                                <thead>
                                    <tr>
                                        <th style="width:80px;" class="text-center">Sr. No.</th>
                                        <th>Protocol Name</th>
                                        <th style="width:220px;" class="text-center">Packet Types</th>
                                        <th style="width:130px;" class="text-center">Status</th>
                                        <th style="width:140px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($protocols as $index => $protocol)
                                        <tr>
                                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="protocol-name-cell">
                                                    <span class="protocol-row-icon"><i class="fa fa-code-fork"></i></span>
                                                    <span>
                                                        <strong>{{ $protocol->name }}</strong>
                                                        <small>Created: {{ $protocol->created_at ? $protocol->created_at->format('M d, Y') : 'N/A' }}</small>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" class="btn btn-info btn-sm protocol-packet-btn">
                                                    <i class="fa fa-list-ul"></i>
                                                    <span>View Packets</span>
                                                    <span class="badge protocol-count-badge">{{ $protocol->packet_types_count }}</span>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @if($protocol->is_active)
                                                    <span class="label label-success protocol-status"><i class="fa fa-check-circle"></i> Active</span>
                                                @else
                                                    <span class="label label-danger protocol-status"><i class="fa fa-times-circle"></i> Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="protocol-actions">
                                                    <a href="{{ route($routePrefix . '.edit', $protocol->id) }}" class="btn btn-warning btn-sm protocol-icon-btn" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <form action="{{ route($routePrefix . '.destroy', $protocol->id) }}" method="post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="return confirm('Are you sure you want to delete this protocol?');" class="btn btn-danger btn-sm protocol-icon-btn" type="submit" title="Delete">
                                                            <i class="fa fa-trash"></i>
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

<style>
    .protocol-page .protocol-panel {
        border-radius: 6px;
        overflow: hidden;
    }

    .protocol-page .c_title h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #222;
    }

    .protocol-add-btn {
        font-weight: 600;
        border-radius: 4px;
        padding: 8px 14px;
    }

    .protocol-add-btn i,
    .protocol-packet-btn i,
    .protocol-status i {
        margin-right: 5px;
    }

    .protocol-stats-row {
        margin-bottom: 18px;
    }

    .protocol-stat-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 82px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .protocol-stat-card h3 {
        margin: 0 0 4px 0;
        font-size: 26px;
        font-weight: 700;
        color: #333;
    }

    .protocol-stat-card p {
        margin: 0;
        font-size: 13px;
        color: #777;
        font-weight: 600;
    }

    .protocol-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .protocol-stat-blue { color: #3498db; background: rgba(52, 152, 219, 0.12); }
    .protocol-stat-green { color: #2ecc71; background: rgba(46, 204, 113, 0.12); }
    .protocol-stat-cyan { color: #00a9c7; background: rgba(0, 169, 199, 0.12); }

    .protocol-table-wrap {
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        background: #fff;
        padding: 0;
        overflow: hidden;
    }

    .protocol-table {
        margin-bottom: 0 !important;
        width: 100% !important;
        table-layout: auto;
    }

    .protocol-table thead th {
        background: #f8f9fb;
        color: #555;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        vertical-align: middle !important;
        padding: 12px 14px !important;
        border-bottom: 1px solid #ddd !important;
    }

    .protocol-table tbody td {
        vertical-align: middle !important;
        padding: 12px 14px !important;
    }

    .protocol-name-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .protocol-row-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #f0f7ff;
        color: #3498db;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
    }

    .protocol-name-cell strong {
        display: block;
        color: #333;
        font-size: 14px;
        line-height: 1.2;
    }

    .protocol-name-cell small {
        display: block;
        color: #888;
        font-size: 11px;
        margin-top: 3px;
    }

    .protocol-packet-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 4px;
        font-weight: 600;
        min-width: 132px;
            white-space: nowrap;
    }

    .protocol-count-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
        min-width: 20px;
        padding: 3px 6px;
    }

    .protocol-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        min-width: 78px;
        font-size: 12px;
    }

    .protocol-actions {
        /* display: flex; */
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .protocol-actions form {
        margin: 0;
        display: inline-flex;
    }

    .protocol-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        }

    .protocol-page .dataTables_wrapper,
    .protocol-page .dataTables_scroll,
    .protocol-page .dataTables_scrollBody {
        width: 100% !important;
        overflow: visible !important;
    }

    .protocol-page .dataTables_wrapper .dataTables_length,
    .protocol-page .dataTables_wrapper .dataTables_filter {
        margin: 0 0 12px 0;
    }

    .protocol-page .dataTables_wrapper .dataTables_filter input,
    .protocol-page .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ddd;
        border-radius: 4px;
        height: 34px;
        padding: 6px 10px;
        margin-left: 6px;
    }

    .protocol-page .dataTables_wrapper .dataTables_info {
        padding-top: 12px;
        color: #777;
    }

    .protocol-page .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px;
    }

    @media (max-width: 767px) {
        .protocol-page .text-right {
            text-align: left !important;
            margin-top: 10px;
        }

        .protocol-table-wrap {
            border-radius: 4px;
        }
    }
</style>

<script>
$(function () {
    #protocol_table.DataTable({
        pageLength: 10,
        autoWidth: false,
        scrollX: false,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [4] }
        ]
    });
});
</script>
@endsection


