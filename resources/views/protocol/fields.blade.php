@extends('layouts.apps')
@section('content')
<section id="main-content" class="protocol-page protocol-fields-page">
  <section class="wrapper">
    @php
      $routePrefix = Auth::user()->user_type == 'Support' ? 'support.protocols' : 'protocols';
    @endphp
    <div class="protocol-breadcrumb-wrap">
      <nav class="protocol-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ route($routePrefix . '.index') }}" class="bc-item">Protocol Management</a>
        <span class="bc-sep">›</span>
        <a href="{{ route($routePrefix . '.packet-types', $packetType->protocol_id) }}" class="bc-item">Packet Types</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Fields Builder</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row">
              <div class="col-lg-6">
                <h2>Parameter Builder: {{ $packetType->name }}</h2>
              </div>
              <div class="col-lg-6 text-right">
                <button type="button" class="btn btn-info" onclick="showAnalyzer()">Analyze Sample</button>
                <button type="button" class="btn btn-primary" onclick="addNewRow()">Add Parameter</button>
              </div>
            </div>
          </div>

          <div class="c_content">
            <div class="alert alert-info">
              <i class="fa fa-info-circle"></i> Drag rows to reorder. Changes are not saved until you click <strong>Save
                Configuration</strong>.
            </div>

            <form id="fieldsForm">
              <table class="table table-hover table-bordered" id="fieldsTable">
                <thead>
                  <tr class="bg-light">
                    <th width="30">#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Len Type</th>
                    <th>Length</th>
                    <th>Validation</th>
                    <th>Fixed Val</th>
                    <th width="50"></th>
                  </tr>
                </thead>
                <tbody id="sortableBody">
                  @foreach ($fields as $field)
                    <tr class="field-row" data-id="{{ $field->id }}">
                      <td class="drag-handle"><i class="fa fa-reorder text-muted"></i></td>
                      <td><input type="text" name="name" value="{{ $field->name }}" class="form-control input-sm"
                          placeholder="Field Name"></td>
                      <td>
                        <select name="data_type" class="form-control input-sm">
                          <option value="ASCII" {{ $field->data_type == 'ASCII' ? 'selected' : '' }}>ASCII</option>
                          <option value="Numeric" {{ $field->data_type == 'Numeric' ? 'selected' : '' }}>Numeric</option>
                          <option value="HEX" {{ $field->data_type == 'HEX' ? 'selected' : '' }}>HEX</option>
                        </select>
                      </td>
                      <td>
                        <select name="length_type" class="form-control input-sm">
                          <option value="Fixed" {{ $field->length_type == 'Fixed' ? 'selected' : '' }}>Fixed</option>
                          <option value="Variable" {{ $field->length_type == 'Variable' ? 'selected' : '' }}>Variable
                          </option>
                        </select>
                      </td>
                      <td><input type="number" name="length" value="{{ $field->length }}" class="form-control input-sm"
                          style="width: 60px"></td>
                      <td>
                        <select name="validation_type" class="form-control input-sm">
                          <option value="none" {{ $field->validation_type == 'none' ? 'selected' : '' }}>None</option>
                          <option value="imei" {{ $field->validation_type == 'imei' ? 'selected' : '' }}>IMEI</option>
                          <option value="date_ddmmyyyy" {{ $field->validation_type == 'date_ddmmyyyy' ? 'selected' : '' }}>
                            Date</option>
                          <option value="time_hhmmss" {{ $field->validation_type == 'time_hhmmss' ? 'selected' : '' }}>Time
                          </option>
                          <option value="nmea_checksum" {{ $field->validation_type == 'nmea_checksum' ? 'selected' : '' }}>
                            NMEA Checksum</option>
                        </select>
                      </td>
                      <td><input type="text" name="fixed_value" value="{{ $field->fixed_value }}"
                          class="form-control input-sm" placeholder="Fixed"></td>
                      <td><button type="button" class="btn btn-danger btn-xs" onclick="removeRow(this)"><i
                            class="fa fa-trash"></i></button></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>

              <div class="text-center margin-top-20">
                <button type="button" class="btn btn-success btn-lg px-5" onclick="saveConfiguration()">
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

<!-- Analyzer Modal -->
<div class="modal" id="analyzerModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Paste Sample Packet</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Raw String</label>
          <textarea id="samplePacket" class="form-control" rows="4"
            placeholder="Paste e.g. $NMP,JSD,2.2.6..."></textarea>
        </div>
        <div class="form-group">
          <label>Delimiter</label>
          <input type="text" id="delimiter" value="," class="form-control" style="width: 50px">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="runAnalysis()">Analyze & Replace Fields</button>
      </div>
    </div>
  </div>
</div>

