<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>VLTD Fitment Certificate</title>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
.page { width: 90%; margin: 0 auto; padding: 20px; border: 1px solid #000; }
.header { display: flex; justify-content: space-between; align-items: flex-start; }
.cert-title { font-weight: bold; font-size: 16px; }
.subtitle { font-size: 11px; }
.qr { width: 90px; height: 90px; border: 1px solid #000; }
.to { margin-top: 12px; }
.fitment-row { display: flex; justify-content: space-between; margin-top: 8px; }
.subject { margin-top: 8px; }
.body { margin-top: 12px; line-height: 1.6; }
.details { margin-top: 8px; }
.box { border: 1px solid #000; padding: 6px; margin-top: 8px; }
.footer { margin-top: 18px; }
.bold { font-weight: bold; }
.underline { text-decoration: underline; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <div>
      <div class="cert-title">VLTD FITMENT CERTIFICATE</div>
      <div class="subtitle">(Generated online By JSD Electronics)</div>
    </div>
    <div class="qr">
      @if(!empty($qr_image))
        <img src="{{ $qr_image }}" style="width:90px;height:90px;" />
      @endif
    </div>
  </div>

  <!-- <div class="to">
    <div>To,</div>
    <div>The Registering Authority</div>
    <div>Transport Department</div>
    <div class="bold">{{ $authority_city }}</div>
  </div> -->

  <div class="fitment-row">
    <div></div>
    <div><span class="bold">VLTD Fitment date :</span> {{ $fitment_date }}</div>
  </div>

  <div class="subject">
    <span class="bold">Subject:</span> Installation of VLTD Serial no: <span class="bold underline">{{ $vltd_serial_no }}</span> in the vehicle registration no : <span class="bold underline">{{ $vehicle_registration_no }}</span>
  </div>

  <div class="body">
    Dear Sir,<br>
    It is to inform you that Mr/Ms. <span class="bold">{{ $holder_name }}</span> is fitted with VLTD make: <span class="bold">{{ $vltd_make }}</span>, Model: <span class="bold">{{ $vltd_model }}</span> at our retrofitment center in his/her vehicle registration no : <span class="bold">{{ $vehicle_registration_no }}</span>,<br>
    Chassis No: <span class="bold">{{ $chassis_no }}</span>, Engine No <span class="bold">{{ $engine_no }}</span>, Color: <span class="bold">{{ $color }}</span>, Vehicle Model: <span class="bold">{{ $vehicle_model }}</span>.<br>
    Our retro-fitment center is approved by state Government Transport Department for fitment of Vehicle Location Tracking Device.<br>
    According to ARAI TAC/COP No : <span class="bold">{{ $arai_tac }}</span> Dated <span class="bold">{{ $arai_date }}</span><br>
  </div>

  <div class="details">
    The details of VLTD shown below :
    <div class="box">
      VLTD Serial No: <span class="bold">{{ $vltd_serial_no }}</span><br>
      VLTD IMEI No: <span class="bold">{{ $imei }}</span><br>
      VLTD ICC ID: <span class="bold">{{ $vltd_icc_id }}</span><br>
      Service Provider:<br>
      1. <span class="bold">{{ $service_provider }}</span>
    </div>
  </div>

  <div class="footer">
    Thanking You<br><br>
    (Authorized Signatory)
  </div>
</div>
</body>
</html>
