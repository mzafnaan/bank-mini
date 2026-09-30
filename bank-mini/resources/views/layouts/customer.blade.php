<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Nasabah') - Bank Mini Sekolah</title>
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

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebarBackdrop" onclick="toggleSidebar(false)" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden transition-opacity no-print"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="w-64 bg-white border-r border-slate-200 flex flex-col fixed inset-y-0 left-0 z-40 transition-transform duration-200 ease-in-out -translate-x-full md:translate-x-0 no-print">
        
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-600 rounded-md flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-slate-900 text-base leading-tight block">Bank Mini</span>
                    <span class="text-xs text-slate-500 block">Portal Nasabah</span>
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
            
            <a href="{{ route('customer.dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('customer.dashboard') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('customer.transactions') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('customer.transactions*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Riwayat Transaksi</span>
            </a>

            <a href="{{ route('customer.qr-code') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('customer.qr-code*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>QR Code Rekening</span>
            </a>

            <a href="{{ route('customer.security') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-md text-sm font-medium transition {{ request()->routeIs('customer.security*') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Keamanan Akun</span>
            </a>

        </nav>

        <!-- Sidebar Footer: Customer Profile & Logout -->
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            @php
                $currentAccount = auth()->guard('customer_web')->user();
                $customerProfile = $currentAccount?->customer;
                $bankAccount = $customerProfile?->bankAccount;
            @endphp
            <div class="flex items-center justify-between gap-2">
                <div class="overflow-hidden flex-1">
                    <span class="block text-sm font-semibold text-slate-900 truncate">
                        {{ $customerProfile?->name ?? $currentAccount?->username }}
                    </span>
                    <span class="block text-xs text-blue-600 font-medium truncate">
                        {{ $bankAccount?->account_number ?? 'Nasabah' }} {{ $customerProfile?->class ? '• ' . $customerProfile->class : '' }}
                    </span>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="flex-shrink-0">
                    @csrf
                    <button type="submit" title="Keluar dari akun" class="p-2 text-slate-500 hover:text-red-600 hover:bg-white rounded-md transition shadow-xs">
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
        <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20 gap-3 no-print">
            <div class="flex items-center gap-3">
                <!-- Mobile Burger Menu Button -->
                <button onclick="toggleSidebar(true)" type="button" class="md:hidden p-2 -ml-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-md transition focus:outline-none flex-shrink-0" aria-label="Buka Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-slate-800 hidden sm:inline">Selamat datang,</span>
                    <span class="text-sm font-bold text-slate-900 truncate max-w-[200px] sm:max-w-xs">{{ $customerProfile?->name ?? 'Nasabah' }}</span>
                </div>
            </div>

            <!-- Header Quick Badge -->
            <div class="flex items-center gap-2.5">
                @if($bankAccount)
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs">
                        <span class="text-slate-500 font-medium">Rekening:</span>
                        <span class="font-bold text-slate-900">{{ $bankAccount->account_number }}</span>
                        @if($bankAccount->status === 'active')
                            <span class="w-2 h-2 rounded-full bg-emerald-500" title="Rekening Aktif"></span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-rose-500" title="Rekening Nonaktif"></span>
                        @endif
                    </div>
                @endif
                <a href="{{ route('customer.qr-code') }}" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold text-xs rounded-md border border-blue-200 transition flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Kartu Digital</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 lg:px-8 mt-6 w-full no-print">
            @if(session('success'))
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-md mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-md mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-md mb-4">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($currentAccount?->first_login)
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 text-sm rounded-lg mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Ini adalah login pertama Anda. Demi keamanan akun, segera ganti password dan PIN default Anda.</span>
                    </div>
                    <a href="{{ route('customer.security') }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-md transition shadow-xs flex-shrink-0">
                        Ganti Password & PIN
                    </a>
                </div>
            @endif
        </div>

        <!-- Content Body -->
        <main class="px-4 sm:px-6 lg:px-8 py-4 flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 mt-auto no-print">
            &copy; {{ date('Y') }} Bank Mini Sekolah - Portal Layanan Mandiri Nasabah.
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
    @stack('scripts')
</body>
</html>
