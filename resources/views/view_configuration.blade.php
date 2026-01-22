<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;
use App\Models\TimezoneModel;
$timeZones = TimezoneModel::all();

?>
@extends('layouts.apps')
@section('content')
<section id="main-content">
	<section class="wrapper">
		<div class="top-page-header">
			<div class="page-breadcrumb">
				<nav class="c_breadcrumbs">
					<ul>
						<li><a href="#">Account</a></li>
						<li><a href="/{{$url_type}}/view-user">View Accounts</a></li>

						<li class="active"><a href="#">View Configurations</a></li>
					</ul>
				</nav>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="container bgx-custom-page">
					<div class="row justify-content-center">
						<div class="col-md-12">
							<div class="card">
								<div class="card-header bg-primary text-white header-custom">
									<h4>User Profile and Configurations</h4>
								</div>
								<div class="card-body body-custom">
									{{-- Display User Information --}}
									<div class="user-info mb-4">
										<div class='col-lg-9'>
											<h5><b>User Information:</b></h5>
										</div>
										<div class='row  bgx-configurations view-user-configurations'>
											<div class='col-lg-5'>
												<div class="bgx-table-container">;
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
											<div class='col-lg-7' style='display: grid;justify-content: center;'>
												<div id="span2" class="btn btn-success" style='margin:3px 0px;'>Total Devices - {{ $deviceCount ?: 0  }}</div>
												<div id="span1" class="btn btn-primary" style='margin:3px 0px;'>Today Pings - {{$user['today_pings'] ?: 0 }}</div>
												<div id="span3" class="btn btn-info" style='margin:3px 0px;float:left;'>Total Pings - {{ $user['total_pings'] ?: 0 }}</div>
												@if(Auth::user()->user_type == 'Admin' && $user['user_type'] == 'Support' )
												<div id="span3" class="btn btn-warning" style='margin:3px 0px;float:left;'>Configuration Edit Permission - {{ $user['is_support_active'] ?: 0 }}</div>
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
											<h5><b>Child Accounts :</b></h5>
										</div>
										<div class='row view-user-configurations'>
											<div class='col-lg-12'>
												<div class="bgx-table-container" style="width: inherit;
                                            overflow: scroll;">
													<div class="table-container">
														<table class="fold-table view_user_table table table-bordered table-striped ">
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
										<h5><b>User Device Configurations:</b></h5>
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
										<h5><b>User CAN Protocol Configurations:</b></h5>
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