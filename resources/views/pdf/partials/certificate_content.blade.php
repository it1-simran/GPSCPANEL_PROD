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
        <td style="text-align:center;"><span class="bold">Fitment Date:</span> {{ !empty($fitment_date) ? \Carbon\Carbon::parse($fitment_date)->format('d-M-Y') : '—' }}</td>
        <td style="text-align:right;"><span class="bold">Issued On:</span> {{ $issued_date ?? '' }}</td>
      </tr>
    </table>
  </div>

  <div class="content">

    <div class="intro">
      This is to certify that the vehicle bearing Registration No
      <span class="bold u">{{ $vehicle_registration_no ?? '—' }}</span>, owned by
      <span class="bold">{{ $owner_name ?? $holder_name ?? '—' }}</span>, has been fitted with a
      Vehicle Location Tracking Device — Make <span class="bold">{{ $vltd_make ?? '—' }}</span>,
      Model <span class="bold">{{ $dModel ?: '—' }}</span>, Serial No <span class="bold u">{{ $vltd_serial_no ?? '—' }}</span> —
      at our authorized retro-fitment centre, in accordance with ARAI TAC/COP No
      <span class="bold">{{ $arai_tac ?? 'AS9076' }}</span>
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
        <!-- <tr>
          <td class="label">VLTD Serial No</td><td class="value">{{ $vltd_serial_no ?? '—' }}</td>
          <td class="label">Firmware Version</td><td class="value">{{ $firmware_version ?? '—' }}</td>
        </tr> -->
        <tr>
          <td class="label">Service Provider</td><td class="value">{{ $provider ?? '—' }}</td>
          <td class="label">ARAI TAC/COP</td><td class="value">{{ $arai_tac ?? '—' }}</td>
        </tr>
      </table>
    </div>

    @if(!empty($sim1_operator) || !empty($sim1_msisdn) || !empty($sim2_operator) || !empty($sim2_msisdn) || !empty($organization_name) || !empty($plan_status))
    <!-- SIM & Plan Information -->
    <div class="section">
      <div class="section-title">eSIM Details</div>

      <!-- Plan Level Details -->
      <table class="detail">
        <tr>
          <td class="label">Organization</td><td class="value">{{ $organization_name ?? '—' }}</td>
          <td class="label">Plan Status</td><td class="value">{{ $plan_status ?? '—' }}</td>
        </tr>
        <tr>
          <td class="label">Activation Date</td><td class="value">{{ $sim1_activation_date ?? '—' }}</td>
          <td class="label">Expiry Date</td><td class="value">{{ $sim1_expiry_date ?? '—' }}</td>
        </tr>
      </table>

      <!-- SIM Profile Details Table -->
      <div style="margin-top: 6px;">
        <table class="sim">
          <tr>
            <th>Slot</th>
            <th>Operator</th>
            <th>MSISDN</th>
            <th>IMSI</th>
            <th>Status</th>
          </tr>
          @if(!empty($sim1_operator) || !empty($sim1_msisdn) || !empty($sim1_imsi) || !empty($sim1_profile_status))
          <tr>
            <td><strong>SIM 1</strong></td>
            <td>{{ !empty($sim1_operator) && $sim1_operator !== '—' ? $sim1_operator : '—' }}</td>
            <td class="sim-id">{{ !empty($sim1_msisdn) && $sim1_msisdn !== '—' ? $sim1_msisdn : '—' }}</td>
            <td class="sim-id">{{ !empty($sim1_imsi) && $sim1_imsi !== '—' ? $sim1_imsi : '—' }}</td>
            <td>{{ !empty($sim1_profile_status) && $sim1_profile_status !== '—' ? $sim1_profile_status : '—' }}</td>
          </tr>
          @endif
          @if(!empty($sim2_operator) || !empty($sim2_msisdn) || !empty($sim2_imsi) || !empty($sim2_profile_status))
          <tr>
            <td><strong>SIM 2</strong></td>
            <td>{{ !empty($sim2_operator) && $sim2_operator !== '—' ? $sim2_operator : '—' }}</td>
            <td class="sim-id">{{ !empty($sim2_msisdn) && $sim2_msisdn !== '—' ? $sim2_msisdn : '—' }}</td>
            <td class="sim-id">{{ !empty($sim2_imsi) && $sim2_imsi !== '—' ? $sim2_imsi : '—' }}</td>
            <td>{{ !empty($sim2_profile_status) && $sim2_profile_status !== '—' ? $sim2_profile_status : '—' }}</td>
          </tr>
          @endif
        </table>
      </div>
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
