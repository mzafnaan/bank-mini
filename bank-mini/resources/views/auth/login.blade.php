<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-width max-w-md bg-white rounded-lg border border-slate-200 shadow-sm p-8">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mx-auto mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" class="w-20 h-20 object-contain">
            </div>
            <h1 class="text-xl font-bold text-slate-900">Bank Mini Sekolah</h1>
            <p class="text-sm text-slate-500 mt-1">SMK Negeri 1 Bantul</p>
        </div>

        <!-- Session Flash Notifications -->
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-md">
                {{ session('success') }}
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-md">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="username" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-md text-sm text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                    placeholder="Masukkan username">
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium hover:underline">
                        Lupa Password?
                    </a>
                </div>
                <input type="password" id="password" name="password" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-md text-sm text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                    placeholder="Masukkan password">
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition duration-150">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Bank Mini Sekolah
        </div>
    </div>

</body>
</html>
