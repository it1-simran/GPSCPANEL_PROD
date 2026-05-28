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
                  <div class="alert alert-info alert-dismissible" role="alert" id="rc-upload-info" style="display:none;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <strong>RC Uploaded Successfully!</strong> The vehicle details have been auto-populated from your RC document. Please review and edit if needed.
                  </div>
                  <form class="validator form-horizontal" id="certificate-details-form" method="post" action="/user/device/{{ $device->id }}/certificate/save">
                    @csrf
                    <div class="form-group">
                      <label class="control-label col-lg-3">Upload RC Document (Optional)</label>
                      <div class="col-lg-6">
                        <div class="input-group">
                          <input type="file" id="rc_file" accept=".pdf,.jpg,.jpeg,.png,.bmp,.gif" class="form-control" />
                          <span class="input-group-btn">
                            <button class="btn btn-info" type="button" id="upload-rc-btn">Upload & Extract</button>
                          </span>
                        </div>
                        <small class="form-text text-muted">Supported: PDF, JPG, PNG, BMP, GIF (Max 5MB). Upload your vehicle RC to auto-populate the form.</small>
                        <div id="rc-upload-progress" style="display:none; margin-top:10px;">
                          <div class="progress">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%">
                              <span id="rc-progress-text">Processing RC document...</span>
                            </div>
                          </div>
                        </div>
                        <div id="rc-upload-error" class="alert alert-danger" style="display:none; margin-top:10px;"></div>
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
      const warningHtml = `
        <div class="alert alert-warning alert-dismissible" role="alert" style="margin-bottom: 20px;">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <strong>Note:</strong> OCR feature is not available. You can still upload RC documents for storage, but automatic text extraction requires <strong>Tesseract-OCR</strong> to be installed.
          <br><br>
          <small>
            <strong>For your OS (${instructions.os}):</strong><br>
            ${instructions.steps.map(step => `<div>${step}</div>`).join('')}
            <br>Or enter RC details manually in the form below.
          </small>
        </div>
      `;

      $('#certificate-details-form').before(warningHtml);
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

          uploadError.html('<strong>Error:</strong> ' + errorMsg);
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
