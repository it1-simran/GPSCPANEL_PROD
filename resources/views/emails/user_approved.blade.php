<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; padding: 40px 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 40px; color: #374151; line-height: 1.8; }
        .content p { margin-bottom: 25px; font-size: 16px; }
        .credentials-box { background: #f0fdf4; border: 1px solid #dcfce7; padding: 25px; margin: 30px 0; border-radius: 12px; position: relative; }
        .credentials-label { font-weight: bold; color: #166534; display: block; margin-bottom: 15px; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; border-bottom: 1px solid #dcfce7; padding-bottom: 10px; }
        .credential-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .credential-key { color: #6b7280; font-weight: 500; }
        .credential-value { color: #111827; font-weight: 700; font-family: 'Courier New', Courier, monospace; background: #e8f5e9; padding: 2px 8px; border-radius: 4px; }
        .action-area { text-align: center; margin: 40px 0; }
        .btn { background: #111827; color: #ffffff !important; padding: 14px 35px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; transition: all 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn:hover { background: #1f2937; transform: translateY(-2px); }
        .footer { background: #f9fafb; padding: 30px; text-align: center; color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb; }
        .important-note { font-size: 12px; color: #9ca3af; margin-top: 15px; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome Aboard!</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $user->name }}</strong>,</p>
            
            <p>We are pleased to inform you that your registration request has been <strong>approved</strong>. Your account is now active and ready to use.</p>

            <div class="credentials-box">
                <span class="credentials-label">Login Credentials</span>
                <div class="credential-item">
                    <span class="credential-key">Login URL:</span>
                    <span class="credential-value">{{ url('/login') }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-key">Email:</span>
                    <span class="credential-value">{{ $user->email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-key">Default Password:</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <p>For security reasons, we strongly recommend that you change your password immediately after your first login.</p>

            <div class="action-area">
                <a href="{{ url('/login') }}" class="btn">Login to Your Account</a>
            </div>

            <p>If you have any questions or encounter any issues, please feel free to contact our support team.</p>
        </div>
        <div class="footer">
            <p>Best Regards,<br><strong>Admin Team - GPS Cpanel</strong></p>
            <p class="important-note">This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
