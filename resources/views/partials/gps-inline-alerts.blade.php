{{-- Inline flash + validation alerts (uses $gpsPageFlash from layout / View composer). --}}
@php
    $gpsFlashAlerts = $gpsPageFlash ?? [];
    $gpsValidationList = $gpsFlashAlerts['validation_errors'] ?? [];
@endphp

@if (!empty($gpsFlashAlerts['success']))
    <div class="col-sm-12 alert alert-success gps-inline-alert" role="alert">
        {{ $gpsFlashAlerts['success'] }}
    </div>
@endif

@if (!empty($gpsFlashAlerts['error']))
    <div class="col-sm-12 alert alert-danger gps-inline-alert" role="alert">
        {{ $gpsFlashAlerts['error'] }}
    </div>
@endif

@if (!empty($gpsFlashAlerts['warning']))
    <div class="col-sm-12 alert alert-warning gps-inline-alert" role="alert">
        {{ $gpsFlashAlerts['warning'] }}
    </div>
@endif

@if (!empty($gpsFlashAlerts['info']))
    <div class="col-sm-12 alert alert-info gps-inline-alert" role="alert">
        {{ $gpsFlashAlerts['info'] }}
    </div>
@endif

@if (!empty($gpsFlashAlerts['message']))
    <div class="col-sm-12 alert alert-info gps-inline-alert" role="alert">
        {{ $gpsFlashAlerts['message'] }}
    </div>
@endif

@if (!empty($gpsFlashAlerts['status']))
    <div class="col-sm-12 alert alert-info gps-inline-alert" role="alert">
        {{ $gpsFlashAlerts['status'] }}
    </div>
@endif

@if (!empty($gpsValidationList))
    <div class="col-sm-12 alert alert-danger gps-inline-alert" role="alert">
        <ul class="gps-inline-alert__list mb-0">
            @foreach ($gpsValidationList as $validationMessage)
                <li>{{ $validationMessage }}</li>
            @endforeach
        </ul>
    </div>
@endif
