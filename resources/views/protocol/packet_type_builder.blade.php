@extends('layouts.apps')
@section('content')
<!-- Google Fonts -->
<link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
  rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<section id="main-content">
  <section class="wrapper protocol-builder-page">
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="{{ route('protocols.index') }}">Protocol Management</a></li>
            <li><a href="{{ route('protocols.packet-types', $protocol->id) }}">Packet Types</a></li>
            <li class="active"><a href="#">{{ isset($packetType) ? 'Edit' : 'Create' }} Packet Configuration</a></li>
          </ul>
        </nav>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Packet Configuration: <span class="text-primary">{{ $protocol->name }}</span></h2>
          </div>

          <div class="c_content">
            <div id="builderErrorBox" class="alert alert-danger builder-error-box" style="display:none;"></div>
            <form id="masterBuilderForm" class="premium-form">
              <div class="section-card mb-4">
                <div class="section-header">
                  <h4><i class="fa fa-info-circle"></i> 1. Packet Details</h4>
                </div>
                <div class="section-body row">
                  <input type="hidden" name="packet_id" value="{{ $packetType->id ?? '' }}">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="premium-label">Packet Name <span class="text-danger">*</span></label>
                      <input type="text" name="packet_name" value="{{ $packetType->name ?? '' }}"
                        class="form-control premium-input" required placeholder="e.g. Login Packet">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="premium-label">Header Identifier</label>
                      <input type="text" name="packet_header" value="{{ $packetType->header_identifier ?? '' }}"
                        class="form-control premium-input" placeholder="e.g. $NMP">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="premium-label">Delimiter (for CSV)</label>
                      <input type="text" name="packet_delimiter" value="{{ $packetType->delimiter ?? '' }}"
                        class="form-control premium-input" placeholder="e.g. ,">
                    </div>
                  </div>
                </div>
              </div>

              <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                  <h4 class="m-0"><i class="fa fa-list"></i> 2. Parameters Definition</h4>
                  <div class="header-actions">
                    <button type="button" class="btn btn-glass-info btn-sm" onclick="showAnalyzer()">
                      <i class="fa fa-magic"></i> Smart Analyzer
                    </button>
                    <button type="button" class="btn btn-glass-primary btn-sm" onclick="addNewRow()">
                      <i class="fa fa-plus"></i> Add Parameter
                    </button>
                  </div>
                </div>
                <div class="section-body p-0">
                  <div class="table-responsive premium-scroll" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-custom mb-0" id="builderTable">
                      <thead>
                        <tr>
                          <th width="40" class="text-center">#</th>
                          <th>Field Name</th>
                          <th width="130">Type</th>
                          <th width="100">Length</th>
                          <th>Validation</th>
                          <th width="180">Range (Min-Max)</th>
                          <th width="60" class="text-center">Req?</th>
                          <th width="50"></th>
                        </tr>
                      </thead>
                      <tbody id="sortableBody">
                        @if(isset($packetType))
                          @foreach ($packetType->fields as $field)
                            @include('protocol.partials.field_row', ['field' => $field])
                          @endforeach
                        @endif
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="form-actions text-right mt-4">
                <a href="{{ route('protocols.packet-types', $protocol->id) }}" style="margin-top: 10px;"
                  class="btn btn-glass-secondary">Cancel</a>
                <button type="button" class="btn btn-premium-success btn-lg" onclick="saveMasterConfiguration(this)">
                  <i class="fa fa-save"></i> Save Configuration
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>

