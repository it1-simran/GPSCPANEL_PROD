@extends('layouts.apps')

@push('styles')
<style>
  .po-wrap { max-width: 1060px; margin: 0 auto; }

  /* Card + header */
  .po-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .po-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .po-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .po-card .c_title h2 i { color: #22c55e; }
  .po-card .c_content { padding: 20px 24px 24px; }
  .po-intro { color: #5b6b82; font-size: 13px; margin: -4px 0 18px; }

  /* Grouped sections */
  .po-section { background: rgba(148,163,184,.045); border: 1px solid rgba(148,163,184,.14); border-radius: 12px; padding: 18px 18px 2px; margin-bottom: 18px; }
  .po-section-title { display: flex; align-items: center; gap: 9px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #475569; margin: 0 0 16px; }
  .po-section-title i { color: #22c55e; font-size: 14px; }
  .po-section-title small { text-transform: none; letter-spacing: 0; font-weight: 500; color: #64748b; }
  .po-step { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: rgba(34,197,94,.16); color: #22c55e; font-size: 12px; font-weight: 700; margin-right: 2px; }
  .po-subhead { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .8px; margin: 6px 0 14px; padding-top: 14px; border-top: 1px dashed rgba(148,163,184,.2); }
  .po-subhead i { color: #22c55e; }
  .po-subhead small { text-transform: none; letter-spacing: 0; font-weight: 500; color: #64748b; }

  /* Fields */
  .po-form .form-group { margin-bottom: 18px; }
  .po-form label.control-label { font-weight: 600; font-size: 12.5px; color: #334155; margin-bottom: 7px; display: block; letter-spacing: .2px; }
  .po-form .form-control { height: 44px; border-radius: 9px; font-size: 14px; border: 1px solid rgba(148,163,184,.35); box-shadow: none; transition: border-color .15s ease, box-shadow .15s ease; }
  .po-form .form-control:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.16); }
  .po-form select.form-control { padding: 8px 12px; }
  .po-form .readonly-field { background: rgba(148,163,184,.12); color: #5b6b82; cursor: not-allowed; }
  .po-form .readonly-field:focus { box-shadow: none; border-color: rgba(148,163,184,.35); }
  .po-hint { font-size: 11.5px; color: #5b6b82; margin-top: 5px; display: block; min-height: 15px; }
  .po-hint.error { color: #ef4444; }
  .po-hint.ok { color: #22c55e; }
  .po-required { color: #ef4444; }

  /* select2 v3.5.2 sizing to match native inputs (vertically centered) */
  .po-form .select2-container { width: 100% !important; }
  .po-form .select2-container .select2-choice {
    height: 44px !important; line-height: 44px !important; border-radius: 9px !important;
    border: 1px solid rgba(148,163,184,.35) !important;
    padding: 0 36px 0 12px !important; display: flex !important; align-items: center !important;
    background-image: none !important;
  }
  .po-form .select2-container .select2-choice > span {
    line-height: 1.2 !important; margin: 0 !important; overflow: hidden; text-overflow: ellipsis;
  }
  .po-form .select2-container .select2-choice .select2-arrow {
    top: 0 !important; height: 44px !important; width: 30px !important; border-radius: 0 9px 9px 0;
    background: transparent !important; border-left: 1px solid rgba(148,163,184,.25);
  }
  /* Hide the select2 sprite arrow and draw a single clean caret. */
  .po-form .select2-container .select2-choice .select2-arrow b { display: none !important; }
  .po-form .select2-container .select2-choice .select2-arrow::after {
    content: ''; position: absolute; top: 50%; right: 11px; width: 0; height: 0; margin-top: -3px;
    border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #64748b;
  }
  .po-form .select2-container .select2-choice abbr { top: 15px; }
  .po-form .select2-container.select2-container-disabled .select2-choice { background: rgba(148,163,184,.12); opacity: .7; }

  /* Configuration subsection */
  #configFieldsRow .form-control { height: 40px; }
  .po-config-hint { color: #5b6b82; font-size: 13px; padding: 4px 2px 12px; display: block; }

  /* Sticky action bar */
  .po-actions { display: flex; justify-content: flex-end; align-items: center; gap: 10px; border-top: 1px solid rgba(148,163,184,.16); padding-top: 18px; margin-top: 4px; }
  .po-actions .btn { min-width: 130px; height: 44px; border-radius: 9px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
  .po-actions .btn-success { background: #22c55e; border-color: #22c55e; }
  .po-actions .btn-success:hover, .po-actions .btn-success:focus { background: #16a34a; border-color: #16a34a; }

  /* Wizard stepper */
  .po-stepper { display: flex; margin: 6px 0 24px; padding: 0; }
  .po-stepper-item { flex: 1; text-align: center; position: relative; cursor: default; }
  .po-stepper-item:not(:first-child)::before { content: ''; position: absolute; top: 16px; left: -50%; width: 100%; height: 3px; background: rgba(148,163,184,.22); z-index: 0; border-radius: 3px; }
  .po-stepper-item.active::before, .po-stepper-item.done::before { background: #22c55e; }
  .po-stepper-dot { position: relative; z-index: 1; width: 34px; height: 34px; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; background: rgba(148,163,184,.18); color: #5b6b82; transition: all .2s ease; }
  .po-stepper-item.active .po-stepper-dot { background: #22c55e; color: #fff; box-shadow: 0 0 0 5px rgba(34,197,94,.16); }
  .po-stepper-item.done .po-stepper-dot { background: #16a34a; color: #fff; }
  .po-stepper-item.done { cursor: pointer; }
  .po-stepper-label { font-size: 11.5px; color: #5b6b82; font-weight: 600; letter-spacing: .2px; padding: 0 4px; }
  .po-stepper-item.active .po-stepper-label { color: #1e293b; }

  /* Wizard sections hide the inline step badge (stepper shows the number) */
  .po-wizard-on .po-section-title .po-step { display: none; }
  .po-wizard-on .po-section { margin-bottom: 0; }

  /* Validation error state */
  .has-error .form-control { border-color: #ef4444 !important; box-shadow: 0 0 0 3px rgba(239,68,68,.12) !important; }
  .has-error .select2-container .select2-choice { border-color: #ef4444 !important; }
  .po-actions .spacer { flex: 1; }

  @media (max-width: 640px) {
    .po-card .c_content { padding: 16px; }
    .po-section { padding: 14px 14px 2px; }
    .po-stepper-label { display: none; }
    .po-actions { flex-wrap: wrap; }
    .po-actions .btn { flex: 1; }
  }
</style>
@endpush

@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="row">
      <div class="col-md-12 po-wrap">
        <div class="c_panel po-card">
          <div class="c_title">
            <div class="row bgx-title-container" style="display:flex;align-items:center;">
              <div class="col-lg-8"><h2><i class="fa fa-file-text-o"></i> {{ !empty($resubmit) ? 'Edit & Resubmit Purchase Order' : 'Raise Purchase Order' }}</h2></div>
              <div class="col-lg-4 text-right">
                <a href="/{{ $url_type }}/purchase-orders" class="btn btn-default"><i class="fa fa-list"></i> View POs</a>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>

          <div class="c_content">
            @php $pf = $prefill ?? []; $isResubmit = !empty($resubmit); @endphp
            <p class="po-intro">
              @if ($isResubmit)
                Review the Sales team's directions below, update the details, and resubmit this Purchase Order for approval.
              @else
                Fill in the device, eSIM and configuration details below. Fields marked <span class="po-required">*</span> are required; the order is sent to the Sales team for approval.
              @endif
            </p>

            @if ($isResubmit && !empty($sales_directions))
              <div class="alert alert-warning" style="border-radius:12px;">
                <strong><i class="fa fa-comment-o"></i> Sales directions:</strong> {{ $sales_directions }}
              </div>
            @endif

            @include('partials.gps-inline-alerts')

            @if ($errors->any())
              <div class="alert alert-danger">
                <ul style="margin:0;padding-left:18px;">
                  @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ $isResubmit ? '/'.$url_type.'/purchase-orders/'.$po_id.'/resubmit' : '/'.$url_type.'/purchase-orders' }}" class="po-form" id="poForm"
                  data-current-user="{{ $current_user_id }}"
                  data-resubmit-user="{{ $isResubmit ? ($locked_account['id'] ?? '') : '' }}"
                  data-config-overrides="{{ json_encode($config_overrides ?? (object)[]) }}"
                  data-lookup-url="/{{ $url_type }}/purchase-orders/model-lookup"
                  data-config-url="/{{ $url_type }}/purchase-orders/category-config"
                  data-assign-url="/{{ $url_type }}/purchase-orders/account-assignments"
                  style="margin-top:15px;">
              @csrf

              {{-- Wizard step indicator (populated by JS) --}}
              <div class="po-stepper" id="poStepper"></div>

              {{-- ACCOUNT --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">1</span><i class="fa fa-user"></i> Account</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Raise PO for Account <span class="po-required">*</span></label>
                    @if ($isResubmit)
                      <input type="text" class="form-control readonly-field"
                        value="{{ $locked_account['name'] ?? '' }}{{ !empty($locked_account['role']) ? ' ('.($locked_account['role'] === 'Reseller' ? 'Manufacturer' : 'Dealer').')' : '' }}" readonly>
                      <span class="po-hint">Resubmitting on behalf of this account (unchanged).</span>
                    @elseif ($is_admin)
                      <select name="raised_by_user_id" id="raised_by_user_id" class="select2" required>
                        <option value="">Select Account</option>
                        @foreach ($accounts as $acc)
                          <option value="{{ $acc->id }}" {{ old('raised_by_user_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->name }} ({{ $acc->user_type === 'Reseller' ? 'Manufacturer' : 'Dealer' }})
                          </option>
                        @endforeach
                      </select>
                      <span class="po-hint">The PO will be created on behalf of this account.</span>
                    @else
                      <input type="text" class="form-control readonly-field"
                        value="{{ Auth::user()->name }} ({{ Auth::user()->user_type === 'Reseller' ? 'Manufacturer' : 'Dealer' }})" readonly>
                      <span class="po-hint">This PO will be raised for your account — the currently logged-in user.</span>
                    @endif
                  </div>
                </div>
              </div>
              </div>

              {{-- DEVICE & eSIM --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">2</span><i class="fa fa-microchip"></i> Device, eSIM &amp; Model</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Device Category <span class="po-required">*</span></label>
                    <select name="device_category_id" id="device_category_id" class="select2" required @if($is_admin && !$isResubmit) disabled @endif>
                      <option value="">{{ $is_admin && !$isResubmit ? 'Select an account first' : 'Select Device Category' }}</option>
                      @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('device_category_id', $pf['device_category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                          {{ $cat->device_category_name }}
                        </option>
                      @endforeach
                    </select>
                    <span class="po-hint" id="categoryHint">{{ $is_admin && !$isResubmit ? 'Only the selected account\'s assigned categories are shown.' : '' }}</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Make <span class="po-required">*</span></label>
                    <select name="esim_make" id="esim_make" class="select2" required>
                      <option value="">Select eSIM Make</option>
                      @foreach ($esimMakes as $mk)
                        <option value="{{ $mk['name'] }}" {{ old('esim_make', $pf['esim_make'] ?? '') == $mk['name'] ? 'selected' : '' }}>{{ $mk['name'] }}</option>
                      @endforeach
                    </select>
                    <span class="po-hint">
                      @if ($esimError)
                        <span class="error">eSIM data unavailable from MES ({{ $esimError }}).</span>
                      @else
                        Fetched from MES. Every eSIM has two profiles.
                      @endif
                    </span>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Profile 1 <span class="po-required">*</span></label>
                    <select name="esim_profile_1" id="esim_profile_1" class="select2" required>
                      <option value="">Select Profile 1</option>
                      @foreach ($esimProfiles as $prof)
                        <option value="{{ $prof['name'] }}" {{ old('esim_profile_1', $pf['esim_profile_1'] ?? '') == $prof['name'] ? 'selected' : '' }}>{{ $prof['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Profile 2 <span class="po-required">*</span></label>
                    <select name="esim_profile_2" id="esim_profile_2" class="select2" required>
                      <option value="">Select Profile 2</option>
                      @foreach ($esimProfiles as $prof)
                        <option value="{{ $prof['name'] }}" {{ old('esim_profile_2', $pf['esim_profile_2'] ?? '') == $prof['name'] ? 'selected' : '' }}>{{ $prof['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">eSIM Recharge Period <span class="po-required">*</span></label>
                    <select name="esim_recharge_period" class="select2" required>
                      <option value="">Select Period</option>
                      <option value="1_year" {{ old('esim_recharge_period', $pf['esim_recharge_period'] ?? '') == '1_year' ? 'selected' : '' }}>1 Year</option>
                      <option value="2_year" {{ old('esim_recharge_period', $pf['esim_recharge_period'] ?? '') == '2_year' ? 'selected' : '' }}>2 Years</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Default Firmware <span class="po-required">*</span></label>
                    <select name="firmware_id" id="firmware_id" class="select2" required @if($is_admin && !$isResubmit) disabled @endif>
                      <option value="">{{ $is_admin && !$isResubmit ? 'Select an account first' : 'Select Firmware' }}</option>
                      @foreach ($firmwares as $fw)
                        <option value="{{ $fw->id }}" data-category="{{ $fw->device_category_id }}"
                          {{ old('firmware_id', $pf['firmware_id'] ?? '') == $fw->id ? 'selected' : '' }}>{{ $fw->name }}</option>
                      @endforeach
                    </select>
                    <span class="po-hint" id="firmwareHint">Firmware options depend on the selected device category.</span>
                  </div>
                </div>
              </div>

              {{-- MODEL (auto-filled) — combined into the Device & eSIM step --}}
              <div class="po-subhead"><i class="fa fa-cube"></i> Model <small>auto-filled from account &amp; firmware</small></div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Model Name</label>
                    <input type="text" name="model_name" id="model_name" class="form-control readonly-field"
                      value="{{ old('model_name', $pf['model_name'] ?? '') }}" placeholder="Auto-filled from Account &amp; Firmware" readonly>
                    <span class="po-hint" id="modelHint"></span>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Vendor ID</label>
                    <input type="text" name="vendor_id" id="vendor_id" class="form-control readonly-field"
                      value="{{ old('vendor_id', $pf['vendor_id'] ?? '') }}" placeholder="Auto-filled from Model" readonly>
                  </div>
                </div>
              </div>

              </div>{{-- /device, esim & model section --}}

              {{-- CONFIGURATION (device-category fields, generated on category select) --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">3</span><i class="fa fa-sliders"></i> Configuration <small>from device category</small></div>
              <div class="row" id="configFieldsRow">
                <div class="col-md-12"><span class="po-config-hint" id="configHint">Select a device category to load its configuration.</span></div>
              </div>
              </div>{{-- /configuration section --}}

              {{-- ORDER --}}
              <div class="po-section">
              <div class="po-section-title"><span class="po-step">4</span><i class="fa fa-shopping-cart"></i> Order</div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Expected Delivery Date <span class="po-required">*</span></label>
                    <input type="date" name="expected_delivery_date" class="form-control"
                      value="{{ old('expected_delivery_date', $pf['expected_delivery_date'] ?? '') }}" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="control-label">Required Quantity <span class="po-required">*</span></label>
                    <input type="number" name="required_quantity" class="form-control" min="1"
                      value="{{ old('required_quantity', $pf['required_quantity'] ?? '') }}" placeholder="Enter quantity" required>
                  </div>
                </div>
              </div>

              </div>{{-- /order section --}}

              <div class="po-actions">
                <a href="/{{ $url_type }}/purchase-orders" class="btn btn-default">Cancel</a>
                <span class="spacer"></span>
                <button type="button" class="btn btn-default" id="poBackBtn"><i class="fa fa-arrow-left"></i> Back</button>
                <button type="button" class="btn btn-success" id="poNextBtn">Next <i class="fa fa-arrow-right"></i></button>
                <button type="submit" class="btn btn-success" id="poSubmitBtn"><i class="fa fa-check"></i> {{ !empty($resubmit) ? 'Resubmit Purchase Order' : 'Submit Purchase Order' }}</button>
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
<script>
  $(document).ready(function () {
    var $form = $('#poForm');
    var IS_ADMIN = {{ $is_admin ? 'true' : 'false' }};
    var CURRENT_USER = String($form.data('current-user'));
    // In resubmit mode the account is locked (no dropdown) — use its id for lookups.
    var RESUBMIT_USER = String($form.data('resubmit-user') || '');
    // Saved configuration values (resubmit): normalized-key -> saved value.
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
    var ASSIGN_URL = $form.data('assign-url');

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    }); }

    // Render the selected device category's configuration fields (editable,
    // pre-filled with defaults). Values submit as config[<key>].
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
            // Prefer the saved PO value (resubmit); fall back to the category default.
            var fval = overrideVal(f.key, f.value);
            var req = f.required ? ' <span class="po-required">*</span>' : '';
            // Same validation rules devices/templates enforce.
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
              // text, hex, IP/URL — text input with maxlength; IP/URL adds a format pattern.
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

    // NOTE: select2 (v3.5.2) is already initialized globally by forms.js on
    // every .select2 element — do NOT re-init here (double-init throws
    // "query function not defined" and corrupts the widget).

    // The account the PO is for: admin picks it; others use their own id.
    function selectedUserId() {
      if (RESUBMIT_USER) return RESUBMIT_USER;
      return IS_ADMIN ? String($('#raised_by_user_id').val() || '') : CURRENT_USER;
    }

    // Full firmware list captured once (value, label, category) so the select
    // can be rebuilt on category change. select2 v3.5.2 needs destroy + re-init
    // to refresh options — hiding <option>s does nothing in its dropdown.
    var FIRMWARE_OPTS = $('#firmware_id option').map(function () {
      return { v: String($(this).val()), t: $(this).text(), c: String($(this).data('category') || '') };
    }).get().filter(function (o) { return o.v !== ''; });

    // Show only firmware belonging to the chosen device category.
    function filterFirmwareByCategory() {
      var cat = String($('#device_category_id').val() || '');
      var $fw = $('#firmware_id');
      var keep = String($fw.val() || '');
      var wasS2 = isSelect2($fw);
      if (wasS2) { try { $fw.select2('destroy'); } catch (e) {} }

      $fw.empty().append($('<option>').val('').text('Select Firmware'));
      FIRMWARE_OPTS.forEach(function (o) {
        if (cat && o.c !== cat) return;             // only this category's firmware
        var $o = $('<option>').val(o.v).text(o.t);
        if (o.v === keep && keep !== '') $o.prop('selected', true);
        $fw.append($o);
      });

      if (wasS2) { $fw.select2({ width: '100%' }); }
      $('#firmwareHint').text(cat ? '' : 'Firmware options depend on the selected device category.');
    }

    // Auto-fill Model Name + Vendor ID from (account, firmware).
    function lookupModel() {
      var userId = selectedUserId();
      var firmwareId = String($('#firmware_id').val() || '');
      var $model = $('#model_name'), $vendor = $('#vendor_id'), $hint = $('#modelHint');

      if (!userId || !firmwareId) {
        $model.val(''); $vendor.val('');
        $hint.removeClass('error ok').text(
          IS_ADMIN && !userId ? 'Select an account and firmware to load the model.'
                              : 'Select a firmware to load the model.'
        );
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
            $model.val(''); $vendor.val('');
            $hint.removeClass('ok error').text('No model configured for this account & firmware — you can still submit the PO.');
          }
        })
        .fail(function () {
          $model.val(''); $vendor.val('');
          $hint.removeClass('ok').addClass('error').text('Could not load model. Please retry.');
        });
    }

    // eSIM Profile 1 and Profile 2 must be different: the value chosen in one is
    // removed entirely from the other's option list. select2 here is v3.5.2, so
    // options are refreshed via destroy + re-init (not the v4 change.select2).
    var PROFILE_OPTS = $('#esim_profile_1 option').map(function () {
      return { v: String($(this).val()), t: $(this).text() };
    }).get().filter(function (o) { return o.v !== ''; });

    function isSelect2($el) {
      return $el.hasClass('select2-offscreen') || $el.hasClass('select2-hidden-accessible') || !!$el.data('select2');
    }

    // Rebuild $sel's options, dropping excludeVal, keeping keepVal selected.
    function rebuildProfile($sel, placeholder, excludeVal, keepVal) {
      var wasS2 = isSelect2($sel);
      if (wasS2) { try { $sel.select2('destroy'); } catch (e) {} }

      $sel.empty().append($('<option>').val('').text(placeholder));
      PROFILE_OPTS.forEach(function (o) {
        if (o.v === excludeVal) return;               // hide the other's choice
        var $o = $('<option>').val(o.v).text(o.t);
        if (o.v === keepVal && keepVal !== '') $o.prop('selected', true);
        $sel.append($o);
      });

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

    // Profiles are locked until an eSIM Make is chosen.
    function setProfilesEnabled(enabled) {
      var $p1 = $('#esim_profile_1'), $p2 = $('#esim_profile_2');
      if (!enabled) {
        // Reset both to the full, empty list when locking.
        rebuildProfile($p1, 'Select Profile 1', '', '');
        rebuildProfile($p2, 'Select Profile 2', '', '');
      }
      $p1.prop('disabled', !enabled);
      $p2.prop('disabled', !enabled);
      try { $p1.select2('enable', enabled); } catch (e) {}
      try { $p2.select2('enable', enabled); } catch (e) {}
    }

    $('#esim_make').on('change', function () {
      setProfilesEnabled(String($(this).val() || '') !== '');
    });

    // Initial state: gate on make, and apply any old() exclusion after a redirect.
    setProfilesEnabled(String($('#esim_make').val() || '') !== '');
    (function () {
      var v1 = String($('#esim_profile_1').val() || '');
      if (v1) {
        rebuildProfile($('#esim_profile_2'), 'Select Profile 2', v1, String($('#esim_profile_2').val() || ''));
      }
    })();

    // Enable/disable the category + firmware selects together (select2 v3.5.2).
    function setDeviceFieldsEnabled(enabled) {
      $('#device_category_id').prop('disabled', !enabled);
      $('#firmware_id').prop('disabled', !enabled);
      try { $('#device_category_id').select2('enable', enabled); } catch (e) {}
      try { $('#firmware_id').select2('enable', enabled); } catch (e) {}
    }

    // Rebuild the Device Category dropdown (destroy + re-init for v3.5.2).
    function rebuildCategory(cats, lock) {
      var $cat = $('#device_category_id');
      if (isSelect2($cat)) { try { $cat.select2('destroy'); } catch (e) {} }
      $cat.empty().append($('<option>').val('').text(lock ? 'Select an account first' : 'Select Device Category'));
      cats.forEach(function (c) { $cat.append($('<option>').val(String(c.id)).text(c.name)); });
      $cat.select2({ width: '100%' });
    }

    // Admin: fetch the selected account's assigned categories + firmware and
    // repopulate both dropdowns, resetting the dependent fields.
    function loadAccountAssignments(userId) {
      if (!userId) {
        rebuildCategory([], true);
        FIRMWARE_OPTS = [];
        filterFirmwareByCategory();
        setDeviceFieldsEnabled(false);
        $('#categoryHint').text('Select an account first.');
        return;
      }
      $('#categoryHint').text('Loading assigned options…');
      $.getJSON(ASSIGN_URL, { user_id: userId })
        .done(function (res) {
          var cats = (res && res.categories) || [];
          FIRMWARE_OPTS = ((res && res.firmware) || []).map(function (f) {
            return { v: String(f.id), t: f.name, c: String(f.device_category_id || '') };
          });
          rebuildCategory(cats, false);
          setDeviceFieldsEnabled(true);
          filterFirmwareByCategory();   // empties firmware until a category is chosen
          loadCategoryConfig();
          lookupModel();
          $('#categoryHint').text(cats.length
            ? "Only the selected account's assigned categories are shown."
            : 'No device categories are assigned to this account.');
        })
        .fail(function () { $('#categoryHint').text('Could not load assignments. Please retry.'); });
    }

    $('#device_category_id').on('change', function () { filterFirmwareByCategory(); lookupModel(); loadCategoryConfig(); });
    $('#firmware_id').on('change', lookupModel);
    if (IS_ADMIN) {
      $('#raised_by_user_id').on('change', function () { loadAccountAssignments(String($(this).val() || '')); });
    }

    // Initial state (handles old() values after a validation redirect).
    if (IS_ADMIN && !RESUBMIT_USER) {
      // Admin lists are account-scoped: load them if an account is already picked.
      var preAcc = String($('#raised_by_user_id').val() || '');
      if (preAcc) { loadAccountAssignments(preAcc); } else { setDeviceFieldsEnabled(false); }
    } else {
      filterFirmwareByCategory();
      if ($('#firmware_id').val()) { lookupModel(); }
      if ($('#device_category_id').val()) { loadCategoryConfig(); }
    }

    // Model is optional — a PO can be raised even when no model is configured
    // for the chosen account & firmware.

    // ---------------- Multi-step wizard ----------------
    (function initWizard() {
      var $steps = $('#poForm .po-section');
      var total = $steps.length;
      if (total < 2) { $('#poBackBtn, #poNextBtn').hide(); return; }

      $form.addClass('po-wizard-on');
      var current = 0;
      var $stepper = $('#poStepper');

      // Build the stepper from each section's title.
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
        // Device-category configuration rules (min/max/maxlength/pattern) via the
        // native constraint API — mirrors the device/template validation.
        $panel.find('#configFieldsRow input, #configFieldsRow select').each(function () {
          if (this.checkValidity && !this.checkValidity()) {
            ok = false; $(this).closest('.form-group').addClass('has-error');
          }
        });
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

      // Let users jump back to a completed step by clicking the stepper.
      $stepper.on('click', '.po-stepper-item.done', function () { showStep(parseInt($(this).data('i'), 10)); });

      // On submit, validate every step; jump to the first invalid one.
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
