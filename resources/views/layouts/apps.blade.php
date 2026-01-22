<?php

use App\Models\TicketModel;
use App\Models\versionModel;

$tickets = TicketModel::where('is_read', 0)->get();
$latestVersion = versionModel::latest('created_at')->first();

$ticketCount = $tickets->count();
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
    <link rel="shortcut icon" href="">
    <title>GPS Control Panel</title>

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
    <link href="{{ asset('assets/css/custom.css?nocache=' . time()) }}" rel="stylesheet" />
</head>

<body id="default-scheme">
    <section id="container">
        <!--header start-->
        <header class="header fixed-top clearfix">
            <!--logo start-->
            <div class="brand">
                @if(Auth::user()->user_type =='Admin')
                <a href="/admin" class="logo">
                    Admin Area
                </a>
                @elseif(Auth::user()->user_type=='Reseller')
                <a href="/reseller" class="logo">
                    Manufacturer Area
                </a>
                @elseif(Auth::user()->user_type =='Support')
                <a href="/user" class="logo">
                    Support Area
                </a>
                @else(Auth::user()->user_type!=='User')
                <a href="/user" class="logo">
                    Dealer Area
                </a>
                @endif
                <div class="sidebar-toggle-box">
                    <div class="fa fa-bars"></div>
                </div>
            </div>
            <!--logo end-->
            <div class="top-nav">
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item dropdown">
                        <div class="d-flex align-items-center px-3 py-2 rounded bg-primary text-white shadow-sm">
                            <i class="fa fa-code-branch mr-2"></i>
                            <span class="font-weight-bold padding-left-6 padding-right-6">v{{$latestVersion->version}}</span>
                        </div>
                    </li>

                    <!-- 🔔 Notification Dropdown -->
                    @if(Auth::user()->user_type == 'Admin')
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bell fa-lg"></i>
                            @if($ticketCount > 0)
                            <span class="badge badge-danger rounded-circle position-absolute" id="notificationCount" style="font-size: 0.7rem; top: 0px; right: 0px;">
                                {{ $ticketCount }}
                            </span>
                            @endif
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow border-0" aria-labelledby="notificationDropdown"
                            style="width: 340px; max-height: 420px; overflow-y: auto; border-radius: 12px;">

                            <!-- Header -->
                            <div class="padding-5 d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light" style="width: auto;align-items: center;justify-content: space-between">
                                <h6 class="mb-0 font-weight-bold text-dark">Notifications</h6>
                                <a href="#" class="small text-muted">Mark all as read</a>
                            </div>

                            <!-- Notifications List -->
                            <div id="notificationList" class="list-group list-group-flush">
                                @forelse($tickets as $ticket)
                                <a class="list-group-item list-group-item-action d-flex align-items-start {{ $ticket->is_read ? '' : 'bg-light' }}" href="/admin/tickets">
                                    <div class="me-3">
                                        @if($ticket->type === 'error')
                                        <i class="fa fa-exclamation-circle padding-10 text-danger fa-lg"></i>
                                        @elseif($ticket->type === 'updation')
                                        <i class="fa fa-refresh text-info padding-10 fa-lg"></i>
                                        @else
                                        <i class="fa fa-bell text-secondary padding-10 fa-lg"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">
                                            {{ ucfirst($ticket->subject) }} - {{ ucfirst($ticket->type) }}
                                        </div>
                                        <small class="text-muted d-block">{{ $ticket->description }}</small>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}</small>
                                    </div>
                                </a>
                                @empty
                                <div class="p-3 text-center text-muted">
                                    No new notifications
                                </div>
                                @endforelse
                            </div>

                            <!-- Footer -->
                            <a class="d-flex padding-right-5 padding-bottom-10 dropdown-item text-center text-primary fw-bold py-2" href="/admin/tickets" style="width: auto;align-items: center;justify-content: end;">
                                View all notifications
                            </a>
                        </div>
                    </li>

                    @endif
                    <li class="search-box">
                        <input type="text" class="form-control search" placeholder="Search">
                    </li>
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <img src="{{ asset('assets/images/profile.jpg') }}" alt="image">{{ Auth::user()->email }}
                            <span class="fa fa-angle-down"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-usermenu animated fadeInUp pull-right">
                            @if (Auth::user()->user_type != 'Admin' && Auth::user()->user_type != "Support")
                            <li>
                                <a href="{{ url(strtolower(Auth::user()->user_type) . '/edit-user/' . Auth::user()->user_type . '/' . Auth::user()->id) }}">Profile</a>
                            </li>
                            @endif
                            <li>
                                <a class="hvr-bounce-to-right" href="{{ route('logout') }}" onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                    <i class="icon-login pull-right"></i>
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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
                <!-- sidebar menu start-->
                <div class="leftside-navigation leftside-navigation-scroll">
                    <ul class="sidebar-menu" id="nav-accordion">
                        @if (Auth::user()->user_type == 'Admin')
                        <li class="{{ request()->is('admin') ? 'active' : '' }}">
                            <a href="{{ url('/admin') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/tickets') ? 'active' : '' }}">
                            <a href="{{ url('/admin/tickets') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <!-- Changed icon to ticket -->
                                <span class='icon-sidebar icon-tag fa-2x'></span>
                                <span>Raised Tickets</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/view-imeis') ? 'active' : '' }}">
                            <a href="{{ url('/admin/view-imeis') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <!-- Changed icon to ticket -->
                                <span class="icon-sidebar icon-phone fa-2x"></span>

                                <span>IMEI Management</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/view-jig') ? 'active' : '' }}">
                            <a href="{{ url('/admin/view-jig') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <!-- Changed icon to ticket -->
                                <span class="icon-sidebar icon-wrench fa-2x"></span>
                                <span>JIG Management</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/version-control') ? 'active' : '' }}">
                            <a href="{{ url('/admin/version-control') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <!-- Changed icon to ticket -->
                                <span class="icon-sidebar icon-diamond fa-2x"></span>
                                <span>Version Management</span>
                            </a>
                        </li>
                        <li class='sub-menu {{ request()->is('admin/*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-user') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/add-user') }}">Add Account</a>
                                </li>
                                <li class="{{ request()->is('admin/view-user') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-user') }}">View Account</a>
                                </li>

                                <li class="{{ request()->is('admin/view-user-approval-request') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-user-approval-request') }}">View User Approval</a>
                                </li>

                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('admin/add-device*', 'admin/view-device*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-device') ? 'active' : '' }}">
                                    <a href="{{ url('admin/add-device') }}">Add Device</a>
                                </li>
                                <li class="{{ request()->is('admin/add-Multipledevice') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/add-Multipledevice') }}">Add Multiple Device</a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-assign') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/view-device-assign') }}">Assigned Devices</a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-unassign') ? 'active' : '' }}">
                                    <a href="{{ url('/admin/view-device-unassign') }}">Unassigned Devices</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('admin/add-template*', 'admin/view-template*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-template') ? 'active' : '' }}">
                                    <a href="{{ url('admin/add-template') }}">Add Settings</a>
                                </li>
                                <li class="{{ request()->is('admin/view-template') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-template') }}">View Settings</a>
                                </li>
                                <li class="{{ request()->is('admin/assign-setting-bulk') ? 'active' : '' }}"><a href="<?php echo url('admin/assign-setting-bulk'); ?>">Assign Settings Bulk</a></li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('admin/add-device-category*', 'admin/View-device-category*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-safe fa-2x'></span><span>Device Category</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/add-device-category') ? 'active' : '' }}">
                                    <a href="{{ url('admin/add-device-category') }}">Add Device Category</a>
                                </li>
                                <li class="{{ request()->is('admin/View-device-category') ? 'active' : '' }}">
                                    <a href="{{ url('admin/View-device-category') }}">View Device Category</a>
                                </li>
                                <li class="{{ request()->is('admin/restore-device-category') ? 'active' : '' }}">
                                    <a href="{{ url('admin/restore-device-category') }}">Restore Device Category</a>
                                </li>
                                <li class="{{ request()->is('admin/view-device-category-fields') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-device-category-fields') }}">View Data Fields</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('admin/view-firmware*', 'admin/view-models*', 'admin/view-backend*', 'admin/view-esim*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-shuffle fa-2x'></span><span>Firmware Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('admin/view-esim-customers') ? 'active' : '' }}"><a href="{{url('admin/view-esim-customers')}}">View ESIM Masters </a>
                                </li>
                                <li class="{{ request()->is('admin/view-models') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-models') }}">View Models</a>
                                </li>
                                <li class="{{ request()->is('admin/view-firmware') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-firmware') }}">View Firmware</a>
                                </li>
                                <li class="{{ request()->is('admin/view-backend') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-backend') }}">View Backend</a>
                                </li>
                                <li class="{{ request()->is('admin/view-esim') ? 'active' : '' }}">
                                    <a href="{{ url('admin/view-esim') }}">View ESIM</a>
                                </li>
                            </ul>
                        </li>
                        @elseif (Auth::user()->user_type == 'Reseller')
                        <li class="{{ request()->is('reseller') ? 'active' : '' }}">
                            <a href="{{ url('/reseller') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>
                        <li class='sub-menu {{ request()->is('reseller/*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/add-user') ? 'active' : '' }}">
                                    <a href="{{ url('/reseller/add-user') }}">Add Account</a>
                                </li>
                                <li class="{{ request()->is('reseller/view-user') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/view-user') }}">View Account</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('reseller/view-device*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/view-device-assign') ? 'active' : '' }}">
                                    <a href="{{ url('/reseller/view-device-assign') }}">Assigned Devices</a>
                                </li>
                                <li class="{{ request()->is('reseller/view-device-unassign') ? 'active' : '' }}">
                                    <a href="{{ url('/reseller/view-device-unassign') }}">Unassigned Devices</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('reseller/add-template*', 'reseller/view-template*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('reseller/add-template') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/add-template') }}">Add Settings</a>
                                </li>
                                <li class="{{ request()->is('reseller/view-template') ? 'active' : '' }}">
                                    <a href="{{ url('reseller/view-template') }}">View Settings</a>
                                </li>
                                <li class="{{ request()->is('reseller/assign-setting-bulk') ? 'active' : '' }}"><a href="<?php echo url('reseller/assign-setting-bulk'); ?>">Assign Settings Bulk</a>
                                </li>
                            </ul>
                        </li>
                        <li class="{{ request()->is('reseller/View-device-category') ? 'active' : '' }}">
                            <a href="{{ url('reseller/View-device-category') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>View Device Category</span>
                            </a>
                        </li>
                        @else
                        @if (Auth::user()->user_type == 'User')
                        <li class="{{ request()->is('user') ? 'active' : '' }}">
                            <a href="{{ url('/user') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>
                        <li class='sub-menu {{ request()->is('user/*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('user/view-device') ? 'active' : '' }}">
                                    <a href="{{ url('user/view-device') }}">View Device</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('user/add-template*', 'user/view-template*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('user/add-template') ? 'active' : '' }}">
                                    <a href="{{ url('user/add-template') }}">Add Settings</a>
                                </li>
                                <li class="{{ request()->is('user/view-template') ? 'active' : '' }}">
                                    <a href="{{ url('user/view-template') }}">View Settings</a>
                                </li>
                            </ul>
                        </li>
                        @endif
                        @if (Auth::user()->user_type == 'Support')
                        <li class="{{ request()->is('support') ? 'active' : '' }}">
                            <a href="{{ url('/support') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('support/view-ticket') ? 'active' : '' }}">
                            <a href="{{  url('/support/view-ticket') }}" class="hvr-bounce-to-right-sidebar-parent">
                                <!-- Changed icon to ticket -->
                                <span class='icon-sidebar icon-tag fa-2x'></span>
                                <span>Ticket Management</span>
                            </a>
                        </li>
                        <li class='sub-menu {{ request()->is('support/*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('support/view-user-approval-request') ? 'active' : '' }}">
                                    <a href="{{ url('support/view-user-approval-request') }}">View User Approval</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('support/*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('support/view-device') ? 'active' : '' }}">
                                    <a href="{{ url('support/view-device') }}">View Device</a>
                                </li>
                                <li class="{{ request()->is('support/add-Multipledevice') ? 'active' : '' }}">
                                    <a href="{{ url('support/assign-device')}}">Assign Devices</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu {{ request()->is('support/add-template*', 'user/view-template*') ? 'active' : '' }}'>
                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                <span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span>
                            </a>
                            <ul class='sub'>
                                <li class="{{ request()->is('support/add-template') ? 'active' : '' }}">
                                    <a href="{{ route('template.add') }}">Add Settings</a>
                                </li>
                                <li class="{{ request()->is('support/view-template') ? 'active' : '' }}">
                                    <a href="{{ route('template.view') }}">View Settings</a>
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
                    </ul>
                </div>
                <!-- sidebar menu end-->
            </div>
        </aside>
        <!--sidebar end-->
        @yield('content')
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
</body>

</html>