<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - M2M Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 mx-auto mb-4">
                <span class="material-symbols-outlined text-4xl">forum</span>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">Sign in</h1>
            <p class="text-gray-500 mt-2">to continue to M2M SMS Portal</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <form method="POST" action="{{ url('/login/sms-portal') }}" class="p-8 space-y-6">
                @csrf

                <!-- Session Status -->
                @if (session('status'))
                    <div class="text-sm font-medium text-emerald-600 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="text-sm font-medium text-rose-600 bg-rose-50 p-3 rounded-xl border border-rose-100">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2 px-1">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Password</label>
                        <a href="{{ url('/forgot-password') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Forgot?</a>
                    </div>
                    <input type="password" name="password" required
                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-gray-700">
                </div>

                <div class="flex items-center px-1">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 border-2" checked>
                    <label for="remember_me" class="ml-2 text-sm text-gray-500">Stay signed in</label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
                        Sign in
                    </button>
                </div>
            </form>

            <div class="bg-gray-50 border-t border-gray-100 p-6 text-center">
                <p class="text-sm text-gray-500">
                    Authorized access only.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
