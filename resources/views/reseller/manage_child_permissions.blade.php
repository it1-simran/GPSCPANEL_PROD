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

                        <div class="info-box">
                            <i class="fa fa-info-circle"></i> You can only assign permissions that you have access to. Child users cannot receive permissions beyond your level.
                        </div>

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
                                        <option value="{{ $childUser->id }}">{{ $childUser->name }} ({{ $childUser->email }})</option>
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
    let currentUserId = null;

    $(document).ready(function() {
        // Check if user_id is in query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id');
        if (userId) {
            currentUserId = userId;
            loadChildUserPermissions(userId);
        }
    });

    $('#childUserSelect').on('change', function() {
        currentUserId = $(this).val();
        if (currentUserId) {
            loadChildUserPermissions(currentUserId);
        } else {
            $('#permissionsContainer').hide();
        }
    });

    function loadChildUserPermissions(userId) {
        $('#loading').addClass('active');

        $.ajax({
            url: '/reseller/permissions/child/' + userId,
            type: 'GET',
            success: function(response) {
                // Uncheck all checkboxes first
                $('.permission-checkbox').prop('checked', false);

                // Check the permissions this child user has
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
                    errorMsg = 'Child user not found';
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
        if (!currentUserId) {
            alert('Please select a user');
            return;
        }

        const permissions = [];
        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });

        $.ajax({
            url: '/reseller/permissions/child/' + currentUserId + '/update',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                permissions: permissions
            },
            success: function(response) {
                alert('Permissions saved successfully!');
            },
            error: function(error) {
                alert('Error saving permissions: ' + error.responseJSON.error);
            }
        });
    }
</script>

@stop
