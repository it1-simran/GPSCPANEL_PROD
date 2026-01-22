<?php

use App\DeviceCategory;
use App\Models\TimezoneModel;
use App\Helper\CommonHelper;

$timeZones = TimezoneModel::all();
$deviceCategory = DeviceCategory::where('is_deleted', '0')->get();
?>
<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Register - GPS Control Panel</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0bb2d4, #0992ad);
            font-family: 'Raleway', sans-serif;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.15);
            padding: 30px;
            background: #fff;
        }

        .card h2 {
            font-weight: 700;
            color: #0bb2d4;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .btn-custom {
            background: #0bb2d4;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-custom:hover {
            background: #0992ad;
        }

        .form-footer {
            text-align: center;
            margin-top: 15px;
        }

        .form-footer a {
            color: #0bb2d4;
            font-weight: 600;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="d-flex justify-content-center align-items-center">
        <div class="card  col-sm-7 mx-auto">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="alert alert-danger alert-dismissible fade showErrorMSG show" role="alert" style="display:none;">
                <p class="errorMsgText" style="margin-bottom: 0;"></p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>⚠️ Please fix the following errors:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            <h2 class="text-center mb-4">Create Account</h2>

            <form method="POST" id="registerForm" action="{{ route('register.user.store') }}" class="row">
                @csrf

                <div class="mb-3 col-sm-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required value="{{ $name ?? '' }}" readonly>
                </div>

                <div class="mb-3 col-sm-6">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="{{ $email ?? '' }}" readonly>
                </div>
                <div class="mb-3 col-sm-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text"
                        name="phone"
                        class="form-control"
                        placeholder="Enter phone number"
                        maxlength="10"
                        pattern="\d{10}"
                        title="Phone number must be exactly 10 digits"
                        required>
                </div>

                <div class="mb-3 col-sm-6">
                    <label for="timezone" class="form-label">TimeZones <span class="require">*</span></label>
                    <select name="timezone" class="select2" id="timezone">
                        <option value="">Please Select Time Zone</option>
                        @foreach($timeZones as $timezone)
                        @php
                        $tzValue = $timezone->name . ' (' . $timezone->utc_offset . ')';
                        @endphp
                        <option value="{{ $timezone->name }}" {{ $timezone->name == 'Asia/Kolkata' ? 'selected' : '' }}>
                            {{ $tzValue }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 col-sm-6">
                    <label for="user_type" class="form-label">Account Type</label>
                    <select name="user_type" class="form-select" required>
                        <option value="">Select Account Type</option>
                        <option value="dealer">Dealer</option>
                        <option value="manufacturer">Manufacturer</option>
                    </select>
                </div>
                <div class="mb-3 col-sm-6">
                    <label for="device_category" class="form-label">Device Category</label>
                    <select id="deviceCategorySelect" name="device_category" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($deviceCategory as $category)
                        <option value="{{$category->id}}">{{$category->device_category_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div id="deviceConfigWrapper" style="display:none;">
                    <h5 class="mt-3">Default Configuration</h5>
                    <div id="deviceConfigFields" class="row"></div>
                </div>
                <button type="button" id="registerBtn" class="btn btn-custom w-100">
                    <span class="btn-text">Register</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>

            </form>
        </div>
    </div>
    <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow-lg rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">🔐 Email Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center px-4">
                    <p class="text-muted mb-3">
                        We’ve sent a <span class="fw-semibold text-dark">6-digit OTP</span> to your email.
                        Please enter it below to verify your account.
                    </p>

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <input type="text" maxlength="1" class="form-control otp-input" />
                        <input type="text" maxlength="1" class="form-control otp-input" />
                        <input type="text" maxlength="1" class="form-control otp-input" />
                        <input type="text" maxlength="1" class="form-control otp-input" />
                        <input type="text" maxlength="1" class="form-control otp-input" />
                        <input type="text" maxlength="1" class="form-control otp-input" />
                    </div>

                    <small id="otpMessage" class="text-muted d-block"></small>
                </div>

                <div class="modal-footer border-0 d-flex justify-content-between px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" id="verifyOtpBtn" class="btn btn-success rounded-3 px-4">
                        Verify OTP
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<style>
    .otp-input {
        width: 50px;
        height: 55px;
        font-size: 1.5rem;
        text-align: center;
        border-radius: 10px;
        border: 1px solid #ddd;
        transition: 0.2s;
    }

    .otp-input:focus {
        border-color: #198754;
        box-shadow: 0 0 5px rgba(25, 135, 84, 0.4);
        outline: none;
    }
</style>
<!-- Bootstrap JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value === '' && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Search and Select",
        });
        // On Register click → send OTP & open modal
        $('#registerBtn').click(function(e) {
            e.preventDefault();

            let $btn = $(this);
            let $spinner = $btn.find('.spinner-border');
            let $btnText = $btn.find('.btn-text');
            $spinner.removeClass('d-none');
            $btnText.text('Processing...');
            let formData = $('#registerForm').serialize();

            $.ajax({
                url: "{{ route('send.otp') }}",
                type: "POST",
                data: formData + '&_token={{ csrf_token() }}',
                success: function(res) {
                    $spinner.addClass('d-none');
                    $btnText.text('Register');
                    if (res.success) {
                        $('#otpMessage').text("OTP sent to your email.");
                        $('#otpModal').modal('show');
                    } else {
                        $('.showErrorMSG').show();
                        $('.errorMsgText').html(res.message);
                        $('html, body').animate({
                            scrollTop: 0
                        }, 'slow')
                    }
                },
                error: function(xhr) {
                    let error = JSON.parse(xhr.responseText);
                    $spinner.addClass('d-none');
                    $btnText.text('Register');
                    $('.showErrorMSG').show();
                    $('.showErrorMSG').addClass('show');
                    $('.errorMsgText').html(error.message);
                    $('html, body').animate({
                        scrollTop: 0
                    }, 'slow')
                }
            });
        });

        // $('#registerBtn').click(function(e) {
        //     e.preventDefault();

        //     // Collect all form inputs
        //     let formData = $('#registerForm').serialize();

        //     // Send form data to controller for validation + OTP
        //     $.ajax({
        //         url: "{{ route('send.otp') }}",
        //         type: "POST",
        //         data: formData + '&_token={{ csrf_token() }}',
        //         success: function(res) {
        //             if (res.success) {
        //                 $('#otpMessage').text("OTP sent to your email.");
        //                 $('#otpModal').modal('show');
        //             }
        //         },
        //         error: function(xhr) {
        //             // Show validation errors
        //             let errors = xhr.responseJSON.errors;
        //             let messages = [];
        //             for (let field in errors) {
        //                 messages.push(errors[field][0]);
        //             }
        //             alert(messages.join("\n"));
        //         }
        //     });
        // });

        // $('#registerBtn').click(function(e) {
        //     e.preventDefault();
        //     let email = $('input[name="email"]').val();
        //     if (!email) {
        //         alert("Please enter email before proceeding.");
        //         return;
        //     }

        //     $.ajax({
        //         url: "{{ url('/send-otp') }}",
        //         type: "POST",
        //         data: {
        //             _token: "{{ csrf_token() }}",
        //             email: email
        //         },
        //         success: function(res) {
        //             $('#otpMessage').text("OTP has been sent to " + email);
        //             $('#otpModal').modal('show');
        //         },
        //         error: function() {
        //             alert("Failed to send OTP.");
        //         }
        //     });
        // });

        // Verify OTP
        $('#verifyOtpBtn').click(function() {
            // Get OTP from 6 input boxes
            let otp = '';
            $('.otp-input').each(function() {
                otp += $(this).val();
            });

            // Verify OTP via AJAX
            $.ajax({
                url: "{{ url('/verify-otp') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    otp: otp
                },
                success: function(res) {
                    if (res.valid) {
                        $('#otpMessage').text("✅ OTP Verified");
                        $('#otpModal').modal('hide');

                        // Submit the form to register.user.store route
                        $('#registerForm').attr('action', "{{ route('register.user.store') }}");
                        $('#registerForm').off('submit').submit();
                    } else {
                        $('#otpMessage').text("❌ Invalid OTP, try again.");
                    }
                }
            });
        });


    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#deviceCategorySelect').on('change', function() {
            let categoryId = $(this).val();
            if (!categoryId) {
                $('#deviceConfigWrapper').hide();
                return;
            }

            $.ajax({
                url: '/device-category/config/' + categoryId,
                type: 'GET',
                success: function(response) {
                    let fields = response.config;
                    let templates = response.templates;
                    let container = $("#deviceConfigFields");
                    container.empty();
                    fields.forEach((field, index) => {
                        let validation = field.validation;
                        console.log('validation ==>', validation);
                        // ✅ Only allow fields with ID 18 (IP Test) and 19 (Port)
                        // if (![18, 19].includes(parseInt(field.id))) {
                        //     return; // skip all other fields
                        // }

                        let input = '';
                        // 🔹 Sanitize key -> lowercase + replace spaces with underscores
                        let safeKey = field.key.toLowerCase().replace(/\s+/g, '_');

                        if (['number', 'text', 'IP/URL'].includes(field.type)) {
                            input = `<input type="${field.type === 'IP/URL' ? 'text' : field.type}"
                            class="form-control"
                            name="config[${safeKey}]"
                            value="${field.default}">`;
                        } else if (field.type === 'select') {
                            console.log("field ==>", field);
                            input = `<select class="form-control" name="config[${safeKey}]">
                                ${validation.selectOptions.map((val, index) => 
                                    `<option value="${validation.selectValues[index]}" selected>${val}</option>`
                                ).join('')}
                            </select>`;
                        } else if (field.type === 'multiselect') {
                            input = `<select class="form-control" name="config[${safeKey}][]" multiple>
                                ${validation.selectOptions.map((val, index) => 
                                    `<option value="${validation.selectValues[index]}" selected>${val}</option>`
                                ).join('')}
                            </select>`;
                        } else if (field.type === 'text_array') {
                            input = `<input type="text" class="form-control"
                            name="config[${safeKey}][]"
                            value="${field.default}">`;
                        }

                        // ✅ Add hidden input for field.id
                        input += `<input type="hidden" name="ids[${safeKey}_id]" value="${field.id}">`;

                        container.append(`
                            <div class="col-md-6 mb-3">
                                <label><b>${field.key}</b></label>
                                ${input}
                            </div>
                        `);
                    });


                    $("#deviceConfigWrapper").show();
                }
            });
        });

    })
</script>