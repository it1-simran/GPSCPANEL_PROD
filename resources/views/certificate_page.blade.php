@extends('layouts.apps')

@push('styles')
<style>
/* ════════════════════════════════════════════════════════════════
   CERTIFICATE FORM — Modern Professional Design
   ════════════════════════════════════════════════════════════════ */

#certificate-details-form {
  background: #ffffff;
  border-radius: 12px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* ── Section Headers (with brand accent) ───────────────────────── */
#certificate-details-form h4 {
  font-size: 15px !important;
  font-weight: 700 !important;
  color: #0f172a !important;
  margin: 0 0 24px 0 !important;
  letter-spacing: 0.3px;
  display: flex;
  align-items: center;
  padding: 0 0 14px 0;
  border-bottom: 2px solid #e2e8f0;
  position: relative;
}

#certificate-details-form h4::after {
  content: "";
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 50px;
  height: 2px;
  background: linear-gradient(90deg, #76CF1C, #5fa816);
  border-radius: 2px;
}

#certificate-details-form h4 i {
  font-size: 18px;
  margin-right: 10px !important;
  color: #76CF1C !important;
}

/* ── Section Cards ─────────────────────────────────────────────── */
#certificate-details-form > div[style*="border-top"] {
  border-top: none !important;
  padding-top: 0 !important;
  margin-top: 20px !important;
  padding: 28px 32px 18px 32px !important;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
  transition: box-shadow 0.2s ease;
}

#certificate-details-form > div[style*="border-top"]:hover {
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

#certificate-details-form > div[style*="border-top"]:first-of-type {
  margin-top: 0 !important;
}

/* Last form-group in a section */
#certificate-details-form > div[style*="border-top"] > .form-group:last-child,
#certificate-details-form > div[style*="border-top"] > .row:last-of-type .form-group {
  margin-bottom: 12px !important;
}

/* Form groups — left-aligned, consistent spacing */
#certificate-details-form .form-group {
  margin-bottom: 24px !important;
  display: block !important;
  clear: both;
}

#certificate-details-form .form-group::before,
#certificate-details-form .form-group::after {
  content: none !important;
}

/* Vertical spacing between two-column rows — main fix for tight gaps */
#certificate-details-form .row {
  margin-bottom: 20px !important;
  row-gap: 24px;
}

#certificate-details-form .row + .row {
  margin-top: 4px !important;
}

#certificate-details-form .row:last-of-type {
  margin-bottom: 8px !important;
}

/* Inside a row, the form-group's own bottom margin shouldn't compound */
#certificate-details-form .row .form-group {
  margin-bottom: 0 !important;
}

/* ── Labels ────────────────────────────────────────────────────── */
#certificate-details-form label.control-label {
  display: block !important;
  text-align: left !important;
  width: 100% !important;
  padding: 0 !important;
  float: none !important;
  font-weight: 600 !important;
  color: #475569 !important;
  font-size: 12.5px !important;
  margin-bottom: 8px !important;
  line-height: 1.4;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

#certificate-details-form label.control-label .require {
  color: #ef4444 !important;
  font-weight: 700;
  margin-left: 2px;
}

/* Override Bootstrap col-lg-3 / col-lg-9 on labels & input wrappers — make full width */
#certificate-details-form .form-group > label.col-lg-3,
#certificate-details-form .form-group > label[class*="col-lg-"] {
  width: 100% !important;
  max-width: 100% !important;
  flex: 0 0 100% !important;
}

#certificate-details-form .form-group > div[class*="col-lg-"] {
  width: 100% !important;
  max-width: 100% !important;
  flex: 0 0 100% !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

/* ── Inputs / Textareas / Selects ──────────────────────────────── */
#certificate-details-form .form-control {
  width: 100% !important;
  padding: 11px 14px !important;
  border: 1.5px solid #e2e8f0 !important;
  border-radius: 8px !important;
  font-size: 13.5px !important;
  font-weight: 500 !important;
  color: #0f172a !important;
  background-color: #ffffff !important;
  transition: all 0.2s ease !important;
  height: auto !important;
  text-align: left !important;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02) !important;
  line-height: 1.5;
}

#certificate-details-form .form-control:hover:not([readonly]):not([disabled]) {
  border-color: #cbd5e1 !important;
}

#certificate-details-form .form-control:focus {
  border-color: #76CF1C !important;
  box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.15) !important;
  outline: none !important;
  background-color: #ffffff !important;
}

#certificate-details-form .form-control[readonly],
#certificate-details-form .form-control[disabled] {
  background-color: #f8fafc !important;
  color: #64748b !important;
  cursor: not-allowed;
  border-color: #e2e8f0 !important;
}

#certificate-details-form .form-control::placeholder {
  color: #94a3b8 !important;
  font-size: 13px;
  font-weight: 400;
  text-align: left;
}

#certificate-details-form textarea.form-control {
  min-height: 90px !important;
  resize: vertical !important;
  line-height: 1.6;
}

/* Select dropdowns */
#certificate-details-form select.form-control {
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
  background-repeat: no-repeat !important;
  background-position: right 12px center !important;
  background-size: 16px !important;
  padding-right: 36px !important;
}

/* Helper text below inputs */
#certificate-details-form .form-text,
#certificate-details-form small.form-text {
  display: block;
  margin-top: 6px;
  font-size: 11px !important;
  color: #94a3b8 !important;
  font-style: italic;
  text-align: left;
}

/* ── Row gutters between columns ───────────────────────────────── */
#certificate-details-form .row {
  margin-left: -16px !important;
  margin-right: -16px !important;
  display: flex !important;
  flex-wrap: wrap;
  align-items: flex-start;
}

#certificate-details-form .row > [class*="col-"] {
  padding-left: 16px !important;
  padding-right: 16px !important;
  float: none !important;
  box-sizing: border-box;
}

/* ── Form-group spacing inside sections ────────────────────────── */
#certificate-details-form > div[style*="border-top"] .form-group {
  margin-bottom: 22px !important;
}

#certificate-details-form > div[style*="border-top"] .row .form-group {
  margin-bottom: 22px !important;
}

#certificate-details-form > div[style*="border-top"] .row {
  margin-bottom: 0 !important;
}

/* ── Helper text under inputs ──────────────────────────────────── */
#certificate-details-form .form-text,
#certificate-details-form small.form-text {
  display: block !important;
  margin-top: 6px !important;
  font-size: 11.5px !important;
  color: #94a3b8 !important;
  font-style: normal !important;
  text-align: left !important;
  line-height: 1.4;
}

/* ── Submit button area ────────────────────────────────────────── */
#certificate-details-form .form-actions,
#certificate-details-form .btn-submit-wrap {
  margin-top: 30px;
  padding-top: 24px;
  border-top: 1px solid #eef0f3;
  text-align: right;
}

