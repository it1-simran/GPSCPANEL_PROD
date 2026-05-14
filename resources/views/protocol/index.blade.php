@extends('layouts.apps')
@section('content')
<section id="main-content" class="protocol-page">
    <section class="wrapper">
        @php
            $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
        @endphp
        <div class="protocol-breadcrumb-wrap">
            <nav class="protocol-breadcrumb protocol-breadcrumb--scroll">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ url('admin') }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Protocols</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel protocol-panel">
                    <div class="c_title" style="margin-bottom: 10px;">
                        <div class="row bgx-title-container protocol-page-title-row">
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 protocol-page-title-col">
                                <h2>Protocol Management</h2>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-right protocol-page-actions-col">
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
                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $totalProtocols }}</h3>
                                        <p>Total Protocols</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-cyan"><i class="fa fa-code-fork"></i></span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $activeProtocols }}</h3>
                                        <p>Active Protocols</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-blue"><i class="fa fa-check-circle"></i></span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
                                <div class="protocol-stat-card">
                                    <div>
                                        <h3>{{ $packetCount }}</h3>
                                        <p>Total Packet Types</p>
                                    </div>
                                    <span class="protocol-stat-icon protocol-stat-green"><i class="fa fa-list-ul"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="protocol-table-wrap">
                            <table id="protocol_table" class="table table-bordered table-striped protocol-table">
                                <thead>
                                    <tr>
                                        <th class="text-center">Sr. No.</th>
                                        <th>Protocol Name</th>
                                        <th class="text-center">Packet Types</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($protocols as $index => $protocol)
                                        <tr class="p-0">
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
                                                <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" class="btn btn-primary btn-sm protocol-manage-btn">
                                                    <i class="fa fa-list-ul"></i> View Packets
                                                    <span class="badge protocol-field-badge">{{ $protocol->packet_types_count }}</span>
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
                                                    <a href="{{ route($routePrefix . '.edit', $protocol->id) }}" class="btn btn-warning btn-sm protocol-manage-btn">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </a>
                                                    <form action="{{ route($routePrefix . '.destroy', $protocol->id) }}" method="POST" style="display:inline-flex; margin:0; padding:0;" onsubmit="return confirm('Are you sure you want to delete this protocol?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger protocol-delete-btn" style="margin-top: -2px;">
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
    .protocol-page {
        font-family: 'Inter', sans-serif;
    }

    #main-content.protocol-page .wrapper {
        padding-top: 0 !important;
    }

    .protocol-breadcrumb-wrap {
        margin-top: 0 !important;
        padding: 0 0 14px 0 !important;
    }

    .protocol-breadcrumb {
        display: inline-flex;
        align-items: center;
        background: #1e293b;
        border-radius: 50px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }

    .protocol-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        background: #76CF1C;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .protocol-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .protocol-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.65);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }

    .protocol-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .protocol-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .protocol-breadcrumb--scroll {
        max-width: 100%;
        box-sizing: border-box;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .protocol-breadcrumb--scroll .bc-item {
        flex-shrink: 0;
    }

    @media (max-width: 767px) {
        #main-content.protocol-page > section.wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }
        .protocol-breadcrumb-wrap {
            padding: 0 0 10px 0 !important;
        }
        .protocol-breadcrumb.protocol-breadcrumb--scroll {
            display: flex;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 6px 12px 6px 6px;
            border-radius: 12px;
            justify-content: flex-start;
        }
        .protocol-page-title-row > [class*="col-"] {
            text-align: left !important;
            margin-bottom: 0 !important;
        }
        .protocol-page-title-row .protocol-page-actions-col {
            text-align: left !important;
        }
    }
</style>

@include('protocol.partials.protocol_styles')
<style>
    /* Ensure packet count number is always visible */
    #protocol_table td .protocol-field-badge {
        color: #ffffff !important;
        font-weight: 700 !important;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    }

    /* Improve serial number visibility */
    #protocol_table tbody td:first-child {
        color: #334155 !important;
        font-weight: 700 !important;
    }
</style>

<script>
    $(function () {
        var table = $('#protocol_table').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });

    });
</script>
@endsection


