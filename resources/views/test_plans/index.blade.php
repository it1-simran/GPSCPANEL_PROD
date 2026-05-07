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
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        <div class="row">
                            <div class="col-md-6" style="display: flex; align-items: center;">
                                <i class="fa fa-list-alt" style="margin-right: 12px; font-size: 20px; color: #76CF1C;"></i>
                                <strong style="font-size: 16px; letter-spacing: 0.5px;">AUTOMATED TEST PLANS</strong>
                            </div>
                            <div class="col-md-6 text-right">
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

                        <div class="adv-table">
                            <table class="table table-hover" id="test-plans-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px; text-align: center;">Sr. No.</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Steps</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th style="width: 150px;">Actions</th>
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
@endsection
