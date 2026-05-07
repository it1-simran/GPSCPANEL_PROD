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
@media (max-width:767px)  { .stats-grid { grid-template-columns: repeat(2,1fr); } }
@media (max-width:480px)  { .stats-grid { grid-template-columns: 1fr; } }

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
        <small>Your {{ Auth::user()->user_type == 'Admin' ? 'Admin' : (Auth::user()->user_type == 'Reseller' ? 'Manufacturer' : 'Dealer') }} Panel</small>
      </div>
      <div class="breadcrumb-wrap text-right">
        <a href="#">{{ Auth::user()->user_type == 'Admin' ? 'Admin' : (Auth::user()->user_type == 'Reseller' ? 'Manufacturer Area' : 'Dealer Area') }}</a>
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
      $totalpingsadmin    = DB::table('writers')->where('writers.created_by','1')->where('writers.is_deleted',0)->sum("total_pings");
      $countTotalPings    = DB::table('writers')->where('id',auth()->id())->where('writers.is_deleted',0)->value('total_pings');
      $todaypingsadmin    = DB::table('writers')->where('writers.created_by','1')->where('writers.is_deleted',0)->whereDate('writers.created_at','=',today())->sum("total_pings");
      $todaypingsuser     = DB::table('writers')->where('id',auth()->id())->first();
      $totalfirmware      = DB::table('firmware')->count();
      $totalDeviceCategory= DB::table('device_categories')->where('is_deleted',0)->count();
      $totalESIM          = DB::table('esims')->count();
      $totalModel         = DB::table('modals')->count();
      $totalBackend       = DB::table('backends')->count();
      $totalEsimMasters   = DB::table('ccids')->count();

      $startDate = now()->subDays(29)->startOfDay();
      $endDate   = now()->endOfDay();

      // Chart 1: Writers registered per day in last 30 days
      $regData = DB::table('writers')
          ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
          ->where('is_deleted', 0)
          ->whereBetween('created_at', [$startDate, $endDate])
          ->groupBy(DB::raw('DATE(created_at)'))
          ->orderBy(DB::raw('DATE(created_at)'))
          ->get();

      $datesRange = [];
      $currentDate = $startDate->copy();
      while($currentDate->lte($endDate)){
          $datesRange[] = $currentDate->format('Y-m-d');
          $currentDate->addDay();
      }
      $regByDate = $regData->keyBy('date');
      $dates = $datesRange;
      $regCounts = array_map(function($date) use($regByDate){
          return $regByDate->has($date) ? (int)$regByDate[$date]->count : 0;
      }, $dates);

      // Chart 2: Top 10 users by total pings
      $topUsers = DB::table('writers')
          ->select('name', DB::raw('COALESCE(total_pings, 0) as total_pings'))
          ->where('created_by', 1)
          ->where('is_deleted', 0)
          ->orderBy('total_pings', 'desc')
          ->limit(10)
          ->get();
      $topUserNames = $topUsers->pluck('name')->map(fn($n) => strlen($n) > 15 ? substr($n,0,15).'…' : $n)->toArray();
      $topUserPings = $topUsers->pluck('total_pings')->map(fn($p) => (int)$p)->toArray();
    ?>

    {{-- STATS GRID --}}
    <div class="stats-grid">

      @if(Auth::user()->user_type=='Admin')
      {{-- Users Registered --}}
      <div class="stat-card green-accent">
        <div class="card-icon"><i class="fa fa-users"></i></div>
        <div class="stat-num">{{ $countregister }}</div>
        <div class="stat-label">Users Registered</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:74%"></div></div>
        <a href="{{ url('admin/view-user') }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Assigned Device --}}
      <div class="stat-card dark-accent">
        <div class="card-icon"><i class="fa fa-link"></i></div>
        <div class="stat-num">{{ $AssignedDevice }}</div>
        <div class="stat-label">Assigned Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:57%"></div></div>
        <a href="{{ url('admin/view-device-assign') }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Total Device --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-hdd-o"></i></div>
        <div class="stat-num">{{ $AdmintotalDevice }}</div>
        <div class="stat-label">Total Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:80%"></div></div>
        <a href="#" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Unassigned Device --}}
      <div class="stat-card orange-accent">
        <div class="card-icon"><i class="fa fa-exclamation-circle"></i></div>
        <div class="stat-num">{{ $UnassignedDevices }}</div>
        <div class="stat-label">Unassigned Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:30%"></div></div>
        <a href="{{ url('admin/view-device-unassign') }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>
      @else
      {{-- Non-admin: Total Device --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-tablet"></i></div>
        <div class="stat-num">{{ $totalDevice }}</div>
        <div class="stat-label">Total Devices</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:60%"></div></div>
        <a href="#" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>
      @endif

      {{-- Total Templates --}}
      <div class="stat-card purple-accent">
        <div class="card-icon"><i class="fa fa-file-code-o"></i></div>
        <div class="stat-num">{{ Auth::user()->user_type=='Admin' ? $totalTemplete : $usertotalTemplete }}</div>
        <div class="stat-label">Total Templates</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:45%"></div></div>
        <a href="{{ Auth::user()->user_type=='Admin' ? url('admin/view-template') : url('user/view-template') }}" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Total Pings --}}
      <div class="stat-card teal-accent">
        <div class="card-icon"><i class="fa fa-wifi"></i></div>
        <div class="stat-num">{{ Auth::user()->user_type=='Admin' ? $totalpingsadmin : $countTotalPings }}</div>
        <div class="stat-label">Total Pings</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:65%"></div></div>
        <a href="#" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Today Pings --}}
      <div class="stat-card pink-accent">
        <div class="card-icon"><i class="fa fa-calendar"></i></div>
        <div class="stat-num">{{ Auth::user()->user_type=='Admin' ? $todaypingsadmin : ($todaypingsuser ? $todaypingsuser->today_pings : 0) }}</div>
        <div class="stat-label">Today's Pings</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:40%"></div></div>
        <a href="#" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      @if(Auth::user()->user_type=='Admin')
      {{-- Total Firmware --}}
      <div class="stat-card amber-accent">
        <div class="card-icon"><i class="fa fa-code"></i></div>
        <div class="stat-num">{{ $totalfirmware }}</div>
        <div class="stat-label">Total Firmware</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:50%"></div></div>
        <a href="/admin/view-firmware" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Device Categories --}}
      <div class="stat-card green-accent">
        <div class="card-icon"><i class="fa fa-th-large"></i></div>
        <div class="stat-num">{{ $totalDeviceCategory }}</div>
        <div class="stat-label">Device Categories</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:55%"></div></div>
        <a href="/admin/View-device-category" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Total ESIM --}}
      <div class="stat-card dark-accent">
        <div class="card-icon"><i class="fa fa-credit-card"></i></div>
        <div class="stat-num">{{ $totalESIM }}</div>
        <div class="stat-label">Total eSIM Types</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:35%"></div></div>
        <a href="/admin/view-esim" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- ESIM Masters --}}
      <div class="stat-card mixed-accent">
        <div class="card-icon"><i class="fa fa-database"></i></div>
        <div class="stat-num">{{ $totalEsimMasters }}</div>
        <div class="stat-label">ESIM Masters</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:42%"></div></div>
        <a href="/admin/view-esim-customers" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Total Models --}}
      <div class="stat-card purple-accent">
        <div class="card-icon"><i class="fa fa-sitemap"></i></div>
        <div class="stat-num">{{ $totalModel }}</div>
        <div class="stat-label">Total Models</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:48%"></div></div>
        <a href="/admin/view-models" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>

      {{-- Total Backends --}}
      <div class="stat-card orange-accent">
        <div class="card-icon"><i class="fa fa-server"></i></div>
        <div class="stat-num">{{ $totalBackend }}</div>
        <div class="stat-label">Total Backends</div>
        <div class="stat-bar"><div class="stat-bar-fill" style="width:52%"></div></div>
        <a href="/admin/view-backend" class="stat-link">View All <i class="fa fa-arrow-right"></i></a>
      </div>
      @endif

    </div>

    @if(Auth::user()->user_type=='Admin')
    <div class="chart-section">
      <div class="chart-header">
        <h5><i class="fa fa-line-chart" style="color:var(--green);margin-right:8px;"></i>User Registrations — Last 30 Days</h5>
        <span class="chart-badge">Live Data</span>
      </div>
      <canvas id="registrationChart" height="90"></canvas>
    </div>

    @endif

  </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>
