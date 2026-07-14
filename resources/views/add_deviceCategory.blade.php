@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('add-devicecategory') }}">
@endpush
@section('content')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS (page uses Select2 v4 API; layout already loads jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<section id="main-content" class="add-device-category-page">
    <section class="wrapper">
        <div class="dc-breadcrumb-wrap">
            <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
                <a href="{{ url($url_type) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
                <a href="{{ url($url_type) }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <a href="{{ url($url_type . '/view-device-category') }}" class="bc-item">Device category</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Add device category</span>
            </nav>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2 class="dc-panel-title"><i class="fa fa-plus-circle"></i> Add device category</h2>
                        <div class="clearfix"></div>
                    </div><!--/.c_title-->
                    <div class="c_content">
                        <div class="row" id="alert_msg">
                            @include('partials.gps-inline-alerts')
                        </div>
                        <form class="validator form-horizontal" id="addDeviceCategory" name="addDeviceCategory" method="post" action="#" onsubmit="return false;">
                            @csrf
                            <div class="form-step form-step-active">
                                <div class="adc-form-card">
                                    <h5 class="adc-section-title"><i class="fa fa-cog"></i> Device Configurations</h5>
                                    <div class="adc-step1-device-config">
                                        <div class="adc-options-grid">
                                            <label class="adc-option-card" for="is_esim">
                                                <input type="checkbox" class="default_template_checkbnox adc-checkbox-input" name="is_esim" id="is_esim">
                                                <span class="adc-option-text">Is ESIM</span>
                                            </label>
                                            <label class="adc-option-card" for="is_certification_enable">
                                                <input type="checkbox" class="default_template_checkbnox adc-checkbox-input" name="is_certification_enable" id="is_certification_enable">
                                                <span class="adc-option-text">Is Certification Enable</span>
                                            </label>
                                            <label class="adc-option-card" for="is_can_enable">
                                                <input type="checkbox" class="default_template_checkbnox adc-checkbox-input" name="is_can_enable" id="is_can_enable">
                                                <span class="adc-option-text">Is Can Enable</span>
                                            </label>
                                        </div>
                                        <div id="certificationFields" class="adc-cert-panel" style="display: none;">
                                            <p class="adc-cert-panel-title">Certification details</p>
                                            <div class="adc-cert-grid">
                                                <div class="adc-field-block">
                                                    <label for="arai_tac_no" class="adc-field-label">ARAI / TAC NO</label>
                                                    <input class="adc-input form-control" id="arai_tac_no" type="text" name="arai_tac_no" />
                                                </div>
                                                <div class="adc-field-block">
                                                    <label for="arai_date" class="adc-field-label">Date</label>
                                                    <input class="adc-input form-control" id="arai_date" type="date" name="arai_date" />
                                                </div>
                                                <div class="adc-field-block adc-field-block--full">
                                                    <label for="certification_model_name" class="adc-field-label">Model Name</label>
                                                    <input class="adc-input form-control" id="certification_model_name" type="text" name="certification_model_name" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="adc-fields-stack">
                                            <div class="adc-field-block adc-field-block--full">
                                                <label for="deviceName" class="adc-field-label">Device Name <span class="require">*</span></label>
                                                <input class="adc-input form-control" id="deviceName" type="text" placeholder="Enter Device Name" name="deviceName" required />
                                            </div>
                                            <div class="adc-field-block adc-field-block--full">
                                                <label for="user-select" class="adc-field-label">Select Data Fields <span class="require">*</span></label>
                                                <select id="user-select" name="user_select[]" class="adc-multiselect" multiple></select>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="adc-table-container" style="display: none;" id="selectedDeviceTableContainer">
                                        <table class="table adc-data-field-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">Sr.No</th>
                                                    <th width="20%">FIELD NAME</th>
                                                    <th width="25%">DEFAULT VALUE <span class="require">*</span></th>
                                                    <th width="15%">TYPE</th>
                                                    <th width="20%">VALIDATION</th>
                                                    <th width="10%">REQUIRED?</th>
                                                    <th width="5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="selectedDeviceInput"></tbody>
                                        </table>
                                    </div>
                                    <div class="adc-form-actions">
                                        <button type="button" class="btn btn-primary next-btn adc-next-btn margin-top-10" disabled="true">Next</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-step">
                                <div class="adc-form-card">
                                    <h5 class="adc-section-title"><i class="fa fa-file-text-o"></i> Template Details</h5>
                                    <div class="form-group row" style="margin-top: 15px;">
                                        <label for="template_name" class="control-label col-xs-12 col-md-3">Template Name <span class="require">*</span></label>
                                        <div class="col-xs-12 col-md-6">
                                            <input class="form-control" id="template_name" type="text" placeholder="Enter Template Name" name="template_name" required />
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div id="inputSecondStep" class="clearfix"></div>
                                    <div class="form-group adc-step2-actions row margin-top-10" style="padding: 0 15px; margin-bottom: 0;">
                                        <div class="col-xs-12 col-md-offset-3 col-md-6 text-left">
                                            <button type="button" class="btn btn-primary btn-flat prev-btn adc-step-nav-btn" style="margin-right: 10px;">Previous</button>
                                            <button class="btn btn-primary btn-flat submit-btn-action adc-step-nav-btn" style="margin-top:1px" type="submit">Save</button>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
<div class="modal" id="canModal" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-times"></i></button>
                <h5 class="modal-title">CAN Protocol Configuration</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="canForm">
                            <!-- Protocol Selection -->
                            <div class="isCanEnable">
                                <label for="curl" class="control-label padding-left-3">Can Protocol<span
                                        class="require">*</span></label>
                                <div class="col-lg-12 padding-1">
                                    <select class="" id="can_protocol" name="canConfiguration[can_protocol]"
                                        onChange="selectedCanProtocol()" required>
                                        <option value=""> </option>
                                        <option value="J1979">J1979</option>
                                        <option value="J1939">J1939</option>
                                    </select>
                                </div>
                            </div>
                            <div id="dynamicCanFields"></div>
                            <!-- Submit -->
                            <!-- <button type="button" class="btn btn-success mt-4">Generate JSON</button> -->
                        </form>
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-success mt-4" onclick="generateJSON()">Submit</button>
                    </div>
                </div>
                <!-- Output JSON -->
                <!-- <div class="mt-4">
               <label class="form-label">Generated JSON:</label>
               <textarea class="form-control" id="outputJson" rows="10" readonly></textarea>
               </div> -->
            </div>
        </div>
    </div>
</div>
<script>
    function openCanModal() {
        $('#canModal').modal('show');
    }

    function selectedCanProtocol() {
        let canProtocolValue = $('#can_protocol').val();
        if (!canProtocolValue) return;

        let actionUrl = "{{ url((Auth::user()->user_type == 'Admin' ? 'admin' : 'reseller') . '/get-can-protocol-fields') }}";

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: {
                protocol: canProtocolValue,
                _token: '{{ csrf_token() }}'
            },
            success: function(fields) {
                let html = '<div class="row">';

                fields.forEach(field => {
                    // console.log("field ==>", field);
                    // const id = field.id;
                    const fieldId = field.fieldName.replace(/\s+/g, '_').toLowerCase();
                    const inputType = field.inputType;
                    let validation = {};

                    try {
                        validation = JSON.parse(field.validationConfig || '{}');
                    } catch (e) {
                        console.warn('Invalid JSON in validationConfig for field:', field.fieldName);
                    }

                    let inputHtml = `<input type="hidden" name="idCanParameters[${fieldId}]" value="${field.id}" />`;
                    inputHtml += `<input type="hidden" name="CanParametersType[${fieldId }]" value="${ inputType}" />`;

                    let attr = `id="${fieldId}" name="canConfiguration[${fieldId}]" class="form-control"  placeholder="Enter ${field.fieldName}"`;

                    if (inputType === 'number') {
                        if (validation.numberInput) {
                            attr += ` min="${validation.numberInput.min}" max="${validation.numberInput.max}"`;
                        }
                        inputHtml += `<input type="number" ${attr} />`;
                    } else if (inputType === 'select') {
                        inputHtml += `<select ${attr}>`;
                        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                            validation.selectOptions.forEach(option => {
                                inputHtml += `<option value="${option}">${option}</option>`;
                            });
                        } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                            Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                inputHtml += `<option value="${key}">${value}</option>`;
                            });
                        } else {
                            inputHtml += `<option value="">-- Select --</option>`;
                        }
                        inputHtml += `</select>`;
                    } else if (inputType == 'multiselect') {
                        inputHtml += `<select id="${fieldId}" placeholder="Enter ${field.fieldName}" multiple name="canConfiguration[${fieldId}][]">`;

                        if (validation.selectOptions && Array.isArray(validation.selectOptions)) {
                            validation.selectOptions.forEach(option => {
                                inputHtml += `<option value="${option}">${option}</option>`;
                            });
                        } else if (validation.selectOptions && typeof validation.selectOptions === 'object') {
                            Object.entries(validation.selectOptions).forEach(([key, value]) => {
                                inputHtml += `<option value="${key}">${value}</option>`;
                            });
                        } else {
                            inputHtml += `<option value="">-- Select --</option>`;
                        }

                        inputHtml += `</select>`;

                        // Apply Select2
                        setTimeout(() => {
                            $('#' + fieldId).select2({
                                placeholder: 'Select options',
                                allowClear: true,
                                width: '100%'
                            });
                        }, 100);
                    } else {
                        // default to text input
                        if (validation.maxValueInput) {
                            attr += ` maxlength="${validation.maxValueInput}"`;
                        }
                        inputHtml += `<input type="text" ${attr} />`;
                    }

                    html += `<div class="col-md-12 padding-3 padding-top-10">
                  <div class="form-group" id="modalInput">
                      <label for="${fieldId}" class="control-label padding-left-14" required>
                          ${field.fieldName} <span class="require">*</span>
                      </label>
                      <div class="col-lg-12">
                          ${inputHtml}
                          <div class="col-sm-12 alert alert-danger ${fieldId}_error" role="alert" style="display:none"></div>
                      </div>
                  </div></div>`;
                });
                html += '</div>';
                $('#dynamicCanFields').html(html).show();
            },
            error: function(xhr) {
                console.error("Error fetching CAN protocol fields", xhr);
            }
        });
    }

    function generateJSON() {
        let canConfigData = {};

        $('input[name^="canConfiguration["], select[name^="canConfiguration["]').each(function() {
            let fieldId = $(this).attr('id'); // Or extract from name if needed
            let value = $(this).val();

            // Special handling for can_protocol
            if (fieldId === 'can_protocol') {
                canConfigData[fieldId] = {
                    id: 91,
                    value: value
                };
            } else {
                let hiddenInput = $(`input[name="idCanParameters[${fieldId}]"]`);
                let canParametersType = $(`input[name="CanParametersType[${fieldId}]"]`).val();

                let id = hiddenInput.val();

                // Check if id and value are not empty/null
                if (id && value !== "") {
                    if (canParametersType == 'multiselect') {
                        const formattedMultiValue = `{${value.join(',')}}`;
                        canConfigData[fieldId] = {
                            id: parseInt(id),
                            value: formattedMultiValue
                        };
                    } else {
                        canConfigData[fieldId] = {
                            id: parseInt(id),
                            value: value
                        };
                    }
                }
            }
        });
        $('#canConfigurationArr').val(JSON.stringify(canConfigData));
        $('#canModal').modal('hide');
    }
    let selectedOrder = [];
    $(document).ready(function() {
        $('#can_protocol').select2({
            placeholder: "Search and Select",
        });
        let data = <?php echo json_encode($dataFields); ?>;
        $('#user-select').select2({
            placeholder: 'Select devices',
            allowClear: true
        });
        $('#user-select').on('change', function() {
            const selectedOptions = Array.from(this.selectedOptions).map(opt => Number(opt.value));

            // Add new selections to the end
            selectedOptions.forEach(id => {
                if (!selectedOrder.includes(id)) {
                    selectedOrder.push(id);
                }
            });

            // Remove unselected items
            selectedOrder = selectedOrder.filter(id => selectedOptions.includes(id));

            console.log("User selection order:", selectedOrder);

            // Get selected items based on the preserved selection order
            //const selectedItems = data.filter(item => selectedOrder.includes(item.id));
            const selectedItems = selectedOrder.map(id => data.find(item => item.id === id));
            console.log("selectedItems ===>", selectedItems);
            // Clear previous inputs
            $('#selectedDeviceInput').empty();
            if (selectedItems.length > 0) {
                $('#selectedDeviceTableContainer').show();
            } else {
                $('#selectedDeviceTableContainer').hide();
            }
            console.log("selectedItems ==>", selectedItems);
            // Generate and append new inputs in order
            selectedItems.forEach((item, index) => {
                const validationConfig = item.validationConfig ? JSON.parse(item.validationConfig) : {};
                const selectOptions = validationConfig.selectOptions || [];
                const selectValues = validationConfig.selectValues || [];
                let defaultInput = '';
                let validationRule = '';

                switch (item.inputType) {
                    case 'select':
                        validationRule = `<span class="adc-meta-chip"><b>Type</b> ${item.inputType}</span>`;
                        defaultInput = `
                        <select class="adc-input form-control" name="default[]" id="defaultValue${item.id}">
                            <option value="">Please Select</option>
                            ${selectOptions.map((opt, i) => `
                            <option value="${selectValues[i] || opt}">${opt}</option>
                            `).join('')}
                        </select>
                        <input type="hidden" name="valConfig[${item.id}]" id="valConfig${item.id}" data-selectOptions="${selectOptions}" data-selectValues="${selectValues}" />
                        `;
                        break;
                    case 'multiselect':
                        validationRule = `<span class="adc-meta-chip"><b>Type</b> ${item.inputType}</span>`;
                        const selectId = `defaultValue${item.id}`;
                        const selectedValues = validationConfig.selectValues || [];
                        defaultInput = `
                        <select class="adc-input form-control select2-multiselect" name="default[${index}][]" id="${selectId}" multiple style="width: 100%;">
                              ${validationConfig.selectOptions.map((opt, i) => {
                                const val = validationConfig.selectValues[i];
                                return `<option value="${val}" >${opt}</option>`;
                            }).join('')}
                        </select>

                        <input type="hidden" 
                                name="valConfig[${item.id}]" 
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
                    case 'number':
                        validationRule = `<span class="adc-meta-chip"><b>Type</b> ${item.inputType}</span><span class="adc-meta-chip"><b>Min</b> ${validationConfig.numberInput?.min ?? '—'}</span><span class="adc-meta-chip"><b>Max</b> ${validationConfig.numberInput?.max ?? '—'}</span>`;
                        defaultInput = `
                        <input type="number" class="adc-input form-control no-space-allowed"
                                name="default[]" id="defaultValue${item.id}"
                                placeholder="Enter Number"
                                min="${validationConfig.numberInput?.min ?? ''}"
                                max="${validationConfig.numberInput?.max ?? ''}" />
                        `;
                        break;
                    case 'text':
                    case 'IP/URL':
                    case 'text_array':
                    default:
                        validationRule = `<span class="adc-meta-chip"><b>Type</b> ${item.inputType}</span><span class="adc-meta-chip"><b>Max length</b> ${validationConfig.maxValueInput ?? '—'}</span>`;
                        let addClassTextArray = item.inputType === 'text_array' ? 'text-array-space' : '';
                        let addClassIpUrl = item.inputType === 'IP/URL' ? 'ip-url-space' : '';
                        defaultInput = `
                        <input type="text" class="adc-input form-control no-space-allowed ${addClassTextArray} ${addClassIpUrl}"
                                name="default[]" id="defaultValue${item.id}"
                                placeholder="Enter Value"
                                maxlength="${validationConfig.maxValueInput ?? ''}" />
                        `;
                        break;
                }

            const html = ` 
              <tr class="adc-dynamic-row" id="device-input-${item.id}">
                <td class="adc-col-count text-center">
                  <span class="adc-count-badge">${index + 1}</span>
                </td>
                <td class="adc-col-name">
                  <input class="adc-input form-control" type="text" disabled name="name[${item.id}]" value="${item.fieldName}" required />
                  <input type="hidden" name="nameParameters[]" id="nameParameters${item.id}" value="${item.fieldName}" />
                  <input type="hidden" name="idParameters[]" id="idParameters${item.id}" value="${item.id}" />
                  <input type="hidden" name="inputType[]" value="${item.inputType}" required />
                </td>
                <td class="adc-col-value">
                  ${defaultInput}
                </td>
                <td class="adc-col-type">
                  <span class="adc-readonly-text">${item.inputType}</span>
                </td>
                <td class="adc-col-val">
                  <div class="adc-val-badges">${validationRule}</div>
                </td>
                <td class="adc-col-req text-center">
                  <label class="adc-custom-checkbox">
                    <input type="hidden" name="inputFieldRequired[]" value="false">
                    <input type="checkbox" checked name="inputFieldRequired[]" value="true"/>
                    <span class="checkmark"></span>
                  </label>
                </td>
                <td class="adc-col-action text-center">
                  <button type="button" class="btn adc-btn-delete remove-input" data-id="${item.id}" title="Remove field">
                    <i class="fa fa-trash-o" style="color: #ef4444; font-size: 16px;"></i>
                  </button>
                </td>
              </tr>
            `;

                $('#selectedDeviceInput').append(html);
            });
        });
        var $select = $('#user-select');
        $select.empty();
        data.forEach(function(item) {
            var option = new Option(item.fieldName, item.id, false, false);
            $select.append(option);
        });
        $select.select2();
    });
    $(document).ready(function() {
        let data = <?php echo json_encode($dataFields); ?>;
        console.log("data IP ==>", data)
        let inputCount = 0;
        let inputParameterCount = 0;
        let currentStep = 0;
        const $steps = $(".form-step");
        const $dynamicInputContainer = $("#selectedDeviceInput");
        const $dynamicInputParameters = $("#dynamicInputParameters");
        const $inputSecondStep = $("#inputSecondStep");
        let step1Fields = [];
        $('#is_esim').change(function() {
            updateField();

        });

        function updateField() {
            var isChecked = $('#is_esim').is(':checked');
            var status = isChecked ? 'CCID' : '';

            // addInputParameters();
            $('#nameParameters0').val(status);
        }
        // addInputField();

        $('input[required]').on('input', validateForm);
        validateForm();
        // Show the current step
        function showStep(step) {
            $steps.removeClass("form-step-active");
            $steps.eq(step).addClass("form-step-active");
        }

        // Initialize form by showing the first step
        showStep(currentStep);

        // Next button handler
        $(".next-btn").click(function() {
            console.log("valconfig ==> ", $(this).find('input[name^="valConfig["]').data('selectoptions'));
            if (currentStep === 0) {
                // Save fields configuration from Step 1
                step1Fields = [];
                let canEnable = $('#is_can_enable').prop('checked');
                console.log("canEnable ==>", canEnable);
                let requiredCheckbox = [];
                $dynamicInputContainer.find('.form-group').each(function() {
                    requiredCheckbox.push({
                        value: $(this).find('input[type="checkbox"][name^="inputFieldRequired["]').is(':checked') ? true : false,
                    });
                    console.log("requiredCheckbox", requiredCheckbox);
                    const inputField = {
                        name: $(this).find('input[name^="name["]').val(),
                        default: $(this).find('input[name^="default["]').val(),
                        type: $(this).find('input[name^="inputType["]').val(),
                        min: $(this).find('input[name^="default["]').attr('min'),
                        max: $(this).find('input[name^="default["]').attr('max'),
                        maxlength: $(this).find('input[name^="default["]').attr('maxlength'),
                        selectOptions: $(this).find('input[name^="valConfig["]').data('selectoptions'),
                        selectValues: $(this).find('input[name^="valConfig\\["]').data('selectvalues'),
                        options: []
                    };

                    if (inputField.type === 'select') {
                        console.log(inputField);
                        let selectOptionsStr = String(inputField.selectOptions);
                        let selectValuesStr = String(inputField.selectValues);

                        let optionArray = selectOptionsStr.includes(",") ?
                            selectOptionsStr.split(",") : [selectOptionsStr];

                        let valueArray = selectValuesStr.includes(",") ?
                            selectValuesStr.split(",") : [selectValuesStr];

                        // Initialize options array
                        inputField.options = [];

                        optionArray.forEach(function(option, index) {
                            inputField.options.push({
                                option: String(option).trim(),
                                value: String(valueArray[index] ?? '').trim()
                            });
                        });
                    }
                    step1Fields.push(inputField);
                });

                // Render fields in Step 2 based on Step 1 configurations
                $inputSecondStep.empty();
                let fieldHtml = '';
                step1Fields.forEach((field, index) => {
                    console.log("field ==>", field);
                    
                    if (field.type === 'text' || field.type === 'number') {
                        fieldHtml += `<div class="form-group">
                                <label class="control-label col-lg-3">${field.name} <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <input class="form-control" type="${field.type}" name="dynamicField[${field.name.toLowerCase().replace(/\s+/g, '_')}]" value="${field.default}" ${requiredCheckbox[index].value  ? 'required' : ''} />
                                </div>
                            </div>`;
                    } else if (field.type === 'select') {

                        fieldHtml += `<div class="form-group">
                                <label class="control-label col-lg-3">${field.name} <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select class="form-control" name="dynamicField[${field.name.toLowerCase().replace(/\s+/g, '_')}]" required>
                                        ${field.options.map(option => `<option value="${option.value}">${option.option}</option>`).join('')}
                                    </select>
                                </div>
                            </div>`;
                    } else if (field.type === 'multiselect') {
                        let defaultValue = $('select[name^="default["]').val();
                        console.log('defaultValue ==>', defaultValue);
                        console.log(`field ===> ${field.type} `, field);
                        const fieldId = `dynamicField_${field.name.toLowerCase().replace(/\s+/g, '_')}`;

                        fieldHtml += `
                            <div class="form-group">
                                <label class="control-label col-lg-3">${field.name} <span class="require">*</span></label>
                                <div class="col-lg-6">
                                    <select id="${fieldId}" class="form-control select2-multiselect"
                                            name="dynamicField[${field.name.toLowerCase().replace(/\s+/g, '_')}][]"
                                            multiple style="width: 100%; height: auto;">
                                               ${field.selectOptions.map((opt, i) => {
                                                    const val = field.selectValues[i];
                                                    const isSelected = defaultValue.includes(val) ? 'selected' : '';
                                                    return `<option value="${val}" ${isSelected}>${opt}</option>`;
                                                }).join('')}
           
                                    </select>
                                </div>
                            </div>
                        `;
                        setTimeout(() => {
                            $(`#${fieldId}`).select2({
                                placeholder: 'Select options',
                                allowClear: true,
                                width: '100%'
                            });
                        }, 0);

                    } else if (field.type === 'IP/URL' || field.type === 'text_array') {
                        fieldHtml += `<div class="form-group">
                            <label class="control-label col-lg-3">${field.name} <span class="require">*</span></label>
                            <div class="col-lg-6">
                                <input class="form-control" type="${field.type}" name="dynamicField[${field.name.toLowerCase().replace(/\s+/g, '_')}]" value="${field.default}" ${requiredCheckbox[index].value  ? 'required' : ''} />
                            </div>
                        </div>`;
                    }
                   
                    // $inputSecondStep.append(fieldHtml);
                });

                 if (canEnable) {
                        fieldHtml += `<div class="form-group isCanEnable row" style="margin-top:15px; clear:both;">
                        <label for="canConfigurationArr" class="control-label col-lg-3" required>
                            CAN Configuration <span class="require">*</span>
                        </label>
                        <div class="col-lg-6">
                            <input type="text" class="form-control" name="canConfigurationArr"
                                id="canConfigurationArr" value="" readonly />
                            
                            <div class="alert alert-danger modelName_error mt-2" role="alert" style="display: none;"></div>
                            
                            <button type="button" class="btn btn-primary mt-2" onclick="openCanModal()">
                                Configure CAN Protocol
                            </button>
                        </div>
                    </div><div class="clearfix"></div>`;
                    $inputSecondStep.append(fieldHtml);
                    }
            }
            if (currentStep < $steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        });
        $(".ip-url-space").on("keydown", validateIpUrlInput);
        $(".text-array-space").on("keydown", validateTextArrayInput);

        function validateIpUrlInput(event) {
            const key = event.key;
            const allowed = /^[a-zA-Z0-9. ]$/;
            if (event.ctrlKey || event.metaKey || key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" || key === "Delete" || key === "Tab") {
                return;
            }
            if (!allowed.test(key)) {
                event.preventDefault();
            }
        }

        function validateTextArrayInput(event) {
            const key = event.key;
            const allowed = /^[a-zA-Z0-9.,{}\s]$/;
            if (event.ctrlKey || event.metaKey || key === "Backspace" || key === "ArrowLeft" || key === "ArrowRight" || key === "Delete" || key === "Tab") {
                return;
            }

            if (!allowed.test(key)) {
                event.preventDefault();
            }
        }

        // Previous button handler
        $(".prev-btn").click(function() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        function validateForm() {
            let isValid = true;

            // Iterate over each input field with the 'required' attribute
            $('#deviceName').each(function() {
                if ($(this).val().trim() === '') {
                    isValid = false;
                    return false; // Exit the loop if any required field is empty
                }
            });

            // Optionally, handle the result of the validation
            if (isValid) {
                console.log('All required fields are filled.');
            } else {
                console.log('Some required fields are missing.');
            }
            // Enable or disable the next button based on form validity
            $('.next-btn').prop('disabled', !isValid);
        }

        function addInputParameters() {
            var isChecked = $('#is_esim').is(':checked');
            const label = 'Device Parameters';
            let html = `<div class="form-group">
                <label for="input${inputParameterCount}" class="control-label col-lg-3">${label} <span class="require">*</span></label>
                <div class="row d-flex">
                    <div class="col-lg-10">
                        <input class="form-control" placeholder="Enter Input Parameters" type="text" id="nameParameters${inputParameterCount}" name="nameParameters[${inputParameterCount}]" required />
                    </div>`;
            if (!(isChecked && inputParameterCount === 0)) {
                html += `<div class="col-lg-2 bgx-del-button-container">
                            <button type="button" class="btn btn-danger btn-sm remove-parameters-input margin-top-1">
                                <img src="/assets/icons/cross.svg" />
                            </button>
                        </div>`;
            }

            html += `</div></div>`;
            $dynamicInputParameters.append(html);
            $(`input[name="nameParameters[${inputParameterCount}]`).focus();
            inputParameterCount++;


        }
        // Add new input field
        function addInputField() {
            const label = 'Device Inputs';
            const html = `
                <div class="form-group">
                    <label for="input${inputCount}" class="control-label col-lg-3">${label} <span class="require">*</span></label>
                    <div class="row d-flex">
                        <div class="col-lg-3">
                            <input class="form-control" placeholder="Enter Input Name" type="text" name="name[${inputCount}]" required />
                        </div>
                        <div class="col-lg-3">
                            <input class="form-control default-val" placeholder="Enter Default Value" type="text" id="defaultValue${inputCount}" name="default[${inputCount}]"/>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control inputType" name="inputType[${inputCount}]" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="select">Select</option>
                                <option value="IP/URL">IP/URL</option>
                                <option value="text_array">Text Array</option>
                            </select>
                        </div>
                        <div class="col-lg-1 d-flex bgx-checkbox-custom">
                            <input type="hidden" name="inputFieldRequired[${inputCount}]" value="false">
                            <input type="checkbox" checked name="inputFieldRequired[${inputCount}]" value="true"/>
                        </div>
                        <div class="col-lg-1 bgx-del-button-container adc-remove-cell">
                            <button type="button" class="btn btn-danger btn-sm remove-input margin-top-1" title="Remove field"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="append-number-options mt-2" style="display:none;">
                        <div class="form-group number-group">
                            <label for="input${inputCount}" class="control-label col-lg-3">Choose Range<span class="require">*</span></label>
                            <div class="row d-flex">
                                <div class="col-lg-4 number-first-input">
                                    <input class="form-control" placeholder="min" type="number" name="numberInput[${inputCount}][min]" />
                                </div>
                                <div class="col-lg-4 number-second-input">
                                    <input class="form-control" placeholder="max" type="number" name="numberInput[${inputCount}][max]" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="append-select-options mt-2" style="display:none;">
                        <div class="form-group">
                            <label for="input${inputCount}" class="control-label col-lg-3">Options <span class="require">*</span></label>
                            <div class="row select-options-container">
                                <div class="options-row">
                                    <div class="col-lg-7 d-flex">
                                        <input class="form-control onlynumberdecimal col-lg-3" placeholder="Enter Option" type="text" name="selectOptions[${inputCount}][]" />
                                        <div class="col-lg-1"></div>
                                        <input class="form-control onlynumberdecimal col-lg-3" placeholder="Enter Value" type="text" name="selectValues[${inputCount}][]"/>
                                    </div>
                                    <div class="col-lg-2 bgx-del-button-container">
                                        <button type="button" class="btn btn-danger btn-sm remove-option"><img src="/assets/icons/cross.svg" /></button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="col-lg-10 text-right">
                                    <button type="button" class="btn btn-success btn-sm add-option" data-inputcount="${inputCount}"><img src="/assets/icons/plus.svg" /></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            $dynamicInputContainer.append(html);
            $(`input[name="name[${inputCount}]"]`).focus();
            inputCount++;
        }

        // Add input field on button click
        $("#addInput").click(function() {
            addInputField();
        });
        // $("#addParameter").click(function() {
        //     addInputParameters();
        // });
        // Handle input type change
        $(document).on("change", ".inputType", function() {
            const selectedType = $(this).val();
            const $formGroup = $(this).closest(".form-group");
            const $appendSelectOptions = $formGroup.find(".append-select-options");
            const $inputOptions = $formGroup.find('.append-number-options');
            const $defaultValue = $formGroup.find('input[name^="default["]');
            var defaultVal = $(this).closest(".form-group").find(".default-val");
            $defaultValue.attr('type', 'text');
            $inputOptions.find('input').attr('required', false);
            $appendSelectOptions.find('input').attr('required', false);

            if (selectedType === "select") {
                $appendSelectOptions.show();
                $appendSelectOptions.find('input').attr('required', true);
                $inputOptions.hide();
                defaultVal.removeClass("ip-url-space");
                defaultVal.removeClass("text-array-space");
            } else if (selectedType === 'number') {
                $defaultValue.attr('type', 'number');
                $inputOptions.show();
                $inputOptions.find('input').attr('required', true);
                $appendSelectOptions.hide();
                defaultVal.removeClass("ip-url-space");
                defaultVal.removeClass("text-array-space");
            } else if (selectedType == 'IP/URL') {
                defaultVal.addClass("ip-url-space no-space-allowed");
            } else if (selectedType == 'text_array') {
                defaultVal.addClass("text-array-space");
            } else {
                $appendSelectOptions.hide();
                $inputOptions.hide();
                defaultVal.removeClass("ip-url-space");
                defaultVal.removeClass("text-array-space");
            }
        });
        $(".no-space-allowed").on("keydown", function(event) {
            if (event.key === " ") {
                event.preventDefault(); // Prevent space
                return false;
            }
        });
        $(document).on("keydown", ".ip-url-space", function(event) {
            const key = event.key;
            console.log("Pressed key:", key);

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
            const allowed = /^[a-zA-Z0-9.]*$/;

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
        // Add option button functionality
        $(document).on("click", ".add-option", function() {
            const inputCount = $(this).data("inputcount");
            const optionsHtml = `
                <div class="options-row">
                    <div class="col-lg-7 d-flex">
                        <input class="form-control onlynumberdecimal col-lg-3" placeholder="Enter Option" type="text" name="selectOptions[${inputCount}][]" required/>
                        <div class="col-lg-1"></div>
                        <input class="form-control onlynumberdecimal col-lg-3" placeholder="Enter Value" type="text" name="selectValues[${inputCount}][]" required/>
                    </div>
                    <div class="col-lg-2 bgx-del-button-container">
                        <button type="button" class="btn btn-danger btn-sm remove-option"><img src="/assets/icons/cross.svg" /></button>
                    </div>
                </div>`;
            $(this).closest(".form-group").find(".select-options-container").append(optionsHtml);
        });

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

        // Remove option button functionality
        $(document).on("click", ".remove-option", function() {
            $(this).closest(".options-row").remove();
        });


        // Prevent special characters and spaces in certain fields
        $(".onlynumberdecimal").on("keydown", function(event) {
            const key = event.key;
            if (event.keyCode === 32 && !event.shiftKey) {
                event.preventDefault();
            }
            const specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?£~]/;
            if (specialChars.test(key)) {
                event.preventDefault();
            }
        });
        $('#addDeviceCategory').submit(function() {
            event.preventDefault();
            $(".submit-btn-action").attr('disabled', true);
            var formData = $(this).serialize();
            $.ajax({
                type: "POST",
                url: "/{{$url_type}}/store-device-category",
                data: formData,
                success: function(response) {
                    $("#alert_msg").html('<div class="col-sm-12 alert alert-success" role="alert">Successfully added device category.</div>');
                    // window.location.href = '/admin/view-device-category';
                },
                error: function(xhr, status, error) {
                    console.error("AJAX request failed");
                    console.error(xhr.responseText);
                    $("#alert_msg").html('<div class="col-sm-12 alert alert-danger" role="alert">Failed to add device category. Please try again.</div>');
                }
            });
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
                $inputs.val('');
            }
        }

        $('#is_certification_enable').on('change', toggleCertificationFields);
        toggleCertificationFields();
    });
</script>
@stop
