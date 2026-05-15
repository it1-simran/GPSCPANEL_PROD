<?php
use App\Helper\CommonHelper;
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/ticket-management.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/ticket-management.css')) }}">
@endpush
@section('content')


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

