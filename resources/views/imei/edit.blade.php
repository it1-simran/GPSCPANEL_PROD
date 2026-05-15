@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('imei-edit') }}">
@endpush
@section('content')
@php
    $routePrefix = auth()->check() && auth()->user()->user_type === 'Support' ? 'support.' : '';
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<section id="main-content" class="imei-page imei-edit-page">
    <section class="wrapper">
        <div class="imei-breadcrumb-wrap">
            <nav class="imei-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ route($routePrefix . 'imei-devices.index') }}" class="bc-item">Manage Trackers</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Edit IMEI Recording</span>
            </nav>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title imei-edit-c-title">
                        <h2 class="imei-edit-title">
                            <i class="fa fa-list"></i>
                            Edit IMEI Recording
                            <span class="imei-edit-pill">{{ $imei_device->imei }}</span>
                        </h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="c_content">
                        @if ($errors->any())
                            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        <form class="form-horizontal" method="POST" action="{{ route($routePrefix . 'imei-devices.update', $imei_device->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="control-label col-lg-3">IMEI *</label>
                                <div class="col-lg-6"><input type="text" name="imei" maxlength="15" value="{{ old('imei', $imei_device->imei) }}" class="form-control" required></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Status *</label>
                                <div class="col-lg-6">
                                    <select name="status" class="form-control" required>
                                        <option value="active" {{ old('status', $imei_device->status) === 'active' ? 'selected' : '' }}>ON</option>
                                        <option value="inactive" {{ old('status', $imei_device->status) === 'inactive' ? 'selected' : '' }}>OFF</option>

                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Start Date &amp; Time *</label>
                                <div class="col-lg-6"><input type="text" name="start_at" value="{{ old('start_at', $imei_device->effective_start_at ? \App\Helper\CommonHelper::getDateAsTimeZone($imei_device->effective_start_at, 'Y-m-d\\TH:i') : '') }}" class="form-control flatpickr-datetime" required></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">End Date &amp; Time *</label>
                                <div class="col-lg-6"><input type="text" name="end_at" value="{{ old('end_at', $imei_device->effective_end_at ? \App\Helper\CommonHelper::getDateAsTimeZone($imei_device->effective_end_at, 'Y-m-d\\TH:i') : '') }}" class="form-control flatpickr-datetime" required></div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-premium btn-premium-success">Update</button>
                                    <a href="{{ route($routePrefix . 'imei-devices.index') }}" style="margin-top: 10px;" class="btn btn-premium btn-premium-cancel">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr-datetime', {
        enableTime: true,
        dateFormat: "Y-m-d\\TH:i",
        altInput: true,
        altFormat: "Y-m-d H:i",
        time_24hr: true
    });
});
</script>
@endsection
