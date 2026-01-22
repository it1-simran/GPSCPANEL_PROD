@extends('layouts.apps')
@section('content')
<style>
    body {
        padding: 40px;
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .select-container {
        max-width: 500px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 5px;
        padding: 5px;
        min-height: 40px;
    }

    label {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .select2-container {
        padding: 0px !important;
    }
</style>
<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Device Category</a></li>
                        <li><a href="/{{$url_type}}/View-device-category">View
                                Device Categories</a></li>
                        <li class="active"><a href="#">Edit Device
                                Category</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="c_panel">
                    <div class="c_title">
                        <h2>Edit Device Category</h2>
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
                        <div class="card bordered-1 ">
                            <form class="validator form-horizontal"
                                id="editDeviceCategory"
                                name="editDeviceCategory" method="post"
                                action="/{{$url_type}}/update-device-category">
                                @csrf
                                <div class='col-lg-12'>
                                    <h5><b>Device Configurations</b></h5>
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
                                        Data Fields <span
                                            class="require">*</span></label>
                                    <div class="col-lg-6">
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

                                <hr>

                                <div class="form-group ">
                                    <div class="col-lg-offset-3 col-lg-6">
                                        <button class="btn btn-primary btn-flat"
                                            type="submit">Save</button>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let selectedOptions = <?php echo json_encode($device_category->inputs);  ?>;
    var selectedIds = selectedOptions.map(item => item?.id);
    let data = <?php echo json_encode($dataFields); ?>;
    let selectedOrder = [];
    $(document).ready(function() {
        $('#user-select').select2({
            placeholder: 'Select devices',
            allowClear: true
        });
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

                const validationConfig = item.validationConfig ? JSON.parse(item.validationConfig) : {};
                const selectOptions = validationConfig.selectOptions || [];
                const selectValues = validationConfig.selectValues || [];
                let defaultInput = '';
                let validationRule = '';
                switch (item.inputType) {
                    case 'select':
                        validationRule = `<p><b>Input Type :</b> ${item.inputType}</p>`;
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
                        validationRule = `<p><b>Input Type :</b> ${item.inputType}</p>`;

                        const selectId = `defaultValue${item.id}`;
                        const selectedValues = validationConfig.selectValues || [];;
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
                    case 'number':
                        validationRule = `<p><b>Input Type :</b> ${item.inputType}</p><p><b>min:</b> ${validationConfig.numberInput?.min ?? ''} <br><b>max:</b> ${validationConfig.numberInput?.max ?? ''}</p>`;
                        defaultInput = `<input type="number" class="form-control no-space-allowed"
                         name="default[]" id="defaultValue${item.id}"
                         placeholder="Enter Number"
                         value ="${selectedOptions[index]?.default ? selectedOptions[index]?.default : ''}";
                         min="${validationConfig.numberInput?.min ?? ''}"
                         max="${validationConfig.numberInput?.max ?? ''}" />`;
                        break;

                    case 'text':
                    case 'IP/URL':
                    case 'text_array':
                    default:
                        let addClassTextArray = item.inputType === 'text_array' ? 'text-array-space' : '';
                        let addClassIpUrl = item.inputType === 'IP/URL' ? 'ip-url-space' : '';
                        validationRule = `<p><b>Input Type :</b> ${item.inputType}</p><p><b>maxlength:</b> ${validationConfig.maxValueInput ?? ''}</p>`;
                        defaultInput = `<input type="text" class="form-control no-space-allowed ${addClassTextArray} ${addClassIpUrl}"
                         name="default[]" id="defaultValue${item.id}"
                         placeholder="Enter Value"
                         value ="${selectedOptions[index]?.default ? selectedOptions[index]?.default : ''}";
                         maxlength="${validationConfig.maxValueInput ?? ''}" />`;
                        break;
                }

                const html = `
              <div class="form-group" id="device-input-${item.id}">
                <label class="control-label col-lg-3">Field ${index + 1} <span class="require">*</span><p>ID : ${item.id}</p></label>
                <div class="row d-flex">
                  <div class="col-lg-3">
                    <input class="form-control" placeholder="Enter Input Name" type="text" disabled name="name[${item.id}]" value="${item.fieldName}" style="width: fit-content;" required />
                    <input type="hidden" name="nameParameters[]" id="nameParameters${item.id}" value="${item.fieldName}" />
                    <input type="hidden" name="idParameters[]" id="idParameters${item.id}" value="${item.id}" />
                
                  </div>
                  <div class="col-lg-2">
                    <label class="control-label">Default Value <span class="require">*</span></label>
                  </div>
                  <div class="col-lg-3">
                    ${defaultInput}
                  </div>
                  <div class="col-lg-3">
                    ${validationRule}
                  </div>
                  <div class="col-lg-1 d-flex bgx-checkbox-custom">
                    <input type="hidden" name="inputFieldRequired[]" value="false">
                    <input type="checkbox" checked name="inputFieldRequired[]" value="true"/>
                </div>
                  <div class="col-lg-1 bgx-del-button-container">
                    <button type="button" class="btn btn-danger btn-sm remove-input" data-id="${item.id}">
                      <img src="/assets/icons/cross.svg" />
                    </button>
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
                '<button type="button" class="btn btn-danger btn-sm remove-option"><img src="/assets/icons/cross.svg" /></button>' +
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

@stop