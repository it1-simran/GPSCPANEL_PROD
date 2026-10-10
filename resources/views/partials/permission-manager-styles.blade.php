{{-- Shared design for the permission manager pages (Admin "Manage Permissions" and Manufacturer "Manage Child Permissions"). --}}
<style>
    .child-user-select-wrap { max-width: 400px; }
    /* Account-type tabs above the child picker (same look as the Bulk Assign page) */
    .child-kind-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
    .child-kind-tabs button {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-width: 150px; padding: 9px 20px;
        border: 1px solid #e2e8f0; border-radius: 10px; background: #eef2f6;
        color: #1e293b; font-size: 14px; font-weight: 600; cursor: pointer;
        transition: background .15s, color .15s, box-shadow .15s;
    }
    .child-kind-tabs button:hover { background: #e4eaf1; }
    .child-kind-tabs button i { font-size: 15px; color: #334155; }
    .child-kind-tabs button.is-active {
        background: linear-gradient(135deg, #4caf1a 0%, #3f9a12 100%);
        border-color: #3f9a12; color: #fff; box-shadow: 0 4px 12px rgba(76, 175, 26, .28);
    }
    .child-kind-tabs button.is-active i { color: #fff; }
    .child-kind-count { min-width: 22px; padding: 1px 7px; border-radius: 10px; font-size: 11.5px; font-weight: 700; background: #fff; color: #475569; }
    .child-kind-tabs button.is-active .child-kind-count { background: rgba(255, 255, 255, .25); color: #fff; }
    .child-kind-label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 10px; }
    .child-kind-label strong { font-weight: 800; color: #0f172a; }
    .child-kind-empty { display: block; margin-top: 8px; color: #dc2626; font-size: 13px; }
    @media (max-width: 767px) { .child-kind-tabs button { flex: 1; min-width: 0; padding: 9px 10px; } }
    .child-user-select-wrap .select2-container { width: 100% !important; }
    /* ===== Manage Child Permissions: header, info strip, picker card ===== */
    /* #main-content prefix: must out-rank the global "#main-content .c_panel" card style */
    #main-content .c_panel.mcp-panel { background: transparent !important; border: 0 !important; box-shadow: none !important; overflow: visible !important; padding: 0 !important; }
    .mcp-content { padding: 6px 0 0 !important; background: transparent !important; }
    .mcp-hero {
        display: flex; align-items: center; justify-content: space-between; gap: 20px;
        padding: 22px 26px; margin-bottom: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e3e9f2; border-radius: 14px; box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
    }
    .mcp-hero-main { display: flex; align-items: center; gap: 18px; min-width: 0; }
    .mcp-hero-icon {
        flex: 0 0 60px; height: 60px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        background: #e3f6d6; color: #3f9a12; font-size: 26px;
    }
    .mcp-hero h1 { margin: 0 0 5px; font-size: 22px; font-weight: 800; color: #0f172a; text-transform: none; letter-spacing: 0; }
    .mcp-hero p { margin: 0; font-size: 13.5px; color: #475569; }
    .mcp-total {
        flex: 0 0 auto; display: flex; align-items: center; gap: 12px;
        padding: 12px 18px; border: 1px solid #c9ebb0; border-radius: 12px; background: #f1fbe9;
    }
    .mcp-total-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #dcf3cb; color: #3f9a12; font-size: 18px;
    }
    .mcp-total-label { display: block; font-size: 12.5px; color: #334155; }
    .mcp-total-value { display: block; font-size: 15px; font-weight: 800; color: #2f7d12; }
    .mcp-info {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 13px 18px; margin-bottom: 16px;
        border: 1px solid #bfe9c5; border-radius: 12px; background: #effcf1;
        color: #166534; font-size: 13.5px;
    }
    .mcp-info i { font-size: 17px; color: #22a34a; margin-top: 1px; }
    .mcp-card {
        padding: 22px 24px 16px; margin-bottom: 20px;
        background: #fff; border: 1px solid #e3e9f2; border-radius: 14px; box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
    }
    .mcp-pick { display: block; }
    .mcp-pick .child-user-select-wrap { max-width: none; margin: 0; }
    .mcp-pick .child-kind-tabs { margin-bottom: 22px; }
    .mcp-pick .child-kind-label i { color: #334155; margin-right: 4px; }
    .mcp-pick .child-kind-label .require { color: #dc2626; }
    /* Account picker: one clean 48px field with a centred icon box, text and arrow */
    .mcp-select { position: relative; }
    .mcp-select-icon {
        position: absolute; left: 10px; top: 50%; transform: translateY(-50%); z-index: 3;
        width: 30px; height: 30px; border-radius: 8px; background: #eef2f6; color: #334155;
        display: flex; align-items: center; justify-content: center; font-size: 14px; pointer-events: none;
    }
    .mcp-select .select2-container { display: block; margin: 0 !important; padding: 0 !important; border: 0 !important; height: auto; }
    .mcp-select .select2-container .select2-choice {
        display: flex !important; align-items: center;
        box-sizing: border-box !important; width: 100% !important;
        height: 48px !important; line-height: normal !important;
        padding: 0 44px 0 52px !important;
        border: 1px solid #d5deea !important; border-radius: 10px !important;
        background: #fff !important; background-image: none !important; box-shadow: none !important;
        transition: border-color .15s, box-shadow .15s;
    }
    .mcp-select .select2-container .select2-choice:hover { border-color: #b7c4d6 !important; }
    .mcp-select .select2-container-active .select2-choice,
    .mcp-select .select2-dropdown-open .select2-choice { border-color: #4caf1a !important; box-shadow: 0 0 0 3px rgba(76, 175, 26, .15) !important; }
    .mcp-select .select2-container .select2-choice .select2-chosen {
        margin: 0 !important; line-height: 1.3 !important; font-size: 14.5px; color: #0f172a;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .mcp-select .select2-container .select2-choice.select2-default .select2-chosen { color: #64748b; }
    .mcp-select .select2-container .select2-choice .select2-arrow {
        top: 0 !important; bottom: 0; height: auto !important; width: 36px; right: 4px;
        border: 0 !important; background: transparent !important;
        display: flex; align-items: center; justify-content: center;
    }
    /* Replace the Select2 sprite (renders as a cropped double arrow here) with a CSS chevron */
    .mcp-select .select2-container .select2-choice .select2-arrow b {
        width: 0 !important; height: 0 !important; background: none !important;
        border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid #64748b;
        transition: transform .15s;
    }
    .mcp-select .select2-dropdown-open .select2-choice .select2-arrow b { transform: rotate(180deg); }
    .mcp-select .select2-container .select2-choice abbr { top: 50% !important; transform: translateY(-50%); right: 38px; }

    /* Account picker dropdown (Select2 appends it to <body>, so it is scoped by its own class) */
    .mcp-s2-drop.select2-drop { border: 1px solid #4caf1a !important; border-top: 0 !important; border-radius: 0 0 10px 10px; box-shadow: 0 10px 24px rgba(15, 23, 42, .12); overflow: hidden; }
    .mcp-s2-drop.select2-drop-above { border-top: 1px solid #4caf1a !important; border-bottom: 0 !important; border-radius: 10px 10px 0 0; }
    .mcp-s2-drop .select2-search { padding: 10px 10px 6px; }
    .mcp-s2-drop .select2-search input {
        height: 38px; min-height: 38px; padding: 6px 12px 6px 34px !important;
        border: 1px solid #d5deea !important; border-radius: 8px !important; font-size: 13.5px; color: #1e293b; box-shadow: none !important;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.4' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-3.5-3.5'/%3E%3C/svg%3E") no-repeat 11px center / 15px 15px !important;
    }
    .mcp-s2-drop .select2-search input:focus { border-color: #4caf1a !important; }
    .mcp-s2-drop .select2-results { max-height: 280px; margin: 0; padding: 0 0 6px; }
    .mcp-s2-drop .select2-results li { margin: 0; }
    .mcp-s2-drop .select2-results .select2-result-label { padding: 10px 14px; font-size: 14px; color: #334155; line-height: 1.35; }
    .mcp-s2-drop .select2-results .select2-highlighted { background: #eef9e3 !important; color: #1e293b !important; }
    .mcp-s2-drop .select2-results .select2-highlighted .select2-result-label { color: #1e293b; }
    .mcp-s2-drop .select2-results .select2-no-results { padding: 12px 14px; font-size: 13px; color: #64748b; background: #fff; }
    .mcp-s2-drop .select2-match { text-decoration: none; font-weight: 700; color: #3f9a12; }
    .mcp-hint { margin-top: 18px; padding-top: 14px; border-top: 1px solid #edf1f6; font-size: 13px; color: #64748b; }
    .mcp-hint i { color: #94a3b8; margin-right: 4px; }
    @media (max-width: 991px) {
        .mcp-hero { flex-direction: column; align-items: flex-start; }
    }
    @media (max-width: 767px) {
        .mcp-hero, .mcp-card { padding: 16px; }
    }

    /* ===== Permissions section: header + module cards (2 per row) + toggles ===== */
    #permissionsContainer {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        align-items: start;
        padding: 22px 24px 24px;
        margin-bottom: 20px;
        background: #fff;
        border: 1px solid #e3e9f2;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
    }
    #permissionsContainer > .perm-head,
    #permissionsContainer > .permission-no-results,
    #permissionsContainer > .save-button { grid-column: 1 / -1; }
    #permissionsContainer .perm-head {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;
        padding-bottom: 16px; border-bottom: 1px solid #edf1f6;
    }
    #permissionsContainer .perm-head h3 { margin: 0 0 4px; font-size: 18px; font-weight: 800; color: #0f172a; }
    #permissionsContainer .perm-head h3 span { color: #3f9a12; }
    #permissionsContainer .perm-head p { margin: 0; font-size: 13px; color: #64748b; }
    #permissionsContainer .perm-head p strong { color: #0f172a; }
    #permissionsContainer .permission-toolbar { margin: 0; flex: 0 1 340px; min-width: 240px; }
    #permissionsContainer .permission-search-wrap { margin: 0; }
    #permissionsContainer .permission-search-wrap input {
        height: 42px; border: 1px solid #d5deea !important; border-radius: 10px !important; background: #f8fafc; box-shadow: none;
    }
    #permissionsContainer .permission-search-wrap input:focus { background: #fff; border-color: #4caf1a !important; box-shadow: 0 0 0 3px rgba(76, 175, 26, .15); outline: none; }

    #permissionsContainer .module-section {
        margin: 0;
        border: 1px solid #e3e9f2;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow .15s, border-color .15s;
    }
    #permissionsContainer .module-section:hover { border-color: #cfe7bd; box-shadow: 0 6px 18px rgba(15, 23, 42, .06); }
    #permissionsContainer .module-title {
        display: flex; align-items: center; gap: 12px;
        margin: 0; padding: 14px 16px;
        background: linear-gradient(180deg, #fbfcfe 0%, #f4f7fb 100%);
        border: 0; border-bottom: 1px solid #e8edf3;
        font-size: 15px; font-weight: 800; color: #0f172a;
    }
    #permissionsContainer .mt-icon {
        flex: 0 0 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        background: #e3f6d6; color: #3f9a12; font-size: 15px;
    }
    #permissionsContainer .mt-name { flex: 1 1 auto; min-width: 0; }
    #permissionsContainer .mt-count {
        flex: 0 0 auto; padding: 3px 10px; border-radius: 12px;
        background: #eef2f6; color: #475569; font-size: 12px; font-weight: 700;
    }
    #permissionsContainer .mt-count.is-full { background: #e3f6d6; color: #2f7d12; }

    #permissionsContainer .permission-matrix { width: 100%; margin: 0; border: 0; border-collapse: collapse; }
    #permissionsContainer .permission-matrix td { border: 0; border-top: 1px solid #f0f3f7; padding: 12px 16px; text-align: left; vertical-align: middle; }
    #permissionsContainer .permission-matrix tr:first-child td { border-top: 0; }
    #permissionsContainer .permission-matrix tbody tr:hover { background: #f9fbf6; }
    #permissionsContainer .perm-cell { display: flex; flex-direction: column; gap: 2px; }
    #permissionsContainer .perm-label { font-size: 14px; font-weight: 600; color: #1e293b; }
    #permissionsContainer .perm-toggle-cell { width: 76px; text-align: right !important; }

    #permissionsContainer .toggle-switch { width: 46px; height: 26px; margin: 0; vertical-align: middle; }
    #permissionsContainer .toggle-slider { background-color: #d5dde7; border-radius: 26px; box-shadow: inset 0 1px 2px rgba(15, 23, 42, .12); }
    #permissionsContainer .toggle-slider:before { height: 20px; width: 20px; left: 3px; bottom: 3px; box-shadow: 0 1px 3px rgba(15, 23, 42, .25); }
    #permissionsContainer input:checked + .toggle-slider { background-color: #4caf1a; }
    #permissionsContainer input:checked + .toggle-slider:before { transform: translateX(20px); }
    #permissionsContainer input:focus-visible + .toggle-slider { box-shadow: 0 0 0 3px rgba(76, 175, 26, .25); }

    #permissionsContainer > .save-button {
        width: 100%; padding: 13px !important; border-radius: 10px;
        background: linear-gradient(135deg, #4caf1a 0%, #3f9a12 100%); font-size: 14.5px;
        box-shadow: 0 4px 12px rgba(76, 175, 26, .25);
    }
    #permissionsContainer > .save-button:hover { filter: brightness(1.05); }

    @media (max-width: 1199px) {
        #permissionsContainer { grid-template-columns: 1fr; }
    }
    @media (max-width: 767px) {
        #permissionsContainer { padding: 16px; }
        #permissionsContainer .permission-toolbar { flex-basis: 100%; }
    }

    /* ===================== Mobile polish (keep last so it wins) ===================== */
    @media (max-width: 767px) {
        .mcp-hero { gap: 14px; }
        .mcp-hero-main { gap: 12px; align-items: flex-start; }
        .mcp-hero-icon { flex-basis: 46px; height: 46px; border-radius: 12px; font-size: 20px; }
        .mcp-hero h1 { font-size: 19px; }
        .mcp-hero p { font-size: 13px; }
        .mcp-total { width: 100%; }
        .mcp-info { font-size: 13px; padding: 12px 14px; }
        #permissionsContainer .perm-head h3 { font-size: 16px; }
        #permissionsContainer .module-title { padding: 12px; gap: 10px; font-size: 14.5px; }
        #permissionsContainer .mt-icon { flex-basis: 30px; height: 30px; font-size: 14px; }
        #permissionsContainer .mt-count { font-size: 11px; padding: 2px 8px; }
        #permissionsContainer .permission-matrix td { padding: 11px 12px; }
        #permissionsContainer .perm-label { font-size: 13.5px; }
    }
    @media (max-width: 575px) {
        .child-kind-tabs { gap: 6px; flex-wrap: nowrap; }
        .child-kind-tabs button { flex: 1 1 0; min-width: 0; padding: 9px 6px; gap: 5px; font-size: 13px; white-space: nowrap; }
        .child-kind-count { min-width: 18px; padding: 0 5px; font-size: 10.5px; }
        .mcp-select .select2-container .select2-choice { height: 46px !important; padding-left: 48px !important; }
        .mcp-select .select2-container .select2-choice .select2-chosen { font-size: 13.5px; }
    }
</style>
