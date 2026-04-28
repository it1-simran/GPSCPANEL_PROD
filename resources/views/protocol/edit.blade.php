@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        @php
                            $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
                        @endphp
                        <li><a href="{{ route($routePrefix . '.index') }}">Protocol Management</a></li>
                        <li class="active"><a href="#">Edit Protocol</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Edit Protocol: <span>{{ $protocol->name }}</span></h2>
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