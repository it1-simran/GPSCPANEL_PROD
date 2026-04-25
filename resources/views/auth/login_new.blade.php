<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPS Cpanel Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: rgb(118, 207, 28);
            --primary-glow: rgba(118, 207, 28, 0.3);
            --bg: #ffffff;
            --card-bg: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(118, 207, 28, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(118, 207, 28, 0.05) 0%, transparent 40%);
            position: relative;
        }

        /* Subtle grid pattern */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.02) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(12px);
            animation: fadeIn 0.8s ease-out;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 1.25rem;
            text-decoration: none;
            color: var(--text-main);
            letter-spacing: -0.02em;
            transition: all 0.3s;
        }

        .brand:hover {
            opacity: 0.8;
        }

        .brand-dot {
            width: 10px;
            height: 10px;
            background: var(--primary);
            border-radius: 2px;
            box-shadow: 0 0 10px var(--primary-glow);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-top: 10px;
            margin-bottom: 4px;
            letter-spacing: -0.03em;
            background: linear-gradient(to bottom, #fff 40%, var(--text-muted));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(118, 207, 28, 0.2);
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.9375rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            color: white;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            font-size: 0.875rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: var(--bg);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 20px var(--primary-glow);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px var(--primary-glow);
            filter: brightness(1.1);
        }

        .back-to-home {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.3s;
        }

        .back-to-home:hover {
            color: var(--primary);
        }

        .invalid-feedback {
            color: #ff4d4d;
            font-size: 0.75rem;
            margin-top: 6px;
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-dot"></span>
                <span>GPS CPANEL</span>
            </a>
            <h1>LOGIN</h1>
            <p>Access your fleet dashboard</p>
        </div>

        @isset($url)
        <form method="POST" action='{{ url("login/$url") }}'>
        @else
        <form method="POST" action="{{ route('login') }}">
        @endisset
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input id="email" type="email" class="form-input" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
                @if ($errors->has('email'))
                <span class="invalid-feedback">
                    {{ $errors->first('email') }}
                </span>
                @endif
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" class="form-input" name="password" required placeholder="••••••••">
                @if ($errors->has('password'))
                <span class="invalid-feedback">
                    {{ $errors->first('password') }}
                </span>
                @endif
            </div>

            <div class="checkbox-group">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : 'checked' }}> Remember Me
                </label>
                <a href="/forgot-password" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login">
                Sign In
            </button>
        </form>

        <a href="{{ url('/') }}" class="back-to-home">← Back to welcome</a>
    </div>
</body>

</html>