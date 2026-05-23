@if(isset($error) && $error == 403)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link rel="icon" href="{{ asset('favicon.svg') . '?v=1.2' }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') . '?v=1.2' }}">
    
    <link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('unauthorized-access') }}" />
</head>
<body>
    <div class="container">
        <h1>Unauthorized Access</h1>
        <p>You do not have permission to access this Resource.</p>
        <p>Please contact your administrator for assistance or <a href="/">return to the homepage</a>.</p>
    </div>
</body>
</html>
@php return; @endphp
@endif
