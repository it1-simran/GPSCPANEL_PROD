<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    
    <link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('errors-link-expired') }}" />
</head>
<body>
    <div class="container">
        <h2>{{ $message ?? 'This link has expired or is invalid.' }}</h2>
        <p>Please request a new link to continue.</p>
    </div>
</body>
</html>

