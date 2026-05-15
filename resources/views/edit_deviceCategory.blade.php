@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/edit-devicecategory.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/edit-devicecategory.css')) }}">
@endpush
@section('content')
@php
    $routePrefix = $url_type ?? 'admin';
@endphp

<section id="main-content" class="edit-device-category-page">
    <section class="wrapper">
        <div class="edc-breadcrumb-wrap">
            <nav class="edc-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
                <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <a href="{{ url($routePrefix . '/view-device-category') }}" class="bc-item">Device category</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Edit device category</span>
            </nav>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <div class="row bgx-title-container">
                            <div class="col-xs-12">
                                <h2 class="edc-panel-title"><i class="fa fa-pencil-square-o"></i> Edit device category</h2>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div><!--/.c_title-->
                    <div class="c_content">
                        <div class="row" id="alert_msg">
                            @if ($message = Session::get('success'))
                            <div class="col-sm-12 alert alert-success"
                                role="alert">
                                {{ $message }}
                            </div>
                            @endif
                            @if ($message = Session::get('error'))
                            <div class="col-sm-12 alert alert-danger"
                                role="alert">
                                {{ $message }}
                            </div>
                            @endif
                            @if ($errors->any())
                            <div class="col-sm-12 alert alert-danger"
                                role="alert">
                                {{ $errors->first() }}
                            </div>
                            @endif
                        </div>
                        <div class="edc-form-card">
                            <form class="validator form-horizontal"
                                id="editDeviceCategory"
                                name="editDeviceCategory" method="post"
                                action="{{ url($routePrefix . '/update-device-category') }}">
                                @csrf
                                <div class="col-lg-12">
                                    <h5 class="edc-section-title">Device configurations</h5>
                                </div>
                                <div class="form-group ">
                                    <label for="curl"
                                        class="control-label col-lg-3"><b>Is
                                            ESIM </b></label>
                                    <div class="col-lg-6">
                                        <input type="checkbox"
                                            class='default_template_checkbnox'
                                            name="is_esim" id="is_esim"
                                            {{$device_category->is_esim == 1 ?
                                        'checked' : ''}}>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="curl" class="control-label col-lg-3"><b>Is Certification Enable </b></label>
                                    <div class="col-lg-6">
                                        <input type="checkbox" class='default_template_checkbnox' name="is_certification_enable" id="is_certification_enable"  {{$device_category->is_certification_enable == 1 ?
                                        'checked' : ''}}>
                                    </div>
                                </div>
                                <div id="certificationFields" style="display: none;">
                                    <div class="form-group">
                                        <label for="arai_tac_no" class="control-label col-lg-3">ARAI/ TAC NO</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" id="arai_tac_no" type="text" name="arai_tac_no" value="{{ $device_category->arai_tac_no }}" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="arai_date" class="control-label col-lg-3">Date</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" id="arai_date" type="date" name="arai_date" value="{{ $device_category->arai_date }}" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="certification_model_name" class="control-label col-lg-3">Model Name</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" id="certification_model_name" type="text" name="certification_model_name" value="{{ $device_category->certification_model_name }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="curl" class="control-label col-lg-3"><b>Is Can Enable </b></label>
                                    <div class="col-lg-6">
                                        <input type="checkbox" class='default_template_checkbnox' name="is_can_enable" id="is_can_enable"
                                            {{$device_category->is_can_protocol == 1 ?
                                        'checked' : ''}}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="curl"
                                        class="control-label col-lg-3">Device
                                        Name <span
                                            class="require">*</span></label>
                                    <div class="col-lg-6">
                                        <input class="form-control"
                                            id="deviceName" type="text"
                                            placeholder="Enter Device Name"
                                            name="deviceName"
                                            value="{{$device_category->device_category_name}}"
                                            required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="deviceName"
                                        class="control-label col-lg-3">Select
                                        data fields <span
                                            class="require">*</span></label>
                                    <div class="col-lg-8">
                                        <select id="user-select"
                                            name="user_select[]"
                                            class="form-control"
                                            style="width: 100%;height:auto;"
                                            multiple></select>
                                    </div>
                                </div>
                                <div id="selectedDeviceInput"></div>
                                <div class="col-sm-12 text-right">
                                    <input type="hidden" name='device_id'
                                        id='device_id'
                                        value="{{$device_category->id}}" />
                                </div>

                                <div class="form-group edc-save-row">
                                    <div class="col-xs-12 edc-save-actions">
                                        <button class="btn btn-success btn-flat" type="submit"><i class="fa fa-save"></i> Save</button>
                                        <a href="{{ url($routePrefix . '/view-device-category') }}" class="btn btn-default btn-flat" style="margin-top: 10px;" >Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

@stop

@section('scripts')
<script>
    let selectedOptions = <?php echo json_encode($device_category->inputs);  ?>;
    var selectedIds = selectedOptions.map(item => item?.id);
    let data = <?php echo json_encode($dataFields); ?>;
    let selectedOrder = [];
    $(document).ready(function() {
        $('#user-select').select2({
            placeholder: 'Select data fields',
            allowClear: true
        });

        function toggleCertificationFields() {
            const enabled = $('#is_certification_enable').is(':checked');
            const $fields = $('#certificationFields');
            const $inputs = $fields.find('input');

            if (enabled) {
                $fields.show();
                $inputs.prop('disabled', false);
                $('#arai_tac_no').prop('required', true);
                $('#arai_date').prop('required', true);
                $('#certification_model_name').prop('required', true);
            } else {
                $fields.hide();
                $inputs.prop('required', false);
                $inputs.prop('disabled', true);
            }
        }

        $('#is_certification_enable').on('change', toggleCertificationFields);
        toggleCertificationFields();
        $('#user-select').on('change', function() {
            const selectedOptionsIndex = Array.from(this.selectedOptions).map(opt => Number(opt.value));
            selectedOptionsIndex.forEach(id => {
                if (!selectedOrder.includes(id)) {
                    selectedOrder.push(id);
                }
            });
            selectedOrder = selectedOrder.filter(id => selectedOptionsIndex.includes(id));
            console.log("selectedOrder ==>", selectedOrder);
            const selectedItems = selectedOrder.map(id => data.find(item => item.id === id));
            console.log("selectedItems ==>", selectedItems);
            $('#selectedDeviceInput').empty();
            selectedItems.forEach((item, index) => {
                console.log("item ==>", item);
                const validationConfig = item.validationConfig ? JSON.parse(item.validationConfig) : {};
                const selectOptions = validationConfig.selectOptions || [];
                const selectValues = validationConfig.selectValues || [];

                let defaultInput = '';
                let validationRule = '';
                switch (item.inputType) {
                    case 'select':
                        validationRule = `<div class="edc-validation-meta"><div><strong>Type</strong> · ${item.inputType}</div></div>`;
                        defaultInput = `
                        <select class="form-control" name="default[]" id="defaultValue${item.id}">
                            ${selectOptions.map((opt, i) => `
                            <option value="${selectValues[i] || opt}">${opt}</option>
                            `).join('')}
                        </select>
                        <input type="hidden" name="valConfig[${item.id}]" id="valConfig${item.id}" data-selectOptions="${selectOptions}" data-selectValues="${selectValues}" />
                        `;
                        break;

                    case 'multiselect':
                        validationRule = `<div class="edc-validation-meta"><div><strong>Type</strong> · ${item.inputType}</div></div>`;

                        const selectId = `defaultValue${item.id}`;
                        const selectedValues = validationConfig.selectValues || [];
                        console.log("validationConfig ==>", validationConfig);
                        defaultInput = `
                        <select class="form-control select2-multiselect" name="default[${index}][]" id="${selectId}" multiple style="width: 100%; height: auto;">
                            ${validationConfig.selectOptions.map((opt, i) => {
                                const val = validationConfig.selectValues[i];
                                const isSelected = selectedOptions[index]?.default.includes(val) ? 'selected' : '';
                                return `<option value="${val}" ${isSelected}>${opt}</option>`;
                            }).join('')}
                        </select>
                        <input type="hidden" 
                                name="valConfig[]" 
                                id="valConfig${item.id}" 
                                data-selectOptions='${JSON.stringify(selectOptions)}' 
                                data-selectValues='${JSON.stringify(selectValues)}' />
                        `;
                        setTimeout(() => {
                            $(`#defaultValue${item.id}`).select2({
                                placeholder: 'Select options',
                                allowClear: true,
                                width: '100%'
                            });
                        }, 0);
                        break;
                    case 'number': {
                        const nMin = validationConfig.numberInput?.min ?? '';
                        const nMax = validationConfig.numberInput?.max ?? '';
                        const rangeTxt = (nMin !== '' || nMax !== '') ? `${nMin} – ${nMax}` : '—';
                        validationRule = `<div class="edc-validation-meta"><div><strong>Type</strong> · number</div><div><strong>Range</strong> · ${rangeTxt}</div></div>`;
                        const numDef = selectedOptions[index]?.default != null && selectedOptions[index]?.default !== ''
                            ? String(selectedOptions[index].default).replace(/"/g, '&quot;')
                            : '';
                        defaultInput = `<input type="number" class="form-control no-space-allowed" name="default[]" id="defaultValue${item.id}" placeholder="Enter number" value="${numDef}" min="${nMin}" max="${nMax}" />`;
                        break;
                    }

                    case 'text':
                    case 'IP/URL':
                    case 'text_array':
                    default: {
                        let addClassTextArray = item.inputType === 'text_array' ? 'text-array-space' : '';
                        let addClassIpUrl = item.inputType === 'IP/URL' ? 'ip-url-space' : '';
                        const maxL = validationConfig.maxValueInput ?? '';
                        validationRule = `<div class="edc-validation-meta"><div><strong>Type</strong> · ${item.inputType}</div><div><strong>Max length</strong> · ${maxL !== '' ? maxL : '—'}</div></div>`;
                        const textDef = selectedOptions[index]?.default != null && selectedOptions[index]?.default !== ''
                            ? String(selectedOptions[index].default).replace(/"/g, '&quot;')
                            : '';
                        defaultInput = `<input type="text" class="form-control no-space-allowed ${addClassTextArray} ${addClassIpUrl}" name="default[]" id="defaultValue${item.id}" placeholder="Enter value" value="${textDef}" maxlength="${maxL}" />`;
                        break;
                    }
                }

                const html = `
              <div class="form-group edc-dynamic-field" id="device-input-${item.id}">
                <div class="edc-dynamic-field-inner">
                  <div class="edc-field-label">
                    <strong>Field ${index + 1}</strong> <span class="require">*</span>
                    <div class="edc-field-id">ID · ${item.id}</div>
                  </div>
                  <div class="edc-field-name">
                    <input class="form-control" type="text" disabled name="name[${item.id}]" value="${String(item.fieldName).replace(/"/g, '&quot;')}" required />
                    <input type="hidden" name="nameParameters[]" id="nameParameters${item.id}" value="${String(item.fieldName).replace(/"/g, '&quot;')}" />
                    <input type="hidden" name="idParameters[]" id="idParameters${item.id}" value="${item.id}" />
                  </div>
                  <div class="edc-field-default-label">Default value <span class="require">*</span></div>
                  <div class="edc-field-default-input">${defaultInput}</div>
                  <div class="edc-field-meta">${validationRule}</div>
                  <div class="edc-field-required">
                    <input type="hidden" name="inputFieldRequired[${index}]" value="off">
                    <label class="edc-check"><input type="checkbox" name="inputFieldRequired[${index}]" value="on" ${selectedOptions[index]?.requiredFieldInput ? 'checked' : ''}/> Required</label>
                  </div>
                  <div class="edc-field-actions">
                    <button type="button" class="btn btn-danger btn-sm remove-input" data-id="${item.id}" title="Remove field" style="margin-top: 1px;"><i class="fa fa-trash"></i></button>
                    <input type="hidden" name="inputType[]" value="${item.inputType}" required />
                  </div>
                </div>
              </div>
            `;

                $('#selectedDeviceInput').append(html);
            });
        });
        var $select = $('#user-select');
        data.forEach(function(item) {
            if (!selectedIds.includes(item.id.toString())) {
                var option = new Option(item.fieldName, item.id, false, false);
                $select.append(option);
            }
        });
        selectedOptions.forEach(function(item) {
            var option = new Option(item.key, item.id, true, true);
            $select.append(option);
        });

        $select.select2();
        $select.trigger('change');
    });
    $(document).ready(function() {
        $('#is_esim').change(function() {
            updateField();
        });

        function updateField() {
            var isChecked = $('#is_esim').is(':checked');
            var status = isChecked ? 'CCID' : '';
            if (!isCcidParameterExists()) {
                // addInputParameters();
                $('#nameParameters0').val(status);
            }
        }

        function isCcidParameterExists() {
            let exists = false;
            $('input[name^="nameParameters"]').each(function() {
                if ($(this).val() === 'CCID') {
                    exists = true;
                    return false; // Exit loop
                }
            });
            return exists;
        }
        // Remove dynamically added input field
        $(document).on("click", ".remove-input", function() {
            let deletedId = $(this).data('id');
            let ids = $('#user-select').val() || [];
            ids = ids.filter(id => id !== String(deletedId));
            $('#user-select').val(ids).trigger('change');
            $(this).closest(".form-group").remove();

            inputCount--;
        });
        $(document).on("click", ".remove-parameters-input", function() {
            $(this).closest(".form-group").remove();
            inputParameterCount--;
        });

        $(document).on("click", ".remove-option", function() {
            $(this).closest(".options-row").remove();
        });

        // Add option button functionality
        $(document).on("click", ".add-option", function() {
            alert("hello");
            var inputCount = $(this).data("inputcount");
            var optionsHtml = '<div class="options-row">' +
                '<div class="col-lg-7 d-flex">' +
                '<input class="form-control onlynumberdecimal col-lg-3" placeholder="Enter Option" type="text" name="selectOptions[' + inputCount + '][]" required/>' +
                '<div class="col-lg-1"></div>' +
                '<input class="form-control onlynumberdecimal col-lg-3" placeholder="Enter Value" type="text" name="selectValues[' + inputCount + '][]" required/>' +
                '</div>' +
                '<div class="col-lg-2 bgx-del-button-container">' +
                '<button type="button" class="btn btn-danger btn-sm remove-option"><i class="fa fa-times"></i></button>' +
                '</div>' +
                '</div>';
            $(this).closest(".form-group").find(".select-options-container").append(optionsHtml);
        });

        // Event listener for selecting inputType
        $(document).on("change", ".inputType", function() {
            var selectedType = $(this).val();
            var inputCount = $(this).attr('name').match(/\d+/)[0];
            var appendSelectOptions = $(this).closest(".form-group").find(".append-select-options");
            var inputOptions = $(this).closest(".form-group").find('.append-number-options');
            var maxvalOptions = $(this).closest(".form-group").find('.append-maxValue-options');
            var defaultVal = $(this).closest(".form-group").find(".default-val");


            var defaultValue = $('#defaultValue' + inputCount);
            defaultValue.attr('type', 'text');
            inputOptions.find('input').attr('required', false);
            appendSelectOptions.find('input').attr('required', false);
            if (selectedType === "select") {
                appendSelectOptions.show();
                appendSelectOptions.find('input').attr('required', true);
                inputOptions.hide();
                maxvalOptions.hide();
                defaultVal.removeClass("ip-url-space");
                defaultVal.removeClass("text-array-space");
            } else if (selectedType == 'number') {
                defaultValue.attr('type', 'number');
                appendSelectOptions.hide();
                inputOptions.show();
                maxvalOptions.hide();
                inputOptions.find('input').attr('required', true);
                defaultVal.removeClass("ip-url-space");
                defaultVal.removeClass("text-array-space");
            } else if (selectedType == 'IP/URL') {
                defaultVal.addClass("ip-url-space no-space-allowed");
                maxvalOptions.show();
            } else if (selectedType == 'text_array') {
                defaultVal.addClass("text-array-space no-space-allowed");
                maxvalOptions.show();
            } else {
                appendSelectOptions.hide();
                maxvalOptions.show();
                inputOptions.hide();
                defaultVal.removeClass("ip-url-space");
                defaultVal.removeClass("text-array-space");
            }
        });
        $(".no-space-allowed").on("keydown", function(event) {
            if (event.key === " ") {
                event.preventDefault(); // Prevent space
            }
        });
        $(document).on("keydown", ".ip-url-space", function(event) {
            const key = event.key;
            // console.log("Pressed key:", key);

            if (key === " ") {
                event.preventDefault();
                return false;
            }

            if (
                event.ctrlKey || event.metaKey ||
                key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" ||
                key === "Delete" || key === "Tab" || key === "Enter"
            ) {
                return;
            }

            const allowed = /^[a-zA-Z0-9.]$/;
            if (!allowed.test(key)) {
                event.preventDefault();
                return false;
            }
        });
        $(document).on("paste", ".ip-url-space", function(event) {
            const pastedData = event.originalEvent.clipboardData.getData('text');
            const allowed = /^[a-zA-Z0-9.,{}]*$/;

            if (!allowed.test(pastedData)) {
                event.preventDefault();
                alert("Pasted content contains invalid characters.");
            }
        });

        // Delegated event binding
        $(document).on("keydown", ".text-array-space", validateTextArrayInput);

        function validateTextArrayInput(event) {
            const key = event.key;
            console.log("Pressed key in text-array-space:", key);

            // Block space
            if (key === " ") {
                event.preventDefault();
                return;
            }

            const allowed = /^[a-zA-Z0-9.,{}]$/;

            // Allow control/navigation keys
            if (
                event.ctrlKey || event.metaKey ||
                key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" ||
                key === "Delete" || key === "Tab" || key === "Enter"
            ) {
                return;
            }

            // Block any key not in the allowed set
            if (!allowed.test(key)) {
                event.preventDefault();
            }
        }
        $(document).on("paste", ".text-array-space", function(event) {
            const pastedData = event.originalEvent.clipboardData.getData('text');
            const allowed = /^[a-zA-Z0-9.,{}]*$/;
            if (event.key === " ") {
                event.preventDefault();
                return;
            }
            if (!allowed.test(pastedData)) {
                event.preventDefault();
                alert("Pasted content contains invalid characters.");
            }
        });


        $(".onlyAlphanumberdecimal").on("keydown", function(event) {
            var key = event.key;
            // Check if the key pressed is space (keyCode 32) and there are no modifiers (e.g., Shift)
            if (event.keyCode === 32 && !event.shiftKey) {
                // Prevent default behavior (typing the space character)
                event.preventDefault();
            }
            var specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.\<>\/?£~]/;
            if (specialChars.test(key)) {
                event.preventDefault();
            }
        });
        $(".onlynumberdecimal").on("keydown", function(event) {
            var key = event.key;
            // Check if the key pressed is space (keyCode 32) and there are no modifiers (e.g., Shift)
            if (event.keyCode === 32 && !event.shiftKey) {
                // Prevent default behavior (typing the space character)
                event.preventDefault();
            }
            var specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.\<>\/?£~]/;
            if (specialChars.test(key)) {
                event.preventDefault();
            }
        });
    });
</script>
@endsection
