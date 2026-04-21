@extends('layouts.apps')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .btn-premium {
        padding: 8px 30px;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        font-size: 13px;
        border: none;
    }
    
    .btn-premium-success {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white !important;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
    }
    
    .btn-premium-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.3);
        filter: brightness(1.1);
    }
    
    .btn-premium-cancel {
        background: #f8f9fa;
        color: #636e72 !important;
        border: 1px solid #dfe6e9;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        margin-left: 10px;
        text-decoration: none !important;
        display: inline-block;
    }
    
    .btn-premium-cancel:hover {
        background: #ebedef;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    }
</style>
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active"><a href="#">Edit IMEI Recording</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title"><h2>Edit IMEI Recording</h2><div class="clearfix"></div></div>
                    <div class="c_content">
                        @if ($errors->any())
                            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        @php
                            $routePrefix = auth()->check() && auth()->user()->user_type === 'Support' ? 'support.' : '';
                        @endphp
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
                                    <a href="{{ route($routePrefix . 'imei-devices.index') }}" class="btn btn-premium btn-premium-cancel">Cancel</a>
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
