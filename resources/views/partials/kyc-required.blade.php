{{-- Blocking notice shown instead of a form when the account's KYC isn't approved yet.
     Expects: $url_type, $kycStatus, $action (e.g. "raise a Purchase Order" / "create a SKU") --}}
@extends('layouts.apps')

@push('styles')
<style>
  .kyc-block-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); max-width: 620px; margin: 0 auto; }
  .kyc-block-card .c_title { background: linear-gradient(90deg, rgba(239,68,68,.14), rgba(239,68,68,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .kyc-block-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .kyc-block-card .c_title .brand-gps-mark { display: inline-flex; width: 22px; height: 22px; color: #ef4444; }
  .kyc-block-card .c_title .brand-gps-mark svg { width: 100%; height: 100%; fill: currentColor; }
  .kyc-block-card .c_content { padding: 28px 24px; text-align: center; }
  .kyc-block-icon { width: 64px; height: 64px; border-radius: 50%; background: rgba(239,68,68,.1); color: #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; }
  .kyc-block-title { font-size: 17px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
  .kyc-block-text { color: #5b6b82; font-size: 13.5px; line-height: 1.6; margin-bottom: 22px; }
  .kyc-block-btn {
    background: linear-gradient(135deg,#22c55e,#16a34a); border: none; border-radius: 9px;
    padding: 0 24px; height: 44px; color: #fff; font-size: 14px; font-weight: 700;
    display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
    box-shadow: 0 4px 12px rgba(34,197,94,.3); transition: all .2s;
  }
  .kyc-block-btn:hover, .kyc-block-btn:focus { transform: translateY(-1px); filter: brightness(1.08); color: #fff; text-decoration: none; }
</style>
@endpush

@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel kyc-block-card">
          <div class="c_title">
            <h2>@include('partials.brand-gps-mark') KYC Required</h2>
          </div>
          <div class="c_content">
            <div class="kyc-block-icon"><i class="fa fa-id-card"></i></div>
            <div class="kyc-block-title">Complete your KYC to {{ $action }}</div>
            <p class="kyc-block-text">
              @if ($kycStatus === 'SupportReviewPending')
                Your KYC details are submitted and awaiting Accounts approval. You'll be able to {{ $action }} once it's approved.
              @elseif ($kycStatus === 'Rejected')
                Your KYC submission was rejected. Please review the reason and resubmit before you can {{ $action }}.
              @else
                You haven't submitted your organization's KYC (GSTIN, organization details) yet. It needs Accounts approval before you can {{ $action }}.
              @endif
            </p>
            <a href="/kyc" class="kyc-block-btn"><i class="fa fa-id-card"></i> Go to My KYC</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@endsection
