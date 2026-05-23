<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPS Cpanel Login</title>
    <link rel="icon" href="{{ asset('favicon.svg') . '?v=1.2' }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') . '?v=1.2' }}">
    @include('partials.gps-notifications-assets')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('auth-login-new') }}" />
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
            <p class="split-left__lead">Web based Device Configuration Tool.</p>

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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 6v6l4 2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                    <h3>Easy Configuration</h3>
                    <p>Quick and simple device setup</p>
                </div>

                <div class="feature-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 17v-6h13"/>
                        <path d="M13 7h8v10"/>
                        <circle cx="5" cy="17" r="2"/>
                    </svg>
                    <h3>View Device Parameters</h3>
                    <p>Monitor real-time device information</p>
                </div>

                <div class="feature-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16v16H4z"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                    <h3>Account Management</h3>
                    <p>Manage users and permissions easily</p>
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
