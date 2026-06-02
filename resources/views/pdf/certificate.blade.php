<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>VLTD Fitment Certificate</title>
  <style>
    @page { margin: 10px 14px; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 9px;
      color: #1a1a1a;
    }

    .page {
      border: 2px solid #76CF1C;
      padding: 0;
    }

    /* ── Header ───────────────────────────────────────────── */
    .header {
      background: #76CF1C;
      color: #ffffff;
      padding: 7px 12px;
    }
    .header table { width: 100%; border-collapse: collapse; }
    .company {
      font-size: 14px;
      font-weight: bold;
      letter-spacing: 0.4px;
    }
    .cert-title {
      font-size: 9.5px;
      margin-top: 2px;
      color: #eafad9;
    }
    .qr-cell { text-align: right; width: 66px; vertical-align: top; }
    .qr {
      width: 58px; height: 58px;
      background: #fff; border: 2px solid #fff;
    }

    .meta-bar {
      background: #eef7e0;
      border-bottom: 1px solid #cfe3b5;
      padding: 4px 12px;
      font-size: 9px;
    }
    .meta-bar table { width: 100%; }
    .meta-bar .bold { font-weight: bold; }

    .content { padding: 8px 12px 6px 12px; }

    .intro {
      line-height: 1.35;
      margin-bottom: 6px;
      text-align: justify;
    }

    /* ── Section ──────────────────────────────────────────── */
    .section { margin-top: 6px; }
    .section-title {
      background: #76CF1C;
      color: #fff;
      font-weight: bold;
      font-size: 9.5px;
      padding: 3px 8px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    table.detail {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #cfe3b5;
      border-top: none;
    }
    table.detail td {
      border: 1px solid #cfe3b5;
      padding: 3px 7px;
      vertical-align: top;
    }
    td.label {
      background: #eef7e0;
      font-weight: bold;
      width: 22%;
      color: #3f6f0e;
    }
    td.value { width: 28%; }

    /* ── Images ───────────────────────────────────────────── */
    table.images { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.images td {
      text-align: center;
      vertical-align: middle;
      padding: 8px;
      border: 1px solid #cfe3b5;
    }
    .img-frame {
      width: 100%;
      height: 200px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    .img-frame img {
      max-width: 95%;
      max-height: 195px;
      object-fit: contain;
    }
    .img-cap {
      font-size: 9px;
      font-weight: bold;
      color: #3f6f0e;
      margin-top: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .img-missing {
      color: #9aa7b4;
      font-size: 9px;
      font-style: italic;
    }

    /* ── SIM table ────────────────────────────────────────── */
    table.sim { width: 100%; border-collapse: collapse; border: 1px solid #cfe3b5; border-top: none; }
    table.sim th, table.sim td { border: 1px solid #cfe3b5; padding: 3px 7px; text-align: left; font-size: 9px; }
    table.sim th { background: #eef7e0; color: #3f6f0e; }

    /* ── Footer ───────────────────────────────────────────── */
    .footer {
      margin-top: 8px;
      padding: 0 4px;
    }
    .footer table { width: 100%; }
    .sign-box { text-align: right; vertical-align: bottom; }
    .sign-line { border-top: 1px solid #1a1a1a; width: 160px; margin-left: auto; padding-top: 2px; }
    .note { margin-top: 7px; font-size: 8px; color: #6b7785; text-align: center; }
    .bold { font-weight: bold; }
    .u { text-decoration: underline; }
  </style>
</head>

<body>
  @php
    // Resolve service provider (string or array form)
    $provider = $service_provider ?? null;
    if (!$provider && isset($service_providers)) {
      $provider = is_array($service_providers) ? ($service_providers[0] ?? null) : $service_providers;
    }
    // Prefer explicit device-detail values, fall back to canonical fields
    $dImei  = ($device_imei  ?? null) ?: ($imei ?? '');
    $dIccid = ($device_iccid ?? null) ?: ($vltd_icc_id ?? '');
    $dModel = ($device_model ?? null) ?: ($vltd_model ?? '');
  @endphp

  <div class="page">

    <!-- Header -->
    <div class="header">
      <table>
        <tr>
          <td>
            <div class="company">JSD ELECTRONICS INDIA PVT LTD</div>
            <div class="cert-title">VEHICLE LOCATION TRACKING DEVICE (VLTD) FITMENT CERTIFICATE</div>
          </td>
          <td class="qr-cell">
            @if(!empty($qr_image))
              <img src="{{ $qr_image }}" class="qr" />
            @endif
          </td>
        </tr>
      </table>
    </div>

    <!-- Meta bar -->
    <div class="meta-bar">
      <table>
        <tr>
          <td><span class="bold">Vehicle Reg. No:</span> {{ $vehicle_registration_no ?? '—' }}</td>
          <td style="text-align:center;"><span class="bold">Fitment Date:</span> {{ $fitment_date ?? '—' }}</td>
          <td style="text-align:right;"><span class="bold">Issued On:</span> {{ $issued_date ?? '' }}</td>
        </tr>
      </table>
    </div>

    <div class="content">

      <div class="intro">
        This is to certify that <span class="bold">{{ $owner_name ?? $holder_name ?? '—' }}</span> has been fitted with a
        Vehicle Location Tracking Device — Make <span class="bold">{{ $vltd_make ?? '—' }}</span>,
        Model <span class="bold">{{ $dModel ?: '—' }}</span>, Serial No <span class="bold u">{{ $vltd_serial_no ?? '—' }}</span> —
        in Vehicle Registration No <span class="bold u">{{ $vehicle_registration_no ?? '—' }}</span> at our authorized
        retro-fitment centre, in accordance with ARAI TAC/COP No <span class="bold">{{ $arai_tac ?? 'AS9076' }}</span>
        dated <span class="bold">{{ $arai_date ?? '08-12-2025' }}</span>.
      </div>

      <!-- Device Details -->
      <div class="section">
        <div class="section-title">Device Details</div>
        <table class="detail">
          <tr>
            <td class="label">IMEI Number</td><td class="value">{{ $dImei ?: '—' }}</td>
            <td class="label">ICCID Number</td><td class="value">{{ $dIccid ?: '—' }}</td>
          </tr>
          <tr>
            <td class="label">Device Model</td><td class="value">{{ $dModel ?: '—' }}</td>
            <td class="label">VLTD Make</td><td class="value">{{ $vltd_make ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">VLTD Serial No</td><td class="value">{{ $vltd_serial_no ?? '—' }}</td>
            <td class="label">Firmware Version</td><td class="value">{{ $firmware_version ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Service Provider</td><td class="value">{{ $provider ?? '—' }}</td>
            <td class="label">ARAI TAC/COP</td><td class="value">{{ $arai_tac ?? '—' }}</td>
          </tr>
        </table>
      </div>

      @if(!empty($sim1_operator) || !empty($sim1_msisdn) || !empty($sim2_operator) || !empty($sim2_msisdn))
      <!-- SIM Profile Details -->
      <div class="section">
        <div class="section-title">SIM Profile Details</div>
        <table class="sim">
          <tr><th>Slot</th><th>Operator</th><th>MSISDN</th><th>Activation Date</th><th>Expiry Date</th></tr>
          @if(!empty($sim1_operator) || !empty($sim1_msisdn))
          <tr><td>SIM 1</td><td>{{ $sim1_operator ?? '—' }}</td><td>{{ $sim1_msisdn ?? '—' }}</td><td>{{ $sim1_activation_date ?? '—' }}</td><td>{{ $sim1_expiry_date ?? '—' }}</td></tr>
          @endif
          @if(!empty($sim2_operator) || !empty($sim2_msisdn))
          <tr><td>SIM 2</td><td>{{ $sim2_operator ?? '—' }}</td><td>{{ $sim2_msisdn ?? '—' }}</td><td>{{ $sim2_activation_date ?? '—' }}</td><td>{{ $sim2_expiry_date ?? '—' }}</td></tr>
          @endif
        </table>
      </div>
      @endif

      <!-- Vehicle Details -->
      <div class="section">
        <div class="section-title">Vehicle Details</div>
        <table class="detail">
          <tr>
            <td class="label">Registration No</td><td class="value">{{ $vehicle_registration_no ?? '—' }}</td>
            <td class="label">Vehicle Model</td><td class="value">{{ $vehicle_model ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Chassis No</td><td class="value">{{ $chassis_no ?? '—' }}</td>
            <td class="label">Engine No</td><td class="value">{{ $engine_no ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Color</td><td class="value">{{ $color ?? '—' }}</td>
            <td class="label">Vehicle Class</td><td class="value">{{ $vehicle_class ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Fuel Type</td><td class="value">{{ $fuel_type ?? '—' }}</td>
            <td class="label">Authority City</td><td class="value">{{ $authority_city ?? '—' }}</td>
          </tr>
        </table>
      </div>

      <!-- Vendor Details -->
      <div class="section">
        <div class="section-title">Vendor Details</div>
        <table class="detail">
          <tr>
            <td class="label">Vendor Name</td><td class="value">{{ $vendor_name ?? '—' }}</td>
            <td class="label">Vendor ID</td><td class="value">{{ $vendor_id ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Contact Number</td><td class="value">{{ $vendor_contact ?? '—' }}</td>
            <td class="label">Email</td><td class="value">{{ $vendor_email ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">GST Number</td><td class="value">{{ $vendor_gst ?? '—' }}</td>
            <td class="label">Address</td><td class="value">{{ $vendor_address ?? '—' }}</td>
          </tr>
        </table>
      </div>

      <!-- Owner Details -->
      <div class="section">
        <div class="section-title">Owner Details</div>
        <table class="detail">
          <tr>
            <td class="label">Owner Name</td><td class="value">{{ $owner_name ?? '—' }}</td>
            <td class="label">Mobile Number</td><td class="value">{{ $owner_mobile ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Email</td><td class="value">{{ $owner_email ?? '—' }}</td>
            <td class="label">Vehicle Reg. No</td><td class="value">{{ $vehicle_registration_no ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Address</td><td colspan="3">{{ $owner_address ?? '—' }}</td>
          </tr>
        </table>
      </div>

      <!-- Fitter Details -->
      <div class="section">
        <div class="section-title">Fitter Details</div>
        <table class="detail">
          <tr>
            <td class="label">Company</td><td class="value">{{ $fitter_company ?? '—' }}</td>
            <td class="label">Email</td><td class="value">{{ $fitter_email ?? '—' }}</td>
          </tr>
          <tr>
            <td class="label">Contact</td><td class="value">{{ $fitter_contact ?? '—' }}</td>
            <td class="label"></td><td class="value"></td>
          </tr>
          <tr>
            <td class="label">Address</td><td colspan="3">{{ $fitter_address ?? '—' }}</td>
          </tr>
        </table>
      </div>

      <!-- Image Attachments (sourced from the images uploaded during OCR) -->
      <div class="section">
        <div class="section-title">Supporting Images</div>
        <table class="images">
          <tr>
            <td style="width:25%;">
              <div class="img-frame">
                @if(!empty($device_image_uri))<img src="{{ $device_image_uri }}" />@else<div class="img-missing">Not provided</div>@endif
              </div>
              <div class="img-cap">Device Image</div>
            </td>
            <td style="width:25%;">
              <div class="img-frame">
                @if(!empty($rc_front_image_uri))<img src="{{ $rc_front_image_uri }}" />@else<div class="img-missing">Not provided</div>@endif
              </div>
              <div class="img-cap">RC Front Page</div>
            </td>
            <td style="width:25%;">
              <div class="img-frame">
                @if(!empty($rc_back_image_uri))<img src="{{ $rc_back_image_uri }}" />@else<div class="img-missing">Not provided</div>@endif
              </div>
              <div class="img-cap">RC Back Page</div>
            </td>
            <td style="width:25%;">
              <div class="img-frame">
                @if(!empty($plate_image_uri))<img src="{{ $plate_image_uri }}" />@else<div class="img-missing">Not provided</div>@endif
              </div>
              <div class="img-cap">Number Plate Image</div>
            </td>
          </tr>
        </table>
      </div>

      <!-- Footer -->
      <div class="footer">
        <table>
          <tr>
            <td style="vertical-align:bottom; text-align:center;">
              Our retro-fitment centre is approved by JSD Electronics India Pvt Ltd for fitment of Vehicle Location Tracking Devices.
            </td>
          </tr>
        </table>
        <div class="note">This is a system-generated certificate issued online by JSD Electronics India Pvt Ltd.</div>
      </div>

    </div>
  </div>
</body>

</html>