/* ── Upload cards (RC, Plate, Device) ──────────────────────────── */
.rc-upload-card,
.plate-verify-card,
.device-extract-card {
  border-radius: 12px !important;
  margin-bottom: 24px !important;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.rc-upload-card:hover,
.plate-verify-card:hover,
.device-extract-card:hover {
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.rc-upload-card h4,
.plate-verify-card h4,
.device-extract-card h4 {
  font-size: 15px !important;
  font-weight: 700 !important;
  margin-bottom: 12px !important;
  border: none !important;
  padding: 0 !important;
}

.rc-upload-card h4::after,
.plate-verify-card h4::after,
.device-extract-card h4::after {
  display: none !important;
}

/* ── Page panel ────────────────────────────────────────────────── */
.c_panel {
  border-radius: 12px !important;
  border: none !important;
  box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06) !important;
  overflow: hidden;
}

.c_panel .c_title {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
  padding: 18px 24px !important;
  border-bottom: 3px solid #76CF1C !important;
}

.c_panel .c_title h2 {
  font-size: 16px !important;
  font-weight: 700 !important;
  margin: 0 !important;
  padding: 0 !important;
  color: #ffffff !important;
  letter-spacing: 0.4px;
  text-transform: uppercase;
}

.c_panel .c_content {
  padding: 28px !important;
  background: #f8fafc;
}

/* ── Responsive — stack 2-column rows on small screens ─────────── */
@media (max-width: 768px) {
  #certificate-details-form .row > [class*="col-lg-"],
  #certificate-details-form .row > [class*="col-md-"] {
    width: 100% !important;
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }

  #certificate-details-form > div[style*="border-top"] {
    padding: 22px 18px 12px 18px !important;
  }
}

