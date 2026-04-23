@extends('layouts.apps')
@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="{{ route('protocols.index') }}">Protocol Management</a></li>
                        <li><a href="{{ route('protocols.packet-types', $protocol->id) }}">Packet Types</a></li>
                        <li class="active"><a href="#">Add Packet Type</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title"><h2>Add Packet Type for {{ $protocol->name }}</h2><div class="clearfix"></div></div>
                    <div class="c_content">
                        <form class="form-horizontal" method="POST" action="{{ route('protocols.packet-types.store', $protocol->id) }}">
                            @csrf
                            <div class="form-group">
                                <label class="control-label col-lg-3">Packet Name *</label>
                                <div class="col-lg-6">
                                    <input type="text" name="name" class="form-control" required placeholder="e.g. Login Packet">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Header Identifier</label>
                                <div class="col-lg-6">
                                    <input type="text" name="header_identifier" class="form-control" placeholder="e.g. $NMP">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-lg-3">Packet Delimiter</label>
                                <div class="col-lg-6">
                                    <input type="text" name="delimiter" class="form-control" placeholder="e.g. , for CSV">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <a href="{{ route('protocols.packet-types', $protocol->id) }}" class="btn btn-default">Cancel</a>
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
