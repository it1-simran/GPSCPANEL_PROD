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
    .user-select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px; }
    .save-button { background-color: #76CF1C; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; }
    .save-button:hover { background-color: #5fb815; }
    .loading { display: none; text-align: center; padding: 20px; }
    .loading.active { display: block; }
    .info-box { background-color: #f0fdf4; border: 1px solid #86efac; padding: 12px; border-radius: 4px; margin-bottom: 20px; color: #166534; }
</style>
@endpush

@section('content')
<section id="main-content">
    <section class="wrapper">
        <div class="vd-breadcrumb-wrap">
            <nav class="vd-breadcrumb">
                <a href="{{ url('reseller') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
                <a href="{{ url('reseller') }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Manage Child Permissions</span>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title" style="margin-bottom: 10px;">
                        <div class="row bgx-title-container">
                            <div class="col-lg-12">
                                <h2><i class="fa fa-lock" style="color:#76CF1C; margin-right:10px;"></i>Manage Child User Permissions</h2>
                            </div>
                        </div>
                    </div>

                    <div class="c_content">
                        <div class="row" id="alert_msg">
                            @include('partials.gps-inline-alerts')
                        </div>

                        <div class="info-box">
                            <i class="fa fa-info-circle"></i> <strong>Permission Inheritance:</strong> Child users can only receive permissions that you (the parent) have assigned. You can only assign a subset of your own permissions.
                        </div>

                        @if(!empty($availablePermissions))
                            <div style="margin-bottom: 20px; padding: 10px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 4px; color: #92400e;">
                                <i class="fa fa-key" style="margin-right: 5px;"></i>
                                <strong>Your Available Permissions:</strong> {{ $availablePermissions->count() }} permission(s)
                            </div>
                        @endif

                        @if(request()->query('user_id'))
                            {{-- Display selected user info when passed via URL --}}
                            @php
                                $selectedUser = null;
                                foreach($childUsers as $childUser) {
                                    if($childUser->id == request()->query('user_id')) {
                                        $selectedUser = $childUser;
                                        break;
                                    }
                                }
                            @endphp
                            @if($selectedUser)
                                <div style="margin-bottom: 20px; padding: 12px; background: #f0fdf4; border-left: 4px solid #76CF1C; border-radius: 4px;">
                                    <label style="font-weight: 600; color: #166534; display: block; margin-bottom: 5px;">Selected Child User:</label>
                                    <div style="color: #166534; font-size: 14px;">{{ $selectedUser->name }} ({{ $selectedUser->email }})</div>
                                </div>
                            @endif
                        @else
                            {{-- Display dropdown when no user_id in URL --}}
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 10px;">Select Child User:</label>
                                <select id="childUserSelect" class="user-select" style="width: 100%; max-width: 400px;">
                                    <option value="">-- Choose a Child User --</option>
                                    @foreach($childUsers as $childUser)
                                        <option value="{{ $childUser->id }}" data-user-type="{{ $childUser->user_type }}">{{ $childUser->name }} ({{ $childUser->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(count($childUsers) == 0)
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> You don't have any child users yet. Create users first to manage their permissions.
                            </div>
                        @endif

                        <div id="loading" class="loading">
                            <img src="/assets/icons/loader.gif" alt="Loading..." style="height: 50px;">
                        </div>

                        <div id="permissionsContainer" style="display:none;">
                            @foreach($modules as $module)
                                <div class="module-section" data-module="{{ $module }}">
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
<script>
    let currentUserId = null;
    let currentChildUserType = null;
    let permissionDependencies = {}; // child_id => parent_id
    let permissionDependents = {}; // parent_id => [child_id, ...]
    const childUserTypes = @json($childUsers->pluck('user_type', 'id'));
    @if(!empty($selectedUser))
    childUserTypes[{{ $selectedUser->id }}] = @json($selectedUser->user_type);
    @endif

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

    function resolveChildUserType(userId, responseUserType) {
        if (responseUserType) {
            return responseUserType;
        }
        if ($('#childUserSelect').length) {
            const selectedType = $('#childUserSelect option:selected').data('user-type');
            if (selectedType) {
                return selectedType;
            }
        }
        return childUserTypes[userId] || childUserTypes[String(userId)] || null;
    }

    function applyModuleVisibilityForChildUser(userType) {
        currentChildUserType = userType;
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
    }

    $(document).ready(function() {
        // Load permission dependencies first, then child permissions if pre-selected
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');
        loadPermissionDependencies(function() {
            if (userId) {
                currentUserId = userId;
                loadChildUserPermissions(userId);
            }
        });

        // Add change event listeners for permission checkboxes
        $(document).on('change', '.permission-checkbox', function() {
            const permId = $(this).val();
            const isChecked = $(this).is(':checked');

            if (isChecked) {
                // If checking a child, also check its parent
                if (permissionDependencies && permissionDependencies[permId]) {
                    const parentId = permissionDependencies[permId];
                    $('.permission-checkbox[value="' + parentId + '"]').prop('checked', true);
                }
            } else {
                // If unchecking a parent, also uncheck all its children
                if (permissionDependents && permissionDependents[permId]) {
                    permissionDependents[permId].forEach(childId => {
                        $('.permission-checkbox[value="' + childId + '"]').prop('checked', false);
                    });
                }
            }

            syncCreateEditPair(this, isChecked);
        });
    });

    $('#childUserSelect').on('change', function() {
        currentUserId = $(this).val();
        if (currentUserId) {
            loadPermissionDependencies(function() {
                loadChildUserPermissions(currentUserId);
            });
        } else {
            $('#permissionsContainer').hide();
        }
    });

    function loadPermissionDependencies(callback) {
        $.ajax({
            url: '/reseller/permissions/dependencies/get',
            type: 'GET',
            success: function(response) {
                permissionDependencies = response.dependencies || {};
                permissionDependents = response.dependents || {};
                console.log('Permission dependencies loaded:', {
                    dependencies: permissionDependencies,
                    dependents: permissionDependents
                });
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading permission dependencies:', error);
                permissionDependencies = permissionDependencies || {};
                permissionDependents = permissionDependents || {};
                if (typeof callback === 'function') {
                    callback();
                }
            }
        });
    }

    function loadChildUserPermissions(userId) {
        $('#loading').addClass('active');

        $.ajax({
            url: '/reseller/permissions/child/' + userId + '?t=' + new Date().getTime(),
            type: 'GET',
            cache: false,
            success: function(response) {
                console.log('Loaded child permissions:', response);

                const childUserType = resolveChildUserType(userId, response.user_type);
                applyModuleVisibilityForChildUser(childUserType);

                // Server already filtered permissions to only assignable ones —
                // all rendered .permission-row elements ARE assignable.
                // Just need to set the checkbox state for permissions the child currently has.
                $('.permission-checkbox').prop('checked', false);

                if (response.permissions && response.permissions.length) {
                    let permissionsToCheck = [...response.permissions];

                    // Also check parent permissions of any checked children
                    response.permissions.forEach(function(permId) {
                        if (permissionDependencies && permissionDependencies[permId]) {
                            // This is a child permission, add its parent
                            let parentId = permissionDependencies[permId];
                            if (!permissionsToCheck.includes(parentId)) {
                                permissionsToCheck.push(parentId);
                            }
                        }
                    });

                    // Check all the permissions
                    permissionsToCheck.forEach(function(permId) {
                        $('.permission-checkbox[value="' + permId + '"]').prop('checked', true);
                    });
                    $('.permission-checkbox:checked').each(function() {
                        syncCreateEditPair(this, true);
                    });
                }

                $('#loading').removeClass('active');
                $('#permissionsContainer').show();
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {xhr, status, error});
                let errorMsg = 'Error loading permissions';
                if (xhr.status === 404) {
                    errorMsg = 'Child user not found';
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
        if (!currentUserId) {
            notifyPermissionMessage('warning', 'Please select a user.');
            return;
        }

        const permissions = [];
        $('.permission-checkbox:checked').each(function() {
            const $section = $(this).closest('.module-section');
            if (currentChildUserType === 'User' && $section.data('module') === 'account_management') {
                return;
            }
            permissions.push($(this).val());
        });

        console.log('=== SAVING PERMISSIONS ===');
        console.log('User ID:', currentUserId);
        console.log('Permissions to save:', permissions);

        // Show loading state
        const saveBtn = $('button[onclick="savePermissions()"]');
        const originalText = saveBtn.html();
        saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '/reseller/permissions/child/' + currentUserId + '/update',
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
                console.log('✓ SUCCESS - Response:', response);

                // Show success message
                const added = response.debug ? (response.debug.added || 0) : 0;
                const removed = response.debug ? (response.debug.removed || 0) : 0;
                const message = response.message ||
                    ('Permissions saved successfully! (Added: ' + added + ', Removed: ' + removed + ')');
                notifyPermissionMessage('success', message);
                saveBtn.prop('disabled', false).html(originalText);

                // Reload permissions to confirm changes
                setTimeout(function() {
                    loadChildUserPermissions(currentUserId);
                }, 1500);
            },
            error: function(xhr, status, error) {
                console.error('✗ ERROR - AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    response: xhr.responseJSON
                });

                let errorMsg = 'Error saving permissions';
                if (xhr.status === 0) {
                    errorMsg = 'Network error - check your connection';
                } else if (xhr.status === 404) {
                    errorMsg = 'Route not found (404)';
                } else if (xhr.status === 403) {
                    errorMsg = 'Unauthorized access (403)';
                } else if (xhr.status === 422) {
                    errorMsg = xhr.responseJSON && xhr.responseJSON.error ?
                        xhr.responseJSON.error : 'Validation failed (422)';
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMsg = 'Error: ' + xhr.responseText.substring(0, 200);
                }

                notifyPermissionMessage('error', errorMsg);
                saveBtn.prop('disabled', false).html(originalText);
            }
        });
    }
</script>

@stop
