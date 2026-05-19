@php
    $gpsPageFlash = array_filter([
        'success' => session()->pull('success'),
        'error' => session()->pull('error'),
        'warning' => session()->pull('warning'),
        'info' => session()->pull('info'),
        'message' => session()->pull('message'),
    ], function ($v) {
        return $v !== null && $v !== '';
    });

    if (session()->has('status')) {
        $gpsStatusPulled = session()->pull('status');
        if (is_string($gpsStatusPulled) && $gpsStatusPulled !== '') {
            $gpsPageFlash['status'] = $gpsStatusPulled;
        }
    }
@endphp
