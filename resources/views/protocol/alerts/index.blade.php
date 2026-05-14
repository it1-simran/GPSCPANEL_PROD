@extends('layouts.apps')
@section('content')
<section id="main-content" class="protocol-page protocol-alerts-page">
    <section class="wrapper">
        @php
            $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
        @endphp
        <div class="protocol-breadcrumb-wrap">
            <nav class="protocol-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ route($routePrefix . '.index') }}" class="bc-item">Protocol Management</a>
                <span class="bc-sep">›</span>
                <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" class="bc-item">Packet Types</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Alerts</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel protocol-panel">
                    <div class="c_title alerts-page-heading">
                        <div class="row bgx-title-container">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <h2 class="alert-title">
                                    <i class="fa fa-list"></i>
                                    Alerts
                                    <span class="alert-title-badge">{{ strtoupper($packetType->name) }}</span>
                                </h2>
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
                                                        {{ optional($cond->field)->name ?? 'Unknown Field' }} {{ $cond->operator }} {{ $cond->value }}
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
<style>
    .protocol-alerts-page .wrapper {
        padding-top: 8px !important;
    }

    .protocol-alerts-page .protocol-breadcrumb-wrap {
        padding: 6px 0 14px 0;
    }

    .protocol-alerts-page .protocol-breadcrumb {
        display: inline-flex;
        align-items: center;
        background: #1e293b;
        border-radius: 50px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }

    .protocol-alerts-page .protocol-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #76CF1C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .protocol-alerts-page .protocol-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .protocol-alerts-page .protocol-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }

    .protocol-alerts-page .protocol-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .protocol-alerts-page .protocol-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .protocol-alerts-page .c_title h2::before {
        content: none !important;
        display: none !important;
    }

    .protocol-alerts-page .c_panel .c_title.alerts-page-heading {
        padding: 11px 20px !important;
        margin-bottom: 0 !important;
    }

    .protocol-alerts-page .alert-title {
        display: inline-flex !important;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #ffffff !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.35px;
        line-height: 1.25;
    }

    .protocol-alerts-page .alert-title > i {
        color: #76CF1C;
        font-size: 13px;
        width: 16px;
        text-align: center;
    }

    .protocol-alerts-page .alert-title-badge {
        display: inline-flex;
        align-items: center;
        margin-left: 2px;
        padding: 3px 9px;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.16);
        border: 1px solid rgba(129, 140, 248, 0.45);
        color: #8ea2ff !important;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.55px;
    }

    .protocol-alerts-page .protocol-add-btn {
        padding: 6px 14px !important;
        font-size: 12px !important;
    }
</style>

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
