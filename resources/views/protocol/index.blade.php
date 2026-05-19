@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('protocol-index') }}">
@endpush

@section('content')
@php
    $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
    $adminBase = Auth::user()->user_type == 'Support' ? url('support') : url('admin');
    $totalProtocols = $protocols->count();
    $activeProtocols = $protocols->where('is_active', true)->count();
    $packetCount = $protocols->sum('packet_types_count');
@endphp

<section id="main-content" class="protocol-index-page">
    <section class="wrapper">
        <div class="pi-breadcrumb-wrap">
            <nav class="pi-breadcrumb pi-breadcrumb--scroll" aria-label="Breadcrumb">
                <a href="{{ $adminBase }}" class="pi-bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
                <a href="{{ $adminBase }}" class="pi-bc-item">Home</a>
                <span class="pi-bc-sep">›</span>
                <span class="pi-bc-item">Testing Tools</span>
                <span class="pi-bc-sep">›</span>
                <span class="pi-bc-item pi-bc-active">Protocol Management</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel pi-panel">
                    <div class="c_title pi-panel-head">
                        <div class="row bgx-title-container pi-title-row">
                            <div class="col-xs-12 col-lg-6 pi-title-col">
                                <h2 class="pi-panel-title"><i class="fa fa-sitemap"></i> Protocol Management</h2>
                            </div>
                            <div class="col-xs-12 col-lg-6 text-right pi-actions-col">
                                <a href="{{ route($routePrefix . '.create') }}" class="btn pi-btn-add">
                                    <i class="fa fa-plus"></i> Add New Protocol
                                </a>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="c_content pi-panel-body">
                        @if(session('success'))
                            <div class="alert alert-success pi-alert-success" role="alert">
                                <i class="fa fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif

                        <div class="row pi-stats-row">
                            <div class="col-xs-12 col-sm-4">
                                <article class="pi-stat-card pi-stat-card--total">
                                    <div class="pi-stat-card__icon"><i class="fa fa-code-fork"></i></div>
                                    <div class="pi-stat-card__body">
                                        <span class="pi-stat-card__value">{{ $totalProtocols }}</span>
                                        <span class="pi-stat-card__label">Total Protocols</span>
                                    </div>
                                </article>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <article class="pi-stat-card pi-stat-card--active">
                                    <div class="pi-stat-card__icon"><i class="fa fa-check-circle"></i></div>
                                    <div class="pi-stat-card__body">
                                        <span class="pi-stat-card__value">{{ $activeProtocols }}</span>
                                        <span class="pi-stat-card__label">Active Protocols</span>
                                    </div>
                                </article>
                            </div>
                            <div class="col-xs-12 col-sm-4">
                                <article class="pi-stat-card pi-stat-card--packets">
                                    <div class="pi-stat-card__icon"><i class="fa fa-list-ul"></i></div>
                                    <div class="pi-stat-card__body">
                                        <span class="pi-stat-card__value">{{ $packetCount }}</span>
                                        <span class="pi-stat-card__label">Total Packet Types</span>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div class="pi-table-shell">
                            <table id="protocol_table" class="table table-striped cf pi-datatable-table no-global-table-ui" style="width:100%">
                                <colgroup>
                                    <col class="pi-col-sr" />
                                    <col class="pi-col-name" />
                                    <col class="pi-col-packets" />
                                    <col class="pi-col-status" />
                                    <col class="pi-col-actions" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="pi-th-sr"><span class="pi-th-label">Sr. No.</span></th>
                                        <th class="pi-th-no-sort"><span class="pi-th-label">Protocol Name</span></th>
                                        <th class="pi-th-no-sort text-center"><span class="pi-th-label">Packet Types</span></th>
                                        <th class="pi-th-no-sort text-center"><span class="pi-th-label">Status</span></th>
                                        <th class="pi-th-actions text-center"><span class="pi-th-label">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($protocols as $index => $protocol)
                                        <tr>
                                            <td class="pi-td-sr">{{ $index + 1 }}</td>
                                            <td class="pi-td-name">
                                                <div class="pi-protocol-cell">
                                                    <span class="pi-protocol-avatar" aria-hidden="true"><i class="fa fa-code-fork"></i></span>
                                                    <div class="pi-protocol-meta">
                                                        <span class="pi-protocol-name">{{ $protocol->name }}</span>
                                                        <span class="pi-protocol-date">Created {{ $protocol->created_at ? $protocol->created_at->format('M d, Y') : 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="pi-td-packets text-center">
                                                <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" class="btn pi-btn-packets">
                                                    <i class="fa fa-list-ul"></i>
                                                    <span>View Packets</span>
                                                    <em class="pi-packet-count">{{ $protocol->packet_types_count }}</em>
                                                </a>
                                            </td>
                                            <td class="pi-td-status text-center">
                                                @if($protocol->is_active)
                                                    <span class="pi-status pi-status--on"><i class="fa fa-check-circle"></i> Active</span>
                                                @else
                                                    <span class="pi-status pi-status--off"><i class="fa fa-times-circle"></i> Inactive</span>
                                                @endif
                                            </td>
                                            <td class="pi-td-actions text-center">
                                                <div class="pi-row-actions">
                                                    <a href="{{ route($routePrefix . '.edit', $protocol->id) }}" class="btn pi-btn-edit" title="Edit protocol">
                                                        <i class="fa fa-pencil"></i><span>Edit</span>
                                                    </a>
                                                    <form action="{{ route($routePrefix . '.destroy', $protocol->id) }}" method="POST" class="pi-delete-form swal-confirm" data-confirm-msg="Are you sure you want to delete this protocol?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button style="margin-top:1px;" type="submit" class="btn pi-btn-delete" title="Delete protocol">
                                                            <i class="fa fa-trash"></i><span>Delete</span>
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
@endsection

@section('scripts')
<script>
$(function () {
    var $tbl = $('#protocol_table');
    if (!$tbl.length || !$.fn.DataTable) {
        return;
    }
    if ($.fn.dataTable.isDataTable($tbl)) {
        $tbl.DataTable().destroy();
    }
    $tbl.DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: true, targets: 0 },
            { orderable: false, targets: [1, 2, 3, 4] }
        ],
        stripeClasses: [],
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>"
    });
    setTimeout(function () {
        $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search protocols…');
    }, 100);
});
</script>
@endsection
