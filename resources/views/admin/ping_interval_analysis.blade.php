@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('ping-interval-analysis') }}">
@endpush

@section('content')
<section id="main-content">
    <section class="wrapper pia-page">

        <div class="pia-breadcrumb-wrap">
            <nav class="pia-breadcrumb">
                <div class="bc-home"><i class="fa fa-home"></i></div>
                <a href="{{ url('admin') }}" class="bc-item">Home</a>
                <span class="bc-sep">›</span>
                <span class="bc-item active">Ping Analysis</span>
            </nav>
        </div>

        <div class="pia-header">
            <div>
                <h1>Ping Analysis</h1>
                <p>Top devices by ping count — same data source as the admin dashboard.</p>
            </div>
            <div class="pia-header-actions">
                <button type="button" class="pia-btn pia-btn-secondary" id="piaRefreshBtn">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
                <a href="{{ url('admin/ping-interval-analysis/export') }}" class="pia-btn pia-btn-primary" id="piaExportBtn">
                    <i class="fa fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        <div class="pia-summary-grid" id="piaSummaryGrid">
            <div class="pia-summary-card">
                <span class="pia-summary-label">Total Devices</span>
                <strong class="pia-summary-value" id="piaTotalDevices">—</strong>
            </div>
            <div class="pia-summary-card">
                <span class="pia-summary-label">Total Pings</span>
                <strong class="pia-summary-value" id="piaTotalPings">—</strong>
            </div>
            <div class="pia-summary-card">
                <span class="pia-summary-label">Today's Pings</span>
                <strong class="pia-summary-value" id="piaTodayPings">—</strong>
            </div>
            <div class="pia-summary-card">
                <span class="pia-summary-label">Last Updated</span>
                <strong class="pia-summary-value pia-summary-value--sm" id="piaLastUpdated">—</strong>
            </div>
        </div>

        <div class="pia-panel">
            <div class="pia-panel-head">
                <div>
                    <h2>Top Devices by Pings</h2>
                    <p class="pia-panel-sub">Compare total pings and today's pings for the selected top range.</p>
                </div>
                <div class="pia-filters">
                    <label for="piaTopLimit">Show</label>
                    <select id="piaTopLimit" class="pia-select">
                        <option value="10">Top 10</option>
                        <option value="25">Top 25</option>
                        <option value="50" selected>Top 50</option>
                        <option value="100">Top 100</option>
                    </select>
                </div>
            </div>
            <div class="row pia-charts-row">
                <div class="col-md-6">
                    <h3 class="pia-chart-title">Total Pings</h3>
                    <div class="pia-chart-wrap">
                        <canvas id="piaTotalChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3 class="pia-chart-title">Today's Pings</h3>
                    <div class="pia-chart-wrap">
                        <canvas id="piaTodayChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="pia-panel">
            <div class="pia-panel-head pia-panel-head--stack">
                <div>
                    <h2>Device List</h2>
                    <p class="pia-panel-sub" id="piaListSubtitle">Showing top 50 devices by pings. Search overrides this filter.</p>
                </div>
                <div class="pia-list-toolbar">
                    <div class="pia-filters">
                        <label for="piaListTopLimit">Top</label>
                        <select id="piaListTopLimit" class="pia-select">
                            <option value="10">Top 10</option>
                            <option value="25">Top 25</option>
                            <option value="50" selected>Top 50</option>
                            <option value="100">Top 100</option>
                        </select>
                    </div>
                    <div class="pia-search-row">
                        <div class="pia-search-wrap">
                            <i class="fa fa-search"></i>
                            <input type="text" id="piaSearchInput" class="pia-search-input" placeholder="Search by Device ID, Name, or IMEI..." autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pia-table-scroll">
                <table class="pia-table" id="piaDeviceTable">
                    <thead>
                        <tr>
                            <th data-sort="id">Device ID</th>
                            <th data-sort="name">Device Name</th>
                            <th data-sort="imei">IMEI</th>
                            <th data-sort="total_pings" class="is-sorted desc">Pings</th>
                            <th data-sort="today_pings">Today's Pings</th>
                            <th>Ping Interval</th>
                            <th data-sort="last_ping">Last Ping</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="piaDeviceTableBody">
                        <tr><td colspan="8" class="pia-empty">Loading devices...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pia-pagination" id="piaPagination"></div>
        </div>

    </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    var baseUrl = @json(url('admin/ping-interval-analysis'));
    var totalChartInstance = null;
    var todayChartInstance = null;
    var currentPage = 1;
    var currentSort = 'total_pings';
    var currentDirection = 'desc';
    var searchTimer = null;
    var searchKeyword = '';

    function getTopLimit() {
        return document.getElementById('piaTopLimit').value;
    }

    function updateListSubtitle() {
        var el = document.getElementById('piaListSubtitle');
        if (!el) return;
        if (searchKeyword) {
            el.textContent = 'Search results (top filter disabled while searching).';
            return;
        }
        el.textContent = 'Showing top ' + getTopLimit() + ' devices by pings.';
    }

    function apiUrl(path, params) {
        var url = baseUrl + path;
        var merged = Object.assign({ _: Date.now() }, params || {});
        var qs = new URLSearchParams(merged).toString();
        return qs ? url + '?' + qs : url;
    }

    function setSummaryLoading(isLoading) {
        var ids = ['piaTotalDevices', 'piaTotalPings', 'piaTodayPings', 'piaLastUpdated'];
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.textContent = isLoading ? '…' : el.textContent;
        });
    }

    function setLoading(isLoading) {
        document.getElementById('piaRefreshBtn').disabled = isLoading;
    }

    function statusClass(status) {
        if (status === 'Online') return 'pia-status pia-status--online';
        return 'pia-status pia-status--offline';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function loadSummary() {
        setSummaryLoading(true);
        return fetch(apiUrl('/summary'), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' } })
            .then(function (r) { if (!r.ok) throw new Error('Summary failed'); return r.json(); })
            .then(function (data) {
                document.getElementById('piaTotalDevices').textContent = Number(data.total_devices || 0).toLocaleString();
                document.getElementById('piaTotalPings').textContent = Number(data.total_pings || 0).toLocaleString();
                document.getElementById('piaTodayPings').textContent = Number(data.today_pings || 0).toLocaleString();
                document.getElementById('piaLastUpdated').textContent = data.last_updated || '—';
            });
    }

    function deviceLabels(devices) {
        return devices.map(function (d) {
            var label = d.imei || 'N/A';
            return label.length > 18 ? label.slice(0, 16) + '…' : label;
        });
    }

    function tooltipLines(d) {
        return [
            'Device ID: ' + d.id,
            'Pings: ' + Number(d.total_pings || 0).toLocaleString(),
            'Today\'s pings: ' + Number(d.today_pings || 0).toLocaleString(),
            'Ping interval: ' + (d.ping_interval_label || 'N/A'),
            'Last ping: ' + (d.last_ping_time || 'N/A'),
            'Last settings update: ' + (d.last_settings_update || 'N/A'),
            'Status: ' + (d.status || 'Offline')
        ];
    }

    function renderBarChart(canvasId, instanceKey, devices, valueKey, label, color) {
        var canvas = document.getElementById(canvasId);
        var labels = deviceLabels(devices);
        var values = devices.map(function (d) { return d[valueKey] || 0; });

        if (instanceKey === 'total' && totalChartInstance) totalChartInstance.destroy();
        if (instanceKey === 'today' && todayChartInstance) todayChartInstance.destroy();

        var chart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: color,
                    borderColor: color.replace('0.75', '1'),
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function (items) {
                                var idx = items[0].dataIndex;
                                return devices[idx].imei || 'N/A';
                            },
                            label: function (item) {
                                return tooltipLines(devices[item.dataIndex]);
                            }
                        }
                    }
                },
                scales: {
                    x: { ticks: { maxRotation: 45, minRotation: 0 } },
                    y: { beginAtZero: true, title: { display: true, text: label } }
                }
            }
        });

        if (instanceKey === 'total') totalChartInstance = chart;
        else todayChartInstance = chart;
    }

    function loadCharts() {
        var limit = getTopLimit();
        return Promise.all([
            fetch(apiUrl('/devices', { limit: limit, metric: 'total' }), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' } })
                .then(function (r) { if (!r.ok) throw new Error('Total chart failed'); return r.json(); }),
            fetch(apiUrl('/devices', { limit: limit, metric: 'today' }), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' } })
                .then(function (r) { if (!r.ok) throw new Error('Today chart failed'); return r.json(); })
        ]).then(function (results) {
            renderBarChart('piaTotalChart', 'total', results[0].devices || [], 'total_pings', 'Total pings', 'rgba(118, 207, 28, 0.75)');
            renderBarChart('piaTodayChart', 'today', results[1].devices || [], 'today_pings', 'Today\'s pings', 'rgba(37, 99, 235, 0.75)');
        });
    }

    function renderTableRows(devices) {
        var tbody = document.getElementById('piaDeviceTableBody');
        if (!devices.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="pia-empty">No devices found.</td></tr>';
            return;
        }

        tbody.innerHTML = devices.map(function (d) {
            var pingInterval = d.ping_interval_days != null ? String(d.ping_interval_days) : 'N/A';
            return '<tr class="pia-row">' +
                '<td>' + d.id + '</td>' +
                '<td>' + escapeHtml(d.name) + '</td>' +
                '<td><code>' + escapeHtml(d.imei) + '</code></td>' +
                '<td><strong>' + Number(d.total_pings || 0).toLocaleString() + '</strong></td>' +
                '<td>' + Number(d.today_pings || 0).toLocaleString() + '</td>' +
                '<td>' + escapeHtml(pingInterval) + '</td>' +
                '<td>' + escapeHtml(d.last_ping_time || 'N/A') + '</td>' +
                '<td><span class="' + statusClass(d.status) + '">' + escapeHtml(d.status) + '</span></td>' +
                '</tr>';
        }).join('');
    }

    function renderPagination(pagination) {
        var el = document.getElementById('piaPagination');
        if (!pagination) {
            el.innerHTML = '';
            return;
        }

        var info = pagination.total + ' device(s)';
        if (pagination.top_limit && !searchKeyword) {
            info = 'Top ' + pagination.top_limit + ' by pings · ' + info;
        }

        if (pagination.last_page <= 1) {
            el.innerHTML = '<span class="pia-page-info">' + info + '</span>';
            return;
        }

        var html = '<span class="pia-page-info">' + info + ' · Page ' + pagination.page + ' of ' + pagination.last_page + '</span><div class="pia-page-btns">';
        if (pagination.page > 1) {
            html += '<button type="button" class="pia-page-btn" data-page="' + (pagination.page - 1) + '">Prev</button>';
        }
        if (pagination.page < pagination.last_page) {
            html += '<button type="button" class="pia-page-btn" data-page="' + (pagination.page + 1) + '">Next</button>';
        }
        html += '</div>';
        el.innerHTML = html;
    }

    function loadTable() {
        var params = {
            page: currentPage,
            per_page: 25,
            sort: currentSort,
            direction: currentDirection
        };
        if (searchKeyword) {
            params.keyword = searchKeyword;
        } else {
            params.limit = getTopLimit();
        }

        updateListSubtitle();

        return fetch(apiUrl('/search', params), { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' } })
            .then(function (r) { if (!r.ok) throw new Error('Search failed'); return r.json(); })
            .then(function (data) {
                renderTableRows(data.devices || []);
                renderPagination(data.pagination || null);
            });
    }

    function refreshAll() {
        setLoading(true);
        Promise.all([loadSummary(), loadCharts(), loadTable()])
            .catch(function () { alert('Could not load ping analysis data.'); })
            .finally(function () { setLoading(false); });
    }

    function onTopLimitChange(limit) {
        document.getElementById('piaTopLimit').value = limit;
        document.getElementById('piaListTopLimit').value = limit;
        currentPage = 1;
        if (!searchKeyword) {
            currentSort = 'total_pings';
            currentDirection = 'desc';
            document.querySelectorAll('#piaDeviceTable th[data-sort]').forEach(function (th) {
                th.classList.remove('is-sorted', 'asc', 'desc');
            });
            var pingTh = document.querySelector('#piaDeviceTable th[data-sort="total_pings"]');
            if (pingTh) pingTh.classList.add('is-sorted', 'desc');
        }
        loadCharts();
        loadTable();
    }

    document.getElementById('piaTopLimit').addEventListener('change', function (e) {
        onTopLimitChange(e.target.value);
    });
    document.getElementById('piaListTopLimit').addEventListener('change', function (e) {
        onTopLimitChange(e.target.value);
    });
    document.getElementById('piaRefreshBtn').addEventListener('click', refreshAll);

    document.getElementById('piaSearchInput').addEventListener('input', function (e) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            searchKeyword = e.target.value.trim();
            currentPage = 1;
            loadTable();
        }, 300);
    });

    document.getElementById('piaExportBtn').addEventListener('click', function (e) {
        var params = {};
        if (searchKeyword) {
            params.keyword = searchKeyword;
        } else {
            params.limit = getTopLimit();
        }
        e.preventDefault();
        var qs = new URLSearchParams(params).toString();
        window.location.href = apiUrl('/export') + (qs ? ('?' + qs) : '');
    });

    document.getElementById('piaDeviceTable').addEventListener('click', function (e) {
        var sortTh = e.target.closest('th[data-sort]');
        if (sortTh) {
            var sort = sortTh.getAttribute('data-sort');
            if (currentSort === sort) {
                currentDirection = currentDirection === 'desc' ? 'asc' : 'desc';
            } else {
                currentSort = sort;
                currentDirection = sort === 'name' || sort === 'imei' ? 'asc' : 'desc';
            }
            document.querySelectorAll('#piaDeviceTable th[data-sort]').forEach(function (th) {
                th.classList.remove('is-sorted', 'asc', 'desc');
            });
            sortTh.classList.add('is-sorted', currentDirection);
            currentPage = 1;
            loadTable();
            return;
        }
    });

    document.getElementById('piaPagination').addEventListener('click', function (e) {
        var btn = e.target.closest('.pia-page-btn');
        if (!btn) return;
        currentPage = parseInt(btn.getAttribute('data-page'), 10) || 1;
        loadTable();
    });

    refreshAll();
})();
</script>
@endsection
