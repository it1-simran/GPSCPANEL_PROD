<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <!--======== Page Title and Breadcrumbs Start ========-->
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Account Management</a></li>
                        <li class="active"><a href="#">View User Approval Request</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <!--======== Page Title and Breadcrumbs End ========-->
        <!--======== Dynamic Datatable Content Start End ========-->
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <div class="row bgx-title-container">
                            <div class="col-lg-6">
                                <h2>View User Approval Request</h2>
                            </div>
                            <div class="col-lg-6 text-right">
                                <!-- Button to trigger modal -->
                                <button type="button" class="btn btn-primary" onclick="requestModel()">
                                    Send Account Request
                                </button>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <!-- Modal -->
                        <div class="modal" id="accountRequestModal" tabindex="-1" aria-labelledby="accountRequestModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route($url_type . '.request.send') }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="accountRequestModalLabel">Send Account Request</h5>
                                        </div>

                                        <div class="modal-body">
                                            <div class="margin-bottom-20">
                                                <label for="name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                            <div class="margin-bottom-20">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                            <div class="margin-bottom-20">
                                                <label for="user_type" class="form-label">Account Type</label>
                                                <select class="form-control" id="user_type" name="user_type" required>
                                                    <option value="">Select User Type</option>
                                                    <option value="Manufacturer">Manufacturer</option>
                                                    <option value="Dealer">Dealer</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" onclick="closeRequestModal()">Close</button>
                                            <button type="submit" class="btn btn-primary">Send Request</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div><!--/.c_title-->
                    <div class="c_content">
                        <div class="row" id="alert_msg">
                            @if ($message = Session::get('success'))
                            <div class="col-sm-12 alert alert-success" role="alert">
                                {{ $message }}
                            </div>
                            @endif
                            @if ($message = Session::get('error'))
                            <div class="col-sm-12 alert alert-danger" role="alert">
                                {{ $message }}
                            </div>
                            @endif
                            @if ($errors->any())
                            <div class="col-sm-12 alert alert-danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                            @endif
                        </div>
                        @if($url_type == 'admin')
                        <div class="container-fluid mt-4">
                            <h4 class="mb-4">Approval Requests</h4>

                            {{-- ✅ Custom Tabs --}}
                            <div class="custom-tabs mb-3">
                                <button class="tab-btn active" data-status="all">
                                    All (<span id="count-all">0</span>)
                                </button>

                                <button class="tab-btn" data-status="Approved">
                                    Approved (<span id="count-Approved">0</span>)
                                </button>

                                <button class="tab-btn" data-status="pending-group">
                                    Pending (<span id="count-pendingGroup">0</span>)
                                </button>

                                <button class="tab-btn" data-status="rejected-group">
                                    Rejected (<span id="count-rejectedGroup">0</span>)
                                </button>
