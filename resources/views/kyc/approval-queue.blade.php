@extends('layouts.apps')

@push('styles')
<style>
  .kyc-approvals-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .kyc-approvals-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .kyc-approvals-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .kyc-approvals-card .c_title .brand-gps-mark { display: inline-flex; width: 22px; height: 22px; color: #22c55e; }
  .kyc-approvals-card .c_title .brand-gps-mark svg { width: 100%; height: 100%; fill: currentColor; }
  .kyc-approvals-card .c_content { padding: 20px 24px 24px; }

  .kyc-approvals-table-wrap { border: 1px solid rgba(148,163,184,.18); border-radius: 12px; overflow: hidden; overflow-x: auto; }
  .kyc-approvals-table { width: 100%; margin: 0; border-collapse: collapse; }
  .kyc-approvals-table thead th {
    background: #101a2b; color: #fff; font-size: 11.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; padding: 14px 16px; border: none; white-space: nowrap;
  }
  .kyc-approvals-table td { vertical-align: middle; padding: 14px 16px; font-size: 13.5px; border-top: 1px solid rgba(148,163,184,.14); }
  .kyc-approvals-table tbody tr:first-child td { border-top: none; }
  .kyc-approvals-table tbody tr:hover { background: rgba(34,197,94,.05); }
  .kyc-reject-box { margin-top: 8px; }
  .kyc-approvals-table form { margin: 0; display: inline-block; }
  .kyc-approve-btn, .kyc-reject-btn {
    appearance: none; -webkit-appearance: none; box-sizing: border-box;
    width: 104px; height: 32px; margin: 0; border-radius: 7px;
    font-family: inherit; font-size: 12.5px; font-weight: 600; line-height: 1;
    padding: 0 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    border: 1px solid transparent;
  }
  .kyc-approve-btn { background: rgba(34,197,94,.12); color: #16a34a; border-color: rgba(34,197,94,.3); }
  .kyc-approve-btn:hover { background: rgba(34,197,94,.2); color: #16a34a; }
  .kyc-reject-btn { background: rgba(239,68,68,.1); color: #ef4444; border-color: rgba(239,68,68,.28); }
  .kyc-reject-btn:hover { background: rgba(239,68,68,.18); color: #ef4444; }

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
        <span class="bc-item active">KYC Approval Requests</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel kyc-approvals-card">
          <div class="c_title">
            <h2>@include('partials.brand-gps-mark') KYC Approval Requests</h2>
          </div>
          <div class="c_content">

            @include('partials.gps-inline-alerts')

            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info" style="border-radius:12px;">
              <i class="fa fa-info-circle"></i> KYC requests are now approved/rejected by the Accounts team in MES. This page is read-only — it lists accounts currently pending review.
            </div>

            <div class="kyc-approvals-table-wrap">
            <table class="kyc-approvals-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Organization</th>
                  <th>GSTIN</th>
                  <th>PAN</th>
                  <th>Address</th>
                  <th>Document</th>
                  <th>Submitted</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pendingRequests as $req)
                <tr>
                  <td>{{ $req->name }}</td>
                  <td>{{ $req->email }}</td>
                  <td>{{ $req->organization_name }}</td>
                  <td>{{ $req->gstin }}</td>
                  <td>{{ $req->pan_number ?: '-' }}</td>
                  <td>{{ $req->organization_address }}</td>
                  <td>
                    @if($req->kyc_document_path)
                      <span class="text-muted"><i class="fa fa-paperclip"></i> Uploaded</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>{{ $req->kyc_submitted_at ? $req->kyc_submitted_at->format('d-M-Y H:i') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No pending KYC requests.</td></tr>
                @endforelse
              </tbody>
            </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@endsection
