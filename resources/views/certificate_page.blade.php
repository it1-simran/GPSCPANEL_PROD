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
                <h2><i class="fa fa-certificate" style="color:#76CF1C;margin-right:10px;"></i>Certificate Details</h2>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="c_content">
            @if ($errors->any())
              <div class="row">
                <div class="col-sm-12">
                  <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <strong>Error!</strong> {{ $errors->first() }}
                  </div>
                </div>
              </div>
            @endif
            @if($saved && empty($edit_mode))
              <div class="row">
                <div class="col-md-12" style="height:80vh;">
                  <iframe src="/user/device/{{ $device->id }}/certificate/view" style="width:100%;height:100%;border:1px solid #ccc;border-radius:4px;"></iframe>
                </div>
              </div>
            @else
              <div class="row">
                <div class="col-md-12">
                  @php
                    $formData = is_array($saved) ? $saved : [];
                  @endphp
                  <div class="alert alert-success alert-dismissible" role="alert" id="rc-upload-info" style="display:none;">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <i class="fa fa-check-circle"></i> <strong>Success!</strong> RC details have been extracted and auto-populated. Please review and edit if needed.
                  </div>

                  <!-- RC Upload Card -->
                  <div class="rc-upload-card" style="background: linear-gradient(135deg, #76CF1C15 0%, #76CF1C08 100%); border: 2px dashed #76CF1C; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
                    <div class="row">
                      <div class="col-md-12">
                        <h4 style="color:#333; margin-bottom:15px; font-weight:600;">
                          <i class="fa fa-file-pdf-o" style="color:#76CF1C;margin-right:8px;"></i>Upload Registration Certificate (RC)
                        </h4>
                        <p style="color:#666; font-size:13px; margin-bottom:15px;">Automatically extract vehicle details from your RC document. Supported formats: PDF, JPG, PNG, BMP, GIF (Max 5MB)</p>

                        <div class="file-upload-wrapper">
                          <div class="form-group" style="margin-bottom:0;">
                            <input type="file" id="rc_file" accept=".pdf,.jpg,.jpeg,.png,.bmp,.gif" class="form-control" style="padding:10px; border: 1px solid #ddd; cursor:pointer;" />
                          </div>
                        </div>

                        <button class="btn btn-success" type="button" id="upload-rc-btn" style="margin-top:10px; background-color:#76CF1C; border-color:#76CF1C; color:#fff; padding:10px 30px; font-weight:500;">
                          <i class="fa fa-upload"></i> Upload & Extract Details
                        </button>

                        <div id="rc-upload-progress" style="display:none; margin-top:15px;">
                          <p style="font-size:12px; color:#666; margin-bottom:8px;">Processing RC document...</p>
                          <div class="progress" style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%; background-color:#76CF1C; border-radius:3px;"></div>
                          </div>
                        </div>
                        <div id="rc-upload-error" style="display:none; margin-top:15px;">
                          <div class="alert alert-danger alert-dismissible" style="margin:0;">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-exclamation-circle"></i> <span id="error-message"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <form class="validator form-horizontal" id="certificate-details-form" method="post" action="/user/device/{{ $device->id }}/certificate/save">
                    @csrf
                    <!-- Certificate Holder Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:20px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-user" style="color:#76CF1C;margin-right:8px;"></i>Holder Information
                      </h4>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label col-lg-3" style="font-weight:500; color:#333;">Certificate Holder Name & Address <span class="require" style="color:#d32f2f;">*</span></label>
                        <div class="col-lg-9">
                          <textarea class="form-control" name="holder_name" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; min-height:80px;">{{ old('holder_name', $formData['holder_name'] ?? '') }}</textarea>
                          <small class="form-text text-muted">Enter name and complete address of certificate holder</small>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label col-lg-3" style="font-weight:500; color:#333;">Authority City <span class="require" style="color:#d32f2f;">*</span></label>
                        <div class="col-lg-9">
                          <input class="form-control" type="text" name="authority_city" placeholder="e.g., Jaipur" value="{{ old('authority_city', $formData['authority_city'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          <small class="form-text text-muted">City of the registering authority</small>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label col-lg-3" style="font-weight:500; color:#333;">Fitment Date <span class="require" style="color:#d32f2f;">*</span></label>
                        <div class="col-lg-9">
                          <input class="form-control" type="date" name="fitment_date_display" value="{{ date('Y-m-d') }}" disabled style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          <input type="hidden" name="fitment_date" value="{{ date('Y-m-d') }}" />
                        </div>
                      </div>
                    </div>

                    <!-- Vehicle Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-car" style="color:#76CF1C;margin-right:8px;"></i>Vehicle Information
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vehicle Registration No <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vehicle_registration_no" placeholder="e.g., RJ18GB8351" value="{{ old('vehicle_registration_no', $formData['vehicle_registration_no'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Chassis No <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="chassis_no" placeholder="Enter chassis number" value="{{ old('chassis_no', $formData['chassis_no'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Engine No <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="engine_no" placeholder="Enter engine number" value="{{ old('engine_no', $formData['engine_no'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Color <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="color" placeholder="e.g., White, Black" value="{{ old('color', $formData['color'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vehicle Model <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vehicle_model" placeholder="Enter vehicle model" value="{{ old('vehicle_model', $formData['vehicle_model'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vehicle Class</label>
                            <input class="form-control" type="text" name="vehicle_class" placeholder="e.g., Truck, Bus" value="{{ old('vehicle_class', $formData['vehicle_class'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Fuel Type</label>
                        <input class="form-control" type="text" name="fuel_type" placeholder="Petrol/Diesel/CNG/Electric" value="{{ old('fuel_type', $formData['fuel_type'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                      </div>
                    </div>
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
                    <!-- VLTD Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-cogs" style="color:#76CF1C;margin-right:8px;"></i>VLTD Device Information
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">VLTD Serial No <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vltd_serial_no" placeholder="VLTD Serial Number" value="{{ old('vltd_serial_no', $formData['vltd_serial_no'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">VLTD Make <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vltd_make" value="{{ old('vltd_make', 'JSD Electronics India Pvt Ltd') }}" required readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">VLTD Model <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vltd_model" value="{{ old('vltd_model', $formData['vltd_model'] ?? ($vltd_model ?? $category_name)) }}" required readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">VLTD ICCID</label>
                            <input class="form-control" type="text" name="vltd_icc_id" placeholder="ICCID (optional)" value="{{ old('vltd_icc_id', $formData['vltd_icc_id'] ?? ($vltd_icc_id ?? '')) }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
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
                    <div class="form-group">
                      <label class="control-label col-lg-3">Vehicle Class</label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="vehicle_class" value="{{ old('vehicle_class', $formData['vehicle_class'] ?? '') }}" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="control-label col-lg-3">Fuel Type</label>
                      <div class="col-lg-6">
                        <input class="form-control" type="text" name="fuel_type" value="{{ old('fuel_type', $formData['fuel_type'] ?? '') }}" />
                      </div>
                    </div>
                    <!-- Certification & Compliance Section -->
                    @if(!empty($is_certification_enable))
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-shield" style="color:#76CF1C;margin-right:8px;"></i>Certification & Compliance
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">ARAI TAC/COP No <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="arai_tac" value="{{ $arai_tac }}" required readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">ARAI Date <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="date" name="arai_date" value="{{ $arai_date }}" required readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                      </div>
                    </div>
                    @endif

                    <!-- Service Provider Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-building" style="color:#76CF1C;margin-right:8px;"></i>Service Provider
                      </h4>

                      <div class="form-group" style="margin-bottom:30px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Select Service Provider <span class="require" style="color:#d32f2f;">*</span></label>
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
                        <select name="service_provider" id="serviceProvidersSelect" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; width:100%;">
                          @php
                            $savedProviders = $selectedProvider ? [$selectedProvider] : [];
                          @endphp
                          <option value="">-- Select a Provider --</option>
                          <option value="Taisys" {{ in_array('Taisys', $savedProviders) ? 'selected' : '' }}>Taisys</option>
                          <option value="Growspace" {{ in_array('Growspace', $savedProviders) ? 'selected' : '' }}>Growspace</option>
                        </select>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <div class="form-group" style="margin-bottom:0;">
                        <div class="col-lg-12 text-right">
                          <button class="btn btn-default" type="reset" style="margin-right:10px; border-radius:4px; padding:10px 30px;">
                            <i class="fa fa-times"></i> Reset
                          </button>
                          <button class="btn btn-primary" type="submit" style="background-color:#76CF1C; border-color:#76CF1C; color:#fff; border-radius:4px; padding:10px 30px; font-weight:500;">
                            <i class="fa fa-save"></i> Save & View Certificate
                          </button>
                        </div>
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

    // Check RC feature status on page load
    checkRCStatus();

    function checkRCStatus() {
      $.ajax({
        url: '/user/device/{{ $device->id }}/certificate/rc-status',
        method: 'GET',
        success: function(response) {
          if (!response.tesseract_available) {
            showTesseractWarning(response.instructions);
          }
        }
      });
    }

    function showTesseractWarning(instructions) {
      if (instructions.options) {
        // Show multi-option setup instructions
        const googleInstructions = instructions.options.google_vision;
        const tesseractInstructions = instructions.options.tesseract;

        const warningHtml = `
          <div class="alert alert-warning alert-dismissible" role="alert" style="margin-bottom: 25px; border-left:4px solid #ffc107; background-color:#fffbf0; border-radius:4px; padding:15px;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fa fa-info-circle" style="color:#ff9800; margin-right:8px;"></i>
            <strong style="color:#333;">OCR Feature Not Configured</strong>
            <p style="color:#666; margin:10px 0 0 0; font-size:12px;">
              Automatic text extraction requires setup. Choose one of the options below:
            </p>

            <!-- Google Vision Option -->
            <div style="margin:15px 0; padding:12px; background-color:#e8f5e9; border-left:3px solid #4caf50; border-radius:3px;">
              <h5 style="margin:0 0 10px 0; color:#2e7d32; font-size:13px;">
                <i class="fa fa-cloud" style="margin-right:5px;"></i>
                <strong>${googleInstructions.name}</strong> <span style="color:#666; font-size:11px;">(Recommended)</span>
              </h5>
              <p style="color:#555; font-size:11px; margin:5px 0;">
                ${googleInstructions.description}
              </p>
              <p style="color:#666; font-size:11px; margin:5px 0;"><strong>Benefits:</strong></p>
              <ul style="margin:5px 0; padding-left:20px; color:#666; font-size:11px;">
                ${googleInstructions.benefits.map(b => `<li>${b}</li>`).join('')}
              </ul>
              <p style="color:#666; font-size:11px; margin:10px 0 0 0;">
                <strong>Setup:</strong><br>
                ${googleInstructions.steps.map(step => `<div style="margin:3px 0; padding-left:15px;">• ${step}</div>`).join('')}
              </p>
              <a href="https://console.cloud.google.com" target="_blank" style="color:#1976d2; text-decoration:none; font-size:11px;">
                <i class="fa fa-external-link"></i> Go to Google Cloud Console
              </a>
            </div>

            <!-- Tesseract Option -->
            <div style="margin:15px 0; padding:12px; background-color:#f3e5f5; border-left:3px solid #9c27b0; border-radius:3px;">
              <h5 style="margin:0 0 10px 0; color:#6a1b9a; font-size:13px;">
                <i class="fa fa-server" style="margin-right:5px;"></i>
                <strong>${tesseractInstructions.name}</strong> <span style="color:#666; font-size:11px;">(Alternative)</span>
              </h5>
              <p style="color:#666; font-size:11px; margin:5px 0;">
                <strong>Setup steps:</strong><br>
                ${tesseractInstructions.steps.map(step => `<div style="margin:3px 0; padding-left:15px;">• ${step}</div>`).join('')}
              </p>
            </div>

            <!-- Manual Entry Option -->
            <div style="margin:15px 0; padding:12px; background-color:#fce4ec; border-left:3px solid #e91e63; border-radius:3px;">
              <h5 style="margin:0 0 10px 0; color:#880e4f; font-size:13px;">
                <i class="fa fa-keyboard-o" style="margin-right:5px;"></i>
                <strong>Manual Entry</strong>
              </h5>
              <p style="color:#666; font-size:11px; margin:0;">
                You can always enter RC details manually in the form below without any setup required.
              </p>
            </div>
          </div>
        `;

        $('#certificate-details-form').before(warningHtml);
      } else {
        // Legacy single-option warning
        const warningHtml = `
          <div class="alert alert-warning alert-dismissible" role="alert" style="margin-bottom: 25px; border-left:4px solid #ffc107; background-color:#fffbf0; border-radius:4px; padding:15px;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fa fa-info-circle" style="color:#ff9800; margin-right:8px;"></i>
            <strong style="color:#333;">OCR Feature Not Available</strong>
            <p style="color:#666; margin:10px 0 0 0; font-size:12px;">
              You can upload RC documents, but automatic text extraction is not available.
            </p>
            <p style="color:#666; margin:10px 0 0 0; font-size:12px;">
              <strong>For your OS (${instructions.os}):</strong><br>
              ${instructions.steps.map(step => `<div style="margin:5px 0; padding-left:15px;">• ${step}</div>`).join('')}
            </p>
            <p style="color:#666; margin:10px 0 0 0; font-size:12px;">
              <em>Or simply enter RC details manually in the form below.</em>
            </p>
          </div>
        `;

        $('#certificate-details-form').before(warningHtml);
      }
    }

    // RC Upload Handler
    $('#upload-rc-btn').on('click', function() {
      const fileInput = document.getElementById('rc_file');
      if (!fileInput.files || fileInput.files.length === 0) {
        alert('Please select an RC file first');
        return;
      }

      uploadRCDocument(fileInput.files[0]);
    });

    // Allow upload on file selection
    $('#rc_file').on('change', function() {
      if (this.files && this.files.length > 0) {
        uploadRCDocument(this.files[0]);
      }
    });

    function uploadRCDocument(file) {
      const formData = new FormData();
      formData.append('rc_file', file);

      const uploadProgress = $('#rc-upload-progress');
      const uploadError = $('#rc-upload-error');
      const uploadInfo = $('#rc-upload-info');

      uploadProgress.show();
      uploadError.hide();
      uploadInfo.hide();

      $.ajax({
        url: '/user/device/{{ $device->id }}/certificate/upload-rc',
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
        },
        success: function(response) {
          uploadProgress.hide();
          uploadInfo.show();

          if (response.data) {
            populateFormFields(response.data);
          }

          setTimeout(() => {
            uploadInfo.fadeOut();
          }, 5000);
        },
        error: function(xhr) {
          uploadProgress.hide();
          uploadError.show();

          let errorMsg = 'Error uploading RC document';
          if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
          } else if (xhr.statusText) {
            errorMsg = xhr.statusText;
          }

          $('#error-message').text(errorMsg);
        }
      });
    }

    function populateFormFields(data) {
      const fieldMappings = {
        'vehicle_registration_no': '#certificate-details-form input[name="vehicle_registration_no"]',
        'holder_name': '#certificate-details-form textarea[name="holder_name"]',
        'fitment_date': '#certificate-details-form input[name="fitment_date"]',
        'chassis_no': '#certificate-details-form input[name="chassis_no"]',
        'engine_no': '#certificate-details-form input[name="engine_no"]',
        'vehicle_model': '#certificate-details-form input[name="vehicle_model"]',
        'color': '#certificate-details-form input[name="color"]',
      };

      Object.keys(fieldMappings).forEach(dataKey => {
        const selector = fieldMappings[dataKey];
        const value = data[dataKey];

        if (value && $(selector).length) {
          $(selector).val(value).change();
        }
      });
    }
  });
</script>
