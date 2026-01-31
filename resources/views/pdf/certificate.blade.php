<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>VLTD Fitment Certificate</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
    }

    .page {
      width: 90%;
      margin: 0 auto;
      padding: 20px;
      border: 1px solid #000;
    }

    .header {
      position: relative;
      margin-bottom: 6px;
    }

    .title-area {
      text-align: center;
    }

    .cert-title {
      font-weight: bold;
      font-size: 16px;
    }

    .subtitle {
      font-size: 11px;
    }

    .right {
      position: absolute;
      right: 0;
      top: 0;
      text-align: right;
    }

    .qr {
      width: 90px;
      height: 90px;
      border: 1px solid #000;
      display: inline-block;
    }

    .fitment {
      margin-top: 6px;
      font-size: 12px;
    }

    .to {
      margin-top: 12px;
    }

    .subject {
      margin-top: 8px;
    }

    .body {
      margin-top: 12px;
      line-height: 1.6;
    }

    .details {
      margin-top: 8px;
    }

    .box {
      border: 1px solid #000;
      padding: 6px;
      margin-top: 8px;
    }

    .footer {
      margin-top: 18px;
    }

    .bold {
      font-weight: bold;
    }

    .underline {
      text-decoration: underline;
    }

    .list {
      margin: 6px 0 0 0;
      padding: 0 0 0 12px;
    }

    .list li {
      margin: 2px 0;
    }
  </style>
</head>

<body>
  <div class="page">
    <div class="header">
      <div class="title-area">
        <div class="cert-title">INSTALLATION CERTIFICATE</div>
        <div class="subtitle">(Generated online By JSD Electronics)</div>
      </div>
      <div class="right">
        <div class="qr">
          @if(!empty($qr_image))
          <img src="{{ $qr_image }}" style="width:90px;height:90px;" />
          @endif
        </div>
        <div class="fitment"><span class="bold">VLTD Fitment date :</span> {{ $fitment_date }}</div>
      </div>
    </div>

    <!-- <div class="to">
    <div>To,</div>
    <div>The Registering Authority</div>
    <div>Transport Department</div>
    <div class="bold">{{ $authority_city }}</div>
  </div> -->



    <div class="body" style="margin-top:80px;">
      <div class="subject" style="margin-top:6px;">
        <span class="bold">Subject:</span> Installation of VLTD Serial no: <span class="bold underline">{{ $vltd_serial_no }}</span> in the vehicle registration no :
        <br><span class="bold underline">{{ $vehicle_registration_no }}</span>
      </div>
      Dear Sir,<br>
      It is to inform you that Mr/Ms. <span class="bold">{{ $holder_name }}</span> is fitted with VLTD make: <span class="bold">{{ $vltd_make }}</span>, Model: <span class="bold">{{ $vltd_model }}</span> at our retrofitment center in his/her vehicle registration no : <span class="bold">{{ $vehicle_registration_no }}</span>,<br>
      Chassis No: <span class="bold">{{ $chassis_no }}</span>, Engine No <span class="bold">{{ $engine_no }}</span>, Color: <span class="bold">{{ $color }}</span>, Vehicle Model: <span class="bold">{{ $vehicle_model }}</span>.<br>
      Our retro-fitment center is approved by state Government Transport Department for fitment of Vehicle Location Tracking Device.<br>
      According to ARAI TAC/COP No : <span class="bold">AS9076</span> Dated <span class="bold">08-12-2025</span><br>
    </div>

    <div class="details">
      The details of VLTD shown below :

      VLTD Serial No: <span class="bold">{{ $vltd_serial_no }}</span><br>
      VLTD IMEI No: <span class="bold">{{ $imei }}</span><br>
      VLTD ICC ID: <span class="bold">{{ $vltd_icc_id }}</span><br>
      Service Providers:
      <div class="box">
        @php
        $providers = isset($service_providers) ? (is_array($service_providers) ? $service_providers : [$service_providers]) : [];
        @endphp
        <ol class="list"style="margin-left:10px;">
          @foreach($providers as $p)
          <li><span class="bold">{{ $p }}</span></li>
          @endforeach
        </ol>
      </div>
    </div>

    <div class="footer">
      Thanking You<br>
      (Authorized Signatory)<br>
      Fitment Center Name: <b>JSD ELECTRONICS INDIA PVT LTD</b>
    </div>
  </div>
</body>

</html>