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
            @if ($errors->any())
              <div class="row">
                <div class="col-sm-12 alert alert-danger" role="alert">
                  {{ $errors->first() }}
                </div>
              </div>
            @endif
            @if($saved && empty($edit_mode))
              <div class="row">
                <!-- <div class="col-lg-12 text-right margin-bottom-10">
                  <a href="/user/device/{{ $device->id }}/certificate?edit=1" class="btn btn-default">Edit Details</a>
                </div> -->
                <div class="col-md-12" style="height:80vh;">
                  <iframe src="/user/device/{{ $device->id }}/certificate/view" style="width:100%;height:100%;border:1px solid #ccc;"></iframe>
                </div>
              </div>
            @else
              <div class="row">
                <div class="col-md-12">
                  @php
                    $formData = is_array($saved) ? $saved : [];
                  @endphp
                  <form class="validator form-horizontal" id="certificate-details-form" method="post" action="/user/device/{{ $device->id }}/certificate/save">
                    @csrf
                    <div class="form-group">
                      <label class="control-label col-lg-3">Certificate Holder Name & Address<span class="require">*</span></label>
                      <div class="col-lg-6">
                        <textarea class="form-control" name="holder_name" required>{{ old('holder_name', $formData['holder_name'] ?? '') }}</textarea>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Authority City <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="authority_city" value="{{ old('authority_city', $formData['authority_city'] ?? '') }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Fitment Date <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="date" name="fitment_date_display" value="{{ date('Y-m-d') }}" disabled="disabled" readonly="readonly" />
                        <input type="hidden" name="fitment_date" value="{{ date('Y-m-d') }}" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Vehicle Registration No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vehicle_registration_no" value="{{ old('vehicle_registration_no', $formData['vehicle_registration_no'] ?? '') }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD Serial No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_serial_no" value="{{ old('vltd_serial_no', $formData['vltd_serial_no'] ?? '') }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD Make <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_make" value="{{ old('vltd_make', 'JSD Electronics India Pvt Ltd') }}" required readonly />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD Model <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_model" value="{{ old('vltd_model', $formData['vltd_model'] ?? ($vltd_model ?? $category_name)) }}" required readonly />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">VLTD ICCID</label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vltd_icc_id" value="{{ old('vltd_icc_id', $formData['vltd_icc_id'] ?? ($vltd_icc_id ?? '')) }}" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Chassis No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="chassis_no" value="{{ old('chassis_no', $formData['chassis_no'] ?? '') }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Engine No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="engine_no" value="{{ old('engine_no', $formData['engine_no'] ?? '') }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Color <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="color" value="{{ old('color', $formData['color'] ?? '') }}" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Vehicle Model <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vehicle_model" value="{{ old('vehicle_model', $formData['vehicle_model'] ?? '') }}" required />
                      </div>
                    </div>
                    @if(!empty($is_certification_enable))
                    <div class="form-group">
                      <label class="control-label col-lg-3">ARAI TAC/COP No <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="arai_tac" value="{{ $arai_tac }}" required readonly />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">ARAI Date <span class="require">*</span></label>
                      <div class="col-lg-6">
                        <input class="form-control" type="date" name="arai_date" value="{{ $arai_date }}" required readonly />
                      </div>
                    </div>
                    @endif
                    <div class="form-group">
                      <label class="control-label col-lg-3">Service Provider <span class="require">*</span></label>
                      <div class="col-lg-6">
                        @php
                          $savedProvider = $formData['service_provider'] ?? null;
                          if (!$savedProvider && isset($formData['service_providers'])) {
                            if (is_array($formData['service_providers'])) {
                              $savedProvider = $formData['service_providers'][0] ?? null;
                            } else {
                              $savedProvider = $formData['service_providers'];
                            }
                          }
                          $selectedProvider = old('service_provider', $savedProvider);
                        @endphp
                        <select name="service_provider" id="serviceProvidersSelect" required>
                          @php
                            $savedProviders = $selectedProvider ? [$selectedProvider] : [];
                          @endphp
                          <option value="Taisys" {{ in_array('Taisys', $savedProviders) ? 'selected' : '' }}>Taisys</option>
                          <option value="Growspace" {{ in_array('Growspace', $savedProviders) ? 'selected' : '' }}>Growspace</option>
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