<div class="modal premium-modal" id="analyzerModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content overflow-hidden">
      <div class="modal-header-premium">
        <div class="d-flex align-items-center" style="gap: 10px;">
          <div class="header-icon mr-3">
            <i class="fa fa-magic"></i>
          </div>
          <div>
            <h4 class="m-0 font-weight-bold">Smart Packet Analyzer</h4>
            <small class="text-muted opacity-75">Automatically extract parameters from a raw string</small>
          </div>
        </div>
        <button type="button" class="close-premium" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-4 bg-light-soft">
        <div class="info-card mb-4" style="margin-bottom: 20px;">
          <div class="d-flex align-items-start" style="align-items: flex-start; gap: 10px;">
            <i class="fa fa-info-circle text-primary mt-1 mr-3 fa-lg"></i>
            <p class="mb-0 text-muted" style="line-height: 1.5;">
              Paste your raw packet string below. The analyzer will detect fields based on the delimiter and populate
              the parameter list for you.
              <strong class="text-danger">Note:</strong> This will replace all current rows.
            </p>
          </div>
        </div>

        <div class="form-group mb-4">
          <label class="premium-label-bold">Paste Sample Packet String</label>
          <textarea id="samplePacket" class="form-control premium-textarea shadow-sm" rows="5"
            placeholder="Example: $NMP,JSD,2.2.6,NR,1,L,860269069112647..."></textarea>
        </div>

        <div class="row">
          <div class="col-md-5">
            <div class="form-group mb-0">
              <label class="premium-label-bold">Separator / Delimiter</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-white border-right-0"></span>
                </div>
                <input type="text" id="analyzerDelim" value=","
                  class="form-control premium-input border-left-0 font-weight-bold text-primary" maxlength="1"
                  style="font-size: 1.2rem; height: 50px;">
              </div>
              <small class="text-muted mt-1 d-block">Character used to split the packet</small>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 p-4 bg-white justify-content-between">
        <button type="button" class="btn btn-glass-secondary px-4 py-2" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-premium-primary btn-lg px-5 shadow-lg" onclick="runAnalysis()">
          <i class="fa fa-bolt"></i> Analyze & Populate Now
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
  window.addEventListener('DOMContentLoaded', (event) => {
    if (typeof Sortable !== 'undefined') {
      new Sortable(document.getElementById('sortableBody'), {
        handle: '.drag-handle',
        animation: 150
      });
    }

    // Add first row if empty
    setTimeout(() => {
      if (document.querySelectorAll('#sortableBody tr').length === 0) {
        addNewRow();
      }
      // Initialize existing rows
      document.querySelectorAll('select[name="validation_type"]').forEach(select => {
        toggleRegex(select);
      });
    }, 500);
  });

  function addNewRow(data = {}) {
    const tbody = document.getElementById('sortableBody');
    if (!tbody) return;

    const row = document.createElement('tr');
    row.className = 'field-row animate-in';
    row.innerHTML = `
        <td class="drag-handle text-center"><i class="fa fa-reorder"></i></td>
        <td><input type="text" name="name" value="${data.name || ''}" class="form-control premium-input" placeholder="Field Name"></td>
        <td>
            <select name="data_type" class="form-control premium-select">
                <option value="String" ${data.data_type == 'String' ? 'selected' : ''}>String</option>
                <option value="Numeric" ${data.data_type == 'Numeric' ? 'selected' : ''}>Numeric</option>
                <option value="ASCII" ${data.data_type == 'ASCII' ? 'selected' : ''}>ASCII</option>
                <option value="HEX" ${data.data_type == 'HEX' ? 'selected' : ''}>HEX</option>
            </select>
        </td>
        <td><input type="number" name="length" value="${data.length || ''}" class="form-control premium-input" placeholder="Length"></td>
        <td>
            <select name="validation_type" class="form-control premium-select" onchange="toggleRegex(this)">
                <option value="none" ${data.validation_type == 'none' ? 'selected' : ''}>None</option>
                <option value="imei" ${data.validation_type == 'imei' ? 'selected' : ''}>IMEI</option>
                <option value="date_ddmmyyyy" ${data.validation_type == 'date_ddmmyyyy' ? 'selected' : ''}>Date (DDMMYYYY)</option>
                <option value="time_hhmmss" ${data.validation_type == 'time_hhmmss' ? 'selected' : ''}>Time (HHMMSS)</option>
                <option value="nmea_checksum" ${data.validation_type == 'nmea_checksum' ? 'selected' : ''}>NMEA Checksum</option>
                <option value="regex" ${data.validation_type == 'regex' ? 'selected' : ''}>Custom Regex</option>
            </select>
            <input type="text" name="regex_pattern" value="${data.regex_pattern || ''}" 
              class="form-control mt-1 premium-input ${data.validation_type === 'regex' ? '' : 'd-none'}" 
              style="${data.validation_type === 'regex' ? 'display: block;' : 'display: none;'}"
              placeholder="Regex Pattern">
        </td>
        <td>
            <div class="d-flex" style="gap: 5px;">
              <input type="number" step="any" name="min_value" value="${data.min_value || ''}" class="form-control premium-input" placeholder="Min" style="flex:1">
              <input type="number" step="any" name="max_value" value="${data.max_value || ''}" class="form-control premium-input" placeholder="Max" style="flex:1">
            </div>
        </td>
        <td class="text-center">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" name="is_required" ${data.is_required !== false ? 'checked' : ''}>
            </div>
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-link text-danger btn-sm p-0" onclick="removeRow(this)">
            <i class="fa fa-trash-o fa-lg"></i>
          </button>
        </td>
    `;
    tbody.appendChild(row);
  }

  function removeRow(btn) {
    const row = btn.closest('tr');
    row.classList.add('animate-out');
    setTimeout(() => row.remove(), 300);
  }

  function toggleRegex(select) {
    const td = select.closest('td');
    const input = td.querySelector('[name="regex_pattern"]');
    if (!input) return;

    if (select.value === 'regex') {
      input.style.display = 'block';
      input.classList.remove('d-none');
    } else {
      input.style.display = 'none';
      input.classList.add('d-none');
      // We don't clear the value here to avoid data loss during incidental toggles, 
      // but you can if required.
    }
  }

  function showAnalyzer() { $('#analyzerModal').modal('show'); }

  function runAnalysis() {
    const rawInput = document.getElementById('samplePacket').value.trim();
    const delim = document.getElementById('analyzerDelim').value || ',';

    if (!rawInput) {
      Swal.fire({
        icon: 'warning',
        title: 'Empty Input',
        text: 'Please paste a sample packet string first.',
        confirmButtonColor: 'var(--premium-primary)'
      });
      return;
    }

    // Attempt to auto-fill header if it starts with $
    if (rawInput.startsWith('$')) {
      const firstComma = rawInput.indexOf(delim);
      if (firstComma > 0) {
        const header = rawInput.substring(0, firstComma);
        document.querySelector('[name="packet_header"]').value = header;
      }
    }

    // Robust parser that ignores delimiters inside parentheses
    const parts = [];
    let currentPart = "";
    let parenLevel = 0;

    for (let i = 0; i < rawInput.length; i++) {
      const char = rawInput[i];
      if (char === '(') parenLevel++;
      else if (char === ')') parenLevel--;

      if (char === delim && parenLevel === 0) {
        parts.push(currentPart);
        currentPart = "";
      } else {
        currentPart += char;
      }
    }
    parts.push(currentPart); // Push the last part

    const tbody = document.getElementById('sortableBody');
    tbody.innerHTML = '';

    parts.forEach((val, i) => {
      let type = 'String';
      let vType = 'none';

      // Basic type guessing
      const trimmedVal = val.trim();
      if (!isNaN(trimmedVal) && trimmedVal !== '') type = 'Numeric';
      else if (/^[0-9a-fA-F]+$/.test(trimmedVal) && trimmedVal.length > 2) type = 'HEX';

      // Validation guessing
      if (trimmedVal.length === 15 && /^\d+$/.test(trimmedVal)) vType = 'imei';
      else if (trimmedVal.length === 8 && /^\d+$/.test(trimmedVal) && i > 5) vType = 'date_ddmmyyyy';
      else if (trimmedVal.length === 6 && /^\d+$/.test(trimmedVal) && i > 5) vType = 'time_hhmmss';
      else if (trimmedVal.includes('*')) vType = 'nmea_checksum';

      addNewRow({
        name: i === 0 ? 'Header' : (i === parts.length - 1 ? 'Checksum' : `Param ${i + 1}`),
        length: val.length,
        data_type: type,
        validation_type: vType
      });
    });

    $('#analyzerModal').modal('hide');
    console.log(`Analyzed ${parts.length} parameters.`);
  }

  function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.field-row.has-error').forEach(row => row.classList.remove('has-error'));
    const box = document.getElementById('builderErrorBox');
    if (box) {
      box.style.display = 'none';
      box.innerHTML = '';
    }
  }

  function showValidationErrors(errors, fallbackMessage) {
    const box = document.getElementById('builderErrorBox');
    const messages = [];

    Object.keys(errors || {}).forEach(key => {
      const firstMessage = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
      if (firstMessage) messages.push(firstMessage);
      markFieldError(key);
    });

    if (box) {
      const uniqueMessages = [...new Set(messages.length ? messages : [fallbackMessage || 'Please correct the validation errors.'])];
      box.innerHTML = '<strong>Please fix the following:</strong><ul><li>' + uniqueMessages.join('</li><li>') + '</li></ul>';
      box.style.display = 'block';
      box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        text: messages.join('\n') || fallbackMessage || 'Please correct the validation errors.',
        confirmButtonColor: '#ef4444'
      });
    }
  }

  function markFieldError(key) {
    if (!key) return;

    if (key === 'packet.name') {
      document.querySelector('[name="packet_name"]')?.classList.add('is-invalid');
      return;
    }

    if (key === 'packet.header_identifier') {
      document.querySelector('[name="packet_header"]')?.classList.add('is-invalid');
      return;
    }

    if (key === 'packet.delimiter') {
      document.querySelector('[name="packet_delimiter"]')?.classList.add('is-invalid');
      return;
    }

    const match = key.match(/^fields\.(\d+)\.(.+)$/);
    if (!match) return;

    const row = document.querySelectorAll('#sortableBody tr')[Number(match[1])];
    if (!row) return;

    row.classList.add('has-error');
    const fieldName = match[2];
    row.querySelector('[name="' + fieldName + '"]')?.classList.add('is-invalid');
  }

  function validateBuilderClientSide() {
    const errors = {};
    const packetName = document.querySelector('[name="packet_name"]').value.trim();
    const rows = document.querySelectorAll('#sortableBody tr');
    const seenNames = {};

    if (!packetName) errors['packet.name'] = ['Packet name is required.'];
    if (rows.length === 0) errors.fields = ['Please add at least one parameter.'];

    rows.forEach((row, index) => {
      const rowNumber = index + 1;
      const name = row.querySelector('[name="name"]').value.trim();
      const length = row.querySelector('[name="length"]').value.trim();
      const validationType = row.querySelector('[name="validation_type"]').value;
      const regexPattern = row.querySelector('[name="regex_pattern"]').value.trim();
      const minValue = row.querySelector('[name="min_value"]').value.trim();
      const maxValue = row.querySelector('[name="max_value"]').value.trim();

      if (!name) errors[`fields.${index}.name`] = [`Parameter name is required on row ${rowNumber}.`];

      const nameKey = name.toLowerCase();
      if (nameKey) {
        if (seenNames[nameKey]) errors[`fields.${index}.name`] = [`Duplicate parameter name found on row ${rowNumber}.`];
        seenNames[nameKey] = true;
      }

      if (length && (!Number.isInteger(Number(length)) || Number(length) <= 0)) {
        errors[`fields.${index}.length`] = [`Length must be a positive whole number on row ${rowNumber}.`];
      }

      if (validationType === 'regex') {
        if (!regexPattern) {
          errors[`fields.${index}.regex_pattern`] = [`Regex pattern is required on row ${rowNumber}.`];
        } else {
          try { new RegExp(regexPattern); } catch (e) { errors[`fields.${index}.regex_pattern`] = [`Regex pattern is invalid on row ${rowNumber}.`]; }
        }
      }

      if (validationType === 'imei' && length && Number(length) !== 15) {
        errors[`fields.${index}.length`] = [`IMEI validation requires length 15 on row ${rowNumber}.`];
      }

      if (minValue !== '' && maxValue !== '' && Number(minValue) > Number(maxValue)) {
        errors[`fields.${index}.min_value`] = [`Minimum range cannot be greater than maximum range on row ${rowNumber}.`];
      }
    });

    return errors;
  }

  function collectBuilderFields() {
    const fields = [];
    document.querySelectorAll('#sortableBody tr').forEach(row => {
      fields.push({
        name: row.querySelector('[name="name"]').value.trim(),
        data_type: row.querySelector('[name="data_type"]').value,
        length: row.querySelector('[name="length"]').value.trim(),
        validation_type: row.querySelector('[name="validation_type"]').value,
        regex_pattern: row.querySelector('[name="regex_pattern"]').value.trim(),
        min_value: row.querySelector('[name="min_value"]').value.trim(),
        max_value: row.querySelector('[name="max_value"]').value.trim(),
        is_required: row.querySelector('[name="is_required"]').checked ? 1 : 0
      });
    });

    return fields;
  }

  function saveMasterConfiguration(btn) {
    clearValidationErrors();

    const clientErrors = validateBuilderClientSide();
    if (Object.keys(clientErrors).length > 0) {
      showValidationErrors(clientErrors, 'Please correct the validation errors.');
      return;
    }

    const packet = {
      id: document.querySelector('[name="packet_id"]').value,
      name: document.querySelector('[name="packet_name"]').value.trim(),
      header_identifier: document.querySelector('[name="packet_header"]').value.trim(),
      delimiter: document.querySelector('[name="packet_delimiter"]').value.trim(),
    };

    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    $.ajax({
      url: "{{ route('protocols.packet-types.store-full', $protocol->id) }}",
      method: 'POST',
      data: { _token: "{{ csrf_token() }}", packet: packet, fields: collectBuilderFields() },
      success: function (res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'Saved!',
            text: res.message || 'Configuration saved successfully.',
            showConfirmButton: false,
            timer: 1500,
            iconColor: 'var(--premium-success)'
          }).then(() => {
            window.location.href = res.redirect;
          });
          return;
        }

        showValidationErrors({}, res.message || 'Unable to save configuration.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      },
      error: function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON) {
          showValidationErrors(xhr.responseJSON.errors || {}, xhr.responseJSON.message);
        } else {
          showValidationErrors({}, 'Server error while saving configuration. Please try again.');
        }
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    });
  }</script>
