@extends('layouts.apps')
@section('content')
<!-- Premium Typography & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<section id="main-content">
    <section class="wrapper alert-config-container">
        <div class="top-page-header mb-4">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        @php $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols'; @endphp
                        <li><a href="{{ route($routePrefix . '.index') }}">Protocols</a></li>
                        <li><a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}">Packet Types</a></li>
                        <li><a href="{{ route($routePrefix . '.packet-types.alerts', $packetType->id) }}">Alerts</a></li>
                        <li class="active"><a href="#">{{ isset($alert) ? 'Edit' : 'Configure New' }} Alert</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="alert-master-card animate__animated animate__fadeIn">
            <!-- Header Section -->
            <div class="alert-header-section">
                <div class="header-content">
                    <div class="alert-badge">
                        <i class="fa fa-bell-o"></i> Alert Builder
                    </div>
                    <h1>{{ isset($alert) ? 'Refine' : 'Create' }} Packet <span>Alert Rule</span></h1>
                    <p>Configure automated triggers for <strong>{{ $packetType->name }}</strong> packets across your device fleet.</p>
                </div>
                <div class="header-illustration">
                    <div class="glow-sphere"></div>
                </div>
            </div>

            <form id="alertForm" class="ultra-premium-form">
                <input type="hidden" name="alert_id" value="{{ $alert->id ?? '' }}">
                
                <div class="row custom-layout-row">
                    <!-- Sidebar: Alert Identity -->
                    <div class="col-lg-4 col-xl-3">
                        <div class="config-sidebar-card">
                            <div class="sidebar-header">
                                <span class="step-num">01</span>
                                <h4>Alert Identity</h4>
                            </div>
                            <div class="sidebar-body">
                                <div class="form-group mb-4">
                                    <label class="u-label">Rule Name</label>
                                    <div class="input-with-icon">
                                        <i class="fa fa-tag"></i>
                                        <input type="text" name="name" value="{{ $alert->name ?? '' }}" class="u-input" required placeholder="e.g. Critical Temp Alert">
                                    </div>
                                    <small class="u-help">Unique name to identify this trigger.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="u-label">Trigger Fields</label>
                                    <select id="fieldSelector" class="u-select2" multiple="multiple" style="width: 100%;">
                                        @foreach($fields as $field)
                                            <option value="{{ $field->id }}" 
                                                @if(isset($alert) && $alert->conditions->contains('packet_field_id', $field->id)) selected @endif
                                                data-name="{{ $field->name }}">
                                                {{ $field->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="u-help">Select fields that will trigger this alert.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="config-sidebar-info mt-4">
                            <div class="info-item">
                                <i class="fa fa-bolt"></i>
                                <span><strong>Real-time:</strong> Evaluated instantly on packet arrival.</span>
                            </div>
                            <div class="info-item">
                                <i class="fa fa-link"></i>
                                <span><strong>AND Logic:</strong> All conditions must be satisfied.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Main Area: Condition Grid -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="config-main-area">
                            <div class="main-area-header d-flex justify-content-between align-items-center w-100">
                                <div class="d-flex align-items-center">
                                    <span class="step-num">02</span>
                                    <h4 style="padding-right: 10px; padding-top:10px">Rule Conditions</h4>
                                </div>
                                <div id="conditionCount" class="badge-count">0 Fields Active</div>
                            </div>

                            <div id="conditionsContainer" class="u-conditions-grid">
                                <!-- Cards will be injected here -->
                                @if(isset($alert))
                                    @foreach($alert->conditions as $cond)
                                        <div class="u-condition-card animate__animated animate__zoomIn" data-field-id="{{ $cond->packet_field_id }}">
                                            <div class="u-card-header">
                                                <div class="field-title">
                                                    <div class="dot"></div>
                                                    {{ $cond->field->name }}
                                                </div>
                                                <button type="button" class="u-remove-btn" onclick="unselectField('{{ $cond->packet_field_id }}')">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="u-card-body">
                                                <div class="condition-inputs">
                                                    <div class="c-input-group">
                                                        <select class="u-minimal-select operator-select">
                                                            <option value="==" {{ $cond->operator == '==' ? 'selected' : '' }}>Equals</option>
                                                            <option value="!=" {{ $cond->operator == '!=' ? 'selected' : '' }}>Not Equal</option>
                                                            <option value="<=" {{ $cond->operator == '<=' ? 'selected' : '' }}>Less/Equal</option>
                                                            <option value=">=" {{ $cond->operator == '>=' ? 'selected' : '' }}>Great/Equal</option>
                                                        </select>
                                                    </div>
                                                    <div class="c-arrow">
                                                        <i class="fa fa-long-arrow-right"></i>
                                                    </div>
                                                    <div class="c-input-group">
                                                        <input type="text" class="u-minimal-input value-input" value="{{ $cond->value }}" placeholder="Value">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div id="emptyState" class="u-empty-state {{ isset($alert) && $alert->conditions->count() > 0 ? 'd-none' : '' }}">
                                <div class="empty-icon">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" width="80">
                                </div>
                                <h5>Building your rule?</h5>
                                <p>Select fields from the left sidebar to start defining your trigger conditions.</p>
                            </div>

                            <div class="u-form-footer mt-5">
                                <a href="{{ route($routePrefix . '.packet-types.alerts', $packetType->id) }}" class="btn-u-secondary" style="margin-top: 10px;">Discard Changes</a>
                                <button type="button" class="btn-u-primary" onclick="saveAlertConfiguration(this)" style="font-size: 15px;">
                                    <i class="fa fa-check-circle"></i> Save Alert Rule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</section>

<style>
    :root {
        --u-primary: #76CF1C;
        --u-primary-dark: #62b115;
        --u-primary-light: #eaf7de;
        --u-accent: #f59e0b;
        --u-bg: #fdfdfe;
        --u-text: #1e293b;
        --u-text-light: #64748b;
        --u-border: #e2e8f0;
        --u-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08);
        --u-card-bg: #ffffff;
    }

    body {
        background-color: #f1f5f9;
        font-family: 'Inter', sans-serif;
    }

    .alert-config-container {
        padding: 30px 40px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .alert-master-card {
        background: var(--u-card-bg);
        border-radius: 24px;
        box-shadow: var(--u-shadow);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    /* Header Section */
    .alert-header-section {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        padding: 60px 50px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .alert-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(118, 207, 28, 0.2);
        color: #81e025;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 20px;
        border: 1px solid rgba(118, 207, 28, 0.3);
    }

    .header-content h1 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 2.5rem;
        margin-bottom: 15px;
        letter-spacing: -0.02em;
        color: white;
    }

    .header-content h1 span {
        color: var(--u-primary);
        background: linear-gradient(to right, #81e025, #76CF1C);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-content p {
        font-size: 1.1rem;
        color: #f1f5f9;
        max-width: 600px;
    }

    .glow-sphere {
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(118, 207, 28, 0.15) 0%, transparent 70%);
        top: -100px;
        right: -100px;
        border-radius: 50%;
    }

    /* Sidebar Area */
    .ultra-premium-form {
        padding: 40px 50px;
    }

    .config-sidebar-card {
        background: #f8fafc;
        border: 1px solid var(--u-border);
        border-radius: 20px;
        padding: 30px;
        position: sticky;
        top: 20px;
    }

    .custom-layout-row {
        gap: 0;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .step-num {
        width: 38px;
        height: 38px;
        background: var(--u-primary);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        box-shadow: 0 4px 10px rgba(118, 207, 28, 0.3);
    }

    .sidebar-header h4 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 1.3rem;
        margin: 0;
        color: var(--u-text);
    }

    .u-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--u-text-light);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }

    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--u-text-light);
    }

    .u-input {
        width: 100%;
        height: 56px;
        border: 1.5px solid var(--u-border);
        border-radius: 14px;
        padding: 10px 15px 10px 45px;
        font-weight: 600;
        font-size: 1.05rem;
        transition: all 0.2s;
        background: white;
    }

    .u-input:focus {
        border-color: var(--u-primary);
        box-shadow: 0 0 0 4px rgba(118, 207, 28, 0.08);
        outline: none;
    }

    .u-help {
        display: block;
        margin-top: 8px;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* Main Area */
    .config-main-area {
        padding-left: 20px;
    }

    .main-area-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--u-border);
    }

    .main-area-header h4 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 1.3rem;
        margin: 0;
        color: var(--u-text);
        margin-left: 15px;
        line-height: 1;
    }

    .badge-count {
        background: var(--u-primary-light);
        color: var(--u-primary);
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    /* Conditions Grid */
    .u-conditions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .u-condition-card {
        background: white;
        border: 1px solid var(--u-border);
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .u-condition-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: var(--u-primary);
        opacity: 0.6;
    }

    .u-condition-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        border-color: var(--u-primary);
    }

    .u-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .field-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--u-text);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .field-title .dot {
        width: 8px;
        height: 8px;
        background: var(--u-primary);
        border-radius: 50%;
    }

    .u-remove-btn {
        background: #fee2e2;
        color: #ef4444;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .u-remove-btn:hover {
        background: #ef4444;
        color: white;
        transform: rotate(90deg);
    }

    .u-minimal-select, .u-minimal-input {
        width: 100%;
        height: 48px;
        background: #f8fafc;
        border: 1px solid var(--u-border);
        border-radius: 10px;
        padding: 0 12px;
        font-size: 1rem;
        font-weight: 600;
        color: var(--u-text);
        transition: all 0.2s;
    }

    .u-minimal-select:focus, .u-minimal-input:focus {
        background: white;
        border-color: var(--u-primary);
        outline: none;
    }

    /* Condition Input Flexbox Layout */
    .condition-inputs {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .c-input-group {
        flex: 1;
        min-width: 0;
    }

    .c-arrow {
        flex: 0 0 20px;
        text-align: center;
        color: #cbd5e1;
        font-size: 0.9rem;
    }

    /* Empty State */
    .u-empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8fafc;
        border-radius: 24px;
        border: 2px dashed var(--u-border);
    }

    .empty-icon {
        margin-bottom: 20px;
        opacity: 0.4;
    }

    .u-empty-state h5 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--u-text);
    }

    .u-empty-state p {
        color: var(--u-text-light);
        font-size: 1rem;
    }

    /* Footer */
    .u-form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
        border-top: 1px solid var(--u-border);
        padding-top: 40px;
    }

    .btn-u-primary {
        background: var(--u-primary);
        color: white;
        border: none;
        height: 54px;
        padding: 0 40px;
        border-radius: 16px;
        font-weight: 700;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        box-shadow: 0 10px 20px -5px rgba(118, 207, 28, 0.4);
        transition: all 0.3s;
    }

    .btn-u-primary:hover {
        background: var(--u-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -5px rgba(118, 207, 28, 0.5);
    }

    .btn-u-secondary {
        height: 54px;
        padding: 0 40px;
        border-radius: 16px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--u-text-light);
        background: white;
        border: 1.5px solid var(--u-border);
        transition: all 0.2s;
    }

    .btn-u-secondary:hover {
        background: #f8fafc;
        color: var(--u-text);
        text-decoration: none;
    }

    /* Select2 Ultra Customization */
    .select2-container--default .select2-selection--multiple {
        border: 1.5px solid var(--u-border) !important;
        border-radius: 14px !important;
        min-height: 52px !important;
        padding: 6px 12px !important;
        background: white !important;
        transition: all 0.2s !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: var(--u-primary) !important;
        box-shadow: 0 0 0 4px rgba(118, 207, 28, 0.08) !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        border-radius: 8px !important;
        padding: 6px 14px !important;
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        margin-top: 4px !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ef4444 !important;
        margin-right: 8px !important;
        border-right: 1px solid #e2e8f0 !important;
        padding-right: 8px !important;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--u-text-light);
        font-size: 0.95rem;
        padding: 10px 0;
    }

    .info-item i {
        color: var(--u-primary);
        font-size: 14px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const initTimer = setInterval(function() {
        if (window.jQuery && window.jQuery.fn.select2) {
            clearInterval(initTimer);
            initUltraPage();
        }
    }, 50);
});

