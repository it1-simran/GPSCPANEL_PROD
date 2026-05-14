@extends('layouts.apps')
@section('content')
<!-- Google Fonts -->
<link
  href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
  rel="stylesheet">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.protocol-builder-page { font-family: 'Inter', sans-serif; }
#main-content.protocol-builder-page .wrapper { padding-top: 10px !important; }

/* Breadcrumb */
.protocol-breadcrumb-wrap { padding: 4px 0 14px 0; }
.protocol-breadcrumb {
    display: inline-flex; align-items: center; flex-wrap: wrap; row-gap: 6px;
    background: #1e293b; border-radius: 50px; padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
}
.protocol-breadcrumb .bc-home {
    width: 30px; height: 30px; border-radius: 50%; background: #76CF1C;
    display: inline-flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
}
.protocol-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
.protocol-breadcrumb .bc-item { color: rgba(255,255,255,0.7); font-size: 13px; font-weight: 500; text-decoration: none; }
.protocol-breadcrumb .bc-sep { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
.protocol-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
.protocol-breadcrumb a.bc-item:hover { color: #e2e8f0; }

/* Panel */
.protocol-builder-page .c_panel {
    border: none !important; border-radius: 14px !important;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06) !important;
    overflow: hidden !important;
}

/* Config Heading */
.pkt-config-heading {
    background: #1e293b !important; padding: 16px 24px !important;
    border-bottom: none !important;
}
.pkt-config-title {
    color: #fff !important; font-size: 17px !important; font-weight: 800 !important;
    text-transform: uppercase; letter-spacing: 0.4px;
    display: flex !important; align-items: center; gap: 10px;
}
.pkt-config-title > i { color: #76CF1C; font-size: 16px; }
.pkt-config-title h2::before { content: none !important; display: none !important; }
.pkt-config-protocol-pill {
    display: inline-flex; align-items: center; padding: 3px 12px;
    border-radius: 999px; background: rgba(118,207,28,0.16);
    color: #cfff9f; font-size: 11px; font-weight: 700; letter-spacing: 0.4px;
    border: 1px solid rgba(118,207,28,0.36);
}

/* Content Area */
.protocol-builder-page .c_content { padding: 24px !important; background: #f8fafc; }

/* Section Cards */
.section-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    margin-bottom: 20px;
}
.section-header {
    background: #fff; padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.section-header h4 {
    margin: 0; font-size: 14px; font-weight: 800; color: #1e293b;
    text-transform: uppercase; letter-spacing: 0.3px;
    display: flex; align-items: center; gap: 8px;
}
.section-header h4 i { color: #76CF1C; font-size: 14px; }
.section-body { padding: 20px; }

/* Header Actions (Smart Analyzer, Add Parameter) */
.header-actions { display: flex; gap: 8px; }

/* Form Elements */
.premium-label {
    font-size: 11px; font-weight: 700; color: #475569;
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
}
.premium-input, .premium-select {
    border: 1px solid #e2e8f0 !important; border-radius: 8px !important;
    padding: 8px 12px !important; font-size: 13px !important;
    color: #1e293b !important; background: #fff !important;
    height: 40px !important; transition: all 0.2s !important;
    font-weight: 500 !important;
}
.premium-input:focus, .premium-select:focus {
    border-color: #76CF1C !important;
    box-shadow: 0 0 0 3px rgba(118,207,28,0.15) !important;
    outline: none !important;
}
.premium-textarea {
    border: 1px solid #e2e8f0 !important; border-radius: 8px !important;
    padding: 12px !important; font-size: 13px !important; color: #1e293b !important;
    font-weight: 500 !important; resize: vertical;
}
.premium-textarea:focus {
    border-color: #76CF1C !important;
    box-shadow: 0 0 0 3px rgba(118,207,28,0.15) !important;
    outline: none !important;
}
.premium-label-bold { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }

/* Builder Table */
.table-custom { border-collapse: separate !important; border-spacing: 0 !important; border: none !important; }
.table-custom thead th {
    background: #1e293b !important; color: #fff !important;
    font-size: 11px !important; font-weight: 800 !important;
    text-transform: uppercase !important; letter-spacing: 0.5px !important;
    padding: 12px 14px !important; border: none !important;
    white-space: nowrap !important; vertical-align: middle !important;
}
.table-custom thead th:first-child { border-top-left-radius: 10px; }
.table-custom thead th:last-child { border-top-right-radius: 10px; }
.table-custom tbody td {
    padding: 10px 14px !important; border: none !important;
    border-bottom: 1px solid #f1f5f9 !important; vertical-align: middle !important;
    background: #fff !important; font-size: 13px !important;
}
.table-custom tbody tr:hover td { background: #f8fafc !important; }

/* Drag handle */
.drag-handle { cursor: grab; color: #cbd5e1; }
.drag-handle:active { cursor: grabbing; }
.drag-handle i { font-size: 14px; }

/* Row animation */
.field-row.animate-in { animation: fadeSlideIn 0.3s ease forwards; }
.field-row.animate-out { animation: fadeSlideOut 0.3s ease forwards; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeSlideOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateX(30px); } }

/* Buttons */
.btn-glass-primary {
    background: #1e293b !important; color: #fff !important;
    border: none !important; border-radius: 8px !important;
    font-weight: 700 !important; font-size: 12px !important;
    padding: 6px 14px !important; box-shadow: 0 2px 8px rgba(30,41,59,0.2);
}
.btn-glass-primary:hover { background: #0f172a !important; transform: translateY(-1px); }

.btn-glass-info {
    background: linear-gradient(135deg,#76CF1C,#5fa816) !important;
    color: #1e293b !important; border: none !important;
    border-radius: 8px !important; font-weight: 800 !important;
    font-size: 12px !important; padding: 6px 14px !important;
    box-shadow: 0 2px 8px rgba(118,207,28,0.3);
}
.btn-glass-info:hover { box-shadow: 0 4px 12px rgba(118,207,28,0.4); transform: translateY(-1px); }

.btn-glass-secondary {
    background: #e2e8f0 !important; color: #475569 !important;
    border: none !important; border-radius: 8px !important;
    font-weight: 700 !important; font-size: 13px !important; padding: 8px 20px !important;
}
.btn-glass-secondary:hover { background: #cbd5e1 !important; color: #1e293b !important; }

.btn-premium-success {
    background: linear-gradient(135deg,#76CF1C,#5fa816) !important;
    color: #1e293b !important; border: none !important;
    border-radius: 10px !important; font-weight: 800 !important;
    font-size: 14px !important; padding: 10px 28px !important;
    box-shadow: 0 4px 14px rgba(118,207,28,0.35);
    text-transform: uppercase; letter-spacing: 0.4px;
}
.btn-premium-success:hover { box-shadow: 0 6px 20px rgba(118,207,28,0.45); transform: translateY(-2px); }

.btn-premium-primary {
    background: #1e293b !important; color: #fff !important;
    border: none !important; border-radius: 8px !important;
    font-weight: 700 !important; padding: 8px 20px !important;
    box-shadow: 0 3px 10px rgba(30,41,59,0.2);
}
.btn-premium-primary:hover { background: #0f172a !important; transform: translateY(-1px); }

/* Form actions */
.form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

/* Premium scroll */
.premium-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.premium-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
.premium-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* ===== ANALYZER MODAL — Fresh Card Design ===== */
#analyzerModal .az-modal-content {
    border: none !important; border-radius: 20px !important;
    overflow: hidden !important; position: relative;
    box-shadow: 0 30px 80px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.05) !important;
    background: #fff !important;
}
#analyzerModal .az-accent-bar {
    height: 5px; width: 100%;
    background: linear-gradient(90deg, #76CF1C, #1e293b, #76CF1C);
}
#analyzerModal .az-close {
    position: absolute; top: 18px; right: 20px; z-index: 10;
    background: #f1f5f9 !important; border: none !important;
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #64748b !important; font-size: 18px; cursor: pointer;
    transition: all 0.2s;
}
#analyzerModal .az-close:hover {
    background: #e2e8f0 !important; color: #1e293b !important;
    transform: rotate(90deg);
}
#analyzerModal .az-body { padding: 32px 32px 24px; }

/* Hero section */
#analyzerModal .az-hero { text-align: center; margin-bottom: 28px; }
#analyzerModal .az-icon-ring {
    width: 60px; height: 60px; border-radius: 16px; margin: 0 auto 14px;
    background: linear-gradient(135deg, rgba(118,207,28,0.12), rgba(118,207,28,0.05));
    border: 2px solid rgba(118,207,28,0.2);
    display: flex; align-items: center; justify-content: center;
}
#analyzerModal .az-icon-ring i { color: #76CF1C; font-size: 22px; }
#analyzerModal .az-title {
    font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 6px;
    letter-spacing: -0.3px;
}
#analyzerModal .az-subtitle {
    font-size: 13px; color: #64748b; margin: 0; font-weight: 500; line-height: 1.5;
}

/* Field groups */
#analyzerModal .az-field-group { margin-bottom: 18px; }
#analyzerModal .az-label {
    display: block; font-size: 11px; font-weight: 800; color: #1e293b;
    text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px;
}
#analyzerModal .az-label i { color: #76CF1C; margin-right: 5px; font-size: 12px; }
#analyzerModal .az-textarea {
    width: 100%; border: 2px solid #e2e8f0; border-radius: 12px;
    padding: 14px 16px; font-size: 13px; font-family: 'Consolas', monospace;
    color: #1e293b; background: #f8fafc; resize: vertical;
    transition: all 0.2s; line-height: 1.6;
}
#analyzerModal .az-textarea:focus {
    border-color: #76CF1C; background: #fff;
    box-shadow: 0 0 0 4px rgba(118,207,28,0.1);
    outline: none;
}
#analyzerModal .az-textarea::placeholder { color: #94a3b8; font-weight: 400; }

