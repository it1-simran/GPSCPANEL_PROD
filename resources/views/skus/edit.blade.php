@extends('layouts.apps')

@push('styles')
<style>
  .po-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .po-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .po-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .po-card .c_title h2 i { color: #22c55e; }
  .po-card .c_content { padding: 20px 24px 24px; }
  .po-intro { color: #5b6b82; font-size: 13px; margin: -4px 0 18px; }

  .po-section { background: rgba(148,163,184,.045); border: 1px solid rgba(148,163,184,.14); border-radius: 12px; padding: 18px 18px 2px; margin-bottom: 18px; }
  .po-section-title { display: flex; align-items: center; gap: 9px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #475569; margin: 0 0 16px; }
  .po-section-title i { color: #22c55e; font-size: 14px; }
  .po-step { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: rgba(34,197,94,.16); color: #22c55e; font-size: 12px; font-weight: 700; margin-right: 2px; }
  .po-subhead { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .8px; margin: 6px 0 14px; padding-top: 14px; border-top: 1px dashed rgba(148,163,184,.2); }
  .po-subhead i { color: #22c55e; }

  .po-form .form-group { margin-bottom: 18px; }
  .po-form label.control-label { font-weight: 600; font-size: 12.5px; color: #334155; margin-bottom: 7px; display: block; letter-spacing: .2px; }
  .po-form .form-control { height: 44px; border-radius: 9px; font-size: 14px; border: 1px solid rgba(148,163,184,.35); box-shadow: none; }
  .po-form .form-control:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.16); }
  .po-form select.form-control { padding: 8px 12px; }
  .po-form .readonly-field { background: rgba(148,163,184,.12); color: #5b6b82; cursor: not-allowed; }
  .po-hint { font-size: 11.5px; color: #5b6b82; margin-top: 5px; display: block; min-height: 15px; }
  .po-hint.error { color: #ef4444; }
  .po-hint.ok { color: #22c55e; }
  .po-required { color: #ef4444; }

  .po-form .select2-container { width: 100% !important; }
  .po-form .select2-container .select2-choice {
    height: 44px !important; line-height: 44px !important; border-radius: 9px !important;
    border: 1px solid rgba(148,163,184,.35) !important;
    padding: 0 36px 0 12px !important; display: flex !important; align-items: center !important;
    background-image: none !important;
  }
  .po-form .select2-container .select2-choice > span { line-height: 1.2 !important; margin: 0 !important; overflow: hidden; text-overflow: ellipsis; }
  .po-form .select2-container .select2-choice .select2-arrow {
    top: 0 !important; height: 44px !important; width: 30px !important; border-radius: 0 9px 9px 0;
    background: transparent !important; border-left: 1px solid rgba(148,163,184,.25);
  }
  .po-form .select2-container .select2-choice .select2-arrow b { display: none !important; }
  .po-form .select2-container .select2-choice .select2-arrow::after {
    content: ''; position: absolute; top: 50%; right: 11px; width: 0; height: 0; margin-top: -3px;
    border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #64748b;
  }
  .po-form .select2-container .select2-choice abbr { top: 15px; }
  .po-form .select2-container.select2-container-disabled .select2-choice { background: rgba(148,163,184,.12); opacity: .7; }

  #configFieldsRow .form-control { height: 40px; }
  .po-config-hint { color: #5b6b82; font-size: 13px; padding: 4px 2px 12px; display: block; }

  .po-actions { display: flex; justify-content: flex-end; align-items: center; gap: 10px; border-top: 1px solid rgba(148,163,184,.16); padding-top: 18px; margin-top: 4px; }
  .po-actions .btn {
    appearance: none; -webkit-appearance: none; box-sizing: border-box;
    width: 150px; height: 44px; margin: 0; border-radius: 9px;
    font-family: inherit; font-weight: 600; line-height: 1;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  }
  .po-actions .btn-success { background: #22c55e; border-color: #22c55e; }
  .po-actions .btn-success:hover, .po-actions .btn-success:focus { background: #16a34a; border-color: #16a34a; }

  .po-stepper { display: flex; margin: 6px 0 24px; padding: 0; }
  .po-stepper-item { flex: 1; text-align: center; position: relative; cursor: default; }
  .po-stepper-item:not(:first-child)::before { content: ''; position: absolute; top: 16px; left: -50%; width: 100%; height: 3px; background: rgba(148,163,184,.22); z-index: 0; border-radius: 3px; }
  .po-stepper-item.active::before, .po-stepper-item.done::before { background: #22c55e; }
  .po-stepper-dot { position: relative; z-index: 1; width: 34px; height: 34px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; background: rgba(148,163,184,.18); color: #5b6b82; }
  .po-stepper-item.active .po-stepper-dot { background: #22c55e; color: #fff; box-shadow: 0 0 0 5px rgba(34,197,94,.16); }
  .po-stepper-item.done .po-stepper-dot { background: #16a34a; color: #fff; }
  .po-stepper-item.done { cursor: pointer; }
  .po-stepper-label { font-size: 11.5px; color: #5b6b82; font-weight: 600; letter-spacing: .2px; padding: 0 4px; }
  .po-stepper-item.active .po-stepper-label { color: #1e293b; }

  .po-wizard-on .po-section-title .po-step { display: none; }
  .po-wizard-on .po-section { margin-bottom: 0; }

  .has-error .form-control { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important; }
  .has-error .select2-container .select2-choice { border-color: #ef4444 !important; }

  @media (max-width: 640px) {
    .po-card .c_content { padding: 16px; }
    .po-section { padding: 14px 14px 2px; }
    .po-stepper-label { display: none; }
  }
</style>
@endpush

@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel po-card">
          <div class="c_title">
            <div class="row bgx-title-container" style="display:flex;align-items:center;">
              <div class="col-lg-8"><h2>@include('partials.brand-gps-mark') Edit SKU — {{ $sku['skuCode'] ?? '' }}</h2></div>
              <div class="col-lg-4 text-right">
                <a href="/skus" class="btn btn-default"><i class="fa fa-list"></i> View SKUs</a>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>

          <div class="c_content">
            @php
              $rejectedAtStage = $sku['rejectedAtStage'] ?? '';
              $reviewRemarks = $rejectedAtStage === 'sales' ? ($sku['salesRemarks'] ?? '') : ($sku['npdRemarks'] ?? '');
              $reviewRemarksLabel = $rejectedAtStage === 'sales' ? 'Sales remarks' : 'NPD remarks';
            @endphp
            <p class="po-intro">
              @if ($isResubmit)
                This SKU was rejected{{ $rejectedAtStage ? ' by ' . ($rejectedAtStage === 'sales' ? 'Sales' : 'NPD') : '' }}. Update the details below and resubmit it for review.
              @else
                This SKU is still awaiting Sales review — update its device, eSIM and configuration below. Saving keeps it pending.
              @endif
              Fields marked <span class="po-required">*</span> are required.
            </p>

            @include('partials.gps-inline-alerts')

            @if (!empty($reviewRemarks))
              <div class="alert alert-{{ $isResubmit ? 'danger' : 'warning' }}" style="border-radius:12px;">
                <strong><i class="fa fa-comment-o"></i> {{ $reviewRemarksLabel }}:</strong> {{ $reviewRemarks }}
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul style="margin:0;padding-left:18px;">
                  @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('skus.update', $skuId) }}" class="po-form" id="poForm"
                  data-current-user="{{ $sku['raisedBy']['cpanelUserId'] ?? '' }}"
                  data-config-overrides='@json($configOverrides)'
                  data-lookup-url="/{{ $url_type }}/purchase-orders/model-lookup"
                  data-config-url="/{{ $url_type }}/purchase-orders/category-config"
                  data-assign-url="/{{ $url_type }}/purchase-orders/account-assignments"
                  data-sticker-preview-url="/{{ $url_type }}/purchase-orders/sticker-format-preview"
                  data-firmwares='@json($firmwares->values())'
                  style="margin-top:15px;">
              @csrf
              @method('PATCH')

              <div class="po-stepper" id="poStepper"></div>

              {{-- ACCOUNT --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">1</span><i class="fa fa-user"></i> Account</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">SKU for Account</label>
                    <input type="text" class="form-control readonly-field" value="{{ $accountName }}" readonly>
                    <span class="po-hint">The account a SKU belongs to can't be changed.</span>
                  </div>
                </div>
              </div>
              </div>

              @php
                // A JSD SKU saved with a typed-in ("Others") make/profile has no
                // matching catalog option — preselect "Others" and prefill its box.
                $isJsdSaved = ($esimProvider ?? 'jsd') !== 'customer';
                $catalogMakes = collect($esimMakes)->pluck('name')->all();
                $catalogProfiles = collect($esimProfiles)->pluck('name')->all();
                $savedEsim = ['make' => $sku['esim']['make'] ?? '', 'p1' => $sku['esim']['profile1'] ?? '', 'p2' => $sku['esim']['profile2'] ?? ''];
                $isOther = fn($v, $list) => $isJsdSaved && $v !== '' && !in_array($v, $list, true);
                $makeSel = $isOther($savedEsim['make'], $catalogMakes) ? 'others' : $savedEsim['make'];
                $p1Sel = $isOther($savedEsim['p1'], $catalogProfiles) ? 'others' : $savedEsim['p1'];
                $p2Sel = $isOther($savedEsim['p2'], $catalogProfiles) ? 'others' : $savedEsim['p2'];
              @endphp
              {{-- DEVICE & eSIM --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">2</span><i class="fa fa-microchip"></i> Device, eSIM &amp; Model</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Device Category <span class="po-required">*</span></label>
                    <select name="device_category_id" id="device_category_id" class="select2" required>
                      <option value="">Select Device Category</option>
                      @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" data-is-esim="{{ ($cat->is_sku_esim ?? 0) ? '1' : '0' }}" {{ old('device_category_id', $sku['deviceCategory']['id'] ?? null) == $cat->id ? 'selected' : '' }}>
                          {{ $cat->device_category_name }}
                        </option>
                      @endforeach
                    </select>
                    <span class="po-hint" id="categoryHint"></span>
                  </div>
                </div>
                <div class="col-md-6" id="esimProviderCol">
                  <div class="form-group">
                    <label class="control-label">eSIM Provider <span class="po-required">*</span></label>
                    <select name="esim_provider" id="esim_provider" class="select2" required>
                      <option value="jsd" {{ old('esim_provider', $esimProvider ?? 'jsd') == 'jsd' ? 'selected' : '' }}>JSD</option>
                      <option value="customer" {{ old('esim_provider', $esimProvider ?? 'jsd') == 'customer' ? 'selected' : '' }}>{{ $accountName }}</option>
                    </select>
                    <span class="po-hint">Choose whose eSIM inventory this SKU uses.</span>
                  </div>
                </div>
                <div class="col-md-6" id="esimJsdMakeCol">
                  <div class="form-group">
                    <label class="control-label">eSIM Make <span class="po-required">*</span></label>
                    <select name="esim_make" id="esim_make" class="select2">
                      <option value="">Select eSIM Make</option>
                      @foreach ($esimMakes as $mk)
                        <option value="{{ $mk['name'] }}" {{ old('esim_make', $makeSel) == $mk['name'] ? 'selected' : '' }}>{{ $mk['name'] }}</option>
                      @endforeach
                      <option value="others" {{ old('esim_make', $makeSel) == 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                    <input type="text" name="esim_make_other" id="esim_make_other" class="form-control" style="margin-top:8px;display:none;"
                      value="{{ old('esim_make_other', $makeSel === 'others' ? $savedEsim['make'] : '') }}" placeholder="Enter eSIM make" disabled>
                    <span class="po-hint">
                      @if ($esimError)
                        <span class="error">eSIM data unavailable from MES ({{ $esimError }}).</span>
                      @else
                        Fetched from MES. Every eSIM has two profiles.
                      @endif
                    </span>
                  </div>
                </div>
                <div class="col-md-6" id="esimCustomerMakeCol" style="display:none;">
                  <div class="form-group">
                    <label class="control-label">eSIM Make <span class="po-required">*</span></label>
                    <input type="text" name="esim_make" id="esim_make_customer" class="form-control"
                      value="{{ old('esim_make', ($esimProvider ?? 'jsd') === 'customer' ? ($sku['esim']['make'] ?? '') : '') }}"
                      placeholder="Enter your eSIM make" disabled>
                    <span class="po-hint">Not in JSD's catalog — enter your own eSIM's make.</span>
                  </div>
                </div>
              </div>

              <div class="row" id="esimJsdProfileRow">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Profile 1 <span class="po-required">*</span></label>
                    <select name="esim_profile_1" id="esim_profile_1" class="select2">
                      <option value="">Select Profile 1</option>
                      @foreach ($esimProfiles as $prof)
                        <option value="{{ $prof['name'] }}" {{ old('esim_profile_1', $p1Sel) == $prof['name'] ? 'selected' : '' }}>{{ $prof['name'] }}</option>
                      @endforeach
                      <option value="others" {{ old('esim_profile_1', $p1Sel) == 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                    <input type="text" name="esim_profile_1_other" id="esim_profile_1_other" class="form-control" style="margin-top:8px;display:none;"
                      value="{{ old('esim_profile_1_other', $p1Sel === 'others' ? $savedEsim['p1'] : '') }}" placeholder="Enter Profile 1 name" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Profile 2 <span class="po-required">*</span></label>
                    <select name="esim_profile_2" id="esim_profile_2" class="select2">
                      <option value="">Select Profile 2</option>
                      @foreach ($esimProfiles as $prof)
                        <option value="{{ $prof['name'] }}" {{ old('esim_profile_2', $p2Sel) == $prof['name'] ? 'selected' : '' }}>{{ $prof['name'] }}</option>
                      @endforeach
                      <option value="others" {{ old('esim_profile_2', $p2Sel) == 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                    <input type="text" name="esim_profile_2_other" id="esim_profile_2_other" class="form-control" style="margin-top:8px;display:none;"
                      value="{{ old('esim_profile_2_other', $p2Sel === 'others' ? $savedEsim['p2'] : '') }}" placeholder="Enter Profile 2 name" disabled>
                  </div>
                </div>
              </div>

              <div class="row" id="esimCustomerProfileRow" style="display:none;">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Profile 1 <span class="po-required">*</span></label>
                    <input type="text" name="esim_profile_1" id="esim_profile_1_customer" class="form-control"
                      value="{{ old('esim_profile_1', ($esimProvider ?? 'jsd') === 'customer' ? ($sku['esim']['profile1'] ?? '') : '') }}"
                      placeholder="Enter Profile 1 name" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Profile 2 <span class="po-required">*</span></label>
                    <input type="text" name="esim_profile_2" id="esim_profile_2_customer" class="form-control"
                      value="{{ old('esim_profile_2', ($esimProvider ?? 'jsd') === 'customer' ? ($sku['esim']['profile2'] ?? '') : '') }}"
                      placeholder="Enter Profile 2 name" disabled>
                  </div>
                </div>
              </div>

              {{-- APNs for a typed-in ("Others") or customer-supplied eSIM — MES
                   adds them to its eSIM master data on final NPD approval. --}}
              <div class="row" id="esimApnRow" style="display:none;">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">APN Profile 1 <span class="po-required">*</span></label>
                    <input type="text" name="esim_apn_1" id="esim_apn_1" class="form-control" maxlength="100"
                      pattern="[A-Za-z0-9.]+" title="Letters, numbers and dots only"
                      value="{{ old('esim_apn_1', $sku['esim']['apnProfile1'] ?? '') }}" placeholder="e.g. iot.com" disabled>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">APN Profile 2 <span class="po-required">*</span></label>
                    <input type="text" name="esim_apn_2" id="esim_apn_2" class="form-control" maxlength="100"
                      pattern="[A-Za-z0-9.]+" title="Letters, numbers and dots only"
                      value="{{ old('esim_apn_2', $sku['esim']['apnProfile2'] ?? '') }}" placeholder="e.g. bsnlnet" disabled>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6" id="esimRechargeCol">
                  <div class="form-group">
                    <label class="control-label">eSIM Recharge Period <span class="po-required">*</span></label>
                    <select name="esim_recharge_period" id="esim_recharge_period" class="select2" required>
                      <option value="">Select Period</option>
                      <option value="1_year" {{ old('esim_recharge_period', $sku['esimRechargePeriod'] ?? null) == '1_year' ? 'selected' : '' }}>1 Year</option>
                      <option value="2_year" {{ old('esim_recharge_period', $sku['esimRechargePeriod'] ?? null) == '2_year' ? 'selected' : '' }}>2 Years</option>
                    </select>
                    <span class="po-hint">Only applies to JSD-managed eSIMs.</span>
                  </div>
                </div>
                <div class="col-md-6" id="firmwareCol">
                  <div class="form-group">
                    <label class="control-label">Backend <span class="po-required">*</span></label>
                    <select id="backend_id" class="select2" required>
                      <option value="">Select Backend</option>
                    </select>
                    <span class="po-hint" id="firmwareHint">Firmware is auto-selected from Device Category, Backend &amp; State.</span>
                  </div>
                </div>
              </div>

              <div class="row" id="stateFirmwareRow">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">State <span class="po-required">*</span></label>
                    <select id="state_id" class="select2" required disabled>
                      <option value="">Select Backend first</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Firmware</label>
                    <input type="text" id="firmware_display" class="form-control readonly-field" readonly
                      placeholder="Auto-selected from Device Category, Backend &amp; State">
                    <input type="hidden" name="firmware_id" id="firmware_id" value="{{ old('firmware_id', $sku['firmware']['id'] ?? '') }}">
                  </div>
                </div>
              </div>

              <div style="display:none;">
                <input type="text" name="model_name" id="model_name" value="{{ old('model_name', $sku['modelName'] ?? '') }}" readonly>
                <input type="text" name="vendor_id" id="vendor_id" value="{{ old('vendor_id', $sku['vendorId'] ?? '') }}" readonly>
                <span id="modelHint"></span>
              </div>

              <div class="po-subhead"><i class="fa fa-barcode"></i> Serial Number</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Sample Serial Number Format <span class="po-required">*</span></label>
                    <input type="text" name="serial_number_format" id="serial_number_format" class="form-control"
                      value="{{ old('serial_number_format', $sku['serialNumberFormat'] ?? '') }}" placeholder="e.g. JSD-XXXX-000000" maxlength="191" required>
                    <span class="po-hint">Give a sample of the serial number pattern this SKU's devices will use.</span>
                  </div>
                </div>
              </div>

              </div>{{-- /device, esim & model section --}}

              {{-- PACKAGING --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">3</span><i class="fa fa-cubes"></i> Packaging</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Packaging Type <span class="po-required">*</span></label>
                    <select name="carton_type" id="carton_type" class="select2" required>
                      <option value="direct_master_carton" {{ old('carton_type', $sku['cartonType'] ?? 'direct_master_carton') == 'direct_master_carton' ? 'selected' : '' }}>Direct Master Carton</option>
                      <option value="unit_packaging" {{ old('carton_type', $sku['cartonType'] ?? 'direct_master_carton') == 'unit_packaging' ? 'selected' : '' }}>Unit Packaging</option>
                    </select>
                    <span class="po-hint">
                      <strong>Direct Master Carton</strong>: units are packed straight into one master carton, which itself carries a consolidated carton sticker.
                      <strong>Unit Packaging</strong>: each unit is individually boxed and stickered.
                    </span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Sticker Format <span class="po-required">*</span></label>
                    <select name="sticker_format_id" id="sticker_format_id" class="select2" required>
                      <option value="">{{ $stickerFormatsError ? 'Sticker formats unavailable' : 'Select Sticker Format' }}</option>
                      @foreach ($stickerFormats as $fmt)
                        <option value="{{ $fmt['_id'] }}" data-name="{{ $fmt['name'] }}" {{ old('sticker_format_id', $sku['stickerFormat']['id'] ?? '') == $fmt['_id'] ? 'selected' : '' }}>{{ $fmt['name'] }}</option>
                      @endforeach
                    </select>
                    <input type="hidden" name="sticker_format_name" id="sticker_format_name" value="{{ old('sticker_format_name', $sku['stickerFormat']['name'] ?? '') }}">
                    <span class="po-hint">
                      @if ($stickerFormatsError)
                        <span class="error">Sticker format data unavailable from MES ({{ $stickerFormatsError }}).</span>
                      @else
                        Fetched from MES.
                      @endif
                    </span>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <label class="control-label">Sticker Preview</label>
                  <div id="stickerPreviewWrap" style="min-height:90px; border:1px dashed rgba(148,163,184,.4); border-radius:9px; padding:16px; display:flex; align-items:center; justify-content:center; background:rgba(148,163,184,.04);">
                    <span class="po-hint" id="stickerPreviewHint">Select a Sticker Format to preview it.</span>
                    <div id="stickerPreviewCanvas" style="position:relative; background:#fff; display:none;"></div>
                  </div>
                </div>
              </div>
              </div>{{-- /packaging section --}}

              {{-- CONFIGURATION --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">4</span><i class="fa fa-sliders"></i> Configuration <small>from device category</small></div>
              <div class="row" id="configFieldsRow">
                <div class="col-md-12"><span class="po-config-hint" id="configHint">Loading configuration…</span></div>
              </div>
              </div>{{-- /configuration section --}}

              <div class="po-actions">
                <a href="/skus" class="btn btn-default">Cancel</a>
                <span class="spacer"></span>
                <button type="button" class="btn btn-default" id="poBackBtn"><i class="fa fa-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-success" id="poNextBtn">Next <i class="fa fa-arrow-right"></i></button>
                <button type="submit" class="btn btn-success" id="poSubmitBtn"><i class="fa fa-check"></i> {{ $isResubmit ? 'Resubmit SKU' : 'Save Changes' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@stop

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.6/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  $(document).ready(function () {
    var $form = $('#poForm');
    var IS_ADMIN = false; // account is fixed on edit — no account picker
    var CURRENT_USER = String($form.data('current-user'));
    var CONFIG_OVERRIDES = {};
    try {
      var _co = $form.data('config-overrides');
      if (_co && typeof _co === 'object') { CONFIG_OVERRIDES = _co; }
    } catch (e) { CONFIG_OVERRIDES = {}; }
    function normKey(k) { return String(k == null ? '' : k).toLowerCase().replace(/ /g, '_'); }
    function overrideVal(key, fallback) {
      var nk = normKey(key);
      return Object.prototype.hasOwnProperty.call(CONFIG_OVERRIDES, nk) ? CONFIG_OVERRIDES[nk] : fallback;
    }
    var LOOKUP_URL = $form.data('lookup-url');
    var CONFIG_URL = $form.data('config-url');
    var STICKER_PREVIEW_URL = $form.data('sticker-preview-url');

    // ---- Sticker Format preview (fields fetched from MES, rendered client-side) ----
    function clearStickerPreview(message) {
      $('#stickerPreviewCanvas').hide().empty();
      $('#stickerPreviewHint').show().removeClass('error').text(message || 'Select a Sticker Format to preview it.');
    }

    // Master formats store no literal value for barcode/QR fields — the real
    // value (IMEI, CCID, serial, …) is filled in per-device at print time.
    // Show what that field WILL hold, not a meaningless numeric placeholder.
    // Fixed-length dummy values matching each field's real-world standard, so
    // the preview shows something print-realistic instead of a placeholder
    // like <IMEI> — Serial No: 16-19 digits, IMEI: 15 digits, CCID: 20 digits.
    var DUMMY_VALUES = [
      { match: /serial/i, value: '123456789012345678' },   // 18 digits (16-19 range)
      { match: /imei/i, value: '490154203237518' },        // 15 digits
      { match: /ccid|iccid/i, value: '89014103211118510720' }, // 20 digits
    ];
    function sampleValueFor(f) {
      if (f.value) { return String(f.value); }
      var key = String(f.name || f.slug || '');
      for (var i = 0; i < DUMMY_VALUES.length; i++) {
        if (DUMMY_VALUES[i].match.test(key)) { return DUMMY_VALUES[i].value; }
      }
      return '<' + (key || 'VALUE').toUpperCase() + '>';
    }

    function renderStickerPreview(format) {
      // dimensions and every field's x/y/width/height are stored in the SAME
      // unit (the designer's own canvas px, at 96dpi — see lib/sticker/units.ts
      // in MES) — scale everything by one factor, no separate mm conversion.
      var boxW = parseFloat(format?.dimensions?.width) || 400;
      var boxH = parseFloat(format?.dimensions?.height) || 240;
      var scale = Math.min(1, 360 / boxW, 220 / boxH);
      var pxW = Math.max(40, boxW * scale);
      var pxH = Math.max(30, boxH * scale);

      var $canvas = $('#stickerPreviewCanvas');
      $canvas.empty().css({
        width: pxW + 'px', height: pxH + 'px',
        border: '1px solid #cbd5e1', boxShadow: '0 2px 8px rgba(0,0,0,.08)',
      }).show();
      $('#stickerPreviewHint').hide();

      (format?.fields || []).forEach(function (f, idx) {
        var left = (parseFloat(f.x) || 0) * scale;
        var top = (parseFloat(f.y) || 0) * scale;
        // A barcode/QR needs real pixel room to be visible even after scaling
        // a small preview box down — floor them higher than plain text fields.
        var ftype = String(f.type || '').trim().toLowerCase();
        var minSize = (ftype === 'barcode' || ftype === 'qrcode') ? 24 : 4;
        var w = Math.max(minSize, (parseFloat(f.width) || 40) * scale);
        var h = Math.max(minSize, (parseFloat(f.height) || 16) * scale);
        var $el = $('<div>').css({
          position: 'absolute', left: left + 'px', top: top + 'px', width: w + 'px', height: h + 'px',
          overflow: 'hidden', display: 'flex', alignItems: 'center', justifyContent: 'center',
          fontSize: Math.max(6, (parseFloat(f.fontSize) || 10) * scale) + 'px',
          color: f.lineColor || '#000', textAlign: 'center', lineHeight: 1.1,
        });

        if (ftype === 'barcode') {
          var $svg = $(document.createElementNS('http://www.w3.org/2000/svg', 'svg'));
          var svgId = 'stickerBc' + idx;
          $svg.attr('id', svgId).css({ display: 'block', width: '100%', height: '100%' });
          $el.append($svg);
          $canvas.append($el);
          try {
            if (typeof JsBarcode === 'undefined') throw new Error('JsBarcode not loaded');
            JsBarcode($svg.get(0), sampleValueFor(f), {
              format: f.format || 'CODE128', width: 1, height: Math.max(14, h * 0.8),
              displayValue: !!f.displayValue, fontSize: Math.max(6, h * 0.2), margin: 0,
            });
            $svg.css({ width: 'auto', height: '100%', maxWidth: '100%' });
            console.debug('Sticker preview: barcode rendered', f.name, $svg.attr('width'), $svg.attr('height'));
          } catch (e) {
            console.error('Sticker preview: barcode field failed to render', f, e);
            $el.css({ borderStyle: 'dashed', borderColor: '#ef4444', color: '#ef4444', fontSize: '9px' }).text('Barcode error: ' + e.message);
          }
        } else if (ftype === 'qrcode') {
          $canvas.append($el);
          try {
            if (typeof QRCode === 'undefined') throw new Error('QRCode not loaded');
            var side = Math.min(w, h);
            // eslint-disable-next-line no-new
            new QRCode($el.get(0), { text: sampleValueFor(f), width: side, height: side, correctLevel: QRCode.CorrectLevel.M });
          } catch (e) {
            console.error('Sticker preview: QR field failed to render', f, e);
            $el.css({ border: '1px dashed #ef4444', color: '#ef4444', fontSize: '9px' }).text('QR error');
          }
        } else if (ftype === 'image') {
          $el.css({ background: 'repeating-linear-gradient(45deg,#e2e8f0,#e2e8f0 4px,#f1f5f9 4px,#f1f5f9 8px)' }).text('Image');
          $canvas.append($el);
        } else {
          var key2 = String(f.name || f.slug || '');
          var dummyMatch = null;
          for (var j = 0; j < DUMMY_VALUES.length; j++) {
            if (DUMMY_VALUES[j].match.test(key2)) { dummyMatch = DUMMY_VALUES[j].value; break; }
          }
          $el.text(f.value || dummyMatch || key2 || '');
          $canvas.append($el);
        }
      });
      console.debug('Sticker preview rendered', { dimensions: format?.dimensions, scale: scale, fieldCount: (format?.fields || []).length, fields: format?.fields });
    }

    function loadStickerPreview(id) {
      if (!id || !STICKER_PREVIEW_URL) { clearStickerPreview(); return; }
      clearStickerPreview('Loading preview…');
      $.getJSON(STICKER_PREVIEW_URL + '/' + id)
        .done(function (res) {
          if (res && res.format) { renderStickerPreview(res.format); }
          else { clearStickerPreview('Could not load this sticker format.'); }
        })
        .fail(function () { clearStickerPreview('Could not load this sticker format.'); });
    }

    $('#sticker_format_id').on('change', function () {
      var $opt = $(this).find('option:selected');
      $('#sticker_format_name').val($opt.data('name') ? String($opt.data('name')) : '');
      loadStickerPreview(String($(this).val() || ''));
    });
    if ($('#sticker_format_id').val()) {
      loadStickerPreview(String($('#sticker_format_id').val()));
    }

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    }); }

    function loadCategoryConfig() {
      var cat = String($('#device_category_id').val() || '');
      var $row = $('#configFieldsRow');
      if (!cat) {
        $row.html('<div class="col-md-12"><span class="po-hint">Select a device category to load its configuration.</span></div>');
        return;
      }
      $row.html('<div class="col-md-12"><span class="po-hint">Loading configuration…</span></div>');
      $.getJSON(CONFIG_URL, { category_id: cat })
        .done(function (res) {
          var fields = (res && res.fields) || [];
          if (!fields.length) {
            $row.html('<div class="col-md-12"><span class="po-hint">No configuration fields for this category.</span></div>');
            return;
          }
          var html = '';
          fields.forEach(function (f) {
            var fval = overrideVal(f.key, f.value);
            var req = f.required ? ' <span class="po-required">*</span>' : '';
            var reqAttr = f.required ? ' required' : '';
            var nm = 'config[' + esc(f.key) + ']';
            html += '<div class="col-md-6"><div class="form-group">';
            html += '<label class="control-label">' + esc(f.label) + req + '</label>';
            if (f.type === 'select' && f.options && f.options.length) {
              html += '<select name="' + nm + '" class="form-control"' + reqAttr + '>';
              html += '<option value="">Select</option>';
              f.options.forEach(function (o) {
                var sel = (String(o.value) === String(fval)) ? ' selected' : '';
                html += '<option value="' + esc(o.value) + '"' + sel + '>' + esc(o.label) + '</option>';
              });
              html += '</select>';
            } else if (f.type === 'number') {
              var minA = (f.min !== null && f.min !== undefined && f.min !== '') ? ' min="' + esc(f.min) + '"' : '';
              var maxA = (f.max !== null && f.max !== undefined && f.max !== '') ? ' max="' + esc(f.max) + '"' : '';
              var ttl = (minA || maxA) ? ' title="Allowed range: ' + esc(f.min != null ? f.min : '') + '–' + esc(f.max != null ? f.max : '') + '"' : '';
              html += '<input type="number" name="' + nm + '" class="form-control" value="' + esc(fval) + '"' + reqAttr + minA + maxA + ttl + '>';
            } else {
              var maxA2 = f.maxLength ? ' maxlength="' + esc(f.maxLength) + '"' : '';
              var patA = (f.type === 'IP/URL') ? ' pattern="[A-Za-z0-9._:/\\-]+" title="Enter a valid IP or URL"' : '';
              html += '<input type="text" name="' + nm + '" class="form-control" value="' + esc(fval) + '"' + reqAttr + maxA2 + patA + '>';
            }
            html += '</div></div>';
          });
          $row.html(html);
        })
        .fail(function () {
          $row.html('<div class="col-md-12"><span class="po-hint error">Could not load configuration. Please retry.</span></div>');
        });
    }

    function selectedUserId() { return CURRENT_USER; }

    function mapFirmwareOpts(rows) {
      return (rows || []).map(function (f) {
        return {
          id: String(f.id), name: f.name, category: String(f.device_category_id || ''),
          backendId: f.backend_id != null ? String(f.backend_id) : '', backendName: f.backend_name || '',
          stateId: f.state_id != null ? String(f.state_id) : '', stateName: f.state_name || '',
        };
      });
    }
    var FIRMWARE_OPTS = mapFirmwareOpts($form.data('firmwares'));

    function rebuildSelect($sel, options, placeholder, keepVal) {
      var wasS2 = isSelect2($sel);
      if (wasS2) { try { $sel.select2('destroy'); } catch (e) {} }
      $sel.empty().append($('<option>').val('').text(placeholder));
      options.forEach(function (o) {
        var $o = $('<option>').val(o.id).text(o.name);
        if (o.id === keepVal && keepVal !== '') $o.prop('selected', true);
        $sel.append($o);
      });
      if (wasS2) { $sel.select2({ width: '100%' }); }
    }

    function uniqueById(items, idKey, nameKey) {
      var seen = {}, out = [];
      items.forEach(function (it) {
        if (it[idKey] === '' || seen[it[idKey]]) return;
        seen[it[idKey]] = true;
        out.push({ id: it[idKey], name: it[nameKey] });
      });
      return out;
    }

    function resolveFirmware() {
      var cat = String($('#device_category_id').val() || '');
      var backendId = String($('#backend_id').val() || '');
      var stateId = String($('#state_id').val() || '');
      var $display = $('#firmware_display'), $hidden = $('#firmware_id');

      if (!cat || !backendId || !stateId) {
        $display.val(''); $hidden.val('');
        $('#firmwareHint').text('Select Device Category, Backend & State to resolve the firmware.');
        lookupModel();
        return;
      }
      var match = FIRMWARE_OPTS.filter(function (f) {
        return f.category === cat && f.backendId === backendId && f.stateId === stateId;
      })[0];

      if (match) {
        $display.val(match.name); $hidden.val(match.id);
        $('#firmwareHint').text('');
      } else {
        $display.val(''); $hidden.val('');
        $('#firmwareHint').text('No firmware configured for this Device Category, Backend & State combination.');
      }
      lookupModel();
    }

    function rebuildState() {
      var cat = String($('#device_category_id').val() || '');
      var backendId = String($('#backend_id').val() || '');
      var $state = $('#state_id');
      var keep = String($state.val() || '');

      if (!backendId) {
        rebuildSelect($state, [], 'Select Backend first', '');
        $state.prop('disabled', true);
        try { $state.select2('disable'); } catch (e) {}
        resolveFirmware();
        return;
      }
      var states = uniqueById(FIRMWARE_OPTS.filter(function (f) {
        return f.category === cat && f.backendId === backendId;
      }), 'stateId', 'stateName');

      rebuildSelect($state, states, 'Select State', keep);
      $state.prop('disabled', false);
      try { $state.select2('enable'); } catch (e) {}
      resolveFirmware();
    }

    function rebuildFirmwareBackend() {
      var cat = String($('#device_category_id').val() || '');
      var $backend = $('#backend_id');
      var keep = String($backend.val() || '');

      var backends = cat
        ? uniqueById(FIRMWARE_OPTS.filter(function (f) { return f.category === cat; }), 'backendId', 'backendName')
        : [];

      rebuildSelect($backend, backends, cat ? 'Select Backend' : 'Select Device Category first', keep);
      rebuildState();
    }

    $('#backend_id').on('change', rebuildState);
    $('#state_id').on('change', resolveFirmware);

    function lookupModel() {
      var userId = selectedUserId();
      var firmwareId = String($('#firmware_id').val() || '');
      var $model = $('#model_name'), $vendor = $('#vendor_id'), $hint = $('#modelHint');

      // Never let a stale value from a previous account/firmware combination
      // ride along into the submit — clear first, only the successful
      // "found" branch below re-populates them.
      $model.val('');
      $vendor.val('');

      if (!userId || !firmwareId) {
        $hint.removeClass('error ok').text('Select a firmware to load the model.');
        return;
      }

      $hint.removeClass('error ok').text('Looking up model…');
      $.getJSON(LOOKUP_URL, { user_id: userId, firmware_id: firmwareId })
        .done(function (res) {
          if (res && res.found) {
            $model.val(res.model_name);
            $vendor.val(res.vendor_id);
            $hint.removeClass('error').addClass('ok').text('Model matched.');
          } else {
            $hint.removeClass('ok error').text('No model configured for this account & firmware — you can still save the SKU.');
          }
        })
        .fail(function () {
          $hint.removeClass('ok').addClass('error').text('Could not load model. Please retry — Sales will treat a blank Model/Vendor ID as "not yet allotted".');
        });
    }

    var PROFILE_OPTS = $('#esim_profile_1 option').map(function () {
      return { v: String($(this).val()), t: $(this).text() };
    }).get().filter(function (o) { return o.v !== '' && o.v !== 'others'; });

    function isSelect2($el) {
      return $el.hasClass('select2-offscreen') || $el.hasClass('select2-hidden-accessible') || !!$el.data('select2');
    }

    function rebuildProfile($sel, placeholder, excludeVal, keepVal) {
      var wasS2 = isSelect2($sel);
      if (wasS2) { try { $sel.select2('destroy'); } catch (e) {} }

      $sel.empty().append($('<option>').val('').text(placeholder));
      PROFILE_OPTS.forEach(function (o) {
        if (o.v === excludeVal && excludeVal !== 'others') return;
        var $o = $('<option>').val(o.v).text(o.t);
        if (o.v === keepVal && keepVal !== '') $o.prop('selected', true);
        $sel.append($o);
      });
      // "Others" is never excluded — both profiles may be typed in.
      var $othersOpt = $('<option>').val('others').text('Others');
      if (keepVal === 'others') { $othersOpt.prop('selected', true); }
      $sel.append($othersOpt);

      if (wasS2) { $sel.select2({ width: '100%' }); }
    }

    $('#esim_profile_1').on('change', function () {
      rebuildProfile($('#esim_profile_2'), 'Select Profile 2',
        String($(this).val() || ''), String($('#esim_profile_2').val() || ''));
    });
    $('#esim_profile_2').on('change', function () {
      rebuildProfile($('#esim_profile_1'), 'Select Profile 1',
        String($(this).val() || ''), String($('#esim_profile_1').val() || ''));
    });

    (function () {
      var v1 = String($('#esim_profile_1').val() || '');
      if (v1) {
        rebuildProfile($('#esim_profile_2'), 'Select Profile 2', v1, String($('#esim_profile_2').val() || ''));
      }
    })();

    // "Others" in a JSD select reveals a free-text box for the real value
    // (only in JSD mode — customer mode has its own free-text inputs).
    var OTHERS_PAIRS = [['#esim_make', '#esim_make_other'], ['#esim_profile_1', '#esim_profile_1_other'], ['#esim_profile_2', '#esim_profile_2_other']];
    function syncOthers() {
      var jsd = String($('#esim_provider').val() || 'jsd') !== 'customer' && !$('#esim_provider').prop('disabled');
      OTHERS_PAIRS.forEach(function (p) {
        var on = jsd && String($(p[0]).val() || '') === 'others';
        $(p[1]).toggle(on).prop('disabled', !on).prop('required', on);
      });
    }
    $('#esim_make, #esim_profile_1, #esim_profile_2').on('change.others', function () { syncOthers(); syncApnFields(); });

    // APN Profile 1/2 are needed whenever the eSIM isn't fully from JSD's
    // catalog — customer-supplied, or any "Others" make/profile.
    function syncApnFields() {
      var isEsimCategory = String($('#device_category_id option:selected').data('is-esim')) === '1';
      var isCustomer = String($('#esim_provider').val() || '') === 'customer';
      var needsApn = isEsimCategory && (isCustomer || OTHERS_PAIRS.some(function (p) { return String($(p[0]).val() || '') === 'others'; }));
      $('#esimApnRow').toggle(needsApn);
      $('#esim_apn_1, #esim_apn_2').prop('disabled', !needsApn).prop('required', needsApn);
    }

    function setProfilesEnabled(enabled) {
      var $p1 = $('#esim_profile_1'), $p2 = $('#esim_profile_2');
      $p1.prop('disabled', !enabled);
      $p2.prop('disabled', !enabled);
      try { $p1.select2('enable', enabled); } catch (e) {}
      try { $p2.select2('enable', enabled); } catch (e) {}
    }

    function setEsimProviderMode(provider) {
      var isCustomer = provider === 'customer';

      $('#esimJsdMakeCol').toggle(!isCustomer);
      $('#esimCustomerMakeCol').toggle(isCustomer);
      $('#esimJsdProfileRow').toggle(!isCustomer);
      $('#esimCustomerProfileRow').toggle(isCustomer);
      $('#esimRechargeCol').toggle(!isCustomer);
      $('#firmwareCol').toggleClass('col-md-6', !isCustomer).toggleClass('col-md-12', isCustomer);

      var $recharge = $('#esim_recharge_period');
      $recharge.prop('disabled', isCustomer).prop('required', !isCustomer);
      try { $recharge.select2(isCustomer ? 'disable' : 'enable'); } catch (e) {}

      var $jsdMake = $('#esim_make'), $custMake = $('#esim_make_customer');
      var $jsdP1 = $('#esim_profile_1'), $custP1 = $('#esim_profile_1_customer');
      var $jsdP2 = $('#esim_profile_2'), $custP2 = $('#esim_profile_2_customer');

      $jsdMake.prop('disabled', isCustomer).prop('required', !isCustomer);
      try { $jsdMake.select2(isCustomer ? 'disable' : 'enable'); } catch (e) {}
      $custMake.prop('disabled', !isCustomer).prop('required', isCustomer);

      setProfilesEnabled(!isCustomer);
      $jsdP1.prop('required', !isCustomer);
      $jsdP2.prop('required', !isCustomer);
      $custP1.prop('disabled', !isCustomer).prop('required', isCustomer);
      $custP2.prop('disabled', !isCustomer).prop('required', isCustomer);
      syncOthers();
      syncApnFields();
    }

    // Show the eSIM inputs only when the selected Device Category has eSIM
    // enabled — otherwise hide + disable them all so nothing is submitted
    // or required for a non-eSIM category (same rule as skus/create).
    function applyEsimVisibilityForCategory() {
      var isEsimCategory = String($('#device_category_id option:selected').data('is-esim')) === '1';
      var $provider = $('#esim_provider');

      $('#esimProviderCol').toggle(isEsimCategory);
      $provider.prop('disabled', !isEsimCategory).prop('required', isEsimCategory);
      try { $provider.select2(isEsimCategory ? 'enable' : 'disable'); } catch (e) {}

      if (isEsimCategory) {
        setEsimProviderMode(String($provider.val() || 'jsd'));
        return;
      }
      $('#esimJsdMakeCol, #esimCustomerMakeCol, #esimJsdProfileRow, #esimCustomerProfileRow, #esimRechargeCol').hide();
      $('#firmwareCol').removeClass('col-md-6').addClass('col-md-12');
      [
        '#esim_make', '#esim_make_customer',
        '#esim_profile_1', '#esim_profile_1_customer',
        '#esim_profile_2', '#esim_profile_2_customer',
        '#esim_recharge_period',
        '#esim_make_other', '#esim_profile_1_other', '#esim_profile_2_other',
      ].forEach(function (sel) {
        var $f = $(sel);
        $f.prop('disabled', true).prop('required', false);
        try { $f.select2('disable'); } catch (e) {}
      });
      $('#esim_make_other, #esim_profile_1_other, #esim_profile_2_other').hide();
      syncApnFields();
    }

    $('#esim_provider').on('change', function () {
      setEsimProviderMode(String($(this).val() || 'jsd'));
    });
    applyEsimVisibilityForCategory();

    $('#device_category_id').on('change', function () { applyEsimVisibilityForCategory(); rebuildFirmwareBackend(); loadCategoryConfig(); });

    rebuildFirmwareBackend();
    // Pre-select Backend/State from the SKU's already-saved firmware.
    var initialFirmwareId = String($('#firmware_id').val() || '');
    if (initialFirmwareId) {
      var prevFw = FIRMWARE_OPTS.filter(function (f) { return f.id === initialFirmwareId; })[0];
      if (prevFw) {
        $('#backend_id').val(prevFw.backendId).trigger('change');
        $('#state_id').val(prevFw.stateId).trigger('change');
      }
    }
    if ($('#device_category_id').val()) { loadCategoryConfig(); }

    (function initWizard() {
      var $steps = $('#poForm .po-section');
      var total = $steps.length;
      if (total < 2) { $('#poBackBtn, #poNextBtn').hide(); return; }

      $form.addClass('po-wizard-on');
      var current = 0;
      var $stepper = $('#poStepper');

      $steps.each(function (i) {
        var $t = $(this).find('.po-section-title').clone();
        $t.find('.po-step, small, i').remove();
        var label = $.trim($t.text());
        $stepper.append(
          '<div class="po-stepper-item" data-i="' + i + '">' +
            '<div class="po-stepper-dot">' + (i + 1) + '</div>' +
            '<div class="po-stepper-label">' + label + '</div>' +
          '</div>'
        );
      });

      function markPanelValidity($panel) {
        var ok = true;
        $panel.find('[required]').each(function () {
          var $f = $(this);
          var val = $.trim(String($f.val() == null ? '' : $f.val()));
          if (val === '') { ok = false; $f.closest('.form-group').addClass('has-error'); }
          else { $f.closest('.form-group').removeClass('has-error'); }
        });
        $panel.find('#configFieldsRow input, #configFieldsRow select').each(function () {
          if (this.checkValidity && !this.checkValidity()) {
            ok = false; $(this).closest('.form-group').addClass('has-error');
          }
        });
        if ($panel.find('#backend_id, #state_id').length && !String($('#firmware_id').val() || '')) {
          ok = false;
          $('#firmware_display').closest('.form-group').addClass('has-error');
        }
        return ok;
      }

      function showStep(n) {
        current = n;
        $steps.each(function (i) { $(this).toggle(i === n); });
        $stepper.find('.po-stepper-item').each(function (i) {
          $(this).toggleClass('active', i === n).toggleClass('done', i < n);
          $(this).find('.po-stepper-dot').html(i < n ? '<i class="fa fa-check"></i>' : (i + 1));
        });
        $('#poBackBtn').toggle(n > 0);
        $('#poNextBtn').toggle(n < total - 1);
        $('#poSubmitBtn').toggle(n === total - 1);
        var top = $('#poForm').offset().top - 90;
        $('html, body').animate({ scrollTop: top < 0 ? 0 : top }, 200);
      }

      $('#poNextBtn').on('click', function (e) {
        e.preventDefault();
        if (markPanelValidity($steps.eq(current))) {
          showStep(Math.min(current + 1, total - 1));
        } else {
          var $bad = $steps.eq(current).find('.has-error').first().find('.form-control, select').first();
          $bad.focus();
          var el = $bad.get(0);
          if (el && el.reportValidity && el.checkValidity && !el.checkValidity()) { el.reportValidity(); }
        }
      });
      $('#poBackBtn').on('click', function (e) { e.preventDefault(); showStep(Math.max(current - 1, 0)); });
      $stepper.on('click', '.po-stepper-item.done', function () { showStep(parseInt($(this).data('i'), 10)); });

      $form.on('submit', function (e) {
        for (var i = 0; i < total; i++) {
          if (!markPanelValidity($steps.eq(i))) { e.preventDefault(); showStep(i); return; }
        }
      });

      showStep(0);
    })();
  });
</script>
@endsection
