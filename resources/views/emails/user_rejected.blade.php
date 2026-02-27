<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Rejected</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: #e11d48; color: white; padding: 25px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 30px; color: #374151; line-height: 1.6; }
        .content p { margin-bottom: 20px; font-size: 16px; }
        .reason-box { background: #fee2e2; border-left: 4px solid #e11d48; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .reason-label { font-weight: bold; color: #991b1b; display: block; margin-bottom: 5px; text-transform: uppercase; font-size: 12px; }
        .reason-text { color: #b91c1c; font-style: italic; }
        .action-area { text-align: center; margin: 30px 0; }
        .btn { background: #111827; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; transition: background 0.3s; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb; }
        .note { font-size: 12px; color: #9ca3af; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Request Update</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{{ $user->name }}</strong>,</p>
            
            <p>Thank you for submitting your account registration request. After reviewing your application, we regret to inform you that it has been <strong>rejected</strong> at this time.</p>

            <div class="reason-box">
                <span class="reason-label">Rejection Reason:</span>
                <span class="reason-text">"{{ $reason }}"</span>
            </div>

            <p>However, we would like to give you the opportunity to correct the information and resubmit your request. You can revisit the registration form using the button below:</p>

            <div class="action-area">
                <a href="{{ $link }}" class="btn">Resubmit Registration</a>
            </div>

            <p>Please ensure all details are accurate according to the feedback provided above.</p>
        </div>
        <div class="footer">
            <p>Regards,<br><strong>Admin Team - GPS Cpanel</strong></p>
            <p class="note">This link will expire in 24 hours. If the button doesn't work, copy and paste this URL into your browser: <br>{{ $link }}</p>
        </div>
    </div>
</body>
</html>