@if(Auth::user()->user_type=='Admin')

// ===== CHART 1: User Registrations Line Chart =====
(function(){
  var ctx1 = document.getElementById('registrationChart').getContext('2d');
  var grad1 = ctx1.createLinearGradient(0, 0, 0, 320);
  grad1.addColorStop(0, 'rgba(118,207,28,0.4)');
  grad1.addColorStop(1, 'rgba(118,207,28,0.01)');

  new Chart(ctx1, {
    type: 'line',
    data: {
      labels: @json($dates),
      datasets: [{
        label: 'New Users',
        data: @json($regCounts),
        borderColor: '#76CF1C',
        backgroundColor: grad1,
        borderWidth: 2.5,
        pointBackgroundColor: '#76CF1C',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 8,
        tension: 0.45,
        fill: true
      }]
    },
    options: {
      responsive: true,
      animation: {
        duration: 1400,
        easing: 'easeInOutQuart'
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#76CF1C',
          bodyColor: '#ffffff',
          borderColor: '#76CF1C',
          borderWidth: 1,
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            title: function(items){ return 'Date: ' + items[0].label; },
            label: function(item){ return '  New Users: ' + item.raw; }
          }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(30,41,59,0.05)' },
          ticks: { color: '#94a3b8', font: { size: 10 }, maxTicksLimit: 10 }
        },
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
          grid: { color: 'rgba(30,41,59,0.07)' }
        }
      }
    }
  });
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