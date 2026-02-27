<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Verification</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background: #0bb2d4; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 40px; color: #374151; text-align: center; }
        .otp-box { 
            background: #f1f5f9; 
            border: 2px dashed #0bb2d4; 
            padding: 20px; 
            margin: 25px 0; 
            border-radius: 8px;
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 8px;
        }
        .timer { color: #856404; font-weight: 600; font-size: 14px; margin-bottom: 20px; background: #fff8e1; display: inline-block; padding: 5px 15px; border-radius: 20px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Verification</h1>
        </div>
        <div class="content">
            <p style="margin: 0; font-size: 16px;">Hello <strong>{{ $user_name }}</strong>,</p>
            <p style="margin-top: 10px; color: #64748b;">Thank you for registering. Please use the verification code below to complete your registration process.</p>

            <div class="otp-box">
                {{ $otp }}
            </div>

            <div class="timer">
                ⏱ This code is valid for 10 minutes
            </div>

            <p style="font-size: 13px; color: #94a3b8; margin: 0;">If you did not initiate this registration, please ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
