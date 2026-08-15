@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Administrator</h1>
            <p class="text-sm text-slate-500">Ringkasan statistik operasional Bank Mini Sekolah.</p>
        </div>
    </div>

    @if(!empty($search))
        <!-- Search Notice Banner -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2 text-sm text-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Menampilkan hasil pencarian untuk kata kunci: <strong class="font-semibold text-blue-950">"{{ $search }}"</strong></span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 bg-white border border-blue-300 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-md transition shadow-sm">
                Reset Pencarian
            </a>
        </div>

        @if($searchResults)
            <!-- Search Results Categories Grid / Sections -->
            
            <!-- 1. Nasabah Results -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Data Nasabah</span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full font-bold">{{ $searchResults['customers']->count() }}</span>
                    </h2>
                    @if($searchResults['customers']->count() > 0)
                        <a href="{{ route('admin.customers', ['search' => $search]) }}" class="text-xs font-semibold text-blue-600 hover:underline">Kelola Nasabah &rarr;</a>
                    @endif
                </div>
                
                @if($searchResults['customers']->isEmpty())
                    <div class="bg-white p-4 rounded-lg border border-slate-200 text-xs text-slate-400 italic">Tidak ditemukan data nasabah yang cocok.</div>
                @else
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">NIS</th>
                                    <th class="px-6 py-3">Nama Nasabah</th>
                                    <th class="px-6 py-3">Kelas</th>
                                    <th class="px-6 py-3">No. Rekening</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($searchResults['customers'] as $c)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-3 font-mono font-semibold text-slate-700">{{ $c->nis }}</td>
                                        <td class="px-6 py-3 font-semibold text-slate-900">{{ $c->name }}</td>
                                        <td class="px-6 py-3 text-slate-600">{{ $c->class }}</td>
                                        <td class="px-6 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-mono font-bold rounded bg-blue-50 text-blue-700">
                                                {{ $c->bankAccount?->account_number ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- 2. Internal Users Results -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Pengguna Internal</span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full font-bold">{{ $searchResults['users']->count() }}</span>
                    </h2>
                    @if($searchResults['users']->count() > 0)
                        <a href="{{ route('admin.users', ['search' => $search]) }}" class="text-xs font-semibold text-blue-600 hover:underline">Kelola Pengguna &rarr;</a>
                    @endif
                </div>

                @if($searchResults['users']->isEmpty())
                    <div class="bg-white p-4 rounded-lg border border-slate-200 text-xs text-slate-400 italic">Tidak ditemukan pengguna internal yang cocok.</div>
                @else
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Nama</th>
                                    <th class="px-6 py-3">Username</th>
                                    <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($searchResults['users'] as $u)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-3 font-semibold text-slate-900">{{ $u->name }}</td>
                                        <td class="px-6 py-3 font-mono text-slate-700">{{ $u->username }}</td>
                                        <td class="px-6 py-3 capitalize"><span class="px-2 py-0.5 text-xs font-medium rounded bg-slate-100 text-slate-700">{{ $u->role }}</span></td>
                                        <td class="px-6 py-3 capitalize"><span class="px-2 py-0.5 text-xs font-medium rounded {{ $u->status === 'active' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">{{ $u->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- 3. Audit Journals Results -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Jurnal Transaksi</span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full font-bold">{{ $searchResults['journals']->count() }}</span>
                    </h2>
                    @if($searchResults['journals']->count() > 0)
                        <a href="{{ route('admin.journals', ['search' => $search]) }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua Jurnal &rarr;</a>
                    @endif
                </div>

                @if($searchResults['journals']->isEmpty())
                    <div class="bg-white p-4 rounded-lg border border-slate-200 text-xs text-slate-400 italic">Tidak ditemukan jurnal transaksi yang cocok.</div>
                @else
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Waktu</th>
                                    <th class="px-6 py-3">ID Transaksi</th>
                                    <th class="px-6 py-3">Nasabah</th>
                                    <th class="px-6 py-3">Kode Akun</th>
                                    <th class="px-6 py-3 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($searchResults['journals'] as $j)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-3 text-xs text-slate-500">{{ $j->created_at ? $j->created_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="px-6 py-3 font-medium text-slate-900">#{{ $j->transaction_id }}</td>
                                        <td class="px-6 py-3">{{ $j->transaction?->bankAccount?->customer?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-3"><span class="px-2 py-0.5 text-xs font-mono font-medium rounded bg-slate-100 text-slate-700">{{ $j->account_code }}</span></td>
                                        <td class="px-6 py-3 text-right font-medium text-slate-900">Rp {{ number_format($j->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    @endif

    <!-- Stat Cards (Simple Clean Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Total Users -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pengguna Internal</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_users']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Admin, Teller, Supervisor</div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Nasabah</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_customers']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Siswa terdaftar</div>
        </div>

        <!-- Total Accounts -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Rekening Aktif</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_accounts']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Rekening bank mini</div>
        </div>

        <!-- Total Balance -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Saldo Bank</div>
            <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($stats['total_balance'], 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Total dana tersimpan</div>
        </div>

    </div>

    <!-- Recent Audit Journals Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-slate-900">Audit Jurnal Terbaru</h2>
            <a href="{{ route('admin.journals') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua Jurnal</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Nasabah</th>
                        <th class="px-6 py-3">Kode Akun</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentJournals as $journal)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-xs">
                                {{ $journal->created_at ? $journal->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                #{{ $journal->transaction_id }}
                            </td>
                            <td class="px-6 py-3.5">
                                {{ $journal->transaction?->bankAccount?->customer?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $journal->account_code }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 capitalize">
                                <span class="px-2 py-0.5 text-xs font-medium rounded {{ $journal->type === 'debit' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $journal->type }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-900">
                                Rp {{ number_format($journal->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-slate-400 text-sm">Belum ada catatan jurnal akuntansi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
