<?php
use App\Helper\CommonHelper;
?>
@extends('layouts.apps')
@section('content')
<style>
/* ===== BREADCRUMB ===== */
.tk-breadcrumb-wrap {
    padding: 14px 0 18px 0;
}
.tk-breadcrumb {
    display: inline-flex;
    align-items: center;
    background: #1e293b;
    border-radius: 50px;
    padding: 6px 18px 6px 8px;
    gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
}
.tk-breadcrumb .bc-home {
    width: 30px; height: 30px;
    background: #76CF1C;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
}
.tk-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
.tk-breadcrumb .bc-item {
    color: rgba(255,255,255,0.65);
    font-size: 13px; font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
}
.tk-breadcrumb .bc-sep {
    color: rgba(255,255,255,0.35);
    margin: 0 8px; font-size: 12px;
}
.tk-breadcrumb .bc-item.active {
    color: #76CF1C; font-weight: 700;
}

/* ===== DATATABLE PAGINATION STYLE ===== */
.dataTables_wrapper .dataTables_paginate {
    display: flex !important;
    align-items: center;
    gap: 4px;
    float: none !important;
    margin-top: 8px;
    flex-wrap: wrap;
}
/* Hide the ellipsis/dots boxes */
.dataTables_wrapper .dataTables_paginate span .paginate_button.disabled,
.dataTables_wrapper .dataTables_paginate .ellipsis,
.dataTables_paginate span > span {
    display: none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    background: transparent !important;
    border: none !important;
    color: #64748b !important;
    border-radius: 6px !important;
    padding: 5px 11px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: none !important;
    min-width: 32px;
    text-align: center;
    line-height: 1.5;
    display: inline-block;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f1f5f9 !important;
    color: #76CF1C !important;
    border: none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #76CF1C !important;
    border: none !important;
    color: #1e293b !important;
    font-weight: 800 !important;
    border-radius: 6px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.previous,
.dataTables_wrapper .dataTables_paginate .paginate_button.next {
    color: #1e293b !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    background: transparent !important;
    border: none !important;
    padding: 5px 10px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.previous.disabled,
.dataTables_wrapper .dataTables_paginate .paginate_button.next.disabled {
    color: #cbd5e1 !important;
    cursor: not-allowed !important;
}

/* Info text */
.dataTables_wrapper .dataTables_info {
    display: flex !important;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 13px;
    font-weight: 500;
    padding: 6px 0 !important;
    float: none !important;
}
.dataTables_wrapper .dataTables_info::before {
    content: '\f0cb';
    font-family: FontAwesome;
    color: #76CF1C;
    font-size: 14px;
}

/* Bottom wrapper layout — target directly, no box on col divs */
.dataTables_wrapper .row:last-child {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
    padding: 8px 2px;
    background: transparent;
    border: none;
    box-shadow: none;
}
/* Kill any col float/width so flex works */
.dataTables_wrapper .row:last-child > div {
    float: none !important;
    width: auto !important;
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    flex: 0 0 auto;
}
/* Info — take remaining space, push paginate to right */
.dataTables_wrapper .row:last-child > div:first-child {
    flex: 1 1 auto !important;
}
/* Paginate — stay on the right */
.dataTables_wrapper .row:last-child > div:last-child {
    flex: 0 0 auto !important;
    margin-left: auto;
}

/* remove extra top padding from wrapper */
#main-content .wrapper { padding-top: 10px !important; }

/* ===== TICKET TABLE — CARD STYLE (matches Packet Types) ===== */
#ticketTable {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 6px !important;
    border: none !important;
    font-family: 'Inter', sans-serif;
}
/* Kill Bootstrap table classes */
#ticketTable.table { border: none !important; }
#ticketTable > thead > tr > th,
#ticketTable > tbody > tr > td { border: none !important; }
#ticketTable > tbody > tr:nth-child(odd) > td,
#ticketTable > tbody > tr:nth-child(even) > td { background-color: transparent !important; }

/* Header */
#ticketTable thead th {
    background: transparent !important;
    color: #64748b !important;
    font-size: 11px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-weight: 700 !important;
    padding: 8px 14px !important;
    border-bottom: 2px solid #f1f5f9 !important;
    white-space: nowrap;
}

