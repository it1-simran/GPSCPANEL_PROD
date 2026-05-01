<tr class="field-row animate-in">
    <td class="drag-handle text-center"><i class="fa fa-reorder"></i></td>
    <td><input type="text" name="name" value="{{ $field->name }}" class="form-control premium-input" placeholder="Field Name"></td>
    <td>
        <select name="data_type" class="form-control premium-select">
            <option value="String" {{ $field->data_type == 'String' ? 'selected' : '' }}>String</option>
            <option value="Numeric" {{ $field->data_type == 'Numeric' ? 'selected' : '' }}>Numeric</option>
            <option value="ASCII" {{ $field->data_type == 'ASCII' ? 'selected' : '' }}>ASCII</option>
            <option value="HEX" {{ $field->data_type == 'HEX' ? 'selected' : '' }}>HEX</option>
        </select>
    </td>
    <td><input type="number" name="length" value="{{ $field->length }}" class="form-control premium-input" placeholder="Length"></td>
    <td>
        <select name="validation_type" class="form-control premium-select" onchange="toggleRegex(this)">
            <option value="none" {{ $field->validation_type == 'none' ? 'selected' : '' }}>None</option>
            <option value="imei" {{ $field->validation_type == 'imei' ? 'selected' : '' }}>IMEI</option>
            <option value="date_ddmmyyyy" {{ $field->validation_type == 'date_ddmmyyyy' ? 'selected' : '' }}>Date (DDMMYYYY)</option>
            <option value="time_hhmmss" {{ $field->validation_type == 'time_hhmmss' ? 'selected' : '' }}>Time (HHMMSS)</option>
            <option value="nmea_checksum" {{ $field->validation_type == 'nmea_checksum' ? 'selected' : '' }}>NMEA Checksum (XOR8)</option>
            <option value="xor8" {{ $field->validation_type == 'xor8' ? 'selected' : '' }}>XOR8 Checksum</option>
            <option value="xor16" {{ $field->validation_type == 'xor16' ? 'selected' : '' }}>XOR16 Checksum</option>
            <option value="xor32" {{ $field->validation_type == 'xor32' ? 'selected' : '' }}>XOR32 Checksum</option>
            <option value="sha256" {{ $field->validation_type == 'sha256' ? 'selected' : '' }}>SHA-256 Hash</option>
            <option value="regex" {{ $field->validation_type == 'regex' || $field->regex_pattern ? 'selected' : '' }}>Custom Regex</option>
        </select>
        <input type="text" name="regex_pattern" value="{{ $field->regex_pattern }}" 
            class="form-control mt-1 premium-input {{ $field->validation_type === 'regex' ? '' : 'd-none' }}" 
            style="{{ $field->validation_type === 'regex' ? 'display: block;' : 'display: none;' }}"
            placeholder="Regex Pattern">
    </td>
    <td>
        <div class="d-flex" style="gap: 5px;">
            <input type="number" step="any" name="min_value" value="{{ $field->min_value }}" class="form-control premium-input" placeholder="Min" style="flex:1">
            <input type="number" step="any" name="max_value" value="{{ $field->max_value }}" class="form-control premium-input" placeholder="Max" style="flex:1">
        </div>
    </td>
    <td class="text-center">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="is_required" {{ $field->is_required ? 'checked' : '' }}>
        </div>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-link text-danger btn-sm p-0" onclick="removeRow(this)">
            <i class="fa fa-trash-o fa-lg"></i>
        </button>
    </td>
</tr>
