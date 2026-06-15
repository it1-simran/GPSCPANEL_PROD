<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-raised-ticket') }}">
@endpush
@section('content')


<section id="main-content" class="tv-page">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Support Management</a></li>
                        <li class="active"><a href="#">View Ticket</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <div class="tv-title-row">
                            <div>
                                <h2>
                                    <span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;"></span>
                                    View Ticket
                                </h2>
                            </div>
                            <div>
                                <button type="button" class="btn tv-btn-primary" onclick="openModel()">
                                    Add Ticket
                                </button>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div><!--/.c_title-->
                    <div class="c_content tv-ticket-table-wrap">
                        <div class="row" id="alert_msg">
                            @include('partials.gps-inline-alerts')
                        </div>
                        @if(Auth::user()->user_type == "Admin")
                        <div class="col-lg-12 text-right margin-bottom-10">
                            <a href="{{ route('backend.excel') }}" class="btn btn-success">Download Excel</a>
                            <a href="{{ route('backend.csv') }}" class="btn btn-success">Download CSV</a>
                        </div>
                        @endif
                        <table id="esim" class="example table table-condensed cf" style="width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Ticket Type</th>
                                    <th>Ticket Subject</th>
                                    <th style="width: 15%; text-align: center;">Description</th>
                                    <th>Status</th>
                                    <th>Created at</th>
                                    <th>Updated at</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <?php
                            $i =  1;
                            ?>
                            <tbody>
                                @foreach ($ticketList as $list)
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td>{{$list->type}}</td>
                                    <td>{{$list->subject}}</td>
                                    <td class="text-center tv-desc-td">
                                        <div class="tv-desc-box"
                                             data-id="{{ $list->id }}"
                                             data-type="{{ $list->type }}"
                                             data-subject="{{ $list->subject }}"
                                             data-description="{{ $list->description }}"
                                             data-file="{{ !empty($list->file) ? asset('storage/' . $list->file) : '' }}"
                                             data-status="{{ strtolower($list->status) }}"
                                             data-created="{{ CommonHelper::getDateAsTimeZone($list->created_at) ?? 'N/A' }}"
                                             data-updated="{{ CommonHelper::getDateAsTimeZone($list->updated_at) ?? 'N/A' }}">
                                             <button type="button" class="tv-tbl-eye-btn" title="View Description & Details" onclick="showTicketDetails(this)">
                                                 <i class="fa fa-eye"></i>
                                             </button>
                                        </div>
                                    </td>
                                    <td>
                                        @if(strtolower($list->status) === 'open')
                                        <span class="tv-badge-open">Open</span>
                                        @elseif(strtolower($list->status) === 'resolved')
                                        <span class="tv-badge-resolved">Resolved</span>
                                        @else
                                        <span class="tv-badge-other">{{ ucfirst($list->status) }}</span>
                                        @endif
                                    </td>

                                    <td>{{CommonHelper::getDateAsTimeZone($list->created_at) ?? 'N/A'}}</td>
                                    <td>{{CommonHelper::getDateAsTimeZone($list->updated_at) ?? 'N/A' }}</td>
                                    <td>
                                        <form action="/{{$url_type}}/delete-backend/{{$list->id}}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button class="swal-confirm btn btn-sm btn-tv-delete" data-confirm-msg="Are you sure you want to delete this?"  type="submit">Delete</button>

                                        </form>
                                    </td>
                                </tr>
                                <?php
                                $i++;
                                ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div><!--/.c_content-->
                </div><!--/.c_panels-->
            </div><!--/col-md-12-->
        </div><!--/row-->

        <!--======= Dynamic Datatable Content Start End ========-->
    </section>
</section>

<div class="modal tv-modal" id="raiseTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">Raise a Ticket</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <!-- Form -->
            <form id="raiseTicketForm" onsubmit="return false" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Error Message -->
                    <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
                    <!-- Ticket Type -->
                    <div class="form-group mb-3">
                        <label for="ticketType">Ticket Type</label>
                        <select class="form-control" id="ticketType" name="ticket_type" required>
                            <option value="">Select Type</option>
                            <option value="error">Error</option>
                            <option value="updation">Updation</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                    <!-- Ticket Subject -->
                    <div class="form-group mb-3">
                        <label for="ticketSubject">Ticket Subject</label>
                        <select class="form-control" id="ticketSubject" name="ticket_subject" required>
                            <option value="">Select Subject</option>
                            <!-- <option value="device">Device</option>
                            <option value="device_category">Device Category</option>
                            <option value="firmware_model">Firmware & Model</option>
                            <option value="vendor_assign">Vendor Assign</option> -->
                        </select>
                    </div>
                    <!-- Error File -->
                    <div class="form-group mb-3">
                        <label for="errorFile">Error File (Optional)</label>
                        <input type="file" class="form-control" id="errorFile" name="error_file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    </div>
                    <!-- Ticket Description -->
                    <div class="form-group mb-3">
                        <label for="ticketDescription">Ticket Description</label>
                        <textarea class="form-control" id="ticketDescription" name="ticket_description" rows="4" required></textarea>
                    </div>
                </div>
                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="SubmitTicket" class="btn btn-primary">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Premium Ticket Details Modal (Dynamic) -->