@media (max-width: 480px) {
  .c_panel .c_content {
    padding: 16px !important;
  }
}
</style>
@endpush

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
            <div class="row ">
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
              <div class="row" style="margin-bottom:12px;">
                <div class="col-md-12" style="display:flex; justify-content:flex-end; gap:10px;">
                  <a href="?edit=1" class="btn btn-warning" style="background-color:#f59e0b; border-color:#f59e0b; color:#fff; padding:8px 20px; font-weight:600; text-decoration:none; border-radius:6px;">
                    <i class="fa fa-edit"></i> Edit Certificate
                  </a>
                </div>
              </div>
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
                    // Auto-fill helpers sourced from existing device data.
                    $deviceCfg    = json_decode($device->configurations ?? '', true) ?: [];
                    $autoImei     = $device->imei ?? '';
                    $autoIccid    = $formData['vltd_icc_id'] ?? ($vltd_icc_id ?? ($deviceCfg['ccid']['value'] ?? ($deviceCfg['iccid']['value'] ?? '')));
                    $autoModel    = $formData['vltd_model'] ?? ($vltd_model ?? ($category_name ?? ''));
                    $autoFirmware = $formData['firmware_version'] ?? ($deviceCfg['firmware_version']['value'] ?? ($deviceCfg['firmwareVersion']['value'] ?? ''));
                    $autoVendorId = $formData['vendor_id'] ?? ($deviceCfg['vendorId']['value'] ?? ($deviceCfg['vendor_id'] ?? ''));
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
                        <p style="color:#666; font-size:13px; margin-bottom:15px;">Upload both <strong>front</strong> and <strong>back</strong> pages of your RC document. We'll extract vehicle details from both. Supported formats: PDF, JPG, PNG, BMP, GIF (Max 5MB each).</p>
                      </div>
                    </div>

                    <div class="row">
                      <!-- RC Front -->
                      <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:12px;">
                          <label style="font-weight:600; color:#333; display:block; margin-bottom:8px; font-size:13px;">
                            <i class="fa fa-id-card" style="color:#76CF1C;margin-right:6px;"></i>RC Front Page <span style="color:#d32f2f;">*</span>
                          </label>
                          <input type="file" id="rc_file_front" accept=".pdf,.jpg,.jpeg,.png,.bmp,.gif" style="display:block; width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:6px; cursor:pointer; background:#f8fafc; font-size:13px; color:#475569; transition:border-color 0.2s;" />
                          <small style="color:#666; font-size:11px; display:block; margin-top:4px;">Main RC page with vehicle details</small>
                          <div id="rc-front-preview" style="display:none; margin-top:8px;">
                            <span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; background:#76CF1C20; color:#166534; border-radius:4px; font-size:12px; font-weight:500;">
                              <i class="fa fa-check-circle"></i> <span id="rc-front-name"></span>
                            </span>
                          </div>
                        </div>
                      </div>

                      <!-- RC Back -->
                      <div class="col-md-6">
                        <div class="form-group" style="margin-bottom:12px;">
                          <label style="font-weight:600; color:#333; display:block; margin-bottom:8px; font-size:13px;">
                            <i class="fa fa-id-card-o" style="color:#76CF1C;margin-right:6px;"></i>RC Back Page <span style="color:#888; font-weight:400;">(optional)</span>
                          </label>
                          <input type="file" id="rc_file_back" accept=".pdf,.jpg,.jpeg,.png,.bmp,.gif" style="display:block; width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:6px; cursor:pointer; background:#f8fafc; font-size:13px; color:#475569; transition:border-color 0.2s;" />
                          <small style="color:#666; font-size:11px; display:block; margin-top:4px;">Optional: back page (address, owner photo)</small>
                          <div id="rc-back-preview" style="display:none; margin-top:8px;">
                            <span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; background:#76CF1C20; color:#166534; border-radius:4px; font-size:12px; font-weight:500;">
                              <i class="fa fa-check-circle"></i> <span id="rc-back-name"></span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <button class="btn btn-success" type="button" id="upload-rc-btn" style="margin-top:10px; background-color:#76CF1C; border-color:#76CF1C; color:#fff; padding:10px 30px; font-weight:500;">
                          <i class="fa fa-upload"></i> Upload & Extract Details
                        </button>

                        <div id="rc-upload-progress" style="display:none; margin-top:15px;">
                          <p style="font-size:12px; color:#666; margin-bottom:8px;">Processing RC document<span id="rc-progress-detail"></span>...</p>
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

                  <!-- Number Plate Verification Card -->
                  <div class="plate-verify-card" style="background: linear-gradient(135deg, #3b82f615 0%, #3b82f608 100%); border: 2px dashed #3b82f6; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
                    <div class="row">
                      <div class="col-md-12">
                        <h4 style="color:#333; margin-bottom:15px; font-weight:600;">
                          <i class="fa fa-car" style="color:#3b82f6;margin-right:8px;"></i>Verify Number Plate
                        </h4>
                        <p style="color:#666; font-size:13px; margin-bottom:15px;">
                          Upload a photo of the vehicle's number plate. We'll check if it matches the registration number from the RC document.
                          <strong>Tip:</strong> Make sure the plate is clearly visible and well-lit.
                        </p>

                        <div class="file-upload-wrapper">
                          <div class="form-group" style="margin-bottom:0;">
                            <input type="file" id="plate_file" accept=".jpg,.jpeg,.png,.bmp,.gif" style="display:block; width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:6px; cursor:pointer; background:#f8fafc; font-size:13px; color:#475569; transition:border-color 0.2s;" />
                          </div>
                        </div>

                        <button class="btn btn-primary" type="button" id="verify-plate-btn" style="margin-top:10px; background-color:#3b82f6; border-color:#3b82f6; color:#fff; padding:10px 30px; font-weight:500;">
                          <i class="fa fa-check-circle"></i> Verify Plate Number
                        </button>

                        <div id="plate-verify-progress" style="display:none; margin-top:15px;">
                          <p style="font-size:12px; color:#666; margin-bottom:8px;">Verifying number plate...</p>
                          <div class="progress" style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%; background-color:#3b82f6; border-radius:3px;"></div>
                          </div>
                        </div>

                        <div id="plate-verify-success" style="display:none; margin-top:15px;">
                          <div class="alert alert-success alert-dismissible" style="margin:0;">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-check-circle"></i> <strong>Verified!</strong> <span id="plate-success-message"></span>
                          </div>
                        </div>

                        <div id="plate-verify-error" style="display:none; margin-top:15px;">
                          <div class="alert alert-danger alert-dismissible" style="margin:0;">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-exclamation-circle"></i> <strong>Mismatch!</strong> <span id="plate-error-message"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Device Image OCR Card -->
                  <div class="device-extract-card" style="background: linear-gradient(135deg, #f59e0b15 0%, #f59e0b08 100%); border: 2px dashed #f59e0b; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
                    <div class="row">
                      <div class="col-md-12">
                        <h4 style="color:#333; margin-bottom:15px; font-weight:600;">
                          <i class="fa fa-microchip" style="color:#f59e0b;margin-right:8px;"></i>Scan Device Label
                        </h4>
                        <p style="color:#666; font-size:13px; margin-bottom:15px;">
                          Upload a photo of the GPS device's label/sticker. We'll automatically extract <strong>IMEI</strong> and <strong>ICCID</strong> and fill them into the form below.
                          <strong>Tip:</strong> Make sure the label text is clearly visible and well-lit.
                        </p>

                        <div class="file-upload-wrapper">
                          <div class="form-group" style="margin-bottom:0;">
                            <input type="file" id="device_file" accept=".jpg,.jpeg,.png,.bmp,.gif" style="display:block; width:100%; padding:12px; border:2px solid #e2e8f0; border-radius:6px; cursor:pointer; background:#f8fafc; font-size:13px; color:#475569; transition:border-color 0.2s;" />
                          </div>
                        </div>

                        <button class="btn btn-warning" type="button" id="extract-device-btn" style="margin-top:10px; background-color:#f59e0b; border-color:#f59e0b; color:#fff; padding:10px 30px; font-weight:500;">
                          <i class="fa fa-search"></i> Scan & Extract IMEI/ICCID
                        </button>

                        <div id="device-extract-progress" style="display:none; margin-top:15px;">
                          <p style="font-size:12px; color:#666; margin-bottom:8px;">Scanning device label...</p>
                          <div class="progress" style="height:6px; background:#f0f0f0; border-radius:3px; overflow:hidden;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%; background-color:#f59e0b; border-radius:3px;"></div>
                          </div>
                        </div>

                        <div id="device-extract-success" style="display:none; margin-top:15px;">
                          <div class="alert alert-success alert-dismissible" style="margin:0;">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-check-circle"></i> <strong>Extracted!</strong> <span id="device-success-message"></span>
                          </div>
                        </div>

                        <!-- SIM Profile Details (populated from GrowSpace API) -->
                        <div id="sim-profiles-container" style="display:none; margin-top:15px; background:#fff; border-radius:8px; padding:18px; border:1px solid #fde68a;">
                          <h5 style="color:#92400e; margin:0 0 12px 0; font-weight:600; font-size:14px;">
                            <i class="fa fa-sim-card" style="margin-right:6px;"></i>SIM Profile Details
                          </h5>
                          <div id="sim-meta" style="font-size:12px; color:#64748b; margin-bottom:10px;"></div>
                          <table id="sim-profiles-table" style="width:100%; border-collapse:collapse; font-size:13px;">
                            <thead>
                              <tr style="background:#fef3c7;">
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">Slot</th>
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">Operator</th>
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">MSISDN</th>
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">IMSI</th>
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">Status</th>
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">Activation Date</th>
                                <th style="padding:8px 10px; text-align:left; font-weight:600; color:#92400e; border-bottom:1px solid #fde68a;">Expiry Date</th>
                              </tr>
                            </thead>
                            <tbody id="sim-profiles-tbody"></tbody>
                          </table>
                        </div>

                        <div id="device-extract-error" style="display:none; margin-top:15px;">
                          <div class="alert alert-danger alert-dismissible" style="margin:0;">
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                            <i class="fa fa-exclamation-circle"></i> <strong>Error:</strong> <span id="device-error-message"></span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <form class="validator form-horizontal" id="certificate-details-form" method="post" action="/user/device/{{ $device->id }}/certificate/save" enctype="multipart/form-data">
                    @csrf
                    <!-- Certificate Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:20px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-calendar" style="color:#76CF1C;margin-right:8px;"></i>Certificate Details
                      </h4>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                          Fitment Date <span class="require" style="color:#d32f2f;">*</span>
                          <span style="font-weight:400; color:#94a3b8; font-size:11px; margin-left:6px;">— today or earlier only</span>
                        </label>
                        <input class="form-control" type="date" name="fitment_date" id="fitment_date_input"
                          value="{{ old('fitment_date', date('Y-m-d')) }}"
                          max="{{ date('Y-m-d') }}"
                          required
                          style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; width:100%; max-width:300px;" />
                      </div>
                    </div>

                    <!-- Vendor Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-building" style="color:#76CF1C;margin-right:8px;"></i>Vendor Details
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vendor Name <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vendor_name" placeholder="Enter vendor name" value="{{ old('vendor_name', $formData['vendor_name'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vendor Contact Number <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vendor_contact" placeholder="e.g., 9876543210" value="{{ old('vendor_contact', $formData['vendor_contact'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vendor Address <span class="require" style="color:#d32f2f;">*</span></label>
                        <textarea class="form-control" name="vendor_address" placeholder="Enter complete vendor address" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; min-height:70px;">{{ old('vendor_address', $formData['vendor_address'] ?? '') }}</textarea>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vendor Email <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="email" name="vendor_email" placeholder="e.g., vendor@example.com" value="{{ old('vendor_email', $formData['vendor_email'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vendor GST Number <span style="font-weight:400; color:#94a3b8; font-size:11px;">(Optional)</span></label>
                            <input class="form-control" type="text" name="vendor_gst" placeholder="e.g., 22AAAAA0000A1Z5" value="{{ old('vendor_gst', $formData['vendor_gst'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Fitter Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-wrench" style="color:#76CF1C;margin-right:8px;"></i>Fitter Details
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Fitter Company Name <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="fitter_company" placeholder="Enter fitter company name" value="{{ old('fitter_company', $formData['fitter_company'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Fitter Contact Number <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="fitter_contact" placeholder="e.g., 9876543210" value="{{ old('fitter_contact', $formData['fitter_contact'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Fitter Address <span class="require" style="color:#d32f2f;">*</span></label>
                        <textarea class="form-control" name="fitter_address" placeholder="Enter complete fitter address" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; min-height:70px;">{{ old('fitter_address', $formData['fitter_address'] ?? '') }}</textarea>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Fitter Email <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="email" name="fitter_email" placeholder="e.g., fitter@example.com" value="{{ old('fitter_email', $formData['fitter_email'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Owner Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-user-circle" style="color:#76CF1C;margin-right:8px;"></i>Owner Details
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Owner Name <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="owner_name" placeholder="Enter owner name" value="{{ old('owner_name', $formData['owner_name'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Owner Mobile Number <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="owner_mobile" placeholder="e.g., 9876543210" value="{{ old('owner_mobile', $formData['owner_mobile'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Owner Address <span class="require" style="color:#d32f2f;">*</span></label>
                        <textarea class="form-control" name="owner_address" placeholder="Enter complete owner address" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; min-height:70px;">{{ old('owner_address', $formData['owner_address'] ?? '') }}</textarea>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Owner Email <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="email" name="owner_email" placeholder="e.g., owner@example.com" value="{{ old('owner_email', $formData['owner_email'] ?? '') }}" required style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vehicle Registration Number <span class="require" style="color:#d32f2f;">*</span></label>
                            {{-- Mirrors the canonical Vehicle Registration No (kept in sync via JS); display-only to avoid a duplicate submit field. --}}
                            <input class="form-control" type="text" id="owner_vehicle_reg_display" placeholder="Auto-filled from Vehicle Information" value="{{ old('vehicle_registration_no', $formData['vehicle_registration_no'] ?? '') }}" readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
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

                    <!-- Service Provider Section (placed before VLTD Device Information) -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-building" style="color:#76CF1C;margin-right:8px;"></i>Service Provider
                      </h4>

                      <div class="form-group" style="margin-bottom:20px;">
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
                        {{-- Service provider is locked to Growspace for now: pre-selected and not changeable. --}}
                        {{-- The disabled <select> below is for display only; the hidden input carries the value on submit. --}}
                        <input type="hidden" name="service_provider" value="Growspace">
                        <select id="serviceProvidersSelect" disabled style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; width:100%; background-color:#f5f5f5; cursor:not-allowed;">
                          <option value="Growspace" selected>Growspace</option>
                        </select>
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
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              VLTD Serial No <span class="require" style="color:#d32f2f;">*</span>
                              <span style="font-weight:400; color:#94a3b8; font-size:11px; margin-left:6px;">— auto-generated, unique</span>
                            </label>
                            <input class="form-control" type="text" name="vltd_serial_no" id="vltd_serial_no_input" placeholder="Auto-generated serial number" value="{{ old('vltd_serial_no', $formData['vltd_serial_no'] ?? '') }}" required readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; width:100%; background-color:#f8f8f8; font-family: monospace; letter-spacing: 1px;" />
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
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              VLTD ICCID
                              <span style="font-weight:400; color:#94a3b8; font-size:11px; margin-left:6px;">— auto-populated from device label scan only</span>
                            </label>
                            <input class="form-control" type="text" name="vltd_icc_id" id="vltd_icc_id_input" placeholder="Auto-fetched from device label" value="{{ old('vltd_icc_id', $formData['vltd_icc_id'] ?? ($vltd_icc_id ?? '')) }}" readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; width:100%; background-color:#f8f8f8;" />
                          </div>
                        </div>
                      </div>

                      <!-- SIM Profile Details (auto-filled from device label scan) -->
                      <div class="row" id="sim-form-row" style="{{ !empty($formData['sim1_operator']) ? '' : 'display:none;' }}">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              <i class="fa fa-sim-card" style="color:#76CF1C;margin-right:6px;"></i>SIM 1 Operator
                            </label>
                            <input class="form-control" type="text" name="sim1_operator" placeholder="e.g., Airtel" value="{{ old('sim1_operator', $formData['sim1_operator'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">SIM 1 MSISDN</label>
                            <input class="form-control" type="text" name="sim1_msisdn" placeholder="Phone number" value="{{ old('sim1_msisdn', $formData['sim1_msisdn'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              <i class="fa fa-sim-card" style="color:#76CF1C;margin-right:6px;"></i>SIM 2 Operator
                            </label>
                            <input class="form-control" type="text" name="sim2_operator" placeholder="e.g., BSNL" value="{{ old('sim2_operator', $formData['sim2_operator'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">SIM 2 MSISDN</label>
                            <input class="form-control" type="text" name="sim2_msisdn" placeholder="Phone number" value="{{ old('sim2_msisdn', $formData['sim2_msisdn'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              <i class="fa fa-calendar" style="color:#76CF1C;margin-right:6px;"></i>SIM 1 Activation Date <span style="font-weight:400; color:#94a3b8; font-size:11px;">(from API)</span>
                            </label>
                            <input class="form-control" type="text" name="sim1_activation_date" placeholder="Auto-fetched from SIM API" value="{{ old('sim1_activation_date', $formData['sim1_activation_date'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8fafc;" readonly />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              <i class="fa fa-calendar" style="color:#76CF1C;margin-right:6px;"></i>SIM 1 Expiry Date <span style="font-weight:400; color:#94a3b8; font-size:11px;">(from API)</span>
                            </label>
                            <input class="form-control" type="text" name="sim1_expiry_date" placeholder="Auto-fetched from SIM API" value="{{ old('sim1_expiry_date', $formData['sim1_expiry_date'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8fafc;" readonly />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              <i class="fa fa-calendar" style="color:#76CF1C;margin-right:6px;"></i>SIM 2 Activation Date <span style="font-weight:400; color:#94a3b8; font-size:11px;">(from API)</span>
                            </label>
                            <input class="form-control" type="text" name="sim2_activation_date" placeholder="Auto-fetched from SIM API" value="{{ old('sim2_activation_date', $formData['sim2_activation_date'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8fafc;" readonly />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">
                              <i class="fa fa-calendar" style="color:#76CF1C;margin-right:6px;"></i>SIM 2 Expiry Date <span style="font-weight:400; color:#94a3b8; font-size:11px;">(from API)</span>
                            </label>
                            <input class="form-control" type="text" name="sim2_expiry_date" placeholder="Auto-fetched from SIM API" value="{{ old('sim2_expiry_date', $formData['sim2_expiry_date'] ?? '') }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8fafc;" readonly />
                          </div>
                        </div>
                      </div>
                    <!-- Device Details Section -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <h4 style="color:#333; margin-bottom:20px; font-weight:600;">
                        <i class="fa fa-microchip" style="color:#76CF1C;margin-right:8px;"></i>Device Details
                      </h4>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">IMEI Number <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="device_imei" value="{{ old('device_imei', $formData['device_imei'] ?? $autoImei) }}" readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">ICCID Number <span class="require" style="color:#d32f2f;">*</span></label>
                            {{-- Mirrors the canonical VLTD ICCID field (kept in sync via JS). --}}
                            <input class="form-control" type="text" name="device_iccid" id="device_iccid_display" value="{{ old('device_iccid', $formData['device_iccid'] ?? $autoIccid) }}" readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Device Model <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="device_model" value="{{ old('device_model', $formData['device_model'] ?? $autoModel) }}" readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group" style="margin-bottom:20px;">
                            <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Vendor ID <span class="require" style="color:#d32f2f;">*</span></label>
                            <input class="form-control" type="text" name="vendor_id" value="{{ old('vendor_id', $autoVendorId) }}" required readonly style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px; background-color:#f8f8f8;" />
                            <small class="form-text text-muted" style="color:#94a3b8;">
                              <i class="fa fa-info-circle"></i> Auto-populated from device configuration.
                            </small>
                          </div>
                        </div>
                      </div>

                      <div class="form-group" style="margin-bottom:20px;">
                        <label class="control-label" style="font-weight:500; color:#333; display:block; margin-bottom:8px;">Firmware Version <span style="font-weight:400; color:#94a3b8; font-size:11px;">(if applicable)</span></label>
                        <input class="form-control" type="text" name="firmware_version" placeholder="e.g., v1.2.3" value="{{ old('firmware_version', $autoFirmware) }}" style="border-radius:4px; border:1px solid #ddd; padding:10px; font-size:13px;" />
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

                    <!-- Action Buttons -->
                    <div style="border-top:2px solid #f0f0f0; padding-top:20px; margin-top:30px;">
                      <div class="form-group" style="margin-bottom:0;">
                        <div class="col-lg-12 text-right">
                          <button class="btn btn-default" type="reset" style="margin-right:10px; border-radius:4px; padding:10px 30px;">
                            <i class="fa fa-times"></i> Reset
                          </button>
                          <button class="btn btn-info" type="button" id="preview-cert-btn" style="margin-right:10px; background-color:#0891b2; border-color:#0891b2; color:#fff; border-radius:4px; padding:10px 30px; font-weight:500;">
                            <i class="fa fa-eye"></i> Preview Certificate
                          </button>
                          <button class="btn btn-primary" type="submit" style="background-color:#76CF1C; border-color:#76CF1C; color:#fff; border-radius:4px; padding:10px 30px; font-weight:500;">
                            <i class="fa fa-save"></i> Save &amp; View Certificate
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
      allowClear: false,
      width: '100%'
    });
    // Lock service provider to Growspace (pre-selected, not changeable for now)
    $('#serviceProvidersSelect').val('Growspace').prop('disabled', true).trigger('change.select2');

    // ═══════════════════════════════════════════════════════════════════
    // VLTD SERIAL AUTO-GENERATION
    // Format: JSDE14A + 6-digit numeric counter (000001, 000002, etc.)
    // ═══════════════════════════════════════════════════════════════════
    function generateVltdSerial() {
      var deviceId = {{ (int)($device->id ?? 0) }};
      if (!deviceId) {
        console.warn('Device ID not found');
        return;
      }

      var $input = $('#vltd_serial_no_input');
      var pathname = window.location.pathname.replace(/\/\d+$/, '/' + deviceId) + '/generate-vltd-serial?t=' + new Date().getTime();

      $.ajax({
        url: pathname,
        type: 'GET',
        dataType: 'json',
        cache: false,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
        },
        success: function(response) {
          console.log('Serial generation response:', response);
          if (response && response.serial) {
            console.log('Setting serial to:', response.serial);
            $input.val(response.serial);
            console.log('Current value:', $input.val());
          } else {
            console.warn('No serial in response:', response);
          }
        },
        error: function(xhr, status, error) {
          console.error('Failed to generate VLTD serial:', {status: xhr.status, statusText: xhr.statusText, error: error});
          console.error('Response:', xhr.responseText);
        }
      });
    }

    // Auto-generate on page load only if input is empty
    var $serialInput = $('#vltd_serial_no_input');
    console.log('Serial input element found:', $serialInput.length > 0);
    console.log('Current value:', $serialInput.val());

    if (!$serialInput.val()) {
      console.log('Triggering serial generation...');
      generateVltdSerial();
    } else {
      console.log('Serial already has value:', $serialInput.val());
    }

    // ═══════════════════════════════════════════════════════════════════
    // VERIFICATION STATE — tracks the result of each verification step
    // null = not attempted, true = passed, false = failed
    // ═══════════════════════════════════════════════════════════════════
    window.verificationState = {
      plate_verified:   null,   // number plate matches RC
      device_verified:  null,   // device IMEI matches stored
      rc_extracted:     null,   // all mandatory RC fields extracted from the document
    };

    /**
     * Show / hide a blocking error banner above the form
     */
    function showBlockingError(messages) {
      let $banner = $('#verification-blocker');
      if ($banner.length === 0) {
        $banner = $('<div id="verification-blocker" style="display:none; background:#fef2f2; border:2px solid #ef4444; border-radius:10px; padding:18px 22px; margin-bottom:20px;"><div style="display:flex; align-items:flex-start; gap:12px;"><i class="fa fa-exclamation-triangle" style="color:#ef4444; font-size:22px; margin-top:2px;"></i><div style="flex:1;"><h5 style="margin:0 0 8px 0; color:#991b1b; font-size:14px; font-weight:700;">Cannot Save Certificate</h5><ul id="verification-blocker-list" style="margin:0; padding-left:18px; color:#7f1d1d; font-size:13px; line-height:1.7;"></ul></div></div></div>');
        $('#certificate-details-form').before($banner);
      }
      const $list = $('#verification-blocker-list');
      $list.empty();
      messages.forEach(function(msg) {
        $list.append('<li>' + msg + '</li>');
      });
      if (messages.length > 0) {
        $banner.show();
        $('html, body').animate({ scrollTop: $banner.offset().top - 100 }, 300);
      } else {
        $banner.hide();
      }
    }

    /**
     * Block form submission if any verification has failed or required RC fields are missing.
     * Plate / Device checks are OPTIONAL — but if attempted and FAILED, must block.
     * Owner Name and Owner Address MUST be populated (from Owner Details section).
     */
    $('#certificate-details-form').on('submit', function(e) {
      const issues = [];
      const state = window.verificationState;
      const ownerName = $('input[name="owner_name"]').val() || '';
      const ownerAddress = $('textarea[name="owner_address"]').val() || '';

      if (state.plate_verified === false) {
        issues.push('Number plate verification <strong>failed</strong> — the plate photo does not match the RC registration number. Re-upload the correct plate photo or fix the registration number.');
      }
      if (state.device_verified === false) {
        issues.push('Device IMEI verification <strong>failed</strong> — the IMEI extracted from the device label does not match the device\'s stored IMEI. Upload the correct device label or contact your administrator.');
      }
      if (state.rc_extracted === false) {
        issues.push('RC document is <strong>incomplete</strong> — one or more mandatory fields could not be extracted. Please upload a clear and readable RC image until all required fields are detected.');
      }

      // Validate Owner Name and Owner Address are filled (required for certificate)
      if (!ownerName.trim()) {
        issues.push('Owner Name is <strong>required</strong> — used as the certificate holder. Please enter the owner name in the Owner Details section.');
      }
      if (!ownerAddress.trim()) {
        issues.push('Owner Address is <strong>required</strong> — used for the certificate. Please enter the owner address in the Owner Details section.');
      }

      if (issues.length > 0) {
        e.preventDefault();
        showBlockingError(issues);
        return false;
      }

      // No issues — allow form to submit normally
      showBlockingError([]);
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

    // ─── File-selection previews ──────────────────────────────────────
    $('#rc_file_front').on('change', function() {
      if (this.files && this.files[0]) {
        $('#rc-front-name').text(this.files[0].name);
        $('#rc-front-preview').show();
      } else {
        $('#rc-front-preview').hide();
      }
    });
    $('#rc_file_back').on('change', function() {
      if (this.files && this.files[0]) {
        $('#rc-back-name').text(this.files[0].name);
        $('#rc-back-preview').show();
      } else {
        $('#rc-back-preview').hide();
      }
    });

    // ─── RC Upload Handler — extracts from FRONT (+ optionally BACK) ──
    $('#upload-rc-btn').on('click', function() {
      const frontFile = document.getElementById('rc_file_front').files[0];
      const backFile  = document.getElementById('rc_file_back').files[0];

      if (!frontFile) {
        $('#error-message').text('Please select the RC Front page (required).');
        $('#rc-upload-error').show();
        return;
      }

      uploadRCDocuments(frontFile, backFile);
    });

    /**
     * Upload front (required) and optionally back. Merges extracted fields
     * — front takes priority; back fills any blanks.
     */
    function uploadRCDocuments(frontFile, backFile) {
      const uploadProgress = $('#rc-upload-progress');
      const uploadError    = $('#rc-upload-error');
      const uploadInfo     = $('#rc-upload-info');

      uploadProgress.show();
      uploadError.hide();
      uploadInfo.hide();
      $('#rc-progress-detail').text(' (front page)');

      uploadSingleRC(frontFile, 'front').then(function(frontData) {
        // If back is also provided, extract from it and merge in MISSING fields
        if (backFile) {
          $('#rc-progress-detail').text(' (back page)');
          return uploadSingleRC(backFile, 'back').then(function(backData) {
            // Merge: front-priority — only fill blanks from back
            const merged = Object.assign({}, backData || {}, frontData || {});
            // Use back values for fields where front returned empty
            if (frontData && backData) {
              Object.keys(backData).forEach(function(k) {
                if (!frontData[k] && backData[k]) merged[k] = backData[k];
              });
            }
            return merged;
          });
        }
        return frontData;
      }).then(function(finalData) {
        uploadProgress.hide();
        console.log('RC extracted data:', finalData);
        if (finalData) populateFormFields(finalData);

        // ── Data extraction validation (on the merged front+back result) ──
        // Every mandatory RC field (marked * on the form) must be present
        // before the user can proceed.
        const requiredRC = {
          'vehicle_registration_no': 'Vehicle Registration No',
          'chassis_no':              'Chassis No',
          'engine_no':               'Engine No',
          'color':                   'Color',
          'vehicle_model':           'Vehicle Model',
        };
        const missing = Object.keys(requiredRC).filter(function(k) {
          return !finalData || !finalData[k] || String(finalData[k]).trim() === '';
        }).map(function(k) { return requiredRC[k]; });

        if (missing.length > 0) {
          window.verificationState.rc_extracted = false;
          uploadInfo.hide();
          uploadError.show();
          $('#error-message').text(
            'Could not extract the following required field(s) from the RC: '
            + missing.join(', ')
            + '. Please upload a clear and readable image until all required fields are detected.'
          );
        } else {
          window.verificationState.rc_extracted = true;
          uploadError.hide();
          uploadInfo.show();
          setTimeout(function() { uploadInfo.fadeOut(); }, 5000);
        }
      }).catch(function(err) {
        uploadProgress.hide();
        window.verificationState.rc_extracted = false;
        uploadError.show();
        $('#error-message').text(err || 'Error uploading RC document');
      });
    }

    /**
     * Sends a single RC image to backend; resolves with extracted data map.
     * @param {File} file - The RC image file
     * @param {string} rcType - 'front' or 'back' to indicate which page
     */
    function uploadSingleRC(file, rcType) {
      return new Promise(function(resolve, reject) {
        const formData = new FormData();
        formData.append('rc_file', file);
        if (rcType) formData.append('rc_type', rcType);

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
            resolve(response.data || {});
          },
          error: function(xhr) {
            let errorMsg = 'Error uploading RC document';
            if (xhr.responseJSON && xhr.responseJSON.error) errorMsg = xhr.responseJSON.error;
            else if (xhr.statusText) errorMsg = xhr.statusText;
            reject(errorMsg);
          }
        });
      });
    }

    function populateFormFields(data) {
      const fieldMappings = {
        'vehicle_registration_no': '#certificate-details-form input[name="vehicle_registration_no"]',
        'fitment_date':            '#certificate-details-form input[name="fitment_date"]',
        'chassis_no':              '#certificate-details-form input[name="chassis_no"]',
        'engine_no':               '#certificate-details-form input[name="engine_no"]',
        'vehicle_model':           '#certificate-details-form input[name="vehicle_model"]',
        'vehicle_class':           '#certificate-details-form input[name="vehicle_class"]',
        'fuel_type':               '#certificate-details-form input[name="fuel_type"]',
        'color':                   '#certificate-details-form input[name="color"]',
      };

      Object.keys(fieldMappings).forEach(dataKey => {
        const selector = fieldMappings[dataKey];
        const value = data[dataKey];

        if (value && $(selector).length) {
          $(selector).val(value).change();
        }
      });

      // Auto-populate Owner Name and Owner Address from RC data
      if (data.holder_name) {
        $('input[name="owner_name"]').val(data.holder_name).change();
      }

      // Auto-populate Owner Address from extracted address
      console.log('Checking owner_address:', data.owner_address);
      if (data.owner_address) {
        console.log('Setting owner_address to:', data.owner_address);
        $('textarea[name="owner_address"]').val(data.owner_address).change();
      } else {
        console.log('Owner address is empty or undefined');
      }
    }

    // ─── Manual ICCID Lookup (Fetch SIM Data button) ──────────────────
    function lookupIccidAndFillSimFields(iccidValue, statusEl) {
      const iccid = (iccidValue || '').replace(/[\s\-]/g, '');
      if (!iccid || iccid.length < 18) {
        statusEl.text('').css('color', '#64748b');
        return;
      }

      statusEl.text('Looking up ICCID...').css('color', '#0891b2');

      $.ajax({
        url: '/user/certificate/{{ $device->id }}/lookup-iccid',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { iccid: iccid },
        success: function(response) {
          const sims = response.sims || [];
          if (sims.length === 0) {
            statusEl.html('<i class="fa fa-info-circle"></i> ' + (response.message || 'No SIM data found for this ICCID.'))
                    .css('color', '#dc2626');
            return;
          }
          // Auto-fill SIM 1 / SIM 2 fields
          sims.forEach(function(sim, idx) {
            if (idx >= 2) return;
            const opField = $('#certificate-details-form input[name="sim' + (idx + 1) + '_operator"]');
            const msField = $('#certificate-details-form input[name="sim' + (idx + 1) + '_msisdn"]');
            if (opField.length && sim.operator) opField.val(sim.operator).change();
            if (msField.length && sim.msisdn)   msField.val(sim.msisdn).change();
          });
          $('#sim-form-row').show();
          statusEl.html('<i class="fa fa-check-circle"></i> Found ' + sims.length + ' SIM profile(s): '
                      + sims.map(s => s.operator + ' (' + s.msisdn + ')').join(', '))
                  .css('color', '#16a34a');
        },
        error: function(xhr) {
          let msg = 'Lookup failed.';
          if (xhr.responseJSON) {
            msg = xhr.responseJSON.message || xhr.responseJSON.error || msg;
          }
          statusEl.html('<i class="fa fa-exclamation-circle"></i> ' + msg).css('color', '#dc2626');
        }
      });
    }

    // ICCID is now read-only and only populated from device label scan
    // Manual lookup functionality removed per requirements

    // ─── Device Label OCR (IMEI + ICCID) ──────────────────────────────
    $('#extract-device-btn').on('click', function() {
      const deviceFile = $('#device_file')[0].files[0];

      $('#device-extract-success, #device-extract-error').hide();
      $('#device-extract-progress').show();

      if (!deviceFile) {
        $('#device-extract-progress').hide();
        $('#device-error-message').text('Please select a device label image first.');
        $('#device-extract-error').show();
        return;
      }

      const formData = new FormData();
      formData.append('device_file', deviceFile);
      formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

      $.ajax({
        url: '/user/certificate/{{ $device->id }}/extract-device',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          $('#device-extract-progress').hide();
          $('#sim-profiles-container').hide();
          $('#sim-profiles-tbody').empty();

          // Populate VLTD Serial No (IMEI) and ICCID fields
          const imei  = response.imei;
          const iccid = response.iccid;
          let msgParts = [];

          if (imei) {
            const imeiField = $('#certificate-details-form input[name="vltd_serial_no"]');
            if (imeiField.length && !imeiField.prop('readonly')) {
              imeiField.val(imei).change();
            }
            msgParts.push('IMEI: <strong>' + imei + '</strong>');
            if (response.imei_matches === true) {
              window.verificationState.device_verified = true;
              msgParts.push('<span style="color:#16a34a;">(✓ matches device)</span>');
            } else if (response.imei_matches === false) {
              window.verificationState.device_verified = false;
              msgParts.push('<span style="color:#dc2626;">(⚠ does NOT match device IMEI '
                + (response.device_imei || '') + ')</span>');
            } else {
              // No stored device IMEI to compare — consider verified
              window.verificationState.device_verified = true;
            }
          }
          if (iccid) {
            const iccidField = $('#certificate-details-form input[name="vltd_icc_id"]');
            if (iccidField.length) iccidField.val(iccid).change();
            msgParts.push('ICCID: <strong>' + iccid + '</strong>');
          }

          if (msgParts.length === 0) {
            window.verificationState.device_verified = false;
            $('#device-error-message').text('Could not detect IMEI or ICCID. Please upload a clearer image.');
            $('#device-extract-error').show();
            return;
          }

          $('#device-success-message').html(msgParts.join(' · '));
          $('#device-extract-success').show();

          // Clear blocker if device verification passed
          if (window.verificationState.device_verified === true) {
            showBlockingError([]);
          }

          // Render SIM profile details from GrowSpace API
          const sims = response.sims || [];
          if (sims.length > 0) {
            const $tbody = $('#sim-profiles-tbody');
            sims.forEach(function(sim, idx) {
              const slot           = sim.profile_slot || (idx + 1);
              const operator       = sim.operator     || '—';
              const msisdn         = sim.msisdn       || '—';
              const imsi           = sim.imsi         || '—';
              const status         = sim.status       || '—';
              const activationDate = sim.activation_date || '—';
              const expiryDate     = sim.expiry_date  || '—';
              const statusColor    = (status.toLowerCase() === 'active') ? '#16a34a'
                                  : (status.toLowerCase() === 'provisioned') ? '#0891b2'
                                  : '#64748b';

              $tbody.append(
                '<tr style="border-bottom:1px solid #fef3c7;">' +
                  '<td style="padding:10px;"><strong>SIM ' + slot + '</strong></td>' +
                  '<td style="padding:10px;"><strong style="color:#1e293b;">' + operator + '</strong></td>' +
                  '<td style="padding:10px; font-family:monospace;">' + msisdn + '</td>' +
                  '<td style="padding:10px; font-family:monospace; color:#64748b;">' + imsi + '</td>' +
                  '<td style="padding:10px;"><span style="background:' + statusColor + '20; color:' + statusColor + '; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600;">' + status + '</span></td>' +
                  '<td style="padding:10px; color:#64748b;">' + activationDate + '</td>' +
                  '<td style="padding:10px; color:#64748b;">' + expiryDate + '</td>' +
                '</tr>'
              );

              // Auto-fill SIM form fields so values get saved with the certificate
              if (idx < 2) {
                const opField       = $('#certificate-details-form input[name="sim' + (idx + 1) + '_operator"]');
                const msField       = $('#certificate-details-form input[name="sim' + (idx + 1) + '_msisdn"]');
                const activationField = $('#certificate-details-form input[name="sim' + (idx + 1) + '_activation_date"]');
                const expiryField   = $('#certificate-details-form input[name="sim' + (idx + 1) + '_expiry_date"]');

                if (opField.length && sim.operator) opField.val(sim.operator).change();
                if (msField.length && sim.msisdn)   msField.val(sim.msisdn).change();
                if (activationField.length && sim.activation_date) activationField.val(sim.activation_date).change();
                if (expiryField.length && sim.expiry_date)   expiryField.val(sim.expiry_date).change();
              }
            });

            // Show SIM form section so user can verify/edit
            $('#sim-form-row').show();

            let meta = [];
            if (response.organization) meta.push('Org: <strong>' + response.organization + '</strong>');
            if (response.plan_status)  meta.push('Plan: <strong>' + response.plan_status + '</strong>');
            $('#sim-meta').html(meta.join(' &nbsp;·&nbsp; '));
            $('#sim-profiles-container').show();
          }
        },
        error: function(xhr) {
          $('#device-extract-progress').hide();
          // A poor-quality image or missing IMEI/ICCID blocks the process.
          window.verificationState.device_verified = false;
          let errorMsg = 'Failed to scan device label.';
          if (xhr.responseJSON && xhr.responseJSON.error) errorMsg = xhr.responseJSON.error;
          $('#device-error-message').text(errorMsg);
          $('#device-extract-error').show();
        }
      });
    });

    // ─── Number Plate Verification ─────────────────────────────────────
    $('#verify-plate-btn').on('click', function() {
      const plateFile = $('#plate_file')[0].files[0];
      const expectedRegNo = $('#certificate-details-form input[name="vehicle_registration_no"]').val().trim();

      // Reset alerts
      $('#plate-verify-success, #plate-verify-error').hide();
      $('#plate-verify-progress').show();

      // Validation
      if (!plateFile) {
        $('#plate-verify-progress').hide();
        $('#plate-error-message').text('Please select a number plate image first.');
        $('#plate-verify-error').show();
        return;
      }
      if (!expectedRegNo) {
        $('#plate-verify-progress').hide();
        $('#plate-error-message').text('Vehicle Registration No is empty. Please upload the RC document first or fill it in manually.');
        $('#plate-verify-error').show();
        return;
      }

      const formData = new FormData();
      formData.append('plate_file', plateFile);
      formData.append('expected_reg_no', expectedRegNo);
      formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

      $.ajax({
        url: '/user/certificate/{{ $device->id }}/verify-plate',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          $('#plate-verify-progress').hide();
          if (response.matched) {
            window.verificationState.plate_verified = true;
            $('#plate-success-message').html(
              'Plate <strong>' + response.detected + '</strong> matches the RC registration number.'
            );
            $('#plate-verify-success').show();
            showBlockingError([]); // clear any prior block
          } else {
            window.verificationState.plate_verified = false;
            $('#plate-error-message').html(response.message || 'Plate does not match.');
            $('#plate-verify-error').show();
          }
        },
        error: function(xhr) {
          $('#plate-verify-progress').hide();
          window.verificationState.plate_verified = false;
          let errorMsg = 'Verification failed.';
          if (xhr.responseJSON) {
            if (xhr.responseJSON.detected && xhr.responseJSON.expected) {
              errorMsg = 'Plate mismatch! RC says <strong>' + xhr.responseJSON.expected
                       + '</strong>, but the photo shows <strong>' + xhr.responseJSON.detected + '</strong>.';
            } else {
              errorMsg = xhr.responseJSON.error || xhr.responseJSON.message || errorMsg;
            }
          }
          $('#plate-error-message').html(errorMsg);
          $('#plate-verify-error').show();
        }
      });
    });

    // ─── Keep new-section mirror fields in sync with their canonical inputs ──
    function syncOwnerRegMirror() {
      var v = $('#certificate-details-form input[name="vehicle_registration_no"]').val() || '';
      $('#owner_vehicle_reg_display').val(v);
    }
    function syncDeviceIccidMirror() {
      var v = $('#vltd_icc_id_input').val() || '';
      $('#device_iccid_display').val(v);
    }
    $('#certificate-details-form input[name="vehicle_registration_no"]')
      .on('input change', syncOwnerRegMirror);
    $('#vltd_icc_id_input').on('input change', syncDeviceIccidMirror);
    // Initial sync on load
    syncOwnerRegMirror();
    syncDeviceIccidMirror();

    // ─── Preview Certificate (opens generated PDF in a new tab) ─────────────
    $('#preview-cert-btn').on('click', function() {
      var form = document.getElementById('certificate-details-form');

      // Use the browser's native required-field validation before previewing.
      if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
        return;
      }

      // Check verification state — don't allow preview if verification failed
      const issues = [];
      const state = window.verificationState;
      if (state.plate_verified === false) {
        issues.push('Number plate verification <strong>failed</strong> — the plate photo does not match the RC registration number. Re-upload the correct plate photo or fix the registration number.');
      }
      if (state.device_verified === false) {
        issues.push('Device IMEI verification <strong>failed</strong> — the IMEI extracted from the device label does not match the device\'s stored IMEI. Upload the correct device label or contact your administrator.');
      }
      if (state.rc_extracted === false) {
        issues.push('RC document is <strong>incomplete</strong> — one or more mandatory fields could not be extracted. Please upload a clear and readable RC image until all required fields are detected.');
      }

      if (issues.length > 0) {
        showBlockingError(issues);
        return;
      }

      var originalAction = form.getAttribute('action');
      var originalTarget = form.getAttribute('target');
      form.setAttribute('action', '/user/device/{{ $device->id }}/certificate/preview');
      form.setAttribute('target', '_blank');
      form.submit();
      // Restore so the normal Save flow is unaffected.
      form.setAttribute('action', originalAction);
      if (originalTarget) { form.setAttribute('target', originalTarget); }
      else { form.removeAttribute('target'); }
    });
  });
</script>
