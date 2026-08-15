<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Teller') - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-only { display: block !important; }
            .print-container { padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col fixed inset-y-0 left-0 z-30 no-print">
        
        <!-- Brand Header -->
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-100">
            <div class="w-9 h-9 bg-blue-600 rounded-md flex items-center justify-center text-white flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-slate-900 text-base leading-tight block">Bank Mini</span>
                <span class="text-xs text-slate-500 block">Teller Panel</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            
            <a href="{{ route('teller.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('teller.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('teller.identification') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('teller.identification') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>Identifikasi Nasabah</span>
            </a>

            <a href="{{ route('teller.deposit') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('teller.deposit') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Setoran Tunai</span>
            </a>

            <a href="{{ route('teller.withdrawal') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('teller.withdrawal') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Penarikan Tunai</span>
            </a>

            <a href="{{ route('teller.transactions') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('teller.transactions*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Riwayat Transaksi</span>
            </a>

            <a href="{{ route('teller.daily-report') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('teller.daily-report*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Penutupan Kas</span>
            </a>

        </nav>

        <!-- Sidebar Footer: Profile & Logout -->
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            <div class="flex items-center justify-between">
                <div class="overflow-hidden">
                    <span class="block text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-blue-600 font-medium capitalize truncate">{{ auth()->user()->role }}</span>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" title="Keluar" class="p-2 text-slate-500 hover:text-red-600 hover:bg-white rounded-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 pl-64 flex flex-col min-h-screen">
        
        <!-- Top Banner / Header space -->
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-20 gap-4 no-print">
            <div class="flex items-center gap-4 flex-1 max-w-xl">
                <form action="{{ route('teller.identification') }}" method="GET" class="relative w-full flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS, Nama, Rekening, User... (Tekan Enter)"
                               class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        @if(request('search'))
                            <a href="{{ route('teller.identification') }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 font-bold text-xs" title="Reset pencarian">&times;</a>
                        @endif
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex-shrink-0">
                        Cari
                    </button>
                </form>
            </div>
            <div class="text-xs text-slate-500 flex items-center gap-2 flex-shrink-0">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                <span>Teller Mode</span>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-8 mt-6 w-full no-print">
            @if(session('success'))
                <div class="p-3.5 bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-md mb-4 flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3.5 bg-red-50 border border-red-200 text-red-800 text-sm rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 bg-red-50 border border-red-200 text-red-800 text-sm rounded-md mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Content Body -->
        <main class="px-8 py-4 flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-8 text-center text-xs text-slate-500 mt-auto no-print">
            &copy; {{ date('Y') }} Bank Mini Sekolah Teller Panel.
        </footer>

    </div>

</body>
</html>
