@extends('layouts.apps')

@push('styles')
<style>
  .sku-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .sku-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .sku-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .sku-card .c_title .brand-gps-mark { display: inline-flex; width: 22px; height: 22px; color: #22c55e; }
  .sku-card .c_title .brand-gps-mark svg { width: 100%; height: 100%; fill: currentColor; }
  .sku-card .c_content { padding: 20px 24px 24px; }

  .sku-table-wrap { border: 1px solid rgba(148,163,184,.18); border-radius: 12px; overflow: hidden; }
  .sku-table { width: 100%; margin: 0; border-collapse: collapse; }
  .sku-table thead th {
    background: #101a2b; color: #fff; font-size: 11.5px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; padding: 14px 16px; border: none; white-space: nowrap;
  }
  .sku-table tbody td { padding: 14px 16px; font-size: 13.5px; vertical-align: middle; border-top: 1px solid rgba(148,163,184,.14); }
  .sku-table tbody tr:hover { background: rgba(34,197,94,.05); }
  .sku-table tbody tr:first-child td { border-top: none; }
  .sku-table .sku-code-cell { font-weight: 700; color: #16a34a; font-family: monospace; letter-spacing: .3px; }
  .sku-status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; color: #fff; font-size: 11.5px; font-weight: 700; letter-spacing: .2px; }
  .sku-table .sku-empty-row td { text-align: center; color: #94a3b8; padding: 32px 16px; }
  .sku-actions { display: flex; gap: 8px; }
  .sku-actions form { margin: 0; }
  .sku-actions .btn {
    appearance: none; -webkit-appearance: none; box-sizing: border-box;
    width: 90px; height: 32px; margin: 0; border-radius: 7px;
    font-family: inherit; font-size: 12.5px; font-weight: 600; line-height: 1;
    padding: 0 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  }
  .sku-actions .btn-view { background: rgba(59,130,246,.1); color: #2563eb; border: 1px solid rgba(59,130,246,.28); }
  .sku-actions .btn-view:hover { background: rgba(59,130,246,.18); color: #2563eb; }
  .sku-actions .btn-edit { background: rgba(34,197,94,.12); color: #16a34a; border: 1px solid rgba(34,197,94,.3); }
  .sku-actions .btn-edit:hover { background: rgba(34,197,94,.2); color: #16a34a; }
  .sku-actions .btn-delete { background: rgba(239,68,68,.1); color: #ef4444; border: 1px solid rgba(239,68,68,.28); }
  .sku-actions .btn-delete:hover { background: rgba(239,68,68,.18); color: #ef4444; }

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

  /* Header action button */
  .sku-btn-create {
    background: linear-gradient(135deg,#22c55e,#16a34a); border: none; border-radius: 8px;
    padding: 0 18px; height: 38px; color: #fff; font-size: 13px; font-weight: 700;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 12px rgba(34,197,94,.3); cursor: pointer; transition: all .2s; white-space: nowrap;
    text-decoration: none;
  }
  .sku-btn-create:hover, .sku-btn-create:focus { transform: translateY(-1px); filter: brightness(1.08); color: #fff; text-decoration: none; }

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
        <span class="bc-item active">My SKUs</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel sku-card">
          <div class="c_title">
            <div class="row bgx-title-container" style="display:flex;align-items:center;">
              <div class="col-lg-8"><h2>@include('partials.brand-gps-mark') My SKUs</h2></div>
              <div class="col-lg-4 text-right">
                <a href="/skus/create" class="sku-btn-create"><i class="fa fa-plus"></i> Create SKU</a>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="c_content">

            <p class="kyc-intro" style="color:#5b6b82;font-size:13px;margin:-4px 0 18px;">
              SKUs are reviewed by Sales (Model/Vendor ID) and then NPD before they can be used to raise a Purchase Order.
            </p>

            @include('partials.gps-inline-alerts')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($mesError)<div class="alert alert-warning">Could not reach MES to load your SKUs. Please try again shortly.</div>@endif

            <div class="sku-table-wrap">
            <table class="sku-table">
              <thead>
                <tr>
                  <th>SKU Code</th>
                  <th>Device Category</th>
                  <th>eSIM Make</th>
                  <th>Firmware</th>
                  <th>Status</th>
                  <th>Remarks</th>
                  <th>Submitted</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($skus as $sku)
                @php
                  $bg = ['PendingSales' => '#f59e0b', 'PendingNpd' => '#3b82f6', 'Completed' => '#22c55e', 'Rejected' => '#ef4444'][$sku['status'] ?? ''] ?? '#64748b';
                  $statusLabel = ['PendingSales' => 'Pending Sales', 'PendingNpd' => 'Pending NPD'][$sku['status'] ?? ''] ?? ($sku['status'] ?? '-');
                  $isPending = ($sku['status'] ?? '') === 'PendingSales';
                  $canResubmit = ($sku['status'] ?? '') === 'Rejected' && !empty($sku['resubmissionAllowed']);
                  $canEdit = $isPending || $canResubmit;
                  $remarks = ($sku['rejectedAtStage'] ?? '') === 'sales' ? ($sku['salesRemarks'] ?? '-') : ($sku['npdRemarks'] ?? '-');
                @endphp
                <tr>
                  <td class="sku-code-cell">{{ $sku['skuCode'] ?? '-' }}</td>
                  <td>{{ $sku['deviceCategory']['name'] ?? '-' }}</td>
                  <td>{{ $sku['esim']['make'] ?? '-' }}</td>
                  <td>{{ $sku['firmware']['name'] ?? '-' }}</td>
                  <td><span class="sku-status-badge" style="background:{{ $bg }};">{{ $statusLabel }}</span></td>
                  <td>{{ $remarks }}</td>
                  <td>{{ !empty($sku['createdAt']) ? \App\Helper\CommonHelper::getDateAsTimeZone($sku['createdAt'], 'd-M-Y H:i') : '-' }}</td>
                  <td>
                    <div class="sku-actions">
                      <a href="/skus/{{ $sku['_id'] }}" class="btn btn-view"><i class="fa fa-eye"></i> View</a>
                      @if($canEdit)
                        <a href="/skus/{{ $sku['_id'] }}/edit" class="btn btn-edit"><i class="fa fa-pencil"></i> Edit</a>
                      @endif
                      @if($isPending)
                        <form method="POST" action="/skus/{{ $sku['_id'] }}" onsubmit="return confirm('Delete {{ $sku['skuCode'] ?? 'this SKU request' }}? This cannot be undone.');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-delete"><i class="fa fa-trash"></i> Delete</button>
                        </form>
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr class="sku-empty-row"><td colspan="8">No SKUs yet. Create one to start raising Purchase Orders.</td></tr>
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
