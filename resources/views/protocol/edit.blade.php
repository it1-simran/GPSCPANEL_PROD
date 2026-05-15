@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('protocol-edit') }}">
@endpush
@section('content')
@php
    $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
@endphp
<section id="main-content" class="protocol-page protocol-edit-page">
    <section class="wrapper">
        <div class="protocol-breadcrumb-wrap">
            <nav class="protocol-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ route($routePrefix . '.index') }}" class="bc-item">Protocol Management</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Edit Protocol</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2 class="edit-protocol-title">
                            <i class="fa fa-cubes"></i>
                            Edit Protocol
                            <span class="edit-protocol-name-pill">{{ strtoupper($protocol->name) }}</span>
                        </h2>
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

                        <form class="form-horizontal" method="POST" action="{{ route($routePrefix . '.update', $protocol->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-12">Protocol Name <span class="text-danger">*</span></label>
                                <div class="col-md-6 col-sm-12">
                                    <input type="text" name="name" class="form-control" required 
                                        value="{{ old('name', $protocol->name) }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-12">Description (Optional)</label>
                                <div class="col-md-6 col-sm-12">
                                    <textarea name="description" class="form-control" rows="4" 
                                        placeholder="Briefly describe the protocol's purpose...">{{ old('description', $protocol->description) }}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-12"></label>
                                <div class="col-md-6 col-sm-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Update Protocol
                                    </button>
                                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-default" style="margin-top: 10px;">Cancel</a>
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