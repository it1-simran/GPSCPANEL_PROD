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
        background: #fff;
        border-radius: 12px;
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
        border-top: 1px solid #eef2f7 !important;
        border-bottom: 1px solid #eef2f7 !important;
    }
    .table tbody tr:hover td {
        background: #f8fbff !important;
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

    /* —— Responsive (test plans page) —— */
    .test-plans-page .bc-card {
        max-width: 100%;
        box-sizing: border-box;
        flex-wrap: nowrap;
        align-items: center;
        gap: 8px;
    }
    /* One row: home + trail; trail scrolls horizontally on narrow screens */
    .test-plans-page .bc-trail {
        display: flex;
        flex-wrap: nowrap;
        flex: 1 1 auto;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        gap: 2px;
        align-items: center;
        padding: 2px 0;
    }
    .test-plans-page .bc-trail::-webkit-scrollbar {
        height: 4px;
    }
    .test-plans-page .bc-trail::-webkit-scrollbar-thumb {
        background: rgba(118, 207, 28, 0.35);
        border-radius: 4px;
    }
    .test-plans-page .bc-trail li {
        flex-shrink: 0;
    }
    .test-plans-page .bc-home-box {
        margin-right: 0;
    }

    .test-plans-page .test-plans-panel-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        min-width: 0;
    }
    .test-plans-page .test-plans-panel-title strong {
        font-size: 14px;
        font-size: clamp(12px, 3.5vw, 16px);
        line-height: 1.3;
        word-break: break-word;
    }
    .test-plans-page .test-plans-panel-actions {
        min-width: 0;
    }
    .test-plans-page .test-plans-panel-actions .create-btn {
        max-width: 100%;
        box-sizing: border-box;
    }

    .test-plans-page .test-plans-adv-table {
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .test-plans-page .test-plans-adv-table #test-plans-table_wrapper {
        overflow-x: auto !important;
        max-width: 100%;
    }
    .test-plans-page .test-plans-adv-table table#test-plans-table {
        min-width: 720px;
        width: auto !important;
    }

    @media (max-width: 767px) {
        .test-plans-page .breadcrumb-wrap {
            padding: 8px 0 10px;
            margin-bottom: 10px;
        }
        .test-plans-page .bc-card {
            width: 100%;
            padding: 8px 10px;
            border-radius: 10px;
        }
        .test-plans-page .bc-home-box {
            width: 28px;
            height: 28px;
            margin-right: 0;
            border-radius: 7px;
        }
        .test-plans-page .bc-home-box i {
            font-size: 12px;
        }
        .test-plans-page .bc-trail li a,
        .test-plans-page .bc-trail li span.bc-label {
            font-size: 11px;
            letter-spacing: 0.2px;
        }
        .test-plans-page .bc-sep {
            font-size: 9px;
            margin: 0 1px;
        }
        .test-plans-page .panel-heading .row {
            margin-left: 0;
            margin-right: 0;
        }
        .test-plans-page .test-plans-panel-actions {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(148, 163, 184, 0.25);
            text-align: left !important;
        }
        .test-plans-page .test-plans-panel-actions .create-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 10px 14px !important;
            font-size: 12px !important;
            white-space: normal;
            text-align: center;
        }
        .test-plans-page .adv-table {
            padding: 12px 10px;
        }
        .test-plans-page #test-plans-table_wrapper .dataTables_length,
        .test-plans-page #test-plans-table_wrapper .dataTables_filter {
            float: none !important;
            width: 100%;
            text-align: left !important;
            margin-bottom: 10px !important;
        }
        .test-plans-page #test-plans-table_wrapper .dataTables_filter label {
            display: block;
            width: 100%;
        }
        .test-plans-page #test-plans-table_wrapper .dataTables_filter input {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
            margin-left: 0 !important;
        }
        .test-plans-page #test-plans-table_wrapper .dataTables_length select {
            max-width: 100%;
        }
    }
</style>