<style>
  :root {
    --premium-primary: #6366f1;
    --premium-primary-hover: #4f46e5;
    --premium-success: #10b981;
    --premium-info: #0ea5e9;
    --premium-dark: #0f172a;
    --premium-bg: #f1f5f9;
    --glass-bg: rgba(255, 255, 255, 0.7);
    --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --card-shadow-hover: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
  }

  body {
    background-color: var(--premium-bg);
    font-family: 'Inter', sans-serif;
    color: #1e293b;
    line-height: 1.5;
  }

  /* Breadcrumb Styling */
  .c_breadcrumbs ul {
    background: transparent;
    padding: 0;
    margin-bottom: 20px;
  }

  .c_breadcrumbs ul li a {
    color: #64748b;
    font-weight: 500;
    transition: color 0.2s;
  }

  .c_breadcrumbs ul li a:hover {
    color: var(--premium-primary);
  }

  .c_breadcrumbs ul li.active a {
    color: var(--premium-dark);
    font-weight: 700;
  }

  /* Main Panel Styling */
  .c_panel {
    background: transparent;
    border: none;
    box-shadow: none;
  }

  .c_title h2 {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 1.75rem;
    color: var(--premium-dark);
    letter-spacing: -0.02em;
    margin-bottom: 25px;
  }

  .section-card {
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    margin-bottom: 30px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .section-card:hover {
    box-shadow: var(--card-shadow-hover);
    transform: translateY(-2px);
  }

  .section-header {
    background: #ffffff;
    padding: 20px 28px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .section-header h4 {
    color: var(--premium-dark);
    font-weight: 700;
    font-size: 1.1rem;
    font-family: 'Outfit', sans-serif;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .section-header h4 i {
    color: var(--premium-primary);
    background: rgba(99, 102, 241, 0.1);
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
  }

  .section-body {
    padding: 28px;
  }

  .premium-label {
    font-weight: 600;
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 8px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .premium-input,
  .premium-select,
  .premium-textarea {
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    padding: 10px 16px;
    height: 46px;
    transition: all 0.2s ease;
    font-size: 0.95rem;
    font-weight: 500;
    background-color: #f8fafc;
    color: #0f172a;
    font-family: 'Inter', sans-serif;
    width: 100%;
    display: block;
    box-sizing: border-box;
  }

  .premium-input:focus,
  .premium-select:focus,
  .premium-textarea:focus {
    border-color: var(--premium-primary);
    background-color: white;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    outline: none;
  }

  .premium-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    background-size: 16px;
    padding-right: 40px;
    cursor: pointer;
  }

  .table-custom {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }

  .table-custom thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.075em;
    padding: 16px 24px;
    border-bottom: 1px solid #e2e8f0;
    font-family: 'Outfit', sans-serif;
  }

  .table-custom tbody tr {
    transition: all 0.2s;
  }

  .table-custom tbody tr:hover {
    background-color: #f8fafc;
  }

  .table-custom td {
    vertical-align: middle;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
  }

  /* Button Styling */
  .btn-glass-primary {
    background: rgba(99, 102, 241, 0.1);
    color: var(--premium-primary);
    border: 1.5px solid rgba(99, 102, 241, 0.2);
    font-weight: 700;
    font-size: 0.85rem;
    padding: 8px 16px;
    border-radius: 10px;
    transition: all 0.2s;
  }

  .btn-glass-primary:hover {
    background: var(--premium-primary);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
  }

  .btn-glass-info {
    background: rgba(14, 165, 233, 0.1);
    color: var(--premium-info);
    border: 1.5px solid rgba(14, 165, 233, 0.2);
    font-weight: 700;
    font-size: 0.85rem;
    padding: 8px 16px;
    border-radius: 10px;
  }

  .btn-glass-info:hover {
    background: var(--premium-info);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
  }

  /* Modal Styling */
  .modal-content {
    border-radius: 24px;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }

  .modal-header-premium {
    padding: 24px 32px;
    background: white;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%);
    color: var(--premium-primary);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  .close-premium {
    background: #f1f5f9;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #64748b;
    transition: all 0.2s;
  }

  .close-premium:hover {
    background: #fee2e2;
    color: #ef4444;
  }

  .info-card {
    background: rgba(99, 102, 241, 0.05);
    border: 1px solid rgba(99, 102, 241, 0.1);
    border-radius: 16px;
    padding: 16px 20px;
  }

  .premium-label-bold {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--premium-dark);
    margin-bottom: 12px;
    display: block;
    font-family: 'Outfit', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.02em;
  }

  .bg-light-soft {
    background-color: #fafafa;
  }

  .btn-premium-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
    font-weight: 700;
    font-family: 'Outfit', sans-serif;
    padding: 14px 32px;
    border-radius: 14px;
    box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
    transition: all 0.3s;
  }

  .btn-premium-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.4);
    filter: brightness(1.1);
  }

  .btn-premium-primary {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    border: none;
    color: white;
    font-weight: 700;
    border-radius: 12px;
    padding: 12px 24px;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
  }

  /* Animations */
  .animate-in {
    animation: fadeInSlideUp 0.4s ease-out;
  }

  @keyframes fadeInSlideUp {
    from {
      opacity: 0;
      transform: translateY(15px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .drag-handle {
    cursor: grab;
    color: #cbd5e1;
    font-size: 1.1rem;
    transition: color 0.2s;
  }

  .drag-handle:hover {
    color: var(--premium-primary);
  }

  .drag-handle:active {
    cursor: grabbing;
  }

  /* Modal Styling */
  .premium-modal .modal-content {
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }

  .premium-modal .modal-header {
    background: var(--premium-dark);
    padding: 24px 32px;
    border-bottom: none;
  }

  .premium-modal .modal-title {
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    letter-spacing: -0.01em;
  }

  /* Custom Checkbox */
  .custom-checkbox input {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    cursor: pointer;
    accent-color: var(--premium-primary);
  }

  .premium-scroll::-webkit-scrollbar {
    width: 6px;
  }

  .premium-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
  }

  .premium-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }

  .premium-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--premium-primary);
  }

  .d-none {
    display: none !important;
  }

  /* Additional Premium Touches */
  .badge-premium-primary {
    background: rgba(99, 102, 241, 0.1);
    color: var(--premium-primary);
    border: 1.5px solid rgba(99, 102, 241, 0.2);
  }

  .premium-form .section-card {
    border-color: #f1f5f9;
  }

  /* Responsive refinements */
  @media (max-width: 768px) {
    .section-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 15px;
    }

    .header-actions {
      width: 100%;
      display: flex;
      gap: 10px;
    }

    .header-actions button {
      flex: 1;
    }
  }

  /* Existing admin-theme alignment for packet field builder */
  .protocol-builder-page .c_panel {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
  }

  .protocol-builder-page .c_title {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    background: #fff;
  }

  .protocol-builder-page .c_title h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #222;
    font-family: inherit;
  }

  .protocol-builder-page .c_content {
    padding: 20px;
  }

  .protocol-builder-page .section-card {
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    box-shadow: none;
    margin-bottom: 18px;
    overflow: hidden;
  }

  .protocol-builder-page .section-card:hover {
    box-shadow: none;
    transform: none;
  }

  .protocol-builder-page .section-header {
    padding: 13px 16px;
    background: #f8f9fb;
    border-bottom: 1px solid #e5e5e5;
  }

  .protocol-builder-page .section-header h4 {
    font-size: 15px;
    font-weight: 700;
    color: #333;
    font-family: inherit;
  }

  .protocol-builder-page .section-header h4 i {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    color: #3498db;
    background: #eef7ff;
  }

  .protocol-builder-page .section-body {
    padding: 16px;
  }

  .protocol-builder-page .premium-label,
  .protocol-builder-page .premium-label-bold {
    font-size: 12px;
    color: #555;
    font-weight: 700;
    text-transform: none;
    letter-spacing: 0;
    margin-bottom: 6px;
    font-family: inherit;
  }

  .protocol-builder-page .premium-input,
  .protocol-builder-page .premium-select,
  .protocol-builder-page .premium-textarea {
    height: 34px;
    min-height: 34px;
    border-radius: 4px;
    border: 1px solid #ddd;
    background: #fff;
    padding: 6px 10px;
    font-size: 13px;
    font-weight: 400;
    box-shadow: none;
  }

  .protocol-builder-page .premium-textarea {
    height: auto;
  }

  .protocol-builder-page .premium-input:focus,
  .protocol-builder-page .premium-select:focus,
  .protocol-builder-page .premium-textarea:focus {
    border-color: #66afe9;
    box-shadow: 0 0 4px rgba(102, 175, 233, 0.35);
  }

  .protocol-builder-page .premium-scroll {
    max-height: none !important;
    overflow: visible !important;
  }

  .protocol-builder-page .table-responsive {
    overflow-x: visible;
  }

  .protocol-builder-page .table-custom {
    width: 100% !important;
    margin-bottom: 0;
    border-collapse: collapse;
    table-layout: auto;
  }

  .protocol-builder-page .table-custom thead th {
    background: #f8f9fb;
    color: #555;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 10px 8px;
    border: 1px solid #ddd;
    vertical-align: middle;
    font-family: inherit;
  }

  .protocol-builder-page .table-custom td {
    padding: 8px;
    border: 1px solid #e5e5e5;
    vertical-align: top;
  }

  .protocol-builder-page .field-row.has-error {
    background: #fff7f7;
  }

  .protocol-builder-page .is-invalid {
    border-color: #dc3545 !important;
    background: #fff5f5 !important;
  }

  .protocol-builder-page .builder-error-box {
    border-left: 4px solid #dc3545;
    margin-bottom: 15px;
  }

  .protocol-builder-page .builder-error-box ul {
    margin: 8px 0 0 18px;
    padding: 0;
  }

  .protocol-builder-page .btn-glass-info,
  .protocol-builder-page .btn-glass-primary,
  .protocol-builder-page .btn-glass-secondary,
  .protocol-builder-page .btn-premium-success {
    border-radius: 4px;
    font-weight: 600;
    box-shadow: none;
    padding: 8px 14px;
  }

  .protocol-builder-page .btn-premium-success {
    background: #2ecc71;
    color: #fff;
  }

  .protocol-builder-page .btn-premium-success:hover {
    background: #27ae60;
    transform: none;
    box-shadow: none;
    color: #fff;
  }

  .protocol-builder-page .form-actions {
    margin-top: 16px;
  }
</style>
@stop




