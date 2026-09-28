@extends('layouts.apps')

@push('styles')
<style>
  .sku-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .sku-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .sku-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .sku-card .c_title .brand-gps-mark { display: inline-flex; width: 22px; height: 22px; color: #22c55e; }
  .sku-card .c_title .brand-gps-mark svg { width: 100%; height: 100%; fill: currentColor; }
  .sku-card .c_content { padding: 20px 24px 24px; }
  .sku-status-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; color: #fff; font-size: 12.5px; font-weight: 700; letter-spacing: .3px; }

  .sku-section { background: rgba(148,163,184,.045); border: 1px solid rgba(148,163,184,.14); border-radius: 12px; padding: 18px; margin-bottom: 18px; }
  .sku-section-title { display: flex; align-items: center; gap: 9px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #475569; margin: 0 0 16px; }
  .sku-section-title i { color: #22c55e; font-size: 14px; }
  .sku-field-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
  .sku-field-value { font-size: 14px; font-weight: 600; color: #1e293b; margin-top: 4px; word-break: break-word; }
  .sku-detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 24px; }
  @media (max-width: 767px) { .sku-detail-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 480px) { .sku-detail-grid { grid-template-columns: 1fr; } }

  .sku-history-item { font-size: 12.5px; padding: 10px 0; border-top: 1px solid rgba(148,163,184,.14); }
  .sku-history-item:first-child { border-top: none; }

  .sku-timeline { list-style: none; margin: 0; padding: 0; }
  .sku-timeline li { position: relative; padding: 0 0 20px 28px; }
  .sku-timeline li:last-child { padding-bottom: 0; }
  .sku-timeline li::before {
    content: ''; position: absolute; left: 4px; top: 3px; width: 11px; height: 11px;
    border-radius: 50%; background: #fff; border: 2px solid #94a3b8; z-index: 1;
  }
  .sku-timeline li::after {
    content: ''; position: absolute; left: 9px; top: 14px; bottom: -6px; width: 2px; background: rgba(148,163,184,.25);
  }
  .sku-timeline li:last-child::after { display: none; }
  .sku-timeline li.is-approved::before { border-color: #22c55e; background: #22c55e; }
  .sku-timeline li.is-rejected::before { border-color: #ef4444; background: #ef4444; }
  .sku-timeline li.is-pending::before { border-color: #f59e0b; background: #f59e0b; }
  .sku-timeline li.is-upcoming .step-label { color: #94a3b8; font-weight: 600; }
  .sku-timeline .step-label { font-size: 13px; font-weight: 700; color: #1e293b; }
  .sku-timeline .step-meta { font-size: 11.5px; color: #94a3b8; margin-top: 1px; }
  .sku-timeline .step-remarks { font-size: 12px; color: #64748b; margin-top: 3px; font-style: italic; }

  .sku-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(148,163,184,.16); padding-top: 18px; margin-top: 4px; }
  .sku-actions .btn { border-radius: 9px; height: 44px; padding: 0 22px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }

  /* Breadcrumb */
  .var-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .var-breadcrumb { display: inline-flex; align-items: center; background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px; box-shadow: 0 4px 16px rgba(30,41,59,.18); }
  .var-breadcrumb .bc-home { width: 30px; height: 30px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; flex-shrink: 0; }
  .var-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
  .var-breadcrumb .bc-item { color: rgba(255,255,255,.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
  .var-breadcrumb .bc-sep { color: rgba(255,255,255,.35); margin: 0 8px; font-size: 12px; }
  .var-breadcrumb .bc-item.active { color: #22c55e; font-weight: 700; }
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
        <a href="/skus" class="bc-item">My SKUs</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">{{ $sku['skuCode'] ?? 'SKU' }}</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel sku-card">
          <div class="c_title">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
              <h2 style="margin:0;">@include('partials.brand-gps-mark') {{ $sku['skuCode'] ?? 'SKU' }}</h2>
              @php
                $bg = ['PendingSales' => '#f59e0b', 'PendingNpd' => '#3b82f6', 'Completed' => '#22c55e', 'Rejected' => '#ef4444'][$sku['status'] ?? ''] ?? '#64748b';
                $statusLabel = ['PendingSales' => 'Pending Sales', 'PendingNpd' => 'Pending NPD'][$sku['status'] ?? ''] ?? ($sku['status'] ?? '-');
                $rejectedAtStage = $sku['rejectedAtStage'] ?? '';
                $reviewRemarks = $rejectedAtStage === 'sales' ? ($sku['salesRemarks'] ?? '') : ($sku['npdRemarks'] ?? '');
                $reviewRemarksLabel = $rejectedAtStage === 'sales' ? 'Sales remarks' : 'NPD remarks';
              @endphp
              <span class="sku-status-badge" style="background:{{ $bg }};">{{ $statusLabel }}</span>
            </div>
          </div>

          <div class="c_content">
            @include('partials.gps-inline-alerts')

            @if(!empty($reviewRemarks))
              <div class="alert {{ ($sku['status'] ?? '') === 'Rejected' ? 'alert-danger' : 'alert-info' }}" style="border-radius:12px;">
                <strong><i class="fa fa-comment-o"></i> {{ $reviewRemarksLabel }}:</strong> {{ $reviewRemarks }}
              </div>
            @endif

            <div class="sku-section">
              <div class="sku-section-title"><i class="fa fa-microchip"></i> Device, eSIM &amp; Model</div>
              <div class="sku-detail-grid">
                <div><div class="sku-field-label">Device Category</div><div class="sku-field-value">{{ $sku['deviceCategory']['name'] ?? '-' }}</div></div>
                <div><div class="sku-field-label">Firmware</div><div class="sku-field-value">{{ $sku['firmware']['name'] ?? '-' }}</div></div>
                @php $skuEsimProvider = $sku['esim']['provider'] ?? 'jsd'; @endphp
                @if ($skuEsimProvider === '')
                  {{-- Device Category has eSIM disabled: no eSIM details apply. --}}
                  <div><div class="sku-field-label">eSIM</div><div class="sku-field-value">Not required for this device category</div></div>
                @else
                <div><div class="sku-field-label">eSIM Provider</div><div class="sku-field-value">{{ $skuEsimProvider === 'customer' ? ($sku['raisedBy']['name'] ?? 'Customer') : 'JSD' }}</div></div>
                <div><div class="sku-field-label">eSIM Make</div><div class="sku-field-value">{{ $sku['esim']['make'] ?? '-' }}</div></div>
                <div><div class="sku-field-label">eSIM Profile 1</div><div class="sku-field-value">{{ $sku['esim']['profile1'] ?? '-' }}</div></div>
                <div><div class="sku-field-label">eSIM Profile 2</div><div class="sku-field-value">{{ $sku['esim']['profile2'] ?? '-' }}</div></div>
                @if (!empty($sku['esim']['apnProfile1']) || !empty($sku['esim']['apnProfile2']))
                  <div><div class="sku-field-label">APN Profile 1</div><div class="sku-field-value">{{ $sku['esim']['apnProfile1'] ?? '' ?: '-' }}</div></div>
                  <div><div class="sku-field-label">APN Profile 2</div><div class="sku-field-value">{{ $sku['esim']['apnProfile2'] ?? '' ?: '-' }}</div></div>
                @endif
                <div><div class="sku-field-label">eSIM Recharge</div><div class="sku-field-value">{{ ($sku['esimRechargePeriod'] ?? '') === '2_year' ? '2 Years' : (($sku['esimRechargePeriod'] ?? '') === '1_year' ? '1 Year' : '-') }}</div></div>
                @endif
                <div><div class="sku-field-label">Model Name</div><div class="sku-field-value">{{ $sku['modelName'] ?? '' ?: '-' }}</div></div>
                <div><div class="sku-field-label">Vendor ID</div><div class="sku-field-value">{{ $sku['vendorId'] ?? '' ?: '-' }}</div></div>
                <div><div class="sku-field-label">Sample Serial Number Format</div><div class="sku-field-value">{{ $sku['serialNumberFormat'] ?? '' ?: '-' }}</div></div>
                <div><div class="sku-field-label">FG BOM Number</div><div class="sku-field-value">{{ $sku['fgBomNumber'] ?? '' ?: '-' }}</div></div>
                <div><div class="sku-field-label">Tranzact ID</div><div class="sku-field-value">{{ $sku['tranzactId'] ?? '' ?: '-' }}</div></div>
                <div><div class="sku-field-label">Packaging Type</div><div class="sku-field-value">{{ ['direct_master_carton' => 'Direct Master Carton', 'unit_packaging' => 'Unit Packaging'][$sku['cartonType'] ?? ''] ?? '-' }}</div></div>
                <div><div class="sku-field-label">Sticker Format</div><div class="sku-field-value">{{ $sku['stickerFormat']['name'] ?? '-' }}</div></div>
                <div><div class="sku-field-label">Submitted</div><div class="sku-field-value">{{ !empty($sku['createdAt']) ? \App\Helper\CommonHelper::getDateAsTimeZone($sku['createdAt'], 'd-M-Y H:i') : '-' }}</div></div>
              </div>
            </div>

            @php $configEntries = $sku['configuration']['values'] ?? []; @endphp
            <div class="sku-section">
              <div class="sku-section-title"><i class="fa fa-sliders"></i> Configuration ({{ count($configEntries) }} fields)</div>
              @if(empty($configEntries))
                <p class="text-muted" style="font-size:13px;">No configuration captured.</p>
              @else
                <div class="sku-detail-grid">
                  @foreach($configEntries as $key => $val)
                    <div>
                      <div class="sku-field-label">{{ $key }}</div>
                      <div class="sku-field-value">{{ is_array($val) ? ($val['value'] ?? '-') : $val }}</div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>

            @if(!empty($sku['statusHistory']))
              @php
                // Fixed pipeline (New SKU -> Sales -> NPD -> Approved), NOT a
                // raw dump of statusHistory — a step already passed is always
                // shown as done/green, regardless of how many stages have
                // happened since. Only the CURRENT stage shows as pending
                // (amber); anything not reached yet is grayed out.
                $history = collect($sku['statusHistory'] ?? []);
                $lastToStatus = fn($status) => $history->filter(fn($h) => ($h['toStatus'] ?? '') === $status)->last();

                $currentStatus = $sku['status'] ?? '';
                $rejectedStage = $sku['rejectedAtStage'] ?? '';
                $isRejected = $currentStatus === 'Rejected';

                $createdEntry = $history->first();
                $salesDoneEntry = $lastToStatus('PendingNpd');
                $npdDoneEntry = $lastToStatus('Completed');
                $rejectedEntry = $lastToStatus('Rejected');

                $metaFor = function ($entry) {
                  if (!$entry) return '';
                  $who = $entry['changedByName'] ?: ($entry['actorType'] ?? '');
                  $when = !empty($entry['changedAt']) ? \App\Helper\CommonHelper::getDateAsTimeZone($entry['changedAt'], 'd-M-Y H:i') : '';
                  return trim($who . ($who && $when ? ' · ' : '') . $when);
                };

                $steps = [];
                $steps[] = ['label' => 'New SKU', 'class' => 'is-approved', 'meta' => $metaFor($createdEntry)];

                if ($isRejected && $rejectedStage === 'sales') {
                  $steps[] = ['label' => 'Rejected By Sales', 'class' => 'is-rejected', 'meta' => $metaFor($rejectedEntry)];
                } elseif ($salesDoneEntry) {
                  $steps[] = ['label' => 'Confirmed By Sales', 'class' => 'is-approved', 'meta' => $metaFor($salesDoneEntry)];
                } elseif ($currentStatus === 'PendingSales') {
                  $steps[] = ['label' => 'Sales Confirmation Pending', 'class' => 'is-pending', 'meta' => ''];
                } else {
                  $steps[] = ['label' => 'Sales Confirmation Pending', 'class' => 'is-upcoming', 'meta' => ''];
                }

                if ($isRejected && $rejectedStage === 'npd') {
                  $steps[] = ['label' => 'Rejected By NPD', 'class' => 'is-rejected', 'meta' => $metaFor($rejectedEntry)];
                } elseif ($npdDoneEntry) {
                  $steps[] = ['label' => 'Confirmed By NPD', 'class' => 'is-approved', 'meta' => $metaFor($npdDoneEntry)];
                } elseif ($currentStatus === 'PendingNpd') {
                  $steps[] = ['label' => 'NPD Confirmation Pending', 'class' => 'is-pending', 'meta' => ''];
                } else {
                  $steps[] = ['label' => 'NPD Confirmation Pending', 'class' => 'is-upcoming', 'meta' => ''];
                }

                $steps[] = $currentStatus === 'Completed'
                  ? ['label' => 'Approved', 'class' => 'is-approved', 'meta' => $metaFor($npdDoneEntry)]
                  : ['label' => 'Approved', 'class' => 'is-upcoming', 'meta' => ''];
              @endphp
              <div class="sku-section">
                <div class="sku-section-title"><i class="fa fa-history"></i> History</div>
                <ul class="sku-timeline">
                  @foreach($steps as $step)
                    <li class="{{ $step['class'] }}">
                      <div class="step-label">{{ $step['label'] }}</div>
                      @if($step['meta'])
                        <div class="step-meta">{{ $step['meta'] }}</div>
                      @endif
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="sku-actions">
              @if(($sku['status'] ?? '') === 'PendingSales' || (($sku['status'] ?? '') === 'Rejected' && !empty($sku['resubmissionAllowed'])))
                <a href="/skus/{{ $skuId }}/edit" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit</a>
              @endif
              <a href="/skus" class="btn btn-default">Back to My SKUs</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@endsection
