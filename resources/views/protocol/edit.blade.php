@extends('layouts.apps')
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
<style>
    .protocol-edit-page .wrapper {
        padding-top: 8px !important;
    }

    .protocol-edit-page .protocol-breadcrumb-wrap {
        padding: 4px 0 12px 0 !important;
        margin: 0 !important;
    }

    .protocol-edit-page .protocol-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        background: #1e293b !important;
        border-radius: 50px !important;
        padding: 6px 18px 6px 8px !important;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18) !important;
    }

    .protocol-edit-page .protocol-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #76CF1C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .protocol-edit-page .protocol-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .protocol-edit-page .protocol-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }

    .protocol-edit-page .protocol-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .protocol-edit-page .protocol-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .protocol-edit-page .c_title h2::before {
        content: none !important;
        display: none !important;
    }

    .protocol-edit-page .c_title {
        margin-top: 4px !important;
    }

    .protocol-edit-page .edit-protocol-title {
        display: inline-flex !important;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        color: #ffffff !important;
        font-size: 19px !important;
        font-weight: 800 !important;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }

    .protocol-edit-page .edit-protocol-title > i {
        color: #76CF1C;
        font-size: 15px;
        width: 22px;
        text-align: center;
    }

    /* Same pill as Packet Types "HTTP" reference */
    .protocol-edit-page .edit-protocol-name-pill {
        display: inline-flex;
        align-items: center;
        margin-left: 4px;
        padding: 4px 11px;
        border-radius: 999px;
        background: rgba(118, 207, 28, 0.16);
        color: #cfff9f !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        border: 1px solid rgba(118, 207, 28, 0.36);
    }
</style>
@endsection