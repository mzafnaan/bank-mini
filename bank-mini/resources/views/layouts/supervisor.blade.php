<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Supervisor Dashboard') - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex">

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col fixed inset-y-0 left-0 z-30">
        
        <!-- Brand Header -->
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-100">
            <div class="w-9 h-9 bg-blue-600 rounded-md flex items-center justify-center text-white flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-slate-900 text-base leading-tight block">Bank Mini</span>
                <span class="text-xs text-slate-500 block">Supervisor Panel</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            
            <a href="{{ route('supervisor.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('supervisor.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('supervisor.reports') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('supervisor.reports*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Laporan Harian Teller</span>
            </a>

            <a href="{{ route('supervisor.journals') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('supervisor.journals*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Audit Jurnal</span>
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
        <header class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between sticky top-0 z-20 gap-4">
            <div class="flex items-center gap-4 flex-1 max-w-xl">
                <form action="{{ request()->url() }}" method="GET" class="relative w-full flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tanggal, nama teller, transaksi... (Tekan Enter)"
                               class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        @if(request('search'))
                            <a href="{{ request()->url() }}" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 font-bold text-xs" title="Reset pencarian">&times;</a>
                        @endif
                    </div>
                    <button type="submit" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex-shrink-0">
                        Cari
                    </button>
                </form>
            </div>
            <div class="text-xs text-slate-500 flex items-center gap-2 flex-shrink-0">
                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                <span>Supervisor Mode</span>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-8 mt-6 w-full">
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
        <footer class="bg-white border-t border-slate-200 py-4 px-8 text-center text-xs text-slate-500 mt-auto">
            &copy; {{ date('Y') }} Bank Mini Sekolah Supervisor Panel.
        </footer>

    </div>

</body>
</html>
