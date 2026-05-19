@extends('layouts.apps')


@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('test-plans-edit') }}">
@endpush
@section('title', 'Edit Test Plan')

@section('content')


<section id="main-content" class="test-plan-edit-page">
    <section class="wrapper">
        <div class="row">
            <div class="col-xs-12 col-md-10 col-md-offset-1">
                <section class="panel" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none;">
                    <header class="panel-heading" style="background: #0f172a !important; color: white !important; border-radius: 15px 15px 0 0; padding: 25px !important; border: none;">
                        <i class="fa fa-edit" style="margin-right: 10px; color: #96c93d;"></i> <strong style="font-size: 18px; letter-spacing: 0.5px;">Modify Test Plan: {{ $testPlan->name }}</strong>
                    </header>
                    <div class="panel-body" style="padding: 35px;">
                        @php
                            $userType = auth()->check() ? strtolower(trim((string) auth()->user()->user_type)) : '';
                            $routePrefix = $userType === 'support' ? 'support' : 'admin';
                        @endphp
                        <form action="{{ route($routePrefix . '.test-plans.update', $testPlan->id) }}" method="POST" class="form-horizontal tasi-form" id="test-plan-form">
                            @csrf
                            @method('PUT')
                            
                            <div class="row top-plan-fields">
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group" style="margin: 0 0 25px 0;">
                                        <label class="control-label" style="font-weight: 700; color: #2d3748; margin-bottom: 10px; display: block; text-align: left;">Plan Name</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-tag text-primary"></i></span>
                                            <input type="text" name="name" class="form-control" value="{{ $testPlan->name }}" style="border-radius: 0 10px 10px 0;" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group" style="margin: 0 0 25px 0;">
                                        <label class="control-label" style="font-weight: 700; color: #2d3748; margin-bottom: 10px; display: block; text-align: left;">Protocol</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-exchange text-primary"></i></span>
                                            <select name="protocol_id" id="plan_protocol_id" class="form-control" style="border-radius: 0 10px 10px 0;" required>
                                                <option value="">Select Protocol...</option>
                                                @foreach($protocols as $p)
                                                    <option value="{{ $p->id }}" {{ $testPlan->protocol_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group" style="margin: 0 0 25px 0;">
                                        <label class="control-label" style="font-weight: 700; color: #2d3748; margin-bottom: 10px; display: block; text-align: left;">Description</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-align-left text-primary"></i></span>
                                            <input type="text" name="description" class="form-control" value="{{ $testPlan->description }}" style="border-radius: 0 10px 10px 0;" placeholder="Briefly describe the purpose of this test plan...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="exec-seq-head" style="margin: 40px 0 20px 0; border-bottom: 2px solid #edf2f7; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-weight: 800; color: #4a5568; text-transform: uppercase; font-size: 13px; letter-spacing: 1.5px;">Execution Sequence</h4>
                                <span class="badge" id="step-count-badge" style="background: #ebf8ff; color: #2b6cb0; padding: 6px 12px; font-weight: 800; border: 1px solid #bee3f8;">{{ $testPlan->steps->count() }} STEPS</span>
                            </div>

                            <div id="steps-container" style="min-height: 120px; background: #f8fafc; border-radius: 15px; padding: 25px; border: 2px dashed #cbd5e0; margin-bottom: 35px;">
                                <div id="empty-steps-msg" style="text-align: center; color: #a0aec0; padding: 50px 0; {{ $testPlan->steps->count() > 0 ? 'display:none;' : '' }}">
                                    <i class="fa fa-cubes fa-3x" style="display: block; margin-bottom: 20px; opacity: 0.2;"></i>
                                    <p style="font-size: 15px;">No actions added to the sequence yet.</p>
                                </div>

                                @foreach($testPlan->steps as $index => $step)
                                    @php 
                                        $typeLabel = ''; $borderCol = '#96c93d';
                                        if($step->step_type == 'send_command') { $typeLabel = 'SEND COMMAND'; }
                                        elseif($step->step_type == 'wait_for_response') { $typeLabel = 'WAIT FOR RESPONSE'; }
                                        elseif($step->step_type == 'validate_response') { $typeLabel = 'VALIDATE PACKET'; }
                                        elseif($step->step_type == 'alert_evaluation') { $typeLabel = 'EVALUATE ALERTS'; }
                                    @endphp
                                    <div class="panel panel-default step-item" data-type="{{ $step->step_type }}" style="margin-bottom: 20px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 6px solid {{ $borderCol }}; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                        <div class="panel-heading step-handle step-item-heading" style="cursor: move; background: #fff !important; padding: 15px 25px !important; border-bottom: 1px solid #f0f4f8 !important;">
                                            <div style="display: flex; align-items: center;">
                                                <i class="fa fa-arrows" style="margin-right: 15px; color: #cbd5e0; font-size: 16px;"></i>
                                                <span class="step-index badge" style="background: #0f172a; color: white; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-right: 15px; font-weight: 800;">{{ $step->sequence }}</span> 
                                                <strong style="color: #0f172a; letter-spacing: 0.5px;">{{ $typeLabel }}</strong>
                                            </div>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="button" class="btn btn-default btn-xs toggle-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #edf2f7; border: none; color: #4a5568;"><i class="fa fa-chevron-up"></i></button>
                                                <button type="button" class="btn btn-danger btn-xs remove-step" style="border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #feb2b2; border: none; color: #9b2c2c;"><i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 25px;">
                                            <input type="hidden" name="steps[{{ $index }}][step_type]" value="{{ $step->step_type }}">
                                            
                                            @if($step->step_type == 'send_command')
                                                <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                                                    <div class="col-xs-12 col-md-4">
                                                        <div class="form-group" style="margin: 0;">
                                                            <label>Command Type</label>
                                                            <select name="steps[{{ $index }}][config][command_type]" class="form-control" required>
                                                                <option value="server" {{ ($step->config['command_type'] ?? '') == 'server' ? 'selected' : '' }}>Server Command (GPRS)</option>
                                                                <option value="sms" {{ ($step->config['command_type'] ?? '') == 'sms' ? 'selected' : '' }}>SMS Command</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-md-8">
                                                        <div class="form-group" style="margin: 0;">
                                                            <label>Command String</label>
                                                            <input type="text" name="steps[{{ $index }}][config][command_text]" class="form-control" value="{{ $step->config['command_text'] ?? '' }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($step->step_type == 'wait_for_response')
                                                <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                                                    <div class="col-xs-12 col-md-4">
                                                        <div class="form-group" style="margin: 0;">
                                                            <label>Timeout Duration</label>
                                                            <div class="input-group">
                                                                <input type="number" name="steps[{{ $index }}][config][timeout_seconds]" class="form-control" value="{{ $step->config['timeout_seconds'] ?? 30 }}" min="1" required style="border-radius: 8px 0 0 8px !important;">
                                                                <span class="input-group-addon" style="height: 40px; min-width: auto; padding: 0 15px; border-radius: 0 8px 8px 0 !important;">sec</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($step->step_type == 'validate_response')
                                                <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                                                    <div class="col-xs-12 col-md-12">
                                                        <div class="form-group" style="margin: 0;">
                                                            <label>Packet Type</label>
                                                            <select name="steps[{{ $index }}][config][packet_type_id]" class="form-control packet-type-select" required data-initial-value="{{ $step->config['packet_type_id'] ?? '' }}">
                                                                <option value="{{ $step->config['packet_type_id'] ?? '' }}">{{ $step->config['packet_type_name'] ?? 'Loading...' }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rules-section" style="margin-top: 30px;">
                                                    <label style="color: #2d3748; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Validation Rules</label>
                                                    <div class="rules-container" id="rules-container-{{ $index }}">
                                                        @foreach(($step->config['rules'] ?? []) as $ruleIdx => $rule)
                                                            <div class="rule-row">
                                                                <select name="steps[{{ $index }}][config][rules][{{ $ruleIdx }}][field]" class="form-control field-select" style="flex: 2; min-width: 0;" required data-initial-value="{{ $rule['field'] }}">
                                                                    <option value="{{ $rule['field'] }}">{{ $rule['field'] }}</option>
                                                                </select>
                                                                <select name="steps[{{ $index }}][config][rules][{{ $ruleIdx }}][operator]" class="form-control" style="flex: 1.2; min-width: 0;" required>
                                                                    <option value="==" {{ $rule['operator'] == '==' ? 'selected' : '' }}>Equals</option>
                                                                    <option value="!=" {{ $rule['operator'] == '!=' ? 'selected' : '' }}>Not Equal</option>
                                                                    <option value="<=" {{ $rule['operator'] == '<=' ? 'selected' : '' }}>Less/Equal</option>
                                                                    <option value=">=" {{ $rule['operator'] == '>=' ? 'selected' : '' }}>Great/Equal</option>
                                                                </select>
                                                                <input type="text" name="steps[{{ $index }}][config][rules][{{ $ruleIdx }}][value]" class="form-control" value="{{ $rule['value'] }}" style="flex: 2; min-width: 0;" placeholder="Value" required>
                                                                <button type="button" class="btn btn-danger remove-rule" title="Remove Rule">
                                                                    <i class="fa fa-minus"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-default add-rule-btn" data-step-index="{{ $index }}">
                                                        <i class="fa fa-plus-circle" style="margin-right: 6px; color: #00b09b;"></i> Add Validation Rule
                                                    </button>
                                                </div>
                                            @elseif($step->step_type == 'alert_evaluation')
                                                <div class="row step-form-row" style="display: flex; align-items: flex-end;">
                                                    <div class="col-xs-12 col-md-12">
                                                        <div class="form-group" style="margin: 0;">
                                                            <label>Packet Type</label>
                                                            <select name="steps[{{ $index }}][config][packet_type_id]" class="form-control packet-type-select" required data-initial-value="{{ $step->config['packet_type_id'] ?? '' }}">
                                                                <option value="{{ $step->config['packet_type_id'] ?? '' }}">{{ $step->config['packet_type_name'] ?? 'Loading...' }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alerts-section" style="margin-top: 25px;">
                                                    <label style="color: #2d3748; font-weight: 800; font-size: 11px; text-transform: uppercase;">Alerts to Validate</label>
                                                    <div class="alerts-container" id="alerts-container-{{ $index }}" data-initial-alerts="{{ json_encode($step->config['alert_ids'] ?? []) }}" style="background: #fdfdfd; border: 1px solid #edf2f7; border-radius: 10px; padding: 15px;">
                                                        <div style="display:flex; flex-wrap:wrap; gap:15px;">
                                                            <div class="checkbox" style="margin:0;"><label style="font-weight:600; color:#4a5568;"><input type="checkbox" name="steps[{{ $index }}][config][evaluate_all]" {{ ($step->config['evaluate_all'] ?? true) ? 'checked' : '' }} value="1"> Evaluate all active alerts for this packet</label></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-md-12">
                                    <div style="background: #fff; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                        <p style="font-weight: 800; color: #718096; margin-bottom: 20px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Add Action to Sequence</p>
                                        <div class="btn-group add-step-actions" style="display: flex; flex-wrap: wrap; justify-content: center;">
                                            <button type="button" class="btn btn-info add-step" data-type="send_command" style="margin: 5px; background: #0f172a !important; color: white !important; border: none;">
                                                <i class="fa fa-paper-plane" style="margin-right: 5px; color: #96c93d;"></i> Send Command
                                            </button>
                                            <button type="button" class="btn btn-info add-step" data-type="wait_for_response" style="margin: 5px; background: #0f172a !important; color: white !important; border: none;">
                                                <i class="fa fa-clock-o" style="margin-right: 5px; color: #96c93d;"></i> Wait
                                            </button>
                                            <button type="button" class="btn btn-info add-step" data-type="validate_response" style="margin: 5px; background: #0f172a !important; color: white !important; border: none;">
                                                <i class="fa fa-check-square-o" style="margin-right: 5px; color: #96c93d;"></i> Validate
                                            </button>
                                            <button type="button" class="btn btn-info add-step" data-type="alert_evaluation" style="margin: 5px; background: #0f172a !important; color: white !important; border: none;">
                                                <i class="fa fa-bell-o" style="margin-right: 5px; color: #96c93d;"></i> Alert
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions-footer" style="margin-top: 50px; text-align: right; border-top: 2px solid #edf2f7; padding-top: 30px;">
                                <a href="{{ route($routePrefix . '.test-plans.index') }}" class="btn btn-default" style="margin-top:10px; padding: 12px 30px; margin-right: 15px; border-radius: 10px; font-weight: 700;">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="padding: 12px 45px; border-radius: 10px; font-weight: 700; background: #96c93d !important; color: white !important; border: none; box-shadow: 0 10px 20px rgba(150, 201, 61, 0.2);">
                                    <i class="fa fa-save" style="margin-right: 8px;"></i> Update Test Plan
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
    let stepCount = {{ $testPlan->steps->count() }};
    const packetFields = {};
    const packetAlerts = {};

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

    let currentPacketTypes = [];

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

    $('#plan_protocol_id').change(function(e, isInitial) {
        const protocolId = $(this).val();
        
        if (!protocolId) {
            currentPacketTypes = [];
            $('.packet-type-select').html('<option value="">Select Type...</option>').prop('disabled', true);
            $('.rules-section, .alerts-section').hide();
            return;
        }

        if (!isInitial) {
            $('.packet-type-select').html('<option value="">Loading...</option>').prop('disabled', true);
        }

        $.get(`/${routePrefix}/tracker/protocols/${protocolId}/packet-types`, function(data) {
            currentPacketTypes = data.packet_types;
            data.packet_types.forEach(function(pt) {
                packetFields[pt.id] = pt.fields;
                packetAlerts[pt.id] = pt.alerts;
            });
            
            $('.packet-type-select').each(function() {
                const $select = $(this);
                const initialPacketId = isInitial ? $select.data('initial-value') : null;
                
                if (currentPacketTypes.length === 0) {
                    $select.html('<option value="">No packet types found</option>').prop('disabled', true);
                    const $panelBody = $select.closest('.panel-body');
                    $panelBody.find('.rules-section, .alerts-section').show();
                    $panelBody.find('.rules-container, .alerts-container').html('<div style="padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; color: #c53030;"><i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> No packet types available for this protocol.</div>');
                } else {
                    let options = '<option value="">Select Type...</option>';
                    data.packet_types.forEach(function(pt) {
                        const selected = (isInitial && pt.id == initialPacketId) ? 'selected' : '';
                        options += `<option value="${pt.id}" ${selected}>${pt.name}</option>`;
                    });
                    $select.html(options).prop('disabled', false);
                }

                if (isInitial && initialPacketId && currentPacketTypes.length > 0) {
                    const $panelBody = $select.closest('.panel-body');
                    $panelBody.find('.field-select').each(function() {
                        const initialField = $(this).data('initial-value');
                        populateFieldSelect($(this), initialPacketId, initialField);
                    });
                    
                    const stepType = $panelBody.closest('.step-item').data('type');
                    if (stepType === 'validate_response') {
                        const $rulesContainer = $panelBody.find('.rules-container');
                        if ($rulesContainer.children().length === 0) {
                            $panelBody.find('.add-rule-btn').click();
                        }
                    } else if (stepType === 'alert_evaluation') {
                        const stepIdx = $panelBody.closest('.step-item').find('.step-index').text() - 1;
                        fetchAndRenderAlerts(initialPacketId, $panelBody.find('.alerts-container'), stepIdx, true);
                    }
                } else if (!isInitial && currentPacketTypes.length > 0) {
                    const $panelBody = $select.closest('.panel-body');
                    $panelBody.find('.rules-container').empty();
                    $panelBody.find('.alerts-container').empty();
                    $panelBody.find('.rules-section, .alerts-section').hide();
                }
            });
        }).fail(function() {
            alert('Error loading packet types. Please try again.');
            if (!isInitial) {
                $('.packet-type-select').html('<option value="">Error</option>');
            }
        });
    });

    // Initial population for existing steps
    if ($('#plan_protocol_id').val()) {
        $('#plan_protocol_id').trigger('change', [true]);
    }

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
                fetchAndRenderAlerts(packetTypeId, $panelBody.find('.alerts-container'), stepIdx, false);
            }
        }
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

    function addRuleRow(stepIdx, packetTypeId) {
        const fields = packetFields[packetTypeId] || [];
        let fieldOptions = '<option value="">Select Field</option>';
        fields.forEach(f => {
            fieldOptions += `<option value="${f.name}">${f.name}</option>`;
        });

        const ruleIdx = $(`#rules-container-${stepIdx} .rule-row`).length;
        const html = `
            <div class="rule-row">
                <select name="steps[${stepIdx}][config][rules][${ruleIdx}][field]" class="form-control field-select" style="flex: 2; min-width: 0;" required>
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

    function populateFieldSelect($select, packetTypeId, initialValue) {
        const fields = packetFields[packetTypeId] || [];
        let options = '<option value="">Select Field</option>';
        fields.forEach(f => {
            const selected = (f.name == initialValue) ? 'selected' : '';
            options += `<option value="${f.name}" ${selected}>${f.name}</option>`;
        });
        $select.html(options);
    }

    function fetchAndRenderAlerts(packetTypeId, $container, stepIdx, isInitial) {
        $container.html('<p class="small text-muted"><i class="fa fa-spinner fa-spin"></i> Loading alerts...</p>');
        
        const alerts = packetAlerts[packetTypeId] || [];
        
        if (alerts.length === 0) {
            $container.html('<div style="padding: 15px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; color: #c53030;">' +
                    '<i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i> No active alerts configured for this packet type.' +
                    '</div>');
            return;
        }

        // For edit view, we might need to get initial selected values from hidden input or data attributes
        // But since we replace the HTML, let's just default to evaluate_all = true if not isInitial
        let isEvaluateAll = true;
        let selectedAlerts = [];
        
        if (isInitial) {
            // Attempt to get the initial value from the existing DOM before replacing
            const existingEvalAll = $container.find('input[name="steps[' + stepIdx + '][config][evaluate_all]"]');
            if (existingEvalAll.length > 0) {
                isEvaluateAll = existingEvalAll.is(':checked');
            }
            
            try {
                const initialAlertsStr = $container.attr('data-initial-alerts');
                if (initialAlertsStr) {
                    selectedAlerts = JSON.parse(initialAlertsStr).map(id => parseInt(id));
                }
            } catch(e) {}
        }
        
        let html = '<div style="margin-bottom: 15px;">' +
            '<div class="checkbox" style="margin:0;"><label style="font-weight:600; color:#4a5568;"><input type="checkbox" name="steps[' + stepIdx + '][config][evaluate_all]" class="evaluate-all-alerts" value="1" ' + (isEvaluateAll ? 'checked' : '') + '> Evaluate all active alerts for this packet</label></div>' +
            '</div>';
            
        html += '<div class="specific-alerts-container" style="display: ' + (isEvaluateAll ? 'none' : 'flex') + '; flex-wrap:wrap; gap:15px; padding-top: 10px; border-top: 1px dashed #e2e8f0;">';
        alerts.forEach(function(alert) {
            const isChecked = isEvaluateAll || (!isInitial) || selectedAlerts.includes(alert.id);
            html += '<div class="checkbox" style="margin:0; width: 100%;">' +
                '<label style="color:#4a5568;"><input type="checkbox" name="steps[' + stepIdx + '][config][alert_ids][]" class="specific-alert-checkbox" value="' + alert.id + '" ' + (isChecked ? 'checked' : '') + '> ' + alert.name + '</label>' +
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
