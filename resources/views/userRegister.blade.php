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
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            background: #0f172a;
            font-family: 'Segoe UI', 'Raleway', sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; top: -40%; right: -20%;
            width: 700px; height: 700px;
            background: radial-gradient(circle, rgba(118,207,28,0.08) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed; bottom: -30%; left: -15%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(118,207,28,0.05) 0%, transparent 70%);
            border-radius: 50%; pointer-events: none;
        }

        .reg-wrapper {
            min-height: 100vh; display: flex;
            align-items: center; justify-content: center;
            padding: 40px 16px;
        }

        .card {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.05) !important;
            padding: 0 !important;
            background: #fff !important;
            overflow: hidden;
            max-width: 750px;
            width: 100%;
        }
        .card-accent-bar {
            height: 5px; width: 100%;
            background: linear-gradient(90deg, #76CF1C, #1e293b, #76CF1C);
        }
        .card-inner { padding: 36px 36px 28px; }

        .card-logo {
            text-align: center; margin-bottom: 8px;
        }
        .card-logo .logo-ring {
            width: 56px; height: 56px; border-radius: 14px;
            background: linear-gradient(135deg, rgba(118,207,28,0.12), rgba(118,207,28,0.05));
            border: 2px solid rgba(118,207,28,0.2);
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
        }
        .card-logo .logo-ring i { color: #76CF1C; font-size: 22px; }

        .card h2 {
            font-weight: 800 !important; color: #0f172a !important;
            font-size: 22px !important; margin-bottom: 4px !important;
            letter-spacing: -0.3px;
        }
        .card .reg-subtitle {
            color: #94a3b8; font-size: 13px; font-weight: 500;
            margin-bottom: 28px; text-align: center;
        }

        .form-label {
            font-weight: 700 !important; color: #334155 !important;
            font-size: 12.5px !important; letter-spacing: 0.2px;
            margin-bottom: 5px !important;
        }

        .form-control, .form-select {
            min-height: 42px !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            font-size: 13px !important; color: #334155 !important;
            background: #f8fafc !important;
            transition: all 0.2s !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #76CF1C !important;
            box-shadow: 0 0 0 3px rgba(118,207,28,0.12) !important;
            background: #fff !important;
        }
        .form-control[readonly] {
            background: #f1f5f9 !important; color: #64748b !important;
        }

        .btn-custom {
            background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
            color: #0f172a !important; font-weight: 800 !important;
            border-radius: 10px !important; border: none !important;
            padding: 12px 0 !important; font-size: 14px !important;
            letter-spacing: 0.3px; transition: all 0.2s !important;
            box-shadow: 0 6px 20px rgba(118,207,28,0.3) !important;
        }
        .btn-custom:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(118,207,28,0.35) !important;
            color: #0f172a !important;
        }

        .form-footer { text-align: center; margin-top: 15px; }
        .form-footer a { color: #76CF1C; font-weight: 600; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }

        /* Select2 */
        .select2-container .select2-selection--single {
            height: 42px !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 0.375rem 0.75rem !important;
            display: flex !important; align-items: center !important;
            background: #f8fafc !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important; padding-left: 0 !important;
            color: #334155 !important; font-size: 13px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important; top: 1px !important; right: 10px !important;
        }
        .select2-container--open .select2-selection--single {
            border-color: #76CF1C !important;
            box-shadow: 0 0 0 3px rgba(118,207,28,0.12) !important;
        }
        .select2-dropdown {
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
        }
        .select2-results__option--highlighted {
            background: #76CF1C !important; color: #0f172a !important;
        }

        .config-header-bg {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; border-left: 4px solid #76CF1C;
            font-size: 14px; font-weight: 700; color: #fff;
            letter-spacing: 0.3px;
        }

        .require { color: #ef4444; font-weight: 700; margin-left: 3px; }

        /* Alerts */
        .alert-success {
            background: linear-gradient(135deg, rgba(118,207,28,0.08), rgba(118,207,28,0.04)) !important;
            border: 1px solid rgba(118,207,28,0.25) !important; color: #2d6a0e !important;
            border-radius: 10px !important;
        }
        .alert-danger {
            background: linear-gradient(135deg, rgba(239,68,68,0.06), rgba(239,68,68,0.03)) !important;
            border: 1px solid rgba(239,68,68,0.2) !important; color: #b91c1c !important;
            border-radius: 10px !important;
        }
        .alert-warning {
            border-radius: 10px !important;
        }

        /* OTP Modal */
        #otpModal .modal-content {
            border: none !important; border-radius: 20px !important;
            overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.25) !important;
        }
        #otpModal .modal-header {
            background: linear-gradient(135deg, #0f172a, #1e293b) !important;
            border: none !important; padding: 18px 24px !important;
        }
        #otpModal .modal-title {
            color: #fff !important; font-weight: 700 !important;
            font-size: 16px !important;
        }
        #otpModal .modal-header .btn-close {
            filter: invert(1); opacity: 0.6;
        }
        #otpModal .modal-body {
            padding: 28px 24px 16px !important;
        }
        #otpModal .btn-success {
            background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
            border: none !important; color: #0f172a !important;
            font-weight: 800 !important; border-radius: 10px !important;
            box-shadow: 0 4px 14px rgba(118,207,28,0.3) !important;
        }
        #otpModal .btn-outline-secondary {
            border-radius: 10px !important; font-weight: 600 !important;
        }

        @media (max-width: 768px) {
            .card-inner { padding: 24px 20px 20px; }
        }
        @media (max-width: 480px) {
            .reg-wrapper { padding: 16px 8px; }
            .card { border-radius: 14px !important; }
            .card-inner { padding: 20px 14px 16px; }
            .card-logo .logo-ring { width: 48px; height: 48px; font-size: 20px; }
            .card h2 { font-size: 1.3rem; }
            .reg-subtitle { font-size: 13px; }
            .form-label { font-size: 11px; }
            .form-control, .form-select { font-size: 13px; min-height: 38px; }
            .btn-custom { font-size: 14px; padding: 11px; }
        }
    </style>
