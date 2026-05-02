@extends('layouts.apps')

@section('title', 'Create Test Plan')

@section('content')
<style>
    .input-group-addon {
        display: table-cell;
        vertical-align: middle;
        min-width: 45px;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }
    .input-group .form-control {
        height: 45px !important;
        border-color: #e2e8f0 !important;
    }
    .step-item .form-control {
        height: 40px !important;
        border-radius: 8px !important;
    }
    .step-item label {
        margin-bottom: 5px !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #718096;
    }
    .remove-step {
        transition: all 0.2s;
    }
    .remove-step:hover {
        background: #e53e3e !important;
        transform: scale(1.1);
    }
    .rules-container {
        background: #fdfdfd;
        border: 1px solid #edf2f7;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
    }
    .rule-row {
        display: flex;
        gap: 12px;
        margin-bottom: 12px;
        align-items: center;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .rule-row:last-child {
        margin-bottom: 0;
    }
    .rule-row .form-control {
        height: 38px !important;
        font-size: 13px !important;
    }
    .remove-rule {
        background: #fed7d7 !important;
        color: #c53030 !important;
        border: none !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .remove-rule:hover {
        background: #feb2b2 !important;
        color: #9b2c2c !important;
        transform: scale(1.05);
    }
    .add-rule-btn {
        margin-top: 15px;
        font-size: 11px;
        font-weight: 800;
        padding: 8px 16px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: #4a5568;
        transition: all 0.2s;
    }
    .add-rule-btn:hover {
        background: #edf2f7;
        border-color: #cbd5e0;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <section class="panel" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none;">
                    <header class="panel-heading" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%) !important; color: white !important; border-radius: 15px 15px 0 0; padding: 25px !important; border: none;">
                        <i class="fa fa-magic" style="margin-right: 10px;"></i> <strong style="font-size: 18px; letter-spacing: 0.5px;">Design Your Automation Workflow</strong>
                    </header>
                    <div class="panel-body" style="padding: 35px;">
                        <form action="{{ route('admin.test-plans.store') }}" method="POST" class="form-horizontal tasi-form" id="test-plan-form">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group" style="margin: 0 0 25px 0;">
                                        <label class="control-label" style="font-weight: 700; color: #2d3748; margin-bottom: 10px; display: block; text-align: left;">Plan Name</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-tag text-primary"></i></span>
                                            <input type="text" name="name" class="form-control" placeholder="e.g., Critical Alert Validation" style="border-radius: 0 10px 10px 0;" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" style="margin: 0 0 25px 0;">
                                        <label class="control-label" style="font-weight: 700; color: #2d3748; margin-bottom: 10px; display: block; text-align: left;">Description</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-align-left text-primary"></i></span>
                                            <input type="text" name="description" class="form-control" style="border-radius: 0 10px 10px 0;" placeholder="Briefly describe the purpose of this test plan...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin: 40px 0 20px 0; border-bottom: 2px solid #edf2f7; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-weight: 800; color: #4a5568; text-transform: uppercase; font-size: 13px; letter-spacing: 1.5px;">Execution Sequence</h4>
                                <span class="badge" id="step-count-badge" style="background: #ebf8ff; color: #2b6cb0; padding: 6px 12px; font-weight: 800; border: 1px solid #bee3f8;">0 STEPS</span>
                            </div>

                            <div id="steps-container" style="min-height: 120px; background: #f8fafc; border-radius: 15px; padding: 25px; border: 2px dashed #cbd5e0; margin-bottom: 35px;">
                                <div id="empty-steps-msg" style="text-align: center; color: #a0aec0; padding: 50px 0;">
                                    <i class="fa fa-cubes fa-3x" style="display: block; margin-bottom: 20px; opacity: 0.2;"></i>
                                    <p style="font-size: 15px;">No actions added to the sequence yet.</p>
                                    <p class="small">Use the buttons below to start building your automated test.</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                        <p style="font-weight: 800; color: #718096; margin-bottom: 20px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Add Action to Sequence</p>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info add-step" data-type="send_command" style="margin: 5px; background: #4b6cb7 !important; border: none;">
                                                <i class="fa fa-paper-plane" style="margin-right: 5px;"></i> Send Command
                                            </button>
                                            <button type="button" class="btn btn-info add-step" data-type="wait_for_response" style="margin: 5px; background: #718096 !important; border: none;">
                                                <i class="fa fa-clock-o" style="margin-right: 5px;"></i> Wait
                                            </button>
                                            <button type="button" class="btn btn-info add-step" data-type="validate_response" style="margin: 5px; background: #00b09b !important; border: none;">
                                                <i class="fa fa-check-square-o" style="margin-right: 5px;"></i> Validate
                                            </button>
                                            <button type="button" class="btn btn-info add-step" data-type="alert_evaluation" style="margin: 5px; background: #96c93d !important; border: none;">
                                                <i class="fa fa-bell-o" style="margin-right: 5px;"></i> Alert
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 50px; text-align: right; border-top: 2px solid #edf2f7; padding-top: 30px;">
                                <a href="{{ route('admin.test-plans.index') }}" class="btn btn-default" style="padding: 12px 30px; margin-right: 15px; border-radius: 10px; font-weight: 700;">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="padding: 12px 45px; border-radius: 10px; font-weight: 700; background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%) !important; border: none; box-shadow: 0 10px 20px rgba(75, 108, 183, 0.2);">
                                    <i class="fa fa-save" style="margin-right: 8px;"></i> Save Test Plan
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
    <div class="panel panel-default step-item" data-type="send_command" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #4b6cb7; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading" style="background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <span class="step-index badge" style="background: #ebf8ff; color: #2b6cb0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #2d3748; letter-spacing: 0.5px;">SEND COMMAND</strong>
            </div>
            <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="send_command">
            <div class="row" style="display: flex; align-items: flex-end;">
                <div class="col-md-4">
                    <div class="form-group" style="margin: 0;">
                        <label>Command Type</label>
                        <select name="steps[INDEX][config][command_type]" class="form-control" required>
                            <option value="server">Server Command (GPRS)</option>
                            <option value="sms">SMS Command</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
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
    <div class="panel panel-default step-item" data-type="wait_for_response" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #718096; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading" style="background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <span class="step-index badge" style="background: #ebf8ff; color: #2b6cb0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #2d3748; letter-spacing: 0.5px;">WAIT FOR RESPONSE</strong>
            </div>
            <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="wait_for_response">
            <div class="row" style="display: flex; align-items: flex-end;">
                <div class="col-md-4">
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
    <div class="panel panel-default step-item" data-type="validate_response" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid #00b09b; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div class="panel-heading" style="background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <span class="step-index badge" style="background: #ebf8ff; color: #2b6cb0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #2d3748; letter-spacing: 0.5px;">VALIDATE PACKET</strong>
            </div>
            <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="validate_response">
            <div class="row" style="display: flex; align-items: flex-end;">
                <div class="col-md-6">
                    <div class="form-group" style="margin: 0;">
                        <label>Protocol</label>
                        <select name="steps[INDEX][config][protocol_id]" class="form-control protocol-select" required>
                            <option value="">Select Protocol...</option>
                            @foreach($protocols as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
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
        <div class="panel-heading" style="background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center;">
                <span class="step-index badge" style="background: #ebf8ff; color: #2b6cb0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;"></span> 
                <strong style="color: #2d3748; letter-spacing: 0.5px;">EVALUATE ALERTS</strong>
            </div>
            <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
        </div>
        <div class="panel-body" style="padding: 25px;">
            <input type="hidden" name="steps[INDEX][step_type]" value="alert_evaluation">
            <div class="row" style="display: flex; align-items: flex-end;">
                <div class="col-md-6">
                    <div class="form-group" style="margin: 0;">
                        <label>Protocol</label>
                        <select class="form-control protocol-select" required>
                            <option value="">Select Protocol...</option>
                            @foreach($protocols as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
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

<script>
$(document).ready(function() {
    let stepCount = 0;
    const packetFields = {}; // Store fields for each packet type

    $('.add-step').click(function() {
        const type = $(this).data('type');
        const template = $('#step-template-' + type).html();
        const html = template.replace(/INDEX/g, stepCount);
        
        const $step = $(html);
        $('#steps-container').append($step);
        $('#empty-steps-msg').hide();
        
        updateStepIndices();
        stepCount++;
    });

    $(document).on('click', '.remove-step', function() {
        $(this).closest('.step-item').remove();
        updateStepIndices();
        if ($('.step-item').length === 0) {
            $('#empty-steps-msg').show();
        }
    });

    $(document).on('change', '.protocol-select', function() {
        const protocolId = $(this).val();
        const $panelBody = $(this).closest('.panel-body');
        const $packetSelect = $panelBody.find('.packet-type-select');
        const $rulesSection = $panelBody.find('.rules-section');
        const $alertsSection = $panelBody.find('.alerts-section');
        
        if (!protocolId) {
            $packetSelect.html('<option value="">Select Type...</option>').prop('disabled', true);
            $rulesSection.hide();
            $alertsSection.hide();
            return;
        }

        $.get(`/admin/tracker/protocols/${protocolId}/packet-types`, function(data) {
            let options = '<option value="">Select Type...</option>';
            data.packet_types.forEach(function(pt) {
                options += `<option value="${pt.id}">${pt.name}</option>`;
                packetFields[pt.id] = pt.fields;
            });
            $packetSelect.html(options).prop('disabled', false);
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
        $(this).closest('.rule-row').remove();
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
        $container.html('<div style="display:flex; flex-wrap:wrap; gap:15px;">' +
            '<div class="checkbox" style="margin:0;"><label style="font-weight:600; color:#4a5568;"><input type="checkbox" name="steps[' + stepIdx + '][config][evaluate_all]" checked value="1"> Evaluate all active alerts for this packet</label></div>' +
            '</div>');
    }

    function updateStepIndices() {
        const count = $('.step-item').length;
        $('#step-count-badge').text(count + (count === 1 ? ' STEP' : ' STEPS'));
        
        $('.step-item').each(function(index) {
            $(this).find('.step-index').text((index + 1));
        });
    }
});
</script>
@endsection
