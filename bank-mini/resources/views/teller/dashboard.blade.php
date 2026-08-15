@extends('layouts.teller')

@section('title', 'Dashboard Teller')

@section('content')
    <!-- Dashboard Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Teller</h1>
            <p class="text-sm text-slate-500">Ringkasan transaksi dan operasional loket teller Bank Mini Sekolah.</p>
        </div>
    </div>

    <!-- Live Search Results Notice Banner (if search query present) -->
    @if(!empty($search))
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2 text-sm text-blue-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Hasil identifikasi rekening untuk kata kunci: <strong class="font-semibold text-blue-950">"{{ $search }}"</strong></span>
            </div>
            <a href="{{ route('teller.dashboard') }}" class="px-3 py-1.5 bg-white border border-blue-300 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-md transition shadow-sm">
                Reset Pencarian
            </a>
        </div>

        @if($searchedAccount)
            <div class="mb-8 bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Nasabah</span>
                        <div class="text-base font-bold text-slate-900 mt-1">{{ $searchedAccount->customer?->name ?? 'N/A' }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">NIS: <span class="font-mono text-slate-700 font-semibold">{{ $searchedAccount->customer?->nis ?? '-' }}</span> | Kelas: {{ $searchedAccount->customer?->class ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">No. Rekening & Saldo</span>
                        <div class="text-sm font-mono font-bold text-blue-600 mt-1">{{ $searchedAccount->account_number }}</div>
                        <div class="text-lg font-bold text-slate-900 mt-0.5">Rp {{ number_format($searchedAccount->balance, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center gap-2 md:justify-end">
                        <a href="{{ route('teller.deposit', ['account_number' => $searchedAccount->account_number]) }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition shadow-sm">
                            Setor Tunai
                        </a>
                        <a href="{{ route('teller.withdrawal', ['account_number' => $searchedAccount->account_number]) }}" class="px-3 py-1.5 bg-slate-100 border border-slate-300 hover:bg-slate-200 text-slate-800 font-medium text-xs rounded-md transition shadow-sm">
                            Tarik Tunai
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-8 bg-white p-4 rounded-lg border border-slate-200 text-xs text-slate-400 italic">
                Rekening atau Nasabah tidak ditemukan dengan kata kunci "{{ $search }}".
            </div>
        @endif
    @endif

    <!-- Stat Cards (Simple Clean Grid matching Admin) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Total Deposit Today -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Setoran Hari Ini</div>
            <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($stats['total_deposit'], 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $stats['count_deposit'] }} transaksi setoran</div>
        </div>

        <!-- Total Withdrawal Today -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Penarikan Hari Ini</div>
            <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($stats['total_withdrawal'], 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">{{ $stats['count_withdrawal'] }} transaksi penarikan</div>
        </div>

        <!-- Net Cash Today -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Kas Bersih Hari Ini</div>
            <div class="text-2xl font-bold {{ $stats['net_cash'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                Rp {{ number_format($stats['net_cash'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Selisih setoran & penarikan</div>
        </div>

        <!-- Daily Closing Status -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Penutupan Kas</div>
            <div class="mt-2">
                @if($stats['daily_report'])
                    @if($stats['daily_report']->status === 'approved')
                        <span class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Approved Supervisor</span>
                    @else
                        <span class="px-2.5 py-1 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Draft (Menunggu Supervisor)</span>
                    @endif
                @else
                    <span class="px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">Belum Disubmit</span>
                @endif
            </div>
        </div>

    </div>

    <!-- Quick Operational Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        
        <a href="{{ route('teller.identification') }}" class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm hover:border-blue-500 transition group flex items-center gap-3">
            <div class="w-10 h-10 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-xs group-hover:text-blue-600 transition">Identifikasi Nasabah</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Cari Rekening & QR Code</p>
            </div>
        </a>

        <a href="{{ route('teller.deposit') }}" class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm hover:border-blue-500 transition group flex items-center gap-3">
            <div class="w-10 h-10 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-xs group-hover:text-blue-600 transition">Setoran Tunai</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Terima simpanan nasabah</p>
            </div>
        </a>

        <a href="{{ route('teller.withdrawal') }}" class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm hover:border-blue-500 transition group flex items-center gap-3">
            <div class="w-10 h-10 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-xs group-hover:text-blue-600 transition">Penarikan Tunai</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Otorisasi PIN nasabah</p>
            </div>
        </a>

        <a href="{{ route('teller.daily-report') }}" class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm hover:border-blue-500 transition group flex items-center gap-3">
            <div class="w-10 h-10 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-slate-900 text-xs group-hover:text-blue-600 transition">Penutupan Kas</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Rekonsiliasi akhir hari</p>
            </div>
        </a>

    </div>

    <!-- Recent Transactions Table (Matching Admin Audit Table Style) -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-slate-900">Transaksi Terbaru Loket Ini</h2>
            <a href="{{ route('teller.transactions') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua Transaksi</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Nasabah</th>
                        <th class="px-6 py-3">No. Rekening</th>
                        <th class="px-6 py-3">Tipe Transaksi</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                        <th class="px-6 py-3 text-center">Struk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stats['recent_transactions'] as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-xs">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-mono font-medium text-slate-900">
                                TRX-{{ str_pad($trx->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="font-semibold text-slate-900 block">{{ $trx->bankAccount?->customer?->name ?? 'N/A' }}</span>
                                <span class="text-xs text-slate-500 block">NIS: {{ $trx->bankAccount?->customer?->nis ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-700">
                                {{ $trx->bankAccount?->account_number ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 capitalize">
                                @if($trx->type === 'deposit')
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-blue-50 text-blue-700">
                                        + Setoran
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-slate-100 text-slate-700">
                                        - Penarikan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-semibold text-slate-900">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <a href="{{ route('teller.transactions.receipt', $trx->id) }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded transition inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-slate-400 text-sm">
                                Belum ada transaksi yang diproses pada loket ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
