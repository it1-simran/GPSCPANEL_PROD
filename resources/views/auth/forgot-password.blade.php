<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPS CPANEL - Forgot Password</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    @include('partials.gps-notifications-assets')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('assets/css/portal/pages/auth-forgot-password.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/auth-forgot-password.css')) }}" />
</head>

<body>
    @include('partials.gps-flash-pull')
    <div class="login-card">
        <div class="login-header">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-dot"></span>
                <span>GPS CPANEL</span>
            </a>
            <h1>Reset Password</h1>
            <p id="header-desc">Recover your account access</p>
        </div>

        <!-- STEP 1: Email Form -->
        <div class="forgotPasswordForm">
            <form method="POST" name="forgotPassword" id="forgotPassword" onsubmit="return false">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" class="form-input" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
                    @if ($errors->has('email'))
                    <span class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </span>
                    @endif
                </div>
                <button type="submit" name="submit" id="submit" class="btn-action">
                    Get Verification Code
                </button>
            </form>
            <a href="javascript:void(0);" onclick="window.history.back();" class="back-link">← Back</a>
        </div>

        <!-- STEP 2: OTP Verification -->
        <div class="otpCheckForm" style="display: none;">
            <p class="info-text">An OTP has been sent to <span id="getSendMail"></span></p>
            <form method="POST" name="verifyCode" id="verifyCode" onsubmit="return false">
                @csrf
                <div class="form-group">
                    <label>Enter 6-Digit Code</label>
                    <div class="otp-input-fields">
                        <input type="text" name="otp[]" class="otp__digit otp__field__1" maxlength="1" required autofocus>
                        <input type="text" name="otp[]" class="otp__digit otp__field__2" maxlength="1" required>
                        <input type="text" name="otp[]" class="otp__digit otp__field__3" maxlength="1" required>
                        <input type="text" name="otp[]" class="otp__digit otp__field__4" maxlength="1" required>
                        <input type="text" name="otp[]" class="otp__digit otp__field__5" maxlength="1" required>
                        <input type="text" name="otp[]" class="otp__digit otp__field__6" maxlength="1" required>
                    </div>
                </div>
                <input type='hidden' name='verifyEmail' id='verifyEmail' value='' />
                <button type="submit" class="btn-action">
                    Verify Code
                </button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <p style="font-size: 0.8125rem; color: var(--text-muted);">Didn't get code? <a href="javascript:void(0);" id="resendOTP" style="color: var(--primary); text-decoration: none; font-weight: 600;">Resend</a></p>
                <p id="resendMessage" style="font-size: 0.75rem; color: var(--primary); margin-top: 5px; display: none;"></p>
            </div>
            <a href="javascript:void(0);" onclick="location.reload();" class="back-link">← Change Email</a>
        </div>

        <!-- STEP 3: Set New Password -->
        <div class="setpassword" style="display: none;">
            <form method="POST" name="setNewPassword" id="setNewPassword" onsubmit="return false">
                @csrf
                <span class="invalid-feedback errorsText" style="text-align: center; margin-bottom: 15px;"></span>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input id="newPassword" type="password" class="form-input" name="newPassword" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="confirmNewPassword">Confirm Password</label>
                    <input id="confirmNewPassword" type="password" class="form-input" name="confirmNewPassword" required placeholder="••••••••">
                </div>
                <input type='hidden' name='userId' id='userId' value='' />
                <button type="submit" class="btn-action">
                    Update Password
                </button>
            </form>
            <a href="javascript:void(0);" onclick="location.reload();" class="back-link">← Cancel</a>
        </div>
    </div>

    <script src="{{ asset('assets/js/global-plugins.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    @include('partials.gps-flash-scripts')
    <script type="text/javascript">
        $(document).ready(function() {  
            $('#forgotPassword').submit(function() {
                var formData = $(this).serialize();
                var btn = $(this).find('button');
                btn.prop('disabled', true).text('Sending...');
                
                $.ajax({
                    url: "{{ route('send.otp') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        let result = JSON.parse(response);
                        if (result.status == 200) {
                            $('.forgotPasswordForm').hide();
                            $('.otpCheckForm').show();
                            $('#header-desc').text('Verify your identity');
                            $('#verifyEmail').val(result.email);
                            var email = result.email;
                            var hiddenEmail = email.charAt(0) + "********" + email.slice(email.indexOf("@"));
                            $('#getSendMail').text(hiddenEmail);
                        } else {
                            showGpsToast('error', 'Error', result.message);
                            btn.prop('disabled', false).text('Get Verification Code');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Get Verification Code');
                        let errorMsg = "Something went wrong. Please try again.";
                        try {
                            let res = JSON.parse(xhr.responseText);
                            if (res.message) errorMsg = res.message;
                        } catch(e) {}
                        showGpsToast('error', 'Error', errorMsg);
                    }
                });
            });

            $('#resendOTP').click(function() {
                var email = $('#verifyEmail').val();
                var $link = $(this);
                var $msg = $('#resendMessage');

                if ($link.hasClass('disabled')) return;

                $link.addClass('disabled').css('opacity', '0.5');
                $msg.text('Resending...').fadeIn();

                $.ajax({
                    url: "{{ route('send.otp') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        email: email
                    },
                    success: function(response) {
                        let result = JSON.parse(response);
                        if (result.status == 200) {
                            $msg.text('OTP has been resent successfully!');
                            setTimeout(function() {
                                $msg.fadeOut();
                                $link.removeClass('disabled').css('opacity', '1');
                            }, 5000);
                        } else {
                            showGpsToast('error', 'Error', result.message);
                            $link.removeClass('disabled').css('opacity', '1');
                            $msg.fadeOut();
                        }
                    },
                    error: function(xhr) {
                        $link.removeClass('disabled').css('opacity', '1');
                        $msg.fadeOut();
                        let errorMsg = "Something went wrong. Please try again.";
                        try {
                            let res = JSON.parse(xhr.responseText);
                            if (res.message) errorMsg = res.message;
                        } catch(e) {}
                        showGpsToast('error', 'Error', errorMsg);
                    }
                });
            });

            $('#verifyCode').submit(function() {
                var formData = $(this).serialize();
                var btn = $(this).find('button');
                btn.prop('disabled', true).text('Verifying...');

                $.ajax({
                    url: "{{ route('verify.otp') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        let result = JSON.parse(response);
                        if (result.status == 200) {
                            $('.otpCheckForm').hide();
                            $('.setpassword').show();
                            $('#header-desc').text('Secure your account');
                            $('#userId').val(result.id);
                        } else {
                            showGpsToast('error', 'Error', result.message);
                            btn.prop('disabled', false).text('Verify Code');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Verify Code');
                        let errorMsg = "Something went wrong. Please try again.";
                        try {
                            let res = JSON.parse(xhr.responseText);
                            if (res.message) errorMsg = res.message;
                        } catch(e) {}
                        showGpsToast('error', 'Error', errorMsg);
                    }
                });
            });

            $('#setNewPassword').submit(function() {
                let password = $('#newPassword').val();
                let confirmPassword = $('#confirmNewPassword').val();
                
                if(password.length < 4){
                    $('.errorsText').text("Password should be at least 4 characters long");
                    return;
                }
                if(password !== confirmPassword){
                    $('.errorsText').text("Passwords do not match");
                    return;
                }

                var formData = $(this).serialize();
                var btn = $(this).find('button');
                btn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: "{{ route('reset.password') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        let result = JSON.parse(response);
                        if (result.status == 200) {
                            showGpsToast('success', 'Password updated', 'Redirecting to sign in…', { durationMs: 2200 });
                            setTimeout(function () {
                                window.location.href = "{{ route('login') }}";
                            }, 1600);
                        } else {
                            showGpsToast('error', 'Error', result.message);
                            btn.prop('disabled', false).text('Update Password');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Update Password');
                        let errorMsg = "Something went wrong. Please try again.";
                        try {
                            let res = JSON.parse(xhr.responseText);
                            if (res.message) errorMsg = res.message;
                        } catch(e) {}
                        showGpsToast('error', 'Error', errorMsg);
                    }
                });
            });

            // OTP Input Logic
            var otp_inputs = document.querySelectorAll(".otp__digit");
            var mykey = "0123456789".split("");

            otp_inputs.forEach((input) => {
                input.addEventListener("input", handleInput);
                input.addEventListener("paste", handlePaste);
            });

            function handleInput(event) {
                let current = event.target;
                let index = Array.from(current.parentNode.children).indexOf(current) + 1;

                if (event.inputType === 'deleteContentBackward' && current.value === '') {
                    if (index > 1) {
                        let previous = current.previousElementSibling;
                        previous.focus();
                    }
                }

                if (event.inputType === 'insertText') {
                    if (index < 6 && mykey.indexOf(current.value) === -1) {
                        current.value = '';
                    } else if (index < 6 && mykey.indexOf(current.value) !== -1) {
                        let next = current.nextElementSibling;
                        next.focus();
                    }
                }
            }

            function handlePaste(event) {
                event.preventDefault();
                let pasteData = event.clipboardData.getData('text');
                let pasteChars = pasteData.split('');

                let idx = 0;
                otp_inputs.forEach((input) => {
                    if (idx < pasteChars.length) {
                        input.value = pasteChars[idx];
                        idx++;
                    } else {
                        input.value = '';
                    }
                });
            }
        });
    </script>
</body>

</html>