<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notice' }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    
    <link rel="stylesheet" href="{{ asset('assets/css/portal/pages/errors-custom-message.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/errors-custom-message.css')) }}" />
</head>
<body>
    <div class="card">
        <h2>{{ $title ?? 'Notice' }}</h2>
        <p>{{ $message ?? 'Please try again later.' }}</p>
        <a href="{{ url('/') }}">Return to Homepage</a>
    </div>
</body>
</html>
