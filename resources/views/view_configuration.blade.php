<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;
use App\Models\TimezoneModel;
$timeZones = TimezoneModel::all();

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-configuration') }}">
<style>
	/* Child Accounts (View Configuration) */
	.ca-section-title .ca-section-title__text { display: inline-flex; flex-direction: column; vertical-align: middle; line-height: 1.25; }
	.ca-section-title small { font-size: 12.5px; font-weight: 500; color: #64748b; margin-top: 3px; letter-spacing: 0; text-transform: none; }
	.ca-panel { padding-top: 4px; }
	.ca-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
	.ca-summary__item { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
	.ca-summary__icon { width: 52px; height: 52px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
	.ca-summary__icon.is-green { background: #ecf8df; color: #5aa915; }
	.ca-summary__icon.is-blue { background: #e8edff; color: #3b4fd8; }
	.ca-summary__icon.is-amber { background: #e9f7e0; color: #4d8a12; }
	.ca-summary__val { display: block; font-size: 26px; font-weight: 800; color: #0f172a; line-height: 1.1; }
	.ca-summary__lbl { display: block; font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-top: 4px; }
	.ca-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
	.ca-filter { display: inline-flex; flex-wrap: wrap; gap: 10px; }
	.ca-filter__btn { border: 1px solid #e2e8f0; background: #f1f5f9; color: #334155; border-radius: 999px; padding: 9px 18px; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; transition: background .15s, color .15s, border-color .15s; }
	.ca-filter__btn span { background: #e2e8f0; color: #475569; border-radius: 999px; min-width: 24px; height: 24px; padding: 0 7px; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
	.ca-filter__btn:hover { border-color: #b7e58a; }
	.ca-filter__btn.active { background: #5cbf16; border-color: #5cbf16; color: #fff; box-shadow: 0 4px 12px rgba(92, 191, 22, .3); }
	.ca-filter__btn.active span { background: #fff; color: #4d8a12; }
	.ca-search { position: relative; flex: 0 1 380px; }
	.ca-search i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 15px; }
	.ca-search__input { width: 100%; height: 46px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0 14px 0 44px; font-size: 14px; background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; }
	.ca-search__input:focus { border-color: #76CF1C; box-shadow: 0 0 0 3px rgba(118, 207, 28, .15); }
	.ca-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
	.ca-table-wrap { overflow-x: auto; }
	.ca-table, .ca-subtable { width: 100%; border-collapse: collapse; margin: 0; }
	.ca-table > thead th { background: #f4f7fb; color: #334155; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; padding: 16px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; text-align: left; }
	.ca-table > .ca-group > .ca-row > td { padding: 14px 12px; border-bottom: 1px solid #eef2f7; vertical-align: middle; font-size: 14px; color: #1e293b; }
	.ca-table > .ca-group.is-alt > .ca-row > td { background: #f7fbf3; }
	.ca-table > .ca-group:hover > .ca-row > td { background: #f1f8ea; }
	.ca-table > .ca-group.is-open > .ca-row > td { background: #eef7e4; border-bottom-color: transparent; }
	.ca-col-num { width: 52px; text-align: center !important; }
	.ca-sr { color: #0f172a; font-weight: 700; }
	.ca-num { text-align: center !important; white-space: nowrap; }
	.ca-pings { text-align: left !important; white-space: nowrap; }
	.ca-mono { font-size: 14px !important; white-space: nowrap; letter-spacing: .01em; }
	.ca-account { display: flex; align-items: center; gap: 12px; min-width: 200px; }
	.ca-account__text { display: flex; flex-direction: column; min-width: 0; flex: 1; }
	.ca-account__text strong { color: #0f172a; font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
	.ca-account__text span { color: #64748b; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
	.ca-avatar { width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 17px; color: #fff; flex-shrink: 0; }
	.ca-avatar.is-small { width: 32px; height: 32px; font-size: 13px; }
	.ca-avatar.is-manufacturer { background: #4f46e5; }
	.ca-avatar.is-dealer { background: #ea7a12; }
	.ca-avatar.is-other { background: #64748b; }
	.ca-pill { display: inline-block; border-radius: 999px; padding: 5px 14px; font-size: 13px; font-weight: 700; white-space: nowrap; }
	.ca-pill.is-manufacturer { background: #eef2ff; color: #4338ca; }
	.ca-pill.is-dealer { background: #fef3c7; color: #a15c07; }
	.ca-pill.is-other { background: #f1f5f9; color: #475569; }
	.ca-count { display: block; font-weight: 800; color: #0f172a; font-size: 16px; }
	.ca-count.is-stock { color: #4d8a12; }
	.ca-sub { display: block; font-size: 12px; color: #94a3b8; margin-top: 2px; font-weight: 500; }
	.ca-muted { color: #cbd5e1; font-weight: 700; }
	.ca-table.is-dealer-view > thead .ca-col-stock,
	.ca-table.is-dealer-view > .ca-group > .ca-row > .ca-col-stock { display: none; }
	.ca-expand { align-self: flex-start; margin-top: 6px; border: 1px solid #e2e8f0; background: #fff; color: #334155; border-radius: 999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; white-space: nowrap; cursor: pointer; transition: all .15s; }
	.ca-expand i { font-size: 10px; margin-right: 4px; transition: transform .2s; }
	.ca-expand:hover:not([disabled]) { border-color: #76CF1C; color: #4d8a12; }
	.ca-expand[aria-expanded="true"] { background: #5cbf16; border-color: #5cbf16; color: #fff; }
	.ca-expand[aria-expanded="true"] i { transform: rotate(180deg); }
	.ca-expand[disabled] { opacity: .5; cursor: default; }
	.ca-detail > td { padding: 0 14px 16px 48px !important; background: #eef7e4; border-bottom: 1px solid #e2e8f0; }
	.ca-nested { background: #fff; border: 1px solid #e2e8f0; border-left: 3px solid #5cbf16; border-radius: 12px; overflow-x: auto; width: 0; min-width: 100%; /* never widens the outer table */ }
	.ca-nested__title { padding: 12px 16px; font-size: 13px; font-weight: 800; color: #334155; border-bottom: 1px solid #f1f5f9; }
	.ca-nested__title i { color: #5cbf16; margin-right: 6px; }
	.ca-subtable th { color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; padding: 10px 11px; text-align: left; white-space: nowrap; border-bottom: 1px solid #f1f5f9; background: #fafcff; }
	.ca-subtable td { padding: 10px 11px; font-size: 13.5px; color: #1e293b; border-bottom: 1px solid #f4f6f9; vertical-align: middle; }
	.ca-subtable tbody tr:last-child td { border-bottom: none; }
	.ca-subtable .ca-account { min-width: 180px; gap: 10px; }
	.ca-subtable .ca-account__text strong { font-size: 13.5px; }
	.ca-subtable .ca-account__text span { font-size: 12px; }
	.ca-subtable .ca-count { font-size: 14px; }
	.ca-empty { text-align: center; padding: 40px 16px !important; color: #94a3b8; font-size: 14px; }
	.ca-empty i { margin-right: 6px; }
	.ca-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 16px 22px; border-top: 1px solid #eef2f7; }
	.ca-footer__left { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; }
	.ca-pagesize { margin: 0; font-size: 14px; color: #475569; font-weight: 500; display: inline-flex; align-items: center; gap: 12px; padding-right: 22px; border-right: 1px solid #e2e8f0; }
	.ca-pagesize__select { height: 40px; min-width: 84px; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0 10px; font-size: 14px; background: #fff; }
	.ca-info { font-size: 14px; color: #475569; }
	.ca-info b { color: #0f172a; font-weight: 700; }
	.ca-pager { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
	.ca-page { min-width: 36px; height: 36px; padding: 0 10px; border: 1px solid #e2e8f0; background: #fff; color: #334155; border-radius: 8px; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
	.ca-page:hover:not([disabled]):not(.active) { border-color: #76CF1C; color: #4d8a12; }
	.ca-page.active { background: #5cbf16; border-color: #5cbf16; color: #fff; }
	.ca-page[disabled] { opacity: .5; cursor: not-allowed; }
	.ca-page-gap { color: #94a3b8; padding: 0 2px; }
	@media (max-width: 1200px) {
		.ca-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
	}
	@media (max-width: 768px) {
		.ca-summary { grid-template-columns: 1fr; }
		.ca-search { flex: 1 1 100%; }
		.ca-detail > td { padding-left: 16px !important; }
		.ca-pagesize { border-right: none; padding-right: 0; }
	}
</style>
@endpush
@section('content')

<section id="main-content" class="view-configuration-page">
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
									<div class="row" id="alert_msg">
										@include('partials.gps-inline-alerts')
									</div>
									{{-- Display User Information --}}
									<div class="user-info no-accordion mb-4">
										<div class="vc-section-title"><i class="fa fa-user"></i> User Information</div>
										<div class='row bgx-configurations view-user-configurations vuc-info-layout'>
											<div class='col-lg-7 col-md-12 vuc-meta-col'>
												<dl class="vuc-dl">
													<div class="vuc-dl-row">
														<dt>Name</dt>
														<dd>{{ $user['name'] ?: '--'  }}</dd>
													</div>
													<div class="vuc-dl-row">
														<dt>Mobile</dt>
														<dd>{{ $user['mobile'] ?: '--'  }}</dd>
													</div>
													<div class="vuc-dl-row">
														<dt>Email</dt>
														<dd>{{ $user['email'] ?: '--'  }}</dd>
													</div>
													<div class="vuc-dl-row">
														<dt>Timezone</dt>
														<dd>{{ isset($user['timezone']) && $user['timezone'] != '' ? CommonHelper::getTimezoneByName($user['timezone']) : 'N/A'  }}</dd>
													</div>
													<div class="vuc-dl-row">
														<dt>Account Type</dt>
														<dd>{{ $user['user_type'] == 'Reseller' ? 'Manufacturer' : ($user['user_type'] == 'User' ? 'Dealer' : '--') }}</dd>
													</div>
													<div class="vuc-dl-row">
														<dt>Created at</dt>
														<dd class="vuc-date">{{ CommonHelper::getDateAsTimeZone($user['created_at']) ?: '--'  }}</dd>
													</div>
													<div class="vuc-dl-row" style="grid-column: span 2;">
														<dt>Last Edit</dt>
														<dd class="vuc-date">{{ CommonHelper::getDateAsTimeZone($user['updated_at']) ?: '--'  }}</dd>
													</div>
												</dl>
											</div>
											<div class='col-lg-5 col-md-12 vuc-badges-col'>
												<div class="vuc-badge-stack">
													<span class="vuc-pill green">
														<span class="vuc-pill-glyph"><i class="fa fa-server"></i></span>
														Total Devices &mdash; {{ $deviceCount ?: 0 }}
													</span>
													<span class="vuc-pill navy">
														<span class="vuc-pill-glyph"><i class="fa fa-signal"></i></span>
														Today Pings &mdash; {{ $user['today_pings'] ?: 0 }}
													</span>
													<span class="vuc-pill blue">
														<span class="vuc-pill-glyph"><i class="fa fa-database"></i></span>
														Total Pings &mdash; {{ $user['total_pings'] ?: 0 }}
													</span>
													@if(Auth::user()->user_type == 'Admin' && $user['user_type'] == 'Support' )
													<span class="vuc-pill orange">
														<span class="vuc-pill-glyph"><i class="fa fa-shield"></i></span>
														Config Edit Permission &mdash; {{ $user['is_support_active'] ?: 0 }}
													</span>
													@endif
												</div>
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
									@php
										$typeLabel = function ($type) {
											return $type === 'Reseller' ? 'Manufacturer' : ($type === 'User' ? 'Dealer' : $type);
										};
										$typeClass = function ($type) {
											return $type === 'Reseller' ? 'is-manufacturer' : ($type === 'User' ? 'is-dealer' : 'is-other');
										};
										$initial = function ($name) {
											return mb_strtoupper(mb_substr(trim((string) $name) ?: '?', 0, 1));
										};
										$orNa = function ($value) {
											return ($value === null || $value === '') ? '—' : $value;
										};
										// In Stock (own Unassigned Devices) only applies to Manufacturer accounts
										$showInStock = $user['user_type'] != 'User';
										$caCols = $showInStock ? 8 : 7;
										$manufacturerCount = $descendants->where('user_type', 'Reseller')->count();
										$dealerCount = $descendants->where('user_type', 'User')->count();
										$hierarchyTotal = collect($descendants)->sum(function ($a) use ($deviceTotals) {
											return $deviceTotals[$a['id']]['total'] ?? 0;
										});
									@endphp
									<div class="user-info mb-4">
										<div class="vc-section-title ca-section-title"><i class="fa fa-users"></i> <span class="ca-section-title__text">Child Accounts<small>Manage and monitor child accounts and their devices.</small></span></div>
										<div class="ca-panel">
											<div class="ca-summary">
												<div class="ca-summary__item"><span class="ca-summary__icon is-green"><i class="fa fa-users"></i></span><div><span class="ca-summary__val">{{ count($descendants) }}</span><span class="ca-summary__lbl">Direct accounts</span></div></div>
												<div class="ca-summary__item"><span class="ca-summary__icon is-blue"><i class="fa fa-industry"></i></span><div><span class="ca-summary__val">{{ $manufacturerCount }}</span><span class="ca-summary__lbl">Manufacturers</span></div></div>
												<div class="ca-summary__item"><span class="ca-summary__icon is-amber"><i class="fa fa-briefcase"></i></span><div><span class="ca-summary__val">{{ $dealerCount }}</span><span class="ca-summary__lbl">Dealers</span></div></div>
												<div class="ca-summary__item"><span class="ca-summary__icon is-green"><i class="fa fa-sitemap"></i></span><div><span class="ca-summary__val">{{ $hierarchyTotal }}</span><span class="ca-summary__lbl">Devices in hierarchy</span></div></div>
											</div>

											<div class="ca-toolbar">
												<div class="ca-filter" role="group" aria-label="Filter child accounts by type">
													<button type="button" class="ca-filter__btn active" data-type="">All <span>{{ count($descendants) }}</span></button>
													<button type="button" class="ca-filter__btn" data-type="Reseller">Manufacturer <span>{{ $manufacturerCount }}</span></button>
													<button type="button" class="ca-filter__btn" data-type="User">Dealer <span>{{ $dealerCount }}</span></button>
												</div>
												<div class="ca-search">
													<i class="fa fa-search"></i>
													<input type="text" class="ca-search__input" placeholder="Search name, email or mobile" aria-label="Search child accounts">
												</div>
											</div>

											<div class="ca-card">
											<div class="ca-table-wrap">
												<table class="ca-table" id="childAccountsTable">
													<thead>
														<tr>
															<th class="ca-col-num">#</th>
															<th>Account</th>
															<th>Type</th>
															<th>Mobile</th>
															<th>Login Password</th>
															<th class="ca-num" title="Devices in this account and all its child accounts">Total Devices</th>
															@if($showInStock)
															<th class="ca-num ca-col-stock" title="Devices in the Manufacturer's own Unassigned Devices">In Stock</th>
															@endif
															<th class="ca-pings">Pings</th>
														</tr>
													</thead>
													@forelse($descendants as $account)
													@php
														$kids = $grandchildren[$account['id']] ?? collect();
														$searchText = mb_strtolower(implode(' ', array_merge(
															[$account['name'], $account['email'], $account['mobile']],
															$kids->flatMap(fn($k) => [$k->name, $k->email, $k->mobile])->all()
														)));
													@endphp
													<tbody class="ca-group" data-type="{{ $account['user_type'] }}" data-search="{{ $searchText }}" data-search-self="{{ mb_strtolower($account['name'] . ' ' . $account['email'] . ' ' . $account['mobile']) }}">
														<tr class="ca-row">
															<td class="ca-col-num ca-sr">{{ $loop->iteration }}</td>
															<td>
																<div class="ca-account">
																	<span class="ca-avatar {{ $typeClass($account['user_type']) }}">{{ $initial($account['name']) }}</span>
																	<div class="ca-account__text">
																		<strong>{{ $orNa($account['name']) }}</strong>
																		<span>{{ $orNa($account['email']) }}</span>
																	@if($account['user_type'] == 'Reseller')
																	<button type="button" class="ca-expand" aria-expanded="false" {{ $kids->isEmpty() ? 'disabled' : '' }}>
																		<i class="fa fa-chevron-down"></i> {{ $kids->count() }} {{ $kids->count() == 1 ? 'account' : 'accounts' }}
																	</button>
																	@endif
																	</div>
																</div>
															</td>
															<td><span class="ca-pill {{ $typeClass($account['user_type']) }}">{{ $typeLabel($account['user_type']) }}</span></td>
															<td class="ca-mono">{{ $orNa($account['mobile']) }}</td>
															<td>
																<div class="vc-pwd-field" id="pwd-field-{{ $account['id'] }}">
																	<div class="vc-pwd-field__dots">••••••••</div>
																	<div class="vc-pwd-field__value">{{ $orNa($account['showLoginPassword']) }}</div>
																	<button class="vc-pwd-field__toggle" type="button" onclick="document.getElementById('pwd-field-{{ $account['id'] }}').classList.toggle('vc-pwd-field--visible')">
																		<i class="fa fa-eye"></i>
																	</button>
																</div>
															</td>
															<td class="ca-num"><span class="ca-count">{{ $deviceTotals[$account['id']]['total'] ?? 0 }}</span></td>
															@if($showInStock)
															<td class="ca-num ca-col-stock">
																@if($account['user_type'] == 'Reseller')
																<span class="ca-count is-stock">{{ $deviceTotals[$account['id']]['in_stock'] ?? 0 }}</span>
																@else
																<span class="ca-muted">—</span>
																@endif
															</td>
															@endif
															<td class="ca-pings">
																<span class="ca-count">{{ $orNa($account['total_pings']) }}</span>
																<span class="ca-sub">Today {{ $orNa($account['today_pings']) }}</span>
															</td>
														</tr>
														@if($account['user_type'] == 'Reseller' && $kids->isNotEmpty())
														<tr class="ca-detail" hidden>
															<td colspan="{{ $caCols }}">
																<div class="ca-nested">
																	<div class="ca-nested__title"><i class="fa fa-level-up fa-rotate-90"></i> Accounts under {{ $account['name'] }}</div>
																	<table class="ca-subtable">
																		<thead>
																			<tr>
																				<th>Account</th>
																				<th>Type</th>
																				<th>Mobile</th>
																				<th>Login Password</th>
																				<th class="ca-num">Total Devices</th>
																				<th class="ca-num">In Stock</th>
																				<th class="ca-pings">Pings</th>
																			</tr>
																		</thead>
																		<tbody>
																			@foreach($kids as $kid)
																			<tr>
																				<td>
																					<div class="ca-account">
																						<span class="ca-avatar is-small {{ $typeClass($kid->user_type) }}">{{ $initial($kid->name) }}</span>
																						<div class="ca-account__text">
																							<strong>{{ $orNa($kid->name) }}</strong>
																							<span>{{ $orNa($kid->email) }}</span>
																						</div>
																					</div>
																				</td>
																				<td><span class="ca-pill {{ $typeClass($kid->user_type) }}">{{ $typeLabel($kid->user_type) }}</span></td>
																				<td class="ca-mono">{{ $orNa($kid->mobile) }}</td>
																				<td>
																					<div class="vc-pwd-field" id="pwd-field-{{ $kid->id }}">
																						<div class="vc-pwd-field__dots">••••••••</div>
																						<div class="vc-pwd-field__value">{{ $orNa($kid->showLoginPassword) }}</div>
																						<button class="vc-pwd-field__toggle" type="button" onclick="document.getElementById('pwd-field-{{ $kid->id }}').classList.toggle('vc-pwd-field--visible')">
																							<i class="fa fa-eye"></i>
																						</button>
																					</div>
																				</td>
																				<td class="ca-num"><span class="ca-count">{{ $deviceTotals[$kid->id]['total'] ?? 0 }}</span></td>
																				<td class="ca-num">
																					@if($kid->user_type == 'Reseller')
																					<span class="ca-count is-stock">{{ $deviceTotals[$kid->id]['in_stock'] ?? 0 }}</span>
																					@else
																					<span class="ca-muted">—</span>
																					@endif
																				</td>
																				<td class="ca-pings">
																					<span class="ca-count">{{ $orNa($kid->total_pings) }}</span>
																					<span class="ca-sub">Today {{ $orNa($kid->today_pings) }}</span>
																				</td>
																			</tr>
																			@endforeach
																		</tbody>
																	</table>
																</div>
															</td>
														</tr>
														@endif
													</tbody>
													@empty
													<tbody>
														<tr><td colspan="{{ $caCols }}" class="ca-empty"><i class="fa fa-users"></i> This account has no child accounts yet.</td></tr>
													</tbody>
													@endforelse
													<tbody class="ca-no-match" hidden>
														<tr><td colspan="{{ $caCols }}" class="ca-empty"><i class="fa fa-search"></i> No accounts match your filter.</td></tr>
													</tbody>
												</table>
											</div>

											@if(count($descendants) > 0)
											<div class="ca-footer">
												<div class="ca-footer__left">
													<label class="ca-pagesize">Show
														<select class="ca-pagesize__select">
															<option value="10">10</option>
															<option value="25">25</option>
															<option value="50">50</option>
															<option value="0">All</option>
														</select>
													</label>
													<span class="ca-info"></span>
												</div>
												<div class="ca-pager"></div>
											</div>
											@endif
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
											@endif
											@endforeach
										</div>
										@endempty
									</div>
									@endif
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
		// Child Accounts: type filter + search + pagination over account groups
		// (each tbody.ca-group = one direct child account and its nested accounts).
		var $ca = $('#childAccountsTable');
		if (!$ca.length) {
			return;
		}
		var $groups = $ca.children('tbody.ca-group');
		var $panel = $ca.closest('.ca-panel');
		var state = { type: '', query: '', page: 1, size: 10 };

		function render() {
			var matched = $groups.filter(function() {
				var $g = $(this);
				return (!state.type || $g.attr('data-type') === state.type) &&
					(!state.query || ($g.attr('data-search') || '').indexOf(state.query) !== -1);
			});
			var total = matched.length;
			var size = state.size > 0 ? state.size : Math.max(total, 1);
			var pages = Math.max(1, Math.ceil(total / size));
			state.page = Math.min(Math.max(state.page, 1), pages);
			var start = (state.page - 1) * size;

			$groups.prop('hidden', true);
			matched.slice(start, start + size).prop('hidden', false).each(function(i) {
				$(this).toggleClass('is-alt', i % 2 === 1); // zebra rows follow what is visible
			});
			// A search hit inside a Manufacturer's nested accounts opens that group
			if (state.query) {
				matched.each(function() {
					if (($(this).attr('data-search-self') || '').indexOf(state.query) === -1) {
						setOpen($(this), true);
					}
				});
			}
			$ca.children('tbody.ca-no-match').prop('hidden', total > 0 || !$groups.length);

			$panel.find('.ca-info').html(total
				? 'Showing <b>' + (start + 1) + '–' + Math.min(start + size, total) + '</b> of <b>' + total + '</b> account' + (total === 1 ? '' : 's')
				: 'No accounts');

			var html = '';
			if (total > 0) {
				html += '<button type="button" class="ca-page" data-page="' + (state.page - 1) + '"' + (state.page === 1 ? ' disabled' : '') + '><i class="fa fa-angle-left"></i></button>';
				for (var p = 1; p <= pages; p++) {
					if (p === 1 || p === pages || Math.abs(p - state.page) <= 1) {
						html += '<button type="button" class="ca-page' + (p === state.page ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
					} else if (p === state.page - 2 || p === state.page + 2) {
						html += '<span class="ca-page-gap">…</span>';
					}
				}
				html += '<button type="button" class="ca-page" data-page="' + (state.page + 1) + '"' + (state.page === pages ? ' disabled' : '') + '><i class="fa fa-angle-right"></i></button>';
			}
			$panel.find('.ca-pager').html(html);
		}

		$panel.on('click', '.ca-filter__btn', function() {
			$(this).addClass('active').siblings().removeClass('active');
			state.type = $(this).data('type') || '';
			// In Stock only applies to Manufacturers: hide the column when listing Dealers
			$ca.toggleClass('is-dealer-view', state.type === 'User');
			state.page = 1;
			render();
		});
		$panel.on('input', '.ca-search__input', function() {
			state.query = $.trim($(this).val()).toLowerCase();
			state.page = 1;
			render();
		});
		$panel.on('change', '.ca-pagesize__select', function() {
			state.size = parseInt($(this).val(), 10) || 0;
			state.page = 1;
			render();
		});
		$panel.on('click', '.ca-page[data-page]:not([disabled])', function() {
			state.page = parseInt($(this).data('page'), 10) || 1;
			render();
		});
		function setOpen($group, open) {
			$group.toggleClass('is-open', open).find('.ca-detail').prop('hidden', !open);
			$group.find('.ca-expand').attr('aria-expanded', open ? 'true' : 'false');
		}
		$ca.on('click', '.ca-expand:not([disabled])', function() {
			var $group = $(this).closest('tbody.ca-group');
			setOpen($group, !$group.hasClass('is-open'));
		});

		render();
	});

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

		// Dynamic Accordion Conversion
		$('.user-info:not(.no-accordion)').each(function(index) {
			var $info = $(this);
			var $title = $info.find('.vc-section-title').first();
			
			// Find the immediate wrapper of the title, if it's a col-lg-9 or col-lg-12
			var $titleWrapper = $title.parent('[class*="col-lg-"]').length ? $title.parent() : $title;
			
			// Wrap everything else in an accordion content div
			var $contents = $info.contents().filter(function() {
				// Don't wrap the title wrapper, and don't wrap empty text nodes
				if (this === $titleWrapper[0]) return false;
				if (this.nodeType === 3 && $.trim(this.nodeValue) === '') return false;
				return true;
			});
			$contents.wrapAll('<div class="acc-content" style="display: none;"></div>');
			
			// Style the title
			$title.addClass('vc-acc-header');
			
			// Handle clicks
			$title.css('cursor', 'pointer').on('click', function() {
				var $myContent = $info.find('.acc-content');
				if ($title.hasClass('acc-open')) {
					$myContent.slideUp(250);
					$title.removeClass('acc-open');
				} else {
					$('.acc-content').slideUp(250);
					$('.vc-acc-header').removeClass('acc-open');
					
					$myContent.slideDown(250);
					$title.addClass('acc-open');
				}
			});
		});

	});
</script>
