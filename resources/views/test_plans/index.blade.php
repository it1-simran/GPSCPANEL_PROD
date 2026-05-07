@extends('layouts.apps')

@section('title', 'Manage Test Plans')

@section('content')
<style>
    .panel-heading {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        color: white !important;
        padding: 20px 25px !important;
        border-radius: 12px 12px 0 0 !important;
        border: none !important;
    }
    .panel {
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
    }
    .adv-table {
        padding: 20px;
    }
    .table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        padding: 15px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
    }
    /* DataTables sort icons */
    table.dataTable thead th.sorting,
    table.dataTable thead th.sorting_asc,
    table.dataTable thead th.sorting_desc {
        padding-right: 30px !important;
        position: relative;
    }
    table.dataTable thead th.sorting::after,
    table.dataTable thead th.sorting_asc::after,
    table.dataTable thead th.sorting_desc::after {
        font-family: 'FontAwesome';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        opacity: 0.4;
    }
    table.dataTable thead th.sorting::after        { content: '\f0dc'; opacity: 0.3; color: #475569; }
    table.dataTable thead th.sorting_asc::after   { content: '\f0de'; opacity: 1;   color: #76CF1C; }
    table.dataTable thead th.sorting_desc::after  { content: '\f0dd'; opacity: 1;   color: #76CF1C; }
    /* Hide default DataTables sort icons */
    table.dataTable thead .sorting:before,
    table.dataTable thead .sorting_asc:before,
    table.dataTable thead .sorting_desc:before,
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting_asc:after,
    table.dataTable thead .sorting_desc:after {
        display: none !important;
    }
    /* Re-enable our custom ::after */
    table.dataTable thead th.sorting::after,
    table.dataTable thead th.sorting_asc::after,
    table.dataTable thead th.sorting_desc::after {
        display: inline-block !important;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 8px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #76CF1C !important;
        border-color: #76CF1C !important;
        color: white !important;
        border-radius: 6px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #1d283e !important;
        border-color: #1d283e !important;
        color: white !important;
        border-radius: 6px;
    }
    .table tbody td {
        padding: 15px !important;
        vertical-align: middle !important;
        color: #1e293b;
        font-size: 13px;
    }
    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 11px;
    }
    .bg-info-soft {
        background: #e0f2fe;
        color: #0369a1;
    }
    .bg-success-soft {
        background: #dcfce7;
        color: #15803d;
    }
    .action-flex {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-action {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
        border: none;
        padding: 0;
        line-height: 1;
        text-decoration: none !important;
        font-size: 14px;
        box-sizing: border-box;
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .btn-edit { background: #1d283e !important; color: white !important; }
    .btn-run { background: #76CF1C !important; color: white !important; }
    .btn-delete { background: #ef4444 !important; color: white !important; }
    
    .create-btn {
        background: #76CF1C !important;
        border: none !important;
        padding: 8px 20px !important;
        border-radius: 8px;
        font-weight: 700;
        color: white !important;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    .create-btn:hover {
        background: #65b515;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px rgba(118, 207, 28, 0.3);
        color: white !important;
    }

    /* Breadcrumb — Dark Card Style */
    .breadcrumb-wrap {
        padding: 16px 0 12px 0;
        margin-bottom: 14px;
    }
    .bc-card {
        display: flex;
        align-items: center;
        gap: 0;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border: 1.5px solid #76CF1C;
        border-radius: 12px;
        padding: 10px 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15), 0 0 0 1px rgba(118,207,28,0.08);
        width: fit-content;
    }
    /* Home icon box */
    .bc-home-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        background: rgba(118, 207, 28, 0.15);
        border: 1px solid rgba(118, 207, 28, 0.35);
        border-radius: 8px;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .bc-home-box i {
        color: #76CF1C;
        font-size: 13px;
    }
    /* Trail items */
    .bc-trail {
        display: flex;
        align-items: center;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 4px;
    }
    .bc-trail li {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .bc-trail li a,
    .bc-trail li span.bc-label {
        font-size: 12.5px;
        font-weight: 600;
        letter-spacing: 0.3px;
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.2s;
        white-space: nowrap;
    }
    .bc-trail li a:hover {
        color: #e2e8f0;
    }
    .bc-trail li.bc-active span.bc-label {
        color: #76CF1C;
    }
    .bc-sep {
        color: #334155;
        font-size: 11px;
        margin: 0 2px;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <!-- Breadcrumb -->
                <div class="breadcrumb-wrap">
                    <div class="bc-card">
                        <!-- Home icon box -->
                        <a href="{{ url('/admin') }}" class="bc-home-box" title="Home">
                            <i class="fa fa-home"></i>
                        </a>
                        <!-- Trail -->
                        <ul class="bc-trail">
                            <li>
                                <i class="fa fa-chevron-right bc-sep"></i>
                                <a href="{{ url('/admin') }}">Home</a>
                            </li>
                            <li>
                                <i class="fa fa-chevron-right bc-sep"></i>
                                <span class="bc-label">Test Automation</span>
                            </li>
                            <li class="bc-active">
                                <i class="fa fa-chevron-right bc-sep"></i>
                                <span class="bc-label">Manage Test Plans</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <section class="panel">
                    <header class="panel-heading">
                        <div class="row">
                            <div class="col-md-6" style="display: flex; align-items: center;">
                                <i class="fa fa-list-alt" style="margin-right: 12px; font-size: 20px; color: #76CF1C;"></i>
                                <strong style="font-size: 16px; letter-spacing: 0.5px;">AUTOMATED TEST PLANS</strong>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.test-plans.create') }}" class="btn create-btn">
                                    <i class="fa fa-plus-circle" style="margin-right: 5px;"></i> CREATE NEW PLAN
                                </a>
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        @if(session('success'))
                            <div class="alert alert-success animate__animated animate__fadeInDown" style="border-radius: 10px; border: none; background: #dcfce7; color: #15803d; font-weight: 600;">
                                <i class="fa fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                            </div>
                        @endif

                        <div class="adv-table">
                            <table class="table table-hover" id="test-plans-table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 60px; text-align: center;">Sr. No.</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Steps</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th style="width: 150px;" class="no-sort">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($testPlans as $index => $plan)
                                        <tr>
                                            <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $index + 1 }}</td>
                                            <td style="font-weight: 700; color: #1e293b;">{{ $plan->name }}</td>
                                            <td class="text-muted">{{ $plan->description ?: 'No description provided' }}</td>
                                            <td>
                                                <span class="badge bg-info-soft">
                                                    {{ $plan->steps_count }} Steps
                                                </span>
                                            </td>
                                            <td style="color: #64748b;">{{ $plan->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <span class="badge bg-success-soft">
                                                    Active
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-flex">
                                                    <a href="{{ route('admin.test-plans.edit', $plan->id) }}" class="btn-action btn-edit" title="Edit Plan">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="{{ route('admin.test-validate.index', ['test_plan_id' => $plan->id]) }}" class="btn-action btn-run" title="Run Plan">
                                                        <i class="fa fa-play"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="btn-action btn-delete" onclick="if(confirm('Are you sure you want to delete this plan?')) document.getElementById('delete-form-{{ $plan->id }}').submit();" title="Delete Plan">
                                                        <i class="fa fa-trash-o"></i>
                                                    </a>
                                                    <form id="delete-form-{{ $plan->id }}" action="{{ route('admin.test-plans.destroy', $plan->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>
@push('scripts')
<script src="{{ asset('assets/vendors/datatable/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/vendors/datatable/bootstrap/dataTables.bootstrap.js') }}"></script>
<style>
    /* Premium pagination bar for this page */
    #test-plans-table_wrapper .dataTables_paginate {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    #test-plans-table_wrapper .dataTables_info {
        display: flex;
        align-items: center;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        min-width: 36px;
        height: 36px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 13px !important;
        font-weight: 700 !important;
        padding: 0 10px !important;
        border: 1.5px solid #e2e8f0 !important;
        background: #fff !important;
        color: #475569 !important;
        transition: all 0.2s ease !important;
        line-height: 1 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f0fce8 !important;
        border-color: #76CF1C !important;
        color: #76CF1C !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(118,207,28,0.15) !important;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.current,
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(135deg, #76CF1C 0%, #5cb815 100%) !important;
        border-color: #76CF1C !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(118,207,28,0.4) !important;
        transform: translateY(-1px);
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.disabled,
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #f8fafc !important;
        border-color: #f1f5f9 !important;
        color: #cbd5e1 !important;
        transform: none !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
    }
    /* Bottom bar */
    #test-plans-table_wrapper .row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 10px 6px;
        border-top: 1px solid #f1f5f9;
        margin-top: 8px;
    }
    #test-plans-table_info {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 14px !important;
        font-size: 12px !important;
        color: #64748b !important;
        font-weight: 600 !important;
    }
</style>
<script>
$(document).ready(function() {
    $('#test-plans-table').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search plans...",
            lengthMenu: "Show _MENU_ entries",
            info: "<i class='fa fa-table' style='color:#76CF1C;margin-right:5px;'></i> Showing _START_–_END_ of <strong>_TOTAL_</strong> plans",
            infoEmpty: "No plans found",
            zeroRecords: "No matching plans found",
            paginate: {
                previous: "<i class='fa fa-chevron-left'></i>",
                next:     "<i class='fa fa-chevron-right'></i>"
            }
        }
    });
});
</script>
@endpush
@endsection

