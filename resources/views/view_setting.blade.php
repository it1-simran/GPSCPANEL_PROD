<?php

use App\Helper\CommonHelper;
use App\DeviceCategory;

$configurations = json_decode($template_info['configurations'], true) ?: [];
$firmwareConfig = $configurations['firmware_id'] ?? null;
$firmwareValue = is_array($firmwareConfig) ? ($firmwareConfig['value'] ?? null) : $firmwareConfig;
if (($firmwareValue === null || $firmwareValue === '') && !empty($displayFirmwareId)) {
    $configurations['firmware_id'] = ['id' => 84, 'value' => $displayFirmwareId];
}
$getCanEnableByDeviceCategory = !empty($template_info['device_category_id'])
    ? DeviceCategory::select('is_can_protocol')->where('id', $template_info['device_category_id'])->first()
    : null;
// dd($configurations);
$canConfigurations = json_decode($template_info['can_configurations'], true);
// dd($configurations);
// dd($configurations['ping_interval']);
?>

@extends('layouts.apps')


@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-setting') }}">
@endpush
@section('content')
<section id="main-content" class="view-template-config-page">
    <section class="wrapper">
        <div class="protocol-breadcrumb-wrap">
            <nav class="protocol-breadcrumb" aria-label="Breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ url($url_type . '/view-template') }}" class="bc-item">Settings</a>
                <span class="bc-sep">›</span>
                <a href="{{ url($url_type . '/view-template') }}" class="bc-item">View Settings</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">View Configurations</span>
            </nav>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card vtc-main-card">
                    <div class="card-header header-custom">
                        <h4>
                            <i class="fa fa-bars" aria-hidden="true"></i>
                            Template and Configurations
                        </h4>
                    </div>
                    <div class="card-body body-custom">
                <div class="user-info no-accordion mb-4">
                    <div class="vc-section-title"><i class="fa fa-file-text-o"></i> Template Information</div>
                                        <div class='row bgx-configurations vtc-info-layout'>
                                            <div class="view-template-configuration vtc-view-block row">
                                                <div class='col-lg-6 col-md-12 vtc-meta-col'>
                                                    <dl class="vtc-dl">
                                                        <div class="vtc-dl-row">
                                                            <dt>Name</dt>
                                                            <dd>{{ $template_info['template_name'] }}</dd>
                                                        </div>
                                                        <div class="vtc-dl-row">
                                                            <dt>Device Category</dt>
                                                            <dd>{{ CommonHelper::getDeviceCategoryName($template_info['device_category_id']) }}</dd>
                                                        </div>
                                                        <div class="vtc-dl-row">
                                                            <dt>Created at</dt>
                                                            <dd class="vtc-date">{{ CommonHelper::getDateAsTimeZone($template_info['created_at']) ?? 'N/A' }}</dd>
                                                        </div>
                                                        <div class="vtc-dl-row">
                                                            <dt>Last Edit</dt>
                                                            <dd class="vtc-date">{{ CommonHelper::getDateAsTimeZone($template_info['updated_at']) ?? 'N/A' }}</dd>
                                                        </div>
                                                    </dl>
                                                </div>
                                                <div class='col-lg-6 col-md-12 vtc-badges-col'>
                                                    <div class="vtc-badge-stack">
                                                    <span class="vtc-pill vtc-pill-default {{ $template_info['default_template'] == 1 ? 'is-yes' : 'is-no' }}">
                                                        <span class="vtc-pill-glyph"><i class="fa {{ $template_info['default_template'] == 1 ? 'fa-check' : 'fa-times' }}"></i></span>
                                                        Default Template — {{ $template_info['default_template'] == 1 ? 'Yes' : 'No' }}
                                                    </span>

                                                    @if(Auth::user()->user_type == "Admin")
                                                    <span class="vtc-pill vtc-pill-ping {{ isset($configurations['ping_interval']['value']) ? 'is-on' : '' }}">
                                                        <span class="vtc-pill-glyph"><i class="fa fa-wifi"></i></span>
                                                        Ping interval — {{ isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : '0' }}
                                                    </span>

                                                    <span class="vtc-pill vtc-pill-edit {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'is-yes' : 'is-no' }}">
                                                        <span class="vtc-pill-glyph"><i class="fa fa-pencil"></i></span>
                                                        Editable — {{ isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 ? 'Yes' : 'No' }}
                                                    </span>
                                                    @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-template-configuration" style="display:none; padding: 25px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 15px;">
                                                <form class="validator form-horizontal " id="updateDeviceInfoConfiguration" method="post" action="{{ url($url_type . '/update-template-info-configurations/' . $template_info['id']) }}">
                                                    @method('PATCH')
                                                    @csrf
                                                    
                                                    <style>
                                                        .modern-form-group { display: flex; align-items: center; margin-bottom: 20px; }
                                                        .modern-label { font-weight: 600; color: #334155; text-align: right; padding-right: 20px; font-size: 14px; margin-bottom: 0; }
                                                        .modern-input { height: 42px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: none; color: #475569; font-size: 14px; padding: 0 15px; width: 100%; transition: all 0.2s; }
                                                        .modern-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none; }
                                                        .modern-checkbox { width: 22px; height: 22px; cursor: pointer; accent-color: #3b82f6; margin: 0; }
                                                        .modern-radio-group { display: flex; align-items: center; gap: 20px; height: 42px; }
                                                        .modern-radio-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: #475569; margin: 0; }
                                                        .modern-radio { width: 18px; height: 18px; cursor: pointer; accent-color: #3b82f6; margin: 0; }
                                                        .require { color: #ef4444; font-weight: bold; margin-left: 3px; }
                                                    </style>

                                                    @if(Auth::user()->user_type!='Support')
                                                    <div class="row modern-form-group">
                                                        <div class="col-lg-3">
                                                            <label class="modern-label">Mark as Default Template</label>
                                                        </div>
                                                        <div class="col-lg-6 d-flex align-items-center" style="height: 42px;">
                                                            <input type="checkbox" name="default_template" id="default_template" class="modern-checkbox" {{ $template_info['default_template'] == 1 ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    <div class="row modern-form-group">
                                                        <div class="col-lg-3">
                                                            <label class="modern-label">Name (optional)<span class="require">*</span></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <input class="modern-input" placeholder="Enter Device Name" id="name" type="text" name="template_name" value="{{ $template_info['template_name']}}">
                                                        </div>
                                                    </div>
                                                    
                                                    @if(Auth::user()->user_type=='Admin')
                                                    <div class="row modern-form-group">
                                                        <div class="col-lg-3">
                                                            <label class="modern-label">Ping interval <span class="require">*</span></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <input class="modern-input" placeholder="Enter Ping Interval" id="ping_interval" type="Number" name="configuration[ping_interval]" value="{{isset($configurations['ping_interval']['value']) ? $configurations['ping_interval']['value'] :''}}" onkeypress="return blockSpecialCharTransmission(event)" required />
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row modern-form-group">
                                                        <div class="col-lg-3">
                                                            <label class="modern-label">Template Edit Permission <span class="require">*</span></label>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="modern-radio-group">
                                                                <label class="modern-radio-label">
                                                                    <input class="modern-radio" {{ isset($configurations['is_editable']['value']) && $configurations['is_editable']['value'] == '1' ? 'checked' : '' }} type="radio" name="configuration[is_editable]" value="1" required>
                                                                    Enable
                                                                </label>
                                                                <label class="modern-radio-label">
                                                                    <input class="modern-radio" {{ isset($configurations['is_editable']['value']) && $configurations['is_editable']['value'] == '0' ? 'checked' : '' }} type="radio" name="configuration[is_editable]" value="0" required>
                                                                    Disable
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="row mt-4">
                                                        <div class="col-lg-9 offset-lg-3 d-flex gap-2">
                                                            <button type="submit" class="btn btn-primary" style="padding: 8px 24px; border-radius: 6px; font-weight: 500;">Save Changes</button>
                                                            <button type="button" class="btn btn-light cancel-template-info-btn" data-key="0" style="padding: 8px 24px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-weight: 500; margin-left: 10px;">Cancel</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            @if(Auth::user()->user_type != "Support")
                                            @if(isset($configurations['is_editable']['value']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                            <div class="row mt-3">
                                                <div class="col-lg-12 text-center">
                                                    <button type="button" class="btn btn-primary edit-template-btn" onclick="toggleEditTemplate()">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Edit
                                                    </button>
                                                </div>
                                            </div>
                                            @endif

                                            @endif
                                        </div>
                </div>

                <div class="user-info mb-4">
                    <div class="vc-section-title"><i class="fa fa-sliders"></i> Template Configurations</div>
                                        @empty($template_info['configurations'])
                                        <p class="col-md-12">No configurations found.</p>
                                        @else
                                        @php
                                        $categoryIds = explode(',', $template_info['device_category_id']);
                                        $deviceCategories = DeviceCategory::select('*')->whereIn('id', $categoryIds)->first();
                                        $input = ($deviceCategories && $deviceCategories->inputs)
                                            ? json_decode($deviceCategories->inputs, true)
                                            : [];
                                        if (!is_array($input)) {
                                            $input = [];
                                        }
                                        @endphp
                                        <div class="row d-flex">
                                            <div class="col-lg-12 mb-4">
                                                <div class="configuration-item vtc-config-list-box">
                                                    <h6><b>{{ CommonHelper::getDeviceCategoryName($template_info['device_category_id']) }}</b></h6>
                                                    <div class="bgx-configurations">
                                                        <div id="config">
                                                            <div class='row d-flex'>
                                                                <div class="col-lg-9">
                                                                    <p><strong>Firmware:</strong>
                                                                        {{ !empty($displayFirmwareId)
                                                                            ? CommonHelper::getFirmwareName($displayFirmwareId)
                                                                            : 'No firmware available' }}
                                                                    </p>
                                                                    @foreach ($input ?? [] as $field => $value)
                                                                    <p>
                                                                        <strong>{{ $value['key'] }}:</strong>
                                                                        @php
                                                                        $key = strtolower(str_replace(' ', '_', $value['key']));
                                                                        $configValue = $configurations[$key]['value'] ?? '';
                                                                        @endphp

                                                                        {{ isset($configurations[$key]) 
                                                                        ? (is_array($configValue) 
                                                                            ? json_encode($configValue) 
                                                                            : CommonHelper::getDeviceCategoryValue($value['key'], $configValue)) 
                                                                        : '' }}
                                                                    </p>


                                                                    @endforeach
                                                                    <!--<p><strong>Ping Interval:</strong>-->
                                                                    <!--{{ isset($configurations['ping_interval']) ? $configurations['ping_interval']['value'] : 0 }}-->
                                                                    <!--</p>-->
                                                                </div>
                                                                @if(Auth::user()->user_type != "Support")
                                                                @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                                                <div class="col-lg-3">
                                                                    <button type="button" class="btn btn-primary edit-btn" onclick="toggleEdit()"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                @endif

                                                                @endif
                                                            </div>
                                                        </div>
                                                        <!--{{$url_type}}-->
                                                        <div id="form" style="display: none;">
                                                            <form action="{{ url($url_type . '/update-template-configurations/' . $template_info['id']) }}" method="POST">
                                                                @csrf
                                                                <div class='row'>
                                                                    <div class='col-sm-12 bgx-form-fields'>
                                                                        <?php echo CommonHelper::getSettingConfigurationInput($template_info['device_category_id'], $configurations);
                                                                        ?>
                                                                    </div>
                                                                    <div class='col-sm-12 bg-margin-top text-right'>
                                                                        <button type="submit" class="btn btn-primary">Save</button>
                                                                        <button type="button" class="btn btn-secondary cancel-btn">Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endempty
                                    </div>
                                    @if($getCanEnableByDeviceCategory && $getCanEnableByDeviceCategory->is_can_protocol == 1)
                <div class="user-info mb-4">
                    <div class="vc-section-title"><i class="fa fa-microchip"></i> CAN Protocol Configurations</div>
                                        @empty($template_info['can_configurations'])

                                        @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')" style="background-color: #1e293b; border-color: #1e293b; color: white; padding: 8px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(30, 41, 59, 0.2); transition: all 0.2s;">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true" style="margin-right: 6px;"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif
                                        @else
                                        <?php echo CommonHelper::getCanProtocolTempConfigurationInput($template_info['device_category_id'], 0, $canConfigurations, $url_type, $template_info); ?>
                                        @if(Auth::user()->user_type != "Support")
                                        @if(isset($configurations['is_editable']) && $configurations['is_editable']['value'] == 1 || Auth::user()->user_type == "Admin")
                                        <div class="row mt-3">
                                            <div class="col-lg-12 text-center">
                                                <button type="button" class="btn btn-primary edit-config-btn" onclick="canConfigToggleEdit('')" style="background-color: #1e293b; border-color: #1e293b; color: white; padding: 8px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(30, 41, 59, 0.2); transition: all 0.2s;">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true" style="margin-right: 6px;"></i> Edit
                                                </button>
                                            </div>
                                        </div>
                                        @endif

                                        @endif
                                        @endempty
                </div>
                                    @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function toggleEditTemplate() {
        $('.edit-template-btn').hide();
        $('.view-template-configuration').hide();
        $('.form-template-configuration').show();
    }

    function canConfigToggleEdit(key) {
        $('.edit-config-btn').hide();
        $('#canConfig-0').hide();
        $('#canConfigForm-0').show();
    }


    // Add event listeners to all edit buttons
    function toggleEdit(key) {
        // Toggle between display and form
        $('#config').hide();
        $('#form').show();
    }

    $(document).ready(function() {
        $('.templates').each(function() {
            // Get the ID of each element
            var id = $(this).attr('id');
            // ids.push(id);
            $('#' + id).select2({
                'placeholder': 'Select and Search '
            })
        });
        $('.cancel-template-info-btn').click(function() {
            $('.edit-template-btn').show();
            $('.view-template-configuration').show();
            $('.form-template-configuration').hide();
        });
        $('.cancel-config-btn').click(function() {
            var key = $(this).data('key');
            $('.edit-config-btn').show();
            $('#canConfig-0').show();
            $('#canConfigForm-0').hide();
        });
        // Add event listeners to all cancel buttons
        $('.cancel-btn').click(function() {
            var key = $(this).data('key');

            // Toggle between display and form
            $('#config').show();
            $('#form').hide();
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
@endsection
