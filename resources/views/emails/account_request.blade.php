<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: #007bff; color: white; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 30px; color: #374151; line-height: 1.6; }
        .content p { margin-bottom: 20px; font-size: 16px; }
        .action-area { text-align: center; margin: 30px 0; }
        .btn { background: #198754; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; transition: background 0.3s; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb; }
        .note { font-size: 12px; color: #9ca3af; margin-top: 10px; }
        .validity-timer { 
            background: #fff8e1; 
            border: 1px solid #ffe082; 
            color: #856404; 
            padding: 10px; 
            border-radius: 4px; 
            margin-top: 20px; 
            font-size: 14px; 
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $user }}</strong>,</p>
            
            <p>You have been invited to create your account on <strong>{{ config('app.name') }}</strong>. Please complete your registration by clicking the button below:</p>

            <div class="action-area">
                <a href="{{ $link }}" class="btn">Create My Account</a>
            </div>

            <div class="validity-timer">
                <i class="fa fa-clock-o"></i> This link is valid for <strong>12 hours</strong>.
            </div>

            <p>If you did not expect this email, you can safely ignore it.</p>
        </div>
        <div class="footer">
            <p>Regards,<br><strong>Admin Team - {{ config('app.name') }}</strong></p>
            <p class="note">If the button doesn't work, copy and paste this URL: <br>{{ $link }}</p>
        </div>
    </div>
</body>
</html>