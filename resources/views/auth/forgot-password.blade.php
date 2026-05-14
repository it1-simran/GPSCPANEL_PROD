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
    <style>
        :root {
            --primary: rgb(118, 207, 28);
            --primary-glow: rgba(118, 207, 28, 0.3);
            --bg: #ffffff;
            --card-bg: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(118, 207, 28, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(118, 207, 28, 0.05) 0%, transparent 40%);
            position: relative;
        }

        /* Subtle grid pattern */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            animation: fadeIn 0.8s ease-out;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 1.25rem;
            text-decoration: none;
            color: var(--text-main);
            letter-spacing: -0.02em;
            transition: all 0.3s;
        }

        .brand:hover {
            opacity: 0.8;
        }

        .brand-dot {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 2px;
            box-shadow: 0 0 10px var(--primary-glow);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-top: 10px;
            margin-bottom: 4px;
            letter-spacing: -0.03em;
            background: linear-gradient(to bottom, #fff 40%, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(118, 207, 28, 0.2);
            text-transform: uppercase;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.9375rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            color: white;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.1);
        }

        .btn-action {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: var(--bg);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 20px var(--primary-glow);
            margin-top: 10px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px var(--primary-glow);
            filter: brightness(1.1);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .invalid-feedback {
            color: #ff4d4d;
            font-size: 0.75rem;
            margin-top: 6px;
            display: block;
        }

        /* OTP Input Styling */
        .otp-input-fields {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 10px;
        }

        .otp__digit {
            width: 45px;
            height: 55px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            transition: all 0.3s;
        }

        .otp__digit:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.1);
        }

        .info-text {
            color: var(--text-muted);
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .info-text span {
            color: var(--primary);
            font-weight: 600;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-card { padding: 28px 20px; border-radius: 16px; margin: 0 12px; }
            .login-header h1 { font-size: 1.6rem; }
            .login-header { margin-bottom: 24px; }
            .form-group { margin-bottom: 16px; }
            .form-control { padding: 12px 14px; font-size: 14px; }
        }
    </style>
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