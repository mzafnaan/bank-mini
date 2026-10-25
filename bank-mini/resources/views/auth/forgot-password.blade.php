<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Bank Mini Sekolah</title>
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

    <div class="w-full max-w-md bg-white rounded-lg border border-slate-200 shadow-sm p-8">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="flex items-center justify-center mx-auto mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" class="w-16 h-16 object-contain">
            </div>
            <h1 class="text-xl font-bold text-slate-900">Atur Ulang Password</h1>
            <p class="text-sm text-slate-500 mt-1">Masukkan alamat email terdaftar Anda untuk menerima tautan reset password.</p>
        </div>

        <!-- Session Status -->
        @if(session('status'))
            <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Email Terdaftar</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-md text-sm text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                    placeholder="nama@domain.com">
                <p class="text-xs text-slate-400 mt-1.5">Berlaku untuk akun staf internal (admin/teller/supervisor) dan nasabah siswa.</p>
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition duration-150">
                Kirim Tautan Reset Password
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium hover:underline inline-flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Halaman Login
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Bank Mini Sekolah
        </div>
    </div>

</body>
</html>
