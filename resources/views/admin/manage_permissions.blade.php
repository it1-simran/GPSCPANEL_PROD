@extends('layouts.apps')

@push('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-device') }}">
<style>
    .permission-matrix { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    .permission-matrix th, .permission-matrix td { border: 1px solid #e2e8f0; padding: 12px; text-align: center; }
    .permission-matrix th { background-color: #1e293b; color: white; font-weight: 600; }
    .permission-matrix tbody tr:hover { background-color: #f8fafc; }
    .permission-matrix td:first-child, .permission-matrix th:first-child { text-align: left; }
    .toggle-switch { position: relative; display: inline-block; width: 40px; height: 20px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: 0.3s; border-radius: 20px; }
    .toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; transition: 0.3s; border-radius: 50%; }
    input:checked + .toggle-slider { background-color: #76CF1C; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }
    .module-section { margin-bottom: 30px; }
    .module-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 15px; padding: 10px; background-color: #f1f5f9; border-left: 4px solid #76CF1C; }
    .reseller-select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; }
    .save-button { background-color: #76CF1C; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
    .save-button:hover { background-color: #5fb815; }
    .loading { display: none; text-align: center; padding: 20px; }
    .loading.active { display: block; }
    .permission-search-wrap { margin-bottom: 20px; position: relative; }
    .permission-search-wrap input {
        width: 100%;
        max-width: 400px;
        padding: 10px 12px 10px 36px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    .permission-search-wrap .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .permission-no-results {
        display: none;
        text-align: center;
        padding: 24px;
        color: #64748b;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .permission-no-results.active { display: block; }
    .permission-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .permission-toolbar .permission-search-wrap { margin-bottom: 0; flex: 1; min-width: 220px; }
    .account-select-wrap { max-width: 400px; }

    /* ===== Hierarchy summary (Admin page only) ===== */
    .hier-card {
        padding: 20px 24px; margin-bottom: 20px;
        background: #fff; border: 1px solid #e3e9f2; border-radius: 14px; box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
    }
    .hier-card--loading { color: #64748b; font-size: 14px; }
    .hier-card#hierarchyCard { padding: 0; overflow: hidden; }
    .hier-head {
        display: flex; align-items: center; justify-content: space-between; gap: 14px;
        padding: 16px 22px; cursor: pointer; user-select: none; transition: background .15s;
    }
    .hier-head:hover { background: #f8fafc; }
    .hier-head:focus-visible { outline: 2px solid #4caf1a; outline-offset: -2px; }
    .hier-chevron {
        flex: 0 0 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: #eef2f6; color: #475569; font-size: 13px; transition: transform .2s, background .15s, color .15s;
    }
    .hier-card.is-open .hier-chevron { transform: rotate(180deg); background: #e3f6d6; color: #2f7d12; }
    .hier-body { padding: 0 22px 20px; border-top: 1px solid #edf1f6; }
    .hier-body-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 14px 0; }
    .hier-note { margin: 0; font-size: 13px; color: #64748b; }
    .hier-note i { color: #94a3b8; margin-right: 4px; }
    #hierHeadSummary { color: #3f9a12; font-weight: 600; }
    .hier-head h3 { margin: 0 0 4px; font-size: 17px; font-weight: 800; color: #0f172a; }
    .hier-head h3 i { color: #3f9a12; margin-right: 6px; }
    .hier-head p { margin: 0; font-size: 13px; color: #64748b; }
    .hier-head p strong { color: #0f172a; }
    .hier-toggle-all {
        flex: 0 0 auto; padding: 7px 14px; border: 1px solid #d5deea; border-radius: 9px;
        background: #fff; color: #334155; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .hier-toggle-all:hover { border-color: #4caf1a; color: #2f7d12; }
    .hier-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .hier-stat { padding: 12px 14px; border-radius: 11px; border: 1px solid #e3e9f2; background: #f8fafc; }
    .hier-stat span { display: block; font-size: 12.5px; font-weight: 600; color: #475569; }
    .hier-stat strong { display: block; margin-top: 2px; font-size: 24px; font-weight: 800; color: #0f172a; }
    .hier-stat--mfr { background: #fdf6ec; border-color: #f6dfbb; } .hier-stat--mfr strong { color: #b45309; }
    .hier-stat--dealer { background: #f4f0ff; border-color: #e2d8fb; } .hier-stat--dealer strong { color: #6d3fd0; }
    .hier-stat--total { background: #eef4ff; border-color: #d6e4fd; } .hier-stat--total strong { color: #1d4ed8; }
    .hier-stat--devices { background: #eef9e6; border-color: #cfeabb; } .hier-stat--devices strong { color: #2f7d12; }
    .hier-tree-wrap { max-height: 420px; overflow: auto; padding: 12px 14px; border: 1px solid #edf1f6; border-radius: 11px; background: #fbfcfe; }
    .hier-tree ul { list-style: none; margin: 0; padding-left: 22px; border-left: 1px dashed #cbd5e1; }
    .hier-tree > ul { padding-left: 0; border-left: 0; }
    .hier-tree li { position: relative; margin: 4px 0; }
    .hier-tree ul ul > li::before { content: ''; position: absolute; left: -22px; top: 17px; width: 18px; border-top: 1px dashed #cbd5e1; }
    .hier-node {
        display: inline-flex; align-items: center; gap: 8px; max-width: 100%;
        padding: 6px 10px; border: 1px solid #e3e9f2; border-radius: 9px; background: #fff;
    }
    .hier-node.is-root { border-color: #4caf1a; background: #f4fbee; }
    .hier-caret {
        width: 20px; height: 20px; flex: 0 0 20px; border: 0; border-radius: 5px; background: #eef2f6; color: #475569;
        display: flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer; padding: 0;
    }
    .hier-caret.is-leaf { visibility: hidden; }
    .hier-icon { width: 26px; height: 26px; flex: 0 0 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 13px; }
    .hier-icon--Reseller { background: #fcefdc; color: #d97706; }
    .hier-icon--User { background: #ece5fb; color: #7c4ddb; }
    .hier-icon--other { background: #e8edf3; color: #334155; }
    .hier-name { font-size: 13.5px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .hier-email { font-size: 12px; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .hier-type { padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; background: #eef2f6; color: #475569; white-space: nowrap; }
    .hier-dev { font-size: 11.5px; color: #2f7d12; white-space: nowrap; }
    .hier-dev i { margin-right: 3px; }
    .hier-empty { font-size: 13px; color: #64748b; padding: 6px 2px; }
    li.is-collapsed > ul { display: none; }
    @media (max-width: 767px) {
        .hier-card { padding: 16px; }
        .hier-head { padding: 14px 16px; }
        .hier-body { padding: 0 16px 16px; }
        .hier-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .hier-email { display: none; }
        .hier-tree ul { padding-left: 14px; }
        .hier-tree ul ul > li::before { left: -14px; width: 10px; }
    }
    .account-select-wrap .select2-container { width: 100% !important; }
</style>
@include('partials.permission-manager-styles')
@endpush

@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel mcp-panel">
                    <div class="c_content mcp-content">
                        <div class="row" id="alert_msg">
                            @include('partials.gps-inline-alerts')
                        </div>

                        @php
                            $selectedAccount = null;
                            if (!empty($selectedAccountId)) {
                                $selectedAccount = $accounts->firstWhere('id', (int) $selectedAccountId);
                            }
                            $accountKindOf = function ($type) {
                                return $type === 'Reseller' ? 'manufacturer' : 'dealer';
                            };
                            $manufacturerCount = $accounts->where('user_type', 'Reseller')->count();
                            $dealerCount = $accounts->where('user_type', 'User')->count();
                            $totalPermissionCount = collect($permissionsByModule)->flatten(1)->count();
                            $moduleIcons = ['account_management' => 'fa-users', 'device_management' => 'fa-mobile', 'certificate_management' => 'fa-certificate', 'settings_management' => 'fa-sliders'];
                        @endphp

                        {{-- Header --}}
                        <div class="mcp-hero">
                            <div class="mcp-hero-main">
                                <div class="mcp-hero-icon"><i class="fa fa-lock"></i></div>
                                <div>
                                    <h1>Manage Account Permissions</h1>
                                    <p>Choose a Manufacturer or Dealer account you created and decide which features it can use.</p>
                                </div>
                            </div>
                            <div class="mcp-total">
                                <div class="mcp-total-icon"><i class="fa fa-key"></i></div>
                                <div>
                                    <span class="mcp-total-label">Total Permissions</span>
                                    <strong class="mcp-total-value">{{ $totalPermissionCount }} permissions</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mcp-info">
                            <i class="fa fa-check-circle"></i>
                            <span><strong>Permission Inheritance:</strong> When you turn a permission off for a Manufacturer, it is also removed automatically from all of its child accounts.</span>
                        </div>

                        {{-- Account picker --}}
                        <div class="mcp-card">
                            <div class="mcp-pick">
                                @if($accounts->isNotEmpty())
                                <div class="child-kind-tabs" role="tablist" aria-label="Account type">
                                    <button type="button" data-kind="manufacturer" role="tab"><i class="fa fa-industry"></i> Manufacturer <span class="child-kind-count">{{ $manufacturerCount }}</span></button>
                                    <button type="button" data-kind="dealer" role="tab"><i class="fa fa-user"></i> Dealer <span class="child-kind-count">{{ $dealerCount }}</span></button>
                                </div>
                                @endif

                                <div class="child-user-select-wrap">
                                    <label for="accountSelect" class="child-kind-label"><i class="fa fa-users"></i> <span id="adminKindLabel">Select the account to manage its permissions.</span> <span class="require">*</span></label>
                                    <div class="mcp-select">
                                        <i class="fa fa-industry mcp-select-icon" id="adminKindIcon"></i>
                                        <select id="accountSelect" style="width: 100%;">
                                            <option value=""></option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}"
                                                    data-user-type="{{ $account->user_type }}"
                                                    data-kind="{{ $accountKindOf($account->user_type) }}"
                                                    {{ $selectedAccount && (int) $selectedAccount->id === (int) $account->id ? 'selected' : '' }}>
                                                    {{ $account->name }} ({{ $account->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="child-kind-empty" id="adminKindEmpty" style="display:none;"><i class="fa fa-exclamation-circle"></i> <span></span></span>
                                </div>

                                @if($accounts->isEmpty())
                                    <div class="alert alert-info" style="margin-top:14px;">
                                        <i class="fa fa-info-circle"></i> No Manufacturer or Dealer accounts created by Admin yet.
                                    </div>
                                @endif
                            </div>

                            <div class="mcp-hint" id="adminPickHint">
                                <i class="fa fa-info-circle"></i> <span id="adminPickHintText">Select a manufacturer to view and manage its permissions.</span>
                            </div>
                        </div>

                        {{-- Hierarchy summary (Manufacturer accounts only, read-only) --}}
                        <div class="hier-card" id="hierarchyCard" style="display:none;">
                            {{-- FAQ-style header: closed by default, click / Enter / Space to open --}}
                            <div class="hier-head" id="hierHead" role="button" tabindex="0" aria-expanded="false" aria-controls="hierBody">
                                <div class="hier-head-text">
                                    <h3><i class="fa fa-sitemap"></i> Hierarchy Summary</h3>
                                    <p>Account chain under <strong id="hierRootName"></strong> &middot; <span id="hierHeadSummary"></span></p>
                                </div>
                                <span class="hier-chevron"><i class="fa fa-chevron-down"></i></span>
                            </div>
                            <div class="hier-body" id="hierBody" style="display:none;">
                            <div class="hier-body-top">
                                <p class="hier-note"><i class="fa fa-info-circle"></i> Permissions you turn off for this Manufacturer are also removed from every account below it.</p>
                                <button type="button" class="hier-toggle-all" id="hierToggleAll"><i class="fa fa-compress"></i> <span>Collapse all</span></button>
                            </div>
                            <div class="hier-stats">
                                <div class="hier-stat hier-stat--mfr"><span>Child Manufacturers</span><strong id="hierMfr">0</strong></div>
                                <div class="hier-stat hier-stat--dealer"><span>Dealers</span><strong id="hierDealer">0</strong></div>
                                <div class="hier-stat hier-stat--other" id="hierOtherWrap" style="display:none;"><span>Other Accounts</span><strong id="hierOther">0</strong></div>
                                <div class="hier-stat hier-stat--total"><span>Total Accounts in Chain</span><strong id="hierTotal">0</strong></div>
                                <div class="hier-stat hier-stat--devices"><span>Devices in Chain</span><strong id="hierDevices">0</strong></div>
                            </div>
                            <div class="hier-tree-wrap">
                                <div class="hier-tree" id="hierTree"></div>
                            </div>
                            </div>
                        </div>
                        <div class="hier-card hier-card--loading" id="hierarchyLoading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Loading account hierarchy...
                        </div>

                        <div id="loading" class="loading">
                            <img src="/assets/icons/loader.gif" alt="Loading..." style="height: 50px;">
                        </div>

                        <div id="permissionsContainer" style="display:none;">
                            <div class="perm-head">
                                <div class="perm-head-text">
                                    <h3>Permissions for <span id="permTargetName"></span></h3>
                                    <p><strong id="permEnabledTotal">0</strong> of <span id="permTotal">0</span> permissions enabled &middot; click <b>Save Permissions</b> to apply</p>
                                </div>
                                <div class="permission-toolbar">
                                    <div class="permission-search-wrap">
                                        <i class="fa fa-search search-icon"></i>
                                        <input type="text" id="permissionSearch" placeholder="Search permissions or modules..." autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div id="permissionNoResults" class="permission-no-results">
                                <i class="fa fa-search" style="margin-right: 6px;"></i>No permissions match your search.
                            </div>
                            @foreach($modules as $module)
                                <div class="module-section" data-module="{{ $module }}">
                                    <div class="module-title">
                                        <span class="mt-icon"><i class="fa {{ $moduleIcons[$module] ?? 'fa-cube' }}"></i></span>
                                        <span class="mt-name">{{ ucwords(str_replace('_', ' ', $module)) }}</span>
                                        <span class="mt-count">0/{{ count($permissionsByModule[$module]) }} enabled</span>
                                    </div>
                                    <table class="permission-matrix">
                                        <tbody>
                                            @foreach($permissionsByModule[$module] as $permission)
                                                <tr class="permission-row" data-permission-id="{{ $permission->id }}">
                                                    <td class="perm-cell">
                                                        <span class="perm-label">{{ $permission->label }}</span>
                                                    </td>
                                                    <td class="perm-toggle-cell">
                                                        <label class="toggle-switch">
                                                            <input type="checkbox" class="permission-checkbox" value="{{ $permission->id }}" data-permission="{{ $permission->key }}">
                                                            <span class="toggle-slider"></span>
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach

                            <button class="save-button" onclick="savePermissions()" style="width: 100%; padding: 15px;">
                                <i class="fa fa-save" style="margin-right: 8px;"></i>Save Permissions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@include('partials.permission-save-confirm')
<script>
    let currentAccountId = @json($selectedAccountId ?? null);
    let currentAccountType = @json(optional($selectedAccount)->user_type);
    let accountTypes = @json($accounts->pluck('user_type', 'id'));
    let permissionDependencies = {}; // child_id => parent_id
    let permissionDependents = {}; // parent_id => [child_id, ...]
    let isSyncingPermissions = false;
    let permissionSaveTimer = null;

    function collectCheckedPermissions() {
        const permissions = [];
        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });
        return permissions;
    }

    function applyPermissionsState(permissionIds) {
        isSyncingPermissions = true;

        let permissionsToCheck = (permissionIds || []).map(String);
        (permissionIds || []).forEach(function(permId) {
            if (permissionDependencies[permId]) {
                const parentId = String(permissionDependencies[permId]);
                if (!permissionsToCheck.includes(parentId)) {
                    permissionsToCheck.push(parentId);
                }
            }
        });

        $('.permission-checkbox').prop('checked', false);
        permissionsToCheck.forEach(function(permId) {
            $('.permission-checkbox[value="' + permId + '"]').prop('checked', true);
        });
        $('.permission-checkbox:checked').each(function() {
            syncCreateEditPair(this, true);
        });

        isSyncingPermissions = false;
        updatePermissionCounts();

        const searchQuery = $('#permissionSearch').val();
        if (searchQuery) {
            filterPermissions(searchQuery);
        }
    }

    function schedulePermissionSave() {
        if (isSyncingPermissions) {
            return;
        }
        clearTimeout(permissionSaveTimer);
        permissionSaveTimer = setTimeout(function() {
            submitPermissions(collectCheckedPermissions(), { auto: true });
        }, 400);
    }

    function syncCreateEditPair(checkbox, isChecked) {
        const permKey = $(checkbox).data('permission');
        if (!permKey) return;
        const match = permKey.match(/^(.+)\.(create|edit)$/);
        if (!match) return;
        const pairKey = match[1] + (match[2] === 'create' ? '.edit' : '.create');
        const pairCheckbox = $('.permission-checkbox[data-permission="' + pairKey + '"]');
        if (pairCheckbox.length) {
            pairCheckbox.prop('checked', isChecked);
        }
    }

    function resolveAccountType(accountId, responseUserType) {
        if (responseUserType) {
            return responseUserType;
        }
        if ($('#accountSelect').length) {
            const selectedType = $('#accountSelect option:selected').data('user-type');
            if (selectedType) {
                return selectedType;
            }
        }
        return accountTypes[accountId] || accountTypes[String(accountId)] || null;
    }

    // Permissions only Manufacturer accounts can hold (mirrors PermissionAssignmentService::MANUFACTURER_ONLY_KEYS).
    const MANUFACTURER_ONLY_PERMISSIONS = @json((new \App\Services\PermissionAssignmentService())->getManufacturerOnlyKeys());

    function manufacturerOnlyRows() {
        return $(MANUFACTURER_ONLY_PERMISSIONS.map(function(key) {
            return '.permission-checkbox[data-permission="' + key + '"]';
        }).join(',')).closest('tr');
    }

    function applyModuleVisibilityForAccount(userType) {
        currentAccountType = userType;
        const hideAccountManagement = userType === 'User';

        $('.module-section[data-module="account_management"]').each(function() {
            const $section = $(this);
            if (hideAccountManagement) {
                $section.hide();
                $section.find('.permission-checkbox').prop('checked', false);
            } else {
                $section.show();
            }
        });

        const $onlyRows = manufacturerOnlyRows();
        if (userType && userType !== 'Reseller') {
            $onlyRows.hide().find('.permission-checkbox').prop('checked', false);
        } else {
            $onlyRows.show();
        }
        updatePermissionCounts();
    }

    function initAccountSelect2() {
        const $select = $('#accountSelect');
        if (!$select.length || $select.hasClass('select2-hidden-accessible')) {
            return;
        }

        $select.select2({
            placeholder: 'Select Manufacturer',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownCssClass: 'mcp-s2-drop'
        }).on('select2-open', function() {
            $('.mcp-s2-drop .select2-input').attr('placeholder', currentAdminKind === 'dealer' ? 'Search dealer...' : 'Search manufacturer...');
        });
    }

    // Manufacturer / Dealer tabs: the picker lists only the chosen account type.
    const ADMIN_KIND_CFG = {
        manufacturer: { placeholder: 'Select Manufacturer', icon: 'fa-industry', hint: 'Select a manufacturer to view and manage its permissions.', intro: 'Select the <strong>Manufacturer</strong> account to manage its permissions.', empty: 'No Manufacturer accounts found.' },
        dealer: { placeholder: 'Select Dealer', icon: 'fa-user', hint: 'Select a dealer to view and manage its permissions.', intro: 'Select the <strong>Dealer</strong> account to manage its permissions.', empty: 'No Dealer accounts found.' }
    };
    const ADMIN_OPTIONS = {};
    let currentAdminKind = 'manufacturer';

    function collectAdminOptions() {
        $('#accountSelect option[data-kind]').each(function() {
            const kind = $(this).data('kind');
            (ADMIN_OPTIONS[kind] = ADMIN_OPTIONS[kind] || []).push(this.outerHTML.replace(/\sselected(="[^"]*")?/, ''));
        });
    }

    function updateAdminPickHint() {
        $('#adminPickHint').toggle(!$('#accountSelect').val());
    }

    // Rebuild the picker for one account type; keepId stays selected if it belongs to that type.
    function setAdminKind(kind, keepId, silent) {
        const cfg = ADMIN_KIND_CFG[kind] || ADMIN_KIND_CFG.manufacturer;
        const $select = $('#accountSelect');
        currentAdminKind = kind;
        $('.child-kind-tabs button').removeClass('is-active').filter('[data-kind="' + kind + '"]').addClass('is-active');
        $('#adminKindLabel').html(cfg.intro);
        $('#adminKindIcon').attr('class', 'fa ' + cfg.icon + ' mcp-select-icon');
        $('#adminPickHintText').text(cfg.hint);
        $select.html('<option value=""></option>' + (ADMIN_OPTIONS[kind] || []).join('')).attr('data-placeholder', cfg.placeholder);

        const keep = keepId && $select.find('option[value="' + keepId + '"]').length ? String(keepId) : '';
        if ($.fn.select2 && $select.data('select2')) {
            $select.select2('val', keep);
        } else {
            $select.val(keep);
        }
        $('#adminKindEmpty').toggle(!(ADMIN_OPTIONS[kind] || []).length).find('span').text(cfg.empty);
        updateAdminPickHint();
        if (!silent) {
            $select.trigger('change');
        }
    }

    // "X/Y enabled" badge per module, overall total and account name in the permissions header.
    // Rows hidden for the account type (Account Management / Manufacturer-only for Dealers) are not counted.
    function updatePermissionCounts() {
        let enabled = 0;
        let total = 0;
        $('#permissionsContainer .module-section').each(function() {
            const $section = $(this);
            if (currentAccountType === 'User' && $section.data('module') === 'account_management') {
                return;
            }
            const $boxes = $section.find('.permission-checkbox').filter(function() {
                return !(currentAccountType && currentAccountType !== 'Reseller'
                    && MANUFACTURER_ONLY_PERMISSIONS.indexOf($(this).data('permission')) !== -1);
            });
            const on = $boxes.filter(':checked').length;
            $section.find('.mt-count').text(on + '/' + $boxes.length + ' enabled').toggleClass('is-full', on > 0 && on === $boxes.length);
            enabled += on;
            total += $boxes.length;
        });
        $('#permEnabledTotal').text(enabled);
        $('#permTotal').text(total);
        $('#permTargetName').text(($('#accountSelect option:selected').text() || '').replace(/\s*\(.*\)\s*$/, '').trim());
    }

    // ----- Hierarchy summary (read-only, Manufacturer accounts only) -----
    let hierarchyRequest = null;

    function hierEsc(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function hideHierarchy() {
        if (hierarchyRequest) {
            hierarchyRequest.abort();
            hierarchyRequest = null;
        }
        $('#hierarchyCard, #hierarchyLoading').hide();
    }

    function renderHierarchyNode(node, isRoot) {
        const hasChildren = node.children && node.children.length;
        const iconClass = node.type === 'Reseller' ? 'fa-industry' : (node.type === 'User' ? 'fa-user' : 'fa-users');
        const iconMod = (node.type === 'Reseller' || node.type === 'User') ? node.type : 'other';
        let html = '<li>' +
            '<div class="hier-node' + (isRoot ? ' is-root' : '') + '">' +
                '<button type="button" class="hier-caret' + (hasChildren ? '' : ' is-leaf') + '" aria-label="Expand or collapse"><i class="fa fa-caret-down"></i></button>' +
                '<span class="hier-icon hier-icon--' + iconMod + '"><i class="fa ' + iconClass + '"></i></span>' +
                '<span class="hier-name">' + hierEsc(node.name) + '</span>' +
                '<span class="hier-email">' + hierEsc(node.email) + '</span>' +
                '<span class="hier-type">' + hierEsc(node.type_label) + (isRoot ? ' &middot; selected' : '') + '</span>' +
                '<span class="hier-dev"><i class="fa fa-mobile"></i>' + node.devices + ' device' + (node.devices === 1 ? '' : 's') + '</span>' +
            '</div>';
        if (hasChildren) {
            html += '<ul>' + node.children.map(function(child) { return renderHierarchyNode(child, false); }).join('') + '</ul>';
        }
        return html + '</li>';
    }

    function loadHierarchy(accountId) {
        hideHierarchy();
        $('#hierarchyLoading').show();
        hierarchyRequest = $.ajax({
            url: '/admin/permissions/' + accountId + '/hierarchy?t=' + new Date().getTime(),
            type: 'GET',
            cache: false,
            success: function(response) {
                if (String(currentAccountId) !== String(accountId)) {
                    return; // another account was selected meanwhile
                }
                const c = response.counts || {};
                $('#hierRootName').text(response.root ? response.root.name : '');
                $('#hierMfr').text(c.manufacturers || 0);
                $('#hierDealer').text(c.dealers || 0);
                $('#hierOther').text(c.others || 0);
                $('#hierOtherWrap').toggle((c.others || 0) > 0);
                $('#hierTotal').text(c.total || 0);
                $('#hierDevices').text(c.devices || 0);
                const tree = response.root ? '<ul>' + renderHierarchyNode(response.root, true) + '</ul>' : '';
                $('#hierTree').html(tree + ((c.total || 0) === 0 ? '<div class="hier-empty"><i class="fa fa-info-circle"></i> This Manufacturer has not created any child accounts yet.</div>' : ''));
                $('#hierToggleAll').toggle((c.total || 0) > 0).data('collapsed', false)
                    .find('span').text('Collapse all').end().find('i').attr('class', 'fa fa-compress');
                const plural = function(n, word) { return n + ' ' + word + (n === 1 ? '' : 's'); };
                $('#hierHeadSummary').text((c.total || 0) === 0
                    ? 'no child accounts'
                    : plural(c.manufacturers || 0, 'Manufacturer') + ' · ' + plural(c.dealers || 0, 'Dealer') + ' · ' + plural(c.total || 0, 'account'));
                setHierarchyOpen(false); // FAQ style: every new account starts closed
                $('#hierarchyLoading').hide();
                $('#hierarchyCard').show();
            },
            error: function(xhr, status) {
                $('#hierarchyLoading').hide();
                if (status !== 'abort') {
                    console.error('Hierarchy load failed', xhr.status);
                }
            },
            complete: function() {
                hierarchyRequest = null;
            }
        });
    }

    function setHierarchyOpen(open, animate) {
        const $card = $('#hierarchyCard').toggleClass('is-open', open);
        $('#hierHead').attr('aria-expanded', open ? 'true' : 'false');
        const $body = $('#hierBody').stop(true, true);
        if (animate) {
            open ? $body.slideDown(180) : $body.slideUp(180);
        } else {
            $body.toggle(open);
        }
        return $card;
    }

    $(document).on('click', '#hierHead', function() {
        setHierarchyOpen(!$('#hierarchyCard').hasClass('is-open'), true);
    });
    $(document).on('keydown', '#hierHead', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            setHierarchyOpen(!$('#hierarchyCard').hasClass('is-open'), true);
        }
    });

    $(document).on('click', '.hier-caret:not(.is-leaf)', function() {
        const $li = $(this).closest('li').toggleClass('is-collapsed');
        $(this).find('i').attr('class', 'fa ' + ($li.hasClass('is-collapsed') ? 'fa-caret-right' : 'fa-caret-down'));
    });

    $(document).on('click', '#hierToggleAll', function() {
        const collapse = !$(this).data('collapsed');
        // keep the selected (root) node open so its direct children stay visible
        $('#hierTree li').filter(function() { return $(this).children('ul').length && !$(this).children('.hier-node').hasClass('is-root'); })
            .toggleClass('is-collapsed', collapse)
            .children('.hier-node').find('.hier-caret i').attr('class', 'fa ' + (collapse ? 'fa-caret-right' : 'fa-caret-down'));
        $(this).data('collapsed', collapse).find('span').text(collapse ? 'Expand all' : 'Collapse all').end()
            .find('i').attr('class', 'fa ' + (collapse ? 'fa-expand' : 'fa-compress'));
    });

    $(document).ready(function() {
        collectAdminOptions();
        const preselectKind = currentAccountId ? $('#accountSelect option[value="' + currentAccountId + '"]').data('kind') : null;
        const startKind = preselectKind || (ADMIN_OPTIONS.manufacturer ? 'manufacturer' : (ADMIN_OPTIONS.dealer ? 'dealer' : 'manufacturer'));

        initAccountSelect2();
        setAdminKind(startKind, currentAccountId, true);

        $('.child-kind-tabs').on('click', 'button', function() {
            const kind = $(this).data('kind');
            if (kind !== currentAdminKind) {
                setAdminKind(kind, null, false);
            }
        });

        // Load permission dependencies
        loadPermissionDependencies();

        if (currentAccountId) {
            loadAccountPermissions(currentAccountId);
        }

        $(document).on('change', '.permission-checkbox', function() {
            setTimeout(updatePermissionCounts, 0);
        });

        $('#accountSelect').on('change', function() {
            updateAdminPickHint();
            currentAccountId = $(this).val();
            if (currentAccountId) {
                applyModuleVisibilityForAccount(resolveAccountType(currentAccountId));
                loadAccountPermissions(currentAccountId);
            } else {
                currentAccountType = null;
                $('#permissionsContainer').hide();
                $('#permissionSearch').val('');
                hideHierarchy();
            }
        });

        // Add change event listeners for permission checkboxes
        $(document).on('change', '.permission-checkbox', function() {
            const permId = $(this).val();
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                // If checking a child, also check its parent
                if (permissionDependencies[permId]) {
                    const parentId = permissionDependencies[permId];
                    $('.permission-checkbox[value="' + parentId + '"]').prop('checked', true);
                }
            } else {
                // If unchecking a parent, also uncheck all its children
                if (permissionDependents[permId]) {
                    permissionDependents[permId].forEach(childId => {
                        $('.permission-checkbox[value="' + childId + '"]').prop('checked', false);
                    });
                }
            }

            syncCreateEditPair(this, isChecked);
        });
    });

    function loadPermissionDependencies() {
        $.ajax({
            url: '/admin/permissions/dependencies/get',
            type: 'GET',
            success: function(response) {
                permissionDependencies = response.dependencies;
                permissionDependents = response.dependents;
                console.log('Permission dependencies loaded:', {
                    dependencies: permissionDependencies,
                    dependents: permissionDependents
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading permission dependencies:', error);
                // Continue even if dependencies fail to load
            }
        });
    }

    $('#permissionSearch').on('input', function() {
        filterPermissions($(this).val());
    });

    function filterPermissions(query) {
        query = query.toLowerCase().trim();
        let visibleSections = 0;

        $('.module-section').each(function() {
            const $section = $(this);
            const moduleTitle = $section.find('.module-title').text().toLowerCase().trim();
            const moduleMatches = !query || moduleTitle.indexOf(query) !== -1;
            let visibleRows = 0;

            $section.find('tbody tr').each(function() {
                const $row = $(this);
                const label = $row.find('td:first').text().toLowerCase().trim();
                const hiddenForAccount = currentAccountType && currentAccountType !== 'Reseller'
                    && MANUFACTURER_ONLY_PERMISSIONS.indexOf($row.find('.permission-checkbox').data('permission')) !== -1;
                const rowMatches = !hiddenForAccount && (!query || moduleMatches || label.indexOf(query) !== -1);
                $row.toggle(rowMatches);
                if (rowMatches) {
                    visibleRows++;
                }
            });

            const sectionVisible = visibleRows > 0;
            $section.toggle(sectionVisible);
            if (sectionVisible && currentAccountType === 'User' && $section.data('module') === 'account_management') {
                $section.hide();
            }
            if (sectionVisible && $section.is(':visible')) {
                visibleSections++;
            }
        });

        $('#permissionNoResults').toggleClass('active', query.length > 0 && visibleSections === 0);
    }

    function loadAccountPermissions(accountId, options) {
        options = options || {};
        if (!options.silent) {
            $('#loading').addClass('active');
        }

        $.ajax({
            url: '/admin/permissions/' + accountId + '?t=' + new Date().getTime(),
            type: 'GET',
            cache: false,
            success: function(response) {
                const accountType = resolveAccountType(accountId, response.user_type);
                applyModuleVisibilityForAccount(accountType);
                applyPermissionsState(response.permissions || []);

                // Hierarchy summary: only for Manufacturers, refreshed on a normal (non-silent) load.
                if (!options.silent) {
                    if (accountType === 'Reseller') {
                        loadHierarchy(accountId);
                    } else {
                        hideHierarchy();
                    }
                }

                if (!options.silent) {
                    $('#permissionSearch').val('');
                    $('#permissionNoResults').removeClass('active');
                    $('.module-section, .module-section tbody tr').show();
                    if (accountType === 'User') {
                        applyModuleVisibilityForAccount(accountType);
                    }
                }

                $('#loading').removeClass('active');
                $('#permissionsContainer').show();
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {xhr, status, error});
                let errorMsg = 'Error loading permissions';
                if (xhr.status === 404) {
                    errorMsg = 'Account not found';
                } else if (xhr.status === 403) {
                    errorMsg = 'Unauthorized access';
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                notifyPermissionMessage('error', errorMsg);
                $('#loading').removeClass('active');
            }
        });
    }

    function notifyPermissionMessage(type, message) {
        if (!message) return;
        if (type === 'success' && window.notifyGpsSuccess) {
            window.notifyGpsSuccess(message);
            return;
        }
        if (type === 'error' && window.notifyGpsError) {
            window.notifyGpsError(message);
            return;
        }
        if (type === 'warning' && window.notifyGpsWarning) {
            window.notifyGpsWarning(message);
            return;
        }
        var cssClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        var alertHtml = '<div class="col-sm-12 alert ' + cssClass + ' gps-js-inline-alert" role="alert">' + message + '</div>';
        var host = document.getElementById('alert_msg');
        if (host) {
            var existing = host.querySelector('.gps-js-inline-alert');
            if (existing) existing.remove();
            host.insertAdjacentHTML('afterbegin', alertHtml);
            host.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function savePermissions() {
        if (!currentAccountId) {
            notifyPermissionMessage('warning', 'Please select an account.');
            return;
        }

        clearTimeout(permissionSaveTimer);

        const runSave = function(permissions) {
            submitPermissions(permissions, { auto: false });
        };

        if (typeof confirmPermissionsBeforeSave !== 'function') {
            runSave(collectCheckedPermissions());
            return;
        }

        confirmPermissionsBeforeSave({
            previewUrl: '/admin/permissions/' + currentAccountId + '/preview',
            collectPermissions: collectCheckedPermissions,
            onConfirm: runSave
        });
    }

    function submitPermissions(permissions, options) {
        options = options || {};

        if (!currentAccountId) {
            return;
        }

        const saveBtn = $('button[onclick="savePermissions()"]');
        const originalText = saveBtn.html();
        if (!options.auto) {
            saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        }

        $.ajax({
            url: '/admin/permissions/' + currentAccountId + '/update',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: JSON.stringify({
                permissions: permissions
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.permissions) {
                    applyPermissionsState(response.permissions);
                }

                if (!options.auto) {
                    const added = response.debug ? (response.debug.added || 0) : 0;
                    const removed = response.debug ? (response.debug.removed || 0) : 0;
                    const message = response.message ||
                        ('Permissions saved successfully! (Added: ' + added + ', Removed: ' + removed + ')');
                    notifyPermissionMessage('success', message);
                    saveBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error saving permissions';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                notifyPermissionMessage('error', errorMsg);
                loadAccountPermissions(currentAccountId, { silent: true });
                if (!options.auto) {
                    saveBtn.prop('disabled', false).html(originalText);
                }
            }
        });
    }
</script>

@stop