/* Delimiter row */
#analyzerModal .az-delim-row {
    display: flex; gap: 16px; align-items: flex-start; margin-bottom: 24px;
}
#analyzerModal .az-delim-field { flex: 0 0 120px; }
#analyzerModal .az-delim-input {
    width: 100%; height: 52px; border: 2px solid #e2e8f0; border-radius: 12px;
    text-align: center; font-size: 24px; font-weight: 800; color: #1e293b;
    background: #fff; transition: all 0.2s;
}
#analyzerModal .az-delim-input:focus {
    border-color: #76CF1C; outline: none;
    box-shadow: 0 0 0 4px rgba(118,207,28,0.1);
}
#analyzerModal .az-hint-box {
    flex: 1; display: flex; align-items: flex-start; gap: 8px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
    padding: 12px 14px; font-size: 12px; color: #92400e; font-weight: 500;
    line-height: 1.5; margin-top: 26px;
}
#analyzerModal .az-hint-box i { color: #f59e0b; font-size: 14px; margin-top: 1px; flex-shrink: 0; }
#analyzerModal .az-hint-box strong { color: #92400e; font-weight: 800; }

/* Actions */
#analyzerModal .az-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 20px; border-top: 1px solid #f1f5f9;
}
#analyzerModal .az-btn-cancel {
    background: #f1f5f9 !important; color: #475569 !important;
    border: none !important; border-radius: 10px !important;
    font-weight: 700 !important; font-size: 13px !important;
    padding: 10px 22px !important;
}
#analyzerModal .az-btn-cancel:hover { background: #e2e8f0 !important; color: #1e293b !important; }
#analyzerModal .az-btn-go {
    background: linear-gradient(135deg, #76CF1C, #5fa816) !important;
    color: #1e293b !important; border: none !important;
    border-radius: 10px !important; font-weight: 800 !important;
    font-size: 13px !important; padding: 10px 24px !important;
    box-shadow: 0 4px 14px rgba(118,207,28,0.35);
    transition: all 0.2s;
}
#analyzerModal .az-btn-go:hover {
    box-shadow: 0 6px 20px rgba(118,207,28,0.45);
    transform: translateY(-1px);
}
#analyzerModal .az-btn-go i { margin-right: 6px; }