/* Body rows — card with border */
#ticketTable tbody tr {
    background: #ffffff;
    transition: box-shadow 0.2s ease, background 0.2s ease;
}
#ticketTable tbody tr:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}

/* Body cells */
#ticketTable tbody td {
    vertical-align: middle !important;
    padding: 13px 14px !important;
    background: #fff !important;
    color: #334155;
    font-size: 13px;
    border-top: 1px solid #e9ecef !important;
    border-bottom: 1px solid #e9ecef !important;
    border-left: none !important;
    border-right: none !important;
}
#ticketTable tbody tr:hover td { background: #f8faff !important; }
#ticketTable tbody td:first-child {
    border-left: 1px solid #e9ecef !important;
    border-top-left-radius: 8px !important;
    border-bottom-left-radius: 8px !important;
}
#ticketTable tbody td:last-child {
    border-right: 1px solid #e9ecef !important;
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
}

/* SR # */
.tk-sr { color: #94a3b8 !important; font-weight: 700 !important; font-size: 13px !important; }

/* Name cell (Type column) */
.tk-name-cell { display: flex; align-items: center; gap: 12px; }
.tk-row-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    flex: 0 0 40px; font-size: 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.tk-icon-red   { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #ef4444; }
.tk-icon-blue  { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #3b82f6; }
.tk-icon-green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #22c55e; }
.tk-name-cell strong { display: block; color: #0f172a; font-size: 14px; font-weight: 700; margin-bottom: 2px; }
.tk-name-cell small  { display: block; color: #94a3b8; font-size: 11px; font-weight: 500; }

/* Subject & Description */
.tk-subject { font-weight: 600 !important; color: #1e293b !important; }
.tk-desc    { color: #64748b !important; font-size: 12px !important; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tk-date    { color: #475569 !important; font-size: 12px !important; white-space: nowrap; }

/* Status badges */
.tk-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.tk-badge-resolved { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; box-shadow: 0 3px 8px rgba(34,197,94,0.25); }
.tk-badge-open     { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 3px 8px rgba(245,158,11,0.25); }

/* View button */
#ticketTable .viewTicketBtn {
    background: linear-gradient(135deg, #1e293b, #2d3f55) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 5px 14px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 3px 8px rgba(30,41,59,0.2) !important;
    display: inline-flex; align-items: center; gap: 5px;
}
#ticketTable .viewTicketBtn:hover {
    transform: translateY(-1px) !important;
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    box-shadow: 0 5px 14px rgba(118,207,28,0.3) !important;
}

/* c_panel wrapper cleanup */
.c_panel { border: none !important; border-radius: 12px !important; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important; }
.c_title { background: #1e293b !important; padding: 14px 20px !important; border-bottom: none !important; }
.c_title h2 { color: #fff !important; font-size: 16px !important; font-weight: 700 !important; margin: 0 !important; display: flex; align-items: center; gap: 8px; }
.c_title h2::before { content: '\f0e0'; font-family: FontAwesome; color: #76CF1C; font-size: 15px; }
</style>

<section id="main-content">
    <section class="wrapper">

        {{-- BREADCRUMB --}}
        <div class="tk-breadcrumb-wrap">
            <nav class="tk-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ url('admin') }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <a href="#" class="bc-item">Ticket Management</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">View Tickets</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Ticket List</h2>
                    </div>
                    <div class="c_content">
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <table id="ticketTable" class="table">
                            <thead>
                                <tr>
                                    <th>Sr.no</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Resolved At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketList as $index => $ticket)
                                <tr>
                                    <td class="tk-sr">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="tk-name-cell">
                                            <span class="tk-row-icon tk-icon-{{ strtolower($ticket->type) == 'error' ? 'red' : (strtolower($ticket->type) == 'updation' ? 'blue' : 'green') }}">
                                                <i class="fa fa-{{ strtolower($ticket->type) == 'error' ? 'exclamation-circle' : (strtolower($ticket->type) == 'updation' ? 'refresh' : 'ticket') }}"></i>
                                            </span>
                                            <span>
                                                <strong>{{ ucfirst($ticket->type) }}</strong>
                                                <small>Support Ticket</small>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="tk-subject">{{ ucfirst($ticket->subject) }}</td>

                                    <td>
                                        @if($ticket->status === 'open')
                                        <span class="tk-badge tk-badge-open">Open</span>
                                        @else
                                        <span class="tk-badge tk-badge-resolved">Resolved</span>
                                        @endif
                                    </td>
                                    <td class="tk-date">{{ CommonHelper::getDateAsTimeZone($ticket->created_at)}}</td>
                                    <td class="tk-date">{{ CommonHelper::getDateAsTimeZone($ticket->resolved_at) ?? '--'}}</td>
                                    <td>
                                        <button class="btn btn-info btn-sm viewTicketBtn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewTicketModal{{ $ticket->id }}"
                                            data-id="{{ $ticket->id }}"
                                            data-type="{{ $ticket->type }}"
                                            data-subject="{{ $ticket->subject }}"
                                            data-description="{{ $ticket->description }}"
                                            data-status="{{ $ticket->is_read ? 'Resolved' : 'Pending' }}"
                                            data-created="{{ CommonHelper::getDateAsTimeZone($ticket->created_at) }}"
                                            data-updated="{{ CommonHelper::getDateAsTimeZone($ticket->resolved_at) }}">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                        <div class="modal" id="viewTicketModal{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content tk-modal-content">
                                                    <div class="tk-modal-header">
                                                        <div class="tk-modal-title">
                                                            <span class="tk-modal-icon"><i class="fa fa-ticket"></i></span>
                                                            <div>
                                                                <h5>Ticket Details</h5>
                                                                <small>Support Request</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="tk-modal-close btn-close-modal" data-bs-dismiss="modal" data-id="{{ $ticket->id }}">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                    <div class="tk-modal-body">
                                                        <!-- Subject + Type -->
                                                        <div class="tk-modal-subject-row">
                                                            <div class="tk-modal-subject">{{ $ticket->subject }}</div>
                                                            <span class="tk-modal-type-pill">{{ ucfirst($ticket->type) }}</span>
                                                        </div>

                                                        <!-- Description -->
                                                        <div class="tk-modal-section">
                                                            <div class="tk-modal-section-title"><i class="fa fa-align-left"></i> Description</div>
                                                            <div class="tk-modal-desc">{{ $ticket->description }}</div>
                                                        </div>

                                                        <!-- File -->
                                                        @if(!empty($ticket->file))
                                                        <div class="tk-modal-section">
                                                            <div class="tk-modal-section-title"><i class="fa fa-paperclip"></i> Attached File</div>
                                                            <div class="tk-file-btns">
                                                                <a href="{{ asset('storage/' . $ticket->file) }}" target="_blank" class="tk-file-btn"><i class="fa fa-eye"></i> View File</a>
                                                                <a href="{{ asset('storage/' . $ticket->file) }}" download class="tk-file-btn"><i class="fa fa-download"></i> Download</a>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <!-- Info Cards -->
                                                        <div class="tk-modal-info-grid">
                                                            <div class="tk-modal-info-card">
                                                                <div class="tk-info-label"><i class="fa fa-info-circle"></i> Status</div>
                                                                <span class="tk-badge {{ $ticket->is_read ? 'tk-badge-resolved' : 'tk-badge-open' }}">
                                                                    {{ $ticket->is_read ? 'Resolved' : 'Pending' }}
                                                                </span>
                                                            </div>
                                                            <div class="tk-modal-info-card">
                                                                <div class="tk-info-label"><i class="fa fa-calendar"></i> Created</div>
                                                                <div class="tk-info-val">{{ CommonHelper::getDateAsTimeZone($ticket->created_at) }}</div>
                                                            </div>
                                                            <div class="tk-modal-info-card">
                                                                <div class="tk-info-label"><i class="fa fa-calendar-check-o"></i> Updated</div>
                                                                <div class="tk-info-val">{{ CommonHelper::getDateAsTimeZone($ticket->updated_at) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tk-modal-footer">
                                                        <button type="button" class="tk-btn-close btn-close-modal" data-bs-dismiss="modal" data-id="{{ $ticket->id }}">
                                                            <i class="fa fa-times"></i> Close
                                                        </button>
                                                        @if(Auth::user()->user_type == "Admin" && $ticket->status == 'open')
                                                        <button type="button" class="tk-btn-resolve markResolvedBtn" data-id="{{ $ticket->id }}">
                                                            <i class="fa fa-check-circle"></i> Mark as Resolved
                                                        </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No tickets found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>




@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).on('click', '.btn-close-modal', function() {
        let ticketId = $(this).data('id');
        $('#viewTicketModal' + ticketId).modal('hide');
    });
    $(document).on('click', '.markResolvedBtn', function() {
        let ticketId = $(this).data('id');
        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Resolving...');

        $.ajax({
            url: '/admin/tickets/' + ticketId + '/resolve',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 200) {
                    $('#ticketStatus' + ticketId).removeClass('bg-warning text-dark').addClass('bg-success text-white').text('Resolved');
                    $btn.closest('.modal').modal('hide');
                    location.reload(); // optional: refresh table row
                } else {
                    alert(res.message || 'Failed to resolve ticket.');
                }
            },
            error: function() {
                alert('Failed to resolve ticket.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-check-circle me-1"></i> Mark as Resolved');
            }
        });
    });
    $(document).on('click', '.viewTicketBtn', function() {
        // Get ticket data from button
        let ticketType = $(this).data('type');
        let ticketSubject = $(this).data('subject');
        let ticketDescription = $(this).data('description');
        let ticketStatus = $(this).data('status');
        let ticketCreated = $(this).data('created');
        let ticketUpdated = $(this).data('updated');
        let ticketId = $(this).data('id');

        // Populate modal elements
        $('#ticketType').text(ticketType);
        $('#ticketSubject').text(ticketSubject);
        $('#ticketDescription').text(ticketDescription);

        $('#ticketStatus')
            .text(ticketStatus)
            .toggleClass('bg-success text-white', ticketStatus.toLowerCase() === 'resolved')
            .toggleClass('bg-warning text-dark', ticketStatus.toLowerCase() !== 'resolved');

        $('#ticketCreated').text(ticketCreated);
        $('#ticketUpdated').text(ticketUpdated);

        // Store ticket ID in Mark as Resolved button
        $('#markResolvedBtn').data('id', ticketId);

        // Show modal (Bootstrap 5)
        $('#viewTicketModal' + ticketId).modal('show');
    });





    // $(document).ready(function() {
    //     $("#ticketTable").DataTable({
    //         paging: true,
    //         searching: true,
    //         info: true,
    //         ordering: true,
    //         lengthChange: true,
    //         scrollCollapse: true,
    //         aLengthMenu: [
    //             [25, 50, 100, 500, -1],
    //             [25, 50, 100, 500, "All"]
    //         ],
    //         iDisplayLength: 25
    //     });
    // });


    $(document).ready(function () {
        $('#ticketTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,
            responsive: true,         
            autoWidth: false,          
            scrollX: true,          
            scrollCollapse: true,
            lengthMenu: [
                [25, 50, 100, 500, -1],
                [25, 50, 100, 500, "All"]
            ],
            pageLength: 25
        });
    });
</script>
<style>
/* ===== TICKET MODAL ===== */

.tk-modal-content {
    border: none !important;
    border-radius: 14px !important;
    overflow: hidden;
    font-family: 'Inter', sans-serif;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18) !important;
}
.tk-modal-header {
    background: #1e293b;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.tk-modal-title { display: flex; align-items: center; gap: 14px; }
.tk-modal-icon {
    width: 40px; height: 40px;
    background: rgba(118,207,28,0.15);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #76CF1C; font-size: 18px;
    border: 1px solid rgba(118,207,28,0.25);
}
.tk-modal-title h5 { margin: 0; color: #fff; font-size: 16px; font-weight: 700; line-height: 1.2; }
.tk-modal-title small { color: #94a3b8; font-size: 12px; font-weight: 400; }
.tk-modal-close {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    color: #fff; width: 32px; height: 32px;
    border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; transition: all 0.2s; padding: 0;
}
.tk-modal-close:hover { background: rgba(239,68,68,0.2); border-color: rgba(239,68,68,0.4); color: #ef4444; }

/* Body */
.tk-modal-body { padding: 22px; background: #fff; }

/* Subject row */
.tk-modal-subject-row { margin-bottom: 18px; }
.tk-modal-subject { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
.tk-modal-type-pill {
    display: inline-block;
    background: rgba(118,207,28,0.12); color: #5a9e12;
    border: 1px solid rgba(118,207,28,0.3);
    padding: 3px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600; text-transform: capitalize;
}

/* Sections */
.tk-modal-section { margin-bottom: 18px; }
.tk-modal-section-title {
    font-size: 12px; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: 0.8px;
    margin-bottom: 8px; display: flex; align-items: center; gap: 6px;
}
.tk-modal-section-title i { color: #76CF1C; }
.tk-modal-desc {
    background: #f8fafc; border: 1px solid #e9ecef;
    border-left: 3px solid #76CF1C;
    border-radius: 6px; padding: 12px 14px;
    color: #334155; font-size: 13px; line-height: 1.6;
}

/* File buttons */
.tk-file-btns { display: flex; gap: 8px; flex-wrap: wrap; }
.tk-file-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border: 1px solid #1e293b;
    border-radius: 6px; color: #1e293b;
    font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.2s;
}
.tk-file-btn:hover { background: #1e293b; color: #76CF1C; text-decoration: none; }

/* Info grid */
.tk-modal-info-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; margin-top: 18px;
}
.tk-modal-info-card {
    background: #f8fafc; border: 1px solid #e9ecef;
    border-radius: 8px; padding: 14px;
}
.tk-info-label {
    font-size: 11px; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.6px;
    margin-bottom: 8px; display: flex; align-items: center; gap: 5px;
}
.tk-info-label i { color: #76CF1C; }
.tk-info-val { font-weight: 600; color: #1e293b; font-size: 12px; }

/* Footer */
.tk-modal-footer {
    padding: 14px 22px; background: #f8fafc;
    border-top: 1px solid #e9ecef;
    display: flex; justify-content: flex-end; gap: 10px;
}
.tk-btn-close {
    background: #fff; border: 1px solid #e2e8f0; color: #64748b;
    padding: 7px 18px; border-radius: 7px; font-size: 13px; font-weight: 600;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;
}
.tk-btn-close:hover { border-color: #cbd5e1; background: #f1f5f9; }
.tk-btn-resolve {
    background: linear-gradient(135deg, #76CF1C, #5fa816);
    border: none; color: #fff; padding: 7px 18px;
    border-radius: 7px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 12px rgba(118,207,28,0.3); transition: all 0.2s;
}
.tk-btn-resolve:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(118,207,28,0.4); }

@media (max-width: 600px) {
    .tk-modal-info-grid { grid-template-columns: 1fr; }
}
</style>