<!-- 
                                <button class="tab-btn" data-status="RequestMailSent">
                                    Request Mail Sent (<span id="count-RequestMailSent">0</span>)
                                </button> -->
                            </div>


                            {{-- ✅ Table --}}
                            <div class="table-responsive margin-top-25">
                                <table id="approvalRequests" class="table table-bordered table-striped table-hover align-middle" style="font-size: 14px;">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>User Type</th>
                                            <th>Device Category</th>
                                            <th>Device IP</th>
                                            <th>Device Port</th>
                                            <th>Resend Count</th>
                                            <th>Status</th>
                                            <th>Requested At</th>
                                            <th>View</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pendingRequests as $i => $request)
                                        <tr data-status="{{ $request->status }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $request->name }}</td>
                                            <td>{{ $request->email }}</td>
                                            <td>{{ $request->phone }}</td>
                                            <td>{{ ucfirst($request->userType) }}</td>
                                            <td>{{ $request->deviceCategory }}</td>
                                            @php
                                                $configurations = json_decode($request->configurations, true);
                                                $deviceIp = $configurations['ip_test']['value'] ?? 'N/A';
                                                $devicePort = $configurations['port']['value'] ?? 'N/A';
                                            @endphp
                                            <td>{{ $deviceIp }}</td>
                                            <td>{{ $devicePort }}</td>
                                            <td>{{ $request->resend_count }}</td>
                                            <td>
                                                <span class="badge 
                                                @if($request->status === 'approved') bg-success 
                                                @elseif($request->status === 'supportApproved') bg-info 
                                                @elseif($request->status === 'pendingApproval') bg-warning text-dark
                                                @elseif($request->status === 'rejected') bg-danger 
                                                @else bg-secondary @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-primary btn-sm btn-view-details" data-request="{{ json_encode($request) }}" onclick="viewDetails(this)" title="View Details">
                                                    <i class="fa fa-eye text-white"></i>
                                                </button>
                                            </td>
                                            <td>
                                                {{-- Action Buttons --}}
                                                @if(in_array($request->status, ['AdminApprovalPending']))
                                                @if($url_type == 'admin')
                                                <button class="btn btn-success btn-sm"
                                                    onclick="showApprovalModal({{ $request->id }}, 'Approved')">
                                                    Approve
                                                </button>
                                                @else
                                                <button class="btn btn-success btn-sm"
                                                    onclick="showApprovalModal({{ $request->id }}, 'Approved')">
                                                    Approve
                                                </button>
                                                @endif

                                                <button class="btn btn-danger btn-sm"
                                                    onclick="showRejectModal({{ $request->id }})">
                                                    Reject
                                                </button>
                                                @elseif(in_array($request->status, ['RejectedByAdmin', 'RejectedBySupport', 'RequestMailSent']))
                                                <button type="button" class="btn btn-sm btn-info"
                                                    onclick="openResendModal('{{ $request->name }}', '{{ $request->email }}', '{{ $request->userType }}')">
                                                    Resend Request
                                                </button>
                                                @endif
                                                <div class="custom-modal modal" id="approvalModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content custom-modal-content">
                                                            <div class="modal-header custom-modal-header">
                                                                <h5 class="modal-title">Confirm Action</h5>
                                                                <button type="button" class="bg-calendar-content btn-close margin-top-1" data-bs-dismiss="modal"
                                                                    onclick="cancelAcceptModel({{ $request->id }})" style="border:none;">x</button>
                                                            </div>
                                                            <div class="modal-body custom-modal-body text-center">
                                                                <p class="font-size-14">
                                                                    Are you sure you want to <span id="actionText{{ $request->id }}"></span> this user?
                                                                </p>
                                                            </div>
                                                            <div class="modal-footer custom-modal-footer">
                                                                <form id="approvalForm{{ $request->id }}" method="POST"
                                                                    action="{{ route('approval.update', $request->id) }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="action" id="actionInput{{ $request->id }}">
                                                                    <button type="button" class="btn custom-btn-secondary"
                                                                        onclick="cancelAcceptModel({{ $request->id }})"
                                                                        data-bs-dismiss="modal">
                                                                        Cancel
                                                                    </button>
                                                                    <button type="submit" class="btn custom-btn-primary">
                                                                        Confirm
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="custom-modal modal" id="rejectModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content custom-modal-content">
                                                            <div class="modal-header custom-modal-header d-flex justify-content-between">
                                                                <h5 class="modal-title">Reject Request</h5>
                                                            </div>
                                                            <div class="modal-body custom-modal-body">
                                                                <p class="font-bold font-size-14">Please provide a reason for rejecting this request:</p>
                                                                <textarea class="form-control custom-textarea w-100" id="rejectReason{{ $request->id }}" name="reason"
                                                                    placeholder="Enter rejection reason" required style="width: 100%;height: 100px;"></textarea>
                                                            </div>
                                                            <div class="modal-footer custom-modal-footer">
                                                                <form id="rejectForm{{ $request->id }}" method="POST"
                                                                    action="{{ route('approval.update', $request->id) }}">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="action" value="reject">
                                                                    <input type="hidden" name="reason" id="rejectReasonInput{{ $request->id }}">
                                                                    <button type="button" class="btn custom-btn-secondary"
                                                                        data-bs-dismiss="modal"
                                                                        onclick="cancelRejectModel({{ $request->id }})">Cancel</button>
                                                                    <button type="submit" class="btn bg-danger">Confirm Reject</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @else

                        <div class="table-responsive">
                            <table id="approvalRequests" class="table table-bordered table-striped table-hover align-middle" style="font-size: 14px;">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>User Type</th>
                                        <th>Device Category</th>
                                        <th>Device IP</th>
                                        <th>Device Port</th>
                                        <th>Resend Count</th>
                                        <th>Status</th>
                                        <th>Requested At</th>
                                        <th>View</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($pendingRequests as $request)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $request->name }}</td>
                                        <td>{{ $request->email }}</td>
                                        <td>{{ $request->phone }}</td>
                                        <td>{{ ucfirst($request->userType) }}</td>
                                        <td>{{ $request->deviceCategory }}</td>
                                        @php
                                            $configurations = json_decode($request->configurations, true);
                                            $deviceIp = $configurations['ip_test']['value'] ?? 'N/A';
                                            $devicePort = $configurations['port']['value'] ?? 'N/A';
                                        @endphp
                                        <td>{{ $deviceIp }}</td>
                                        <td>{{ $devicePort }}</td>
                                        <td>{{ $request->resend_count }}</td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ ucfirst($request->status) }}</span>
                                        </td>
                                        <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-primary btn-sm btn-view-details" data-request="{{ json_encode($request) }}" onclick="viewDetails(this)" title="View Details">
                                                <i class="fa fa-eye text-white"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <!-- Approve Button -->
                                            @if($request->status == 'SupportApprovalPending')
                                            <button class="btn btn-success btn-sm"
                                                onclick="showApprovalModal({{ $request->id }}, 'AdminApprovalPending')">
                                                Approve
                                            </button>

                                            <!-- Reject Button -->
                                            <button class="btn btn-danger btn-sm"
                                                onclick="showRejectModal({{ $request->id }})">
                                                Reject
                                            </button>
                                            @elseif(in_array($request->status, ['RequestMailSent']))
                                            <button type="button" class="btn btn-sm btn-info"
                                                onclick="openResendModal('{{ $request->name }}', '{{ $request->email }}', '{{ $request->userType }}')">
                                                Resend Request
                                            </button>
                                            @endif
                                            <!-- Modal -->
                                            <div class="custom-modal modal" id="approvalModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content custom-modal-content">
                                                        <div class="modal-header custom-modal-header">
                                                            <h5 class="modal-title">Confirm Action</h5>
                                                            <!-- <button type="button" class="bg-calendar-content btn-close margin-top-1" data-bs-dismiss="modal"
                                                                onclick="cancelAcceptModel({{ $request->id }})" style="border:none;">x</button> -->
                                                        </div>
                                                        <div class="modal-body custom-modal-body text-center">
                                                            <p class="font-size-14">
                                                                Are you sure you want to <span id="actionText{{ $request->id }}"></span> this user?
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer custom-modal-footer">
                                                            <form id="approvalForm{{ $request->id }}" method="POST"
                                                                action="{{ route('approval.update', $request->id) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="action" id="actionInput{{ $request->id }}">
                                                                <button type="button" class="btn custom-btn-secondary"
                                                                    onclick="cancelAcceptModel({{ $request->id }})"
                                                                    data-bs-dismiss="modal">
                                                                    Cancel
                                                                </button>
                                                                <button type="submit" class="btn custom-btn-primary">
                                                                    Confirm
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Reject Modal -->
                                            <div class="custom-modal modal" id="rejectModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content custom-modal-content">
                                                        <div class="modal-header custom-modal-header   d-flex justify-content-between">
                                                            <h5 class="modal-title">Reject Request</h5>
                                                            <!-- <button type="button" class="bg-calendar-content btn-close margin-top-1" data-bs-dismiss="modal" aria-label="Close"
                                                                onclick="cancelRejectModel({{ $request->id }})" style="border:none;">X</button> -->
                                                        </div>
                                                        <div class="modal-body custom-modal-body">
                                                            <p class="font-bold font-size-14">Please provide a reason for rejecting this request:</p>
                                                            <textarea class="form-control custom-textarea w-100" id="rejectReason{{ $request->id }}" name="reason"
                                                                placeholder="Enter rejection reason" required style="width: 100%;height: 100;"></textarea>
                                                        </div>
                                                        <div class="modal-footer custom-modal-footer">
                                                            <form id="rejectForm{{ $request->id }}" method="POST"
                                                                action="{{ route('approval.update', $request->id) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="action" value="reject">
                                                                <input type="hidden" name="reason" id="rejectReasonInput{{ $request->id }}">
                                                                <button type="button" class="btn custom-btn-secondary"
                                                                    data-bs-dismiss="modal"
                                                                    onclick="cancelRejectModel({{ $request->id }})">Cancel</button>
                                                                <button type="submit" class="btn bg-danger">Confirm Reject</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $i++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        <!--======= Dynamic Datatable Content Start End ========-->
    </section>
