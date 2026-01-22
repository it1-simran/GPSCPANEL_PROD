<!DOCTYPE html>
<html>
<head>
    <title>Request Rejected</title>
</head>
<body>
    <p>Dear {{ $user->name }},</p>

    <p>We regret to inform you that your request has been <strong>rejected</strong>.</p>

    <p><strong>Reason:</strong></p>
    <blockquote>{{ $reason }}</blockquote>

    <p>If you have any questions, feel free to contact our support team.</p>

    <p>Regards,<br>Admin Team</p>
</body>
</html>