<div id="tvTicketDetailsModal" class="tv-details-modal" onclick="if(event.target === this || event.target.classList.contains('tv-details-modal-backdrop')) closeTicketDetails();">
  <div class="tv-details-modal-backdrop"></div>
  <div class="tv-details-modal-dialog">
    <div class="tv-details-modal-content tk-modal-content">
      <div class="tk-modal-header">
        <div class="tk-modal-title">
          <span class="tk-modal-icon"><i class="fa fa-ticket"></i></span>
          <div>
            <h5>Ticket Details</h5>
            <small>Support Request</small>
          </div>
        </div>
        <button type="button" class="tk-modal-close tv-details-modal-close" aria-label="Close" onclick="closeTicketDetails()">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="tk-modal-body">
        <!-- Subject + Type Pill -->
        <div class="tk-modal-subject-row">
          <div class="tk-modal-subject" id="tvModalSubject">Ticket Subject</div>
          <span class="tk-modal-type-pill" id="tvModalType">Type</span>
        </div>

        <!-- Description -->
        <div class="tk-modal-section">
          <div class="tk-modal-section-title"><i class="fa fa-align-left"></i> Description</div>
          <div class="tk-modal-desc" id="tvModalDesc">--</div>
        </div>

        <!-- File (Dynamic Attached File Box) -->
        <div class="tk-modal-section" id="tvModalFileSection" style="display: none;">
          <div class="tk-modal-section-title"><i class="fa fa-paperclip"></i> Attached File</div>
          <div class="tk-file-btns">
            <a href="#" target="_blank" class="tk-file-btn" id="tvModalFileView"><i class="fa fa-eye"></i> View File</a>
            <a href="#" download class="tk-file-btn" id="tvModalFileDownload"><i class="fa fa-download"></i> Download</a>
          </div>
        </div>

        <!-- Meta Information Grid -->
        <div class="tk-modal-info-grid">
          <div class="tk-modal-info-card">
            <div class="tk-info-label"><i class="fa fa-info-circle"></i> Status</div>
            <span class="tk-badge" id="tvModalStatus">Pending</span>
          </div>
          <div class="tk-modal-info-card">
            <div class="tk-info-label"><i class="fa fa-calendar"></i> Created</div>
            <div class="tk-info-val" id="tvModalCreated">--</div>
          </div>
          <div class="tk-modal-info-card">
            <div class="tk-info-label"><i class="fa fa-calendar-check-o"></i> Updated</div>
            <div class="tk-info-val" id="tvModalUpdated">--</div>
          </div>
        </div>
      </div>
      <div class="tk-modal-footer">
        <button type="button" class="tk-btn-close tv-btn-close-details-modal" onclick="closeTicketDetails()">
          <i class="fa fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

@stop