<section id="main-content" class="test-plans-page">
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
                            <div class="col-xs-12 col-sm-6 test-plans-panel-title">
                                <i class="fa fa-list-alt" style="font-size: 20px; color: #76CF1C; flex-shrink: 0;"></i>
                                <strong style="letter-spacing: 0.5px;">AUTOMATED TEST PLANS</strong>
                            </div>
                            <div class="col-xs-12 col-sm-6 text-right test-plans-panel-actions">
                                @php
                                    $userType = auth()->check() ? strtolower(trim((string) auth()->user()->user_type)) : '';
                                    $routePrefix = $userType === 'support' ? 'support' : 'admin';
                                    $createRoute = $routePrefix . '.test-plans.create';
                                @endphp
                                <a href="{{ route($createRoute) }}" class="btn create-btn">
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

                        <div class="adv-table test-plans-adv-table">
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
                                            <!-- <td>
                                                <div class="action-flex">
                                                    @php
                                                        $routePrefix = auth()->user() && auth()->user()->user_type === 'support' ? 'support' : 'admin';
                                                    @endphp
                                                    <a href="{{ route($routePrefix . '.test-plans.edit', $plan->id) }}" class="btn-action btn-edit" title="Edit Plan">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="{{ route($routePrefix . '.test-validate.index', ['test_plan_id' => $plan->id]) }}" class="btn-action btn-run" title="Run Plan">
                                                        <i class="fa fa-play"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" class="btn-action btn-delete" onclick="if(confirm('Are you sure you want to delete this plan?')) document.getElementById('delete-form-{{ $plan->id }}').submit();" title="Delete Plan">
                                                        <i class="fa fa-trash-o"></i>
                                                    </a>
                                                    <form id="delete-form-{{ $plan->id }}" action="{{ route($routePrefix . '.test-plans.destroy', $plan->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td> -->

                                            <td>
    <div class="action-flex">
        @php
            $userType = auth()->check() ? strtolower(trim(auth()->user()->user_type)) : '';

            $routePrefix = $userType === 'support'
                ? 'support'
                : 'admin';
        @endphp

        <a href="{{ route($routePrefix . '.test-plans.edit', $plan->id) }}" class="btn-action btn-edit" title="Edit Plan">
            <i class="fa fa-pencil"></i>
        </a>

        <a href="{{ route($routePrefix . '.test-validate.index', ['test_plan_id' => $plan->id]) }}" class="btn-action btn-run" title="Run Plan">
            <i class="fa fa-play"></i>
        </a>

        <a href="javascript:void(0)" class="btn-action btn-delete" onclick="if(confirm('Are you sure you want to delete this plan?')) document.getElementById('delete-form-{{ $plan->id }}').submit();" title="Delete Plan">
            <i class="fa fa-trash-o"></i>
        </a>

        <form id="delete-form-{{ $plan->id }}" action="{{ route($routePrefix . '.test-plans.destroy', $plan->id) }}" method="POST" style="display: none;">
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
    /* Datatable footer - same style as view-jig */
    #test-plans-table_wrapper .row:last-child {
        display: flex !important; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 8px; margin-top: 12px; padding: 8px 2px;
    }
    #test-plans-table_wrapper .row:last-child > div {
        float: none !important; width: auto !important; padding: 0 !important;
        background: transparent !important; border: none !important; box-shadow: none !important;
        flex: 0 0 auto;
    }
    #test-plans-table_wrapper .row:last-child > div:first-child { flex: 1 1 auto !important; }
    #test-plans-table_wrapper .row:last-child > div:last-child  { flex: 0 0 auto !important; margin-left: auto; }

    #test-plans-table_wrapper .dataTables_info {
        display: flex !important; align-items: center; gap: 6px;
        color: #64748b; font-size: 13px; font-weight: 500;
        padding: 6px 0 !important; float: none !important;
    }
    #test-plans-table_wrapper .dataTables_info::before {
        content:'\f0cb'; font-family:FontAwesome; color:#76CF1C; font-size:14px;
    }

    #test-plans-table_wrapper .dataTables_paginate {
        display: flex !important; align-items: center; gap: 4px;
        float: none !important; flex-wrap: wrap;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button {
        background: transparent !important; border: none !important;
        color: #64748b !important; border-radius: 6px !important; padding: 5px 11px !important;
        font-size: 13px !important; font-weight: 600 !important; cursor: pointer;
        transition: all 0.2s; box-shadow: none !important;
        min-width: 32px; text-align: center; line-height: 1.5; display: inline-block;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button:hover {
        background:#f1f5f9 !important; color:#76CF1C !important;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.current,
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #76CF1C !important; border: none !important;
        color: #1e293b !important; font-weight: 800 !important; border-radius: 6px !important;
    }
    #test-plans-table_wrapper .dataTables_paginate .paginate_button.disabled {
        color:#cbd5e1 !important; cursor:not-allowed !important;
    }
    #test-plans-table_wrapper .dataTables_paginate span > span { display: none !important; }

    #test-plans-table_wrapper .dataTables_length,
    #test-plans-table_wrapper .dataTables_filter {
        margin-bottom: 10px;
    }
    @media (min-width: 768px) {
        #test-plans-table_wrapper .dataTables_filter input {
            min-width: 190px;
        }
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
            infoEmpty: "No entries to show",
            zeroRecords: "No matching plans found"
        }
    });
});
</script>
@endpush
@endsection

