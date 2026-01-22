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
                                            <td>{{ $request->deviceIp }}</td>
                                            <td>{{ $request->devicePort }}</td>
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
                                                    onclick="openResendModal('{{ $request->name }}', '{{ $request->email }}')">
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
                                        <td>{{ $request->deviceIp }}</td>
                                        <td>{{ $request->devicePort }}</td>
                                        <td>{{ $request->resend_count }}</td>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ ucfirst($request->status) }}</span>
                                        </td>
                                        <td>{{ $request->created_at->format('d M Y H:i') }}</td>
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
                                                onclick="openResendModal('{{ $request->name }}', '{{ $request->email }}')">
                                                Resend Request
                                            </button>
                                            @endif
                                            <!-- Modal -->
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
                                                        <div class="modal-header custom-modal-header">
                                                            <h5 class="modal-title">Reject Request</h5>
                                                            <button type="button" class="bg-calendar-content btn-close margin-top-1" data-bs-dismiss="modal" aria-label="Close"
                                                                onclick="cancelRejectModel({{ $request->id }})" style="border:none;">X</button>
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
@stop
<style>
    /* Custom modal background and border */
    .custom-modal-content {
        background-color: #fff;
        border-radius: 12px;
        border: 1px solid #dee2e6;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Custom header */
    .custom-modal-header {
        background-color: #f8f9fa;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Modal body */
    .custom-modal-body {
        padding: 1.5rem;
        font-size: 1rem;
    }

    /* Modal footer */
    .custom-modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        padding: 1rem;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Custom buttons */
    .custom-btn-secondary {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: 0.3s;
    }

    .custom-btn-secondary:hover {
        background-color: #5a6268;
    }

    .custom-btn-primary {
        background-color: #0d6efd;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        transition: 0.3s;
    }

    .custom-btn-primary:hover {
        background-color: #0b5ed7;
    }

    /* Responsive fix for small screens */
    @media (max-width: 576px) {
        .custom-modal-body {
            font-size: 0.95rem;
            padding: 1rem;
        }

        .custom-modal-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .custom-btn-primary,
        .custom-btn-secondary {
            width: 100%;
        }
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function closeRequestModal() {
        $("#accountRequestModal").modal("hide");
    }

    function openResendModal(name, email) {
        // Set form values
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;

        // Change modal title & button text (optional)
        document.getElementById('accountRequestModalLabel').innerText = 'Resend Account Request';
        document.querySelector('#accountRequestModal button[type="submit"]').innerText = 'Resend Request';

        // Open modal
        $("#accountRequestModal").modal("show");
    }

    function requestModel() {
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