<?php

use App\Models\TicketModel;
use App\Models\versionModel;

$tickets = TicketModel::where('is_read', 0)->get();
$latestVersion = versionModel::latest('created_at')->first();

$ticketCount = $tickets->count();
$userType = Auth::check() ? strtolower(trim((string) Auth::user()->user_type)) : '';

$globalSearchItems = [];
if ($userType === 'admin') {
    $globalSearchItems = [
        ['label' => 'Dashboard', 'url' => url('/admin'), 'keywords' => ['home', 'dashboard']],
        ['label' => 'Raised Tickets', 'url' => url('/admin/tickets'), 'keywords' => ['ticket', 'raised', 'complaint', 'issue']],
        ['label' => 'Version Management', 'url' => url('/admin/version-control'), 'keywords' => ['version', 'release', 'notes']],
        ['label' => 'IMEI Management', 'url' => url('/admin/view-imeis'), 'keywords' => ['imei', 'imei list']],
        ['label' => 'Live Tracker', 'url' => url('/admin/tracker'), 'keywords' => ['live', 'tracker', 'tracking', 'logs']],
        ['label' => 'Manage Test Plans', 'url' => url('/admin/test-plans'), 'keywords' => ['test', 'plans', 'automation']],
        ['label' => 'Test Validation', 'url' => url('/admin/test-validate'), 'keywords' => ['validate', 'execution', 'run test']],
        ['label' => 'Protocol Management', 'url' => url('/admin/protocols'), 'keywords' => ['protocol', 'packet', 'alerts']],
        ['label' => 'View Settings', 'url' => url('/admin/view-template'), 'keywords' => ['settings', 'template', 'config']],
        ['label' => 'View Devices', 'url' => url('/admin/view-device-assign'), 'keywords' => ['device', 'assigned']],
    ];
} elseif ($userType === 'support') {
    $globalSearchItems = [
        ['label' => 'Dashboard', 'url' => url('/support'), 'keywords' => ['home', 'dashboard']],
        ['label' => 'Ticket Management', 'url' => url('/support/view-ticket'), 'keywords' => ['ticket', 'raise ticket', 'issue', 'support ticket']],
        ['label' => 'User Approval', 'url' => url('/support/view-user-approval-request'), 'keywords' => ['approval', 'user approval', 'account approval']],
        ['label' => 'View Devices', 'url' => url('/support/view-device'), 'keywords' => ['device', 'devices', 'imei']],
        ['label' => 'Assign Devices', 'url' => url('/support/assign-device'), 'keywords' => ['assign device', 'multiple device']],
        ['label' => 'Manage Trackers', 'url' => url('/support/imei-devices'), 'keywords' => ['tracker', 'imei devices', 'manage tracker']],
        ['label' => 'Live Track / Logs', 'url' => url('/support/tracker'), 'keywords' => ['live', 'tracking', 'logs', 'tracker logs']],
        ['label' => 'Manage Test Plans', 'url' => url('/support/test-plans'), 'keywords' => ['test', 'plans', 'automation']],
        ['label' => 'Test Validation', 'url' => url('/support/test-validate'), 'keywords' => ['validate', 'test validate', 'execution']],
        ['label' => 'Protocol Management', 'url' => url('/support/protocols'), 'keywords' => ['protocol', 'packet', 'alerts']],
        ['label' => 'View Settings', 'url' => url('/support/view-template'), 'keywords' => ['settings', 'template', 'config']],
    ];
} elseif ($userType === 'reseller') {
    $globalSearchItems = [
        ['label' => 'Dashboard', 'url' => url('/reseller'), 'keywords' => ['home', 'dashboard']],
        ['label' => 'View Account', 'url' => url('/reseller/view-user'), 'keywords' => ['account', 'users']],
        ['label' => 'Assigned Devices', 'url' => url('/reseller/view-device-assign'), 'keywords' => ['device', 'assigned devices']],
        ['label' => 'View Settings', 'url' => url('/reseller/view-template'), 'keywords' => ['settings', 'template']],
    ];
} else {
    $globalSearchItems = [
        ['label' => 'Dashboard', 'url' => url('/user'), 'keywords' => ['home', 'dashboard']],
        ['label' => 'View Device', 'url' => url('/user/view-device'), 'keywords' => ['device', 'devices']],
        ['label' => 'View Settings', 'url' => url('/user/view-template'), 'keywords' => ['settings', 'template']],
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="description" content="">
    <meta name="keywords" content="JSD ELECTRONICS">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="author" content="JSD Electronics">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="{{ asset('favicon.svg') . '?v=1.2' }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') . '?v=1.2' }}">
    <title>@yield('title', 'GPS Control Panel')</title>

    <!-- Start Global plugin css -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="{{ asset('assets/css/global-plugins.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/jquery-icheck/skins/all.css') }}" rel="stylesheet" />

    <!-- This page plugin css start -->
    <link href="{{ asset('assets/vendors/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" />

    <!-- This page plugin css end -->
    <link href="{{ asset('assets/css/table-responsive.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendors/datatable/bootstrap/dataTables.bootstrap.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="{{ asset('assets/css/theme.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-responsive.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/class-helpers.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/fonts/Open-Sans/open-sans.css?family=Open+Sans:300,400,700') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendors/jquery.multi-select/css/multi-select.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendors/select2/select2.css') }}" rel="stylesheet" />
    <link href="{{ \App\Support\PortalAssets::publicUrl('assets/css/custom.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ \App\Support\PortalAssets::publicUrl('assets/css/portal/tokens.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.gps-notifications-assets')


    <!-- Page loader + header / nav (assets/css/portal/layout-shell.css) -->
    <link rel="stylesheet" href="{{ \App\Support\PortalAssets::publicUrl('assets/css/portal/layout-shell.css') }}">
    {{-- Page-specific portal CSS is stacked after @yield('content') so it wins over theme/custom.css like former inline <style> in Blade. --}}
</head>

<body id="default-scheme" class="{{ $userType === 'reseller' ? 'user-reseller' : '' }}">
    @include('partials.gps-flash-pull')

    <!-- ===== Global Page Loader ===== -->
    <div id="page-loader" role="status" aria-label="Loading page">
        <div class="loader-icon">
            <div class="loader-ring"></div>
            <div class="loader-ring-2"></div>
            <div class="loader-pin">
                <!-- GPS Map Pin SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                </svg>
            </div>
        </div>
        <div class="loader-text">GPS Control Panel</div>
        <div class="loader-dots" style="margin-top:10px;">
            <span></span><span></span><span></span>
        </div>
    </div>
    <!-- ===== End Page Loader ===== -->

    <section id="container">
        <!--header start-->
        <header class="header fixed-top clearfix">
            <!--logo start-->
            <div class="brand">
                @if(Auth::user()->user_type == 'Admin')
                <a href="/admin" class="logo" style="margin-top: 1px; margin-left: 1px;">
                    @include('partials.brand-gps-mark')
                    <span class="brand-area-label">Admin Area</span>
                </a>
                @elseif(Auth::user()->user_type == 'Reseller')
                <a href="/reseller" class="logo" style="margin-top: 1px; margin-left: 1px;">
                    @include('partials.brand-gps-mark')
                    <span class="brand-area-label">Manufacturer Area</span>
                </a>
                @elseif(Auth::user()->user_type == 'Support')
                <a href="/support" class="logo" style="margin-top: 1px; margin-left: 1px;">
                    @include('partials.brand-gps-mark')
                    <span class="brand-area-label">Support Area</span>
                </a>
                @else
                <a href="/user" class="logo" style="margin-top: 1px; margin-left: 1px;">
                    @include('partials.brand-gps-mark')
                    <span class="brand-area-label">Dealer Area</span>
                </a>
                @endif
                <div class="sidebar-toggle-box" style="margin-top: 1px; margin-left: 1px;">
                    <div class="fa fa-bars"></div>
                </div>
            </div>
            <!--logo end-->
            <div class="top-nav">
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item dropdown">
                        <div class="d-flex align-items-center px-3 py-2 rounded bg-primary text-white shadow-sm">
                            <i class="fa fa-code-branch mr-2"></i>
                            <span
                                class="font-weight-bold padding-left-6 padding-right-6">v{{$latestVersion->version}}</span>
                        </div>
                    </li>

                    <!-- 🔔 Notification Dropdown -->
                    @if(Auth::user()->user_type == 'Admin')
                    <li class="nav-item dropdown gps-notif-nav">
                        <a class="nav-link gps-notif-trigger" href="#" id="notificationDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            aria-label="Notifications{{ $ticketCount > 0 ? ' (' . $ticketCount . ' unread)' : '' }}">
                            <span class="gps-notif-chip{{ $ticketCount > 0 ? ' gps-notif-chip--has-count' : '' }}">
                                <span class="gps-notif-chip__bell" aria-hidden="true">
                                    <i class="fa fa-bell"></i>
                                    @if($ticketCount > 0)
                                    <span class="gps-notif-chip__dot" aria-hidden="true"></span>
                                    @endif
                                </span>
                                @if($ticketCount > 0)
                                <span class="gps-notif-chip__count" id="notificationCount" data-digits="{{ strlen((string) $ticketCount) }}">{{ $ticketCount }}</span>
                                @endif
                            </span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right notif-dropdown-menu"
                            aria-labelledby="notificationDropdown">

                            <!-- Notification Header -->
                            <div class="notif-header">
                                <h6>Notifications
                                    @if($ticketCount > 0)
                                    <span style="background:rgba(118,207,28,0.2);color:#76CF1C;font-size:10px;padding:2px 7px;border-radius:10px;font-weight:700;">
                                        {{ $ticketCount }} new
                                    </span>
                                    @endif
                                </h6>
                                <a href="#" class="notif-mark-read">Mark all as read</a>
                            </div>

                            <!-- Notification List -->
                            <div class="notif-list" id="notificationList">
                                @forelse($tickets as $ticket)
                                <a class="notif-item {{ $ticket->is_read ? '' : 'unread' }}"
                                    href="/admin/tickets">
                                    <div class="notif-icon-chip {{ $ticket->type === 'error' ? 'error' : ($ticket->type === 'updation' ? 'update' : 'default') }}">
                                        @if($ticket->type === 'error')
                                        <i class="fa fa-exclamation-circle"></i>
                                        @elseif($ticket->type === 'updation')
                                        <i class="fa fa-refresh"></i>
                                        @else
                                        <i class="fa fa-bell"></i>
                                        @endif
                                    </div>
                                    <div class="notif-body">
                                        <div class="notif-title">{{ ucfirst($ticket->subject) }} &mdash; {{ ucfirst($ticket->type) }}</div>
                                        <div class="notif-desc">{{ $ticket->description }}</div>
                                        <div class="notif-time">
                                            <i class="fa fa-clock-o" style="font-size:10px;"></i>
                                            {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>
                                @empty
                                <div class="notif-empty">
                                    <i class="fa fa-bell-slash-o"></i>
                                    <p>No new notifications</p>
                                </div>
                                @endforelse
                            </div>

                            <!-- Footer -->
                            <a class="notif-footer" href="/admin/tickets">
                                <i class="fa fa-eye"></i> View all notifications
                            </a>

                        </div>
                    </li>

                    @endif
                    <li class="search-box">
                        <input type="text" id="global-nav-search" class="form-control search" list="global-nav-search-options"
                            placeholder="Search pages or table data">
                        <datalist id="global-nav-search-options">
                            @foreach($globalSearchItems as $item)
                            <option value="{{ $item['label'] }}"></option>
                            @endforeach
                        </datalist>
                    </li>
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="user-profile dropdown-toggle" data-toggle="dropdown"
                            aria-expanded="false">
                            {{-- Dummy Avatar: First letter of email --}}
                            <span class="nav-user-avatar">{{ strtoupper(substr(Auth::user()->email, 0, 1)) }}</span>
                            <span class="nav-email-text">{{ Auth::user()->email }}</span>
                            <span class="fa fa-angle-down"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-usermenu animated fadeInUp pull-right">
                            @if (Auth::user()->user_type != 'Admin' && Auth::user()->user_type != "Support")
                            <li>
                                <a
                                    href="{{ url(strtolower(Auth::user()->user_type) . '/edit-user/' . Auth::user()->user_type . '/' . Auth::user()->id) }}">Profile</a>
                            </li>
                            @endif
                            <li>
                                <a class="hvr-bounce-to-right" href="{{ route('logout') }}" onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                    <i class="icon-login pull-right"></i>
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </header>
        <!--header end-->

        <!--sidebar start-->
        <aside>
            <div id="sidebar" class="nav-collapse md-box-shadowed">
                @php
                $userType = Auth::check() ? Auth::user()->user_type : '';
                $areaLabel = 'User Area';
                if ($userType === 'Admin') {
                $areaLabel = 'Admin Area';
                } elseif ($userType === 'Reseller') {
                $areaLabel = 'Manufacturer Area';
                } elseif ($userType === 'Support') {
                $areaLabel = 'Support Area';
                } elseif ($userType === 'User') {
                $areaLabel = 'Dealer Area';
                }
                @endphp

                <!-- Mobile Sidebar Header -->
                <div class="sidebar-mobile-header">
                    <div class="sidebar-mobile-profile">
                        <div class="sidebar-mobile-avatar">
                            <svg class="shield-svg" viewBox="0 0 24 24" width="24" height="24">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="sidebar-mobile-userinfo">
                            <span class="sidebar-mobile-greeting">Hi, {{ ucfirst(Auth::user()->name) }}</span>
                            <span class="sidebar-mobile-role">{{ $areaLabel }}</span>
                        </div>
                    </div>
                    <button type="button" class="sidebar-mobile-close" id="sidebarMobileCloseBtn" aria-label="Close Sidebar">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <!-- sidebar menu start-->
                <div class="leftside-navigation leftside-navigation-scroll">
                    <ul class="sidebar-menu" id="nav-accordion">
                        @if (Auth::user()->user_type == 'Admin')

                        <li class="{{ request()->is('admin') ? 'active' : '' }}">
                            <a href="{{ url('/admin') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>

                        <li class="{{ request()->is('admin/tickets') ? 'active' : '' }}">
                            <a href="{{ url('/admin/tickets') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/tickets') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-tag fa-2x'></span>
                                <span>Raised Tickets</span>
                            </a>
                        </li>

                        <li class="{{ request()->is('admin/view-imeis') ? 'active' : '' }}">
                            <a href="{{ url('/admin/view-imeis') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/view-imeis') ? 'active' : '' }}">
                                <span class="icon-sidebar icon-phone fa-2x"></span>
                                <span>IMEI Management</span>
                            </a>
                        </li>

                        <li class="{{ request()->is('admin/view-jig') ? 'active' : '' }}">
                            <a href="{{ url('/admin/view-jig') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/view-jig') ? 'active' : '' }}">
                                <span class="icon-sidebar icon-wrench fa-2x"></span>
                                <span>JIG Management</span>
                            </a>
                        </li>

                        <li class="{{ request()->is('admin/version-control') ? 'active' : '' }}">
                            <a href="{{ url('/admin/version-control') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/version-control') ? 'active' : '' }}">
                                <span class="icon-sidebar icon-diamond fa-2x"></span>
                                <span>Version Management</span>
                            </a>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('admin/add-user', 'admin/view-user', 'admin/view-user-approval-request') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/add-user', 'admin/view-user', 'admin/view-user-approval-request') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-user') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/add-user') }}"
                                        class="{{ request()->is('admin/add-user') ? 'active' : '' }}">Add Account</a>
                                </li>
                                <li class="{{ request()->is('admin/view-user') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-user') }}"
                                        class="{{ request()->is('admin/view-user') ? 'active' : '' }}">View Account</a>
                                </li>
                                <li class="{{ request()->is('admin/view-user-approval-request') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-user-approval-request') }}"
                                        class="{{ request()->is('admin/view-user-approval-request') ? 'active' : '' }}">
                                        View User Approval
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('admin/add-device', 'admin/add-Multipledevice', 'admin/view-device-assign', 'admin/view-device-unassign') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/add-device', 'admin/add-Multipledevice', 'admin/view-device-assign', 'admin/view-device-unassign') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-device') ? 'active' : '' }}">
                                    <a href="{{ url('admin/add-device') }}"
                                        class="{{ request()->is('admin/add-device') ? 'active' : '' }}">Add Device</a>
                                </li>
                                <li class="{{ request()->is('admin/add-Multipledevice') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/add-Multipledevice') }}"
                                        class="{{ request()->is('admin/add-Multipledevice') ? 'active' : '' }}">Add
                                        Multiple Device</a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-assign') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/view-device-assign') }}"
                                        class="{{ request()->is('admin/view-device-assign') ? 'active' : '' }}">Assigned
                                        Devices</a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-unassign') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/view-device-unassign') }}"
                                        class="{{ request()->is('admin/view-device-unassign') ? 'active' : '' }}">Unassigned
                                        Devices</a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('admin/certificates') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/certificates') ? 'active' : '' }}">
                                <span class='icon-sidebar fa fa-certificate fa-2x'></span><span>Certificate Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/certificates') ? 'active' : '' }}">
                                    <a href="{{ url('admin/certificates') }}"
                                        class="{{ request()->is('admin/certificates') ? 'active' : '' }}">Manage Certificates</a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('admin/imei-devices', 'admin/test-plans*', 'admin/protocols*', 'admin/packet-analyzer*') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/imei-devices', 'admin/test-plans*', 'admin/protocols*', 'admin/packet-analyzer*') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-science fa-2x'></span><span>Testing Tools</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/imei-devices') ? 'active' : '' }}">
                                    <a href="{{ url('admin/imei-devices') }}"
                                        class="{{ request()->is('admin/imei-devices') ? 'active' : '' }}">
                                        Manage Trackers
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/test-plans*') ? 'active' : '' }}">
                                    <a href="{{ url('admin/test-plans') }}"
                                        class="{{ request()->is('admin/test-plans*') ? 'active' : '' }}">
                                        Manage Test Plans
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/protocols*') ? 'active' : '' }}">
                                    <a href="{{ route('protocols.index') }}"
                                        class="{{ request()->is('admin/protocols*') ? 'active' : '' }}">
                                        Protocol Management
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/packet-analyzer*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.packet-analyzer.index') }}"
                                        class="{{ request()->is('admin/packet-analyzer*') ? 'active' : '' }}">
                                        Packet Analyzer
                                    </a>
                                </li>
                            </ul>
                        </li>



                        <li
                            class='sub-menu {{ request()->is('admin/add-template', 'admin/view-template', 'admin/assign-setting-bulk') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/add-template', 'admin/view-template', 'admin/assign-setting-bulk') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                @if (\App\Helpers\PermissionHelper::hasPermission('settings_management.create'))
                                <li class="{{ request()->is('admin/add-template') ? 'active' : '' }}">
                                    <a href="{{ url('admin/add-template') }}"
                                        class="{{ request()->is('admin/add-template') ? 'active' : '' }}">
                                        Add Settings
                                    </a>
                                </li>
                                @endif
                                <li class="{{ request()->is('admin/view-template') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-template') }}"
                                        class="{{ request()->is('admin/view-template') ? 'active' : '' }}">
                                        View Settings
                                    </a>
                                </li>
                                @if(auth()->user()->hasPermission('settings_management.assign_bulk'))
                                <li class="{{ request()->is('admin/assign-setting-bulk') ? 'active' : '' }}">
                                    <a href="<?php echo url('admin/assign-setting-bulk'); ?>"
                                        class="{{ request()->is('admin/assign-setting-bulk') ? 'active' : '' }}">
                                        Assign Settings Bulk
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('admin/add-device-category', 'admin/view-device-category', 'admin/restore-device-category', 'admin/view-device-category-fields') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/add-device-category', 'admin/view-device-category', 'admin/restore-device-category', 'admin/view-device-category-fields') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-safe fa-2x'></span><span>Device Category</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-device-category') ? 'active' : '' }}">
                                    <a href="{{ url('admin/add-device-category') }}"
                                        class="{{ request()->is('admin/add-device-category') ? 'active' : '' }}">
                                        Add Device Category
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-category') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-device-category') }}"
                                        class="{{ request()->is('admin/view-device-category') ? 'active' : '' }}">
                                        View Device Category
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/restore-device-category') ? 'active' : '' }}">
                                    <a href="{{ url('admin/restore-device-category') }}"
                                        class="{{ request()->is('admin/restore-device-category') ? 'active' : '' }}">
                                        Restore Device Category
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-category-fields') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-device-category-fields') }}"
                                        class="{{ request()->is('admin/view-device-category-fields') ? 'active' : '' }}">
                                        View Data Fields
                                    </a>
                                </li>
                            </ul>
                        </li>



                        <li
                            class='sub-menu {{ request()->is('admin/view-esim-customers', 'admin/view-models', 'admin/view-firmware', 'admin/view-backend', 'admin/view-esim') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/view-esim-customers', 'admin/view-models', 'admin/view-firmware', 'admin/view-backend', 'admin/view-esim') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-shuffle fa-2x'></span><span>Firmware Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/view-esim-customers') ? 'active' : '' }}">
                                    <a href="{{url('admin/view-esim-customers')}}"
                                        class="{{ request()->is('admin/view-esim-customers') ? 'active' : '' }}">
                                        View ESIM Masters
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/view-models') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-models') }}"
                                        class="{{ request()->is('admin/view-models') ? 'active' : '' }}">
                                        View Models
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/view-firmware') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-firmware') }}"
                                        class="{{ request()->is('admin/view-firmware') ? 'active' : '' }}">
                                        View Firmware
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/view-backend') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-backend') }}"
                                        class="{{ request()->is('admin/view-backend') ? 'active' : '' }}">
                                        View Backend
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/view-esim') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-esim') }}"
                                        class="{{ request()->is('admin/view-esim') ? 'active' : '' }}">
                                        View ESIM
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="{{ request()->is('admin/manage-permissions') ? 'active' : '' }}">
                            <a href="{{ url('/admin/manage-permissions') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/manage-permissions') ? 'active' : '' }}">
                                <span class='icon-sidebar fa fa-lock fa-2x'></span><span>Manage Permissions</span>
                            </a>
                        </li>

                        @elseif (Auth::user()->user_type == 'Reseller')

                        <li class="{{ request()->is('reseller') ? 'active' : '' }}">
                            <a href="{{ url('/reseller') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>

                        @if (\App\Helpers\PermissionHelper::canViewModule('account_management'))
                        <li
                            class='sub-menu {{ request()->is('reseller/add-user', 'reseller/view-user') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/add-user', 'reseller/view-user') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/add-user') ? 'active' : '' }}">
                                    <a href="{{ url('/reseller/add-user') }}"
                                        class="{{ request()->is('reseller/add-user') ? 'active' : '' }}">
                                        Add Account
                                    </a>
                                </li>
                                <li class="{{ request()->is('reseller/view-user') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/view-user') }}"
                                        class="{{ request()->is('reseller/view-user') ? 'active' : '' }}">
                                        View Account
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if (\App\Helpers\PermissionHelper::canViewModule('device_management'))
                        <li
                            class='sub-menu {{ request()->is('reseller/view-device-assign', 'reseller/view-device-unassign') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/view-device-assign', 'reseller/view-device-unassign') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/view-device-assign') ? 'active' : '' }}">
                                    <a href="{{ url('/reseller/view-device-assign') }}"
                                        class="{{ request()->is('reseller/view-device-assign') ? 'active' : '' }}">
                                        Assigned Devices
                                    </a>
                                </li>
                                <li class="{{ request()->is('reseller/view-device-unassign') ? 'active' : '' }}">
                                    <a href="{{ url('/reseller/view-device-unassign') }}"
                                        class="{{ request()->is('reseller/view-device-unassign') ? 'active' : '' }}">
                                        Unassigned Devices
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if (\App\Helpers\PermissionHelper::canViewModule('certificate_management'))
                        <li
                            class='sub-menu {{ request()->is('reseller/certificates') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/certificates') ? 'active' : '' }}">
                                <span class='icon-sidebar fa fa-certificate fa-2x'></span><span>Certificate Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/certificates') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/certificates') }}"
                                        class="{{ request()->is('reseller/certificates') ? 'active' : '' }}">Manage Certificates</a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if (\App\Helpers\PermissionHelper::canViewModule('settings_management'))
                        <li
                            class='sub-menu {{ request()->is('reseller/add-template', 'reseller/view-template', 'reseller/assign-setting-bulk') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/add-template', 'reseller/view-template', 'reseller/assign-setting-bulk') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/add-template') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/add-template') }}"
                                        class="{{ request()->is('reseller/add-template') ? 'active' : '' }}">
                                        Add Settings
                                    </a>
                                </li>
                                <li class="{{ request()->is('reseller/view-template') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/view-template') }}"
                                        class="{{ request()->is('reseller/view-template') ? 'active' : '' }}">
                                        View Settings
                                    </a>
                                </li>
                                <li class="{{ request()->is('reseller/assign-setting-bulk') ? 'active' : '' }}">
                                    <a href="<?php echo url('reseller/assign-setting-bulk'); ?>"
                                        class="{{ request()->is('reseller/assign-setting-bulk') ? 'active' : '' }}">
                                        Assign Settings Bulk
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        <li class="{{ request()->is('reseller/view-device-category') ? 'active' : '' }}">
                            <a href="{{ url('reseller/view-device-category') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/view-device-category') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>View Device Category</span>
                            </a>
                        </li>

                        <li class="{{ request()->is('reseller/manage-child-permissions') ? 'active' : '' }}">
                            <a href="{{ url('/reseller/manage-child-permissions') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/manage-child-permissions') ? 'active' : '' }}">
                                <span class='icon-sidebar fa fa-lock fa-2x'></span><span>Manage Permissions</span>
                            </a>
                        </li>
                        @else
                        @if (Auth::user()->user_type == 'User')

                        <li class="{{ request()->is('user') ? 'active' : '' }}">
                            <a href="{{ url('/user') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('user') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>

                        @if (\App\Helpers\PermissionHelper::canViewModule('device_management'))
                        <li class='sub-menu {{ request()->is('user/view-device') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('user/view-device') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('user/view-device') ? 'active' : '' }}">
                                    <a href="{{ url('user/view-device') }}"
                                        class="{{ request()->is('user/view-device') ? 'active' : '' }}">
                                        View Device
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if (\App\Helpers\PermissionHelper::canViewModule('certificate_management'))
                        <li
                            class='sub-menu {{ request()->is('user/certificates') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('user/certificates') ? 'active' : '' }}">
                                <span class='icon-sidebar fa fa-certificate fa-2x'></span><span>Certificate Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('user/certificates') ? 'active' : '' }}">
                                    <a href="{{ url('user/certificates') }}"
                                        class="{{ request()->is('user/certificates') ? 'active' : '' }}">Manage Certificates</a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if (\App\Helpers\PermissionHelper::canViewModule('settings_management'))
                        <li
                            class='sub-menu {{ request()->is('user/add-template', 'user/view-template', 'user/assign-setting-bulk') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('user/add-template', 'user/view-template', 'user/assign-setting-bulk') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('user/add-template') ? 'active' : '' }}">
                                    <a href="{{ url('user/add-template') }}"
                                        class="{{ request()->is('user/add-template') ? 'active' : '' }}">
                                        Add Settings
                                    </a>
                                </li>
                                <li class="{{ request()->is('user/view-template') ? 'active' : '' }}">
                                    <a href="{{ url('user/view-template') }}"
                                        class="{{ request()->is('user/view-template') ? 'active' : '' }}">
                                        View Settings
                                    </a>
                                </li>
                                <li class="{{ request()->is('user/assign-setting-bulk') ? 'active' : '' }}">
                                    <a href="{{ url('user/assign-setting-bulk') }}"
                                        class="{{ request()->is('user/assign-setting-bulk') ? 'active' : '' }}">
                                        Assign Settings Bulk
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @endif
                        @if (Auth::user()->user_type == 'Support')

                        <li class="{{ request()->is('support') ? 'active' : '' }}">
                            <a href="{{ url('/support') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>

                        <li class="{{ request()->is('support/view-ticket') ? 'active' : '' }}">
                            <a href="{{  url('/support/view-ticket') }}"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/view-ticket') ? 'active' : '' }}">
                                <span class='icon-sidebar icon-tag fa-2x'></span>
                                <span>Ticket Management</span>
                            </a>
                        </li>

                        <li class='sub-menu {{ request()->is('support/view-user-approval-request') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/view-user-approval-request') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('support/view-user-approval-request') ? 'active' : '' }}">
                                    <a href="{{ url('support/view-user-approval-request') }}"
                                        class="{{ request()->is('support/view-user-approval-request') ? 'active' : '' }}">
                                        View User Approval
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('support/view-device', 'support/add-Multipledevice') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/view-device', 'support/add-Multipledevice') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('support/view-device') ? 'active' : '' }}">
                                    <a href="{{ url('support/view-device') }}"
                                        class="{{ request()->is('support/view-device') ? 'active' : '' }}">
                                        View Device
                                    </a>
                                </li>
                                <li class="{{ request()->is('support/add-Multipledevice') ? 'active' : '' }}">
                                    <a href="{{ url('support/assign-device') }}"
                                        class="{{ request()->is('support/add-Multipledevice') ? 'active' : '' }}">
                                        Assign Devices
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('support/add-template', 'support/view-template') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/add-template', 'support/view-template') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                @if (\App\Helpers\PermissionHelper::hasPermission('settings_management.create'))
                                <li class="{{ request()->is('support/add-template') ? 'active' : '' }}">
                                    <a href="{{ route('template.add') }}"
                                        class="{{ request()->is('support/add-template') ? 'active' : '' }}">
                                        Add Settings
                                    </a>
                                </li>
                                @endif
                                <li class="{{ request()->is('support/view-template') ? 'active' : '' }}">
                                    <a href="{{ route('template.view') }}"
                                        class="{{ request()->is('support/view-template') ? 'active' : '' }}">
                                        View Settings
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li
                            class='sub-menu {{ request()->is('support/imei-devices*', 'support/test-plans*', 'support/protocols*', 'support/packet-analyzer*') ? 'active' : '' }}'>
                            <a href="#"
                                class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/imei-devices*', 'support/test-plans*', 'support/protocols*', 'support/packet-analyzer*') ? 'active' : '' }}">
                                <span class='icon-sidebar pe-7s-science fa-2x'></span><span>Testing Tools</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('support/imei-devices*') ? 'active' : '' }}">
                                    <a href="{{ url('support/imei-devices') }}"
                                        class="{{ request()->is('support/imei-devices*') ? 'active' : '' }}">
                                        Manage Trackers
                                    </a>
                                </li>
                                <li class="{{ request()->is('support/test-plans*') ? 'active' : '' }}">
                                    <a href="{{ url('support/test-plans') }}"
                                        class="{{ request()->is('support/test-plans*') ? 'active' : '' }}">
                                        Manage Test Plans
                                    </a>
                                </li>
                                <li class="{{ request()->is('support/protocols*') ? 'active' : '' }}">
                                    <a href="{{ route('support.protocols.index') }}"
                                        class="{{ request()->is('support/protocols*') ? 'active' : '' }}">
                                        Protocol Management
                                    </a>
                                </li>
                                <li class="{{ request()->is('support/packet-analyzer*') ? 'active' : '' }}">
                                    <a href="{{ route('support.packet-analyzer.index') }}"
                                        class="{{ request()->is('support/packet-analyzer*') ? 'active' : '' }}">
                                        Packet Analyzer
                                    </a>
                                </li>
                            </ul>
                        </li>





                        @endif
                        <!--<li class="{{ request()->is('user') ? 'active' : '' }}">-->
                        <!--    <a href="{{ url('/user') }}" class="hvr-bounce-to-right-sidebar-parent">-->
                        <!--        <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>-->
                        <!--    </a>-->
                        <!--</li>-->
                        <!--<li class='sub-menu {{ request()->is('user/*') ? 'active' : '' }}'>-->
                        <!--    <a href="#" class="hvr-bounce-to-right-sidebar-parent">-->
                        <!--        <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>-->
                        <!--    </a>-->
                        <!--    <ul class='sub'>-->
                        <!--        <li class="{{ request()->is('user/view-device') ? 'active' : '' }}">-->
                        <!--            <a href="{{ route('device.view') }}">View Device</a>-->
                        <!--        </li>-->
                        <!--    </ul>-->
                        <!--</li>-->
                        <!--<li class='sub-menu {{ request()->is('user/add-template*', 'user/view-template*') ? 'active' : '' }}'>-->
                        <!--    <a href="#" class="hvr-bounce-to-right-sidebar-parent">-->
                        <!--        <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>-->
                        <!--    </a>-->
                        <!--    <ul class='sub'>-->
                        <!--        <li class="{{ request()->is('user/add-template') ? 'active' : '' }}">-->
                        <!--            <a href="{{ route('template.add') }}">Add Settings</a>-->
                        <!--        </li>-->
                        <!--        <li class="{{ request()->is('user/view-template') ? 'active' : '' }}">-->
                        <!--            <a href="{{ route('template.view') }}">View Settings</a>-->
                        <!--        </li>-->
                        <!--    </ul>-->
                        <!--</li>-->
                        @endif

                        {{-- Mobile logout button --}}
                        <li class="sidebar-logout-mobile">
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="hvr-bounce-to-right-sidebar-parent">
                                <span class="icon-sidebar fa fa-sign-out fa-2x"></span><span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- sidebar menu end-->
            </div>
        </aside>
        <!--sidebar end-->
        @yield('content')
        @stack('styles')
    </section>
    <!--/.container-->

    <!--===== Footer Start ========-->
    <script src="{{ asset('assets/js/global-plugins.js') }}"></script>
    <!-- <script src="{{ asset('assets/vendors/skycons/skycons.js') }}"></script> -->
    <script src="{{ asset('assets/js/tooltipster.js') }}"></script>
    <script src="{{ asset('assets/js/tables.js') }}"></script>
    <script src="{{ asset('assets/js/table_editable.js') }}"></script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/dashboard-green.js') }}"></script>
    <script src="{{ asset('assets/js/forms.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js?nocache=' . time()) }}"></script>
    <script src="{{ asset('assets/js/form-wizard.js') }}"></script>
    <script src="{{ asset('assets/js/form-plupload.js') }}"></script>
    <script src="{{ asset('assets/js/form-x-editable.js') }}"></script>
    <script src="{{ asset('assets/js/portal.js') }}"></script>
    @stack('scripts')

    <!-- ===== Page Loader Script ===== -->
    <script>
        (function() {
            var loader = document.getElementById('page-loader');
            if (!loader) return;

            // ---- Show for min 1.5s then fade out ----
            var MIN_SHOW_MS = 1500;
            var startTime = Date.now();

            function hideLoader() {
                var elapsed = Date.now() - startTime;
                var remaining = Math.max(0, MIN_SHOW_MS - elapsed);
                setTimeout(function() {
                    loader.classList.add('loader-hidden');
                    setTimeout(function() {
                        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
                    }, 600);
                }, remaining);
            }

            if (document.readyState === 'complete') {
                hideLoader();
            } else {
                window.addEventListener('load', hideLoader);
            }

            // Hard fallback: 8s
            setTimeout(function() {
                if (loader && loader.parentNode) {
                    loader.classList.add('loader-hidden');
                    setTimeout(function() {
                        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
                    }, 600);
                }
            }, 8000);
        })();
    </script>
    <script>
        (function() {
            var searchInput = document.getElementById('global-nav-search');
            if (!searchInput) return;

            var quickLinks = @json($globalSearchItems);

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function tryFilterCurrentDataTable(query) {
                if (!window.jQuery || !$.fn || !$.fn.dataTable) return false;

                try {
                    var tablesApi = $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    });
                    if (!tablesApi || tablesApi.count() === 0) return false;
                    tablesApi.search(query).draw();
                    return true;
                } catch (error) {
                    return false;
                }
            }

            function pickBestRoute(query) {
                var q = normalize(query);
                if (!q) return null;

                var best = null;
                quickLinks.forEach(function(item) {
                    var score = 0;
                    var label = normalize(item.label);
                    var keywords = Array.isArray(item.keywords) ? item.keywords.map(normalize) : [];

                    if (label === q) score += 120;
                    if (label.indexOf(q) !== -1) score += 70;

                    keywords.forEach(function(keyword) {
                        if (!keyword) return;
                        if (keyword === q) score += 100;
                        else if (keyword.indexOf(q) !== -1) score += 40;
                        else if (q.indexOf(keyword) !== -1) score += 20;
                    });

                    if (!best || score > best.score) {
                        best = {
                            score: score,
                            item: item
                        };
                    }
                });

                return best && best.score > 0 ? best.item : null;
            }

            function executeSearch() {
                var query = normalize(searchInput.value);
                if (!query) return;

                // 1) If current page has DataTable, search there first.
                if (tryFilterCurrentDataTable(query)) return;

                // 2) Fallback to role-based page routing.
                var bestRoute = pickBestRoute(query);
                if (bestRoute && bestRoute.url) {
                    window.location.href = bestRoute.url;
                } else {
                    alert('No matching result found for your role.');
                }
            }

            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    executeSearch();
                }
            });
        })();
    </script>
    <script>
        (function() {
            var sidebarScroller = document.querySelector('.leftside-navigation');
            if (!sidebarScroller) return;

            var storageKey = 'gpscpanel-sidebar-scroll';

            // Restore sidebar scroll position after navigation.
            try {
                var savedScroll = window.localStorage.getItem(storageKey);
                if (savedScroll !== null) {
                    sidebarScroller.scrollTop = parseInt(savedScroll, 10) || 0;
                }
            } catch (error) {
                // Ignore storage access issues.
            }

            function persistSidebarScroll() {
                try {
                    window.localStorage.setItem(storageKey, String(sidebarScroller.scrollTop));
                } catch (error) {
                    // Ignore storage access issues.
                }
            }

            sidebarScroller.addEventListener('scroll', persistSidebarScroll);
            window.addEventListener('beforeunload', persistSidebarScroll);

            // Prevent hash jump for expandable parent items.
            document.addEventListener('click', function(event) {
                var trigger = event.target.closest('#nav-accordion .sub-menu > a[href="#"]');
                if (!trigger) return;
                event.preventDefault();
            });

            // Save scroll before navigating through sidebar links.
            document.addEventListener('click', function(event) {
                var link = event.target.closest('#nav-accordion a[href]');
                if (!link) return;
                var href = link.getAttribute('href') || '';
                if (href && href !== '#' && !href.startsWith('javascript:')) {
                    persistSidebarScroll();
                }
            });
        })();
    </script>
    <script>
        (function() {
            if (!window.jQuery) return;
            var $sidebarScroll = $('.leftside-navigation-scroll');
            if (!$sidebarScroll.length) return;

            function disableSidebarNiceScroll() {
                try {
                    var ns = $sidebarScroll.getNiceScroll();
                    if (ns && ns.length) {
                        ns.hide();
                        ns.remove();
                    }
                } catch (error) {
                    // ignore plugin timing errors
                }

                // Safety: hide any leftover thin rails near left sidebar
                $('.nicescroll-rails').each(function() {
                    var left = parseInt(this.style.left || '-1', 10);
                    if (left >= 0 && left < 120) {
                        this.style.display = 'none';
                        this.style.opacity = '0';
                        this.style.visibility = 'hidden';
                    }
                });
            }

            $(window).on('load', disableSidebarNiceScroll);
            setTimeout(disableSidebarNiceScroll, 250);
            setTimeout(disableSidebarNiceScroll, 1000);
        })();
    </script>
    <!-- ===== End Page Loader Script ===== -->
    @yield('scripts')

    <!-- ===== Mobile Sidebar Toggle ===== -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <script>
        (function() {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebarOverlay');
            var mainContent = document.getElementById('main-content');
            if (!sidebar || !overlay) return;

            // Mobile close button handler
            var closeBtn = document.getElementById('sidebarMobileCloseBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }

            function isMobile() {
                return window.innerWidth <= 991;
            }

            function isSidebarOpen() {
                return sidebar.classList.contains('mobile-open') ||
                    sidebar.classList.contains('show-left-bar-mobile');
            }

            function openSidebar() {
                sidebar.classList.add('mobile-open');
                sidebar.classList.remove('hide-left-bar');
                overlay.classList.add('active');
                if (mainContent) {
                    mainContent.classList.remove('merge-left');
                }
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('mobile-open', 'show-left-bar-mobile');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function(e) {
                var toggleBtn = e.target.closest('.sidebar-toggle-box');
                if (!toggleBtn || !isMobile()) return;
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                if (isSidebarOpen()) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }, true);

            var touchStartX = 0;
            sidebar.addEventListener('touchstart', function(e) {
                touchStartX = e.touches[0].clientX;
            }, {
                passive: true
            });
            sidebar.addEventListener('touchend', function(e) {
                var diff = touchStartX - e.changedTouches[0].clientX;
                if (diff > 60 && isMobile()) closeSidebar();
            }, {
                passive: true
            });

            // Also watch for theme.js toggling show-left-bar-mobile directly (fallback)
            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    if (!isMobile()) return;
                    mutations.forEach(function(m) {
                        if (m.attributeName === 'class') {
                            var hasOpen = sidebar.classList.contains('show-left-bar-mobile') ||
                                sidebar.classList.contains('mobile-open');
                            if (hasOpen) {
                                overlay.classList.add('active');
                            } else {
                                overlay.classList.remove('active');
                            }
                        }
                    });
                });
                observer.observe(sidebar, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

            overlay.addEventListener('click', function() {
                closeSidebar();
            });

            if (sidebar) {
                sidebar.addEventListener('click', function(e) {
                    var link = e.target.closest('a[href]');
                    if (!link || !isMobile()) return;
                    var href = link.getAttribute('href') || '';
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        setTimeout(closeSidebar, 200);
                    }
                });
            }

            window.addEventListener('resize', function() {
                if (!isMobile()) {
                    overlay.classList.remove('active');
                    sidebar.classList.remove('mobile-open');
                }
            });
        })();
    </script>
    <script>
        $(document).ready(function() {
            // Global handler for link/button clicks requiring SweetAlert
            $(document).on('click', '.swal-confirm', function(e) {
                // If it's a submit button inside a form, let the form submit handler catch it instead
                if ($(this).attr('type') === 'submit') {
                    return;
                }
                e.preventDefault();
                var $el = $(this);
                var message = $el.data('confirm-msg') || "Are you sure you want to proceed?";

                Swal.fire({
                    title: 'Confirm Deletion',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    background: '#1e293b',
                    color: '#f8fafc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var formId = $el.data('form-id');
                        if (formId) {
                            $('#' + formId).submit();
                        } else if ($el.attr('href') && $el.attr('href') !== '#') {
                            window.location.href = $el.attr('href');
                        }
                    }
                });
            });

            // Global handler for form submissions requiring SweetAlert
            $(document).on('submit', 'form.swal-confirm, form:has(button.swal-confirm[type="submit"])', function(e) {
                var $form = $(this);
                var $btn = $form.find('button.swal-confirm[type="submit"]');

                if (!$form.data('swal-confirmed')) {
                    e.preventDefault();
                    var message = $form.data('confirm-msg') || ($btn.length ? $btn.data('confirm-msg') : "Are you sure you want to proceed?");

                    Swal.fire({
                        title: 'Confirm Deletion',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        background: '#1e293b',
                        color: '#f8fafc'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $form.data('swal-confirmed', true);
                            $form.submit();
                        }
                    });
                }
            });
        });
    </script>
    @include('partials.gps-flash-scripts')
</body>

</html>