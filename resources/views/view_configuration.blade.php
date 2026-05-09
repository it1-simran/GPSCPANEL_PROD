<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;
use App\Models\TimezoneModel;
$timeZones = TimezoneModel::all();

?>
@extends('layouts.apps')

@push('styles')
<style>
    /* Keep content below fixed header */
    #main-content { padding-top: 70px !important; margin-top: 0 !important; }
    #main-content .wrapper { padding-top: 0 !important; }

    /* ---- Modern Breadcrumb ---- */
    .vc-breadcrumb-bar {
        background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%);
        padding: 8px 14px;
        display: flex;
        align-items: center;
        margin-bottom: 22px;
        border: 1px solid rgba(118, 207, 28, 0.2);
        border-radius: 999px;
        box-shadow: 0 8px 20px rgba(2, 8, 23, 0.22);
        width: fit-content;
        max-width: 100%;
    }
    .vc-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .vc-breadcrumb li { display: flex; align-items: center; gap: 6px; }
    .vc-breadcrumb li a {
        color: rgba(226, 232, 240, 0.82);
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.2s;
        line-height: 1;
    }
    .vc-breadcrumb li a:hover { color: #ffffff; }
    .vc-breadcrumb li.active span {
        color: #76CF1C;
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1;
    }
    .vc-breadcrumb li .vc-crumb-muted {
        color: rgba(226, 232, 240, 0.82);
        font-size: 12.5px;
        font-weight: 700;
        line-height: 1;
    }
    .vc-breadcrumb .sep {
        color: rgba(148, 163, 184, 0.9);
        font-size: 18px;
        margin: 0 2px;
    }
    .vc-breadcrumb .home-icon {
        width: 28px;
        height: 28px;
        background: #76CF1C;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f172a;
        font-size: 12px;
        text-decoration: none;
        transition: background 0.2s, transform 0.2s;
        box-shadow: 0 0 0 4px rgba(118, 207, 28, 0.15);
    }
    .vc-breadcrumb .home-icon:hover { background: #86de2d; transform: translateY(-1px); }

    @media (max-width: 768px) {
        .vc-breadcrumb-bar {
            border-radius: 14px;
            width: 100%;
            overflow-x: auto;
            white-space: nowrap;
        }
        .vc-breadcrumb li a,
        .vc-breadcrumb li .vc-crumb-muted,
        .vc-breadcrumb li.active span {
            font-size: 12px;
        }
    }

    /* ---- Card Header ---- */
    .vc-card-header {
        background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%) !important;
        padding: 16px 24px !important;
        border-radius: 10px 10px 0 0 !important;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none !important;
    }
    .vc-card-header h4 {
        color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        margin: 0 !important;
        letter-spacing: 0.3px;
    }
    .vc-card-header::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 22px;
        background: linear-gradient(180deg, #76CF1C, #4fa812);
        border-radius: 3px;
        flex-shrink: 0;
    }

    /* ---- Card ---- */
    .vc-card {
        border: none !important;
        border-radius: 10px !important;
        box-shadow: 0 4px 24px rgba(0,0,0,0.09) !important;
        overflow: hidden;
        margin-bottom: 24px;
    }

    /* ---- Section titles ---- */
    .vc-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        padding: 10px 0 8px;
        border-bottom: 2px solid #76CF1C;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .vc-section-title i { color: #76CF1C; }

    /* ---- Info stat badges ---- */
    .vc-stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 9px;
        font-size: 12.5px;
        font-weight: 600;
        margin: 4px 0;
        width: 100%;
        justify-content: center;
    }
    .vc-stat-badge.green  { background: rgba(118,207,28,0.12); color: #3a8c0a; border: 1px solid rgba(118,207,28,0.3); }
    .vc-stat-badge.navy   { background: rgba(15,23,42,0.07);   color: #1d283e; border: 1px solid rgba(15,23,42,0.15);  }
    .vc-stat-badge.blue   { background: rgba(59,130,246,0.09); color: #2563eb; border: 1px solid rgba(59,130,246,0.25);}
    .vc-stat-badge.orange { background: rgba(249,115,22,0.09); color: #ea580c; border: 1px solid rgba(249,115,22,0.25);}

    /* ---- User info table ---- */
    .vc-info-table { width: 100%; }
    .vc-info-table .bgx-table-row {
        display: flex;
        gap: 12px;
        margin-bottom: 10px;
    }
    .vc-info-table .bgx-table-cell {
        flex: 1;
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        color: #374151;
        border: 1px solid #f0f0f0;
    }
    .vc-info-table .bgx-table-cell strong {
        color: #0f172a;
        font-weight: 600;
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 3px;
    }

    /* ---- Child Accounts Table ---- */
    .vc-table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid #f0f0f0; }
    .vc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .vc-table thead tr {
        background: linear-gradient(90deg, #0f172a, #1d283e);
    }
    .vc-table thead th {
        color: rgba(255,255,255,0.85) !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        padding: 12px 14px !important;
        border: none !important;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .vc-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
    .vc-table tbody tr:hover { background: rgba(118,207,28,0.04); }
    .vc-table tbody td { padding: 11px 14px; color: #374151; vertical-align: middle; border: none !important; }

    /* ---- Configuration item card ---- */
    .configuration-item {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 18px;
        height: 100%;
        transition: box-shadow 0.2s, border-color 0.2s;
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
    }
    .configuration-item:hover {
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
        border-color: rgba(118, 207, 28, 0.35);
    }
    .configuration-item h6 {
        color: #0f172a;
        font-size: 14px;
        font-weight: 800;
        margin: 0 0 14px;
        padding: 10px 12px;
        border-bottom: 0;
        border-radius: 8px;
        background: rgba(118, 207, 28, 0.12);
    }
    .configuration-item [id^="config-"] .row.d-flex,
    .configuration-item [id^="canConfig-"] .row.d-flex {
        display: flex;
        align-items: flex-start;
    }
    .configuration-item [id^="config-"] .col-lg-9,
    .configuration-item [id^="canConfig-"] .col-lg-9 {
        width: 88%;
    }
    .configuration-item [id^="config-"] .col-lg-3,
    .configuration-item [id^="canConfig-"] .col-lg-3 {
        width: 12%;
        text-align: right;
    }
    .configuration-item [id^="config-"] p,
    .configuration-item [id^="canConfig-"] p {
        font-size: 12.5px;
        color: #334155;
        margin: 0 0 7px;
        display: flex;
        align-items: baseline;
        gap: 6px;
        line-height: 1.4;
    }
    .configuration-item [id^="config-"] p strong,
    .configuration-item [id^="canConfig-"] p strong {
        color: #0f172a;
        min-width: 210px;
        font-weight: 700;
    }
    .configuration-item .edit-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #1d283e !important;
        border: none !important;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
    }

    /* ---- Edit mode layout fix for long configuration forms ---- */
    .configuration-item [id^="form-"],
    .configuration-item [id^="canConfigForm-"] {
        margin-top: 8px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        max-height: 520px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .configuration-item .bgx-form-fields .form-group {
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-bottom: 10px !important;
        clear: both;
    }
    .configuration-item .bgx-form-fields .form-group::after {
        content: "";
        display: block;
        clear: both;
    }
    .configuration-item .bgx-form-fields .form-group > .control-label {
        float: left !important;
        width: 42% !important;
        max-width: none !important;
        text-align: left !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #334155 !important;
        padding-top: 10px !important;
        padding-left: 0 !important;
        padding-right: 10px !important;
    }
    .configuration-item .bgx-form-fields .form-group > div[class*="col-"] {
        float: left !important;
        width: 58% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .configuration-item .bgx-form-fields .form-control,
    .configuration-item .bgx-form-fields select,
    .configuration-item .bgx-form-fields textarea {
        width: 100% !important;
        min-height: 38px !important;
        border: 1px solid #d5deea !important;
        border-radius: 7px !important;
        font-size: 12.5px !important;
        color: #334155 !important;
        box-shadow: none !important;
        background: #fff !important;
    }
    .configuration-item .bgx-form-fields .form-control:focus,
    .configuration-item .bgx-form-fields select:focus,
    .configuration-item .bgx-form-fields textarea:focus {
        border-color: #76CF1C !important;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.12) !important;
        outline: none !important;
    }
    .configuration-item .bg-margin-top {
        margin-top: 10px !important;
        border-top: 1px solid #eef2f7;
        padding-top: 12px;
    }

    /* ---- CAN edit form polish ---- */
    .configuration-item [id^="canConfigForm-"] .row {
        margin-left: -6px;
        margin-right: -6px;
    }
    .configuration-item [id^="canConfigForm-"] .row > [class*="col-"] {
        padding-left: 6px;
        padding-right: 6px;
    }
    .configuration-item [id^="canConfigForm-"] .form-group {
        margin-bottom: 10px !important;
    }
    .configuration-item [id^="canConfigForm-"] label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .configuration-item [id^="canConfigForm-"] .form-control,
    .configuration-item [id^="canConfigForm-"] select {
        min-height: 38px !important;
        border: 1px solid #d5deea !important;
        border-radius: 7px !important;
        background: #ffffff !important;
        font-size: 12.5px !important;
        color: #334155 !important;
    }
    .configuration-item [id^="canConfigForm-"] .form-control:focus,
    .configuration-item [id^="canConfigForm-"] select:focus {
        border-color: #76CF1C !important;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.12) !important;
        outline: none !important;
    }
    .configuration-item [id^="canConfigForm-"] .select2-container {
        width: 100% !important;
    }
    .configuration-item [id^="canConfigForm-"] .select2-container--default .select2-selection--multiple {
        min-height: 38px !important;
        border: 1px solid #d5deea !important;
        border-radius: 7px !important;
        padding: 3px 6px !important;
        background: #fff !important;
    }
    .configuration-item [id^="canConfigForm-"] .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #76CF1C !important;
        box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.12) !important;
    }
    .configuration-item [id^="canConfigForm-"] .bg-margin-top {
        margin-top: 12px !important;
        padding-top: 12px;
        border-top: 1px solid #e9eff7;
        text-align: right;
    }
    .configuration-item [id^="canConfigForm-"] .btn.btn-primary {
        min-width: 96px;
    }

    @media (max-width: 1199px) {
        .configuration-item [id^="config-"] .col-lg-9,
        .configuration-item [id^="config-"] .col-lg-3,
        .configuration-item [id^="canConfig-"] .col-lg-9,
        .configuration-item [id^="canConfig-"] .col-lg-3 {
            width: 100%;
            text-align: left;
        }
        .configuration-item .edit-btn {
            margin-top: 8px;
        }
        .configuration-item [id^="config-"] p,
        .configuration-item [id^="canConfig-"] p {
            display: block;
            margin-bottom: 10px;
        }
        .configuration-item [id^="config-"] p strong,
        .configuration-item [id^="canConfig-"] p strong {
            min-width: 0;
            display: inline;
        }
        .configuration-item .bgx-form-fields .form-group > .control-label,
        .configuration-item .bgx-form-fields .form-group > div[class*="col-"] {
            float: none !important;
            width: 100% !important;
        }
        .configuration-item .bgx-form-fields .form-group > .control-label {
            padding-right: 0 !important;
            padding-top: 0 !important;
            margin-bottom: 6px;
        }
    }

    /* Keep breadcrumb and card left alignment consistent */
    .vc-breadcrumb-bar,
    .vc-content-row {
        margin-left: 10px;
        margin-right: 10px;
    }
</style>
@endpush

@section('content')

<section id="main-content">
<section class="wrapper" style="padding-top:0;">

    {{-- Breadcrumb row above main card (page CSS loaded via layouts stack) --}}
    <div class="vc-breadcrumb-bar">
        <ol class="vc-breadcrumb">
            <li>
                <a href="/{{ $url_type }}" class="home-icon" title="Dashboard">
                    <i class="fa fa-home"></i>
                </a>
            </li>
            <li><span class="sep"><i class="fa fa-angle-right"></i></span></li>
            <li><span class="vc-crumb-muted">Account</span></li>
            <li><span class="sep"><i class="fa fa-angle-right"></i></span></li>
            <li><a href="/{{ $url_type }}/view-user">View Accounts</a></li>
            <li><span class="sep"><i class="fa fa-angle-right"></i></span></li>
            <li class="active"><span>View Configurations{{ !empty($user['name']) ? ' - ' . $user['name'] : '' }}</span></li>
        </ol>
    </div>

    <div class="row vc-content-row" style="margin: 0;">
        <div class="col-md-12">
            <div class="vc-card card">
                <div class="vc-card-header card-header">
                    <h4>User Profile and Configurations</h4>
								</div>
								<div class="card-body body-custom">
									{{-- Display User Information --}}
									<div class="user-info mb-4">
										<div class='col-lg-9'>
											<div class="vc-section-title"><i class="fa fa-user"></i> User Information</div>
										</div>
										<div class='row bgx-configurations view-user-configurations'>
											<div class='col-lg-5'>
												<div class="bgx-table-container vc-info-table">
													<div class="bgx-table-row">
														<div class="bgx-table-cell"><strong>Name:</strong> {{ $user['name'] ?: '--'  }}</div>
														<div class="bgx-table-cell"><strong>Mobile:</strong> {{ $user['mobile'] ?: '--'  }}</div>
													</div>
													<div class="bgx-table-row">
														<div class="bgx-table-cell"><strong>Email:</strong> {{ $user['email'] ?: '--'  }}</div>
														<div class="bgx-table-cell"><strong>TimeZone:</strong> {{ isset($user['timezone']) && $user['timezone'] != '' ? CommonHelper::getTimezoneByName($user['timezone']) : 'N/A'  }}</div>

													</div>
													<div class="bgx-table-row">
														<div class="bgx-table-cell">
															<strong>Account Type:</strong>
															{{ $user['user_type'] == 'Reseller' ? 'Manufacturer' : ($user['user_type'] == 'User' ? 'Dealer' : '--') }}
														</div>
														<div class="bgx-table-cell"><strong>Created at:</strong> {{ CommonHelper::getDateAsTimeZone($user['created_at']) ?: '--'  }}</div>
													</div>
													<div class="bgx-table-row">
														<div class="bgx-table-cell"><strong>Last Edit:</strong> {{ CommonHelper::getDateAsTimeZone($user['updated_at']) ?: '--'  }}</div>
													</div>
												</div>
											</div>
											<div class='col-lg-7' style='display:grid;justify-content:center;gap:4px;align-content:start;'>
												<div class="vc-stat-badge green"><i class="fa fa-server"></i> Total Devices &mdash; {{ $deviceCount ?: 0 }}</div>
												<div class="vc-stat-badge navy"><i class="fa fa-signal"></i> Today Pings &mdash; {{ $user['today_pings'] ?: 0 }}</div>
												<div class="vc-stat-badge blue"><i class="fa fa-database"></i> Total Pings &mdash; {{ $user['total_pings'] ?: 0 }}</div>
												@if(Auth::user()->user_type == 'Admin' && $user['user_type'] == 'Support' )
												<div class="vc-stat-badge orange"><i class="fa fa-shield"></i> Config Edit Permission &mdash; {{ $user['is_support_active'] ?: 0 }}</div>
												@endif
											</div>
											<div class="row mt-3">
												<div class="col-lg-12 text-center">
													<button type="button" class="btn btn-primary edit-user-btn" onclick="toggleEditUser()">
														<i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
													</button>
												</div>
											</div>
										</div>
										<div class='row  bgx-configurations edit-user-configurations' style="display:none;">
											<form class="validator form-horizontal userResellerEditForm" id="commentForm" method="post" action="/{{$url_type}}/update-user-info/{{$user['id']}}/{{$user['user_type']}}">
												@method('PATCH')
												@csrf
												<input type="hidden" class="current_utype" value="{{ $user['user_type'] }}">
												<div class="form-group ">
													<input type="hidden" class="userNewAccType" name="acc_type_changed">
													<div class="userAccCases"></div>
												</div>
												@if(Auth::user()->user_type !='User')
												<div class="form-group ">
													<label for="curl" class="control-label col-lg-3">Account Type</label>
													<div class="col-lg-6">
														<select data-prev="{{$user['user_type']}}" class="form-control userAccType" name="user_type">
															@if(Auth::user()->user_type == 'Admin' && $user['user_type'] == 'Support')
															<option <?php echo (($user['user_type'] == 'Support') ? 'selected' : '') ?> value="Support">Support</option>
															@else
															<option <?php echo (($user['user_type'] == 'Reseller') ? 'selected' : '') ?> value="Reseller">Manufacturer</option>
															<option <?php echo (($user['user_type'] == 'User') ? 'selected' : '') ?> value="User">Dealer</option>
															@endif
														</select>
													</div>
												</div>
												<div class="form-group ">
													<label for="cname" class="control-label col-lg-3">Name <span class="require">*</span></label>
													<div class="col-lg-6">
														<input class=" form-control" id="cname" name="name" type="text" value="{{ $user['name'] }}" placeholder="Enter Name" required />
													</div>
												</div>
												<div class="form-group ">
													<label for="cemail" class="control-label col-lg-3">E-Mail <span class="require">*</span></label>
													<div class="col-lg-6">
														<input class="form-control " id="cemail" type="email" name="email" value="{{ $user['email'] }}" placeholder="Enter E-Mail" required />
													</div>
												</div>
												<div class="form-group ">
													<label for="cemail" class="control-label col-lg-3">Mobile <span class="require">*</span></label>
													<div class="col-lg-6">
														<input class="form-control " id="cmobile" type="text" name="mobile" value="{{ $user['mobile'] }}" placeholder="Enter Mobile Number" maxlength="10" required />
													</div>
												</div>
												<div class="form-group">
													<label for="timezone" class="control-label col-lg-3">TimeZones <span class="require">*</span></label>
													<div class="col-lg-6">
														<select name="timezone" class="select2" id="timezone">
															<option value="">Please Select Time Zone</option>
															@foreach($timeZones as $timezone)
															@php
															$tzValue = $timezone->name . ' (' . $timezone->utc_offset . ')';
															@endphp
															<option value="{{ $timezone->name
															 }}"
																{{ isset($user) && $user['timezone'] == $timezone->name ? 'selected' : '' }}>
																{{ $tzValue }}
															</option>
															@endforeach
														</select>
													</div>
												</div>

												@endif
												@if(Auth::user()->user_type == 'Admin' && $user['user_type'] == 'Support' )
												<div class="form-group">
													<label for="is_support_active" class="control-label col-lg-3">Configuration Edit Permission</label>
													<div class="col-lg-6" style="position: absolute; left: 4%;">
														<input
															type="checkbox"
															class="form-control"
															name="is_support_active"
															style="height: 20px;"
															{{ $user['is_support_active'] == 1 ? 'checked' : '' }}>
													</div>
												</div>

												@endif
												<div class="col-sm-12 bg-margin-top text-right">
													<input type="hidden" id="device_id" name="device_id" value="{{$user['id']}}">
													<button class="btn btn-primary updateUserSubBtn" type="button">Update</button>
													<button type="button" class="btn btn-secondary cancel-user-info-btn" data-key="0">Cancel</button>
												</div>
											</form>
										</div>
									</div>
									@if(Auth::user()->user_type == 'Admin' && $user['user_type'] != 'Support')
									<div class="user-info mb-4">
										<div class='col-lg-12'>
												<div class="vc-section-title"><i class="fa fa-users"></i> Child Accounts</div>
										</div>
										<div class='row view-user-configurations'>
											<div class='col-lg-12'>
												<div class="vc-table-wrap">
													<div class="table-container">
														<table class="vc-table fold-table view_user_table">
															<thead>
																<tr>
																	<th></th>
																	<th>Account Type</th>
																	<th>Name</th>
																	<th>Mobile</th>
																	<th>Email</th>
																	<th>Login Password</th>
																	<th>Total Devices</th>
																	<th>Total Pings</th>
																	<th>Today Pings</th>
																</tr>
															</thead>
															<tbody>
																@if(count($descendants) > 0)
																@foreach($descendants as $account)
																<tr class="view">
																	<td class="{{ $account['user_type'] == 'Reseller' ? 'accordion-header' : '' }}">
																		@if($account['user_type'] == 'Reseller')
																		<div class="svg-container">
																			<svg fill="#000000" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
																				width="15px" height="15px" viewBox="0 0 45.402 45.402" xml:space="preserve">
																				<g>
																					<path d="M41.267,18.557H26.832V4.134C26.832,1.851,24.99,0,22.707,0c-2.283,0-4.124,1.851-4.124,4.135v14.432H4.141
                                                                                        c-2.283,0-4.139,1.851-4.138,4.135c-0.001,1.141,0.46,2.187,1.207,2.934c0.748,0.749,1.78,1.222,2.92,1.222h14.453V41.27
                                                                                        c0,1.142,0.453,2.176,1.201,2.922c0.748,0.748,1.777,1.211,2.919,1.211c2.282,0,4.129-1.851,4.129-4.133V26.857h14.435
                                                                                        c2.283,0,4.134-1.867,4.133-4.15C45.399,20.425,43.548,18.557,41.267,18.557z" />
																				</g>
																			</svg>
																		</div>
																		@endif
																	</td>
																	<td>{{ $account['user_type'] == 'Reseller ' ? 'ManuFacturer' :'Dealer'  }}</td>
																	<td>{{ $account['name'] }}</td>
																	<td>{{ $account['mobile'] }}</td>
																	<td>{{ $account['email'] }}</td>
																	<td>
																		<div id="showpassword-{{ $account['id'] }}" hidden>
																			{{ $account['showLoginPassword'] }}
																		</div>
																		<button id="hide-{{ $account['id'] }}" onclick="togglePasswordShow({{ $account['id'] }})">show</button>
																	</td>
																	<td>{{ $account['device_count'] }}</td>
																	<td>{{ $account['total_pings'] }}</td>
																	<td>{{ $account['today_pings'] }}</td>
																</tr>
																@if($account['user_type'] == 'Reseller')
																<tr class="fold">
																	<td colspan="9">
																		<div class="fold-content">
																			<table class="view_user_table table table-bordered table-striped ">
																				<thead>
																					<tr>
																						<th></th>
																						<th>Account Type</th>
																						<th>Name</th>
																						<th>Mobile</th>
																						<th>Email</th>
																						<th>Login Password</th>
																						<th>Total Devices</th>
																						<th>Total Pings</th>
																						<th>Today Pings</th>
																					</tr>
																				</thead>
																				<tbody>
																					@php
																					$j = 1;
																					$grandchilds = DB::table('writers')->where('created_by', $account['id'])->get();
																					@endphp
																					@foreach($grandchilds as $grandchild)
																					<tr class="accordion-content">
																						<td class="{{ $grandchild->user_type == 'Reseller' ? 'accordion-header' : '' }}">
																							@if($grandchild->user_type == 'Reseller')
																							<div class="svg-container">
																								<svg class="icon plus-icon" fill="#000000" version="1.1" xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" viewBox="0 0 24 24">
																									<g>
																										<path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
																									</g>
																								</svg>
																								<svg class="icon minus-icon" fill="#000000" version="1.1" xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" viewBox="0 0 24 24">
																									<g>
																										<path d="M19 13H5V11H19V13Z" />
																									</g>
																								</svg>
																							</div>
																							@else
																							{{ $j }}
																							@endif
																						</td>
																						<td>{{ $account['user_type'] == 'Reseller ' ? 'ManuFacturer' :'Dealer'  }}</td>
																						<td>{{ $grandchild->name }}</td>
																						<td>{{ $grandchild->mobile }}</td>
																						<td>{{ $grandchild->email }}</td>
																						<td>
																							<div id="showpassword-{{ $grandchild->id }}" hidden>
																								{{ $grandchild->showLoginPassword }}
																							</div>
																							<button id="hide-{{ $grandchild->id }}" onclick="togglePasswordShow({{ $grandchild->id }})">show</button>
																						</td>
																						<td></td>
																						<td>{{ $grandchild->total_pings }}</td>
																						<td>{{ $grandchild->today_pings }}</td>
																					</tr>
																					@php $j++; @endphp
																					@endforeach
																				</tbody>
																			</table>
																		</div>
																	</td>
																</tr>
																@endif
																@endforeach
																@else
																<tr>
																	<td colspan="9" class="text-center">No Account found</td>
																</tr>
																@endif
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
									</div>
									@endif
									<div class="user-info ">
										<div class="vc-section-title"><i class="fa fa-cog"></i> User Device Configurations</div>
										@empty($user['configurations'])
										<p class="col-md-12">No configurations found.</p>
										@else
										@php
										$categoryIds = explode(',',$user['device_category_id']);
										$configurations = json_decode($user['configurations'], true);
										$categoryConfiguration = DeviceCategory::select('*')->whereIn('id', $categoryIds)->get();
										@endphp
										<div class="row">
											@foreach ($categoryConfiguration as $key => $config)
											@php $inputs = json_decode($config->inputs,true)@endphp
											<div class="col-lg-6 mb-4">
												<div class="configuration-item">
													<h6><b>{{ $config->device_category_name}}</b></h6>
													<div class="bgx-configurations">
														<div id="config-{{ $key }}">
															<div class='row d-flex'>
																<div class="col-lg-9">
																	@foreach ($inputs as $field => $value)
																	@php
																	$configKey = strtolower(str_replace(' ', '_', $value['key']));
																	$rawValue = $configurations[$key][$configKey]['value'] ?? '';
																	$processedValue = is_array($rawValue) ? implode(', ', $rawValue) : $rawValue;
																	@endphp

																	<p><strong>{{ $value['key'] }}:</strong>
																		{{ isset($configurations[$key][$configKey]) ? CommonHelper::getDeviceCategoryValue($value['key'], $processedValue) : '' }}
																	</p>
																	@endforeach
																	@if($user['user_type'] == 'Admin')
																	<p><strong>Ping Interval:</strong>
																		{{ isset($configurations[$key]['ping_interval']) ? $configurations[$key]['ping_interval']['value'] : 0 }}
																	</p>

																	<p><strong>Is Editable:</strong>
																		{{ isset($configurations[$key]['is_editable']) ? $configurations[$key]['is_editable']['value'] : 0 }}
																	</p>
																	@endif
																</div>
																<div class="col-lg-3">
																	<button type="button" class="btn btn-primary edit-btn" onclick="toggleEdit('{{ $key }}')"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
																	</button>
																</div>
															</div>
														</div>
														<div id="form-{{ $key }}" style="display: none;">
															<form action="/{{$url_type}}/update-configurations/{{$user['id']}}" method="POST">
																@csrf
																<div class='row'>
																	<div class='col-sm-12 bgx-form-fields'>
																		<?php echo CommonHelper::getConfigurationInput($config['id'], $key, $configurations[$key]) ?>
																	</div>
																	<div class='col-sm-12 bg-margin-top text-right'>
																		<button type="submit" class="btn btn-primary">Update</button>
																		<button type="button" class="btn btn-secondary cancel-btn" data-key="{{ $key }}">Cancel</button>
																	</div>
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
											@endforeach
										</div>
										@endempty
									</div>
									@php
									$categoryIds = explode(',',$user['device_category_id']);
									$configurations = json_decode($user['can_configurations'], true);
									$categoryConfiguration = DeviceCategory::select('*')->whereIn('id', $categoryIds)->where('is_can_protocol', 1)->get();
									@endphp
									@if(count($categoryConfiguration) > 0)
									<div class="user-info padding-bottom-35">
										<div class="vc-section-title"><i class="fa fa-sitemap"></i> User CAN Protocol Configurations</div>
										@empty($user['can_configurations'])
										<p class="col-md-12 margin-bottom-11 text-center">No configurations found.</p>
										@else
										<div class="row">
											@foreach ($categoryConfiguration as $key => $config)
											@php $inputs = json_decode($config->inputs,true)@endphp
											@if (isset($configurations[$config->id]))
											<div class="col-lg-6 mb-4">
												<div class="configuration-item">
													<h6><b>{{ $config->device_category_name}}</b></h6>
													<div class="bgx-configurations">
														<div id="canConfig-{{$config->id }}">
															<div class='row d-flex'>
																<div class="col-lg-9">
																	@php
																	$config1 = is_string($configurations[$config->id]) ? json_decode($configurations[$config->id], true) : $configurations[$config->id];
																	@endphp
																	@foreach ($config1 as $key1 => $value)
																	<p class="card-text" style="white-space: normal; word-break: break-word;">
																		<strong>{!! CommonHelper::getDataFieldName($value['id']) !!}:</strong>
																		{{ is_array($value['value'] ?? '') ? implode(', ', $value['value']) : (CommonHelper::getFieldValueById($value['id'], $value['value']) ?? '') }}
																	</p>
																	@endforeach
																</div>
																<div class="col-lg-3">
																	<button type="button" class="btn btn-primary edit-btn" onclick="toggleCanEdit('{{ $config->id }}')">
																		<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
																	</button>
																</div>
															</div>
														</div>
														<div id="canConfigForm-{{ $config->id }}" style="display: none;">
															<form action="/{{$url_type}}/update-canprotocolWriter-configurations/{{ $user['id'] }}" method="POST">
																@csrf
																<div class="row">
																	<div class="col-sm-12 bgx-form-fields">
																		<?php //echo CommonHelper::getCanProtocolWriterConfigurationInput(json_decode($configurations[$key], true)); 
																		?>
																		{!! CommonHelper::getCanProtocolWriterConfigurationInput(
																		$config->id,
																		is_string($configurations[$config->id]) ? json_decode($configurations[$config->id], true) : $configurations[$config->id]
																		) !!}

																	</div>
																</div>
																<!-- <div class="col-sm-12 bg-margin-top text-right">
																		<button type="submit" class="btn btn-primary">Update</button>
																		<button type="button" class="btn btn-secondary cancel-btn" data-key="{{ $key }}">Cancel</button>
																	</div> -->
															</form>
														</div>
													</div>
												</div>
											</div>
										</div>
										@endif
										@endforeach
									</div>
									@endif
									@endempty
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
		</div>
	</section>
</section>
@include('modals.userEditDelOptions')
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
	$(document).ready(function() {
		$("#childDataTable").DataTable({
			paging: true,
			searching: true,
			ordering: true,
			lengthChange: true,
			pageLength: 10,
			scrollX: true,
			scrollY: '500px',
			"aLengthMenu": [
				[25, 50, 100, 500, -1],
				[25, 50, 100, 500, "All"]
			],
			"iDisplayLength": 25
		});
	})

	function togglePasswordShow(id) {
		var passwordDiv = document.getElementById('showpassword-' + id);
		var button = document.getElementById('hide-' + id);

		if (passwordDiv.style.display === 'none') {
			passwordDiv.style.display = 'block';
			button.textContent = 'hide';
		} else {
			passwordDiv.style.display = 'none';
			button.textContent = 'show';
		}
	}

	function toggleEdit(key) {
		$('#config-' + key).hide();
		$('#form-' + key).show();
	}

	function toggleCanEdit(key) {
		$('#canConfig-' + key).hide();
		$('#canConfigForm-' + key).show()
	}

	function toggleEditUser() {
		$(".view-user-configurations").hide();
		$(".edit-user-configurations").show();
	}
	$(document).ready(function() {
		$(function() {
			$(".fold-table").on("click", ".accordion-header", function() {
				var $parentRow = $(this).closest("tr");
				var $foldRow = $parentRow.next(".fold");
				$parentRow.toggleClass("open");
				$foldRow.toggleClass("open");
				$parentRow.find('.plus-icon').toggle(!$parentRow.hasClass('open'));
				$parentRow.find('.minus-icon').toggle($parentRow.hasClass('open'));
			});
		});
		$('.cancel-can-btn').click(function() {
			var key = $(this).data('key');
			$('#canConfig-' + key).show();
			$('#canConfigForm-' + key).hide();
		});
		$(".cancel-user-info-btn").click(function() {
			$(".view-user-configurations").show();
			$(".edit-user-configurations").hide();
		});
		$('.templates').each(function() {
			var id = $(this).attr('id');
			$('#' + id).select2({
				'placeholder': 'Select and Search '
			})
		});
		$('.cancel-btn').click(function() {
			var key = $(this).data('key');
			$('#config-' + key).show();
			$('#form-' + key).hide();
		});
	});
</script>
