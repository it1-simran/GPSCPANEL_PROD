@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active"><a href="#">Edit Tracker</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Edit IMEI Tracker</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="c_content">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="form-horizontal" method="POST" action="{{ route('imei-devices.update', $imei_device->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="control-label col-lg-3">IMEI (15 digits) *</label>
                                <div class="col-lg-6">
                                    <input type="text" name="imei" class="form-control" value="{{ $imei_device->imei }}" required maxlength="15">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Status *</label>
                                <div class="col-lg-6">
                                    <select name="status" class="form-control" required>
                                        <option value="active" {{ $imei_device->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $imei_device->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="close" {{ $imei_device->status == 'close' ? 'selected' : '' }}>Close</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Schedule Start (Optional)</label>
                                <div class="col-lg-6">
                                    <input type="datetime-local" name="schedule_start" class="form-control" value="{{ $imei_device->schedule_start ? $imei_device->schedule_start->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Schedule End (Optional)</label>
                                <div class="col-lg-6">
                                    <input type="datetime-local" name="schedule_end" class="form-control" value="{{ $imei_device->schedule_end ? $imei_device->schedule_end->format('Y-m-d\TH:i') : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-success">Update</button>
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
