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
    .reseller-select-wrap { max-width: 400px; }
    .reseller-select-wrap .select2-container { width: 100% !important; }
</style>
@endpush

@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="vd-breadcrumb-wrap">
            <nav class="vd-breadcrumb">
                <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
                <a href="{{ url('admin') }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Manage Permissions</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title" style="margin-bottom: 10px;">
                        <div class="row bgx-title-container">
                            <div class="col-lg-12">
                                <h2><i class="fa fa-lock" style="color:#76CF1C; margin-right:10px;"></i>Manage Reseller Permissions</h2>
                            </div>
                        </div>
                    </div>

                    <div class="c_content">
                        <div class="row" id="alert_msg">
                            @include('partials.gps-inline-alerts')
                        </div>

                        @if(request()->query('reseller_id'))
                            {{-- Display selected reseller info when passed via URL --}}
                            @php
                                $selectedReseller = null;
                                foreach($resellers as $reseller) {
                                    if($reseller->id == request()->query('reseller_id')) {
                                        $selectedReseller = $reseller;
                                        break;
                                    }
                                }
                            @endphp
                            @if($selectedReseller)
                                <div style="margin-bottom: 20px; padding: 12px; background: #f0fdf4; border-left: 4px solid #76CF1C; border-radius: 4px;">
                                    <label style="font-weight: 600; color: #166534; display: block; margin-bottom: 5px;">Selected Reseller:</label>
                                    <div style="color: #166534; font-size: 14px;">{{ $selectedReseller->name }} ({{ $selectedReseller->email }})</div>
                                </div>
                            @endif
                        @else
                            {{-- Display dropdown when no reseller_id in URL --}}
                            <div class="reseller-select-wrap" style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 10px;">Select Reseller:</label>
                                <select id="resellerSelect" class="reseller-select" style="width: 100%;">
                                    <option value="">-- Choose a Reseller --</option>
                                    @foreach($resellers as $reseller)
                                        <option value="{{ $reseller->id }}">{{ $reseller->name }} ({{ $reseller->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div id="loading" class="loading">
                            <img src="/assets/icons/loader.gif" alt="Loading..." style="height: 50px;">
                        </div>

                        <div id="permissionsContainer" style="display:none;">
                            <div class="permission-toolbar">
                                <div class="permission-search-wrap">
                                    <i class="fa fa-search search-icon"></i>
                                    <input type="text" id="permissionSearch" placeholder="Search permissions or modules..." autocomplete="off">
                                </div>
                            </div>
                            <div id="permissionNoResults" class="permission-no-results">
                                <i class="fa fa-search" style="margin-right: 6px;"></i>No permissions match your search.
                            </div>
                            @foreach($modules as $module)
                                <div class="module-section">
                                    <div class="module-title">
                                        {{ ucwords(str_replace('_', ' ', $module)) }}
                                    </div>
                                    <table class="permission-matrix">
                                        <thead>
                                            <tr>
                                                <th style="text-align: left;">Permission</th>
                                                <th>Enabled</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($permissionsByModule[$module] as $permission)
                                                <tr class="permission-row" data-permission-id="{{ $permission->id }}">
                                                    <td style="text-align: left;">{{ $permission->label }}</td>
                                                    <td>
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
    let currentResellerId = null;
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

    function initResellerSelect2() {
        const $select = $('#resellerSelect');
        if (!$select.length || $select.hasClass('select2-hidden-accessible')) {
            return;
        }

        $select.select2({
            placeholder: '-- Choose a Reseller --',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0
        });
    }

    $(document).ready(function() {
        initResellerSelect2();

        // Load permission dependencies
        loadPermissionDependencies();

        // Check if reseller_id is in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const resellerId = urlParams.get('reseller_id');
        if (resellerId) {
            currentResellerId = resellerId;
            if ($('#resellerSelect').length) {
                $('#resellerSelect').select2('val', resellerId);
            }
            loadResellerPermissions(resellerId);
        }

        $('#resellerSelect').on('change', function() {
            currentResellerId = $(this).val();
            if (currentResellerId) {
                loadResellerPermissions(currentResellerId);
            } else {
                $('#permissionsContainer').hide();
                $('#permissionSearch').val('');
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
                const rowMatches = !query || moduleMatches || label.indexOf(query) !== -1;
                $row.toggle(rowMatches);
                if (rowMatches) {
                    visibleRows++;
                }
            });

            const sectionVisible = visibleRows > 0;
            $section.toggle(sectionVisible);
            if (sectionVisible) {
                visibleSections++;
            }
        });

        $('#permissionNoResults').toggleClass('active', query.length > 0 && visibleSections === 0);
    }

    function loadResellerPermissions(resellerId, options) {
        options = options || {};
        if (!options.silent) {
            $('#loading').addClass('active');
        }

        $.ajax({
            url: '/admin/permissions/' + resellerId + '?t=' + new Date().getTime(),
            type: 'GET',
            cache: false,
            success: function(response) {
                applyPermissionsState(response.permissions || []);

                if (!options.silent) {
                    $('#permissionSearch').val('');
                    $('#permissionNoResults').removeClass('active');
                    $('.module-section, .module-section tbody tr').show();
                }

                $('#loading').removeClass('active');
                $('#permissionsContainer').show();
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {xhr, status, error});
                let errorMsg = 'Error loading permissions';
                if (xhr.status === 404) {
                    errorMsg = 'Reseller not found';
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
        if (!currentResellerId) {
            notifyPermissionMessage('warning', 'Please select a reseller.');
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
            previewUrl: '/admin/permissions/' + currentResellerId + '/preview',
            collectPermissions: collectCheckedPermissions,
            onConfirm: runSave
        });
    }

    function submitPermissions(permissions, options) {
        options = options || {};

        if (!currentResellerId) {
            return;
        }

        const saveBtn = $('button[onclick="savePermissions()"]');
        const originalText = saveBtn.html();
        if (!options.auto) {
            saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        }

        $.ajax({
            url: '/admin/permissions/' + currentResellerId + '/update',
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
                loadResellerPermissions(currentResellerId, { silent: true });
                if (!options.auto) {
                    saveBtn.prop('disabled', false).html(originalText);
                }
            }
        });
    }
</script>

@stop
