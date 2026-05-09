<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.tv-page { font-family: 'Inter', sans-serif; }
#main-content .wrapper { padding-top: 8px !important; }

/* Breadcrumb */
.tv-breadcrumb-wrap { padding: 8px 0 14px 0; }
.tv-breadcrumb {
    display: inline-flex;
    align-items: center;
    background: #1e293b;
    border-radius: 50px;
    padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
}
.tv-breadcrumb .bc-home {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #76CF1C;
    color: #1e293b;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}
.tv-breadcrumb .bc-item { color: rgba(255,255,255,0.68); font-size: 13px; font-weight: 500; }
.tv-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
.tv-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; }

/* Panel + title */
.c_panel {
    border: none !important;
    border-radius: 12px !important;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important;
}
.c_title {
    background: #1e293b !important;
    padding: 14px 20px !important;
    border-bottom: none !important;
}
.tv-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.tv-title-row h2 {
    margin: 0;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
}
.tv-btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    border: none !important;
    border-radius: 7px !important;
    height: 34px;
    padding: 0 16px !important;
    color: #1e293b !important;
    font-size: 13px !important;
    font-weight: 800 !important;
    box-shadow: 0 4px 12px rgba(118,207,28,0.3);
}

/* Table style (version-control reference) */
#esim {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 6px !important;
    border: none !important;
}
#esim.table { border: none !important; }
#esim > thead > tr > th,
#esim > tbody > tr > td { border: none !important; }
#esim thead th {
    background: transparent !important;
    color: #64748b !important;
    font-size: 11px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    font-weight: 700 !important;
    padding: 8px 14px !important;
    border-bottom: 2px solid #f1f5f9 !important;
}
#esim tbody tr { background: #fff; transition: box-shadow 0.2s, background 0.2s; }
#esim tbody tr:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
#esim tbody td {
    vertical-align: middle !important;
    padding: 13px 14px !important;
    background: #fff !important;
    color: #334155;
    font-size: 13px;
    border-top: 1px solid #e9ecef !important;
    border-bottom: 1px solid #e9ecef !important;
}
#esim tbody td:first-child {
    border-left: 1px solid #e9ecef !important;
    border-top-left-radius: 8px !important;
    border-bottom-left-radius: 8px !important;
    color: #94a3b8;
    font-weight: 700;
}
#esim tbody td:last-child {
    border-right: 1px solid #e9ecef !important;
    border-top-right-radius: 8px !important;
    border-bottom-right-radius: 8px !important;
}

/* Modal theme */
.tv-modal .modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.22);
}
.tv-modal .modal-header {
    background: #1e293b;
    border-bottom: none;
    padding: 14px 20px;
}
.tv-modal .modal-title {
    color: #fff;
    font-weight: 700;
    letter-spacing: 0.4px;
}
.tv-modal .close {
    color: #fff;
    opacity: 0.8;
}
.tv-modal .modal-body {
    background: #f8fafc;
    padding: 20px;
}
.tv-modal .modal-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 12px 20px 16px;
}
.tv-modal .form-group label {
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}
.tv-modal .form-control {
    border: 1.5px solid #dbe3ef;
    border-radius: 8px;
    height: 40px;
    font-size: 13px;
}
.tv-modal textarea.form-control {
    height: auto;
    min-height: 80px;
}
.tv-modal .form-control:focus {
    border-color: #76CF1C;
    box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.15);
}
.tv-modal .btn-secondary {
    background: #e2e8f0 !important;
    border: none !important;
    color: #334155 !important;
    border-radius: 7px !important;
    font-weight: 700;
}
.tv-modal .btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    border: none !important;
    color: #1e293b !important;
    border-radius: 7px !important;
    font-weight: 800;
    box-shadow: 0 6px 14px rgba(118, 207, 28, 0.25);
}
</style>

<section id="main-content" class="tv-page">
    <section class="wrapper">
        <div class="tv-breadcrumb-wrap">
            <nav class="tv-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <span class="bc-item">Support Management</span>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Raise Ticket</span>
            </nav>
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