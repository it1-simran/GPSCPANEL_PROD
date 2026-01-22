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
                        <li><a href="#">Support Management</a></li>
                        <li class="active"><a href="#">Raise Ticket</a></li>
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
                                <h2>View Ticket</h2>
                            </div>
                            <div class="col-lg-6 text-right">
                                <button type="button" class="btn btn-primary" onclick="openModel()">
                                    Add Ticket
                                </button>
                            </div>
                        </div>

                        <div class="clearfix"></div>
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
                        @if(Auth::user()->user_type == "Admin")
                        <div class="col-lg-12 text-right margin-bottom-10">
                            <a href="{{ route('backend.excel') }}" class="btn btn-success">Download Excel</a>
                            <a href="{{ route('backend.csv') }}" class="btn btn-success">Download CSV</a>
                        </div>
                        @endif
                        <table id="esim" class="example table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Ticket Type</th>
                                    <th>Ticket Subject</th>
                                    <th>Decription</th>
                                    <th>Status</th>
                                    <th>Created at</th>
                                    <th>Updated By</th>
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
                                    <td>{{$list->description}}</td>
                                    <td>
                                        @if(strtolower($list->status) === 'open')
                                        <span class="badge bg-warning text-dark">Open</span>
                                        @elseif(strtolower($list->status) === 'resolved')
                                        <span class="badge bg-success text-white">Resolved</span>
                                        @else
                                        <span class="badge bg-secondary text-white">{{ ucfirst($list->status) }}</span>
                                        @endif
                                    </td>

                                    <td>{{CommonHelper::getDateAsTimeZone($list->created_at) ?? 'N/A'}}</td>
                                    <td>{{CommonHelper::getDateAsTimeZone($list->updated_at) ?? 'N/A' }}</td>
                                    <td>
                                        <form action="/{{$url_type}}/delete-backend/{{$list->id}}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm margin-top-1" type="submit">Delete</button>

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
<div class="modal" id="raiseTicketModal" tabindex="-1" aria-hidden="true">
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

@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function editBackend(backendData) {
        $('#addBackendLabel').text("Edit Backend");
        $('#name').val(backendData.name);
        $('#backendId').val(backendData.id);
        $("#addBackend").modal();
    }

    function openModel() {
        // $('#raiseTicketModal').text("ADD Ticket");
        // $('#backendId').val('');
        $("#raiseTicketModal").modal();
    }
    $(document).ready(function() {
        $('#ticketType').change(function() {
            let value = $(this).val();

            // Reset ticketSubject first
            $('#ticketSubject').empty().append('<option value="">Select Subject</option>');

            let subjects = [];

            if (value === 'error') {
                subjects = ['Firmware Download', 'Device Not Found', "Connection Failed", 'Others'];
            } else if (value === 'updation') {
                subjects = ['New Device Category', 'Firmware', 'Assign Vendor & Model', "Access Permission", "Custom Report"];
            } else if (value === 'support') {
                subjects = ['Password Reset', 'Account Unlock', 'General Help', 'Others'];
            }

            // Append options dynamically
            $.each(subjects, function(i, subject) {
                $('#ticketSubject').append('<option value="' + subject + '">' + subject + '</option>');
            });
        });

        $('.example').each(function() {
            var elementId = $(this).attr('id');
            $("#" + elementId).dataTable({
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
        $('#SubmitTicket').on('click', function() {
            function validateTicketForm() {
                let isValid = true;
                let errorMessage = '';

                // Validate Ticket Type
                if ($('#ticketType').val().trim() === '') {
                    isValid = false;
                    errorMessage += 'Ticket Type is required.<br>';
                }

                // Validate Ticket Subject
                if ($('#ticketSubject').val().trim() === '') {
                    isValid = false;
                    errorMessage += 'Ticket Subject is required.<br>';
                }

                // Validate Ticket Description
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
                    url: '/support/create-ticket', // 🔹 Replace with your actual endpoint (or support/create-ticket if needed)
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

    });
</script>