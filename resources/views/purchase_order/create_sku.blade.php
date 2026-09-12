@extends('layouts.apps')

@push('styles')
<style>
  .po-card { border-radius: 14px; overflow: hidden; box-shadow: 0 12px 34px rgba(0,0,0,.28); }
  .po-card .c_title { background: linear-gradient(90deg, rgba(34,197,94,.16), rgba(34,197,94,0) 60%); padding: 18px 24px; margin: 0; border-bottom: 1px solid rgba(148,163,184,.16); }
  .po-card .c_title h2 { font-size: 20px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 10px; }
  .po-card .c_title .brand-gps-mark { display: inline-flex; width: 22px; height: 22px; color: #22c55e; }
  .po-card .c_title .brand-gps-mark svg { width: 100%; height: 100%; fill: currentColor; }
  .po-card .c_content { padding: 20px 24px 24px; }
  .po-intro { color: #5b6b82; font-size: 13px; margin: -4px 0 18px; }
  .po-form .form-group { margin-bottom: 18px; }
  .po-form label.control-label { font-weight: 600; font-size: 12.5px; color: #334155; margin-bottom: 7px; display: block; }
  .po-form .form-control { height: 44px; border-radius: 9px; font-size: 14px; border: 1px solid rgba(148,163,184,.35); }
  .po-form .form-control:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.16); }
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
  .po-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid rgba(148,163,184,.16); padding-top: 18px; margin-top: 4px; }
  .po-actions .btn {
    appearance: none; -webkit-appearance: none; box-sizing: border-box;
    width: 210px; height: 44px; margin: 0; border-radius: 9px;
    font-family: inherit; font-size: 14px; font-weight: 600; line-height: 1; white-space: nowrap;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  }
  .po-actions .btn-success { background: #22c55e; border-color: #22c55e; }
  .po-actions .btn-success:hover, .po-actions .btn-success:focus { background: #16a34a; border-color: #16a34a; }
  .po-empty-hint { border-radius: 12px; }

  .po-section { background: rgba(148,163,184,.045); border: 1px solid rgba(148,163,184,.14); border-radius: 12px; padding: 18px 18px 2px; margin-bottom: 18px; }
  .po-section-title { display: flex; align-items: center; gap: 9px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 1.1px; font-weight: 700; color: #475569; margin: 0 0 16px; }
  .po-section-title i { color: #22c55e; font-size: 14px; }
  .po-logistics-toggle { display: flex; gap: 10px; margin-bottom: 18px; }
  .po-logistics-toggle label {
    flex: 1; display: flex; align-items: center; gap: 8px; cursor: pointer;
    border: 1px solid rgba(148,163,184,.35); border-radius: 9px; padding: 10px 14px; font-size: 13.5px; font-weight: 600; color: #334155;
    transition: all .15s ease;
  }
  .po-logistics-toggle input { margin: 0; }
  .po-logistics-toggle label.active { border-color: #22c55e; background: rgba(34,197,94,.08); color: #16a34a; }
  .po-form textarea.form-control { height: auto; }
  .po-checkbox-row { display: flex; align-items: center; gap: 8px; height: 44px; font-size: 13.5px; font-weight: 600; color: #334155; cursor: pointer; }
  .po-checkbox-row input[type="checkbox"] { position: static !important; float: none !important; width: 16px; height: 16px; margin: 0 !important; }

  /* Breadcrumb */
  .var-breadcrumb-wrap { padding: 14px 0 18px 0; }
  .var-breadcrumb { display: inline-flex; align-items: center; background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px; box-shadow: 0 4px 16px rgba(30,41,59,.18); }
  .var-breadcrumb .bc-home { width: 30px; height: 30px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; flex-shrink: 0; }
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
        <a href="/{{ $url_type }}/purchase-orders" class="bc-item">Purchase Orders</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Raise PO</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel po-card">
          <div class="c_title">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
              <h2 style="margin:0;">@include('partials.brand-gps-mark') Raise Purchase Order</h2>
              <div>
                <a href="/{{ $url_type }}/purchase-orders" class="btn btn-default"><i class="fa fa-list"></i> View POs</a>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>

          <div class="c_content">
            <p class="po-intro">
              A Purchase Order is raised against an approved SKU. Select the SKU, then enter the quantity and expected delivery date.
              Don't have an approved SKU yet? <a href="/skus/create">Create one</a> first.
            </p>

            @include('partials.gps-inline-alerts')
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul style="margin:0;padding-left:18px;">
                  @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="/{{ $url_type }}/purchase-orders" class="po-form" id="poSkuForm"
                  data-skus-url="/{{ $url_type }}/purchase-orders/account-skus"
                  data-sku-details='@json($mySkuDetails->values())'>
              @csrf

              <div class="form-group">
                <label class="control-label">Raise PO for Account <span class="po-required">*</span></label>
                @if ($is_admin)
                  <select name="raised_by_user_id" id="raised_by_user_id" class="select2" required>
                    <option value="">Select Account</option>
                    @foreach ($accounts as $acc)
                      <option value="{{ $acc->id }}" {{ old('raised_by_user_id') == $acc->id ? 'selected' : '' }}>
                        {{ $acc->name }} ({{ $acc->user_type === 'Reseller' ? 'Manufacturer' : 'Dealer' }})
                      </option>
                    @endforeach
                  </select>
                @else
                  <input type="text" class="form-control" value="{{ Auth::user()->name }} ({{ Auth::user()->user_type === 'Reseller' ? 'Manufacturer' : 'Dealer' }})" readonly>
                @endif
              </div>

              <div class="form-group">
                <label class="control-label">SKU <span class="po-required">*</span></label>
                <select name="sku_id" id="sku_id" class="select2" required @if($is_admin) disabled @endif>
                  <option value="">{{ $is_admin ? 'Select an account first' : 'Select SKU' }}</option>
                  @if(!$is_admin)
                    @foreach ($mySkus as $s)
                      <option value="{{ $s->id }}" {{ old('sku_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->sku_code }} — {{ $s->device_category_name }} ({{ $s->esim_make }})
                      </option>
                    @endforeach
                  @endif
                </select>
                @if(!$is_admin && $mySkus->isEmpty())
                  <div class="alert alert-warning po-empty-hint" style="margin-top:10px;">
                    You have no SKUs yet. <a href="/skus/create">Create one</a> before raising a PO.
                  </div>
                @endif
              </div>

              <div id="skuDetailsPanel" class="po-section" style="display:none;">
                <div class="po-section-title"><i class="fa fa-info-circle"></i> SKU Details</div>
                <div class="row" id="skuDetailsGrid"></div>
                <div id="skuConfigWrap" style="display:none;margin:4px 0 16px;">
                  <a href="#" id="skuConfigToggle" style="font-size:12.5px;font-weight:700;color:#16a34a;text-decoration:none;">
                    <i class="fa fa-caret-right" id="skuConfigCaret"></i> <span id="skuConfigLabel">Configuration</span>
                  </a>
                  <div class="row" id="skuConfigGrid" style="display:none;margin-top:12px;"></div>
                </div>
              </div>

              <div class="form-group">
                <label class="control-label">Required Quantity <span class="po-required">*</span></label>
                <input type="number" name="required_quantity" class="form-control" min="1" value="{{ old('required_quantity') }}" placeholder="Enter quantity" required>
              </div>

              {{-- LOGISTICS --}}
              <div class="po-section">
                <div class="po-section-title"><i class="fa fa-truck"></i> Logistics</div>

                <div class="form-group">
                  <label class="control-label">How would you like to receive this order? <span class="po-required">*</span></label>
                  <div class="po-logistics-toggle">
                    <label id="logisticsLabelUs">
                      <input type="radio" name="logistics_managed_by" value="us" {{ old('logistics_managed_by', 'us') == 'us' ? 'checked' : '' }}>
                      Deliver it to me (JSD arranges delivery)
                    </label>
                    <label id="logisticsLabelCustomer">
                      <input type="radio" name="logistics_managed_by" value="customer" {{ old('logistics_managed_by') == 'customer' ? 'checked' : '' }}>
                      I'll arrange my own pickup
                    </label>
                  </div>
                </div>

                {{-- "Deliver it to me" fields --}}
                <div id="logisticsUsFields">
                  <div class="form-group">
                    <label class="control-label">Delivery Address <span class="po-required">*</span></label>
                    <textarea name="delivery_address" class="form-control" rows="2" placeholder="Full shipping address">{{ old('delivery_address') }}</textarea>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Contact Person at Delivery Site <span class="po-required">*</span></label>
                        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name') }}" placeholder="Who should we hand it to">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Contact Phone <span class="po-required">*</span></label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}" placeholder="Phone number">
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label">Delivery Mode</label>
                    <select name="delivery_mode" class="form-control">
                      <option value="">Select (optional)</option>
                      <option value="Road" {{ old('delivery_mode') == 'Road' ? 'selected' : '' }}>Road</option>
                      <option value="Air" {{ old('delivery_mode') == 'Air' ? 'selected' : '' }}>Air</option>
                      <option value="Rail" {{ old('delivery_mode') == 'Rail' ? 'selected' : '' }}>Rail</option>
                      <option value="Self Pickup" {{ old('delivery_mode') == 'Self Pickup' ? 'selected' : '' }}>Self Pickup</option>
                      <option value="Porter" {{ old('delivery_mode') == 'Porter' ? 'selected' : '' }}>Porter</option>
                    </select>
                  </div>
                </div>

                {{-- "I'll arrange my own pickup" fields --}}
                <div id="logisticsCustomerFields" style="display:none;">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">My Transporter's Name <span class="po-required">*</span></label>
                        <input type="text" name="transporter_name" class="form-control" value="{{ old('transporter_name') }}" placeholder="Who will pick it up">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Transporter Contact Number <span class="po-required">*</span></label>
                        <input type="text" name="transporter_contact" class="form-control" value="{{ old('transporter_contact') }}" placeholder="Phone number">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Vehicle Number <span class="po-required">*</span></label>
                        <input type="text" name="vehicle_number" class="form-control" value="{{ old('vehicle_number') }}" placeholder="For gate-pass / security clearance">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="control-label">Pickup Date &amp; Time <span class="po-required">*</span></label>
                        <input type="datetime-local" name="pickup_datetime" class="form-control" value="{{ old('pickup_datetime') }}">
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label">Authorized Pickup Person</label>
                    <input type="text" name="pickup_person_name" class="form-control" value="{{ old('pickup_person_name') }}" placeholder="Name of the person collecting it (optional)">
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label">Special Instructions</label>
                  <textarea name="special_instructions" class="form-control" rows="2" placeholder="Fragile, temperature-sensitive, etc. (optional)">{{ old('special_instructions') }}</textarea>
                </div>
              </div>

              <div class="po-actions">
                <a href="/{{ $url_type }}/purchase-orders" class="btn btn-default">Cancel</a>
                <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Submit Purchase Order</button>
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
    // ---------------- Logistics toggle ----------------
    var $usFields = $('#logisticsUsFields');
    var $customerFields = $('#logisticsCustomerFields');
    var $labelUs = $('#logisticsLabelUs');
    var $labelCustomer = $('#logisticsLabelCustomer');

    function setRequired($container, required) {
      $container.find('input, textarea, select').each(function () {
        var $f = $(this);
        var name = $f.attr('name');
        // Only the "starred" fields are conditionally required — leave optional ones alone.
        if (['delivery_address', 'contact_name', 'contact_phone', 'transporter_name', 'transporter_contact', 'vehicle_number', 'pickup_datetime'].indexOf(name) !== -1) {
          $f.prop('required', required);
        }
      });
    }

    function applyLogisticsMode() {
      var mode = $('input[name="logistics_managed_by"]:checked').val() || 'us';
      var isUs = mode === 'us';
      $usFields.toggle(isUs);
      $customerFields.toggle(!isUs);
      setRequired($usFields, isUs);
      setRequired($customerFields, !isUs);
      $labelUs.toggleClass('active', isUs);
      $labelCustomer.toggleClass('active', !isUs);
    }

    $('input[name="logistics_managed_by"]').on('change', applyLogisticsMode);
    applyLogisticsMode();

    // ---------------- SKU details (view mode once an SKU is selected) ----------------
    var $sku = $('#sku_id');
    var $detailsPanel = $('#skuDetailsPanel');
    var $detailsGrid = $('#skuDetailsGrid');
    var $configWrap = $('#skuConfigWrap');
    var $configGrid = $('#skuConfigGrid');
    var $configToggle = $('#skuConfigToggle');
    var $configCaret = $('#skuConfigCaret');
    var $configLabel = $('#skuConfigLabel');
    var SKU_DETAILS = {};

    $configToggle.on('click', function (e) {
      e.preventDefault();
      var open = $configGrid.is(':visible');
      $configGrid.toggle(!open);
      $configCaret.toggleClass('fa-caret-right fa-caret-down');
    });

    function indexSkuDetails(list) {
      (list || []).forEach(function (d) { if (d && d.id) SKU_DETAILS[String(d.id)] = d; });
    }
    indexSkuDetails($('#poSkuForm').data('sku-details'));

    var CARTON_LABELS = { direct_master_carton: 'Direct Master Carton (Default)', unit_packaging: 'Unit Packaging' };
    var RECHARGE_LABELS = { '1_year': '1 Year', '2_year': '2 Years' };

    function detailField(label, value) {
      if (!value) return '';
      return '<div class="col-md-4" style="margin-bottom:14px;">' +
        '<div style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;font-weight:700;color:#94a3b8;margin-bottom:3px;">' + label + '</div>' +
        '<div style="font-size:13.5px;font-weight:600;color:#334155;">' + $('<div>').text(value).html() + '</div>' +
      '</div>';
    }

    function renderSkuDetails() {
      var id = String($sku.val() || '');
      var d = SKU_DETAILS[id];
      if (!d) { $detailsPanel.hide(); $detailsGrid.empty(); $configWrap.hide(); $configGrid.empty().hide(); return; }

      var html = ''
        + detailField('Device Category', d.device_category_name)
        + detailField('Firmware', d.firmware_name)
        + detailField('eSIM Provider', d.esim_provider === 'jsd' ? 'JSD' : d.esim_provider === 'customer' ? 'Customer' : d.esim_provider)
        + detailField('eSIM Make', d.esim_make)
        + detailField('eSIM Profile 1', d.esim_profile_1)
        + detailField('eSIM Profile 2', d.esim_profile_2)
        + detailField('eSIM Recharge Period', RECHARGE_LABELS[d.esim_recharge_period] || d.esim_recharge_period)
        + detailField('Model Name', d.model_name)
        + detailField('Vendor ID', d.vendor_id)
        + detailField('Sample Serial Number Format', d.serial_number_format)
        + detailField('Packaging Type', CARTON_LABELS[d.carton_type] || d.carton_type)
        + detailField('Sticker Format', d.sticker_format_name)
        + detailField('FG BOM Number', d.fg_bom_number)
        + detailField('Tranzact ID', d.tranzact_id);

      $detailsGrid.html(html);
      $detailsPanel.toggle(!!html);

      var config = d.configuration || [];
      $configCaret.removeClass('fa-caret-down').addClass('fa-caret-right');
      $configGrid.hide();
      if (config.length) {
        $configLabel.text('Configuration (' + config.length + ' fields)');
        $configGrid.html(config.map(function (c) { return detailField(c.label, c.value); }).join(''));
        $configWrap.show();
      } else {
        $configWrap.hide();
        $configGrid.empty();
      }
    }

    $sku.on('change', renderSkuDetails);
    renderSkuDetails();

    var IS_ADMIN = {{ $is_admin ? 'true' : 'false' }};
    if (!IS_ADMIN) return;

    var SKUS_URL = $('#poSkuForm').data('skus-url');

    function loadSkus(userId) {
      if (isSelect2($sku)) { try { $sku.select2('destroy'); } catch (e) {} }
      if (!userId) {
        $sku.empty().append($('<option>').val('').text('Select an account first')).prop('disabled', true);
        $sku.select2({ width: '100%' });
        renderSkuDetails();
        return;
      }
      $sku.empty().append($('<option>').val('').text('Loading…')).prop('disabled', true);
      $sku.select2({ width: '100%' });
      $.getJSON(SKUS_URL, { user_id: userId }).done(function (res) {
        var skus = (res && res.skus) || [];
        indexSkuDetails(res && res.details);
        if (isSelect2($sku)) { try { $sku.select2('destroy'); } catch (e) {} }
        $sku.empty().append($('<option>').val('').text(skus.length ? 'Select SKU' : 'No approved SKUs for this account'));
        skus.forEach(function (s) { $sku.append($('<option>').val(String(s.id)).text(s.label)); });
        $sku.prop('disabled', false);
        $sku.select2({ width: '100%' });
        $sku.trigger('change');
      });
    }

    function isSelect2($el) {
      return $el.hasClass('select2-offscreen') || $el.hasClass('select2-hidden-accessible') || !!$el.data('select2');
    }

    $('#raised_by_user_id').on('change', function () { loadSkus(String($(this).val() || '')); });

    var pre = String($('#raised_by_user_id').val() || '');
    if (pre) { loadSkus(pre); }
  });
</script>
@endsection
