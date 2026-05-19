@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('protocol-packet-type-builder') }}">
@endpush
@section('content')
<!-- Google Fonts -->
<link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
  rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<section id="main-content" class="protocol-page protocol-builder-page">
  <section class="wrapper">
    @php
      $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
    @endphp
    <div class="protocol-breadcrumb-wrap">
      <nav class="protocol-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ route($routePrefix . '.index') }}" class="bc-item">Protocol Management</a>
        <span class="bc-sep">›</span>
        <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" class="bc-item">Packet Types</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">{{ isset($packetType) ? 'Edit Packet Configuration' : 'Create Packet Configuration' }}</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title pkt-config-heading d-flex justify-content-between align-items-center">
            <h2 class="pkt-config-title m-0">
              <i class="fa fa-cubes"></i>
              Packet Configuration
              <span class="pkt-config-protocol-pill">{{ strtoupper($protocol->name) }}</span>
            </h2>
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
                <a href="{{ route($routePrefix . '.packet-types', $protocol->id) }}" style="margin-top: 10px;"
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
  <div class="modal-dialog modal-dialog-centered" style="max-width:680px;">
    <div class="modal-content az-modal-content">
      {{-- Top accent bar --}}
      <div class="az-accent-bar"></div>
      {{-- Close --}}
      <button type="button" class="az-close" data-dismiss="modal">&times;</button>

      <div class="az-body">
        {{-- Icon + Title --}}
        <div class="az-hero">
          <div class="az-icon-ring">
            <i class="fa fa-magic"></i>
          </div>
          <h3 class="az-title">Smart Packet Analyzer</h3>
          <p class="az-subtitle">Paste a raw packet string and auto-extract all parameters in one click.</p>
        </div>

        {{-- Packet Input --}}
        <div class="az-field-group">
          <label class="az-label"><i class="fa fa-code"></i> Raw Packet String</label>
          <textarea id="samplePacket" class="az-textarea" rows="4"
            placeholder="$NMP,JSD,2.2.6,NR,1,L,860269069112647,0,1,29042026..."></textarea>
        </div>

        {{-- Delimiter --}}
        <div class="az-delim-row">
          <div class="az-field-group az-delim-field">
            <label class="az-label"><i class="fa fa-cut"></i> Delimiter</label>
            <input type="text" id="analyzerDelim" value="," class="az-delim-input" maxlength="1">
          </div>
          <div class="az-hint-box">
            <i class="fa fa-info-circle"></i>
            <span>This will <strong>replace</strong> all current rows with the detected fields.</span>
          </div>
        </div>

        {{-- Actions --}}
        <div class="az-actions">
          <button type="button" class="btn az-btn-cancel" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn az-btn-go" onclick="runAnalysis()">
            <i class="fa fa-bolt"></i> Analyze & Populate
          </button>
        </div>
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
                <option value="nmea_checksum" ${data.validation_type == 'nmea_checksum' ? 'selected' : ''}>NMEA Checksum (XOR8)</option>
                <option value="xor8" ${data.validation_type == 'xor8' ? 'selected' : ''}>XOR8 Checksum</option>
                <option value="xor16" ${data.validation_type == 'xor16' ? 'selected' : ''}>XOR16 Checksum</option>
                <option value="xor32" ${data.validation_type == 'xor32' ? 'selected' : ''}>XOR32 Checksum</option>
                <option value="sha256" ${data.validation_type == 'sha256' ? 'selected' : ''}>SHA-256 Hash</option>
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

    // Robust parser that ignores delimiters inside parentheses and stops at *
    const parts = [];
    let currentPart = "";
    let parenLevel = 0;
    let foundAsterisk = false;

    for (let i = 0; i < rawInput.length; i++) {
      const char = rawInput[i];
      
      // If we hit *, we stop and treat everything from here as the last part (checksum)
      if (char === '*') {
        foundAsterisk = true;
        break;
      }

      if (char === '(') parenLevel++;
      else if (char === ')') parenLevel--;

      if (char === delim && parenLevel === 0) {
        parts.push(currentPart);
        currentPart = "";
      } else {
        currentPart += char;
      }
    }
    
    if (foundAsterisk) {
      if (currentPart !== "" || parts.length > 0) parts.push(currentPart);
    } else {
      parts.push(currentPart);
    }

    // Handle data after asterisk if found
    let securityParts = [];
    if (foundAsterisk) {
      const starIndex = rawInput.indexOf('*');
      const afterStar = rawInput.substring(starIndex + 1).trim();
      
      if (afterStar.length >= 2) {
        securityParts.push({
          name: 'XOR Checksum',
          val: afterStar.substring(0, 2),
          type: 'HEX',
          vType: 'nmea_checksum'
        });
        
        let hashVal = afterStar.substring(2).trim();
        if (hashVal.startsWith(',')) hashVal = hashVal.substring(1).trim();
        
        if (hashVal.length > 0) {
          securityParts.push({
            name: 'SHA-256 Hash',
            val: hashVal,
            type: 'STRING',
            vType: 'sha256'
          });
        }
      }
    }

    const tbody = document.getElementById('sortableBody');
    tbody.innerHTML = '';

    // Add regular parts
    parts.forEach((val, i) => {
      let type = 'String';
      let vType = 'none';
      const trimmedVal = val.trim();
      if (!isNaN(trimmedVal) && trimmedVal !== '') type = 'Numeric';
      else if (/^[0-9a-fA-F]+$/.test(trimmedVal) && trimmedVal.length > 2) type = 'HEX';

      if (trimmedVal.length === 15 && /^\d+$/.test(trimmedVal)) vType = 'imei';
      else if (trimmedVal.length === 8 && /^\d+$/.test(trimmedVal) && i > 5) vType = 'date_ddmmyyyy';
      else if (trimmedVal.length === 6 && /^\d+$/.test(trimmedVal) && i > 5) vType = 'time_hhmmss';

      addNewRow({
        name: i === 0 ? 'Header' : `Param ${i + 1}`,
        length: val.length,
        data_type: type,
        validation_type: vType
      });
    });

    // Add security parts
    securityParts.forEach(sp => {
      addNewRow({
        name: sp.name,
        length: sp.val.length,
        data_type: sp.type,
        validation_type: sp.vType
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
      url: "{{ route($routePrefix . '.packet-types.store-full', $protocol->id) }}",
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

@stop



