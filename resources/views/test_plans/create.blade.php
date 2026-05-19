@extends('layouts.apps')


@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('test-plans-create') }}">
@endpush
@section('title', 'Create Test Plan')

@section('content')


<section id="main-content" class="test-plan-create-page">
    <section class="wrapper">
        <div class="row">
            <div class="col-xs-12 col-md-10 col-md-offset-1">
                <section class="panel" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none;">
                    <header class="panel-heading" style="background: #0f172a !important; color: white !important; border-radius: 15px 15px 0 0; padding: 25px !important; border: none;">
                        <i class="fa fa-magic" style="margin-right: 10px; color: #96c93d;"></i> <strong style="font-size: 18px; letter-spacing: 0.5px;">Design Your Automation Workflow</strong>
                    </header>
                    <div class="panel-body" style="padding: 35px;">
                        @php
                            $userType = auth()->check() ? strtolower(trim((string) auth()->user()->user_type)) : '';
                            $routePrefix = $userType === 'support' ? 'support' : 'admin';
                        @endphp
                        <form action="{{ route($routePrefix . '.test-plans.store') }}" method="POST" class="form-horizontal tasi-form" id="test-plan-form">
                            @csrf
                            {{-- Plan Info Card --}}
                            <div class="plan-info-card">
                                <div class="section-header" style="margin-bottom: 22px;">
                                    <div class="section-title">
                                        <i class="fa fa-info-circle"></i> Plan Details
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-6">
                                        <div class="pf-group">
                                            <label class="pf-label">
                                                <i class="fa fa-tag"></i> Plan Name
                                            </label>
                                            <input type="text" name="name" class="pf-input"
                                                placeholder="e.g., Critical Alert Validation" required>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="pf-group">
                                            <label class="pf-label">
                                                <i class="fa fa-exchange"></i> Protocol
                                            </label>
                                            <select name="protocol_id" id="plan_protocol_id" class="pf-input" required>
                                                <option value="">Select Protocol...</option>
                                                @foreach($protocols as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-md-12">
                                        <div class="pf-group">
                                            <label class="pf-label">
                                                <i class="fa fa-align-left"></i> Description
                                            </label>
                                            <input type="text" name="description" class="pf-input"
                                                placeholder="Briefly describe the purpose of this test plan...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Execution Sequence --}}
                            <div class="section-header">
                                <div class="section-title">
                                    <i class="fa fa-list-ol"></i> Execution Sequence
                                </div>
                                <span class="badge" id="step-count-badge"
                                    style="background:#f0fce8; color:#3d8b08; padding:6px 14px; font-weight:800; border:1px solid #c6f6d5; border-radius:20px;">
                                    0 STEPS
                                </span>
                            </div>

                            <div id="steps-container" class="steps-drop-zone">
                                <div id="empty-steps-msg" style="text-align:center; color:#a0aec0; padding:40px 0;">
                                    <i class="fa fa-cubes fa-3x" style="display:block; margin-bottom:18px; opacity:0.2;"></i>
                                    <p style="font-size:14px; font-weight:600;">No actions added yet.</p>
                                    <p class="small">Use the buttons below to build your automation sequence.</p>
                                </div>
                            </div>

                            {{-- Add Step Action Bar --}}
                            <div class="action-bar">
                                <p class="action-bar-title"><i class="fa fa-plus-circle" style="color:#76CF1C; margin-right:5px;"></i> Add Action to Sequence</p>
                                <div class="add-step-actions-wrap" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 4px;">
                                    <button type="button" class="add-step-btn add-step" data-type="send_command">
                                        <i class="fa fa-paper-plane"></i> Send Command
                                    </button>
                                    <button type="button" class="add-step-btn add-step" data-type="wait_for_response">
                                        <i class="fa fa-clock-o"></i> Wait
                                    </button>
                                    <button type="button" class="add-step-btn add-step" data-type="validate_response">
                                        <i class="fa fa-check-square-o"></i> Validate
                                    </button>
                                    <button type="button" class="add-step-btn add-step" data-type="alert_evaluation">
                                        <i class="fa fa-bell-o"></i> Alert
                                    </button>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="form-footer">
                                <a href="{{ route($routePrefix . '.test-plans.index') }}" class="btn-cancel" style="margin-top: 10px;">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                                <button type="submit" class="btn-save">
                                    <i class="fa fa-save"></i> Save Test Plan
                                </button>
                            </div>

                        </form>
                    </div>

                </section>
            </div>
        </div>
    </section>
</section>

<!-- Step Templates -->
<template id="step-template-send_command">
    <div class="panel panel-default step-item" data-type="send_command" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #96c93d; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading step-handle step-item-heading" style="cursor: move; background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important;">
            <div style="display: flex; align-items: center;">
                <i class="fa fa-arrows" style="margin-right: 15px; color: #cbd5e0; font-size: 16px;"></i>
                <span class="step-index badge" style="background: #0f172a; color: white; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #0f172a; letter-spacing: 0.5px;">SEND COMMAND</strong>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-default btn-xs toggle-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #edf2f7; border: none; color: #4a5568;"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="send_command">
            <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group" style="margin: 0;">
                        <label>Command Type</label>
                        <select name="steps[INDEX][config][command_type]" class="form-control" required>
                            <option value="server">Server Command (GPRS)</option>
                            <option value="sms">SMS Command</option>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 col-md-8">
                    <div class="form-group" style="margin: 0;">
                        <label>Command String</label>
                        <input type="text" name="steps[INDEX][config][command_text]" class="form-control" placeholder="e.g., ENG_STOP_CONFIRM" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="step-template-wait_for_response">
    <div class="panel panel-default step-item" data-type="wait_for_response" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #96c93d; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading step-handle step-item-heading" style="cursor: move; background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important;">
            <div style="display: flex; align-items: center;">
                <i class="fa fa-arrows" style="margin-right: 15px; color: #cbd5e0; font-size: 16px;"></i>
                <span class="step-index badge" style="background: #0f172a; color: white; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #0f172a; letter-spacing: 0.5px;">WAIT FOR RESPONSE</strong>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-default btn-xs toggle-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #edf2f7; border: none; color: #4a5568;"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="wait_for_response">
            <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                <div class="col-xs-12 col-md-4">
                    <div class="form-group" style="margin: 0;">
                        <label>Timeout Duration</label>
                        <div class="input-group">
                            <input type="number" name="steps[INDEX][config][timeout_seconds]" class="form-control" value="30" min="1" required style="border-radius: 8px 0 0 8px !important;">
                            <span class="input-group-addon" style="height: 40px; min-width: auto; padding: 0 15px; border-radius: 0 8px 8px 0 !important;">sec</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="step-template-validate_response">
    <div class="panel panel-default step-item" data-type="validate_response" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #96c93d; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading step-handle step-item-heading" style="cursor: move; background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important;">
            <div style="display: flex; align-items: center;">
                <i class="fa fa-arrows" style="margin-right: 15px; color: #cbd5e0; font-size: 16px;"></i>
                <span class="step-index badge" style="background: #0f172a; color: white; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #0f172a; letter-spacing: 0.5px;">VALIDATE PACKET</strong>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-default btn-xs toggle-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #edf2f7; border: none; color: #4a5568;"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="validate_response">
            <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                <div class="col-xs-12 col-md-12">
                    <div class="form-group" style="margin: 0;">
                        <label>Packet Type</label>
                        <select name="steps[INDEX][config][packet_type_id]" class="form-control packet-type-select" required disabled>
                            <option value="">Select Type...</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="rules-section" style="display: none; margin-top: 30px;">
                <label style="color: #2d3748; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Validation Rules</label>
                <div class="rules-container" id="rules-container-INDEX">
                    <!-- Rule rows will be added here -->
                </div>
                <button type="button" class="btn btn-default add-rule-btn" data-step-index="INDEX">
                    <i class="fa fa-plus-circle" style="margin-right: 6px; color: #00b09b;"></i> Add Validation Rule
                </button>
            </div>
        </div>
    </div>
</template>

<template id="step-template-alert_evaluation">
    <div class="panel panel-default step-item" data-type="alert_evaluation" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #96c93d; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading step-handle step-item-heading" style="cursor: move; background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important;">
            <div style="display: flex; align-items: center;">
                <i class="fa fa-arrows" style="margin-right: 15px; color: #cbd5e0; font-size: 16px;"></i>
                <span class="step-index badge" style="background: #0f172a; color: white; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #0f172a; letter-spacing: 0.5px;">EVALUATE ALERTS</strong>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-default btn-xs toggle-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #edf2f7; border: none; color: #4a5568;"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="alert_evaluation">
            <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                <div class="col-xs-12 col-md-12">
                    <div class="form-group" style="margin: 0;">
                        <label>Packet Type</label>
                        <select name="steps[INDEX][config][packet_type_id]" class="form-control packet-type-select" required disabled>
                            <option value="">Select Type...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="alerts-section" style="display: none; margin-top: 25px;">
                <label style="color: #2d3748; font-weight: 800; font-size: 11px; text-transform: uppercase;">Alerts to Validate</label>
                <div class="alerts-container" id="alerts-container-INDEX" style="background: #fdfdfd; border: 1px solid #edf2f7; border-radius: 10px; padding: 15px;">
                    <!-- Alert checkboxes will be added here -->
                </div>
            </div>
        </div>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    const routePrefix = @json($routePrefix);
    let stepCount = 0;
    const packetFields = {}; // Store fields for each packet type
    const packetAlerts = {}; // Store alerts for each packet type

    // Initialize Sortable
    const stepsContainer = document.getElementById('steps-container');
    new Sortable(stepsContainer, {
        animation: 150,
        handle: '.step-handle',
        ghostClass: 'sortable-ghost',
        scroll: true,
        forceFallback: true,
        scrollSensitivity: 100,
        scrollSpeed: 20,
        onEnd: function() {
            updateStepIndices();
        }
    });

    // Expand / Collapse Step
    $(document).on('click', '.toggle-step', function(e) {
        e.stopPropagation();
        const $panelBody = $(this).closest('.step-item').find('.panel-body');
        const $icon = $(this).find('i');
        $panelBody.slideToggle(300, function() {
            if ($panelBody.is(':visible')) {
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });
    });

    let currentPacketTypes = [];

    $('#plan_protocol_id').change(function() {
        const protocolId = $(this).val();
        
        if (!protocolId) {
            currentPacketTypes = [];
            $('.packet-type-select').html('<option value="">Select Type...</option>').prop('disabled', true);
            $('.rules-section, .alerts-section').hide();
            return;
        }

        $('.packet-type-select').html('<option value="">Loading...</option>').prop('disabled', true);

        $.get(`/${routePrefix}/tracker/protocols/${protocolId}/packet-types`, function(data) {
            currentPacketTypes = data.packet_types;
            let options = '<option value="">Select Type...</option>';
            data.packet_types.forEach(function(pt) {
                options += `<option value="${pt.id}">${pt.name}</option>`;
                packetFields[pt.id] = pt.fields;
                packetAlerts[pt.id] = pt.alerts;
            });
            
            if (currentPacketTypes.length === 0) {
                $('.packet-type-select').html('<option value="">No packet types found</option>').prop('disabled', true);
                $('.rules-section, .alerts-section').show();
                $('.rules-container, .alerts-container').html('<div style="padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; color: #c53030;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> No packet types available for this protocol.</div>');
            } else {
                $('.packet-type-select').html(options).prop('disabled', false);
                $('.rules-container').empty();
                $('.alerts-container').empty();
                $('.rules-section, .alerts-section').hide();
            }
        }).fail(function() {
            alert('Error loading packet types. Please try again.');
            $('.packet-type-select').html('<option value="">Error</option>');
        });
    });

    $('.add-step').click(function() {
        const type = $(this).data('type');
        const template = $('#step-template-' + type).html();
        const html = template.replace(/INDEX/g, stepCount);
        
        const $step = $(html);
        $('#steps-container').append($step);
        $('#empty-steps-msg').hide();
        
        if (type === 'validate_response' || type === 'alert_evaluation') {
            const $packetSelect = $step.find('.packet-type-select');
            if (!$('#plan_protocol_id').val()) {
                alert('Please select a Protocol at the top of the form first.');
                $step.find('.rules-section, .alerts-section').show();
                $step.find('.rules-container, .alerts-container').html('<div style="padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; color: #c53030;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> Please select a Protocol first.</div>');
            } else if (currentPacketTypes.length > 0) {
                let options = '<option value="">Select Type...</option>';
                currentPacketTypes.forEach(function(pt) {
                    options += `<option value="${pt.id}">${pt.name}</option>`;
                });
                $packetSelect.html(options).prop('disabled', false);
            } else {
                $packetSelect.html('<option value="">No packet types found</option>').prop('disabled', true);
                $step.find('.rules-section, .alerts-section').show();
                $step.find('.rules-container, .alerts-container').html('<div style="padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; color: #c53030;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> No packet types available for this protocol.</div>');
            }
        }
        
        updateStepIndices();
        stepCount++;
    });

    $(document).on('click', '.remove-step', function() {
        var $btn = $(this);
        Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure you want to remove this step?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Remove',
            cancelButtonText: 'Cancel',
            background: '#1e293b',
            color: '#f8fafc'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.closest('.step-item').remove();
                updateStepIndices();
                if ($('.step-item').length === 0) {
                    $('#empty-steps-msg').show();
                }
            }
        });
    });

    $(document).on('change', '.packet-type-select', function() {
        const packetTypeId = $(this).val();
        const $panelBody = $(this).closest('.panel-body');
        const stepType = $(this).closest('.step-item').data('type');
        
        if (packetTypeId) {
            if (stepType === 'validate_response') {
                const $rulesSection = $panelBody.find('.rules-section');
                $rulesSection.show();
                const stepIdx = $panelBody.find('.add-rule-btn').data('step-index');
                $(`#rules-container-${stepIdx}`).empty();
                addRuleRow(stepIdx, packetTypeId);
            } else if (stepType === 'alert_evaluation') {
                const $alertsSection = $panelBody.find('.alerts-section');
                $alertsSection.show();
                const stepIdx = $(this).closest('.step-item').find('.step-index').text() - 1;
                fetchAndRenderAlerts(packetTypeId, $panelBody.find('.alerts-container'));
            }
        }
    });

    $(document).on('click', '.add-rule-btn', function() {
        const stepIdx = $(this).data('step-index');
        const packetTypeId = $(this).closest('.panel-body').find('.packet-type-select').val();
        addRuleRow(stepIdx, packetTypeId);
    });

    $(document).on('click', '.remove-rule', function() {
        var $btn = $(this);
        Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure you want to remove this rule?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Remove',
            cancelButtonText: 'Cancel',
            background: '#1e293b',
            color: '#f8fafc'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.closest('.rule-row').remove();
            }
        });
    });

    $(document).on('change', '.evaluate-all-alerts', function() {
        const $container = $(this).closest('.alerts-container');
        const $specificAlerts = $container.find('.specific-alerts-container');
        if ($(this).is(':checked')) {
            $specificAlerts.hide();
            $specificAlerts.find('.specific-alert-checkbox').prop('checked', true);
        } else {
            $specificAlerts.show();
        }
    });

    function addRuleRow(stepIdx, packetTypeId) {
        const fields = packetFields[packetTypeId] || [];
        let fieldOptions = '<option value="">Select Field</option>';
        fields.forEach(f => {
            fieldOptions += `<option value="${f.name}">${f.name}</option>`;
        });

        const ruleIdx = $(`#rules-container-${stepIdx} .rule-row`).length;
        const html = `
            <div class="rule-row">
                <select name="steps[${stepIdx}][config][rules][${ruleIdx}][field]" class="form-control" style="flex: 2; min-width: 0;" required>
                    ${fieldOptions}
                </select>
                <select name="steps[${stepIdx}][config][rules][${ruleIdx}][operator]" class="form-control" style="flex: 1.2; min-width: 0;" required>
                    <option value="==">Equals</option>
                    <option value="!=">Not Equal</option>
                    <option value="<=">Less/Equal</option>
                    <option value=">=">Great/Equal</option>
                </select>
                <input type="text" name="steps[${stepIdx}][config][rules][${ruleIdx}][value]" class="form-control" style="flex: 2; min-width: 0;" placeholder="Value" required>
                <button type="button" class="btn btn-danger remove-rule" title="Remove Rule">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        `;
        $(`#rules-container-${stepIdx}`).append(html);
    }

    function fetchAndRenderAlerts(packetTypeId, $container) {
        const stepIdx = $container.attr('id').split('-').pop();
        $container.html('<p class="small text-muted"><i class="fa fa-spinner fa-spin"></i> Loading alerts...</p>');
        
        const alerts = packetAlerts[packetTypeId] || [];
        
        if (alerts.length === 0) {
            $container.html('<div style="padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; color: #c53030;">' +
                    '<i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> No active alerts configured for this packet type.' +
                    '</div>');
            return;
        }
        
        let html = '<div style="margin-bottom: 15px;">' +
            '<div class="checkbox" style="margin:0;"><label style="font-weight:600; color:#4a5568;"><input type="checkbox" name="steps[' + stepIdx + '][config][evaluate_all]" class="evaluate-all-alerts" checked value="1"> Evaluate all active alerts for this packet</label></div>' +
            '</div>';
            
        html += '<div class="specific-alerts-container" style="display: none; flex-wrap:wrap; gap:15px; padding-top: 10px; border-top: 1px dashed #e2e8f0;">';
        alerts.forEach(function(alert) {
            html += '<div class="checkbox" style="margin:0; width: 100%;">' +
                '<label style="color:#4a5568;"><input type="checkbox" name="steps[' + stepIdx + '][config][alert_ids][]" class="specific-alert-checkbox" value="' + alert.id + '" checked> ' + alert.name + '</label>' +
                '</div>';
        });
        html += '</div>';
        
        $container.html(html);
    }

    function updateStepIndices() {
        const count = $('.step-item').length;
        $('#step-count-badge').text(count + (count === 1 ? ' STEP' : ' STEPS'));
        
        $('.step-item').each(function(index) {
            $(this).find('.step-index').text((index + 1));
            
            // Re-index all inputs
            $(this).find('input, select, textarea').each(function() {
                if ($(this).attr('name')) {
                    const newName = $(this).attr('name').replace(/steps\[\d+\]/, 'steps[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
            
            // Update data attributes and IDs for rules/alerts
            $(this).find('.add-rule-btn').data('step-index', index).attr('data-step-index', index);
            $(this).find('.rules-container').attr('id', 'rules-container-' + index);
            $(this).find('.alerts-container').attr('id', 'alerts-container-' + index);
        });
    }
});
</script>
@endsection
