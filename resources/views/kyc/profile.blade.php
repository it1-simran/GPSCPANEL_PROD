@extends('layouts.apps')

@push('styles')
<style>
  .kyc-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .kyc-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .kyc-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .kyc-card .c_title h2 i { color: #22c55e; }
  .kyc-card .c_title .brand-gps-mark { display: inline-flex; width: 22px; height: 22px; color: #22c55e; }
  .kyc-card .c_title .brand-gps-mark svg { width: 100%; height: 100%; fill: currentColor; }
  .kyc-card .c_content { padding: 20px 24px 24px; }
  .kyc-intro { color: #5b6b82; font-size: 13px; margin: -4px 0 18px; }

  .kyc-status-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; color: #fff; font-size: 12.5px; font-weight: 700; letter-spacing: .3px; }

  .kyc-section { background: rgba(148,163,184,.045); border: 1px solid rgba(148,163,184,.14); border-radius: 12px; padding: 18px 18px 2px; margin-bottom: 18px; }
  .kyc-section-title { display: flex; align-items: center; gap: 9px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #475569; margin: 0 0 16px; }
  .kyc-section-title i { color: #22c55e; font-size: 14px; }

  .kyc-form .form-group { margin-bottom: 18px; }
  .kyc-form label.control-label { font-weight: 600; font-size: 12.5px; color: #334155; margin-bottom: 7px; display: block; letter-spacing: .2px; }
  .kyc-form .form-control { height: 44px; border-radius: 9px; font-size: 14px; border: 1px solid rgba(148,163,184,.35); box-shadow: none; transition: border-color .15s ease, box-shadow .15s ease; }
  .kyc-form textarea.form-control { height: auto; }
  .kyc-form .form-control:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.16); }
  .kyc-required { color: #ef4444; }

  .kyc-actions { display: flex; justify-content: flex-end; border-top: 1px solid rgba(148,163,184,.16); padding-top: 18px; margin-top: 4px; }
  .kyc-actions .btn { min-width: 170px; height: 44px; border-radius: 9px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
  .kyc-actions .btn-success { background: #22c55e; border-color: #22c55e; }
  .kyc-actions .btn-success:hover, .kyc-actions .btn-success:focus { background: #16a34a; border-color: #16a34a; }

  @media (max-width: 640px) {
    .kyc-card .c_content { padding: 16px; }
    .kyc-section { padding: 14px 14px 2px; }
  }

  /* Breadcrumb */
  .var-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .var-breadcrumb {
    display: inline-flex; align-items: center;
    background: #1e293b; border-radius: 50px;
    padding: 6px 18px 6px 8px; gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,.18);
  }
  .var-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #22c55e;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
  }
  .var-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
  .var-breadcrumb .bc-item { color: rgba(255,255,255,.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
  .var-breadcrumb .bc-sep { color: rgba(255,255,255,.35); margin: 0 8px; font-size: 12px; }
  .var-breadcrumb .bc-item.active { color: #22c55e; font-weight: 700; }
  @media (max-width: 480px) {
    .var-breadcrumb { padding: 4px 8px 4px 5px; }
    .var-breadcrumb .bc-home { width: 22px; height: 22px; }
    .var-breadcrumb .bc-item { font-size: 10px; }
  }
</style>
@endpush

@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="var-breadcrumb-wrap">
      <nav class="var-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url($url_type) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">KYC Details</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel kyc-card">
          <div class="c_title">
            <div class="row bgx-title-container" style="display:flex;align-items:center;">
              <div class="col-lg-9"><h2>@include('partials.brand-gps-mark') Organization / KYC Details</h2></div>
              <div class="col-lg-3 text-right">
                @php
                  $status = $writer->kyc_status ?? 'NotSubmitted';
                  $badgeColor = [
                    'NotSubmitted' => '#94a3b8',
                    'SupportReviewPending' => '#f59e0b',
                    'Approved' => '#22c55e',
                    'Rejected' => '#ef4444',
                  ][$status] ?? '#94a3b8';
                @endphp
                <span class="kyc-status-badge" style="background:{{ $badgeColor }};">{{ $status }}</span>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>

          <div class="c_content">
            <p class="kyc-intro">
              Submit your organization's GST and business details for Accounts review. A Purchase Order can only be raised once your KYC is approved.
            </p>

            @include('partials.gps-inline-alerts')

            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
              <div class="alert alert-danger">
                <ul style="margin:0;padding-left:18px;">
                  @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
              </div>
            @endif

            @if($status === 'Approved')
              <div class="alert alert-success" style="border-radius:12px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <span><i class="fa fa-check-circle"></i> Your KYC is approved. You are eligible to raise Purchase Orders.</span>
                <button type="button" id="kycEditToggle" class="btn btn-success" style="border-radius:7px;"><i class="fa fa-pencil"></i> Edit Details</button>
              </div>
            @endif

            @if($status === 'Rejected' && $writer->kyc_rejection_reason)
              <div class="alert alert-danger" style="border-radius:12px;">
                <strong><i class="fa fa-times-circle"></i> Rejected:</strong> {{ $writer->kyc_rejection_reason }} — please correct the details below and resubmit.
              </div>
            @endif

            @if($status === 'SupportReviewPending')
              <div class="alert alert-warning" style="border-radius:12px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <span><i class="fa fa-clock-o"></i> Your KYC request is awaiting Accounts approval. You cannot raise Purchase Orders until it is approved.</span>
                <button type="button" id="kycEditToggle" class="btn btn-warning" style="border-radius:7px;"><i class="fa fa-pencil"></i> Edit Details</button>
              </div>
            @endif

            <div id="kycFormWrap" @if(in_array($status, ['SupportReviewPending', 'Approved'])) style="display:none;" @endif>
            <form method="POST" action="{{ route('kyc.store') }}" enctype="multipart/form-data" class="kyc-form">
              @csrf

              <div class="kyc-section">
                <div class="kyc-section-title"><i class="fa fa-building-o"></i> Organization Details</div>

                <div class="form-group">
                  <label for="organization_name" class="control-label">Organization Name <span class="kyc-required">*</span></label>
                  <input type="text" class="form-control" id="organization_name" name="organization_name"
                    value="{{ old('organization_name', $writer->organization_name) }}" required maxlength="255">
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="gstin" class="control-label">GSTIN <span class="kyc-required">*</span></label>
                      <input type="text" class="form-control" id="gstin" name="gstin" style="text-transform:uppercase;"
                        value="{{ old('gstin', $writer->gstin) }}" required maxlength="15" placeholder="e.g. 22AAAAA0000A1Z5">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="pan_number" class="control-label">PAN Number</label>
                      <input type="text" class="form-control" id="pan_number" name="pan_number" style="text-transform:uppercase;"
                        value="{{ old('pan_number', $writer->pan_number) }}" maxlength="10" placeholder="e.g. AAAAA0000A">
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="organization_address" class="control-label">Organization Address <span class="kyc-required">*</span></label>
                  <textarea class="form-control" id="organization_address" name="organization_address" rows="3" required maxlength="1000">{{ old('organization_address', $writer->organization_address) }}</textarea>
                </div>

                <div class="form-group">
                  <label for="kyc_document" class="control-label">GST Certificate / Supporting Document (optional)</label>
                  <input type="file" class="form-control" id="kyc_document" name="kyc_document" accept=".pdf,.jpg,.jpeg,.png" style="height:auto;padding:8px 12px;">
                </div>
              </div>

              @if(in_array($status, ['SupportReviewPending', 'Approved']))
                <p class="kyc-intro" style="margin:-6px 0 14px;">Saving will send these details for Accounts review again.</p>
              @endif

              <div class="kyc-actions">
                @if(in_array($status, ['SupportReviewPending', 'Approved']))
                  <button type="button" id="kycEditCancel" class="btn btn-default" style="border-radius:9px; min-width:120px; height:44px; margin-right:10px;">Cancel</button>
                @endif
                <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> {{ in_array($status, ['SupportReviewPending', 'Approved']) ? 'Save & Resubmit' : 'Submit for Approval' }}</button>
              </div>
            </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.getElementById('kycFormWrap');
    var toggle = document.getElementById('kycEditToggle');
    var cancel = document.getElementById('kycEditCancel');
    if (toggle && wrap) {
      toggle.addEventListener('click', function () {
        wrap.style.display = 'block';
        toggle.closest('.alert').style.display = 'none';
        wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    }
    if (cancel && wrap) {
      cancel.addEventListener('click', function () {
        wrap.style.display = 'none';
        var alertBox = document.querySelector('.alert-success, .alert-warning');
        if (alertBox) { alertBox.style.display = 'flex'; }
      });
    }
  });
</script>
@endsection
