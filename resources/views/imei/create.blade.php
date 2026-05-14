@extends('layouts.apps')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    .imei-create-page {
        font-family: 'Inter', sans-serif;
    }

    #main-content.imei-create-page .wrapper {
        padding-top: 10px !important;
    }

    .imei-create-page .top-page-header {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        padding: 0 !important;
        background: transparent !important;
    }

    .imei-breadcrumb-wrap {
        padding: 14px 0 18px 0;
    }

    .imei-breadcrumb {
        display: inline-flex;
        align-items: center;
        background: #1e293b;
        border-radius: 50px;
        padding: 6px 18px 6px 8px;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
    }

    .imei-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        background: #76CF1C;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .imei-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .imei-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.65);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        white-space: nowrap;
    }

    .imei-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .imei-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .imei-create-page .c_content {
        padding-top: 20px !important;
    }

    .imei-create-page .form-group {
        margin-bottom: 16px;
    }

    .imei-create-page .control-label {
        font-weight: 700;
        color: #1f2937;
        padding-top: 12px;
    }

    .imei-create-page .form-control {
        height: 44px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: none;
        padding: 10px 12px;
        font-size: 13px;
        color: #1f2937;
    }

    .imei-create-page .form-control:focus {
        border-color: #76CF1C;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.12);
    }

    .imei-create-page .btn {
        border-radius: 8px;
        font-weight: 700;
        font-size: 12px;
        padding: 9px 18px;
    }

    .imei-create-page .btn-success {
        background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
        border: none !important;
        color: #1e293b !important;
    }
</style>

<section id="main-content" class="imei-create-page">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="imei-breadcrumb-wrap">
                <nav class="imei-breadcrumb">
                    <div class="bc-home"><i class="fa fa-home"></i></div>
                    <a href="{{ url('admin') }}" class="bc-item">Home</a>
                    <span class="bc-sep">›</span>
                    <a href="{{ route((auth()->check() && strtolower(auth()->user()->user_type) === 'support') ? 'support.imei-devices.index' : 'imei-devices.index') }}" class="bc-item">Manage Trackers</a>
                    <span class="bc-sep">›</span>
                    <span class="bc-item active">Add IMEI Recording</span>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title"><h2>Add IMEI Recording</h2><div class="clearfix"></div></div>
                    <div class="c_content">
                        @if ($errors->any())
                            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        @php
                            $routePrefix = auth()->check() && auth()->user()->user_type === 'Support' ? 'support.' : '';
                        @endphp
                        <form class="form-horizontal" method="POST" action="{{ route($routePrefix . 'imei-devices.store') }}">
                            @csrf
                            <div class="form-group">
                                <label class="control-label col-lg-3">IMEI *</label>
                                <div class="col-lg-6"><input type="text" name="imei" maxlength="15" value="{{ old('imei') }}" class="form-control" required></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Status *</label>
                                <div class="col-lg-6">
                                    <select name="status" class="form-control" required>
                                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>ON</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>OFF</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Start Date &amp; Time *</label>
                                <div class="col-lg-6"><input type="text" name="start_at" value="{{ old('start_at', \App\Helper\CommonHelper::getDateAsTimeZone(now(), 'Y-m-d\\TH:i')) }}" class="form-control flatpickr-datetime" required></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">End Date &amp; Time *</label>
                                <div class="col-lg-6"><input type="text" name="end_at" value="{{ old('end_at', \App\Helper\CommonHelper::getDateAsTimeZone(now()->addDays(7), 'Y-m-d\\TH:i')) }}" class="form-control flatpickr-datetime" required></div>
                            </div>

                            <!-- <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a href="{{ route('imei-devices.index') }}" class="btn btn-default">Cancel</a>
                                </div>
                            </div> -->
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                                    <button type="button" 
                                            onclick="window.location='{{ route($routePrefix . 'imei-devices.index') }}'" 
                                            class="btn btn-default">
                                        <i class="fa fa-times"></i> Cancel
                                    </button>
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
