@extends('layouts.supervisor')

@section('title', 'Dashboard Overview')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Supervisor</h1>
            <p class="text-sm text-slate-500">Pengawasan operasional harian dan verifikasi laporan teller.</p>
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
            <a href="{{ route('supervisor.dashboard') }}" class="px-3 py-1.5 bg-white border border-blue-300 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-md transition shadow-sm">
                Reset Pencarian
            </a>
        </div>

        @if($searchResults)
            <!-- Search Results Sections -->
            
            <!-- 1. Daily Reports Search Results -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Hasil Laporan Harian</span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full font-bold">{{ $searchResults['reports']->total() }}</span>
                    </h2>
                    <a href="{{ route('supervisor.reports', ['search' => $search]) }}" class="text-xs font-semibold text-blue-600 hover:underline">Kelola Laporan</a>
                </div>
                
                @if($searchResults['reports']->isEmpty())
                    <div class="bg-white p-4 rounded-lg border border-slate-200 text-xs text-slate-400 italic">Tidak ditemukan laporan harian yang cocok.</div>
                @else
                    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-4">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Teller</th>
                                    <th class="px-6 py-3 text-right">Kas Akhir</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($searchResults['reports'] as $report)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-3.5 font-medium text-slate-900">
                                            {{ $report->report_date ? $report->report_date->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 font-semibold text-slate-800">
                                            {{ $report->teller?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-medium text-slate-900">
                                            Rp {{ number_format($report->closing_cash, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-3.5">
                                            @if($report->status === 'approved')
                                                <span class="px-2.5 py-1 text-xs font-medium rounded bg-blue-50 text-blue-700">Disetujui</span>
                                            @else
                                                <span class="px-2.5 py-1 text-xs font-medium rounded bg-amber-50 text-amber-700">Menunggu Approval</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            @if($report->status === 'draft')
                                                <form action="{{ route('supervisor.reports.approve', $report) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md shadow-sm transition">
                                                        Setujui
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-400 font-medium">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- 2. Audit Journals Search Results -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Hasil Audit Jurnal</span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full font-bold">{{ $searchResults['journals']->count() }}</span>
                    </h2>
                    <a href="{{ route('supervisor.journals', ['search' => $search]) }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat Semua Jurnal</a>
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
        
        <!-- Menunggu Approval -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Menunggu Approval</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['pending_reports_count']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Laporan harian teller</div>
        </div>

        <!-- Total Disetujui -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Laporan Disetujui</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['approved_reports_count']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Telah diverifikasi</div>
        </div>

        <!-- Total Teller -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Teller</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_tellers']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Pengguna bertugas</div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Transaksi Hari Ini</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['today_transactions_count']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Setoran & penarikan</div>
        </div>

    </div>

    <!-- Pending Reports Section -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm mb-8">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Laporan Harian Menunggu Approval</h2>
                <p class="text-xs text-slate-500">Laporan harian teller yang memerlukan verifikasi supervisor.</p>
            </div>
            <a href="{{ route('supervisor.reports', ['status' => 'draft']) }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua Laporan</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Teller</th>
                        <th class="px-6 py-3 text-right">Kas Awal</th>
                        <th class="px-6 py-3 text-right">Total Setoran</th>
                        <th class="px-6 py-3 text-right">Total Penarikan</th>
                        <th class="px-6 py-3 text-right">Kas Akhir</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stats['pending_reports'] as $report)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 font-medium text-slate-900 whitespace-nowrap">
                                {{ $report->report_date ? $report->report_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">
                                {{ $report->teller?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs text-slate-600">
                                Rp {{ number_format($report->opening_cash, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs text-slate-700 font-medium">
                                Rp {{ number_format($report->total_deposit, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs text-slate-700 font-medium">
                                Rp {{ number_format($report->total_withdrawal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs font-bold text-slate-900">
                                Rp {{ number_format($report->closing_cash, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <form action="{{ route('supervisor.reports.approve', $report) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md shadow-sm transition">
                                        Setujui Laporan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-sm">
                                Tidak ada laporan harian yang menunggu approval.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Audit Journals Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-slate-900">Audit Jurnal Terbaru</h2>
            <a href="{{ route('supervisor.journals') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua Jurnal</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Nasabah</th>
                        <th class="px-6 py-3">Teller</th>
                        <th class="px-6 py-3">Kode Akun</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stats['recent_journals'] as $journal)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-xs">
                                {{ $journal->created_at ? $journal->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                #{{ $journal->transaction_id }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-800">
                                {{ $journal->transaction?->bankAccount?->customer?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $journal->transaction?->teller?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-100 text-slate-700">
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
                            <td colspan="7" class="px-6 py-6 text-center text-slate-400 text-sm">Belum ada catatan jurnal akuntansi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
