<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GPS Control Panel')</title>
    <link rel="icon" href="{{ asset('favicon.svg') . '?v=1.2' }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') . '?v=1.2' }}">
</head>
<body>
    @yield('content')
    @stack('styles')
</body>
</html>
