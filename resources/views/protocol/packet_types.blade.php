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
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @php
                            $totalPacketTypes = $packetTypes->count();
                            $totalFields = $packetTypes->sum('fields_count');
                        @endphp

                        <div class="row protocol-stats-row">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $totalPacketTypes }}</h3>
                                        <p>Packet Types</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-cyan"><i class="fa fa-list-ul"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $totalFields }}</h3>
                                        <p>Total Parameters</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-blue"><i class="fa fa-cogs"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
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
                                        <th style="width:190px;" class="text-center">Header Identifier</th>
                                        <th style="width:130px;" class="text-center">Fields Count</th>
                                        <th style="width:190px;" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($packetTypes as $type)
                                        <tr>
                                            <td>
                                                <div class="protocol-name-cell">
                                                    <span class="protocol-row-icon"><i class="fa fa-cube"></i></span>
                                                    <span>
                                                        <strong>{{ $type->name }}</strong>
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
                                                    <a href="{{ route($routePrefix . '.fields', $type->id) }}" class="btn btn-primary btn-sm protocol-manage-btn">
                                                        <i class="fa fa-cogs"></i> Manage Parameters
                                                    </a>
                                                    <form action="{{ route($routePrefix . '.packet-types.destroy', $type->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this packet type? This will also delete all its associated parameters.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm protocol-delete-btn">
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

    .protocol-page .c_title h2 span {
        color: #3498db;
    }

    .protocol-add-btn {
        font-weight: 600;
        border-radius: 4px;
        padding: 8px 14px;
    }

    .protocol-add-btn i,
    .protocol-manage-btn i {
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
        font-size: 24px;
        font-weight: 700;
        color: #333;
        line-height: 1.1;
        max-width: 175px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
        flex: 0 0 42px;
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

    .protocol-code-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 70px;
                max-width: 150px;
padding: 6px 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background: #f8f9fb;
        color: #333;
        font-family: Consolas, 'Courier New', monospace;
        font-weight: 600;
            overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .protocol-field-badge {
        background: #3498db;
        padding: 6px 10px;
        font-size: 12px;
    }

    .protocol-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .protocol-manage-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
                min-width: 150px;
        white-space: nowrap;
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
    }
    .protocol-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        padding: 5px 12px;
        white-space: nowrap;
    }
</style>

<script>
$(function () {
    #packet_table.DataTable({
        pageLength: 10,
        autoWidth: false,
        scrollX: false,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [3] }
        ]
    });
});
</script>
@endsection



