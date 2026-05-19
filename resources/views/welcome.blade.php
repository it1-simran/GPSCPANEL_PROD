<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Advanced GPS Control Panel for real-time tracking and fleet management.">
    <title>GPS Control Panel | Precision Tracking</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">

    
    <link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('welcome') }}" />
</head>
<body>
    <div class="decoration dec-1"></div>
    <div class="decoration dec-2"></div>

    <div class="full-height">
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">Dashboard</a>
            @else
                <a href="{{ url('login') }}">Access Panel</a>
            @endauth
        </div>

        <div class="content">
            <div class="float-wrap">
                <!-- <span class="badge">Next-Gen Fleet Management</span> -->
                <div class="radar-stage" aria-hidden="true">
                    <div class="radar-dots"></div>
                    <div class="radar-perspective">
                        <i></i><i></i><i></i>
                    </div>
                    <div class="radar-static-rings">
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <div class="radar-sweep"></div>
                    <div class="radar-ripples">
                        <span></span><span></span><span></span><span></span>
                    </div>
                </div>
                <div class="hero-foreground">
                <div class="pin-icon-wrap" aria-hidden="true">
                    <div class="welcome-pin-outer">
                        <div class="welcome-pin">
                            <svg viewBox="0 0 48 64" xmlns="http://www.w3.org/2000/svg" fill="none">
                                <defs>
                                    <linearGradient id="welcomeGPin" x1="24" y1="4" x2="24" y2="56" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#d9f99d"/>
                                        <stop offset="0.4" stop-color="#76cf1c"/>
                                        <stop offset="1" stop-color="#3f6212"/>
                                    </linearGradient>
                                </defs>
                                <path fill="url(#welcomeGPin)" stroke="#14532d" stroke-width="1.2"
                                    d="M24 4C16.82 4 11 9.82 11 17c0 11.2 13 25.5 13 25.5S37 28.2 37 17C37 9.82 31.18 4 24 4z"/>
                                <ellipse cx="24" cy="54" rx="9" ry="4" fill="rgba(0,0,0,0.25)"/>
                                <circle cx="24" cy="17" r="5.5" fill="rgba(255,255,255,0.95)"/>
                                <circle cx="24" cy="17" r="2.8" fill="#166534"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <h1 class="title">
                    <span class="word">GPS</span>
                    <span class="word">Control</span>
                    <span class="word">Panel</span>
                </h1>
                </div>
                <!-- <p class="subtitle">
                    Precision. Reliability. Control.
                </p> -->
            </div>
        </div>
    </div>
</body>
</html>