</section>

<div class="modal" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content info-modal-content">
            <div class="modal-header portal-modal-header d-flex justify-content-between bg-primary">
                <h5 class="modal-title" id="viewDetailsModalLabel">
                    <i class="fa fa-info-circle"></i> USER REQUEST DETAILS
                </h5>
                <div>
                <button type="button" class="portal-close-btn" data-bs-dismiss="modal" onclick="closeViewDetailsModal()" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
</div>
            </div>
            <div class="modal-body info-modal-body">
                {{-- Quick Summary Header --}}
                <div class="user-summary-card mb-4">
                    <div class="summary-avatar">
                        <i class="fa fa-user-circle"></i>
                    </div>
                    <div class="summary-details">
                        <h4 id="view_name_summary"></h4>
                        <p id="view_email_summary"></p>
                    </div>
                    <div id="view_status_badge_container"></div>
                </div>

                {{-- Detail Cards Grid --}}
                <div class="details-cards-grid">
                    <div class="detail-card">
                        <div class="card-icon"><i class="fa fa-phone"></i></div>
                        <div class="card-content">
                            <span class="card-label">Phone Number</span>
                            <span id="view_phone" class="card-value"></span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="card-icon"><i class="fa fa-briefcase"></i></div>
                        <div class="card-content">
                            <span class="card-label">User Type</span>
                            <span id="view_userType" class="card-value"></span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="card-icon"><i class="fa fa-tags"></i></div>
                        <div class="card-content">
                            <span class="card-label">Category</span>
                            <span id="view_deviceCategory" class="card-value"></span>
                        </div>
                    </div>
                    <div class="detail-card">
                        <div class="card-icon"><i class="fa fa-calendar"></i></div>
                        <div class="card-content">
                            <span class="card-label">Request Date</span>
                            <span id="view_date" class="card-value"></span>
                        </div>
                    </div>
                </div>

                {{-- Rejection Reason - Hidden by default --}}
                <div id="rejection_reason_container" style="display:none;" class="mt-3">
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-start">
                        <i class="fa fa-exclamation-triangle mt-1 me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Rejection Reason</h6>
                            <p id="view_rejection_reason" class="mb-0 small"></p>
                        </div>
                    </div>
                </div>

                <div class="config-container-premium mt-4">
                    <div class="config-header">
                        <h5><i class="fa fa-cogs"></i> Device Configuration</h5>
                        <span class="config-count" id="config_count">0 Settings</span>
                    </div>
                    <div class="config-table-wrapper">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Parameter Field</th>
                                    <th>Configured Value</th>
                                </tr>
                            </thead>
                            <tbody id="config_body">
                                {{-- Loaded via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-right w-100">
                <button type="button" class="btn btn-portal-close" onclick="closeViewDetailsModal()">
                    CLOSE
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    /* Portal Integrated Theme Styles */
    :root {
        --portal-blue: #004a99;
        --portal-dark: #1a2732;
        --portal-gray: #f4f7f6;
        --border-light: #e0e4e8;
    }

    .info-modal-content {
        border-radius: 8px; /* Sharper, cleaner corners */
        border: 1px solid var(--border-light);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        background: #ffffff;
    }

    .portal-modal-header {
        /* background: var(--portal-blue); */
        padding: 12px 20px;
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 8px 8px 0 0;
    }

    .portal-modal-header .modal-title {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: white !important;
        margin: 0;
    }

    .portal-close-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 16px;
        opacity: 0.8;
        transition: 0.2s;
        padding: 5px;
    }

    .portal-close-btn:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    .info-modal-body {
        padding: 20px;
        background: white;
        max-height: 75vh;
        overflow-y: auto;
    }

    /* Summary Card - Flattened */
    .user-summary-card {
        background: var(--portal-gray);
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        border: 1px solid var(--border-light);
        margin-bottom: 20px;
    }

    .summary-avatar {
        font-size: 30px;
        color: var(--portal-blue);
        background: white;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--border-light);
    }

    .summary-details h4 {
        margin: 0;
        font-weight: 700;
        color: var(--portal-dark);
        font-size: 1.1rem;
    }

    .summary-details p {
        margin: 2px 0 0;
        color: #666;
        font-size: 13px;
    }

    /* Details Grid */
    .details-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .detail-card {
        background: white;
        padding: 12px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--border-light);
    }

    .card-icon {
        color: var(--portal-blue);
        font-size: 14px;
    }

    .card-content {
        display: flex;
        flex-direction: column;
    }

    .card-label {
        font-size: 9px;
        text-transform: uppercase;
        font-weight: 700;
        color: #999;
    }

    .card-value {
        font-size: 13px;
        font-weight: 600;
        color: var(--portal-dark);
    }

    /* Config Section */
    .config-container-premium {
        margin-top: 20px;
        border: 1px solid var(--border-light);
        border-radius: 8px;
        overflow: hidden;
    }

    .config-header {
        background: var(--portal-gray);
        padding: 10px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border-light);
    }

    .config-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--portal-dark);
        font-size: 13px;
    }

    .config-count {
        background: var(--portal-blue);
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
    }

    .premium-table {
        width: 100%;
        font-size: 13px;
    }

    .premium-table thead th {
        background: #fafafa;
        padding: 10px 15px;
        text-align: left;
        font-size: 11px;
        color: #777;
        border-bottom: 1px solid var(--border-light);
    }

    .premium-table tbody td {
        padding: 10px 15px;
        border-bottom: 1px solid #f9f9f9;
    }

    /* Badge Style */
    .status-badge-premium {
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .status-approved-p { background: #e6fdf5; color: #059669; border-color: #a7f3d0; }
    .status-pending-p { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .status-rejected-p { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

    .portal-modal-footer {
        padding: 12px 20px;
        background: #fafafa;
        border-top: 1px solid var(--border-light);
        display: flex;
        justify-content: center;
    }

    .btn-portal-close {
        background: var(--portal-dark);
        color: white;
        border: none;
        padding: 8px 40px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 12px;
        transition: 0.2s;
    }

    .btn-portal-close:hover {
        background: #000;
    }

    @media (max-width: 768px) {
        .details-cards-grid { grid-template-columns: 1fr; }
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function closeRequestModal() {
        $("#accountRequestModal").modal("hide");
    }

    function closeViewDetailsModal() {
        $("#viewDetailsModal").modal("hide");
    }

    function viewDetails(element) {
        let request;
        try {
            // Read data from the button's data-request attribute
            request = $(element).data('request');
            if (typeof request === 'string') {
                request = JSON.parse(request);
            }
        } catch (e) {
            console.error("Error parsing request data:", e);
            alert("Error loading request details.");
            return;
        }

        console.log("Request Data:", request);
        
        // Fill summary header
        $("#view_name_summary").text(request.name || 'User Request');
        $("#view_email_summary").text(request.email || 'No email provided');

        // Fill basic info cards
        $("#view_phone").text(request.phone || 'N/A');
        $("#view_userType").text(request.userType || 'N/A');
        $("#view_deviceCategory").text(request.deviceCategory || 'N/A');
        
        // Format Date
        let dateObj = new Date(request.created_at);
        let formattedDate = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        $("#view_date").text(formattedDate);
        
        // Premium Status Badge Logic
        let status = request.status || 'pending';
        let statusClass = 'status-pending-p';
        if (status.toLowerCase().includes('approved')) statusClass = 'status-approved-p';
        if (status.toLowerCase().includes('rejected')) statusClass = 'status-rejected-p';
        
        $("#view_status_badge_container").html(`<span class="status-badge-premium ${statusClass}">${status.toUpperCase()}</span>`);

        // Handling Rejection Reason
        if (request.description) {
            $("#view_rejection_reason").text(request.description);
            $("#rejection_reason_container").show();
        } else {
            $("#rejection_reason_container").hide();
        }

        // Fill configurations with clean rows
        let configBody = $("#config_body");
        configBody.empty();
        let settingCount = 0;

        if (request.configurations) {
            try {
                let configs = typeof request.configurations === 'string' ? JSON.parse(request.configurations) : request.configurations;
                settingCount = Object.keys(configs).length;
                
                if (settingCount > 0) {
                    for (let key in configs) {
                        let valObj = configs[key];
                        let value = (valObj && typeof valObj === 'object') ? (valObj.value || 'N/A') : valObj;
                        let label = key.replace(/_/g, ' ').toUpperCase();
                        configBody.append(`
                            <tr>
                                <td style="color: #64748b; font-weight: 700; font-size: 11px;">${label}</td>
                                <td style="color: #1e293b; font-weight: 700;">${value}</td>
                            </tr>
                        `);
                    }
                } else {
                    configBody.append('<tr><td colspan="2" class="text-center py-4 text-muted small">No configurations found.</td></tr>');
                }
            } catch (e) {
                console.error("Error parsing configurations:", e);
                configBody.append('<tr><td colspan="2" class="text-center py-4 text-danger small">Error reading configuration data.</td></tr>');
            }
        } else {
            configBody.append('<tr><td colspan="2" class="text-center py-4 text-muted small">No configurations provided.</td></tr>');
        }

        $("#config_count").text(`${settingCount} Settings Found`);
        $("#viewDetailsModal").modal("show");
    }

    function openResendModal(name, email, userType) {
        // Set form values
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;
        document.getElementById('user_type').value = userType;
        
        // Change modal title & button text (optional)
        document.getElementById('accountRequestModalLabel').innerText = 'Resend Account Request';
        document.querySelector('#accountRequestModal button[type="submit"]').innerText = 'Resend Request';

        // Open modal
        $("#accountRequestModal").modal("show");
    }

    function requestModel() {
        // Reset form values
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('user_type').value = '';

        // Reset modal title & button text
        document.getElementById('accountRequestModalLabel').innerText = 'Send Account Request';
        document.querySelector('#accountRequestModal button[type="submit"]').innerText = 'Send Request';

        $("#accountRequestModal").modal("show");
    }

    function cancelAcceptModel(id) {
        $("#approvalModal" + id).modal("hide");
    }

    function cancelRejectModel(id) {
        $('#rejectModal' + id).modal('hide');
    }

    function showRejectModal(id) {
        $('#rejectModal' + id).modal('show');
        $('#rejectForm' + id).on('submit', function(e) {
            let reason = $('#rejectReason' + id).val().trim();
            if (!reason) {
                e.preventDefault();
                alert("Please provide a rejection reason.");
                return false;
            }
            $('#rejectReasonInput' + id).val(reason);
        });
    }

    function showDeleteModal(id) {
        $('#deleteModal' + id).modal('show');
    }

    function confirmDelete(id, response) {
        const urlType = `{{ $url_type }}`;
        const form = document.getElementById('deleteForm-' + id);
        form.action = `/${urlType}/delete-modal/${id}/${response}`;
        form.submit();
    }

    function showApprovalModal(id, action) {
        $("#actionText" + id).text(action);
        $("#actionInput" + id).val(action);

        $("#approvalModal" + id).modal("show");
    }
    $(document).ready(function() {
        $("#approvalRequests").dataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,
            // pageLength: 10,
            // scrollX: true,
            // scrollY: '500px',
            scrollCollapse: true,
            "aLengthMenu": [
                [25, 50, 100, 500, -1],
                [25, 50, 100, 500, "All"]
            ],
            "iDisplayLength": 25
        });

    });

    function openModel(id) {
        $('.error_msg').hide().text();
        $('#firmwareId' + id).val(id);
        $('#addModel' + id).modal('show');

    }

    function getModelById(firmwareId) {
        $('.hide-field').hide();
        $("#modalName").val("");
        $("#vendorId").val("");
        $("#modalId").val("");
        let id = $('#userAssign').val();
        $.ajax({
            url: `/admin/getModelById/` + id + `/` + firmwareId,
            type: 'GET',
            processData: false,
            contentType: false,
            success: function(response) {
                let result = JSON.parse(response);
                if (result.status == 200 && result.modal != null) {
                    if (result.modal) {
                        $('.hide-field').show();
                        $("#modalName").val(result.modal.name);
                        $("#vendorId").val(result.modal.vendorId);
                        $("#modalId").val(result.modal.id);
                    }
                } else {
                    $('.hide-field').show();
                }
            },
        });
    }
</script>


{{-- ✅ Custom Tab CSS --}}
<style>
    .custom-tabs {
        display: flex;
        gap: 1px;
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 6px;
    }

    .tab-btn {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 8px 16px;
        font-size: 14px;
        border-radius: 8px 8px 0 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .tab-btn:hover {
        background: #e9ecef;
    }

    .tab-btn.active {
        background: #007bff;
        color: #fff;
        font-weight: 600;
        border-bottom: 2px solid #007bff;
    }
</style>

{{-- ✅ JavaScript for Filtering + Count --}}
<script>
    // document.addEventListener("DOMContentLoaded", () => {
    //     const tabs = document.querySelectorAll(".tab-btn");
    //     const rows = document.querySelectorAll("#approvalRequests tbody tr");

    //     // Count status occurrences
    //     const counts = {
    //         all: rows.length,
    //         Approved: 0,
    //         AdminApprovalPending: 0,
    //         SupportApprovalPending: 0,
    //         RequestMailSent: 0,
    //         RejectedByAdmin: 0,
    //         RejectedBySupport: 0
    //     };

    //     rows.forEach(row => {
    //         const status = row.getAttribute("data-status");

    //         console.log("status ==>", status);
    //         if (counts[status] !== undefined) counts[status]++;
    //     });

    //     // Update tab counts
    //     Object.keys(counts).forEach(status => {
    //         const el = document.getElementById(`count-${status}`);
    //         if (el) el.textContent = counts[status];
    //     });

    //     // Filter rows on tab click
    //     tabs.forEach(tab => {
    //         tab.addEventListener("click", () => {
    //             tabs.forEach(t => t.classList.remove("active"));
    //             tab.classList.add("active");

    //             const status = tab.getAttribute("data-status");

    //             rows.forEach(row => {
    //                 const rowStatus = row.getAttribute("data-status");
    //                 if (status === "all" || rowStatus === status) {
    //                     row.style.display = "";
    //                 } else {
    //                     row.style.display = "none";
    //                 }
    //             });
    //         });
    //     });
    // });

    document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab-btn");
    const rows = document.querySelectorAll("#approvalRequests tbody tr");

    // Initialize counts
    const counts = {
        all: rows.length,
        Approved: 0,
        AdminApprovalPending: 0,
        SupportApprovalPending: 0,
        RequestMailSent: 0,
        RejectedByAdmin: 0,
        RejectedBySupport: 0,
        pendingGroup: 0,
        rejectedGroup: 0
    };

    // Count individual statuses
    rows.forEach(row => {
        const status = row.getAttribute("data-status");
        if (counts[status] !== undefined) counts[status]++;
    });

    // Calculate grouped counts
    counts.pendingGroup = counts.AdminApprovalPending + counts.SupportApprovalPending + counts.RequestMailSent;
    counts.rejectedGroup = counts.RejectedByAdmin + counts.RejectedBySupport;

    // Update tab counts dynamically
    Object.keys(counts).forEach(status => {
            console.log("`count-${status}`", `count-${status}`)
        const el = document.getElementById(`count-${status}`);
        if (el) el.textContent = counts[status];
    });

    // Handle tab click
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            // Activate selected tab
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const status = tab.getAttribute("data-status");

            rows.forEach(row => {
                const rowStatus = row.getAttribute("data-status");

                // Show rows according to tab status
                if (
                    status === "all" ||
                    rowStatus === status ||
                    (status === "pending-group" && (rowStatus === "AdminApprovalPending" || rowStatus === "SupportApprovalPending" || rowStatus === "RequestMailSent")) ||
                    (status === "rejected-group" && (rowStatus === "RejectedByAdmin" || rowStatus === "RejectedBySupport"))
                ) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
});

</script>
@stop