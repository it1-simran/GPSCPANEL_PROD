@extends('layouts.apps')
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

    /* Match protocol pages: tight top, pill breadcrumb above card */
    .alert-config-page .wrapper.alert-config-container {
        padding-top: 8px !important;
    }

    .alert-config-container {
        padding: 0 24px 28px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .alert-config-page .protocol-breadcrumb-wrap {
        padding: 4px 0 14px 0 !important;
        margin: 0 !important;
    }

    .alert-config-page .protocol-breadcrumb {
        display: inline-flex !important;
        align-items: center !important;
        flex-wrap: wrap;
        row-gap: 6px;
        background: #1e293b !important;
        border-radius: 50px !important;
        padding: 6px 18px 6px 8px !important;
        box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18) !important;
    }

    .alert-config-page .protocol-breadcrumb .bc-home {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #76CF1C;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
    }

    .alert-config-page .protocol-breadcrumb .bc-home i {
        color: #1e293b;
        font-size: 13px;
    }

    .alert-config-page .protocol-breadcrumb .bc-item {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
    }

    .alert-config-page .protocol-breadcrumb .bc-sep {
        color: rgba(255, 255, 255, 0.35);
        margin: 0 8px;
        font-size: 12px;
    }

    .alert-config-page .protocol-breadcrumb .bc-item.active {
        color: #76CF1C;
        font-weight: 700;
    }

    .alert-config-page .protocol-breadcrumb a.bc-item:hover {
        color: #e2e8f0;
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
        padding: 28px 32px 36px;
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
        padding: 28px 32px 36px;
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

    /* Prevent horizontal bleed / double-edge scrollbar in narrow layouts */
    .alert-config-page .custom-layout-row > [class*="col-"] {
        min-width: 0;
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

    /* Main Area — Rule Conditions header (step + title + badge column) */
    .config-main-area {
        padding-left: 20px;
        min-width: 0;
        overflow-x: hidden;
    }

    .rule-conditions-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--u-border);
        width: 100%;
        box-sizing: border-box;
    }

    .rule-conditions-head .step-num {
        flex-shrink: 0;
        align-self: flex-start;
        margin-top: 2px;
    }

    .rule-conditions-head__body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .rule-conditions-head__title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 1.3rem;
        margin: 0;
        padding: 0;
        color: var(--u-text);
        line-height: 1.25;
    }

    .badge-count {
        background: var(--u-primary-light);
        color: var(--u-primary);
        padding: 8px 20px;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        max-width: 100%;
    }

    /* Conditions Grid */
    .u-conditions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
        min-width: 0;
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

    /* ---- Tablet: stacked columns, drop sidebar sticky ---- */
    @media (max-width: 991px) {
        .alert-config-container {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .config-sidebar-card {
            position: static !important;
        }

        .config-main-area {
            padding-left: 0 !important;
            margin-top: 20px;
        }

        .ultra-premium-form {
            padding: 22px 18px 30px !important;
        }

        .alert-header-section {
            padding: 24px 22px 30px !important;
        }

        .header-content h1 {
            font-size: 1.85rem !important;
        }

        .u-conditions-grid {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }
    }

    /* ---- Phone: full-width breadcrumb bar, tighter chrome, single-column grid ---- */
    @media (max-width: 767px) {
        .alert-config-page > section.wrapper.alert-config-container {
            padding-left: 8px !important;
            padding-right: 8px !important;
            box-sizing: border-box !important;
        }

        .alert-config-container {
            padding-left: 0 !important;
            padding-right: 0 !important;
            padding-bottom: 18px !important;
            max-width: none !important;
        }

        .alert-config-page .protocol-breadcrumb-wrap {
            width: calc(100% + 16px) !important;
            max-width: none !important;
            margin-left: -8px !important;
            margin-right: -8px !important;
            padding-top: 2px !important;
            padding-bottom: 10px !important;
        }

        .alert-config-page .protocol-breadcrumb {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: flex-start !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            border-radius: 0 !important;
            padding: 12px 14px !important;
            row-gap: 10px !important;
            column-gap: 6px !important;
        }

        .alert-config-page .protocol-breadcrumb .bc-home {
            flex-shrink: 0 !important;
        }

        .alert-config-page .protocol-breadcrumb .bc-item,
        .alert-config-page .protocol-breadcrumb .bc-sep {
            font-size: 12px !important;
        }

        .alert-config-page .protocol-breadcrumb .bc-item.active {
            flex: 1 1 100% !important;
            padding-top: 8px !important;
            margin-top: 2px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
            line-height: 1.35 !important;
        }

        .alert-master-card {
            border-radius: 14px !important;
        }

        .header-illustration {
            display: none !important;
        }

        .glow-sphere {
            display: none !important;
        }

        .alert-header-section {
            padding: 16px 14px 20px !important;
        }

        .alert-badge {
            font-size: 0.65rem !important;
            padding: 5px 12px !important;
            margin-bottom: 14px !important;
        }

        .header-content h1 {
            font-size: 1.4rem !important;
            line-height: 1.2 !important;
            margin-bottom: 10px !important;
        }

        .header-content p {
            font-size: 0.9rem !important;
            line-height: 1.45 !important;
            max-width: none !important;
        }

        .ultra-premium-form {
            padding: 14px 12px 20px !important;
        }

        .config-sidebar-card {
            padding: 16px 14px !important;
            border-radius: 14px !important;
        }

        .sidebar-header {
            margin-bottom: 20px !important;
        }

        .config-main-area {
            margin-top: 16px !important;
            padding-left: 0 !important;
        }

        .rule-conditions-head {
            gap: 12px !important;
            margin-bottom: 20px !important;
            padding-bottom: 16px !important;
        }

        .rule-conditions-head__title {
            font-size: 1.1rem !important;
        }

        .rule-conditions-head__body {
            gap: 8px !important;
        }

        .badge-count {
            font-size: 0.8rem !important;
            padding: 6px 14px !important;
        }

        .u-conditions-grid {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }

        .u-condition-card {
            padding: 14px !important;
            border-radius: 14px !important;
        }

        .u-condition-card:hover {
            transform: none !important;
        }

        .condition-inputs {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 10px !important;
        }

        .u-condition-card .c-arrow {
            display: none !important;
        }

        .u-empty-state {
            padding: 36px 14px !important;
            border-radius: 16px !important;
        }

        .u-empty-state .empty-icon img {
            width: 64px !important;
            height: auto !important;
        }

        .u-form-footer {
            flex-direction: column-reverse !important;
            align-items: stretch !important;
            gap: 10px !important;
            padding-top: 24px !important;
        }

        .u-form-footer .btn-u-primary,
        .u-form-footer .btn-u-secondary {
            width: 100% !important;
            justify-content: center !important;
            height: 50px !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

        .config-sidebar-info {
            margin-top: 16px !important;
        }

        .alert-config-page .select2-container {
            max-width: 100% !important;
        }
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
