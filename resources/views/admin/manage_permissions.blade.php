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
                        @if ($message = Session::get('success'))
                            <div class="row">
                                <div class="col-sm-12 alert alert-success" role="alert">{{ $message }}</div>
                            </div>
                        @endif

                        @if ($message = Session::get('error'))
                            <div class="row">
                                <div class="col-sm-12 alert alert-danger" role="alert">{{ $message }}</div>
                            </div>
                        @endif

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
                            <div style="margin-bottom: 20px;">
                                <label style="font-weight: 600; display: block; margin-bottom: 10px;">Select Reseller:</label>
                                <select id="resellerSelect" class="reseller-select" style="width: 100%; max-width: 400px;">
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
                                                <tr>
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
    let currentResellerId = null;

    $(document).ready(function() {
        // Check if reseller_id is in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const resellerId = urlParams.get('reseller_id');
        if (resellerId) {
            currentResellerId = resellerId;
            loadResellerPermissions(resellerId);
        }
    });

    $('#resellerSelect').on('change', function() {
        currentResellerId = $(this).val();
        if (currentResellerId) {
            loadResellerPermissions(currentResellerId);
        } else {
            $('#permissionsContainer').hide();
        }
    });

    function loadResellerPermissions(resellerId) {
        $('#loading').addClass('active');

        $.ajax({
            url: '/admin/permissions/' + resellerId + '?t=' + new Date().getTime(),
            type: 'GET',
            cache: false,
            success: function(response) {
                console.log('Loaded permissions:', response);

                // Uncheck all checkboxes first
                $('.permission-checkbox').prop('checked', false);

                // Check the permissions this reseller has
                response.permissions.forEach(function(permId) {
                    $('.permission-checkbox[value="' + permId + '"]').prop('checked', true);
                });

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
                alert(errorMsg);
                $('#loading').removeClass('active');
            }
        });
    }

    function savePermissions() {
        if (!currentResellerId) {
            alert('Please select a reseller');
            return;
        }

        const permissions = [];
        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });

        console.log('Sending permissions:', permissions);
        console.log('Total permissions count:', permissions.length);
        console.log('Reseller ID:', currentResellerId);
        console.log('Sending to URL: /admin/permissions/' + currentResellerId + '/update');

        // Show loading state
        const saveBtn = $('button[onclick="savePermissions()"]');
        const originalText = saveBtn.html();
        saveBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '/admin/permissions/' + currentResellerId + '/update',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            data: {
                permissions: permissions
            },
            success: function(response) {
                console.log('Response received:', response);

                // Show success message
                showSuccessAlert('Permissions saved successfully! (' + response.debug.after_count + ' permissions now enabled)');
                saveBtn.prop('disabled', false).html(originalText);

                // Reload permissions to confirm changes
                setTimeout(function() {
                    loadResellerPermissions(currentResellerId);
                }, 1500);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });

                let errorMsg = 'Error saving permissions';
                if (xhr.status === 0) {
                    errorMsg = 'Network error - check your connection';
                } else if (xhr.status === 404) {
                    errorMsg = 'Route not found (404)';
                } else if (xhr.status === 403) {
                    errorMsg = 'Unauthorized access (403)';
                } else if (xhr.status === 422) {
                    errorMsg = 'Validation failed (422)';
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    errorMsg = 'Error: ' + xhr.responseText.substring(0, 100);
                }

                showErrorAlert(errorMsg);
                saveBtn.prop('disabled', false).html(originalText);
            }
        });
    }

    function showSuccessAlert(message) {
        const alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
            '<i class="fa fa-check-circle" style="margin-right: 8px;"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '</div>';

        if ($('#alert_container').length) {
            $('#alert_container').html(alertHtml);
        } else {
            $('#permissionsContainer').before('<div id="alert_container">' + alertHtml + '</div>');
        }

        // Auto-close after 5 seconds
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }

    function showErrorAlert(message) {
        const alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<i class="fa fa-exclamation-circle" style="margin-right: 8px;"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '</div>';

        if ($('#alert_container').length) {
            $('#alert_container').html(alertHtml);
        } else {
            $('#permissionsContainer').before('<div id="alert_container">' + alertHtml + '</div>');
        }
    }
</script>

@stop
