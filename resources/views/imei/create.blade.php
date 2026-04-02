@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active"><a href="#">Add IMEI Recording</a></li>
                    </ul>
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
                        <form class="form-horizontal" method="POST" action="{{ route('imei-devices.store') }}">
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
                                <div class="col-lg-6"><input type="datetime-local" name="start_at" value="{{ old('start_at', now()->format('Y-m-d\\TH:i')) }}" class="form-control" required></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">End Date &amp; Time *</label>
                                <div class="col-lg-6"><input type="datetime-local" name="end_at" value="{{ old('end_at', now()->addDays(7)->format('Y-m-d\\TH:i')) }}" class="form-control" required></div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a href="{{ route('imei-devices.index') }}" class="btn btn-default">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
@endsection
