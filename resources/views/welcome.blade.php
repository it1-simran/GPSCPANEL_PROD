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
            animation: float 6s ease-in-out infinite;
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

        /* Car Animation Styles */
        .car-container {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 0;
            pointer-events: none;
            z-index: 20;
            transform: translateY(-50%);
        }

        .car {
            position: absolute;
            width: 48px;
            height: 48px;
            fill: var(--primary);
            filter: drop-shadow(0 0 15px var(--primary));
            opacity: 0;
            animation: zigzagRun 6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes zigzagRun {
            0% { 
                left: -10%; 
                top: 0; 
                transform: rotate(10deg) scale(0.8); 
                opacity: 0; 
            }
            10% { opacity: 1; }
            25% { 
                left: 15%; 
                top: -80px; 
                transform: rotate(-20deg) scale(1); 
            }
            50% { 
                left: 30%; 
                top: 80px; 
                transform: rotate(20deg) scale(1.1); 
            }
            75% { 
                left: 45%; 
                top: -80px; 
                transform: rotate(-20deg) scale(1); 
            }
            100% { 
                left: 50%; 
                top: -110px; 
                transform: translateX(-50%) rotate(0deg) scale(1.1); 
                opacity: 1; 
            }
        }
    </style>
</head>
<body>
    <div class="decoration dec-1"></div>
    <div class="decoration dec-2"></div>

    <div class="car-container">
        <svg class="car" viewBox="0 0 24 24">
            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
        </svg>
    </div>

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
                <h1 class="title">
                    <span class="word">GPS</span>
                    <span class="word">Control</span>
                    <span class="word">Panel</span>
                </h1>
                <!-- <p class="subtitle">
                    Precision. Reliability. Control.
                </p> -->
            </div>
        </div>
    </div>
</body>
</html>