function initUltraPage() {
    const $ = window.jQuery;
    const $selector = $('#fieldSelector');

    $selector.select2({
        placeholder: "Choose fields...",
        allowClear: true
    });

    $selector.on('change select2:select select2:unselect', function() {
        syncRuleConditions();
    });

    window.unselectField = function(id) {
        const val = $selector.val() || [];
        $selector.val(val.filter(v => v != id)).trigger('change');
    };

    function syncRuleConditions() {
        const selected = $selector.val() || [];
        const $container = $('#conditionsContainer');
        
        // Count update
        $('#conditionCount').text(`${selected.length} Field${selected.length !== 1 ? 's' : ''} Active`);

        // Check for removals
        $('.u-condition-card').each(function() {
            const id = $(this).attr('data-field-id');
            if (!selected.includes(id)) {
                $(this).removeClass('animate__zoomIn').addClass('animate__zoomOut');
                setTimeout(() => { $(this).remove(); updateEmptyState(); }, 300);
            }
        });

        // Check for additions
        selected.forEach(id => {
            if ($(`.u-condition-card[data-field-id="${id}"]`).length === 0) {
                const $opt = $selector.find(`option[value="${id}"]`);
                const name = $opt.attr('data-name') || $opt.text().trim();
                renderConditionCard(id, name);
            }
        });

        updateEmptyState();
    }

    function renderConditionCard(id, name) {
        const html = `
            <div class="u-condition-card animate__animated animate__zoomIn" data-field-id="${id}">
                <div class="u-card-header">
                    <div class="field-title">
                        <div class="dot"></div>
                        ${name}
                    </div>
                    <button type="button" class="u-remove-btn" onclick="unselectField('${id}')">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="u-card-body">
                    <div class="condition-inputs">
                        <div class="c-input-group">
                            <select class="u-minimal-select operator-select">
                                <option value="==">Equals</option>
                                <option value="!=">Not Equal</option>
                                <option value="<=">Less/Equal</option>
                                <option value=">=">Great/Equal</option>
                            </select>
                        </div>
                        <div class="c-arrow">
                            <i class="fa fa-long-arrow-right"></i>
                        </div>
                        <div class="c-input-group">
                            <input type="text" class="u-minimal-input value-input" placeholder="Value">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#conditionsContainer').append(html);
    }

    function updateEmptyState() {
        const selected = $('#fieldSelector').val() || [];
        if (selected.length > 0) {
            $('#emptyState').addClass('d-none').hide();
        } else {
            setTimeout(() => {
                const currentSelected = $('#fieldSelector').val() || [];
                if (currentSelected.length === 0 && $('.u-condition-card').length === 0) {
                    $('#emptyState').removeClass('d-none').show();
                }
            }, 350);
        }
    }

    // Run initial sync
    syncRuleConditions();
}

function saveAlertConfiguration(btn) {
    const $ = window.jQuery;
    const alertId = $('input[name="alert_id"]').val();
    const name = $('input[name="name"]').val().trim();
    const conditions = [];

    if (!name) {
        Swal.fire({ icon: 'error', title: 'Name Required', text: 'Please give your alert a descriptive name.', confirmButtonColor: '#76CF1C' });
        return;
    }

    $('.u-condition-card').each(function() {
        conditions.push({
            field_id: $(this).attr('data-field-id'),
            operator: $(this).find('.operator-select').val(),
            value: $(this).find('.value-input').val().trim()
        });
    });

    if (conditions.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No Rules', text: 'You must define at least one condition to save this alert.', confirmButtonColor: '#76CF1C' });
        return;
    }

    if (conditions.some(c => c.value === '')) {
        Swal.fire({ icon: 'error', title: 'Incomplete Rules', text: 'One or more conditions are missing comparison values.', confirmButtonColor: '#76CF1C' });
        return;
    }

    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    const url = alertId 
        ? "{{ route($routePrefix . '.packet-alerts.update', ':id') }}".replace(':id', alertId)
        : "{{ route($routePrefix . '.packet-types.alerts.store', $packetType->id) }}";
    
    $.ajax({
        url: url,
        method: alertId ? 'PUT' : 'POST',
        data: { _token: "{{ csrf_token() }}", name: name, conditions: conditions },
        success: function(res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Rule Saved!', text: res.message, timer: 1500, showConfirmButton: false }).then(() => { window.location.href = res.redirect; });
            } else {
                btn.disabled = false; btn.innerHTML = originalHtml;
                Swal.fire({ icon: 'error', title: 'Save Failed', text: res.message });
            }
        },
        error: function(xhr) {
            btn.disabled = false; btn.innerHTML = originalHtml;
            Swal.fire({ icon: 'error', title: 'Error', text: 'Server communication error. Please try again.' });
        }
    });
}
</script>
@endsection