/* Builder table inputs inside cells */
.table-custom tbody td .form-control {
    height: 36px !important; font-size: 12px !important;
    border: 1px solid #e2e8f0 !important; border-radius: 6px !important;
    padding: 4px 8px !important;
}
.table-custom tbody td .form-control:focus {
    border-color: #76CF1C !important;
    box-shadow: 0 0 0 2px rgba(118,207,28,0.12) !important;
}
.table-custom tbody td .btn-link.text-danger { color: #ef4444 !important; opacity: 0.7; }
.table-custom tbody td .btn-link.text-danger:hover { opacity: 1; transform: scale(1.1); }

/* Error box */
.builder-error-box { border-radius: 10px !important; font-size: 13px; font-weight: 600; }

@media (max-width: 768px) {
    .header-actions { flex-wrap: wrap; }
    .form-actions { flex-direction: column; }
}
</style>
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

  /* Compact top spacing + modern breadcrumb */
  .protocol-builder-page .wrapper {
    padding-top: 8px !important;
  }

  .protocol-builder-page .protocol-breadcrumb-wrap {
    padding: 4px 0 12px 0 !important;
    margin: 0 !important;
  }

  .protocol-builder-page .protocol-breadcrumb {
    display: inline-flex !important;
    align-items: center !important;
    background: #1e293b !important;
    border-radius: 50px !important;
    padding: 6px 18px 6px 8px !important;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18) !important;
  }

  .protocol-builder-page .protocol-breadcrumb .bc-home {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #76CF1C;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
  }

  .protocol-builder-page .protocol-breadcrumb .bc-home i {
    color: #1e293b;
    font-size: 13px;
  }

  .protocol-builder-page .protocol-breadcrumb .bc-item {
    color: rgba(255, 255, 255, 0.72);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
  }

  .protocol-builder-page .protocol-breadcrumb .bc-sep {
    color: rgba(255, 255, 255, 0.35);
    margin: 0 8px;
    font-size: 12px;
  }

  .protocol-builder-page .protocol-breadcrumb .bc-item.active {
    color: #76CF1C;
    font-weight: 700;
  }

  /* Main Panel Styling */
  .c_panel {
    background: transparent;
    border: none;
    box-shadow: none;
  }

  /* Main panel title uses .pkt-config-heading + .pkt-config-title (dark bar, pill protocol) */
  .protocol-builder-page .c_title:not(.pkt-config-heading) h2 {
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

  .protocol-builder-page .c_title.pkt-config-heading {
    padding: 14px 22px !important;
    margin-top: 4px !important;
    margin-bottom: 0 !important;
    background: #0f172a !important;
    border-bottom: none !important;
    border-radius: 14px 14px 0 0 !important;
  }

  .protocol-builder-page .pkt-config-title {
    display: inline-flex !important;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 !important;
    color: #ffffff !important;
    font-size: 19px !important;
    font-weight: 800 !important;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    font-family: 'Inter', sans-serif !important;
  }

  .protocol-builder-page .pkt-config-title > i.fa {
    color: #76CF1C;
    font-size: 15px;
    width: 22px;
    text-align: center;
  }

  /* Same pill as Packet Types list (protocol name) */
  .protocol-builder-page .pkt-config-protocol-pill {
    display: inline-flex;
    align-items: center;
    margin-left: 4px;
    padding: 4px 11px;
    border-radius: 999px;
    background: rgba(118, 207, 28, 0.16);
    color: #cfff9f !important;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    border: 1px solid rgba(118, 207, 28, 0.36);
  }

  .protocol-builder-page .c_title.pkt-config-heading h2::before {
    content: none !important;
    display: none !important;
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

  /* ===== Narrow screens: full-width breadcrumb, tighter padding, scrollable builder table ===== */
  @media (max-width: 767px) {
    .protocol-builder-page > section.wrapper {
      padding-left: 8px !important;
      padding-right: 8px !important;
      box-sizing: border-box !important;
    }

    /* Bleed breadcrumb to edges (wrapper uses horizontal padding) */
    .protocol-builder-page .protocol-breadcrumb-wrap {
      width: calc(100% + 16px) !important;
      max-width: none !important;
      margin-left: -8px !important;
      margin-right: -8px !important;
      padding-top: 2px !important;
      padding-bottom: 10px !important;
    }

    .protocol-builder-page .protocol-breadcrumb {
      display: flex !important;
      flex-wrap: wrap !important;
      align-items: flex-start !important;
      align-content: flex-start !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      border-radius: 0 !important;
      padding: 12px 14px !important;
      row-gap: 10px !important;
      column-gap: 6px !important;
    }

    .protocol-builder-page .protocol-breadcrumb .bc-home {
      margin-right: 10px !important;
      flex-shrink: 0 !important;
    }

    .protocol-builder-page .protocol-breadcrumb .bc-item,
    .protocol-builder-page .protocol-breadcrumb .bc-sep {
      font-size: 12px !important;
    }

    /* Long page title on its own row, aligned with text block */
    .protocol-builder-page .protocol-breadcrumb .bc-item.active {
      flex: 1 1 100% !important;
      margin-left: 0 !important;
      padding-top: 8px !important;
      margin-top: 2px !important;
      border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
      line-height: 1.35 !important;
    }

    .protocol-builder-page .row {
      margin-left: -6px !important;
      margin-right: -6px !important;
    }

    .protocol-builder-page .row > [class*="col-"] {
      padding-left: 6px !important;
      padding-right: 6px !important;
    }

    .protocol-builder-page .c_content {
      padding: 12px 10px !important;
    }

    .protocol-builder-page .c_title.pkt-config-heading {
      padding: 12px 14px !important;
      border-radius: 12px 12px 0 0 !important;
    }

    .protocol-builder-page .pkt-config-title {
      font-size: 15px !important;
      line-height: 1.35 !important;
    }

    .protocol-builder-page .pkt-config-protocol-pill {
      font-size: 10px !important;
      padding: 3px 8px !important;
    }

    .protocol-builder-page .section-body {
      padding: 12px 10px !important;
    }

    .protocol-builder-page .section-header {
      padding: 12px 12px !important;
    }

    .protocol-builder-page .table-responsive {
      overflow-x: auto !important;
      -webkit-overflow-scrolling: touch !important;
      max-width: 100% !important;
    }

    .protocol-builder-page .premium-scroll {
      max-height: min(70vh, 520px) !important;
      overflow-y: auto !important;
    }

    .protocol-builder-page .form-actions {
      flex-direction: column-reverse !important;
      align-items: stretch !important;
      gap: 10px !important;
    }

    .protocol-builder-page .form-actions .btn {
      width: 100% !important;
      margin-top: 0 !important;
    }
  }
</style>
@stop




