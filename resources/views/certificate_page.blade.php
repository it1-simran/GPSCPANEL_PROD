@extends('layouts.apps')
@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Certificate</a></li>
            <li class="active"><a href="#">VLTD Fitment Certificate</a></li>
          </ul>
        </nav>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2>Certificate</h2>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="c_content">
            @if($saved)
              <div class="row">
                <div class="col-lg-12 text-right margin-bottom-10">
                  <a href="/user/device/{{ $device->id }}/certificate" class="btn btn-default">Edit Details</a>
                </div>
                <div class="col-md-12" style="height:80vh;">
                  <iframe src="/user/device/{{ $device->id }}/certificate/view" style="width:100%;height:100%;border:1px solid #ccc;"></iframe>
                </div>
              </div>
            @else
              <div class="row">
                <div class="col-md-12">
                  <form class="validator form-horizontal" id="certificate-details-form" method="post" action="/user/device/{{ $device->id }}/certificate/save">
                    @csrf
                    <div class="form-group">
                      <label class="control-label col-lg-3">Certificate Holder Name <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="holder_name" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Authority City <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="authority_city" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Fitment Date <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="date" name="fitment_date" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Vehicle Registration No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vehicle_registration_no" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD Serial No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_serial_no" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD Make <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_make" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD Model <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_model" value="{{ $category_name }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Chassis No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="chassis_no" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Engine No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="engine_no" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Color <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="color" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Vehicle Model <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vehicle_model" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">ARAI TAC/COP No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="arai_tac" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">ARAI Date <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="date" name="arai_date" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Service Providers <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <select class="form-control" name="service_providers[]" multiple id="serviceProvidersSelect">
                          <option value="Taisys">Taisys</option>
                          <option value="ProviderA">ProviderA</option>
                          <option value="ProviderB">ProviderB</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-lg-12 text-right">
                        <button class="btn btn-primary btn-flat" type="submit">Save & View</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script>
  $(document).ready(function() {
    $('#serviceProvidersSelect').select2({
      placeholder: 'Select providers',
      allowClear: true,
      width: '100%'
    });
  });
</script>
