<?php
use App\Helper\CommonHelper;
$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/view-approval-request.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/view-approval-request.css')) }}">
@endpush
@section('content')

<section id="main-content">
  <section class="wrapper">
    {{-- BREADCRUMB --}}
    <div class="var-breadcrumb-wrap">
      <nav class="var-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="#" class="bc-item">Account Management</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View User Approval Request</span>
      </nav>
    </div>
    {{-- CONTENT --}}
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <div class="var-title-row">
                          <h2>
                            <span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;vertical-align:middle;"></span>
                            View User Approval Request
                          </h2>
                          <button type="button" class="var-btn-primary" onclick="requestModel()">
                            <i class="fa fa-paper-plane"></i> Send Account Request
                          </button>
                        </div>
                        <!-- Modal -->
                        <div class="modal" id="accountRequestModal" tabindex="-1" aria-labelledby="accountRequestModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route($url_type . '.request.send') }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="accountRequestModalLabel">Send Account Request</h5>
                                            <button type="button" class="arm-close" onclick="closeRequestModal()" aria-label="Close">
                                                <i class="fa fa-times"></i>
                                            </button>
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
                                            <button type="submit" class="btn btn-primary request-submit-btn">Send Request</button>
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
                                <table id="approvalRequests" class="table">
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
                                            <td>{{ $request->phone ?: 'N/A' }}</td>
                                            <td>{{ $request->userType ? ucfirst($request->userType) : 'N/A' }}</td>
                                            <td>{{ $request->deviceCategory ?: 'N/A' }}</td>
                                            @php
                                                $configurations = json_decode($request->configurations, true) ?: [];
                                                $deviceIp = \App\Helper\CommonHelper::emptyToNA($configurations['ip_test']['value'] ?? null);
                                                $devicePort = \App\Helper\CommonHelper::emptyToNA($configurations['port']['value'] ?? null);
                                            @endphp
                                            <td>{{ $deviceIp }}</td>
                                            <td>{{ $devicePort }}</td>
                                            <td>{{ $request->resend_count }}</td>
                                            <td>
                                                @php
                                                    $s = $request->status;
                                                    $badgeClass = str_contains(strtolower($s),'approved') ? 'approved' : (str_contains(strtolower($s),'reject') ? 'rejected' : (str_contains(strtolower($s),'pending') ? 'pending' : 'secondary'));
                                                @endphp
                                                <span class="var-badge {{ $badgeClass }}">{{ ucfirst($s) }}</span>
                                            </td>
                                            <td>{{ $request->created_at->format('d M Y H:i') }}</td>
                                            <td>
                                                <button class="var-btn-view btn-view-details" data-request="{{ json_encode($request) }}" onclick="viewDetails(this)" title="View Details">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                            </td>
                                            <td>
                                                {{-- Action Buttons --}}
                                                @if(in_array($request->status, ['AdminApprovalPending']))
                                                @if($url_type == 'admin')
                                                <button class="var-btn-approve" onclick="showApprovalModal({{ $request->id }}, 'Approved')">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                                @else
                                                <button class="var-btn-approve" onclick="showApprovalModal({{ $request->id }}, 'Approved')">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                                @endif
                                                <button class="var-btn-reject" onclick="showRejectModal({{ $request->id }})">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                                @elseif(in_array($request->status, ['RejectedByAdmin', 'RejectedBySupport', 'RequestMailSent']))
                                                <button type="button" class="var-btn-resend" onclick="openResendModal('{{ $request->name }}', '{{ $request->email }}', '{{ $request->userType }}')">
                                                    <i class="fa fa-repeat"></i> Resend
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
                                        <td>{{ $request->phone ?: 'N/A' }}</td>
                                        <td>{{ $request->userType ? ucfirst($request->userType) : 'N/A' }}</td>
                                        <td>{{ $request->deviceCategory ?: 'N/A' }}</td>
                                        @php
                                            $configurations = json_decode($request->configurations, true) ?: [];
                                            $deviceIp = \App\Helper\CommonHelper::emptyToNA($configurations['ip_test']['value'] ?? null);
                                            $devicePort = \App\Helper\CommonHelper::emptyToNA($configurations['port']['value'] ?? null);
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
            <div class="modal-header portal-modal-header">
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
                        <i class="fa fa-user" style="font-size:22px; color:#76CF1C;"></i>
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
                <div id="rejection_reason_container" style="display:none;" class="mt-3 mb-2">
                    <div class="var-rejection-box">
                        <div class="var-rejection-icon">
                            <i class="fa fa-ban"></i>
                        </div>
                        <div class="var-rejection-body">
                            <span class="var-rejection-label">Rejection Reason</span>
                            <p id="view_rejection_reason" class="var-rejection-text"></p>
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
        $("#approvalRequests").DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,
            scrollX: true,
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


{{-- Duplicate tab CSS removed; styles live in assets/css/portal/pages/view-approval-request.css --}}

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