</head>

<body>
    <div class="reg-wrapper">
        <div class="card">
            <div class="card-accent-bar"></div>
            <div class="card-inner">

                <div class="card-logo">
                    <div class="logo-ring"><i class="fa fa-user-plus"></i></div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="alert alert-danger alert-dismissible fade showErrorMSG show" role="alert" style="display:none;">
                    <p class="errorMsgText" style="margin-bottom: 0;"></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(isset($user) && in_array($user->status, ['RejectedBySupport', 'RejectedByAdmin']) && $user->description)
                <div class="alert alert-warning border-start border-danger border-4 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-exclamation-circle text-danger me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <h6 class="fw-bold text-danger mb-1">Attention: Previous Request Rejected</h6>
                            <p class="mb-0 text-muted small"><strong>Reason:</strong> "{{ $user->description }}"</p>
                            <p class="mb-0 text-muted small mt-1">Please update the information below and resubmit.</p>
                        </div>
                    </div>
                </div>
                @endif

                <h2 class="text-center">Create Account</h2>
                <p class="reg-subtitle">Fill in the details below to complete your registration</p>

                <form method="POST" id="registerForm" action="{{ route('register.user.store') }}" class="row">
                    @csrf

                    <div class="mb-3 col-sm-6">
                        <label for="name" class="form-label"><i class="fa fa-user" style="color:#76CF1C;margin-right:5px;font-size:11px;"></i> Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your name" required value="{{ $name ?? '' }}" readonly>
                    </div>

                    <div class="mb-3 col-sm-6">
                        <label for="email" class="form-label"><i class="fa fa-envelope" style="color:#76CF1C;margin-right:5px;font-size:11px;"></i> Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="{{ $email ?? '' }}" readonly>
                    </div>
                    <div class="mb-3 col-sm-6">
                        <label for="phone" class="form-label"><i class="fa fa-phone" style="color:#76CF1C;margin-right:5px;font-size:11px;"></i> Phone Number</label>
                        <input type="text"
                            name="phone"
                            class="form-control"
                            placeholder="Enter phone number"
                            maxlength="10"
                            pattern="\d{10}"
                            title="Phone number must be exactly 10 digits"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            required>
                    </div>

                    <div class="mb-3 col-sm-6">
                        <label for="timezone" class="form-label"><i class="fa fa-globe" style="color:#76CF1C;margin-right:5px;font-size:11px;"></i> TimeZones <span class="require">*</span></label>
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
                        <label for="user_type" class="form-label"><i class="fa fa-id-badge" style="color:#76CF1C;margin-right:5px;font-size:11px;"></i> Account Type</label>
                        <select class="form-select" disabled required>
                            <option value="">Select Account Type</option>
                            <option value="dealer" {{ (isset($user) && strtolower($user->userType) == 'dealer') ? 'selected' : '' }}>Dealer</option>
                            <option value="manufacturer" {{ (isset($user) && strtolower($user->userType) == 'manufacturer') ? 'selected' : '' }}>Manufacturer</option>
                        </select>
                        <input type="hidden" name="user_type" value="{{ (isset($user) && strtolower($user->userType) == 'manufacturer') ? 'manufacturer' : 'dealer' }}">
                    </div>
                    <div class="mb-3 col-sm-6">
                        <label for="device_category" class="form-label"><i class="fa fa-cubes" style="color:#76CF1C;margin-right:5px;font-size:11px;"></i> Device Category</label>
                        <select id="deviceCategorySelect" name="device_category" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($deviceCategory as $category)
                            <option value="{{$category->id}}">{{$category->device_category_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="deviceConfigWrapper" style="display:none;">
                        <h5 class="mt-4 config-header-bg"><i class="fa fa-cogs" style="margin-right:8px;color:#76CF1C;"></i> Default Configuration</h5>
                        <div id="deviceConfigFields" class="row"></div>
                    </div>
                    <button type="button" id="registerBtn" class="btn btn-custom w-100 mt-2">
                        <span class="btn-text"><i class="fa fa-check-circle" style="margin-right:6px;"></i> Register</span>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>

                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow-lg rounded-4 border-0">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-lock" style="margin-right:8px;color:#76CF1C;"></i> Email Verification</h5>
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
        width: 50px; height: 55px;
        font-size: 1.5rem; text-align: center;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        transition: 0.2s; font-weight: 700;
        color: #0f172a;
    }
    .otp-input:focus {
        border-color: #76CF1C;
        box-shadow: 0 0 0 3px rgba(118,207,28,0.15);
        outline: none; background: #fff;
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
            width: '100%'
        });
        // On Register click → send OTP & open modal
        $('#registerBtn').click(function(e) {
            e.preventDefault();

            let form = document.getElementById('registerForm');
            if (!form.reportValidity()) {
                return;
            }

            let $btn = $(this);
            let $spinner = $btn.find('.spinner-border');
            let $btnText = $btn.find('.btn-text');
            $spinner.removeClass('d-none');
            $btnText.text('Processing...');
            let formData = $('#registerForm').serialize();

            $.ajax({
                url: "{{ route('guest.send.otp') }}",
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
                url: "{{ route('guest.verify.otp') }}",
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

                        let attrs = '';
                        if (field.required) attrs += ' required';
                        
                        // Check for category-level numeric range
                        if (field.type === 'number' && field.numberRange && !Array.isArray(field.numberRange)) {
                            if (field.numberRange.min !== undefined && field.numberRange.min !== '') attrs += ` min="${field.numberRange.min}"`;
                            if (field.numberRange.max !== undefined && field.numberRange.max !== '') attrs += ` max="${field.numberRange.max}"`;
                        } 
                        // Global fallback for numeric range
                        else if (field.type === 'number' && validation && validation.numberInput) {
                            if (validation.numberInput.min !== undefined) attrs += ` min="${validation.numberInput.min}"`;
                            if (validation.numberInput.max !== undefined) attrs += ` max="${validation.numberInput.max}"`;
                        }

                        // Check for category-level maxlength
                        if (['text', 'IP/URL', 'text_array'].includes(field.type)) {
                            if (field.maxValueInput && !Array.isArray(field.maxValueInput) && field.maxValueInput !== "") {
                                attrs += ` maxlength="${field.maxValueInput}"`;
                            } else if (validation && validation.maxValueInput) {
                                attrs += ` maxlength="${validation.maxValueInput}"`;
                            }
                        }

                        if (['number', 'text', 'IP/URL'].includes(field.type)) {
                            input = `<input type="${field.type === 'IP/URL' ? 'text' : field.type}"
                            class="form-control"
                            name="config[${safeKey}]"
                            value="${field.default}" ${attrs}>`;
                        } else if (field.type === 'select') {
                            console.log("field ==>", field);
                            let optionsHtml = '';
                            if (field.selectOptions && Array.isArray(field.selectOptions) && field.selectOptions.length > 0) {
                                optionsHtml = field.selectOptions.map(opt => 
                                    `<option value="${opt.value}" ${opt.value == field.default ? 'selected' : ''}>${opt.option}</option>`
                                ).join('');
                            } else if (validation && validation.selectOptions) {
                                optionsHtml = validation.selectOptions.map((val, index) => 
                                    `<option value="${validation.selectValues[index]}" ${validation.selectValues[index] == field.default ? 'selected' : ''}>${val}</option>`
                                ).join('');
                            }
                            input = `<select class="form-control" name="config[${safeKey}]" ${field.required ? 'required' : ''}>
                                ${optionsHtml}
                            </select>`;
                        } else if (field.type === 'multiselect') {
                             let optionsHtml = '';
                            if (field.selectOptions && Array.isArray(field.selectOptions) && field.selectOptions.length > 0) {
                                optionsHtml = field.selectOptions.map(opt => 
                                    `<option value="${opt.value}">${opt.option}</option>`
                                ).join('');
                            } else if (validation && validation.selectOptions) {
                                optionsHtml = validation.selectOptions.map((val, index) => 
                                    `<option value="${validation.selectValues[index]}">${val}</option>`
                                ).join('');
                            }
                            input = `<select class="form-control" name="config[${safeKey}][]" multiple ${field.required ? 'required' : ''}>
                                ${optionsHtml}
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
                                <label><b>${field.key}</b>${field.required ? '<span class="require">*</span>' : ''}</label>
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