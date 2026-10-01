@extends('layouts.apps')

@push('styles')
<style>
  .po-view-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .po-view-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .po-view-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .po-view-card .c_content { padding: 20px 24px 24px; }
  .po-view-status-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; color: #fff; font-size: 12.5px; font-weight: 700; letter-spacing: .3px; }

  .po-view-section { background: rgba(148,163,184,.045); border: 1px solid rgba(148,163,184,.14); border-radius: 12px; padding: 18px; margin-bottom: 18px; }
  .po-view-section-title { display: flex; align-items: center; gap: 9px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #475569; margin: 0 0 16px; }
  .po-view-section-title i { color: #22c55e; font-size: 14px; }
  .po-view-field-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
  .po-view-field-value { font-size: 14px; font-weight: 600; color: #1e293b; margin-top: 4px; word-break: break-word; }
  .po-view-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 24px; }
  @media (max-width: 767px) { .po-view-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 480px) { .po-view-grid { grid-template-columns: 1fr; } }

  .po-view-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(148,163,184,.16); padding-top: 18px; margin-top: 4px; }
  .po-view-actions .btn { border-radius: 9px; height: 44px; padding: 0 22px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }

  .po-view-timeline { list-style: none; margin: 0; padding: 0; }
  .po-view-timeline li { position: relative; padding: 0 0 20px 28px; }
  .po-view-timeline li:last-child { padding-bottom: 0; }
  .po-view-timeline li::before {
    content: ''; position: absolute; left: 4px; top: 3px; width: 11px; height: 11px;
    border-radius: 50%; background: #fff; border: 2px solid #94a3b8; z-index: 1;
  }
  .po-view-timeline li::after {
    content: ''; position: absolute; left: 9px; top: 14px; bottom: -6px; width: 2px; background: rgba(148,163,184,.25);
  }
  .po-view-timeline li:last-child::after { display: none; }
  .po-view-timeline li.is-approved::before { border-color: #22c55e; background: #22c55e; }
  .po-view-timeline li.is-rejected::before { border-color: #ef4444; background: #ef4444; }
  .po-view-timeline li.is-pending::before { border-color: #f59e0b; background: #f59e0b; }
  .po-view-timeline li.is-upcoming .step-label { color: #94a3b8; font-weight: 600; }
  .po-view-timeline .step-label { font-size: 13px; font-weight: 700; color: #1e293b; }
  .po-view-timeline .step-meta { font-size: 11.5px; color: #94a3b8; margin-top: 1px; }

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
        <a href="/{{ $url_type }}/purchase-orders" class="bc-item">Purchase Orders</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">{{ $po['poNumber'] ?? 'PO' }}</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel po-view-card">
          <div class="c_title">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
              <h2 style="margin:0;">@include('partials.brand-gps-mark') {{ $po['poNumber'] ?? 'PO' }}</h2>
              @php
                $poBg = ['Pending' => '#f59e0b', 'PendingPpc' => '#3b82f6', 'PendingSalesConfirm' => '#8b5cf6', 'Approved' => '#22c55e', 'Rejected' => '#ef4444'][$po['status'] ?? ''] ?? '#64748b';
                $poStatusLabel = ['Pending' => 'Pending Sales', 'PendingPpc' => 'Pending PPC', 'PendingSalesConfirm' => 'Pending Sales Confirm', 'Rejected' => 'Cancelled'][$po['status'] ?? ''] ?? ($po['status'] ?? '-');
              @endphp
              <span class="po-view-status-badge" style="background:{{ $poBg }};">{{ $poStatusLabel }}</span>
            </div>
          </div>

          <div class="c_content">
            @include('partials.gps-inline-alerts')

            @if(($po['status'] ?? '') === 'Rejected' && !empty($po['salesRemarks']))
              <div class="alert alert-danger" style="border-radius:12px;">
                <strong><i class="fa fa-comment-o"></i> Cancellation reason:</strong> {{ $po['salesRemarks'] }}
              </div>
            @endif

            <div class="po-view-section">
              <div class="po-view-section-title"><i class="fa fa-microchip"></i> Device, eSIM &amp; Model</div>
              <div class="po-view-grid">
                <div><div class="po-view-field-label">SKU Code</div><div class="po-view-field-value">{{ $po['skuCode'] ?? '' ?: '-' }}</div></div>
                <div><div class="po-view-field-label">Device Category</div><div class="po-view-field-value">{{ $po['deviceCategory']['name'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">Firmware</div><div class="po-view-field-value">{{ $po['firmware']['name'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">eSIM Make</div><div class="po-view-field-value">{{ $po['esim']['make'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">eSIM Profile 1</div><div class="po-view-field-value">{{ $po['esim']['profile1'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">eSIM Profile 2</div><div class="po-view-field-value">{{ $po['esim']['profile2'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">eSIM Recharge</div><div class="po-view-field-value">{{ ($po['esimRechargePeriod'] ?? '') === '2_year' ? '2 Years' : (($po['esimRechargePeriod'] ?? '') === '1_year' ? '1 Year' : '-') }}</div></div>
                <div><div class="po-view-field-label">Model Name</div><div class="po-view-field-value">{{ $po['modelName'] ?? '' ?: '-' }}</div></div>
                <div><div class="po-view-field-label">Vendor ID</div><div class="po-view-field-value">{{ $po['vendorId'] ?? '' ?: '-' }}</div></div>
                <div><div class="po-view-field-label">Sample Serial Number Format</div><div class="po-view-field-value">{{ $po['serialNumberFormat'] ?? '' ?: '-' }}</div></div>
                <div><div class="po-view-field-label">Packaging Type</div><div class="po-view-field-value">{{ ['direct_master_carton' => 'Direct Master Carton', 'unit_packaging' => 'Unit Packaging'][$po['cartonType'] ?? ''] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">Sticker Format</div><div class="po-view-field-value">{{ $po['stickerFormat']['name'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">FG BOM Number</div><div class="po-view-field-value">{{ $po['fgBomNumber'] ?? '' ?: '-' }}</div></div>
                <div><div class="po-view-field-label">Tranzact ID</div><div class="po-view-field-value">{{ $po['tranzactId'] ?? '' ?: '-' }}</div></div>
                <div><div class="po-view-field-label">Required Quantity</div><div class="po-view-field-value">{{ $po['requiredQuantity'] ?? '-' }}</div></div>
                <div><div class="po-view-field-label">Expected Delivery</div><div class="po-view-field-value">
                  @php $delivery = $po['expectedDeliveryDate'] ?? $po['ppcDispatchDate'] ?? null; @endphp
                  {{ $delivery ? \App\Helper\CommonHelper::getDateAsTimeZone($delivery, 'd-M-Y') : '-' }}
                </div></div>
                <div><div class="po-view-field-label">Raised On</div><div class="po-view-field-value">{{ !empty($po['createdAt']) ? \App\Helper\CommonHelper::getDateAsTimeZone($po['createdAt'], 'd-M-Y H:i') : '-' }}</div></div>
                <div><div class="po-view-field-label">Last Updated</div><div class="po-view-field-value">{{ !empty($po['updatedAt']) ? \App\Helper\CommonHelper::getDateAsTimeZone($po['updatedAt'], 'd-M-Y H:i') : '-' }}</div></div>
              </div>
            </div>

            @if(!empty($po['accessories']))
              <div class="po-view-section">
                <div class="po-view-section-title"><i class="fa fa-paperclip"></i> Accessories ({{ count($po['accessories']) }})</div>
                <div class="table-responsive">
                  <table class="table table-bordered" style="margin-bottom:0;">
                    <thead><tr><th>Accessory</th><th>Quantity</th><th>Total for PO</th><th>Issued</th></tr></thead>
                    <tbody>
                      @foreach($po['accessories'] as $acc)
                        <tr>
                          <td><strong>{{ $acc['name'] ?? '-' }}</strong> @if(!empty($acc['mandatory']))<span class="label label-danger">Mandatory</span>@endif
                            <div style="font-size:11.5px;color:#94a3b8;">{{ $acc['code'] ?? '' }}</div></td>
                          <td>{{ $acc['qtyPerUnit'] ?? '-' }} {{ ($acc['qtyMode'] ?? '') === 'per_po' ? 'per PO' : 'per device' }}</td>
                          <td>{{ $acc['requiredQty'] ?? 0 }} {{ $acc['unit'] ?? '' }}</td>
                          <td>{{ max(0, (int) ($acc['issuedQty'] ?? 0) - (int) ($acc['returnedQty'] ?? 0)) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endif

            @if(!empty($po['logistics']))
              <div class="po-view-section">
                <div class="po-view-section-title"><i class="fa fa-truck"></i> Logistics — {{ ($po['logistics']['managedBy'] ?? 'us') === 'customer' ? "I'll arrange my own pickup" : 'JSD arranges delivery' }}</div>
                <div class="po-view-grid">
                  @if(($po['logistics']['managedBy'] ?? 'us') === 'customer')
                    <div><div class="po-view-field-label">Transporter Name</div><div class="po-view-field-value">{{ $po['logistics']['transporterName'] ?? '' ?: '-' }}</div></div>
                    <div><div class="po-view-field-label">Transporter Contact</div><div class="po-view-field-value">{{ $po['logistics']['transporterContact'] ?? '' ?: '-' }}</div></div>
                    <div><div class="po-view-field-label">Vehicle Number</div><div class="po-view-field-value">{{ $po['logistics']['vehicleNumber'] ?? '' ?: '-' }}</div></div>
                    <div><div class="po-view-field-label">Pickup Date &amp; Time</div><div class="po-view-field-value">{{ !empty($po['logistics']['pickupDateTime']) ? \App\Helper\CommonHelper::getDateAsTimeZone($po['logistics']['pickupDateTime'], 'd-M-Y H:i') : '-' }}</div></div>
                    <div><div class="po-view-field-label">Authorized Pickup Person</div><div class="po-view-field-value">{{ $po['logistics']['pickupPersonName'] ?? '' ?: '-' }}</div></div>
                  @else
                    <div><div class="po-view-field-label">Delivery Address</div><div class="po-view-field-value">{{ $po['logistics']['deliveryAddress'] ?? '' ?: '-' }}</div></div>
                    <div><div class="po-view-field-label">Contact Person</div><div class="po-view-field-value">{{ $po['logistics']['contactName'] ?? '' ?: '-' }}</div></div>
                    <div><div class="po-view-field-label">Contact Phone</div><div class="po-view-field-value">{{ $po['logistics']['contactPhone'] ?? '' ?: '-' }}</div></div>
                    <div><div class="po-view-field-label">Delivery Mode</div><div class="po-view-field-value">{{ $po['logistics']['deliveryMode'] ?? '' ?: '-' }}</div></div>
                  @endif
                  <div><div class="po-view-field-label">Special Instructions</div><div class="po-view-field-value">{{ $po['logistics']['specialInstructions'] ?? '' ?: '-' }}</div></div>
                </div>
              </div>
            @endif

            @php $poConfigEntries = $po['configuration']['values'] ?? []; @endphp
            <div class="po-view-section">
              <div class="po-view-section-title"><i class="fa fa-sliders"></i> Configuration ({{ count($poConfigEntries) }} fields)</div>
              @if(empty($poConfigEntries))
                <p class="text-muted" style="font-size:13px;">No configuration captured.</p>
              @else
                <div class="po-view-grid">
                  @foreach($poConfigEntries as $key => $val)
                    <div>
                      <div class="po-view-field-label">{{ $key }}</div>
                      <div class="po-view-field-value">{{ is_array($val) ? ($val['value'] ?? '-') : $val }}</div>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>

            @if(!empty($po['statusHistory']))
              @php
                // Fixed pipeline (New PO -> Sales Approval -> PPC Dispatch
                // Date -> Sales Confirmation), NOT a raw dump of
                // statusHistory — a step already passed is always shown as
                // done/green regardless of how many stages have happened
                // since. Only the CURRENT stage shows as pending (amber);
                // anything not reached yet is grayed out.
                $history = collect($po['statusHistory'] ?? []);
                $lastToStatus = fn($status) => $history->filter(fn($h) => ($h['toStatus'] ?? '') === $status)->last();

                $currentStatus = $po['status'] ?? '';
                $isCancelled = $currentStatus === 'Rejected';
                $rejectedEntry = $lastToStatus('Rejected');
                $rejectedFrom = $rejectedEntry['fromStatus'] ?? '';

                $createdEntry = $history->first();
                $salesApprovedEntry = $lastToStatus('PendingPpc');
                $ppcDoneEntry = $lastToStatus('PendingSalesConfirm');
                $approvedEntry = $lastToStatus('Approved');

                $metaFor = function ($entry) {
                  if (!$entry) return '';
                  $who = $entry['changedByName'] ?? '' ?: ($entry['actorType'] ?? '');
                  $when = !empty($entry['changedAt']) ? \App\Helper\CommonHelper::getDateAsTimeZone($entry['changedAt'], 'd-M-Y H:i') : '';
                  return trim($who . ($who && $when ? ' · ' : '') . $when);
                };

                $steps = [];
                $steps[] = ['label' => 'New PO', 'class' => 'is-approved', 'meta' => $metaFor($createdEntry)];

                if ($isCancelled && $rejectedFrom === 'Pending') {
                  $steps[] = ['label' => 'Cancelled by Sales', 'class' => 'is-rejected', 'meta' => $metaFor($rejectedEntry)];
                } elseif ($salesApprovedEntry) {
                  $steps[] = ['label' => 'Approved by Sales', 'class' => 'is-approved', 'meta' => $metaFor($salesApprovedEntry)];
                } elseif ($currentStatus === 'Pending') {
                  $steps[] = ['label' => 'Pending Sales Review', 'class' => 'is-pending', 'meta' => ''];
                } else {
                  $steps[] = ['label' => 'Pending Sales Review', 'class' => 'is-upcoming', 'meta' => ''];
                }

                if ($isCancelled && $rejectedFrom === 'PendingPpc') {
                  $steps[] = ['label' => 'Cancelled While With PPC', 'class' => 'is-rejected', 'meta' => $metaFor($rejectedEntry)];
                } elseif ($ppcDoneEntry) {
                  $steps[] = ['label' => 'Dispatch Date Set by PPC', 'class' => 'is-approved', 'meta' => $metaFor($ppcDoneEntry)];
                } elseif ($currentStatus === 'PendingPpc') {
                  $steps[] = ['label' => 'Pending PPC (Dispatch Date)', 'class' => 'is-pending', 'meta' => ''];
                } else {
                  $steps[] = ['label' => 'Pending PPC (Dispatch Date)', 'class' => 'is-upcoming', 'meta' => ''];
                }

                if ($isCancelled && $rejectedFrom === 'PendingSalesConfirm') {
                  $steps[] = ['label' => 'Cancelled at Final Confirmation', 'class' => 'is-rejected', 'meta' => $metaFor($rejectedEntry)];
                } elseif ($currentStatus === 'Approved' || $approvedEntry) {
                  $steps[] = ['label' => 'Approved', 'class' => 'is-approved', 'meta' => $metaFor($approvedEntry)];
                } elseif ($currentStatus === 'PendingSalesConfirm') {
                  $steps[] = ['label' => 'Pending Sales Confirmation', 'class' => 'is-pending', 'meta' => ''];
                } else {
                  $steps[] = ['label' => 'Pending Sales Confirmation', 'class' => 'is-upcoming', 'meta' => ''];
                }

                // Cancelled AFTER it was already Approved — a separate,
                // terminal event tacked on after the (still-green) Approved step.
                if ($isCancelled && $rejectedFrom === 'Approved') {
                  $steps[] = ['label' => 'Cancelled', 'class' => 'is-rejected', 'meta' => $metaFor($rejectedEntry)];
                }
              @endphp
              <div class="po-view-section">
                <div class="po-view-section-title"><i class="fa fa-history"></i> History</div>
                <ul class="po-view-timeline">
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

            @if(!empty($resubmitMode))
              {{-- Resubmit: only quantity + delivery date change. The device,
                   eSIM and configuration above come from the approved SKU. --}}
              <form method="POST" action="/{{ $url_type }}/purchase-orders/{{ $poId }}/resubmit" id="poResubmitForm">
                @csrf
                <div class="po-view-section">
                  <div class="po-view-section-title"><i class="fa fa-pencil"></i> Correct &amp; Resubmit</div>
                  <p class="text-muted" style="font-size:13px;margin-top:-6px;">
                    Device, eSIM and configuration come from the approved SKU {{ $po['skuCode'] ?? '' }} and can't be changed here — raise a new SKU for a different specification.
                  </p>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label" for="required_quantity">Required Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" max="1000000" step="1" required class="form-control" id="required_quantity" name="required_quantity"
                          value="{{ old('required_quantity', $po['requiredQuantity'] ?? '') }}">
                        @error('required_quantity')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label" for="expected_delivery_date">Expected Delivery Date</label>
                        <input type="date" class="form-control" id="expected_delivery_date" name="expected_delivery_date" min="{{ now()->toDateString() }}"
                          value="{{ old('expected_delivery_date', !empty($po['expectedDeliveryDate']) ? substr($po['expectedDeliveryDate'], 0, 10) : '') }}">
                        @error('expected_delivery_date')<span class="text-danger">{{ $message }}</span>@enderror
                      </div>
                    </div>
                  </div>
                </div>
                <div class="po-view-actions">
                  <a href="/{{ $url_type }}/purchase-orders/{{ $poId }}" class="btn btn-default">Cancel</a>
                  <button type="submit" class="btn btn-primary" id="poResubmitBtn"><i class="fa fa-paper-plane"></i> Resubmit for Approval</button>
                </div>
              </form>
            @else
            <div class="po-view-actions">
              @if(($po['status'] ?? '') === 'Rejected' && !empty($po['resubmissionAllowed']))
                <a href="/{{ $url_type }}/purchase-orders/{{ $poId }}/edit" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit &amp; Resubmit</a>
              @endif
              <a href="/{{ $url_type }}/purchase-orders" class="btn btn-default">Back to Purchase Orders</a>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@stop

@section('scripts')
<script>
  // One click = one resubmission.
  $('#poResubmitForm').on('submit', function () {
    $('#poResubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Resubmitting…');
  });
</script>
@stop