@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
  $(document).ready(function () {
    new Sortable(document.getElementById('sortableBody'), {
      handle: '.drag-handle',
      animation: 150,
      ghostClass: 'bg-info-light'
    });
  });

  function addNewRow(data = {}) {
    const html = `
        <tr class="field-row">
            <td class="drag-handle"><i class="fa fa-reorder text-muted"></i></td>
            <td><input type="text" name="name" value="${data.name || ''}" class="form-control input-sm" placeholder="Field Name"></td>
            <td>
                <select name="data_type" class="form-control input-sm">
                    <option value="ASCII" ${data.data_type == 'ASCII' ? 'selected' : ''}>ASCII</option>
                    <option value="Numeric" ${data.data_type == 'Numeric' ? 'selected' : ''}>Numeric</option>
                    <option value="HEX" ${data.data_type == 'HEX' ? 'selected' : ''}>HEX</option>
                </select>
            </td>
            <td>
                <select name="length_type" class="form-control input-sm">
                    <option value="Fixed" ${data.length_type == 'Fixed' ? 'selected' : ''}>Fixed</option>
                    <option value="Variable" ${data.length_type == 'Variable' ? 'selected' : ''}>Variable</option>
                </select>
            </td>
            <td><input type="number" name="length" value="${data.length || ''}" class="form-control input-sm" style="width: 60px"></td>
            <td>
                <select name="validation_type" class="form-control input-sm">
                    <option value="none" ${data.validation_type == 'none' ? 'selected' : ''}>None</option>
                    <option value="imei" ${data.validation_type == 'imei' ? 'selected' : ''}>IMEI</option>
                    <option value="date_ddmmyyyy" ${data.validation_type == 'date_ddmmyyyy' ? 'selected' : ''}>Date</option>
                    <option value="time_hhmmss" ${data.validation_type == 'time_hhmmss' ? 'selected' : ''}>Time</option>
                    <option value="nmea_checksum" ${data.validation_type == 'nmea_checksum' ? 'selected' : ''}>NMEA Checksum</option>
                </select>
            </td>
            <td><input type="text" name="fixed_value" value="${data.fixed_value || ''}" class="form-control input-sm"></td>
            <td><button type="button" class="btn btn-danger btn-xs" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
        </tr>`;
    $('#sortableBody').append(html);
  }

  function removeRow(btn) {
    if (confirm('Remove this parameter?')) {
      $(btn).closest('tr').remove();
    }
  }

  function showAnalyzer() {
    $('#analyzerModal').modal('show');
  }

  function runAnalysis() {
    const raw = $('#samplePacket').val();
    const delim = $('#delimiter').val();
    if (!raw) return;

    const parts = raw.split(delim);
    if (confirm(`Detected ${parts.length} parts. This will clear existing fields. Continue?`)) {
      $('#sortableBody').empty();
      parts.forEach((val, i) => {
        let data = { name: `Param ${i + 1}`, length: val.length };

        // Smart guessing
        if (val.length === 15 && /^\d+$/.test(val)) {
          data.name = 'IMEI';
          data.validation_type = 'imei';
          data.data_type = 'Numeric';
        } else if (i === 0) {
          data.name = 'Header';
          data.fixed_value = val;
        } else if (val.startsWith('*')) {
          data.name = 'Checksum';
          data.validation_type = 'nmea_checksum';
        }

        addNewRow(data);
      });
      $('#analyzerModal').modal('hide');
    }
  }

  function saveConfiguration() {
    const fields = [];
    $('#sortableBody tr').each(function () {
      const row = $(this);
      fields.push({
        name: row.find('[name="name"]').val(),
        data_type: row.find('[name="data_type"]').val(),
        length_type: row.find('[name="length_type"]').val(),
        length: row.find('[name="length"]').val(),
        validation_type: row.find('[name="validation_type"]').val(),
        fixed_value: row.find('[name="fixed_value"]').val(),
      });
    });

    $.ajax({
      url: "{{ route('protocols.fields.update', $packetType->id) }}",
      method: 'POST',
      data: {
        _token: "{{ csrf_token() }}",
        fields: fields
      },
      success: function (res) {
        alert('Protocol Configuration Saved Successfully!');
        window.location.reload();
      },
      error: function () {
        alert('Error saving configuration.');
      }
    });
  }
</script>

<style>
  .protocol-fields-page .wrapper {
    padding-top: 8px !important;
  }

  .protocol-fields-page .protocol-breadcrumb-wrap {
    padding: 4px 0 12px 0 !important;
    margin: 0 !important;
  }

  .protocol-fields-page .protocol-breadcrumb {
    display: inline-flex !important;
    align-items: center !important;
    background: #1e293b !important;
    border-radius: 50px !important;
    padding: 6px 18px 6px 8px !important;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18) !important;
  }

  .protocol-fields-page .protocol-breadcrumb .bc-home {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #76CF1C;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
  }

  .protocol-fields-page .protocol-breadcrumb .bc-home i {
    color: #1e293b;
    font-size: 13px;
  }

  .protocol-fields-page .protocol-breadcrumb .bc-item {
    color: rgba(255, 255, 255, 0.7);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
  }

  .protocol-fields-page .protocol-breadcrumb .bc-sep {
    color: rgba(255, 255, 255, 0.35);
    margin: 0 8px;
    font-size: 12px;
  }

  .protocol-fields-page .protocol-breadcrumb .bc-item.active {
    color: #76CF1C;
    font-weight: 700;
  }

  .drag-handle {
    cursor: move;
  }

  .bg-info-light {
    background-color: #e3f2fd !important;
  }

  .field-row:hover {
    background-color: #f8f9fa;
  }
</style>
@stop