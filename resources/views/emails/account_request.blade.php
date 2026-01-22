<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Create Your Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f6f6f6;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <p>Hello {{ $user }},</p>

        <p>You have been invited to create your account on <strong>{{ config('app.name') }}</strong>.</p>

        <p>Please click the button below to set your password and activate your account:</p>

        <p>
           <p><a href="{{ $link }}" class="button">Create My Account</a></p>
        </p>

        <p>If you did not expect this email, you can safely ignore it.</p>

        <p>Thank you,<br>{{ config('app.name') }}</p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>