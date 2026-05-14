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

    <style>
        :root {
            --primary: rgb(118, 207, 28);
            --bg: rgb(29, 40, 62);
            --white: #ffffff;
            --text-dim: rgba(255, 255, 255, 0.8);
            --glow: rgba(118, 207, 28, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg);
            color: var(--white);
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Premium Background Effect */
        body::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle at center, var(--glow) 0%, transparent 70%);
            top: -25%;
            left: -25%;
            z-index: -1;
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .full-height {
            height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .top-right {
            position: absolute;
            right: 40px;
            top: 40px;
            z-index: 10;
        }

        .links > a {
            color: var(--primary);
            padding: 12px 28px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.1rem;
            text-decoration: none;
            text-transform: uppercase;
            border: 1px solid var(--primary);
            border-radius: 50px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(118, 207, 28, 0.05);
            backdrop-filter: blur(10px);
        }

        .links > a:hover {
            background: var(--primary);
            color: var(--bg);
            border-color: var(--primary);
            box-shadow: 0 0 20px var(--glow);
            transform: translateY(-2px);
        }

        .content {
            text-align: center;
            animation: fadeInUp 1.2s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .title {
            font-size: clamp(3rem, 10vw, 6rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -2px;
            display: flex;
            justify-content: center;
            gap: 0.3em;
            flex-wrap: wrap;
        }

        .word {
            display: inline-block;
            background: linear-gradient(to bottom, #fff 40%, var(--text-dim));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            transform: translateY(-100px);
            animation: dropIn 1.2s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            filter: drop-shadow(0 0 20px var(--glow));
        }

        .word:nth-child(1) { animation-delay: 0.2s; }
        .word:nth-child(2) { animation-delay: 0.5s; }
        .word:nth-child(3) { animation-delay: 0.8s; }

        @keyframes dropIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .subtitle {
            color: var(--primary);
            font-size: 18px;
            font-weight: 400;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(118, 207, 28, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            animation: slideDown 0.8s ease-out forwards;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Floating Animation */
        .float-wrap {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 48px 24px 56px;
            animation: float 6s ease-in-out infinite;
        }

        /* Radar / sonar layer (behind pin + title) */
        .radar-stage {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: visible;
        }

        .radar-dots {
            position: absolute;
            inset: -8%;
            background-image: radial-gradient(circle, rgba(118, 207, 28, 0.22) 1px, transparent 1px);
            background-size: 18px 18px;
            opacity: 0.35;
            mask-image: radial-gradient(ellipse 75% 70% at 50% 22%, black 0%, transparent 72%);
            -webkit-mask-image: radial-gradient(ellipse 75% 70% at 50% 22%, black 0%, transparent 72%);
        }

        .radar-static-rings {
            position: absolute;
            left: 50%;
            top: 96px;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 280px;
        }

        .radar-static-rings span {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid rgba(118, 207, 28, 0.18);
            border-radius: 50%;
            box-shadow: 0 0 12px rgba(118, 207, 28, 0.06) inset;
        }

        .radar-static-rings span:nth-child(1) { width: 72px; height: 72px; }
        .radar-static-rings span:nth-child(2) { width: 120px; height: 120px; opacity: 0.85; }
        .radar-static-rings span:nth-child(3) { width: 180px; height: 180px; opacity: 0.65; }
        .radar-static-rings span:nth-child(4) { width: 248px; height: 248px; opacity: 0.45; }

        .radar-sweep {
            position: absolute;
            left: 50%;
            top: 96px;
            width: 260px;
            height: 260px;
            margin: -130px 0 0 -130px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg 285deg,
                rgba(118, 207, 28, 0.45) 300deg,
                rgba(118, 207, 28, 0.08) 330deg,
                transparent 360deg
            );
            -webkit-mask-image: radial-gradient(circle, transparent 38%, black 40%, black 92%, transparent 94%);
            mask-image: radial-gradient(circle, transparent 38%, black 40%, black 92%, transparent 94%);
            opacity: 0.55;
            animation: radarSweep 5s linear infinite;
        }

        @keyframes radarSweep {
            to { transform: rotate(360deg); }
        }

        .radar-ripples {
            position: absolute;
            left: 50%;
            top: 96px;
            transform: translate(-50%, -50%);
            width: 4px;
            height: 4px;
        }

        .radar-ripples span {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 48px;
            height: 48px;
            margin: -24px 0 0 -24px;
            border: 2px solid rgba(118, 207, 28, 0.55);
            border-radius: 50%;
            box-shadow: 0 0 16px rgba(118, 207, 28, 0.25);
            animation: sonarRipple 3.2s cubic-bezier(0.22, 1, 0.36, 1) infinite;
        }

        .radar-ripples span:nth-child(1) { animation-delay: 0s; }
        .radar-ripples span:nth-child(2) { animation-delay: 0.8s; }
        .radar-ripples span:nth-child(3) { animation-delay: 1.6s; }
        .radar-ripples span:nth-child(4) { animation-delay: 2.4s; }

        @keyframes sonarRipple {
            0% {
                transform: scale(0.35);
                opacity: 0.75;
            }
            100% {
                transform: scale(9);
                opacity: 0;
            }
        }

        .radar-perspective {
            position: absolute;
            left: 50%;
            bottom: -20px;
            width: 420px;
            height: 140px;
            transform: translateX(-50%) perspective(280px) rotateX(68deg);
            transform-origin: 50% 100%;
            opacity: 0.35;
        }

        .radar-perspective i {
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 1px;
            height: 200px;
            margin-left: -0.5px;
            transform-origin: 50% 100%;
            background: linear-gradient(to top, rgba(118, 207, 28, 0.5), transparent);
        }

        .radar-perspective i:nth-child(1) { transform: rotate(-32deg); }
        .radar-perspective i:nth-child(2) { transform: rotate(0deg); }
        .radar-perspective i:nth-child(3) { transform: rotate(32deg); }

        .hero-foreground {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Decorative Elements */
        .decoration {
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--primary);
            filter: blur(150px);
            border-radius: 50%;
            opacity: 0.1;
            z-index: -1;
        }

        .dec-1 { top: -100px; left: -100px; }
        .dec-2 { bottom: -100px; right: -100px; }

        /* Same 3D GPS pin as login (login_new.blade.php) */
        .pin-icon-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 6px;
        }

        .welcome-pin-outer {
            opacity: 0;
            transform: translateY(-32px);
            animation: welcomePinEntrance 0.95s cubic-bezier(0.2, 0.8, 0.2, 1) 0.05s forwards;
        }

        @keyframes welcomePinEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-pin {
            width: 72px;
            height: 96px;
            filter: drop-shadow(0 0 24px rgba(118, 207, 28, 0.65)) drop-shadow(0 16px 32px rgba(0, 0, 0, 0.45));
            animation: welcomePinFloat 2.8s ease-in-out infinite;
        }

        .welcome-pin svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        @keyframes welcomePinFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        @media (prefers-reduced-motion: reduce) {
            .radar-sweep,
            .radar-ripples span,
            .float-wrap,
            .welcome-pin {
                animation: none !important;
            }
            .welcome-pin-outer {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
            .radar-sweep {
                opacity: 0.2;
            }
            .radar-ripples span {
                opacity: 0 !important;
                transform: none !important;
            }
        }
    </style>
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
