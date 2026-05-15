@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/protocol-alerts-create.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/protocol-alerts-create.css')) }}">
@endpush
@section('content')
<!-- Premium Typography & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
@endphp
<section id="main-content" class="protocol-page alert-config-page">
    <section class="wrapper alert-config-container">
        <div class="protocol-breadcrumb-wrap">
            <nav class="protocol-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ route($routePrefix . '.index') }}" class="bc-item">Protocol Management</a>
                <span class="bc-sep">›</span>
                <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" class="bc-item">Packet Types</a>
                <span class="bc-sep">›</span>
                <a href="{{ route($routePrefix . '.packet-types.alerts', $packetType->id) }}" class="bc-item">Alerts</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">{{ isset($alert) ? 'Edit Alert' : 'Configure New Alert' }}</span>
            </nav>
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
                            <div class="rule-conditions-head">
                                <span class="step-num" aria-hidden="true">02</span>
                                <div class="rule-conditions-head__body">
                                    <h4 class="rule-conditions-head__title">Rule Conditions</h4>
                                    <span id="conditionCount" class="badge-count" role="status">0 Fields Active</span>
                                </div>
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
