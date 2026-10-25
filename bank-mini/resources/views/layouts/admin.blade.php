<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop" onclick="toggleSidebar(false)" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden transition-opacity"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="w-64 bg-white border-r border-slate-200 flex flex-col fixed inset-y-0 left-0 z-40 transition-transform duration-200 ease-in-out -translate-x-full md:translate-x-0">
        
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" class="w-10 h-10 object-contain flex-shrink-0">
                <div>
                    <span class="font-bold text-slate-900 text-base leading-tight block">Bank Mini</span>
                    <span class="text-xs text-slate-500 block">Admin Panel</span>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar(false)" type="button" class="md:hidden p-1.5 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100 transition" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.users') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.users*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Pengguna Internal</span>
            </a>

            <a href="{{ route('admin.customers') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.customers*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Data Nasabah</span>
            </a>

            <a href="{{ route('admin.accounts') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.accounts*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span>Rekening Nasabah</span>
            </a>

            <a href="{{ route('admin.journals') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.journals*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
    <div class="flex-1 md:pl-64 flex flex-col min-h-screen w-full min-w-0">
        
        <!-- Top Banner / Header space -->
        <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20 gap-3">
            <div class="flex items-center gap-2 sm:gap-4 flex-1 max-w-xl">
                <!-- Mobile Burger Menu Button -->
                <button onclick="toggleSidebar(true)" type="button" class="md:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-md transition focus:outline-none flex-shrink-0" aria-label="Buka Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <form action="{{ request()->url() }}" method="GET" class="relative w-full flex items-center gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS, Nama, Rekening, User... (Tekan Enter)"
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
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 lg:px-8 mt-6 w-full">
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
        <main class="px-4 sm:px-6 lg:px-8 py-4 flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 mt-auto">
            &copy; {{ date('Y') }} Bank Mini Sekolah Administrator Panel.
        </footer>

    </div>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        function toggleSidebar(open) {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'md:overflow-auto');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'md:overflow-auto');
            }
        }
    </script>
</body>
</html>