@push('scripts')
<script>
(function ($) {
    console.log("GPSC View Ticket script loaded and executing.");

    // Expose openModel globally for the inline onclick handler
    window.openModel = function () {
        console.log("Opening Raise Ticket modal.");
        $("#raiseTicketModal").modal();
    };

    // Expose editBackend globally just in case
    window.editBackend = function (backendData) {
        $('#addBackendLabel').text("Edit Backend");
        $('#name').val(backendData.name);
        $('#backendId').val(backendData.id);
        $("#addBackend").modal();
    };

    // Use delegated document events for select/clicks so they work immediately
    // and aren't blocked by any ready state errors
    $(document).on('change', '#ticketType', function() {
        let value = $(this).val();
        let $subject = $('#ticketSubject');
        $subject.empty().append('<option value="">Select Subject</option>');

        let subjects = [];
        if (value === 'error') {
            subjects = ['Firmware Download', 'Device Not Found', "Connection Failed", 'Others'];
        } else if (value === 'updation') {
            subjects = ['New Device Category', 'Firmware', 'Assign Vendor & Model', "Access Permission", "Custom Report"];
        } else if (value === 'support') {
            subjects = ['Password Reset', 'Account Unlock', 'General Help', 'Others'];
        }

        $.each(subjects, function(i, subject) {
            $subject.append('<option value="' + subject + '">' + subject + '</option>');
        });
    });

    // Delegated click for Submit Ticket button
    $(document).on('click', '#SubmitTicket', function() {
        function validateTicketForm() {
            let isValid = true;
            let errorMessage = '';

            if ($('#ticketType').val().trim() === '') {
                isValid = false;
                errorMessage += 'Ticket Type is required.<br>';
            }
            if ($('#ticketSubject').val().trim() === '') {
                isValid = false;
                errorMessage += 'Ticket Subject is required.<br>';
            }
            if ($('#ticketDescription').val().trim() === '') {
                isValid = false;
                errorMessage += 'Ticket Description is required.<br>';
            }

            if (!isValid) {
                $('.error_msg').show().html(errorMessage);
            }
            return isValid;
        }

        if (validateTicketForm()) {
            $('.error_msg').hide();
            var formData = new FormData($('#raiseTicketForm')[0]);

            $.ajax({
                url: '/support/create-ticket',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    let result = JSON.parse(response);
                    if (result.status === 200) {
                        alert(result.status_msg);
                        $('#raiseTicketModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert('Error occurred: ' + result.status_msg);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while submitting the ticket.');
                }
            });
        }
    });

    // ==========================================
    // Premium Ticket Details Modal Functionality
    // ==========================================
    
    // Resilient Global Vanilla JS functions
    window.showTicketDetails = function (button) {
        try {
            console.log("Eye toggle click event triggered!");
            var box = button.closest('.tv-desc-box');
            if (!box) {
                console.error("box element (.tv-desc-box) not found!");
                return;
            }

            var subject = box.getAttribute('data-subject') || '';
            var type = box.getAttribute('data-type') || '';
            var description = box.getAttribute('data-description') || '';
            var file = box.getAttribute('data-file') || '';
            var status = box.getAttribute('data-status') || '';
            var created = box.getAttribute('data-created') || '--';
            var updated = box.getAttribute('data-updated') || '--';

            console.log("Ticket details loaded successfully: ", { subject: subject, type: type });

            // Helper to set text content safely
            var setElText = function(id, text) {
                var el = document.getElementById(id);
                if (el) el.textContent = text;
                else console.warn("Element #" + id + " not found!");
            };

            setElText('tvModalSubject', subject);
            setElText('tvModalType', type);
            setElText('tvModalDesc', description);

            var fileSection = document.getElementById('tvModalFileSection');
            if (fileSection) {
                if (file) {
                    var fileView = document.getElementById('tvModalFileView');
                    var fileDownload = document.getElementById('tvModalFileDownload');
                    if (fileView) fileView.setAttribute('href', file);
                    if (fileDownload) fileDownload.setAttribute('href', file);
                    fileSection.style.display = 'block';
                } else {
                    fileSection.style.display = 'none';
                }
            }

            var statusBadge = document.getElementById('tvModalStatus');
            if (statusBadge) {
                statusBadge.textContent = status.toUpperCase();
                statusBadge.className = 'tk-badge ' + (status === 'resolved' ? 'tk-badge-resolved' : 'tk-badge-open');
            }

            setElText('tvModalCreated', created);
            setElText('tvModalUpdated', updated);

            var detailsModal = document.getElementById('tvTicketDetailsModal');
            if (detailsModal) {
                // Force display flex to override any other rules
                detailsModal.style.setProperty('display', 'flex', 'important');
                
                // Add class in timeout for transition
                setTimeout(function () {
                    detailsModal.classList.add('show');
                    console.log("Details Modal opened successfully.");
                }, 20);
            } else {
                console.error("Element #tvTicketDetailsModal not found in DOM!");
            }
        } catch (err) {
            console.error("Error in showTicketDetails: ", err);
        }
    };

    window.closeTicketDetails = function () {
        try {
            console.log("Closing ticket details modal.");
            var detailsModal = document.getElementById('tvTicketDetailsModal');
            if (detailsModal) {
                detailsModal.classList.remove('show');
                setTimeout(function () {
                    detailsModal.style.setProperty('display', 'none', 'important');
                }, 300);
            }
        } catch (err) {
            console.error("Error in closeTicketDetails: ", err);
        }
    };

    // Close on Escape key press (Native)
    window.addEventListener('keyup', function(e) {
        if (e.key === "Escape") { 
            window.closeTicketDetails();
        }
    });

    // Initialize Datatables on window load or DOMContentLoaded (independent of jQuery ready failures)
    function initMyDatatables() {
        try {
            if ($.fn.dataTable) {
                $('.example').each(function() {
                    var elementId = $(this).attr('id');
                    // Avoid double initialization
                    if (!$.fn.DataTable.isDataTable("#" + elementId)) {
                        $("#" + elementId).dataTable({
                            paging: true,
                            searching: true,
                            info: true,
                            ordering: true,
                            lengthChange: true,
                            scrollCollapse: true,
                            "aLengthMenu": [
                                [25, 50, 100, 500, -1],
                                [25, 50, 100, 500, "All"]
                            ],
                            "iDisplayLength": 25
                        });
                    }
                });
                console.log("Datatables initialized successfully.");
            } else {
                console.warn("Datatables library not found on this page.");
            }
        } catch (err) {
            console.error("Error during Datatables initialization: ", err);
        }
    }

    if (document.readyState === "complete" || document.readyState === "interactive") {
        initMyDatatables();
    } else {
        document.addEventListener("DOMContentLoaded", initMyDatatables);
    }

})(window.jQuery || window.$);
</script>
@endpush