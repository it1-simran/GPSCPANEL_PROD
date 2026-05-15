@extends('layouts.apps')


@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/packet-analyzer-index.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/packet-analyzer-index.css')) }}">
@endpush
@section('content')


<section id="main-content">
    <section class="wrapper">
        <div class="main-content-panel">
            <div class="panel-header-custom">
                <div style="display: flex; align-items: center;">
                    <i class="fa fa-search" style="margin-right: 15px; font-size: 20px; color: #76CF1C;"></i>
                    <strong style="font-size: 16px; letter-spacing: 0.5px;">PACKET ANALYZER & IDENTIFIER</strong>
                </div>
                <div style="font-size: 11px; opacity: 0.8; font-weight: 600;">UTILITY v2.0</div>
            </div>
            <div style="padding: 25px;">
                <label style="display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px;">Input Raw String</label>
                <textarea id="raw_packet_input" class="packet-input-box" rows="3" placeholder="Paste packet string here..."></textarea>
                
                <div style="margin-top: 20px; display: flex; align-items: center; gap: 20px;">
                    <button id="analyze_btn" class="btn-green-premium">
                        <i class="fa fa-bolt" style="margin-right: 8px;"></i> Analyze Now
                    </button>
                    <div id="loading_indicator" style="display:none; color: #64748b; font-size: 13px; font-weight: 600;">
                        <i class="fa fa-spinner fa-spin" style="margin-right: 8px; color: #76CF1C;"></i> Scanning protocols...
                    </div>
                </div>
            </div>
        </div>

        <div id="results_container" style="display:none;">
            <h4 style="font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center;">
                <span style="width: 4px; height: 18px; background: #76CF1C; border-radius: 2px; margin-right: 10px;"></span>
                DETECTION RESULTS
            </h4>
            <div id="results_content"></div>
        </div>
    </section>
</section>

<script type="text/template" id="result-template">
    <div class="analysis-result-card">
        <div class="card-status-bar {status-class}"></div>
        <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleCard({id})">
            <div>
                <h4 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 16px;">
                    <i class="fa fa-chevron-down" id="toggle_icon_{id}" style="margin-right: 10px; font-size: 12px; transition: transform 0.3s; transform: rotate(-90deg);"></i>
                    {protocol_name} <i class="fa fa-angle-right" style="margin: 0 8px; color: #94a3b8;"></i> {packet_type_name}
                </h4>
                <div style="font-size: 12px; margin-top: 5px; margin-left: 22px; color: #64748b; font-weight: 600;">
                    <i class="fa {icon-class}" style="margin-right: 5px;"></i> {status_label} {error_count_text}
                </div>
            </div>
            <div style="text-align: right; display: flex; align-items: center; gap: 15px;">
                <span class="badge" style="background: #f1f5f9; color: #1e293b; font-weight: 800; padding: 6px 12px;">{score}% MATCH</span>
            </div>
        </div>

        <div id="card_body_{id}" style="display: none;">
            <div class="raw-data-well">
                <span style="color: #76CF1C; font-weight: 800; margin-right: 10px;">RAW:</span> {raw_packet}
            </div>

            <div id="errors_list_placeholder_{id}"></div>

            <table class="field-table-premium">
                <thead>
                    <tr>
                        <th>Field Name</th>
                        <th>Extracted Value</th>
                        <th>Type</th>
                        <th style="text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    {fields_rows}
                </tbody>
            </table>
        </div>
    </div>
</script>

<script>
    function toggleCard(id) {
        const body = document.getElementById(`card_body_${id}`);
        const icon = document.getElementById(`toggle_icon_${id}`);
        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.style.transform = 'rotate(0deg)';
        } else {
            body.style.display = 'none';
            icon.style.transform = 'rotate(-90deg)';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('analyze_btn').addEventListener('click', function() {
            const rawPacket = document.getElementById('raw_packet_input').value.trim();
            if (!rawPacket) return;

            const btn = document.getElementById('analyze_btn');
            const loader = document.getElementById('loading_indicator');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin" style="margin-right: 8px;"></i> Analyzing...';
            loader.style.display = 'block';

            fetch(window.location.pathname + '/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ raw_packet: rawPacket })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-bolt" style="margin-right: 8px;"></i> Analyze Now';
                loader.style.display = 'none';
                
                if (data.matches && data.matches.length > 0) {
                    renderResults(data.matches);
                    document.getElementById('results_container').style.display = 'block';
                } else {
                    alert('No protocol match detected.');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-bolt" style="margin-right: 8px;"></i> Analyze Now';
                loader.style.display = 'none';
            });
        });

        function renderResults(matches) {
            const template = document.getElementById('result-template').innerHTML;
            let finalHtml = '';

            matches.forEach((match, index) => {
                const isPass = match.is_valid;
                const statusClass = isPass ? 'status-pass' : 'status-fail';
                const iconClass = isPass ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                const errCount = Object.keys(match.errors).length;
                const matchScore = isPass ? '100' : Math.max(0, 100 - errCount * 10);

                let fieldsHtml = '';
                if (match.field_summary) {
                    match.field_summary.forEach(f => {
                        const statusIcon = f.is_valid ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
                        fieldsHtml += `
                            <tr>
                                <td class="field-name-col">${f.name}</td>
                                <td class="field-value-col">${f.value !== null && f.value !== undefined ? f.value : '-'}</td>
                                <td><span style="color:#94a3b8; font-size:11px;">${f.data_type || 'STR'}</span></td>
                                <td style="text-align:center;">${statusIcon}</td>
                            </tr>
                        `;
                    });
                }

                let errorsHtml = '';
                if (errCount > 0) {
                    errorsHtml = '<div style="margin: 0 20px 20px 20px; padding: 15px; background: #fff5f5; border-radius: 6px; border: 1px solid #fee2e2;">';
                    errorsHtml += '<strong style="color:#b91c1c; font-size:12px; text-transform:uppercase;">Validation Errors:</strong>';
                    errorsHtml += '<ul style="margin:10px 0 0 0; padding-left:15px; font-size:13px; color:#b91c1c;">';
                    for (const [key, msg] of Object.entries(match.errors)) {
                        errorsHtml += `<li><b>${key}:</b> ${msg}</li>`;
                    }
                    errorsHtml += '</ul></div>';
                }

                let html = template
                    .replace(/{id}/g, index)
                    .replace(/{status-class}/g, statusClass)
                    .replace(/{protocol_name}/g, match.protocol_name)
                    .replace(/{packet_type_name}/g, match.packet_type_name)
                    .replace(/{icon-class}/g, iconClass)
                    .replace(/{status_label}/g, match.label)
                    .replace(/{error_count_text}/g, errCount > 0 ? `(${errCount} issues)` : '')
                    .replace(/{score}/g, matchScore)
                    .replace(/{raw_packet}/g, match.raw_packet)
                    .replace(/{fields_rows}/g, fieldsHtml);

                // Use a dedicated placeholder for errors
                html = html.replace(`<div id="errors_list_placeholder_${index}"></div>`, errorsHtml);
                
                finalHtml += html;
            });

            document.getElementById('results_content').innerHTML = finalHtml;
        }
    });
</script>
@endsection


