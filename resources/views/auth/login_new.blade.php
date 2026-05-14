<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPS Cpanel Login</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    @include('partials.gps-notifications-assets')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --lime: #76cf1c;
            --lime-bright: #9fe871;
            --lime-dim: #5dab13;
            --lime-glow: rgba(118, 207, 28, 0.55);
            --ink-dark: #030712;
            --ink-panel: #0c1222;
            --form-text: #111827;
            --form-muted: #6b7280;
            --form-border: #e5e7eb;
            --font: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { min-height: 100%; }

        body {
            font-family: var(--font);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #fff;
        }

        @media (min-width: 960px) {
            body { flex-direction: row; }
        }

        /* ========== LEFT: brand + map + GPS animation ========== */
        .split-left {
            position: relative;
            flex: 1.05;
            min-height: 42vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: linear-gradient(165deg, #050a14 0%, #0c1526 40%, #0a1628 100%);
        }

        @media (min-width: 960px) {
            .split-left { min-height: 100vh; }
        }

        .split-left__bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        /* Stylized “world” grid + meridians */
        .map-grid {
            position: absolute;
            inset: -10%;
            opacity: 0.35;
            background-image:
                linear-gradient(90deg, rgba(118, 207, 28, 0.12) 1px, transparent 1px),
                linear-gradient(rgba(118, 207, 28, 0.08) 1px, transparent 1px);
            background-size: 8% 100%, 100% 10%;
            transform: perspective(400px) rotateX(12deg) scale(1.05);
            transform-origin: center 70%;
        }

        .map-curve {
            position: absolute;
            left: 50%;
            top: 42%;
            width: 140%;
            height: 85%;
            transform: translate(-50%, -50%);
            border: 1px solid rgba(118, 207, 28, 0.1);
            border-radius: 50%;
            opacity: 0.5;
        }

        .map-curve:nth-child(2) {
            width: 115%;
            height: 70%;
            opacity: 0.35;
        }

        .map-curve:nth-child(3) {
            width: 92%;
            height: 56%;
            opacity: 0.25;
        }

        /* Twinkling data points */
        .map-dots {
            position: absolute;
            inset: 0;
        }

        .map-dots i {
            position: absolute;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--lime);
            box-shadow: 0 0 12px var(--lime-glow);
            animation: dotPulse 2.4s ease-in-out infinite;
        }

        .map-dots i:nth-child(1) { left: 12%; top: 28%; animation-delay: 0s; }
        .map-dots i:nth-child(2) { left: 22%; top: 52%; animation-delay: 0.4s; }
        .map-dots i:nth-child(3) { left: 35%; top: 35%; animation-delay: 0.8s; }
        .map-dots i:nth-child(4) { left: 48%; top: 58%; animation-delay: 0.2s; }
        .map-dots i:nth-child(5) { left: 58%; top: 32%; animation-delay: 1.1s; }
        .map-dots i:nth-child(6) { left: 72%; top: 48%; animation-delay: 0.6s; }
        .map-dots i:nth-child(7) { left: 82%; top: 38%; animation-delay: 1.4s; }
        .map-dots i:nth-child(8) { left: 18%; top: 68%; animation-delay: 0.9s; }
        .map-dots i:nth-child(9) { left: 65%; top: 22%; animation-delay: 0.3s; }
        .map-dots i:nth-child(10) { left: 40%; top: 72%; animation-delay: 1.2s; }

        @keyframes dotPulse {
            0%, 100% { opacity: 0.35; transform: scale(0.85); }
            50% { opacity: 1; transform: scale(1.15); }
        }

        .split-left__inner {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: clamp(28px, 5vw, 48px) clamp(24px, 4vw, 56px);
            min-height: 0;
        }

        .split-left__title {
            font-size: clamp(1.75rem, 4vw, 2.75rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            max-width: 14ch;
        }

        .split-left__title .accent {
            color: var(--lime-bright);
            text-shadow: 0 0 40px rgba(118, 207, 28, 0.45);
        }

        .split-left__lead {
            margin-top: 14px;
            font-size: clamp(0.9rem, 1.8vw, 1.05rem);
            line-height: 1.55;
            color: rgba(226, 232, 240, 0.88);
            max-width: 36ch;
            font-weight: 500;
        }

        /* Data beams */
        .data-beams {
            position: absolute;
            left: 50%;
            top: 38%;
            width: 4px;
            height: 28%;
            margin-left: -2px;
            background: linear-gradient(to top, rgba(118, 207, 28, 0.5), transparent);
            opacity: 0.35;
            animation: beamFlicker 3s ease-in-out infinite;
            pointer-events: none;
        }

        .data-beams::before,
        .data-beams::after {
            content: '';
            position: absolute;
            bottom: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(to top, rgba(118, 207, 28, 0.35), transparent);
        }

        .data-beams::before { left: -36px; transform: rotate(-8deg); animation-delay: 0.5s; }
        .data-beams::after { left: 36px; transform: rotate(8deg); animation-delay: 1s; }

        @keyframes beamFlicker {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.45; }
        }

        /* GPS stage */
        .gps-stage {
            position: relative;
            flex: 1;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 16px 0;
        }

        .gps-orbit {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 88px;
            height: 88px;
            margin: -44px 0 0 -44px;
        }

        .gps-orbit .ring {
            position: absolute;
            inset: 0;
            border: 2px solid rgba(118, 207, 28, 0.55);
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(118, 207, 28, 0.15);
            animation: ringOut 2.6s cubic-bezier(0.22, 0.61, 0.36, 1) infinite;
        }

        .gps-orbit .ring:nth-child(2) { animation-delay: 0.85s; }
        .gps-orbit .ring:nth-child(3) { animation-delay: 1.7s; }

        @keyframes ringOut {
            0% { transform: scale(0.55); opacity: 0.7; }
            100% { transform: scale(2.9); opacity: 0; }
        }

        .gps-sweep {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 55%;
            max-width: 280px;
            aspect-ratio: 1;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0deg 280deg, rgba(118, 207, 28, 0.15) 300deg, rgba(118, 207, 28, 0.45) 360deg);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 12px), #000 calc(100% - 8px), transparent 100%);
            mask: radial-gradient(farthest-side, transparent calc(100% - 12px), #000 calc(100% - 8px), transparent 100%);
            animation: sweep 4s linear infinite;
        }

        @keyframes sweep {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .gps-core {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 12px;
            height: 12px;
            margin: -6px 0 0 -6px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 25%, #ecfccb, var(--lime) 50%, #3f6212);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.95), 0 0 28px var(--lime-glow);
            animation: corePulse 1.6s ease-in-out infinite;
            z-index: 3;
        }

        @keyframes corePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .gps-pin {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 72px;
            height: 96px;
            transform: translate(-50%, -100%);
            filter: drop-shadow(0 0 24px rgba(118, 207, 28, 0.65)) drop-shadow(0 16px 32px rgba(0, 0, 0, 0.45));
            animation: pinFloat 2.8s ease-in-out infinite;
            z-index: 4;
        }

        .gps-pin svg { width: 100%; height: 100%; display: block; }

        @keyframes pinFloat {
            0%, 100% { transform: translate(-50%, -100%) translateY(0); }
            50% { transform: translate(-50%, -100%) translateY(-12px); }
        }

        .feature-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: auto;
            padding-top: 8px;
        }

        @media (max-width: 520px) {
            .feature-row { grid-template-columns: 1fr; }
        }

        .feature-card {
            padding: 14px 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(118, 207, 28, 0.18);
            backdrop-filter: blur(8px);
            transition: transform 0.25s, border-color 0.25s;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            border-color: rgba(118, 207, 28, 0.4);
        }

        .feature-card svg {
            width: 26px;
            height: 26px;
            color: var(--lime-bright);
            margin-bottom: 8px;
            filter: drop-shadow(0 0 8px rgba(118, 207, 28, 0.4));
        }

        .feature-card h3 {
            font-size: 0.8125rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: #fff;
        }

        .feature-card p {
            font-size: 0.7rem;
            color: rgba(203, 213, 225, 0.85);
            line-height: 1.35;
        }

        /* ========== RIGHT: white form panel ========== */
        .split-right {
            flex: 1;
            min-height: 58vh;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(28px, 5vw, 56px) clamp(22px, 4vw, 48px);
        }

        @media (min-width: 960px) {
            .split-right { min-height: 100vh; }
        }

        .form-panel {
            width: 100%;
            max-width: 420px;
            animation: panelIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes panelIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--form-text);
            margin-bottom: 28px;
        }

        .form-brand__mark {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--lime-bright), var(--lime) 55%, var(--lime-dim));
            display: grid;
            place-items: center;
            box-shadow: 0 4px 16px rgba(118, 207, 28, 0.35);
        }

        .form-brand__mark svg {
            width: 22px;
            height: 22px;
            color: #fff;
        }

        .form-brand span {
            font-weight: 800;
            font-size: 1.125rem;
            letter-spacing: -0.02em;
        }

        .form-panel h1 {
            font-size: clamp(1.75rem, 4vw, 2.125rem);
            font-weight: 800;
            color: var(--form-text);
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }

        .form-panel .sub {
            font-size: 0.95rem;
            color: var(--form-muted);
            margin-bottom: 28px;
            font-weight: 500;
        }

        .field { margin-bottom: 20px; }

        .field label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--form-text);
            margin-bottom: 8px;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            border: 1px solid var(--form-border);
            border-radius: 12px;
            background: #fafafa;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-wrap:focus-within {
            border-color: rgba(118, 207, 28, 0.55);
            box-shadow: 0 0 0 3px rgba(118, 207, 28, 0.12);
            background: #fff;
        }

        .input-wrap .ico {
            flex-shrink: 0;
            width: 48px;
            display: grid;
            place-items: center;
            color: #9ca3af;
        }

        .input-wrap .ico svg { width: 20px; height: 20px; }

        .input-wrap input {
            flex: 1;
            min-width: 0;
            border: none;
            background: transparent;
            padding: 14px 14px 14px 0;
            font-family: inherit;
            font-size: 1rem;
            color: var(--form-text);
        }

        .input-wrap input:focus { outline: none; }

        .input-wrap input::placeholder { color: #9ca3af; }

        .toggle-pw {
            flex-shrink: 0;
            width: 38px;
            min-width: 38px;
            align-self: stretch;
            display: grid;
            place-items: center;
            padding: 0 10px 0 2px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #9ca3af;
            border-radius: 0 12px 12px 0;
            transition: color 0.2s, background 0.2s;
        }

        .toggle-pw svg {
            width: 16px;
            height: 16px;
            display: block;
        }

        .toggle-pw:hover {
            color: var(--lime-dim);
            background: rgba(118, 207, 28, 0.06);
        }

        .row-opt {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
            font-size: 0.875rem;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--form-muted);
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 17px;
            height: 17px;
            accent-color: var(--lime);
            cursor: pointer;
        }

        .forgot {
            color: var(--lime-dim);
            font-weight: 600;
            text-decoration: none;
        }

        .forgot:hover { text-decoration: underline; }

        .btn-signin {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 16px 15px 24px;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--lime-bright) 0%, var(--lime) 45%, var(--lime-dim) 100%);
            box-shadow: 0 8px 28px rgba(118, 207, 28, 0.38);
            transition: transform 0.2s, box-shadow 0.2s, filter 0.2s;
        }

        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(118, 207, 28, 0.45);
            filter: brightness(1.03);
        }

        .btn-signin__text {
            flex: 1;
            text-align: center;
        }

        .btn-signin__arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .btn-signin__arrow svg {
            width: 18px;
            height: 18px;
            color: var(--lime-dim);
        }

        .form-footer {
            margin-top: 28px;
            text-align: center;
        }

        .form-footer__trust {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            margin-bottom: 10px;
            color: var(--form-muted);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .form-footer__line {
            flex: 1;
            height: 1px;
            max-width: 100px;
            background: linear-gradient(90deg, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
        }

        .form-footer__mid {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .form-footer__mid svg {
            width: 18px;
            height: 18px;
            color: var(--lime);
            flex-shrink: 0;
        }

        .copyright {
            font-size: 0.6875rem;
            color: #9ca3af;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 18px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--form-muted);
            text-decoration: none;
        }

        .back-link:hover { color: var(--lime-dim); }

        .invalid {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 6px;
            display: block;
        }

        @media (prefers-reduced-motion: reduce) {
            .map-dots i,
            .gps-orbit .ring,
            .gps-sweep,
            .gps-pin,
            .gps-core,
            .data-beams,
            .form-panel { animation: none !important; }
        }
    </style>
</head>

<body>
    @include('partials.gps-flash-pull')
    <section class="split-left" aria-labelledby="brand-heading">
        <div class="split-left__bg" aria-hidden="true">
            <div class="map-grid"></div>
            <div class="map-curve"></div>
            <div class="map-curve"></div>
            <div class="map-curve"></div>
            <div class="map-dots">
                @for ($i = 1; $i <= 10; $i++)
                <i></i>
                @endfor
            </div>
            <div class="data-beams"></div>
        </div>

        <div class="split-left__inner">
            <h1 id="brand-heading" class="split-left__title">GPS <span class="accent">Control</span> Panel</h1>
            <p class="split-left__lead">Real-time tracking. Smart analytics. Complete control over your fleet.</p>

            <div class="gps-stage" aria-hidden="true">
                <div class="gps-sweep"></div>
                <div class="gps-orbit">
                    <span class="ring"></span>
                    <span class="ring"></span>
                    <span class="ring"></span>
                </div>
                <div class="gps-core"></div>
                <div class="gps-pin">
                    <svg viewBox="0 0 48 64" xmlns="http://www.w3.org/2000/svg" fill="none">
                        <defs>
                            <linearGradient id="gPin" x1="24" y1="4" x2="24" y2="56" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#d9f99d"/>
                                <stop offset="0.4" stop-color="#76cf1c"/>
                                <stop offset="1" stop-color="#3f6212"/>
                            </linearGradient>
                        </defs>
                        <path fill="url(#gPin)" stroke="#14532d" stroke-width="1.2"
                            d="M24 4C16.82 4 11 9.82 11 17c0 11.2 13 25.5 13 25.5S37 28.2 37 17C37 9.82 31.18 4 24 4z"/>
                        <ellipse cx="24" cy="54" rx="9" ry="4" fill="rgba(0,0,0,0.25)"/>
                        <circle cx="24" cy="17" r="5.5" fill="rgba(255,255,255,0.95)"/>
                        <circle cx="24" cy="17" r="2.8" fill="#166534"/>
                    </svg>
                </div>
            </div>

            <div class="feature-row">
                <div class="feature-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    <h3>Live Tracking</h3>
                    <p>Real-time updates</p>
                </div>
                <div class="feature-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <h3>Secure Data</h3>
                    <p>Enterprise grade</p>
                </div>
                <div class="feature-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 5-7"/></svg>
                    <h3>Smart Insights</h3>
                    <p>Actionable reports</p>
                </div>
            </div>
        </div>
    </section>

    <section class="split-right">
        <div class="form-panel">
            <a href="{{ url('/') }}" class="form-brand">
                <span class="form-brand__mark" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg>
                </span>
                <span>GPS CPANEL</span>
            </a>

            <h1>Welcome Back</h1>
            <p class="sub">Sign in to access your dashboard</p>

            @isset($url)
            <form method="POST" action='{{ url("login/$url") }}' data-login-form="1" onsubmit="var b=this.querySelector('button[type=submit]'); if(b){ b.disabled=true; b.setAttribute('aria-busy','true'); } return true;">
            @else
            <form method="POST" action="{{ route('login') }}" data-login-form="1" onsubmit="var b=this.querySelector('button[type=submit]'); if(b){ b.disabled=true; b.setAttribute('aria-busy','true'); } return true;">
            @endisset
                @csrf

                <div class="field">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="ico" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16v12H4z"/><path stroke-linecap="round" d="M4 7l8 6 8-6"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email" autocomplete="username">
                    </div>
                    @if ($errors->has('email'))
                    <span class="invalid">{{ $errors->first('email') }}</span>
                    @endif
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="ico" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M7 11V8a5 5 0 0110 0v3"/><rect x="5" y="11" width="14" height="10" rx="2"/></svg>
                        </span>
                        <input id="password" type="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="toggle-pw" id="togglePw" aria-label="Show or hide password" title="Show password">
                            <svg id="eyeOpen" width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeShut" width="16" height="16" style="display:none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 3l18 18M10.6 10.6a3 3 0 004.8 4.8M9.9 5.1A10.3 10.3 0 0112 5c7 0 11 7 11 7a21 21 0 01-3.5 5M6.4 6.4C4 8.6 2 12 2 12s4 7 11 7a10.5 10.5 0 005-1.4"/></svg>
                        </button>
                    </div>
                    @if ($errors->has('password'))
                    <span class="invalid">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="row-opt">
                    <label class="remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : 'checked' }}>
                        Remember Me
                    </label>
                    <a href="/forgot-password" class="forgot">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-signin">
                    <span class="btn-signin__text">Sign In</span>
                    <span class="btn-signin__arrow" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </button>
            </form>

            <div class="form-footer">
                <div class="form-footer__trust">
                    <span class="form-footer__line" aria-hidden="true"></span>
                    <span class="form-footer__mid">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
                        Secure access to your fleet data
                    </span>
                    <span class="form-footer__line" aria-hidden="true"></span>
                </div>
                <p class="copyright">© {{ date('Y') }} GPS CPanel. All rights reserved.</p>
                <a href="{{ url('/') }}" class="back-link">← Back to welcome</a>
            </div>
        </div>
    </section>

    <script>
        (function () {
            var btn = document.getElementById('togglePw');
            var input = document.getElementById('password');
            var open = document.getElementById('eyeOpen');
            var shut = document.getElementById('eyeShut');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                open.style.display = show ? 'none' : 'block';
                shut.style.display = show ? 'block' : 'none';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                btn.setAttribute('title', show ? 'Hide password' : 'Show password');
            });
        })();
    </script>
    @include('partials.gps-flash-scripts')
</body>

</html>
