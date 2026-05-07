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
    <link href="{{ asset('assets/css/custom.css?nocache=' . time()) }}" rel="stylesheet" />

    <!-- ===== Loader: Hide INSTANTLY in head if NOT a reload (before any paint) ===== -->
    <script>
        try {
            var _entries = performance.getEntriesByType('navigation');
            var _type = _entries.length
                ? _entries[0].type
                : (performance.navigation && performance.navigation.type === 1 ? 'reload' : 'navigate');
            if (_type !== 'reload') {
                document.documentElement.classList.add('no-loader');
            }
        } catch(e) { /* fail-safe: show loader */ }
    </script>

    <!-- ===== Page Loader + Navbar Styles ===== -->
    <style>
        /* ================================================================
           NEW PREMIUM NAVBAR — FULL DARK UNIFIED HEADER
        ================================================================ */

        /* ---- Full dark header bar ---- */
        header.header {
            background: linear-gradient(90deg, #0f172a 0%, #1a2540 100%) !important;
            border-bottom: 1px solid rgba(118,207,28,0.15) !important;
            box-shadow: 0 4px 24px rgba(0,0,0,0.35) !important;
            height: 60px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 !important;
            z-index: 1050 !important;
        }

        /* ---- Brand / Logo Area ---- */
        header.header .brand {
            background: transparent !important;
            height: 60px !important;
            min-width: 240px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 18px !important;
            border-right: 1px solid rgba(255,255,255,0.06) !important;
            box-shadow: none !important;
            flex-shrink: 0;
        }
        header.header .brand .logo {
            color: #ffffff !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            letter-spacing: 2px !important;
            text-transform: uppercase !important;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        /* GPS dot pulse animation */
        header.header .brand .logo::before {
            content: '';
            display: inline-block;
            width: 9px;
            height: 9px;
            background: #76CF1C;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(118,207,28,0.7);
            animation: navPulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes navPulse {
            0%   { box-shadow: 0 0 0 0   rgba(118,207,28,0.7); }
            60%  { box-shadow: 0 0 0 7px rgba(118,207,28,0); }
            100% { box-shadow: 0 0 0 0   rgba(118,207,28,0); }
        }

        /* ---- Hamburger toggle ---- */
        header.header .brand .sidebar-toggle-box {
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: background 0.2s, border-color 0.2s;
            flex-shrink: 0;
        }
        header.header .brand .sidebar-toggle-box:hover {
            background: rgba(118,207,28,0.15);
            border-color: rgba(118,207,28,0.3);
        }
        /* Reset the fa-bars div — prevent white box from theme styles */
        header.header .brand .sidebar-toggle-box .fa-bars {
            display: block !important;
            background: transparent !important;
            background-color: transparent !important;
            color: rgba(255,255,255,0.75) !important;
            font-size: 15px !important;
            font-family: FontAwesome !important;
            width: auto !important;
            height: auto !important;
            min-width: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1 !important;
            border: none !important;
            box-shadow: none !important;
            transition: color 0.2s;
        }
        header.header .brand .sidebar-toggle-box:hover .fa-bars {
            color: #76CF1C !important;
        }

        /* ---- Right nav container ---- */
        header.header .top-nav {
            flex: 1 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-end !important;
            height: 60px !important;
            padding: 0 24px !important;
        }
        header.header .top-nav ul.nav {
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 60px !important;
            list-style: none !important;
        }
        header.header .top-nav ul.nav > li {
            display: flex !important;
            align-items: center !important;
            height: 60px !important;
        }

        /* ---- Version badge ---- */
        header.header .top-nav .nav-item .d-flex.bg-primary {
            background: rgba(118,207,28,0.12) !important;
            border: 1px solid rgba(118,207,28,0.5) !important;
            border-radius: 20px !important;
            padding: 5px 14px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: 0.8px !important;
            color: #76CF1C !important;
            box-shadow: 0 0 12px rgba(118,207,28,0.15) !important;
            gap: 5px !important;
        }
        header.header .top-nav .nav-item .d-flex.bg-primary span,
        header.header .top-nav .nav-item .d-flex.bg-primary i {
            color: #76CF1C !important;
        }

        /* ---- Bell / Notification icon ---- */
        header.header .top-nav .nav-link {
            color: rgba(255,255,255,0.55) !important;
            padding: 8px 10px !important;
            border-radius: 10px !important;
            transition: background 0.2s, color 0.2s !important;
            display: flex !important;
            align-items: center !important;
            position: relative;
        }
        header.header .top-nav .nav-link:hover {
            background: rgba(118,207,28,0.12) !important;
            color: #76CF1C !important;
        }
        header.header .top-nav .nav-link .fa-bell {
            font-size: 16px !important;
        }
        /* Notification badge */
        header.header .top-nav .badge-danger {
            background: #ef4444 !important;
            font-size: 9px !important;
            min-width: 16px;
            height: 16px;
            line-height: 16px;
            padding: 0 4px;
            border-radius: 8px;
            top: 4px !important;
            right: 4px !important;
        }

        /* ---- Search box ---- */
        header.header .top-nav li.search-box {
            display: flex !important;
            align-items: center !important;
        }
        header.header .top-nav li.search-box .search {
            background: rgba(255,255,255,0.07) !important;
            border: 1px solid rgba(255,255,255,0.12) !important;
            border-radius: 20px !important;
            height: 36px !important;
            width: 170px !important;
            padding: 0 16px !important;
            font-size: 12px !important;
            color: rgba(255,255,255,0.85) !important;
            transition: border-color 0.25s, width 0.3s, box-shadow 0.25s, background 0.25s !important;
            outline: none !important;
        }
        header.header .top-nav li.search-box .search:focus {
            border-color: rgba(118,207,28,0.6) !important;
            background: rgba(255,255,255,0.11) !important;
            box-shadow: 0 0 0 3px rgba(118,207,28,0.12) !important;
            width: 210px !important;
        }
        header.header .top-nav li.search-box .search::placeholder {
            color: rgba(255,255,255,0.3) !important;
        }

        /* ---- Separator before user ---- */
        header.header .top-nav ul.nav > li.dropdown:last-child {
            border-left: 1px solid rgba(255,255,255,0.08);
            margin-left: 8px;
            padding-left: 12px;
        }

        /* ---- User Avatar ---- */
        .nav-user-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #76CF1C, #4fa812);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Open Sans', sans-serif;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(118,207,28,0.35), 0 2px 10px rgba(118,207,28,0.3);
            transition: box-shadow 0.25s ease, transform 0.2s ease;
            text-transform: uppercase;
            margin-right: 8px;
        }
        .user-profile:hover .nav-user-avatar {
            box-shadow: 0 0 0 3px rgba(118,207,28,0.6), 0 4px 16px rgba(118,207,28,0.4);
            transform: scale(1.06);
        }

        /* ---- User profile trigger ---- */
        .user-profile {
            display: flex !important;
            align-items: center !important;
            gap: 0 !important;
            padding: 7px 12px !important;
            border-radius: 10px !important;
            transition: background 0.2s !important;
            cursor: pointer;
        }
        .user-profile:hover {
            background: rgba(255,255,255,0.07) !important;
        }
        .user-profile .nav-email-text {
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.85);
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .user-profile .fa-angle-down {
            margin-left: 6px;
            color: rgba(255,255,255,0.35);
            font-size: 11px;
            transition: transform 0.22s;
        }
        .user-profile[aria-expanded="true"] .fa-angle-down {
            transform: rotate(180deg);
            color: #76CF1C;
        }

        /* ---- User dropdown menu ---- */
        .dropdown-usermenu {
            border-radius: 12px !important;
            border: 1px solid rgba(0,0,0,0.08) !important;
            box-shadow: 0 12px 40px rgba(0,0,0,0.18) !important;
            min-width: 200px !important;
            overflow: hidden;
            margin-top: 6px !important;
            background: #ffffff !important;
        }
        .dropdown-usermenu li a {
            padding: 11px 18px !important;
            font-size: 13px !important;
            color: #374151 !important;
            display: flex !important;
            align-items: center !important;
            gap: 9px !important;
            transition: background 0.15s, color 0.15s !important;
            font-weight: 500 !important;
        }
        .dropdown-usermenu li a:hover {
            background: rgba(118,207,28,0.08) !important;
            color: #3a8c0a !important;
        }
        .dropdown-usermenu li a i {
            width: 16px;
            text-align: center;
            color: #9ca3af;
        }
        .dropdown-usermenu li a:hover i {
            color: #76CF1C !important;
        }

        /* ================================================================
           NOTIFICATION DROPDOWN STYLES
        ================================================================ */
        .notif-dropdown-menu {
            width: 360px !important;
            max-height: 480px !important;
            overflow: hidden !important;
            border-radius: 16px !important;
            border: 1px solid rgba(15,23,42,0.10) !important;
            box-shadow: 0 20px 56px rgba(0,0,0,0.20) !important;
            padding: 0 !important;
            margin-top: 10px !important;
            background: #ffffff !important;
        }
        /* Header */
        .notif-header {
            background: linear-gradient(135deg, #0f172a 0%, #1d283e 100%);
            padding: 15px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 16px 16px 0 0;
        }
        .notif-header h6 {
            color: #ffffff !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .notif-header h6::before {
            content: '';
            display: inline-block;
            width: 7px;
            height: 7px;
            background: #76CF1C;
            border-radius: 50%;
            box-shadow: 0 0 6px rgba(118,207,28,0.8);
            flex-shrink: 0;
        }
        .notif-mark-read {
            font-size: 11px !important;
            color: rgba(118,207,28,0.75) !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            transition: color 0.2s;
            white-space: nowrap;
        }
        .notif-mark-read:hover { color: #76CF1C !important; }

        /* Scrollable list */
        .notif-list {
            max-height: 340px;
            overflow-y: auto;
        }
        .notif-list::-webkit-scrollbar { width: 4px; }
        .notif-list::-webkit-scrollbar-track { background: #f9fafb; }
        .notif-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

        /* Each notification row */
        .notif-item {
            display: flex !important;
            align-items: flex-start !important;
            gap: 12px !important;
            padding: 13px 18px !important;
            border-bottom: 1px solid #f3f4f6 !important;
            text-decoration: none !important;
            transition: background 0.15s !important;
            position: relative;
            background: #fff;
        }
        .notif-item:last-child { border-bottom: none !important; }
        .notif-item:hover { background: #f9fafb !important; }
        .notif-item.unread { background: rgba(118,207,28,0.04) !important; }
        .notif-item.unread::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #76CF1C, #4fa812);
            border-radius: 0 2px 2px 0;
        }

        /* Icon chip */
        .notif-icon-chip {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
        }
        .notif-icon-chip.error   { background: rgba(239,68,68,0.10);  color: #ef4444; }
        .notif-icon-chip.update  { background: rgba(59,130,246,0.10);  color: #3b82f6; }
        .notif-icon-chip.default { background: rgba(107,114,128,0.10); color: #6b7280; }

        /* Text content */
        .notif-body { flex: 1; min-width: 0; }
        .notif-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .notif-desc {
            font-size: 11.5px;
            color: #6b7280;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.45;
        }
        .notif-time {
            font-size: 10.5px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        /* Empty state */
        .notif-empty {
            padding: 36px 20px;
            text-align: center;
        }
        .notif-empty i {
            font-size: 34px;
            color: #d1d5db;
            margin-bottom: 10px;
            display: block;
        }
        .notif-empty p {
            font-size: 13px;
            color: #9ca3af;
            margin: 0;
        }

        /* Footer */
        .notif-footer {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 12px 18px !important;
            border-top: 1px solid #f3f4f6 !important;
            font-size: 12.5px !important;
            font-weight: 600 !important;
            color: #76CF1C !important;
            text-decoration: none !important;
            transition: background 0.15s, color 0.15s !important;
            gap: 6px;
            background: #fff;
        }
        .notif-footer:hover {
            background: rgba(118,207,28,0.05) !important;
            color: #4fa812 !important;
        }

        /* ---- Page Loader Overlay ---- */
        #page-loader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1d283e 60%, #1a3520 100%);
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        #page-loader.loader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        /* Hide loader instantly via class set in <head> script (navigation, not reload) */
        html.no-loader #page-loader {
            display: none !important;
        }

        /* ---- GPS Pin SVG Spinner ---- */
        .loader-icon {
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 24px;
        }
        .loader-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #76CF1C;
            border-right-color: rgba(118,207,28,0.3);
            animation: loaderSpin 0.9s linear infinite;
        }
        .loader-ring-2 {
            position: absolute;
            inset: 10px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-bottom-color: #76CF1C;
            border-left-color: rgba(118,207,28,0.2);
            animation: loaderSpin 1.4s linear infinite reverse;
        }
        .loader-pin {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .loader-pin svg {
            width: 32px;
            height: 32px;
            fill: #76CF1C;
            animation: loaderPulse 1.5s ease-in-out infinite;
            filter: drop-shadow(0 0 8px rgba(118,207,28,0.7));
        }
        @keyframes loaderSpin {
            to { transform: rotate(360deg); }
        }
        @keyframes loaderPulse {
            0%, 100% { transform: scale(1);   opacity: 1; }
            50%       { transform: scale(1.15); opacity: 0.7; }
        }

        /* ---- Loader Text ---- */
        .loader-text {
            color: #ffffff;
            font-family: 'Open Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .loader-dots span {
            display: inline-block;
            width: 5px;
            height: 5px;
            margin: 0 3px;
            background: #76CF1C;
            border-radius: 50%;
            animation: loaderDot 1.2s ease-in-out infinite;
        }
        .loader-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loader-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes loaderDot {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40%            { transform: scale(1);   opacity: 1; }
        }

        /* ---- Brand Label ---- */
        .loader-brand {
            margin-top: 28px;
            color: rgba(255,255,255,0.3);
            font-family: 'Open Sans', sans-serif;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
    @stack('styles')
</head>

<body id="default-scheme">

    <!-- ===== Global Page Loader ===== -->
    <div id="page-loader" role="status" aria-label="Loading page">
        <div class="loader-icon">
            <div class="loader-ring"></div>
            <div class="loader-ring-2"></div>
            <div class="loader-pin">
                <!-- GPS Map Pin SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
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
                    <a href="/admin" class="logo">
                        Admin Area
                    </a>
                @elseif(Auth::user()->user_type == 'Reseller')
                    <a href="/reseller" class="logo">
                        Manufacturer Area
                    </a>
                @elseif(Auth::user()->user_type == 'Support')
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
                            <span
                                class="font-weight-bold padding-left-6 padding-right-6">v{{$latestVersion->version}}</span>
                        </div>
                    </li>

                    <!-- 🔔 Notification Dropdown -->
                    @if(Auth::user()->user_type == 'Admin')
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell fa-lg"></i>
                                @if($ticketCount > 0)
                                    <span class="badge badge-danger rounded-circle position-absolute" id="notificationCount"
                                        style="font-size: 0.7rem; top: 0px; right: 0px;">
                                        {{ $ticketCount }}
                                    </span>
                                @endif
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
                        <input type="text" class="form-control search" placeholder="Search">
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
                                class='sub-menu {{ request()->is('admin/imei-devices', 'admin/tracker*') ? 'active' : '' }}'>
                                <a href="#"
                                    class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/imei-devices', 'admin/tracker*') ? 'active' : '' }}">
                                    <span class='icon-sidebar pe-7s-map-marker fa-2x'></span><span>Live Tracking</span>
                                </a>
                                <ul class='sub'>
                                    <li class="{{ request()->is('admin/imei-devices') ? 'active' : '' }}">
                                        <a href="{{ url('admin/imei-devices') }}"
                                            class="{{ request()->is('admin/imei-devices') ? 'active' : '' }}">
                                            Manage Trackers
                                        </a>
                                    </li>
                                    <li class="{{ request()->is('admin/tracker*') ? 'active' : '' }}">
                                        <a href="{{ url('admin/tracker') }}"
                                            class="{{ request()->is('admin/tracker*') ? 'active' : '' }}">
                                            Live Track / Logs
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class='sub-menu {{ request()->is('admin/test-plans*', 'admin/test-validate*') ? 'active' : '' }}'>
                                <a href="#" class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/test-plans*', 'admin/test-validate*') ? 'active' : '' }}">
                                    <span class='icon-sidebar pe-7s-rocket fa-2x'></span><span>Test Automation</span>
                                </a>
                                <ul class='sub'>
                                    <li class="{{ request()->is('admin/test-plans*') ? 'active' : '' }}">
                                        <a href="{{ url('admin/test-plans') }}" class="{{ request()->is('admin/test-plans*') ? 'active' : '' }}">
                                            Manage Test Plans
                                        </a>
                                    </li>
                                    <li class="{{ request()->is('admin/test-validate*') ? 'active' : '' }}">
                                        <a href="{{ url('admin/test-validate') }}" class="{{ request()->is('admin/test-validate*') ? 'active' : '' }}">
                                            Test Validation Page
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
                                    <li class="{{ request()->is('admin/add-template') ? 'active' : '' }}">
                                        <a href="{{ url('admin/add-template') }}"
                                            class="{{ request()->is('admin/add-template') ? 'active' : '' }}">
                                            Add Settings
                                        </a>
                                    </li>
                                    <li class="{{ request()->is('admin/view-template') ? 'active' : '' }}">
                                        <a href="{{ url('admin/view-template') }}"
                                            class="{{ request()->is('admin/view-template') ? 'active' : '' }}">
                                            View Settings
                                        </a>
                                    </li>
                                    <li class="{{ request()->is('admin/assign-setting-bulk') ? 'active' : '' }}">
                                        <a href="<?php    echo url('admin/assign-setting-bulk'); ?>"
                                            class="{{ request()->is('admin/assign-setting-bulk') ? 'active' : '' }}">
                                            Assign Settings Bulk
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li
                                class='sub-menu {{ request()->is('admin/add-device-category', 'admin/View-device-category', 'admin/restore-device-category', 'admin/view-device-category-fields') ? 'active' : '' }}'>
                                <a href="#"
                                    class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/add-device-category', 'admin/View-device-category', 'admin/restore-device-category', 'admin/view-device-category-fields') ? 'active' : '' }}">
                                    <span class='icon-sidebar pe-7s-safe fa-2x'></span><span>Device Category</span>
                                </a>
                                <ul class='sub'>
                                    <li class="{{ request()->is('admin/add-device-category') ? 'active' : '' }}">
                                        <a href="{{ url('admin/add-device-category') }}"
                                            class="{{ request()->is('admin/add-device-category') ? 'active' : '' }}">
                                            Add Device Category
                                        </a>
                                    </li>
                                    <li class="{{ request()->is('admin/View-device-category') ? 'active' : '' }}">
                                        <a href="{{ url('admin/View-device-category') }}"
                                            class="{{ request()->is('admin/View-device-category') ? 'active' : '' }}">
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

                            <li class="{{ request()->is('admin/protocols*') ? 'active' : '' }}">
                                <a href="{{ route('protocols.index') }}"
                                    class="hvr-bounce-to-right-sidebar-parent {{ request()->is('admin/protocols*') ? 'active' : '' }}">
                                    <span class='icon-sidebar pe-7s-link fa-2x'></span><span>Protocol Management</span>
                                </a>
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

                        @elseif (Auth::user()->user_type == 'Reseller')

                            <li class="{{ request()->is('reseller') ? 'active' : '' }}">
                                <a href="{{ url('/reseller') }}"
                                    class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller') ? 'active' : '' }}">
                                    <span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span>
                                </a>
                            </li>

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
                                        <a href="<?php    echo url('reseller/assign-setting-bulk'); ?>"
                                            class="{{ request()->is('reseller/assign-setting-bulk') ? 'active' : '' }}">
                                            Assign Settings Bulk
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="{{ request()->is('reseller/View-device-category') ? 'active' : '' }}">
                                <a href="{{ url('reseller/View-device-category') }}"
                                    class="hvr-bounce-to-right-sidebar-parent {{ request()->is('reseller/View-device-category') ? 'active' : '' }}">
                                    <span class='icon-sidebar icon-home fa-2x'></span><span>View Device Category</span>
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

                                <!-- <li class='sub-menu {{ request()->is('user/*') ? 'active' : '' }}'>
                                            <a href="#" class="hvr-bounce-to-right-sidebar-parent">
                                                <span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span>
                                            </a>
                                            <ul class='sub'>
                                                <li class="{{ request()->is('user/view-device') ? 'active' : '' }}">
                                                    <a href="{{ url('user/view-device') }}">View Device</a>
                                                </li>
                                            </ul>
                                        </li> -->

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

                                <!-- <li class='sub-menu {{ request()->is('user/add-template*', 'user/view-template*') ? 'active' : '' }}'>
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
                                                <li class="{{ request()->is('user/assign-setting-bulk') ? 'active' : '' }}">
                                                    <a href="{{ url('user/assign-setting-bulk') }}">Assign Settings Bulk</a>
                                                </li>
                                            </ul>
                                        </li> -->
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
                                        <li class="{{ request()->is('support/add-template') ? 'active' : '' }}">
                                            <a href="{{ route('template.add') }}"
                                                class="{{ request()->is('support/add-template') ? 'active' : '' }}">
                                                Add Settings
                                            </a>
                                        </li>
                                        <li class="{{ request()->is('support/view-template') ? 'active' : '' }}">
                                            <a href="{{ route('template.view') }}"
                                                class="{{ request()->is('support/view-template') ? 'active' : '' }}">
                                                View Settings
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li
                                    class='sub-menu {{ request()->is('support/imei-devices*', 'support/tracker*') ? 'active' : '' }}'>
                                    <a href="#"
                                        class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/imei-devices*', 'support/tracker*') ? 'active' : '' }}">
                                        <span class='icon-sidebar pe-7s-map-marker fa-2x'></span><span>Live Tracking</span>
                                    </a>
                                    <ul class='sub'>
                                        <li class="{{ request()->is('support/imei-devices*') ? 'active' : '' }}">
                                            <a href="{{ url('support/imei-devices') }}"
                                                class="{{ request()->is('support/imei-devices*') ? 'active' : '' }}">
                                                Manage Trackers
                                            </a>
                                        </li>
                                        <li class="{{ request()->is('support/tracker*') ? 'active' : '' }}">
                                            <a href="{{ url('support/tracker') }}"
                                                class="{{ request()->is('support/tracker*') ? 'active' : '' }}">
                                                Live Track / Logs
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class='sub-menu {{ request()->is('support/test-plans*', 'support/test-validate*') ? 'active' : '' }}'>
                                    <a href="#" class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/test-plans*', 'support/test-validate*') ? 'active' : '' }}">
                                        <span class='icon-sidebar pe-7s-rocket fa-2x'></span><span>Test Automation</span>
                                    </a>
                                    <ul class='sub'>
                                        <li class="{{ request()->is('support/test-plans*') ? 'active' : '' }}">
                                            <a href="{{ url('support/test-plans') }}" class="{{ request()->is('support/test-plans*') ? 'active' : '' }}">
                                                Manage Test Plans
                                            </a>
                                        </li>
                                        <li class="{{ request()->is('support/test-validate*') ? 'active' : '' }}">
                                            <a href="{{ url('support/test-validate') }}" class="{{ request()->is('support/test-validate*') ? 'active' : '' }}">
                                                Test Validation Page
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="{{ request()->is('support/protocols*') ? 'active' : '' }}">
                                    <a href="{{ route('support.protocols.index') }}"
                                        class="hvr-bounce-to-right-sidebar-parent {{ request()->is('support/protocols*') ? 'active' : '' }}">
                                        <span class='icon-sidebar pe-7s-link fa-2x'></span><span>Protocol Management</span>
                                    </a>
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
    @stack('scripts')

    <!-- ===== Page Loader Script ===== -->
    <script>
        (function () {
            var loader = document.getElementById('page-loader');
            if (!loader) return;

            // If head script already marked as no-loader, nothing to do
            if (document.documentElement.classList.contains('no-loader')) return;

            // Fail-safe: default TRUE so loader shows if detection is uncertain
            var isReload = true;
            try {
                var _e = performance.getEntriesByType('navigation');
                if (_e && _e.length) {
                    isReload = (_e[0].type === 'reload');
                } else if (performance.navigation) {
                    isReload = (performance.navigation.type === 1);
                }
            } catch(e) { /* keep true */ }

            if (!isReload) {
                loader.style.display = 'none';
                return;
            }

            // ---- Reload confirmed: show for min 1.5s then fade out ----
            var MIN_SHOW_MS = 1500;
            var startTime = Date.now();

            function hideLoader() {
                var elapsed = Date.now() - startTime;
                var remaining = Math.max(0, MIN_SHOW_MS - elapsed);
                setTimeout(function () {
                    loader.classList.add('loader-hidden');
                    setTimeout(function () {
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
            setTimeout(function () {
                if (loader && loader.parentNode) {
                    loader.classList.add('loader-hidden');
                    setTimeout(function () {
                        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
                    }, 600);
                }
            }, 8000);
        })();
    </script>
    <!-- ===== End Page Loader Script ===== -->
</body>

</html>