<?php use App\Helper\CommonHelper; ?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('dashboard') }}">
@endpush

@section('content')

<section id="main-content">
  <section class="wrapper">

    {{-- PAGE HEADER --}}
    <div class="db-header">
      <div>
        <h2>Dashboard</h2>
        <small>Your {{ Auth::user()->user_type == 'Admin' ? 'Admin' : (Auth::user()->user_type == 'Reseller' ? 'Manufacturer' : (Auth::user()->user_type == 'Support' ? 'Support' : 'Dealer')) }} Panel</small>
      </div>
      <div class="breadcrumb-wrap text-right">
        <a href="#">{{ Auth::user()->user_type == 'Admin' ? 'Admin' : (Auth::user()->user_type == 'Reseller' ? 'Manufacturer Area' : (Auth::user()->user_type == 'Support' ? 'Support Area' : 'Dealer Area')) }}</a>
        <span>/</span>
        <span class="active">Dashboard</span>
      </div>
    </div>

    {{-- NOTIFICATION BANNER --}}
    @if(Auth::user()->user_type != 'Admin')
    <?php $notification = DB::table('notifications')->where(['user_id' => Auth::user()->id, 'is_view' => 0])->first(); ?>
    @if(isset($notification))
    <div class="notif-banner">
      <p>{{ isset($notification->notification) ? $notification->notification : '' }}</p>
      <button class="btn-upgrade" onclick="openModel('{{$notification->notification}}')">Upgrade Now</button>
    </div>

    <div class="modal" id="upgradeModal" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form onsubmit="return false;">
            @csrf
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
              <h5 class="modal-title">Releasing Notes</h5>
            </div>
            <div class="bgx-notes padding-15">
              @php echo CommonHelper::getReleasingNotes($notification->firmware_id); @endphp
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" onClick="updateVersion('{{strtolower(Auth::user()->user_type)}}')">Upgrade</button>
              <input type="hidden" name="firmwareId" id="firmwareId" value="{{$notification->firmware_id}}" />
              <input type="hidden" name="notificationId" id="notificationId" value="{{$notification->id}}" />
            </div>
          </form>
        </div>
      </div> 
    </div>
    @endif
    @endif

    @php
      $insights = $insights ?? [];
      $isAdminDashboard = ($insights['role'] ?? '') === 'Admin';

      $pingYearOptions = [];
      $pingChartYear = (int) now()->year;
      $pingChartMonth = 0;
      $pingChartLabels = [];
      $pingChartCounts = [];
      $pingChartJsType = 'line';
      $pingChartSubtitle = '';
      $pingChartTotal = 0;

      if ($isAdminDashboard) {
          $p = app(\App\Services\DashboardPingChartService::class)->getForAdmin(request());
          $pingYearOptions = $p['year_options'];
          $pingChartYear = $p['year'];
          $pingChartMonth = $p['month'];
          $pingChartLabels = $p['labels'];
          $pingChartCounts = $p['counts'];
          $pingChartJsType = $p['chart_type'];
          $pingChartSubtitle = $p['subtitle'];
          $pingChartTotal = $p['total'];
      }

      $__portalPrefix = match (Auth::user()->user_type) {
          'Admin' => 'admin',
          'Reseller' => 'reseller',
          'Support' => 'support',
          default => 'user',
      };
    @endphp

    {{-- STATS GRID --}}
    <div class="stats-grid">
      @php
        $__dashboardStatViewAll = function ($href) {
            if ($href === null) {
                return null;
            }
            $h = trim((string) $href);
            if ($h === '' || $h === '#' || strncasecmp($h, 'javascript:', 11) === 0) {
                return null;
            }
            return $href;
        };
      @endphp

      @if(!empty($insights['showUserStats']))
      {{-- Users Registered --}}
      <div class="stat-card green-accent">
        <div class="card-icon"><i class="fa fa-users"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['usersRegistered'] }}">0</div>
        <div class="stat-label">Users Registered</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:74%"></div></div>
        @if($__va = $__dashboardStatViewAll(url($__portalPrefix . '/view-user')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>
      @endif

      @if(!empty($insights['showDeviceBreakdown']))
      {{-- Assigned Device --}}
      <div class="stat-card dark-accent">
        <div class="card-icon"><i class="fa fa-link"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['assignedDevices'] }}">0</div>
        <div class="stat-label">Assigned Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:57%"></div></div>
        @if($__va = $__dashboardStatViewAll(url($__portalPrefix . '/view-device-assign')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Device --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-hdd-o"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalDevices'] }}">0</div>
        <div class="stat-label">Total Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:80%"></div></div>
      </div>

      {{-- Unassigned Device --}}
      <div class="stat-card orange-accent">
        <div class="card-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['unassignedDevices'] }}">0</div>
        <div class="stat-label">Unassigned Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:30%"></div></div>
        @if($__va = $__dashboardStatViewAll(url($__portalPrefix . '/view-device-unassign')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>
      @elseif(empty($insights['showDeviceBreakdown']))
      @php
        $__nonAdminDeviceHref = match (Auth::user()->user_type) {
            'User' => url('user/view-device'),
            'Reseller' => url('reseller/view-device-assign'),
            'Support' => url('support/view-device'),
            default => null,
        };
      @endphp
      {{-- Scoped account: Total Device --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-tablet"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalDevices'] }}">0</div>
        <div class="stat-label">Total Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:60%"></div></div>
        @if($__va = $__dashboardStatViewAll($__nonAdminDeviceHref))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>
      @endif

      {{-- Total Templates --}}
      <div class="stat-card purple-accent">
        <div class="card-icon"><i class="fa fa-file-code-o"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalTemplates'] }}">0</div>
        <div class="stat-label">Total Templates</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:45%"></div></div>
        @php
          $__tplHref = match (Auth::user()->user_type) {
              'Admin' => url('admin/view-template'),
              'User' => url('user/view-template'),
              'Reseller' => url('reseller/view-template'),
              'Support' => url('support/view-template'),
              default => null,
          };
        @endphp
        @if($__va = $__dashboardStatViewAll($__tplHref))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Pings --}}
      <div class="stat-card teal-accent">
        <div class="card-icon"><i class="fa fa-wifi"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalPings'] }}">0</div>
        <div class="stat-label">Total Pings</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:65%"></div></div>
      </div>

      {{-- Today Pings --}}
      <div class="stat-card pink-accent">
        <div class="card-icon"><i class="fa fa-calendar"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['todayPings'] }}">0</div>
        <div class="stat-label">Today's Pings</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:40%"></div></div>
      </div>

      @if($isAdminDashboard)
      {{-- Total Firmware --}}
      <div class="stat-card amber-accent">
        <div class="card-icon"><i class="fa fa-code"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalFirmware'] }}">0</div>
        <div class="stat-label">Total Firmware</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:50%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-firmware')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Device Categories --}}
      <div class="stat-card green-accent">
        <div class="card-icon"><i class="fa fa-th-large"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalDeviceCategory'] }}">0</div>
        <div class="stat-label">Device Categories</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:55%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-device-category')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total ESIM --}}
      <div class="stat-card dark-accent">
        <div class="card-icon"><i class="fa fa-credit-card"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalESIM'] }}">0</div>
        <div class="stat-label">Total eSIM Types</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:35%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-esim')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- ESIM Masters --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-database"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalEsimMasters'] }}">0</div>
        <div class="stat-label">ESIM Masters</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:42%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-esim-customers')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Models --}}
      <div class="stat-card purple-accent">
        <div class="card-icon"><i class="fa fa-sitemap"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalModel'] }}">0</div>
        <div class="stat-label">Total Models</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:48%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-models')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Backends --}}
      <div class="stat-card orange-accent">
        <div class="card-icon"><i class="fa fa-server"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $insights['totalBackend'] }}">0</div>
        <div class="stat-label">Total Backends</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:52%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-backend')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>
      @endif

    </div>

    @if($isAdminDashboard)
    <div class="chart-section chart-section--pings">
      <div class="ping-chart-inner">
        <div class="chart-header-row">
          <div class="chart-title-block">
            <h5><i class="fa fa-signal" style="margin-right:10px;"></i>Device Pings</h5>
            <p class="chart-sub" id="pingChartSubtitleEl">{{ $pingChartSubtitle }}</p>
          </div>
          <div class="ping-chart-filters" id="pingChartFilterForm">
            <label>Year
              <select id="pingYearSelect" name="ping_year" autocomplete="off">
                @foreach($pingYearOptions as $y)
                  <option value="{{ $y }}" {{ $pingChartYear === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
              </select>
            </label>
            <label>Month
              <select id="pingMonthSelect" name="ping_month" autocomplete="off">
                <option value="0" {{ $pingChartMonth === 0 ? 'selected' : '' }}>All months</option>
                @for($m = 1; $m <= 12; $m++)
                  <option value="{{ $m }}" {{ $pingChartMonth === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F') }}</option>
                @endfor
              </select>
            </label>
            <span style="margin-top: 20px;" class="ping-chart-total" id="pingChartTotalEl">Total: {{ number_format($pingChartTotal) }} pings</span>
          </div>
        </div>
        <div class="ping-chart-canvas-wrap" id="pingChartCanvasWrap">
          <canvas id="pingsByMonthChart" height="100"></canvas>
        </div>
      </div>
    </div>

    @endif

  </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
(function () {
  function easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4);
  }
  function animateStatCount(el, index) {
    var raw = el.getAttribute('data-stat-target');
    var target = parseInt(raw, 10);
    if (isNaN(target) || target < 0) target = 0;
    var duration = 950 + Math.min(900, Math.floor(target * 0.35));
    var delay = index * 55;
    var startAt = null;
    function tick(now) {
      if (startAt === null) startAt = now + delay;
      if (now < startAt) {
        requestAnimationFrame(tick);
        return;
      }
      var t = Math.min(1, (now - startAt) / duration);
      var eased = easeOutQuart(t);
      var val = Math.round(target * eased);
      el.textContent = val.toLocaleString();
      if (t < 1) requestAnimationFrame(tick);
      else el.textContent = target.toLocaleString();
    }
    requestAnimationFrame(tick);
  }
  function runStatCounters() {
    var nodes = document.querySelectorAll('.js-stat-count');
    for (var i = 0; i < nodes.length; i++) animateStatCount(nodes[i], i);
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runStatCounters);
  } else {
    runStatCounters();
  }
})();
@if(Auth::user()->user_type=='Admin')

// ===== Device Pings chart — blue series + #1e293b axes (AJAX) =====
(function(){
  var el = document.getElementById('pingsByMonthChart');
  var wrapEl = document.getElementById('pingChartCanvasWrap');
  if (!el) return;
  var ctx2 = el.getContext('2d');
  var pingStatsUrl = @json(route('admin.dashboard.ping-stats'));

  function buildDataset(isLine, counts) {
    var blue = '#2563eb';
    var blueDeep = '#1e40af';
    var h = el.parentNode ? el.parentNode.clientHeight : 280;
    var gradLine = ctx2.createLinearGradient(0, 0, 0, Math.max(h, 260));
    gradLine.addColorStop(0, 'rgba(37, 99, 235, 0.35)');
    gradLine.addColorStop(0.45, 'rgba(56, 189, 248, 0.12)');
    gradLine.addColorStop(1, 'rgba(255, 255, 255, 0)');
    var gradBar = ctx2.createLinearGradient(0, 0, 0, Math.max(h, 260));
    gradBar.addColorStop(0, 'rgba(37, 99, 235, 0.95)');
    gradBar.addColorStop(0.5, 'rgba(29, 78, 216, 0.88)');
    gradBar.addColorStop(1, 'rgba(14, 165, 233, 0.65)');
    return isLine ? {
      label: 'Pings',
      data: counts,
      borderColor: blueDeep,
      backgroundColor: gradLine,
      borderWidth: 3,
      pointBackgroundColor: '#ffffff',
      pointBorderColor: blue,
      pointBorderWidth: 2.5,
      pointRadius: 6,
      pointHoverRadius: 10,
      pointHoverBackgroundColor: blue,
      pointHoverBorderColor: '#ffffff',
      tension: 0.4,
      fill: true
    } : {
      label: 'Pings',
      data: counts,
      backgroundColor: gradBar,
      borderColor: 'rgba(255,255,255,0.35)',
      borderWidth: 1,
      borderRadius: 10,
      borderSkipped: false,
      maxBarThickness: 18,
      hoverBackgroundColor: 'rgba(30, 64, 175, 0.95)'
    };
  }

  function chartOptions(isLine) {
    return {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 10, right: 6, bottom: 4, left: 4 } },
      animation: { duration: 1000, easing: 'easeOutQuart' },
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#93c5fd',
          bodyColor: '#f8fafc',
          borderColor: 'rgba(59, 130, 246, 0.45)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 12,
          displayColors: false,
          callbacks: {
            title: function(items){ return items[0].label; },
            label: function(item){ return 'Pings: ' + Number(item.raw).toLocaleString(); }
          }
        }
      },
      scales: {
        x: {
          border: { display: false },
          grid: {
            display: !isLine,
            color: 'rgba(30, 41, 59, 0.1)',
            lineWidth: 1
          },
          ticks: {
            color: '#1e293b',
            font: { size: isLine ? 12 : 10, weight: '600', family: 'system-ui, Segoe UI, sans-serif' },
            maxRotation: isLine ? 0 : 50,
            minRotation: 0,
            autoSkip: true,
            maxTicksLimit: isLine ? 12 : 31,
            padding: 6
          }
        },
        y: {
          beginAtZero: true,
          border: { display: false },
          grid: { color: 'rgba(30, 41, 59, 0.08)', lineWidth: 1 },
          ticks: {
            color: '#1e293b',
            font: { size: 11, weight: '600', family: 'system-ui, Segoe UI, sans-serif' },
            padding: 8
          }
        }
      }
    };
  }

  function renderPingChart(labels, counts, chartType) {
    if (window.__pingChartInstance) {
      window.__pingChartInstance.destroy();
      window.__pingChartInstance = null;
    }
    var isLine = chartType === 'line';
    window.__pingChartInstance = new Chart(ctx2, {
      type: chartType,
      data: { labels: labels, datasets: [buildDataset(isLine, counts)] },
      options: chartOptions(isLine)
    });
  }

  renderPingChart(@json($pingChartLabels), @json($pingChartCounts), @json($pingChartJsType));

  var ySel = document.getElementById('pingYearSelect');
  var mSel = document.getElementById('pingMonthSelect');
  var subEl = document.getElementById('pingChartSubtitleEl');
  var totEl = document.getElementById('pingChartTotalEl');

  function setPingLoading(on) {
    if (wrapEl) wrapEl.classList.toggle('is-loading', !!on);
  }

  function loadPingStats() {
    if (!ySel || !mSel) return;
    var params = new URLSearchParams();
    params.set('ping_year', ySel.value);
    params.set('ping_month', mSel.value);
    setPingLoading(true);
    fetch(pingStatsUrl + '?' + params.toString(), {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })
      .then(function(r) {
        if (!r.ok) throw new Error('bad status');
        return r.json();
      })
      .then(function(data) {
        if (subEl) subEl.textContent = data.subtitle || '';
        if (totEl) totEl.textContent = 'Total: ' + Number(data.total || 0).toLocaleString() + ' pings';
        renderPingChart(data.labels || [], data.counts || [], data.chart_type || 'line');
        var qs = params.toString();
        if (window.history && window.history.replaceState) {
          window.history.replaceState({}, '', window.location.pathname + (qs ? ('?' + qs) : ''));
        }
      })
      .catch(function() {
        alert('Could not load ping statistics. Please try again.');
      })
      .finally(function() {
        setPingLoading(false);
      });
  }

  ySel.addEventListener('change', loadPingStats);
  mSel.addEventListener('change', loadPingStats);
})();


@endif

function openModel(notifications){ $('#upgradeModal').modal('show'); }

function updateVersion(url_type){
  var firmwareId = $('#firmwareId').val();
  var notificationId = $('#notificationId').val();
  $.ajax({
    url: '/'+url_type+'/updateFirmware', type:'POST',
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    data: { firmware_id: firmwareId, notification_id: notificationId },
    success: function(response){
      var result = JSON.parse(response);
      if(result.status==200){ window.location.reload(); }
      else { alert('Error updating firmware version.'); }
    },
    error: function(){ alert('An error occurred. Please try again.'); }
  });
}
</script>

@stop