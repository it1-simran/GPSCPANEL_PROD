<?php use App\Helper\CommonHelper; ?>
@extends('layouts.apps')
@section('content')

<style>
:root {
  --dark: #1e293b;
  --green: #76CF1C;
  --white: #ffffff;
  --dark2: #253347;
  --dark3: #2d3f55;
  --green-soft: rgba(118,207,28,0.12);
  --green-glow: rgba(118,207,28,0.25);
}

/* ===== DASHBOARD WRAPPER ===== */
#main-content .wrapper { padding: 20px 24px; background: #f0f4f8; min-height: 100vh; }

/* ===== PAGE HEADER ===== */
.db-header {
  display: flex; align-items: center; justify-content: space-between;
  background: linear-gradient(135deg, var(--dark) 0%, var(--dark3) 100%);
  border-radius: 16px; padding: 22px 28px; margin-bottom: 26px;
  box-shadow: 0 8px 32px rgba(30,41,59,0.25); position: relative; overflow: hidden;
}
.db-header::before {
  content: ''; position: absolute; top: -40px; right: -40px;
  width: 180px; height: 180px; border-radius: 50%;
  background: var(--green-glow); pointer-events: none;
}
.db-header::after {
  content: ''; position: absolute; bottom: -50px; left: 30%;
  width: 120px; height: 120px; border-radius: 50%;
  background: rgba(118,207,28,0.08); pointer-events: none;
}
.db-header h2 { color: var(--white); font-size: 22px; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
.db-header small { color: rgba(255,255,255,0.6); font-size: 13px; display: block; margin-top: 4px; }
.db-header .breadcrumb-wrap a { color: var(--green); text-decoration: none; font-size: 13px; }
.db-header .breadcrumb-wrap span { color: rgba(255,255,255,0.4); margin: 0 6px; }
.db-header .breadcrumb-wrap .active { color: rgba(255,255,255,0.7); font-size: 13px; }

/* ===== STAT CARDS ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 26px;
}
@media (max-width:1199px) { .stats-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width:767px)  {
  .stats-grid { grid-template-columns: repeat(2,1fr); gap: 10px; }
  .chart-section { padding: 16px 14px; border-radius: 12px; }
  .chart-header h5 { font-size: 14px; }
  .chart-header-row { flex-direction: column; gap: 8px; }
  .ping-chart-canvas-wrap { padding: 12px 8px 8px; }
  .col-lg-6 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
  .db-header { padding: 16px 18px; border-radius: 12px; margin-bottom: 16px; }
  .db-header h2 { font-size: 18px; }
  .db-header .breadcrumb-wrap { text-align: left !important; margin-top: 6px; }
}
@media (max-width:480px)  {
  .stats-grid { grid-template-columns: 1fr; gap: 8px; }
  .stat-card { padding: 14px 16px 12px; }
  .chart-section { padding: 12px 10px; }
  .db-header { padding: 14px 14px; border-radius: 10px; }
  .db-header h2 { font-size: 16px; }
  .db-header small { font-size: 12px; }
  .db-header::before { width: 120px; height: 120px; top: -30px; right: -30px; }
}

.stat-card {
  background: var(--white);
  border-radius: 14px;
  padding: 20px 22px 16px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(30,41,59,0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  border-top: 3px solid transparent;
}
.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 32px rgba(30,41,59,0.15);
}
.stat-card.green-accent  { border-top-color: var(--green); }
.stat-card.dark-accent   { border-top-color: var(--dark); }
.stat-card.mixed-accent  { border-top-color: #38bdf8; }
.stat-card.purple-accent { border-top-color: #a855f7; }
.stat-card.orange-accent { border-top-color: #f97316; }
.stat-card.teal-accent   { border-top-color: #14b8a6; }
.stat-card.pink-accent   { border-top-color: #ec4899; }
.stat-card.amber-accent  { border-top-color: #f59e0b; }

.stat-card .card-icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px; font-size: 20px;
}
.stat-card.green-accent  .card-icon { background: var(--green-soft); color: var(--green); }
.stat-card.dark-accent   .card-icon { background: rgba(30,41,59,0.08); color: var(--dark); }
.stat-card.mixed-accent  .card-icon { background: rgba(56,189,248,0.1); color: #38bdf8; }
.stat-card.purple-accent .card-icon { background: rgba(168,85,247,0.1); color: #a855f7; }
.stat-card.orange-accent .card-icon { background: rgba(249,115,22,0.1); color: #f97316; }
.stat-card.teal-accent   .card-icon { background: rgba(20,184,166,0.1); color: #14b8a6; }
.stat-card.pink-accent   .card-icon { background: rgba(236,72,153,0.1); color: #ec4899; }
.stat-card.amber-accent  .card-icon { background: rgba(245,158,11,0.1); color: #f59e0b; }

.stat-card .stat-num {
  font-size: 32px; font-weight: 800; color: var(--dark);
  line-height: 1; margin-bottom: 4px; letter-spacing: -1px;
}
.stat-card .stat-label {
  font-size: 12px; color: #64748b; font-weight: 500;
  text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;
}
.stat-card .stat-bar { height: 4px; border-radius: 4px; background: #e9ecef; margin-bottom: 14px; }
.stat-card .stat-bar-fill { height: 100%; border-radius: 4px; transition: width 1s ease; }
.stat-card.green-accent  .stat-bar-fill { background: var(--green); }
.stat-card.dark-accent   .stat-bar-fill { background: var(--dark); }
.stat-card.mixed-accent  .stat-bar-fill { background: #38bdf8; }
.stat-card.purple-accent .stat-bar-fill { background: #a855f7; }
.stat-card.orange-accent .stat-bar-fill { background: #f97316; }
.stat-card.teal-accent   .stat-bar-fill { background: #14b8a6; }
.stat-card.pink-accent   .stat-bar-fill { background: #ec4899; }
.stat-card.amber-accent  .stat-bar-fill { background: #f59e0b; }

.stat-card .stat-link {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 600; text-decoration: none;
  color: var(--dark); transition: color 0.2s;
}
.stat-card .stat-link:hover { color: var(--green); }
.stat-card .stat-link i { font-size: 11px; }

/* ===== CHART SECTION ===== */
.chart-section {
  background: var(--white);
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 2px 12px rgba(30,41,59,0.08);
  margin-bottom: 26px;
}
.chart-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
}
.chart-header h5 {
  font-size: 16px; font-weight: 700; color: var(--dark); margin: 0;
}
.chart-badge {
  background: var(--green-soft); color: var(--green);
  border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600;
}
.chart-header-row {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 16px; flex-wrap: wrap; margin-bottom: 20px;
}
.chart-header-row .chart-title-block { flex: 1; min-width: 200px; }
.chart-header-row .chart-title-block h5 { margin-bottom: 4px; }
.chart-header-row .chart-sub { font-size: 13px; color: #64748b; font-weight: 500; margin: 0; }

/* ===== PING CHART — site green (#76CF1C) same as header / stat cards ===== */
.chart-section--pings {
  background: linear-gradient(160deg, #ffffff 0%, #fbfef8 42%, #f4fce8 100%);
  border: 1px solid rgba(118, 207, 28, 0.28);
  box-shadow:
    0 4px 28px rgba(118, 207, 28, 0.1),
    0 1px 3px rgba(15, 23, 42, 0.06);
  position: relative;
  overflow: hidden;
}
.chart-section--pings::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, #5a9e14, var(--green), #9fe04a);
  border-radius: 16px 16px 0 0;
}
.chart-section--pings .ping-chart-inner { position: relative; z-index: 1; }
.chart-section--pings .chart-header-row .chart-title-block h5 {
  font-size: 17px;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--dark);
}
.chart-section--pings .chart-header-row .chart-title-block h5 i {
  color: var(--green);
}
.ping-chart-canvas-wrap {
  margin-top: 10px;
  padding: 18px 14px 12px;
  background: rgba(255, 255, 255, 0.78);
  backdrop-filter: blur(8px);
  border-radius: 16px;
  border: 1px solid rgba(118, 207, 28, 0.15);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
  transition: opacity 0.22s ease;
  min-height: 300px;
  height: 300px;
}
.ping-chart-canvas-wrap.is-loading {
  opacity: 0.48;
  pointer-events: none;
}
.ping-chart-filters {
  display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px 14px;
}
.ping-chart-filters label {
  display: flex; flex-direction: column; gap: 5px;
  font-size: 10px; font-weight: 700; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.55px; margin: 0;
}
.ping-chart-filters select {
  min-width: 118px; padding: 9px 14px; border-radius: 12px;
  border: 1px solid rgba(118, 207, 28, 0.35);
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  color: var(--dark);
  font-size: 13px; font-weight: 700; cursor: pointer;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.ping-chart-filters select:hover {
  border-color: var(--green);
}
.ping-chart-filters select:focus {
  outline: none;
  border-color: var(--green);
  box-shadow: 0 0 0 3px var(--green-soft);
}
.ping-chart-total {
  font-size: 12px; font-weight: 700;
  color: var(--dark);
  background: linear-gradient(135deg, var(--green-soft) 0%, rgba(118, 207, 28, 0.22) 45%, var(--green-soft) 100%);
  border: 1px solid rgba(118, 207, 28, 0.45);
  padding: 8px 14px; border-radius: 999px;
  align-self: center;
  box-shadow: 0 2px 10px rgba(118, 207, 28, 0.2);
  letter-spacing: 0.02em;
}

/* ===== NOTIFICATION BANNER ===== */
.notif-banner {
  background: linear-gradient(135deg, var(--dark) 0%, var(--dark3) 100%);
  border-left: 4px solid var(--green);
  border-radius: 12px; padding: 14px 20px;
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 22px; box-shadow: 0 4px 16px rgba(30,41,59,0.15);
}
.notif-banner p { color: rgba(255,255,255,0.85); margin: 0; font-size: 14px; }
.notif-banner .btn-upgrade {
  background: var(--green); color: var(--dark); border: none;
  border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 700;
  cursor: pointer; transition: opacity 0.2s; white-space: nowrap; margin-left: 16px;
}
.notif-banner .btn-upgrade:hover { opacity: 0.85; }

/* ===== MODAL ===== */
.modal-header { background: var(--dark); }
.modal-header h5, .modal-header button { color: var(--white) !important; }
</style>

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

    {{-- PHP QUERIES --}}
    <?php
      $countregister      = DB::table('writers')->where('is_deleted','0')->count();
      $UnassignedDevices  = DB::table('devices')->leftJoin('writers','writers.id','=','devices.user_id')->select('devices.*','writers.name as username')->where('devices.is_deleted','0')->where('devices.user_id',NULL)->orwhere('devices.user_id',0)->count();
      $AssignedDevice     = DB::table('devices')->leftJoin('writers','writers.id','=','devices.user_id')->select('devices.*','writers.name as username')->where('devices.is_deleted','0')->where('devices.user_id','!=','')->count();
      $totalTemplete      = DB::table('templates')->where('is_deleted','0')->where('verify','1')->count();
      $usertotalTemplete  = DB::table('templates')->where('is_deleted','0')->where('verify','2')->where('id_user',auth()->id())->count();
      $totalDevice        = DB::table('devices')->where('is_deleted','0')->where('user_id',auth()->id())->orwhereRaw('FIND_IN_SET('.auth()->id().',devices.assign_to_ids)')->count();
      $AdmintotalDevice   = DB::table('devices')->where('is_deleted','0')->count();
      /* Admin: total = all writers; today = sum(today_pings) where pings_date is today. */
      $totalpingsadmin    = DB::table('writers')->where('writers.is_deleted', '0')->sum('total_pings');
      $countTotalPings    = DB::table('writers')->where('id',auth()->id())->where('writers.is_deleted',0)->value('total_pings');
      $todaypingsadmin    = DB::table('writers')->where('writers.is_deleted', '0')
          ->whereDate('writers.pings_date', today())
          ->sum('today_pings');
      $todaypingsuser     = DB::table('writers')->where('id',auth()->id())->first();
      $totalfirmware      = DB::table('firmware')->count();
      $totalDeviceCategory= DB::table('device_categories')->where('is_deleted',0)->count();
      $totalESIM          = DB::table('esims')->count();
      $totalModel         = DB::table('modals')->count();
      $totalBackend       = DB::table('backends')->count();
      $totalEsimMasters   = DB::table('ccids')->count();

      // Chart: pings (same logic as GET /admin/dashboard/ping-stats for AJAX)
      $pingYearOptions = [];
      $pingChartYear = (int) now()->year;
      $pingChartMonth = 0;
      $pingChartLabels = [];
      $pingChartCounts = [];
      $pingChartJsType = 'line';
      $pingChartSubtitle = '';
      $pingChartTotal = 0;

      if (auth()->user()->user_type == 'Admin') {
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
    ?>

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

      @if(Auth::user()->user_type=='Admin')
      {{-- Users Registered --}}
      <div class="stat-card green-accent">
        <div class="card-icon"><i class="fa fa-users"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $countregister }}">0</div>
        <div class="stat-label">Users Registered</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:74%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-user')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Assigned Device --}}
      <div class="stat-card dark-accent">
        <div class="card-icon"><i class="fa fa-link"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $AssignedDevice }}">0</div>
        <div class="stat-label">Assigned Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:57%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-device-assign')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Device --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-hdd-o"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $AdmintotalDevice }}">0</div>
        <div class="stat-label">Total Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:80%"></div></div>
      </div>

      {{-- Unassigned Device --}}
      <div class="stat-card orange-accent">
        <div class="card-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $UnassignedDevices }}">0</div>
        <div class="stat-label">Unassigned Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:30%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-device-unassign')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>
      @else
      @php
        $__nonAdminDeviceHref = match (Auth::user()->user_type) {
            'User' => url('user/view-device'),
            'Reseller' => url('reseller/view-device-assign'),
            'Support' => url('support/view-device'),
            default => null,
        };
      @endphp
      {{-- Non-admin: Total Device --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-tablet"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalDevice }}">0</div>
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
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) (Auth::user()->user_type=='Admin' ? $totalTemplete : $usertotalTemplete) }}">0</div>
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
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) (Auth::user()->user_type=='Admin' ? $totalpingsadmin : $countTotalPings) }}">0</div>
        <div class="stat-label">Total Pings</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:65%"></div></div>
      </div>

      {{-- Today Pings --}}
      <div class="stat-card pink-accent">
        <div class="card-icon"><i class="fa fa-calendar"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) (Auth::user()->user_type=='Admin' ? $todaypingsadmin : ($todaypingsuser ? $todaypingsuser->today_pings : 0)) }}">0</div>
        <div class="stat-label">Today's Pings</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:40%"></div></div>
      </div>

      @if(Auth::user()->user_type=='Admin')
      {{-- Total Firmware --}}
      <div class="stat-card amber-accent">
        <div class="card-icon"><i class="fa fa-code"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalfirmware }}">0</div>
        <div class="stat-label">Total Firmware</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:50%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-firmware')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Device Categories --}}
      <div class="stat-card green-accent">
        <div class="card-icon"><i class="fa fa-th-large"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalDeviceCategory }}">0</div>
        <div class="stat-label">Device Categories</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:55%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-device-category')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total ESIM --}}
      <div class="stat-card dark-accent">
        <div class="card-icon"><i class="fa fa-credit-card"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalESIM }}">0</div>
        <div class="stat-label">Total eSIM Types</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:35%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-esim')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- ESIM Masters --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-database"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalEsimMasters }}">0</div>
        <div class="stat-label">ESIM Masters</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:42%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-esim-customers')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Models --}}
      <div class="stat-card purple-accent">
        <div class="card-icon"><i class="fa fa-sitemap"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalModel }}">0</div>
        <div class="stat-label">Total Models</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:48%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-models')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>

      {{-- Total Backends --}}
      <div class="stat-card orange-accent">
        <div class="card-icon"><i class="fa fa-server"></i></div>
        <div class="stat-num js-stat-count" data-stat-target="{{ (int) $totalBackend }}">0</div>
        <div class="stat-label">Total Backends</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:52%"></div></div>
        @if($__va = $__dashboardStatViewAll(url('admin/view-backend')))
        <a href="{{ $__va }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
        @endif
      </div>
      @endif

    </div>

    @if(Auth::user()->user_type=='Admin')
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