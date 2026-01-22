<?php
use App\Helper\CommonHelper;
?>
@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Ticket Management</a></li>
                        <li class="active"><a href="#">View Tickets</a></li>
                    </ul>
                </nav>
            </div>
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
                        <table id="ticketTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Resolved At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ticketList as $index => $ticket)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ ucfirst($ticket->type) }}</td>
                                    <td>{{ ucfirst($ticket->subject) }}</td>
                                    <td>{{ $ticket->description }}</td>
                                    <td>
                                        @if($ticket->status === 'open')
                                        <span class="badge badge-warning">Open</span>
                                        @else
                                        <span class="badge badge-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td>{{ CommonHelper::getDateAsTimeZone($ticket->created_at)}}</td>
                                    <td>{{ CommonHelper::getDateAsTimeZone($ticket->resolved_at) ?? '--'}}</td>
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
                                                <div class="modal-content shadow-lg border-0 rounded-3">
                                                    <div class="modal-header bg-primary text-white d-flex justify-content-between">
                                                        <h5 class="modal-title">
                                                            <i class="fa fa-ticket-alt me-2"></i> Ticket Details
                                                        </h5>
                                                    </div>
                                                    <div class="ticket-modal-body">
                                                        <!-- Header -->
                                                        <div class="ticket-header">
                                                            <h4 id="ticketSubject{{ $ticket->id }}">{{ $ticket->subject }}</h4>
                                                            <span id="ticketType{{ $ticket->id }}" class="ticket-type-badge">{{ $ticket->type }}</span>
                                                        </div>

                                                        <!-- Description -->
                                                        <div class="ticket-description-section">
                                                            <h6 class="ticket-description-title"><i class="fa fa-align-left me-1"></i> Description</h6>
                                                            <div id="ticketDescription{{ $ticket->id }}" class="ticket-description">
                                                                {{ $ticket->description }}
                                                            </div>
                                                        </div>

                                                        <!-- File Attachment -->
                                                        @if(!empty($ticket->file))
                                                        <div class="ticket-file-section">
                                                            <h6><i class="fa fa-file-alt me-1"></i> Attached File</h6>
                                                            <a href="{{ asset('storage/' . $ticket->file) }}" target="_blank">
                                                                <i class="fa fa-eye me-1"></i> View File
                                                            </a>
                                                            <a href="{{ asset('storage/' . $ticket->file) }}" download>
                                                                <i class="fa fa-download me-1"></i> Download
                                                            </a>
                                                        </div>
                                                        @endif

                                                        <!-- Info Cards -->
                                                        <div class="ticket-info-row">
                                                            <div class="ticket-info-card">
                                                                <h6><i class="fa fa-info-circle me-2"></i> Status</h6>
                                                                <span id="ticketStatus{{ $ticket->id }}" class="ticket-status-badge {{ $ticket->is_read ? 'ticket-status-resolved' : 'ticket-status-open' }}">
                                                                    {{ $ticket->is_read ? 'Resolved' : 'Pending' }}
                                                                </span>
                                                            </div>

                                                            <div class="ticket-info-card">
                                                                <h6><i class="fa fa-calendar-plus me-2"></i> Created</h6>
                                                                <span id="ticketCreated{{ $ticket->id }}">{{ CommonHelper::getDateAsTimeZone($ticket->created_at) }}</span>
                                                            </div>

                                                            <div class="ticket-info-card">
                                                                <h6><i class="fa fa-calendar-check me-2"></i> Updated</h6>
                                                                <span id="ticketUpdated{{ $ticket->id }}">{{ CommonHelper::getDateAsTimeZone($ticket->updated_at) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light btn-close-modal" data-bs-dismiss="modal" data-id="{{ $ticket->id }}">
                                                            <i class="fa fa-times me-1"></i> Close
                                                        </button>
                                                        @if(Auth::user()->user_type == "Admin" && $ticket->status == 'open')
                                                        <button type="button" class="btn btn-success markResolvedBtn" data-id="{{ $ticket->id }}">
                                                            <i class="fa fa-check-circle me-1"></i> Mark as Resolved
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



    $(document).ready(function() {
        $("#ticketTable").DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,
            scrollCollapse: true,
            aLengthMenu: [
                [25, 50, 100, 500, -1],
                [25, 50, 100, 500, "All"]
            ],
            iDisplayLength: 25
        });
    });
</script>
<style>
    /* Modal Body */
    .ticket-modal-body {
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    /* Header Section */
    .ticket-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
    }

    .ticket-header h4 {
        font-weight: 700;
        color: #0d6efd;
        font-size: 1.5rem;
        /* Standard heading size */
        margin-bottom: 8px;
    }

    .ticket-type-badge {
        display: inline-block;
        background-color: #0dcaf0;
        color: #fff;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.95rem;
        /* slightly larger */
    }

    /* Description Section */
    .ticket-description-title {
        font-size: 1rem;
        /* standard readable size */
        color: #495057;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .ticket-description {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 12px;
        color: #212529;
        font-size: 0.95rem;
        /* standard paragraph text */
    }

    /* File Section */
    .ticket-file-section h6 {
        font-size: 1rem;
        /* standard heading */
        color: #495057;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .ticket-file-section a {
        font-size: 0.9rem;
        padding: 5px 10px;
        margin-right: 5px;
        text-decoration: none;
        border: 1px solid #0d6efd;
        border-radius: 4px;
        color: #0d6efd;
        transition: 0.2s;
    }

    .ticket-file-section a:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    /* Info Cards */
    .ticket-info-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 20px;
    }

    .ticket-info-card {
        flex: 1 1 30%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .ticket-info-card h6 {
        font-size: 1rem;
        /* standard heading */
        color: #495057;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .ticket-info-card span {
        font-weight: 600;
        color: #212529;
        font-size: 0.95rem;
    }

    /* Status Badge */
    .ticket-status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .ticket-status-open {
        background-color: #ffc107;
        color: #212529;
    }

    .ticket-status-resolved {
        background-color: #198754;
        color: #fff;
    }
</style>