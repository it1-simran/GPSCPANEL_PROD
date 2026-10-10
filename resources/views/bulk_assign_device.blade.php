@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('add-multipledevice') }}">
<style>
    .bulk-assign-page {
        --bad-navy: #0f172a;
        --bad-navy-2: #1e293b;
        --bad-green: #76CF1C;
        --bad-green-2: #5fb015;
        --bad-green-soft: #eef9e3;
        --bad-red: #dc2626;
        --bad-red-soft: #fdecec;
        --bad-text: #1e293b;
        --bad-muted: #64748b;
        --bad-line: #e2e8f0;
        --bad-bg: #f8fafc;
        --bad-card: #f3f5f8;
        --bad-card-line: #dde3eb;
    }

    /* ---------- stat tiles ---------- */
    .bulk-assign-page .bad-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }
    .bulk-assign-page .bad-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        margin-top: 12px;
        font-size: 12px;
        color: var(--bad-muted);
    }
    .bulk-assign-page .bad-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .bulk-assign-page .bad-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
    .bulk-assign-page .bad-dot--assign { background: var(--bad-green-2); }
    .bulk-assign-page .bad-dot--move { background: #2563eb; }
    .bulk-assign-page .bad-dot--back { background: #d97706; }
    .bulk-assign-page .bad-stat {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        background: #fff;
        border: 1px solid var(--bad-line);
        border-radius: 12px;
        min-width: 0;
    }
    .bulk-assign-page .bad-stat-icon {
        flex: 0 0 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: var(--bad-green-soft);
        color: var(--bad-green-2);
    }
    .bulk-assign-page .bad-stat-icon--navy { background: #e8edf5; color: var(--bad-navy-2); }
    .bulk-assign-page .bad-stat-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: var(--bad-muted);
    }
    .bulk-assign-page .bad-stat-value {
        font-size: 22px;
        font-weight: 700;
        color: var(--bad-text);
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .bulk-assign-page .bad-stat-value--sm { font-size: 16px; padding-top: 4px; }

    /* ---------- step cards ---------- */
    .bulk-assign-page .bad-step {
        background: #fff;
        border: 1px solid var(--bad-line);
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 18px;
    }
    .bulk-assign-page .bad-step-head {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
    }
    .bulk-assign-page .bad-step-num {
        flex: 0 0 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--bad-navy-2);
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bulk-assign-page .bad-step.is-done .bad-step-num { background: var(--bad-green); color: var(--bad-navy); }
    .bulk-assign-page .bad-step-title { margin: 0; font-size: 16px; font-weight: 700; color: var(--bad-text); }
    .bulk-assign-page .bad-step-sub { margin: 3px 0 0; font-size: 13px; color: var(--bad-muted); }
    .bulk-assign-page .bad-step-body { padding-left: 46px; }

    .bulk-assign-page .bad-field-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }
    .bulk-assign-page .bad-field-label .require { color: var(--bad-red); }
    .bulk-assign-page #bulk_user_id,
    .bulk-assign-page .select2-container .select2-choice,
    .bulk-assign-page .select2-container .select2-selection--single {
        min-height: 30px;
        border: 1px solid #d5deea !important;
        border-radius: 8px !important;
        box-shadow: none !important;
        background: #fff !important;
    }
    .bulk-assign-page .select2-container .select2-choice { line-height: 42px; }
    .bulk-assign-page .select2-container .select2-selection--single .select2-selection__rendered { line-height: 42px; }
    .bulk-assign-page .select2-container .select2-selection--single .select2-selection__arrow { height: 42px; }
    .bulk-assign-page .bad-empty-note { display: block; margin-top: 8px; color: var(--bad-red); font-size: 13px; }

    /* ---------- segmented toggle ---------- */
    .bulk-assign-page .bad-seg {
        display: inline-flex;
        background: #f1f5f9;
        border-radius: 10px;
        padding: 4px;
        margin-bottom: 16px;
    }
    .bulk-assign-page .bad-seg button {
        border: 0;
        background: transparent;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--bad-muted);
        cursor: pointer;
        transition: background .15s, color .15s;
        margin-top: 1px;
    }
    .bulk-assign-page .bad-seg button i { margin-right: 6px; }
    .bulk-assign-page .bad-seg button.is-active {
        background: #fff;
        color: var(--bad-text);
        box-shadow: 0 1px 3px rgba(15, 23, 42, .12);
    }

    /* ---------- dropzone ---------- */
    .bulk-assign-page .bad-drop {
        position: relative;
        display: block;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background: var(--bad-bg);
        padding: 30px 20px;
        margin: 0;
        text-align: center;
        font-weight: normal;
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }
    .bulk-assign-page .bad-drop:hover,
    .bulk-assign-page .bad-drop.is-over { border-color: var(--bad-green); background: var(--bad-green-soft); }
    .bulk-assign-page .bad-drop input[type=file] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .bulk-assign-page .bad-drop-icon { font-size: 34px; color: var(--bad-green-2); margin-bottom: 8px; }
    .bulk-assign-page .bad-drop-title { font-size: 15px; font-weight: 600; color: var(--bad-text); }
    .bulk-assign-page .bad-drop-title u { color: var(--bad-green-2); text-decoration: none; border-bottom: 1px solid currentColor; }
    .bulk-assign-page .bad-drop-hint { font-size: 12px; color: var(--bad-muted); margin-top: 4px; }

    .bulk-assign-page .bad-file {
        display: none;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid var(--bad-line);
        border-radius: 10px;
        background: #fff;
    }
    .bulk-assign-page .bad-file.is-visible { display: flex; }
    .bulk-assign-page .bad-file-icon { font-size: 26px; color: #1d7a3a; }
    .bulk-assign-page .bad-file-meta { flex: 1; min-width: 0; }
    .bulk-assign-page .bad-file-name { font-weight: 600; color: var(--bad-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bulk-assign-page .bad-file-size { font-size: 12px; color: var(--bad-muted); }
    .bulk-assign-page .bad-icon-btn {
        border: 0;
        background: #f1f5f9;
        color: var(--bad-muted);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
    }
    .bulk-assign-page .bad-icon-btn:hover { background: var(--bad-red-soft); color: var(--bad-red); }

    .bulk-assign-page .bad-helper-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        font-size: 12px;
        color: var(--bad-muted);
    }
    .bulk-assign-page .bad-link { color: var(--bad-green-2); font-weight: 600; }
    .bulk-assign-page .bad-link:hover { color: var(--bad-navy-2); text-decoration: none; }

    .bulk-assign-page .bad-textarea {
        width: 100%;
        min-height: 150px;
        padding: 12px 14px;
        border: 1px solid #d5deea;
        border-radius: 10px;
        font-family: Consolas, Monaco, monospace;
        font-size: 13px;
        color: var(--bad-text);
        resize: vertical;
        outline: none;
    }
    .bulk-assign-page .bad-textarea:focus { border-color: var(--bad-green); box-shadow: 0 0 0 3px rgba(118, 207, 28, .15); }
    .bulk-assign-page .bad-count-pill {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        background: #e8edf5;
        color: var(--bad-navy-2);
        font-weight: 600;
    }

    /* ---------- buttons ---------- */
    .bulk-assign-page .bad-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }
    .bulk-assign-page .bad-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 24px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        border: 1px solid transparent;
        cursor: pointer;
        transition: filter .15s, box-shadow .15s, transform .15s;
    }
    .bulk-assign-page .bad-btn--primary { background: linear-gradient(135deg, var(--bad-green) 0%, var(--bad-green-2) 100%); color: var(--bad-navy); }
    .bulk-assign-page .bad-btn--primary:hover { box-shadow: 0 6px 20px rgba(118, 207, 28, .4); transform: translateY(-1px); }
    .bulk-assign-page .bad-btn--dark { background: var(--bad-navy-2); color: #fff; }
    .bulk-assign-page .bad-btn--dark:hover { box-shadow: 0 6px 20px rgba(15, 23, 42, .3); transform: translateY(-1px); }
    .bulk-assign-page .bad-btn--ghost { background: #fff; color: var(--bad-muted); border-color: #d5deea; }
    .bulk-assign-page .bad-btn--ghost:hover { color: var(--bad-text); border-color: #94a3b8; }
    .bulk-assign-page .bad-btn[disabled] { opacity: .5; cursor: not-allowed; box-shadow: none; transform: none; }

    /* ---------- preview ---------- */
    .bulk-assign-page #bulkPreviewWrap { display: none; }
    .bulk-assign-page .bad-mini-stats {
        display: grid;
        /* 5-7 cards (two appear only when they have a count): fill the row whatever the number */
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .bulk-assign-page .bad-mini {
        border-radius: 10px;
        padding: 12px 14px;
        background: var(--bad-bg);
        border: 1px solid var(--bad-line);
    }
    .bulk-assign-page .bad-mini span { display: block; font-size: 12px; font-weight: 600; color: var(--bad-muted); text-transform: uppercase; letter-spacing: .4px; }
    .bulk-assign-page .bad-mini strong { font-size: 22px; color: var(--bad-text); }
    .bulk-assign-page .bad-mini--info { background: #eef4ff !important; border-color: #c9d9fb; }
    .bulk-assign-page .bad-mini--info span,
    .bulk-assign-page .bad-mini--info strong { color: #1d4ed8; }
    .bulk-assign-page .bad-mini--stock { background: #fff8ee !important; border-color: #fde2bd; }
    .bulk-assign-page .bad-mini--stock span,
    .bulk-assign-page .bad-mini--stock strong { color: #b45309; }
    .bulk-assign-page .bad-mini--category { background: #f5f0ff !important; border-color: #ddd0fa; }
    .bulk-assign-page .bad-mini--category span,
    .bulk-assign-page .bad-mini--category strong { color: #6d3fd0; }
    .bulk-assign-page .bad-mini--ready { background: var(--bad-green-soft); border-color: #cdebb0; }
    .bulk-assign-page .bad-mini--ready strong { color: #2f6b12; }
    .bulk-assign-page .bad-mini--skip { background: var(--bad-red-soft); border-color: #f5c2c2; }
    .bulk-assign-page .bad-mini--skip strong { color: #a12626; }

    .bulk-assign-page .bad-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .bulk-assign-page .bad-filter { display: inline-flex; gap: 6px; flex-wrap: wrap; }
    .bulk-assign-page .bad-filter button {
        border: 1px solid #d5deea;
        background: #fff;
        color: var(--bad-muted);
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .bulk-assign-page .bad-filter button.is-active { background: var(--bad-navy-2); border-color: var(--bad-navy-2); color: #fff; }
    .bulk-assign-page .bad-search { position: relative; width: 260px; max-width: 100%; }
    .bulk-assign-page .bad-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .bulk-assign-page .bad-search input {
        width: 100%;
        height: 38px;
        padding: 0 12px 0 34px;
        border: 1px solid #d5deea;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
    }
    .bulk-assign-page .bad-search input:focus { border-color: var(--bad-green); }

    .bulk-assign-page .bad-table-wrap {
        border: 1px solid var(--bad-line);
        border-radius: 10px;
        overflow: auto;
        max-height: 480px;
    }
    .bulk-assign-page .bad-table { width: 100%; margin: 0; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .bulk-assign-page .bad-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: var(--bad-navy-2);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        padding: 12px 14px;
        white-space: nowrap;
        border: 0;
    }
    .bulk-assign-page .bad-table tbody td { padding: 11px 14px; border-top: 1px solid var(--bad-line); color: #334155; vertical-align: middle; }
    .bulk-assign-page .bad-table tbody tr:hover td { background: #f8fafc; }
    .bulk-assign-page .bad-table tbody tr.is-skip td { color: #94a3b8; }
    .bulk-assign-page .bad-table .bad-imei { font-family: Consolas, Monaco, monospace; font-weight: 600; color: var(--bad-text); }
    .bulk-assign-page .bad-table tbody tr.is-skip .bad-imei { color: #94a3b8; }
    .bulk-assign-page .bad-table input[type=checkbox] { width: 16px; height: 16px; cursor: pointer; accent-color: var(--bad-green-2); }
    .bulk-assign-page .bad-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    .bulk-assign-page .bad-badge--ready { background: var(--bad-green-soft); color: #2f6b12; }
    .bulk-assign-page .bad-badge--skip { background: var(--bad-red-soft); color: #a12626; }
    .bulk-assign-page .bad-badge--move { background: #e6efff; color: #1d4ed8; }
    .bulk-assign-page .bad-badge--back { background: #fef3e2; color: #b45309; }
    .bulk-assign-page .bad-no-rows td { text-align: center; padding: 28px !important; color: var(--bad-muted) !important; }

    .bulk-assign-page .bad-footer {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--bad-line);
    }
    .bulk-assign-page .bad-footer-note { font-size: 13px; color: var(--bad-muted); }
    .bulk-assign-page .bad-footer-note strong { color: var(--bad-text); }

    /* ---------- responsive ---------- */
    @media (max-width: 1399px) {
        .bulk-assign-page .bad-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 991px) {
        .bulk-assign-page .bad-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .bulk-assign-page .bad-stats { grid-template-columns: 1fr; }
        .bulk-assign-page .bad-step { padding: 18px 16px; }
        .bulk-assign-page .bad-step-body { padding-left: 0; }
        .bulk-assign-page .bad-seg { display: flex; }
        .bulk-assign-page .bad-seg button { flex: 1; padding: 8px 10px; }
        .bulk-assign-page .bad-search { width: 100%; }
        .bulk-assign-page .bad-actions .bad-btn,
        .bulk-assign-page .bad-footer .bad-btn { width: 100%; }
    }
    /* ================= v2 layout (hero, stat cards, navy step headers) ================= */
    .bulk-assign-page {
        --bad-edge: #e3e8ef;
        --bad-accent-green: #4caf1a;
        --bad-accent-blue: #2f6fe4;
        --bad-accent-purple: #7c4ddb;
        --bad-accent-orange: #e08a1e;
        --bad-accent-slate: #94a3b8;
    }

    /* hero */
    .bulk-assign-page .bad-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 150px;
        padding: 22px 34px 26px;
        margin: 6px 0 20px;
        background: linear-gradient(180deg, #fcfdff 0%, #f5f8fc 100%);
        border: 1px solid #e3e9f2;
        border-radius: 16px;
        box-shadow: 0 6px 22px rgba(15, 23, 42, .06);
        overflow: hidden;
    }
    /* soft green wave bottom-left, light blue blob top-right */
    .bulk-assign-page .bad-hero::before {
        content: '';
        position: absolute;
        left: -80px;
        bottom: -90px;
        width: 420px;
        height: 190px;
        border-radius: 50%;
        background: radial-gradient(ellipse at center, rgba(118, 207, 28, .20) 0%, rgba(118, 207, 28, .08) 45%, rgba(118, 207, 28, 0) 72%);
        pointer-events: none;
    }
    .bulk-assign-page .bad-hero::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -80px;
        width: 320px;
        height: 170px;
        border-radius: 50%;
        background: radial-gradient(ellipse at center, rgba(47, 111, 228, .18) 0%, rgba(47, 111, 228, .07) 45%, rgba(47, 111, 228, 0) 72%);
        pointer-events: none;
    }
    .bulk-assign-page .bad-hero > * { position: relative; z-index: 1; }
    .bulk-assign-page .bad-crumbs {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 14px;
        color: #475569;
        margin-bottom: 18px;
    }
    .bulk-assign-page .bad-crumbs a { color: #475569; text-decoration: none; }
    .bulk-assign-page .bad-crumbs a:hover { color: var(--bad-text); }
    .bulk-assign-page .bad-crumbs .bad-crumb-home { color: var(--bad-accent-green); font-size: 19px; line-height: 1; }
    .bulk-assign-page .bad-crumbs .fa-angle-right { color: #a3afbf; }
    .bulk-assign-page .bad-crumbs .is-current { color: var(--bad-accent-green); font-weight: 700; }
    .bulk-assign-page .bad-hero-title { display: flex; align-items: center; gap: 22px; }
    .bulk-assign-page .bad-hero-icon {
        flex: 0 0 72px;
        height: 72px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        color: #3f9a12;
        background: #e6f6dc;
        box-shadow: inset 0 0 0 1px rgba(76, 175, 26, .10);
    }
    .bulk-assign-page .bad-hero h1 { margin: 0 0 6px; font-size: 28px; font-weight: 800; line-height: 1.2; color: var(--bad-navy); letter-spacing: 0; text-transform: none; }
    .bulk-assign-page .bad-hero p { margin: 0; font-size: 14.5px; color: #5b6b82; }
    .bulk-assign-page .bad-hero-art { flex: 0 0 240px; width: 240px; margin: -8px 0 -10px; }
    .bulk-assign-page .bad-hero-art svg { display: block; width: 100%; height: auto; }

    /* stat cards */
    .bulk-assign-page .bad-stat {
        position: relative;
        display: grid !important;
        grid-template-columns: 48px minmax(0, 1fr);
        grid-template-rows: auto auto;
        column-gap: 14px;
        row-gap: 10px;
        align-items: center;
        padding: 16px 18px 14px !important;
        background: #fff !important;
        border: 1px solid var(--bad-edge) !important;
        border-bottom: 3px solid var(--bad-stat-accent, var(--bad-accent-slate)) !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .05) !important;
    }
    .bulk-assign-page .bad-stat-icon {
        width: 48px;
        height: 48px;
        flex: none;
        border-radius: 12px;
        font-size: 22px;
        background: var(--bad-stat-tint, #e8edf3) !important;
        color: var(--bad-stat-accent, #334155) !important;
    }
    .bulk-assign-page .bad-stat-label { font-size: 11.5px; font-weight: 700; color: #334155; letter-spacing: .5px; line-height: 1.3; }
    .bulk-assign-page .bad-stat-value { font-size: 24px; font-weight: 800; color: var(--bad-navy); }
    .bulk-assign-page .bad-stat-value--sm { font-size: 17px; padding-top: 3px; }
    .bulk-assign-page .bad-stat-sub {
        grid-column: 2;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--bad-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .bulk-assign-page .bad-stat-sub i { color: var(--bad-stat-accent, var(--bad-muted)); }
    .bulk-assign-page .bad-stat--green { --bad-stat-accent: var(--bad-accent-green); --bad-stat-tint: #e3f6d6; }
    .bulk-assign-page .bad-stat--blue { --bad-stat-accent: var(--bad-accent-blue); --bad-stat-tint: #e1ecfd; }
    .bulk-assign-page .bad-stat--purple { --bad-stat-accent: var(--bad-accent-purple); --bad-stat-tint: #ece5fb; }
    .bulk-assign-page .bad-stat--orange { --bad-stat-accent: var(--bad-accent-orange); --bad-stat-tint: #fcefdc; }
    .bulk-assign-page .bad-stat--slate { --bad-stat-accent: #334155; --bad-stat-tint: #e8edf3; }
    .bulk-assign-page .bad-stat--slate { border-bottom-color: var(--bad-accent-slate) !important; }

    /* step cards with navy header bar */
    .bulk-assign-page .bad-step {
        padding: 0 !important;
        background: #fff !important;
        border: 1px solid var(--bad-edge) !important;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(15, 23, 42, .05) !important;
    }
    .bulk-assign-page .bad-step-head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 0;
        padding: 13px 22px;
        background: linear-gradient(90deg, #0f1b33 0%, #14244a 62%, #1d3b34 100%);
    }
    .bulk-assign-page .bad-step-num {
        flex: 0 0 34px;
        height: 34px;
        background: var(--bad-green) !important;
        color: var(--bad-navy) !important;
        font-size: 16px;
        font-weight: 800;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, .18);
    }
    .bulk-assign-page .bad-step-title { color: #fff; font-size: 17px; font-weight: 700; }
    .bulk-assign-page .bad-step-hint {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        color: rgba(255, 255, 255, .82);
        text-align: right;
    }
    .bulk-assign-page .bad-step-hint i { color: var(--bad-green); font-size: 14px; }
    .bulk-assign-page .bad-step-body { padding: 18px 24px 20px !important; }
    .bulk-assign-page .bad-step-intro { margin: 0 0 14px; font-size: 14px; color: #334155; }
    .bulk-assign-page .bad-step-intro strong { color: var(--bad-navy); }

    /* account select with leading icon */
    .bulk-assign-page .bad-select { position: relative; }
    .bulk-assign-page .bad-select-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        color: var(--bad-muted);
        pointer-events: none;
    }
    .bulk-assign-page .bad-select #bulk_user_id,
    .bulk-assign-page .bad-select .select2-choice,
    .bulk-assign-page .bad-select .select2-selection__rendered { padding-left: 38px !important; }

    /* legend strip */
    .bulk-assign-page .bad-legend {
        gap: 0;
        margin-top: 14px;
        padding: 9px 6px;
        background: #f1f5f9;
        border-radius: 8px;
    }
    .bulk-assign-page .bad-legend span { padding: 2px 14px; border-left: 1px solid #d9e0e8; }
    .bulk-assign-page .bad-legend span:first-child { border-left: 0; }
    .bulk-assign-page .bad-legend b:not(.bad-dot) { color: var(--bad-text); font-weight: 700; }

    .bulk-assign-page .bad-drop,
    .bulk-assign-page .bad-mini,
    .bulk-assign-page .bad-table-wrap,
    .bulk-assign-page .bad-textarea { background: #fff; }
    .bulk-assign-page .bad-mini--ready { background: var(--bad-green-soft); }
    .bulk-assign-page .bad-mini--skip { background: var(--bad-red-soft); }
    .bulk-assign-page .bad-drop { background: var(--bad-bg); }
    .bulk-assign-page .bad-drop:hover,
    .bulk-assign-page .bad-drop.is-over { background: var(--bad-green-soft); }

    /* Account-type tabs above the picker */
    .bulk-assign-page .bad-kind-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
    .bulk-assign-page .bad-kind-tabs button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 150px;
        justify-content: center;
        padding: 9px 20px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #eef2f6;
        color: #1e293b;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s, color .15s, box-shadow .15s;
    }
    .bulk-assign-page .bad-kind-tabs button:hover { background: #e4eaf1; }
    .bulk-assign-page .bad-kind-tabs button i { font-size: 15px; color: #334155; }
    .bulk-assign-page .bad-kind-tabs button.is-active {
        background: linear-gradient(135deg, #4caf1a 0%, #3f9a12 100%);
        border-color: #3f9a12;
        color: #fff;
        box-shadow: 0 4px 12px rgba(76, 175, 26, .28);
    }
    .bulk-assign-page .bad-kind-tabs button.is-active i { color: #fff; }
    .bulk-assign-page .bad-kind-count {
        min-width: 22px;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 11.5px;
        font-weight: 700;
        background: #fff;
        color: #475569;
    }
    .bulk-assign-page .bad-kind-tabs button.is-active .bad-kind-count { background: rgba(255, 255, 255, .25); color: #fff; }

    .bulk-assign-page .bad-kind-label { font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 10px; }
    .bulk-assign-page .bad-kind-label strong { font-weight: 800; color: var(--bad-navy); }

    /* Device category tiles (Step 2) */
    .bulk-assign-page .bad-cat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 12px;
    }
    .bulk-assign-page .bad-cat {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 14px 16px;
        text-align: left;
        background: #fff;
        border: 1.5px solid #dde3eb;
        border-radius: 12px;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .bulk-assign-page .bad-cat:hover { border-color: #b7dd94; box-shadow: 0 4px 12px rgba(15, 23, 42, .06); }
    .bulk-assign-page .bad-cat-icon {
        flex: 0 0 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e1ecfd;
        color: #2f6fe4;
        font-size: 17px;
    }
    .bulk-assign-page .bad-cat-text { display: flex; flex-direction: column; min-width: 0; }
    .bulk-assign-page .bad-cat-name { font-size: 14.5px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .bulk-assign-page .bad-cat-meta { font-size: 12px; color: #64748b; margin-top: 2px; }
    .bulk-assign-page .bad-cat-meta b { color: #1e293b; }
    .bulk-assign-page .bad-cat-lock { display: none; font-size: 12px; color: #a12626; margin-top: 3px; }
    .bulk-assign-page .bad-cat-check {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1.5px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: transparent;
        background: #fff;
    }
    .bulk-assign-page .bad-cat.is-selected { border-color: #4caf1a; background: #f4fbee; box-shadow: 0 0 0 3px rgba(76, 175, 26, .15); }
    .bulk-assign-page .bad-cat.is-selected .bad-cat-icon { background: #e3f6d6; color: #3f9a12; }
    .bulk-assign-page .bad-cat.is-selected .bad-cat-check { background: #4caf1a; border-color: #4caf1a; color: #fff; }
    .bulk-assign-page .bad-cat.is-locked { cursor: not-allowed; background: #f8fafc; border-style: dashed; box-shadow: none; }
    .bulk-assign-page .bad-cat.is-locked .bad-cat-icon { background: #eef2f6; color: #94a3b8; }
    .bulk-assign-page .bad-cat.is-locked .bad-cat-name,
    .bulk-assign-page .bad-cat.is-locked .bad-cat-meta { color: #94a3b8; }
    .bulk-assign-page .bad-cat.is-locked .bad-cat-meta { display: none; }
    .bulk-assign-page .bad-cat.is-locked .bad-cat-lock { display: block; }

    .bulk-assign-page .bad-cat.is-selected:hover { border-color: #3f9a12; }

    /* Placeholder shown while Step 3 is hidden */
    .bulk-assign-page .bad-waiting {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        padding: 16px 20px;
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        color: #64748b;
        font-size: 13.5px;
    }
    .bulk-assign-page .bad-waiting i { font-size: 18px; color: #94a3b8; }
    .bulk-assign-page .bad-waiting strong { color: #1e293b; }

    /* My Stock (take back) panel */
    .bulk-assign-page .bad-stock-note {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 16px;
        border: 1px solid #fde2bd;
        border-radius: 10px;
        background: #fff8ee;
    }
    .bulk-assign-page .bad-stock-note-icon {
        flex: 0 0 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fcefdc;
        color: #d97706;
        font-size: 17px;
    }
    .bulk-assign-page .bad-stock-note strong { display: block; color: #1e293b; font-size: 14px; margin-bottom: 2px; }
    .bulk-assign-page .bad-stock-note span { font-size: 13px; color: #64748b; }
    @media (max-width: 767px) {
        .bulk-assign-page .bad-kind-tabs button { flex: 1; min-width: 0; padding: 9px 10px; }
    }

    /* Account picker dropdown (Select2 appends it to <body>, so it is scoped by its own class) */
    .bad-s2-drop.select2-drop {
        border: 1px solid #dde3eb !important;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
        overflow: hidden;
    }
    .bad-s2-drop.select2-drop-above { border-radius: 10px 10px 0 0; }
    .bad-s2-drop .select2-search { padding: 10px 10px 6px; }
    .bad-s2-drop .select2-search input {
        height: 36px;
        min-height: 36px;
        padding: 6px 12px 6px 34px !important;
        border: 1px solid #d5deea !important;
        border-radius: 8px !important;
        font-size: 13px;
        color: #1e293b;
        box-shadow: none !important;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.4' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-3.5-3.5'/%3E%3C/svg%3E") no-repeat 11px center / 15px 15px !important;
    }
    .bad-s2-drop .select2-search input:focus { border-color: #76CF1C !important; }
    .bad-s2-drop .select2-results { max-height: 300px; margin: 0; padding: 0 0 6px; }
    .bad-s2-drop .select2-results li { margin: 0; }
    .bad-s2-drop .select2-results .select2-result-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px 8px 34px;
        font-size: 13px;
        color: #334155;
        line-height: 1.35;
    }
    .bad-s2-drop .bad-opt-icon { width: 16px; text-align: center; font-size: 14px; color: #475569; }
    /* group headings */
    .bad-s2-drop .bad-opt-group { margin-top: 2px; }
    .bad-s2-drop .bad-opt-group > .select2-result-label {
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        background: #f1f5f9;
        border-top: 1px solid #e8edf3;
        cursor: default;
    }
    .bad-s2-drop .bad-opt-group > .select2-result-label .bad-opt-icon { color: #1e293b; }
    .bad-s2-drop .bad-opt-group.bad-kind--manufacturer > .select2-result-label { color: #3f9a12; background: #eef9e3; }
    .bad-s2-drop .bad-opt-group.bad-kind--manufacturer > .select2-result-label .bad-opt-icon { color: #4caf1a; }
    .bad-s2-drop .bad-opt-group.bad-kind--stock > .select2-result-label .bad-opt-icon { color: #4caf1a; }
    .bad-s2-drop .select2-result-sub { margin: 0; padding: 2px 0 4px; }
    /* hover / keyboard highlight */
    .bad-s2-drop .select2-results .select2-highlighted { background: #eef9e3 !important; color: #1e293b !important; }
    .bad-s2-drop .select2-results .select2-highlighted .select2-result-label { color: #1e293b; }
    .bad-s2-drop .select2-results .select2-highlighted .bad-opt-icon { color: #3f9a12; }
    .bad-s2-drop .select2-results .select2-no-results {
        padding: 12px 14px;
        font-size: 13px;
        color: #64748b;
        background: #fff;
    }
    .bad-s2-drop .select2-match { text-decoration: none; font-weight: 700; color: #3f9a12; }

    @media (max-width: 991px) {
        .bulk-assign-page .bad-hero-art { display: none; }
    }
    @media (max-width: 767px) {
        .bulk-assign-page .bad-hero { padding: 14px 16px; }
        .bulk-assign-page .bad-hero h1 { font-size: 21px; }
        .bulk-assign-page .bad-hero-title { gap: 14px; }
        .bulk-assign-page .bad-hero-icon { flex-basis: 54px; height: 54px; border-radius: 14px; font-size: 25px; }
        .bulk-assign-page .bad-step-head { padding: 12px 16px; }
        .bulk-assign-page .bad-step-hint { display: none; }
        .bulk-assign-page .bad-step-body { padding: 16px !important; }
        .bulk-assign-page .bad-legend { flex-direction: column; gap: 6px; }
        .bulk-assign-page .bad-legend span { border-left: 0; }
    }

    /* ===== Review summary cards: tinted gradient, icon tile, label, value, sub-line, soft wave ===== */
    .bulk-assign-page .bad-mini-stats .bad-mini {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px 16px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff 0%, var(--mini-tint, #f8fafc) 100%) !important;
        border: 1px solid var(--mini-border, #e2e8f0) !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
    }
    .bulk-assign-page .bad-mini-stats .bad-mini::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -42px;
        width: 140px;
        height: 90px;
        border-radius: 50%;
        background: var(--mini-accent, #94a3b8);
        opacity: .07;
        pointer-events: none;
    }
    .bulk-assign-page .bad-mini-icon {
        flex: 0 0 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        background: var(--mini-icon-bg, #e8edf3);
        color: var(--mini-icon-color, var(--mini-accent, #334155));
        position: relative;
        z-index: 1;
    }
    /* No icons: the text block takes the full card width and wraps instead of being cut off. */
    .bulk-assign-page .bad-mini-body { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; position: relative; z-index: 1; }
    .bulk-assign-page .bad-mini-stats .bad-mini .bad-mini-label {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.3;
        color: #1e293b !important;
        text-transform: none;
        letter-spacing: 0;
        white-space: normal;
        overflow-wrap: anywhere;
    }
    .bulk-assign-page .bad-mini-stats .bad-mini strong { font-size: 26px; font-weight: 800; line-height: 1.2; margin-top: 4px; color: #0f172a !important; }
    .bulk-assign-page .bad-mini-sub {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.35;
        color: var(--mini-accent, #64748b);
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .bulk-assign-page .bad-mini--total    { --mini-accent: #2f6fe4; --mini-tint: #eef4ff; --mini-border: #d6e4fd; --mini-icon-bg: #dbe7fd; }
    .bulk-assign-page .bad-mini--info     { --mini-accent: #3f9a12; --mini-tint: #eef9e6; --mini-border: #cfeabb; --mini-icon-bg: #4caf1a; --mini-icon-color: #fff; }
    .bulk-assign-page .bad-mini--ready    { --mini-accent: #7c4ddb; --mini-tint: #f4f0ff; --mini-border: #e2d8fb; --mini-icon-bg: #e6dcfb; }
    .bulk-assign-page .bad-mini--stock    { --mini-accent: #d97706; --mini-tint: #fff7ec; --mini-border: #fde2bd; --mini-icon-bg: #fcebd2; }
    .bulk-assign-page .bad-mini--category { --mini-accent: #b02fc4; --mini-tint: #fdf2fe; --mini-border: #f1cff6; --mini-icon-bg: #f7dafb; }
    .bulk-assign-page .bad-mini--skip     { --mini-accent: #dc2626; --mini-tint: #fff1f1; --mini-border: #fbd0d0; --mini-icon-bg: #ef4444; --mini-icon-color: #fff; }
    .bulk-assign-page .bad-mini--selected { --mini-accent: #5b5bd6; --mini-tint: #f3f3ff; --mini-border: #dcdcf8; --mini-icon-bg: #e3e3fb; }
    .bulk-assign-page .bad-mini-stats { grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }

    /* ===================== Mobile / tablet polish (keep last so it wins) ===================== */
    @media (max-width: 991px) {
        /* 2-column stats: the 5th card (Assigning To) takes the full row */
        .bulk-assign-page .bad-stat:last-child { grid-column: 1 / -1; }
        /* long hints push the step title onto two lines on narrow screens */
        .bulk-assign-page .bad-step-hint { display: none; }
        /* preview table becomes one card per device */
        .bulk-assign-page .bad-table-wrap { max-height: none; overflow: visible; border: 0; border-radius: 0; background: transparent !important; }
        /* header row: keep only the "select all" checkbox, as a full-width bar */
        .bulk-assign-page .bad-table thead { display: block; margin-bottom: 10px; }
        .bulk-assign-page .bad-table thead tr { display: block; }
        .bulk-assign-page .bad-table thead th { display: none; }
        .bulk-assign-page .bad-table thead th:first-child {
            position: static; display: flex; align-items: center; gap: 10px; width: auto !important;
            padding: 10px 12px; border-radius: 10px; font-size: 12.5px; letter-spacing: .3px;
        }
        .bulk-assign-page .bad-table thead th:first-child::after { content: 'Select all ready devices'; }
        .bulk-assign-page .bad-table,
        .bulk-assign-page .bad-table tbody { display: block; width: 100%; }
        .bulk-assign-page .bad-table tbody tr[data-status] {
            display: grid;
            grid-template-columns: 26px minmax(0, 1fr);
            column-gap: 10px;
            row-gap: 5px;
            margin-bottom: 10px;
            padding: 12px 12px 12px 10px;
            background: #fff;
            border: 1px solid var(--bad-line);
            border-radius: 10px;
        }
        .bulk-assign-page .bad-table tbody tr.is-skip { background: #fafbfc; }
        .bulk-assign-page .bad-table tbody tr:hover td { background: transparent; }
        .bulk-assign-page .bad-table tbody td { display: block; padding: 0; border: 0; font-size: 13px; }
        .bulk-assign-page .bad-table tbody td:nth-child(1) { grid-column: 1; grid-row: 1 / span 6; padding-top: 2px; }
        .bulk-assign-page .bad-table tbody td:nth-child(2) { display: none; }
        .bulk-assign-page .bad-table tbody td:nth-child(n+3) { grid-column: 2; }
        .bulk-assign-page .bad-table tbody td:nth-child(3) { font-size: 14.5px; }
        .bulk-assign-page .bad-table tbody td:nth-child(7) { order: -1; }
        .bulk-assign-page .bad-table tbody td:nth-child(4)::before { content: 'Name: '; color: #94a3b8; }
        .bulk-assign-page .bad-table tbody td:nth-child(5)::before { content: 'Category: '; color: #94a3b8; }
        .bulk-assign-page .bad-table tbody td:nth-child(6)::before { content: 'Holder: '; color: #94a3b8; }
        .bulk-assign-page .bad-table .bad-badge { white-space: normal; }
        .bulk-assign-page .bad-no-rows td { display: block; border: 1px dashed var(--bad-line); border-radius: 10px; }
    }
    @media (max-width: 767px) {
        /* top stats: two per row, compact; "Assigning To" full width */
        .bulk-assign-page .bad-stats { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 10px; margin-bottom: 16px; }
        .bulk-assign-page .bad-stat { grid-template-columns: 34px minmax(0, 1fr); column-gap: 10px; row-gap: 6px; padding: 12px !important; }
        .bulk-assign-page .bad-stat:last-child { grid-column: 1 / -1; }
        .bulk-assign-page .bad-stat-icon { width: 34px; height: 34px; font-size: 16px; border-radius: 9px; }
        .bulk-assign-page .bad-stat-label { font-size: 10.5px; letter-spacing: .3px; white-space: normal; }
        .bulk-assign-page .bad-stat-value { font-size: 20px; }
        .bulk-assign-page .bad-stat-value--sm { font-size: 15px; white-space: normal; }
        .bulk-assign-page .bad-stat-sub { grid-column: 1 / -1; font-size: 11px; white-space: normal; }

        /* step cards */
        .bulk-assign-page .bad-step-title { font-size: 15.5px; }
        .bulk-assign-page .bad-step-num { flex-basis: 30px; height: 30px; font-size: 14px; }

        /* review cards: two per row */
        .bulk-assign-page .bad-mini-stats { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 10px; }
        .bulk-assign-page .bad-mini-stats .bad-mini { padding: 12px 12px 14px; }
        .bulk-assign-page .bad-mini-stats .bad-mini strong { font-size: 22px; }
        .bulk-assign-page .bad-mini-stats .bad-mini .bad-mini-label { font-size: 12.5px; }
        .bulk-assign-page .bad-mini-sub { font-size: 11px; }

    }
    @media (max-width: 575px) {
        /* Step 1 type tabs: Manufacturer + Dealer side by side, My Stock full width below */
        .bulk-assign-page .bad-kind-tabs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .bulk-assign-page .bad-kind-tabs button { min-width: 0; padding: 9px 8px; gap: 6px; font-size: 13.5px; white-space: nowrap; }
        .bulk-assign-page .bad-kind-tabs button[data-kind="stock"] { grid-column: 1 / -1; }
        .bulk-assign-page .bad-kind-count { min-width: 18px; padding: 0 6px; font-size: 11px; }

        /* stat cards: drop the icon so label / value / sub-line get the full card width */
        .bulk-assign-page .bad-stat { grid-template-columns: minmax(0, 1fr) !important; row-gap: 4px; }
        .bulk-assign-page .bad-stat-icon { display: none !important; }
        .bulk-assign-page .bad-stat-sub { grid-column: 1; overflow: visible; text-overflow: clip; }
        .bulk-assign-page .bad-stat-label { overflow-wrap: anywhere; }
        .bulk-assign-page .bad-hero h1 { font-size: 20px; }
        .bulk-assign-page .bad-hero p { font-size: 13px; }
        .bulk-assign-page .bad-crumbs { font-size: 12.5px; gap: 6px; margin-bottom: 12px; }
        .bulk-assign-page .bad-filter button { padding: 6px 12px; }
    }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<section id="main-content" class="add-multiple-device-page bulk-assign-page">
    <section class="wrapper">

        {{-- Hero --}}
        <div class="bad-hero">
            <div class="bad-hero-main">
                <nav class="bad-crumbs" aria-label="Breadcrumb">
                    <a href="{{ url('reseller') }}" class="bad-crumb-home" title="Home"><i class="fa fa-home"></i></a>
                    <a href="{{ url('reseller') }}">Home</a>
                    <i class="fa fa-angle-right"></i>
                    <span>Device Management</span>
                    <i class="fa fa-angle-right"></i>
                    <span class="is-current">Bulk Assign Devices</span>
                </nav>
                <div class="bad-hero-title">
                    <div class="bad-hero-icon"><i class="fa fa-cubes"></i></div>
                    <div>
                        <h1>Bulk Assign Devices</h1>
                        <p>Assign or move devices to your accounts in bulk. Manage your stock, dealer accounts and devices efficiently.</p>
                    </div>
                </div>
            </div>
            <div class="bad-hero-art" aria-hidden="true">
                <svg viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="120" cy="70" rx="98" ry="60" fill="#e7f6dc"/>
                    <ellipse cx="120" cy="126" rx="82" ry="7" fill="#dfe8f3"/>
                    <path d="M14 34 h14 M34 34 h4 M18 60 h8 M206 30 h4 M212 44 h16 M214 62 h7 M30 92 h5" stroke="#5cc23a" stroke-width="3.2" stroke-linecap="round"/>
                    <circle cx="44" cy="20" r="2" fill="#5cc23a"/><circle cx="206" cy="16" r="2" fill="#5cc23a"/>
                    <rect x="68" y="20" width="104" height="72" rx="6" fill="#1e293b"/>
                    <rect x="74" y="26" width="92" height="60" rx="3" fill="#2f6fe4"/>
                    <path d="M74 68 q24 -16 46 -5 t46 -9 v29 a3 3 0 0 1 -3 3 h-86 a3 3 0 0 1 -3 -3z" fill="#2559c4"/>
                    <rect x="112" y="92" width="16" height="11" fill="#334155"/>
                    <rect x="94" y="103" width="52" height="5" rx="2.5" fill="#1e293b"/>
                    <circle cx="120" cy="54" r="18" fill="#4caf1a" stroke="#fff" stroke-width="4"/>
                    <path d="M111 54 l6 6 l12 -13" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="36" y="56" width="40" height="60" rx="5" fill="#1e293b"/>
                    <rect x="40" y="61" width="32" height="46" rx="2" fill="#3b82f6"/>
                    <circle cx="56" cy="111.5" r="1.8" fill="#94a3b8"/>
                    <path d="M48 84 l5 5 l10 -11" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="164" y="50" width="34" height="68" rx="6" fill="#1e293b"/>
                    <rect x="168" y="57" width="26" height="52" rx="2" fill="#3b82f6"/>
                    <rect x="176" y="53" width="10" height="2" rx="1" fill="#475569"/>
                    <circle cx="181" cy="113.5" r="1.8" fill="#94a3b8"/>
                    <path d="M174 84 l5 5 l10 -11" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        {{-- Overview --}}
        <div class="bad-stats">
            <div class="bad-stat bad-stat--green">
                <div class="bad-stat-icon"><i class="fa fa-cube"></i></div>
                <div style="min-width:0;">
                    <div class="bad-stat-label">Available Stock</div>
                    <div class="bad-stat-value" id="bulkStatStock">{{ $stock_count }}</div>
                </div>
                <div class="bad-stat-sub"><i class="fa fa-check-circle"></i> Ready to assign</div>
            </div>
            <div class="bad-stat bad-stat--blue">
                <div class="bad-stat-icon"><i class="fa fa-mobile"></i></div>
                <div style="min-width:0;">
                    <div class="bad-stat-label">Assigned Stock</div>
                    <div class="bad-stat-value" id="bulkStatAssigned">{{ $accounts_device_count }}</div>
                </div>
                <div class="bad-stat-sub"><i class="fa fa-share"></i> With your accounts</div>
            </div>
            <div class="bad-stat bad-stat--purple">
                <div class="bad-stat-icon"><i class="fa fa-users"></i></div>
                <div style="min-width:0;">
                    <div class="bad-stat-label">Dealer Accounts</div>
                    <div class="bad-stat-value" id="bulkStatDealers">{{ $dealer_count }}</div>
                </div>
                <div class="bad-stat-sub"><i class="fa fa-user"></i> Total accounts</div>
            </div>
            <div class="bad-stat bad-stat--orange">
                <div class="bad-stat-icon"><i class="fa fa-industry"></i></div>
                <div style="min-width:0;">
                    <div class="bad-stat-label">Manufacturer Accounts</div>
                    <div class="bad-stat-value" id="bulkStatManufacturers">{{ $manufacturer_count }}</div>
                </div>
                <div class="bad-stat-sub"><i class="fa fa-building"></i> Total accounts</div>
            </div>
            <div class="bad-stat bad-stat--slate">
                <div class="bad-stat-icon"><i class="fa fa-user"></i></div>
                <div style="min-width:0;">
                    <div class="bad-stat-label">Assigning To</div>
                    <div class="bad-stat-value bad-stat-value--sm" id="bulkStatDealer">Not selected</div>
                </div>
                <div class="bad-stat-sub"><i class="fa fa-info-circle"></i> <span id="bulkStatDealerSub">Choose an account</span></div>
            </div>
        </div>

        <form id="bulkAssignForm" enctype="multipart/form-data" onsubmit="return false;">
            @csrf

            {{-- Step 1 --}}
            <div class="bad-step" id="badStep1">
                <div class="bad-step-head">
                    <div class="bad-step-num">1</div>
                    <h3 class="bad-step-title">Choose Where Devices Go</h3>
                    <div class="bad-step-hint"><i class="fa fa-random"></i> Assign from stock, move between accounts, or take devices back to My Stock.</div>
                </div>
                <div class="bad-step-body">
                    @php
                        $manufacturerAccounts = $users->where('user_type', 'Reseller');
                        $dealerAccounts = $users->where('user_type', '!=', 'Reseller');
                    @endphp
                    <div class="bad-kind-tabs" role="tablist" aria-label="Account type">
                        <button type="button" data-kind="manufacturer" role="tab"><i class="fa fa-industry"></i> Manufacturer <span class="bad-kind-count">{{ $manufacturerAccounts->count() }}</span></button>
                        <button type="button" data-kind="dealer" role="tab"><i class="fa fa-user"></i> Dealer <span class="bad-kind-count">{{ $dealerAccounts->count() }}</span></button>
                        <button type="button" data-kind="stock" role="tab"><i class="fa fa-cubes"></i> My Stock</button>
                    </div>
                    <label for="bulk_user_id" class="bad-field-label bad-kind-label"><span id="bulkKindIntro"></span> <span class="require" id="bulkKindRequired">*</span></label>

                    <div id="bulkPickWrap">
                    <div class="bad-select">
                        <i class="fa fa-user bad-select-icon"></i>
                        <select id="bulk_user_id" name="user_id" style="width:100%;">
                            <option value=""></option>
                            <optgroup label="My Stock" data-kind="stock">
                                <option value="self" data-kind="stock">My Stock (Take back / Unassign)</option>
                            </optgroup>
                            @if($manufacturerAccounts->count() > 0)
                            <optgroup label="Manufacturers" data-kind="manufacturer">
                                @foreach($manufacturerAccounts as $user)
                                <option value="{{ $user->id }}" data-kind="manufacturer" data-categories="{{ $user->device_category_id }}">{{ $user->name }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                            @if($dealerAccounts->count() > 0)
                            <optgroup label="Dealer Accounts" data-kind="dealer">
                                @foreach($dealerAccounts as $user)
                                <option value="{{ $user->id }}" data-kind="dealer" data-categories="{{ $user->device_category_id }}">{{ $user->name }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                    </div>
                    <span class="bad-empty-note" id="bulkKindEmpty" style="display:none;"><i class="fa fa-exclamation-circle"></i> <span></span></span>
                    </div>

                    <div class="bad-stock-note" id="bulkStockNote" style="display:none;">
                        <div class="bad-stock-note-icon"><i class="fa fa-reply"></i></div>
                        <div>
                            <strong>Take back to My Stock</strong>
                            <span>Devices you add in Step 2 will be taken back from your Manufacturer / Dealer accounts and will appear under <b>Unassigned Devices</b>.</span>
                        </div>
                    </div>
                    <div class="bad-legend">
                        <span><b class="bad-dot bad-dot--assign"></b> <b>Assign:</b> My Stock &rarr; Account</span>
                        <span><b class="bad-dot bad-dot--move"></b> <b>Move:</b> Account &rarr; another Account</span>
                        <span><b class="bad-dot bad-dot--back"></b> <b>Take back:</b> Account &rarr; My Stock</span>
                    </div>
                </div>
            </div>

            {{-- Step 2: device category --}}
            <div class="bad-step" id="badStepCategory">
                <div class="bad-step-head">
                    <div class="bad-step-num">2</div>
                    <h3 class="bad-step-title">Choose Device Category</h3>
                    <div class="bad-step-hint"><i class="fa fa-tags"></i> Only devices of this category will be assigned.</div>
                </div>
                <div class="bad-step-body">
                    <label class="bad-field-label bad-kind-label">Select the <strong>device category</strong> of the devices you want to assign. <span class="require">*</span></label>
                    @if($categories->count() > 0)
                    <div class="bad-cat-grid" id="bulkCatGrid" role="radiogroup" aria-label="Device category">
                        @foreach($categories as $category)
                        <button type="button" class="bad-cat" role="radio" aria-checked="false" data-id="{{ $category->id }}" data-name="{{ $category->device_category_name }}">
                            <span class="bad-cat-check"><i class="fa fa-check"></i></span>
                            <span class="bad-cat-icon"><i class="fa fa-tags"></i></span>
                            <span class="bad-cat-text">
                                <span class="bad-cat-name">{{ $category->device_category_name }}</span>
                                <span class="bad-cat-meta"><b class="bad-cat-stock">{{ $category->stock_count }}</b> in stock &middot; <b class="bad-cat-assigned">{{ $category->assigned_count }}</b> with accounts</span>
                                <span class="bad-cat-lock"><i class="fa fa-lock"></i> Not enabled for <span></span></span>
                            </span>
                        </button>
                        @endforeach
                    </div>
                    @else
                    <span class="bad-empty-note"><i class="fa fa-exclamation-circle"></i> No device category is enabled for your account. Please contact the administrator.</span>
                    @endif
                </div>
            </div>

            {{-- Step 3: devices --}}
            <div class="bad-step" id="badStep2">
                <div class="bad-step-head">
                    <div class="bad-step-num">3</div>
                    <h3 class="bad-step-title">Add Devices</h3>
                    <div class="bad-step-hint"><i class="fa fa-file-excel-o"></i> Excel file (.xlsx, .xls, .csv) or pasted IMEI numbers.</div>
                </div>
                <div class="bad-step-body">
                    <p class="bad-step-intro">Upload an Excel sheet or paste the IMEI numbers you want to assign.</p>
                    <div class="bad-seg" role="tablist">
                        <button type="button" class="is-active" data-mode="excel" role="tab"><i class="fa fa-file-excel-o"></i> Upload Excel</button>
                        <button type="button" data-mode="paste" role="tab"><i class="fa fa-paste"></i> Paste IMEIs</button>
                    </div>

                    <div class="bad-pane" data-pane="excel">
                        <label class="bad-drop" id="bulkDrop">
                            <input type="file" name="excel_file" id="bulk_excel_file" accept=".xlsx,.xls,.csv">
                            <div class="bad-drop-icon"><i class="fa fa-cloud-upload"></i></div>
                            <div class="bad-drop-title">Drag &amp; drop your Excel file here, or <u>browse</u></div>
                            <div class="bad-drop-hint">Supports .xlsx, .xls, .csv &middot; Max 5 MB</div>
                        </label>
                        <div class="bad-file" id="bulkFileCard">
                            <i class="fa fa-file-excel-o bad-file-icon"></i>
                            <div class="bad-file-meta">
                                <div class="bad-file-name" id="bulkFileName"></div>
                                <div class="bad-file-size" id="bulkFileSize"></div>
                            </div>
                            <button type="button" class="bad-icon-btn" id="bulkFileRemove" title="Remove file"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="bad-helper-row">
                            <span><i class="fa fa-info-circle"></i> Columns: SL NO, Name, IMEI (IMEI in the 3rd column, first row is the header)</span>
                            <a href="{{ asset('assets/bulkAssignDeviceSample.xlsx') }}" download class="bad-link"><i class="fa fa-download"></i> Download sample file</a>
                        </div>
                    </div>

                    <div class="bad-pane" data-pane="paste" style="display:none;">
                        <textarea id="bulk_imei_list" name="imei_list" class="bad-textarea" placeholder="866192071233611&#10;866192077046033&#10;866192077132692"></textarea>
                        <div class="bad-helper-row">
                            <span><i class="fa fa-info-circle"></i> One IMEI per line, or separated by comma / space</span>
                            <span class="bad-count-pill"><span id="bulkPasteCount">0</span> IMEI detected</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bad-waiting" id="bulkStep3Waiting">
                <i class="fa fa-lock"></i>
                <span><strong>Step 3: Add Devices</strong> will appear after you select an account (Step 1) and a device category (Step 2).</span>
            </div>

            <div class="bad-actions">
                <button type="button" class="bad-btn bad-btn--ghost" id="bulkResetBtn"><i class="fa fa-refresh"></i> Reset</button>
                <button type="button" class="bad-btn bad-btn--primary" id="bulkPreviewBtn"><i class="fa fa-search"></i> Check Devices</button>
            </div>
        </form>

        {{-- Step 4: review --}}
        <div class="bad-step" id="bulkPreviewWrap" style="margin-top:22px;">
            <div class="bad-step-head">
                <div class="bad-step-num">4</div>
                <h3 class="bad-step-title">Review &amp; Assign</h3>
                <div class="bad-step-hint"><i class="fa fa-check-square-o"></i> Only devices marked ready can be selected.</div>
            </div>
            <div class="bad-step-body">
                <p class="bad-step-intro">Target: <strong id="bulkDealerName"></strong> &middot; Category: <strong id="bulkCategoryName"></strong></p>

                <div class="bad-mini-stats">
                    <div class="bad-mini bad-mini--total">
                        <div class="bad-mini-body"><span class="bad-mini-label">Total IMEI</span><strong id="bulkTotal">0</strong><small class="bad-mini-sub">IMEIs checked</small></div>
                    </div>
                    <div class="bad-mini bad-mini--info">
                        <div class="bad-mini-body"><span class="bad-mini-label">Already Assigned</span><strong id="bulkAlreadyAssigned">0</strong><small class="bad-mini-sub">With your accounts</small></div>
                    </div>
                    <div class="bad-mini bad-mini--ready">
                        <div class="bad-mini-body"><span class="bad-mini-label">Ready</span><strong id="bulkReady">0</strong><small class="bad-mini-sub">Can be processed</small></div>
                    </div>
                    <div class="bad-mini bad-mini--stock" id="bulkInStockCard" style="display:none;">
                        <div class="bad-mini-body"><span class="bad-mini-label">Already in Stock</span><strong id="bulkAlreadyInStock">0</strong><small class="bad-mini-sub">Already in My Stock</small></div>
                    </div>
                    <div class="bad-mini bad-mini--category" id="bulkSkipCategoryCard" style="display:none;">
                        <div class="bad-mini-body"><span class="bad-mini-label">Different Category</span><strong id="bulkSkipCategory">0</strong><small class="bad-mini-sub">Not in selected category</small></div>
                    </div>
                    <div class="bad-mini bad-mini--skip">
                        <div class="bad-mini-body"><span class="bad-mini-label">Skipped</span><strong id="bulkSkipped">0</strong><small class="bad-mini-sub">Cannot be processed</small></div>
                    </div>
                    <div class="bad-mini bad-mini--selected">
                        <div class="bad-mini-body"><span class="bad-mini-label">Selected</span><strong id="bulkSelected">0</strong><small class="bad-mini-sub">Will be processed</small></div>
                    </div>
                </div>

                <div class="bad-toolbar">
                    <div class="bad-filter" id="bulkFilter">
                        <button type="button" class="is-active" data-filter="all">All</button>
                        <button type="button" data-filter="ready">Ready</button>
                        <button type="button" data-filter="skip">Skipped</button>
                    </div>
                    <div class="bad-search">
                        <i class="fa fa-search"></i>
                        <input type="text" id="bulkSearch" placeholder="Search IMEI, name, category...">
                    </div>
                </div>

                <div class="bad-table-wrap">
                    <table class="bad-table" id="bulkPreviewTable">
                        <thead>
                            <tr>
                                <th style="width:60px;"><input type="checkbox" id="bulkCheckAll" checked title="Select all ready devices"></th>
                                <th style="width:70px;">Sr. No</th>
                                <th>IMEI</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Current Holder</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="bad-footer">
                    <div class="bad-footer-note"><strong id="bulkSelectedNote">0</strong> device(s) will go to <strong id="bulkDealerNote"></strong></div>
                    <button type="button" class="bad-btn bad-btn--dark" id="bulkAssignBtn"><i class="fa fa-check-circle"></i> <span id="bulkAssignLabel">Assign Selected Devices</span></button>
                </div>
            </div>
        </div>

    </section>
</section>
@stop

@push('scripts')
<script>
    (function ($) {
        var previewUrl = "{{ url('reseller/bulk-assign-device/preview') }}";
        var submitUrl = "{{ url('reseller/bulk-assign-device/submit') }}";
        var statsUrl = "{{ url('reseller/bulk-assign-device/stats') }}";

        // Re-read stock / account counts from the server and update the cards (top row + category tiles).
        function refreshStats() {
            $.ajax({
                url: statsUrl,
                type: 'GET',
                cache: false,
                success: function (s) {
                    $('#bulkStatStock').text(s.stock_count);
                    $('#bulkStatAssigned').text(s.accounts_device_count);
                    $('#bulkStatDealers').text(s.dealer_count);
                    $('#bulkStatManufacturers').text(s.manufacturer_count);
                    $.each(s.categories || [], function (i, c) {
                        var $tile = $('#bulkCatGrid .bad-cat[data-id="' + c.id + '"]');
                        $tile.find('.bad-cat-stock').text(c.stock_count);
                        $tile.find('.bad-cat-assigned').text(c.assigned_count);
                    });
                }
            });
        }
        var csrf = $('meta[name="csrf-token"]').attr('content');
        var previewUserId = null;
        var previewToSelf = false;
        var previewCategoryId = null;
        var selectedCategoryId = null;
        var mode = 'excel';
        // action -> [badge class, icon]
        var ACTION_STYLE = {
            assign: ['bad-badge--ready', 'fa-check-circle'],
            move: ['bad-badge--move', 'fa-exchange'],
            take_back: ['bad-badge--back', 'fa-reply'],
            skip: ['bad-badge--skip', 'fa-exclamation-circle']
        };

        function esc(v) {
            return $('<div>').text(v == null ? '' : String(v)).html();
        }

        function notify(icon, title, html) {
            if (window.Swal) {
                Swal.fire({ icon: icon, title: title, html: html, background: '#1e293b', color: '#f8fafc' });
            } else {
                alert($('<div>').html(html).text());
            }
        }

        function formatSize(bytes) {
            return bytes < 1024 * 1024 ? (bytes / 1024).toFixed(1) + ' KB' : (bytes / 1024 / 1024).toFixed(2) + ' MB';
        }

        function parsePasted() {
            var parts = $.trim($('#bulk_imei_list').val()).split(/[\s,;]+/);
            var seen = {};
            return parts.map(function (p) { return p.replace(/\D/g, ''); })
                .filter(function (p) { if (!p || seen[p]) return false; seen[p] = 1; return true; });
        }

        function showFile(file) {
            if (!file) {
                $('#bulkFileCard').removeClass('is-visible');
                $('#bulkDrop').show();
                return;
            }
            $('#bulkFileName').text(file.name);
            $('#bulkFileSize').text(formatSize(file.size));
            $('#bulkDrop').hide();
            $('#bulkFileCard').addClass('is-visible');
        }

        function updateSteps() {
            var hasUser = !!$('#bulk_user_id').val();
            var hasDevices = mode === 'excel' ? !!$('#bulk_excel_file')[0].files.length : parsePasted().length > 0;
            $('#badStep1').toggleClass('is-done', hasUser);
            $('#badStepCategory').toggleClass('is-done', !!selectedCategoryId);
            $('#badStep2').toggleClass('is-done', hasDevices);
            $('#bulkStatDealer').text(hasUser ? $('#bulk_user_id option:selected').text() : 'Not selected');
            $('#bulkStatDealerSub').text(hasUser ? ($('#bulk_user_id').val() === 'self' ? 'Take back to your stock' : 'Target account') : 'Choose an account');

            // Step 3 (Add Devices) only appears once an account and a category are chosen.
            var ready = hasUser && !!selectedCategoryId;
            $('#badStep2, #bulkPreviewBtn').toggle(ready);
            $('#bulkStep3Waiting').toggle(!ready);
        }

        function updateSelected() {
            var $boxes = $('#bulkPreviewTable .bulk-chk');
            var checked = $boxes.filter(':checked').length;
            $('#bulkSelected, #bulkSelectedNote').text(checked);
            $('#bulkCheckAll').prop('checked', $boxes.length > 0 && checked === $boxes.length)
                .prop('disabled', $boxes.length === 0);
            $('#bulkAssignBtn').prop('disabled', checked === 0);
        }

        function applyFilter() {
            var filter = $('#bulkFilter .is-active').data('filter');
            var term = $.trim($('#bulkSearch').val()).toLowerCase();
            var visible = 0;
            $('#bulkPreviewTable tbody tr[data-status]').each(function () {
                var $tr = $(this);
                var ok = (filter === 'all' || $tr.data('status') === filter)
                    && (!term || $tr.text().toLowerCase().indexOf(term) !== -1);
                $tr.toggle(ok);
                if (ok) visible++;
            });
            $('#bulkPreviewTable .bad-no-rows').toggle(visible === 0);
        }

        function resetPreview() {
            previewUserId = null;
            $('#bulkPreviewWrap').hide();
            $('#bulkPreviewTable tbody').empty();
        }

        function resetAll() {
            resetPreview();
            $('#bulk_excel_file').val('');
            $('#bulk_imei_list').val('');
            $('#bulkPasteCount').text(0);
            showFile(null);
            updateSteps();
        }

        $(function () {
            // Account picker: grouped (My Stock / Manufacturers / Dealer Accounts) with an icon per row.
            var KIND_ICON = { stock: 'fa-cubes', manufacturer: 'fa-industry', dealer: 'fa-user' };
            function kindOf(item) {
                var el = item && item.element && item.element[0];
                return el ? ($(el).data('kind') || '') : '';
            }
            if ($.fn.select2) {
                $('#bulk_user_id').select2({
                    placeholder: 'Select Account',
                    width: '100%',
                    dropdownCssClass: 'bad-s2-drop',
                    formatResult: function (item, container, query, escapeMarkup) {
                        var icon = KIND_ICON[kindOf(item)];
                        return (icon ? '<i class="fa ' + icon + ' bad-opt-icon"></i>' : '')
                            + '<span>' + (escapeMarkup ? escapeMarkup(item.text) : esc(item.text)) + '</span>';
                    },
                    formatResultCssClass: function (item) {
                        var kind = kindOf(item);
                        return (item.children ? 'bad-opt-group' : 'bad-opt') + (kind ? ' bad-kind--' + kind : '');
                    }
                }).on('select2-open', function () {
                    $('.bad-s2-drop .select2-input').attr('placeholder', KIND_CFG[currentKind].search);
                });
            }

            // Account-type tabs above the picker: each tab shows only its own accounts.
            var KIND_CFG = {
                manufacturer: {
                    placeholder: 'Select Manufacturer', search: 'Search manufacturer...',
                    intro: 'Select the <strong>Manufacturer</strong> account to assign or move devices to.',
                    empty: 'No Manufacturer accounts found. Create one from Account Management.'
                },
                dealer: {
                    placeholder: 'Select Dealer', search: 'Search dealer...',
                    intro: 'Select the <strong>Dealer</strong> account to assign or move devices to.',
                    empty: 'No Dealer accounts found. Create one from Account Management.'
                },
                stock: {
                    placeholder: 'My Stock', search: '',
                    intro: 'Take devices back from your accounts into <strong>My Stock</strong>.'
                }
            };
            var OPTION_GROUPS = {};
            $('#bulk_user_id optgroup').each(function () {
                // Options only: the tab already says which type is listed, so no group heading.
                OPTION_GROUPS[$(this).data('kind')] = this.innerHTML;
            });
            var currentKind = 'manufacturer';
            var defaultKind = OPTION_GROUPS.manufacturer ? 'manufacturer' : (OPTION_GROUPS.dealer ? 'dealer' : 'manufacturer');

            function setKind(kind) {
                var cfg = KIND_CFG[kind];
                var $sel = $('#bulk_user_id');
                currentKind = kind;
                $('.bad-kind-tabs button').removeClass('is-active').filter('[data-kind="' + kind + '"]').addClass('is-active');
                $('#bulkKindIntro').html(cfg.intro);
                $('#bulkKindRequired').toggle(kind !== 'stock');

                $sel.html('<option value=""></option>' + (OPTION_GROUPS[kind] || '')).attr('data-placeholder', cfg.placeholder);
                var value = kind === 'stock' ? 'self' : '';
                if ($.fn.select2) {
                    $sel.select2('val', value);
                } else {
                    $sel.val(value);
                }

                var isStock = kind === 'stock';
                var isEmpty = !isStock && !OPTION_GROUPS[kind];
                $('#bulkPickWrap').toggle(!isStock);
                $('#bulkStockNote').toggle(isStock);
                $('#bulkKindEmpty').toggle(isEmpty).find('span').text(cfg.empty || '');
                $sel.trigger('change');
            }

            $('.bad-kind-tabs').on('click', 'button', function () {
                var kind = $(this).data('kind');
                if (kind !== currentKind) {
                    setKind(kind);
                }
            });

            // Device category tiles: lock the ones the chosen account has not enabled.
            function refreshCategories() {
                var $opt = $('#bulk_user_id option:selected');
                var raw = $opt.length && $opt.val() && $opt.val() !== 'self' ? String($opt.data('categories') || '') : null;
                var allowed = raw === null ? null : raw.split(',').map(function (v) { return $.trim(v); });
                var accountName = $opt.text();
                var $tiles = $('#bulkCatGrid .bad-cat');

                $tiles.each(function () {
                    var $t = $(this);
                    var locked = allowed !== null && allowed.indexOf(String($t.data('id'))) === -1;
                    $t.toggleClass('is-locked', locked).prop('disabled', locked);
                    $t.find('.bad-cat-lock span').text(accountName);
                    if (locked && String($t.data('id')) === selectedCategoryId) {
                        selectCategory(null);
                    }
                });

                var $open = $tiles.not('.is-locked');
                if (!selectedCategoryId && $open.length === 1) {
                    selectCategory(String($open.data('id')));
                }
            }

            function selectCategory(id) {
                selectedCategoryId = id;
                $('#bulkCatGrid .bad-cat').each(function () {
                    var on = String($(this).data('id')) === id;
                    $(this).toggleClass('is-selected', on).attr('aria-checked', on ? 'true' : 'false');
                });
                resetPreview();
                updateSteps();
            }

            // Click to select, click the selected card again to unselect.
            $('#bulkCatGrid').on('click', '.bad-cat', function () {
                if ($(this).hasClass('is-locked')) {
                    return;
                }
                var id = String($(this).data('id'));
                selectCategory(id === selectedCategoryId ? null : id);
            });

            // A different account means different category checks, so force a fresh preview.
            $('#bulk_user_id').on('change', function () {
                var kind = $(this).find('option:selected').data('kind');
                $('.bad-select-icon').attr('class', 'fa ' + (KIND_ICON[kind] || 'fa-user') + ' bad-select-icon');
                refreshCategories();
                resetPreview();
                updateSteps();
            });

            $('.bad-seg button').on('click', function () {
                mode = $(this).data('mode');
                $('.bad-seg button').removeClass('is-active');
                $(this).addClass('is-active');
                $('.bad-pane').hide().filter('[data-pane="' + mode + '"]').show();
                resetPreview();
                updateSteps();
            });

            $('#bulk_excel_file').on('change', function () {
                showFile(this.files[0]);
                resetPreview();
                updateSteps();
            });
            $('#bulkFileRemove').on('click', function () {
                $('#bulk_excel_file').val('');
                showFile(null);
                resetPreview();
                updateSteps();
            });
            $('#bulkDrop')
                .on('dragenter dragover', function () { $(this).addClass('is-over'); })
                .on('dragleave drop', function () { $(this).removeClass('is-over'); });

            $('#bulk_imei_list').on('input', function () {
                $('#bulkPasteCount').text(parsePasted().length);
                resetPreview();
                updateSteps();
            });

            $('#bulkResetBtn').on('click', function () {
                selectedCategoryId = null;
                setKind(defaultKind);
                selectCategory(null);
                refreshCategories();
                resetAll();
            });

            $('#bulkPreviewBtn').on('click', function () {
                var userId = $('#bulk_user_id').val();
                var file = $('#bulk_excel_file')[0].files[0];
                if (!userId) {
                    notify('warning', 'Select Account', 'Please select an account, or My Stock to take devices back.');
                    return;
                }
                if (!selectedCategoryId) {
                    notify('warning', 'Select Category', 'Please select the device category in Step 2.');
                    return;
                }
                if (mode === 'excel' && !file) {
                    notify('warning', 'No File', 'Please upload an Excel file with the IMEI list.');
                    return;
                }
                if (mode === 'paste' && !parsePasted().length) {
                    notify('warning', 'No IMEI', 'Please paste at least one IMEI.');
                    return;
                }

                var fd = new FormData();
                fd.append('user_id', userId);
                fd.append('category_id', selectedCategoryId);
                var categoryId = selectedCategoryId;
                if (mode === 'excel') fd.append('excel_file', file);
                else fd.append('imei_list', $.trim($('#bulk_imei_list').val()));

                var $btn = $(this).prop('disabled', true);
                $('#loading').show();
                $.ajax({
                    url: previewUrl,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        previewUserId = userId;
                        previewCategoryId = categoryId;
                        $('#bulkCategoryName').text(res.category || '');
                        var html = '';
                        $.each(res.rows, function (i, r) {
                            var ready = r.status === 'ready';
                            html += '<tr data-status="' + (ready ? 'ready' : 'skip') + '" class="' + (ready ? '' : 'is-skip') + '">'
                                // Devices already with another account ("move") are opt-in: not ticked by default.
                                + '<td>' + (ready ? '<input type="checkbox" class="bulk-chk" value="' + esc(r.id) + '"' + (r.action === 'move' ? '' : ' checked') + '>' : '<i class="fa fa-ban" style="color:#cbd5e1"></i>') + '</td>'
                                + '<td>' + (i + 1) + '</td>'
                                + '<td class="bad-imei">' + esc(r.imei) + '</td>'
                                + '<td>' + esc(r.name || (r.holder ? 'N/A' : '-')) + '</td>'
                                + '<td>' + esc(r.category || '-') + '</td>'
                                + '<td>' + esc(r.holder || '-') + '</td>'
                                + '<td><span class="bad-badge ' + (ACTION_STYLE[r.action] || ACTION_STYLE.skip)[0] + '">'
                                + '<i class="fa ' + (ACTION_STYLE[r.action] || ACTION_STYLE.skip)[1] + '"></i>' + esc(r.reason) + '</span></td>'
                                + '</tr>';
                        });
                        html += '<tr class="bad-no-rows" style="display:none;"><td colspan="7">No devices match this filter.</td></tr>';
                        $('#bulkPreviewTable tbody').html(html);
                        $('#bulkDealerName, #bulkDealerNote').text(res.dealer);
                        previewToSelf = !!res.to_self;
                        $('#bulkAssignLabel').text(previewToSelf ? 'Take Back Selected Devices' : 'Assign Selected Devices');
                        $('#bulkTotal').text(res.total);
                        $('#bulkAlreadyAssigned').text(res.already_assigned || 0);
                        $('#bulkReady').text(res.ready);
                        $('#bulkSkipped').text(res.total - res.ready);
                        $('#bulkSkipCategory').text(res.different_category || 0);
                        $('#bulkSkipCategoryCard').toggle((res.different_category || 0) > 0);
                        $('#bulkAlreadyInStock').text(res.already_in_stock || 0);
                        $('#bulkInStockCard').toggle((res.already_in_stock || 0) > 0);
                        $('#bulkSearch').val('');
                        $('#bulkFilter button').removeClass('is-active').filter('[data-filter="all"]').addClass('is-active');
                        $('#bulkPreviewWrap').show();
                        updateSelected();
                        applyFilter();
                        $('html, body').animate({ scrollTop: $('#bulkPreviewWrap').offset().top - 90 }, 300);
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Could not read the devices.';
                        notify('error', 'Error', esc(msg));
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $('#loading').hide();
                    }
                });
            });

            $('#bulkFilter').on('click', 'button', function () {
                $('#bulkFilter button').removeClass('is-active');
                $(this).addClass('is-active');
                applyFilter();
            });
            $('#bulkSearch').on('input', applyFilter);

            $('#bulkCheckAll').on('change', function () {
                $('#bulkPreviewTable .bulk-chk').prop('checked', this.checked);
                updateSelected();
            });
            $(document).on('change', '#bulkPreviewTable .bulk-chk', updateSelected);

            $('#bulkAssignBtn').on('click', function () {
                var ids = $('#bulkPreviewTable .bulk-chk:checked').map(function () { return this.value; }).get();
                if (!previewUserId || ids.length === 0) {
                    notify('warning', 'No Device Selected', 'Please select at least one device.');
                    return;
                }
                var dealer = $('#bulkDealerName').text();
                var confirmText = previewToSelf
                    ? 'Take back ' + ids.length + ' device(s) into your stock? They will appear under Unassigned Devices.'
                    : 'Assign ' + ids.length + ' device(s) to ' + dealer + '?';
                var go = function () {
                    var $btn = $('#bulkAssignBtn').prop('disabled', true);
                    $('#loading').show();
                    $.ajax({
                        url: submitUrl,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        data: { user_id: previewUserId, category_id: previewCategoryId, ids: ids },
                        success: function (data) {
                            var html = (data.success || '') + (data.error ? '<br><span style="color:#f87171">' + data.error + '</span>' : '');
                            notify(data.success ? 'success' : 'error', data.success ? (previewToSelf ? 'Devices Taken Back' : 'Devices Assigned') : 'Error', html);
                            // No page reload: clear the device list and refresh the counts in place.
                            resetAll();
                            refreshStats();
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Assignment failed.';
                            notify('error', 'Error', esc(msg));
                            $btn.prop('disabled', false);
                        },
                        complete: function () {
                            $('#loading').hide();
                        }
                    });
                };
                if (window.Swal) {
                    Swal.fire({
                        title: previewToSelf ? 'Confirm Take Back' : 'Confirm Assignment',
                        text: confirmText,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: previewToSelf ? 'Yes, take back' : 'Yes, assign',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#5fb015',
                        background: '#1e293b',
                        color: '#f8fafc'
                    }).then(function (r) { if (r.isConfirmed) go(); });
                } else if (confirm(confirmText)) {
                    go();
                }
            });

            setKind(defaultKind);
            updateSteps();
        });
    })(jQuery);
</script>
@endpush
