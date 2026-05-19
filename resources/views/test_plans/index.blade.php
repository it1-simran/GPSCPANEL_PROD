@extends('layouts.apps')


@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('test-plans-index') }}">
@endpush
@section('title', 'Manage Test Plans')

@section('content')


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
                            <table class="table table-hover tp-datatable-table no-global-table-ui" id="test-plans-table" style="width:100%">
                                <colgroup>
                                    <col class="tp-col-sr" />
                                    <col class="tp-col-name" />
                                    <col class="tp-col-desc" />
                                    <col class="tp-col-steps" />
                                    <col class="tp-col-date" />
                                    <col class="tp-col-status" />
                                    <col class="tp-col-actions" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="tp-th-sr">Sr. No.</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Steps</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th class="tp-th-actions no-sort">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($testPlans as $index => $plan)
                                        <tr>
                                            <td class="tp-td-sr">{{ $index + 1 }}</td>
                                            <td class="tp-td-name">{{ $plan->name }}</td>
                                            <td class="tp-td-desc">{{ $plan->description ?: 'No description provided' }}</td>
                                            <td class="tp-td-steps">
                                                <span class="badge bg-info-soft">{{ $plan->steps_count }} Steps</span>
                                            </td>
                                            <td class="tp-td-date">{{ $plan->created_at->format('Y-m-d H:i') }}</td>
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
                                                    <a href="javascript:void(0)" class="btn-action btn-delete swal-confirm" data-confirm-msg="Are you sure you want to delete this plan?" data-form-id="delete-form-{{ $plan->id }}" title="Delete Plan">
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

        <a href="javascript:void(0)" class="btn-action btn-delete swal-confirm" data-confirm-msg="Are you sure you want to delete this plan?" data-form-id="delete-form-{{ $plan->id }}" title="Delete Plan">
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

