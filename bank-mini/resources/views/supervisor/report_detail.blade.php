@extends('layouts.supervisor')

@section('title', 'Detail Laporan Harian')

@section('content')
    <!-- Header with Back Button -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('supervisor.reports') }}" class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-medium">
                    &larr; Kembali ke Daftar Laporan
                </a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Detail Laporan Harian</h1>
            <p class="text-sm text-slate-500">Rincian operasional teller <strong>{{ $report->teller?->name }}</strong> tanggal <strong>{{ $report->report_date ? $report->report_date->format('d/m/Y') : '-' }}</strong>.</p>
        </div>

        <!-- Approval / Rejection Action Header -->
        <div class="flex items-center gap-2">
            @if($report->status === 'draft')
                <form action="{{ route('supervisor.reports.approve', $report) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                        Setujui Laporan
                    </button>
                </form>
                <form action="{{ route('supervisor.reports.reject', $report) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menolak laporan harian ini?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                        Tolak Laporan
                    </button>
                </form>
            @elseif($report->status === 'approved')
                <span class="px-3 py-1.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    Disetujui oleh {{ $report->supervisor?->name ?? 'Supervisor' }} {{ $report->approved_at ? '(' . $report->approved_at->format('d/m/Y H:i') . ')' : '' }}
                </span>
            @elseif($report->status === 'rejected')
                <span class="px-3 py-1.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                    Ditolak oleh {{ $report->supervisor?->name ?? 'Supervisor' }} {{ $report->approved_at ? '(' . $report->approved_at->format('d/m/Y H:i') . ')' : '' }}
                </span>
            @endif
        </div>
    </div>

    <!-- Overview Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Kas Awal -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Saldo Kas Awal</div>
            <div class="text-xl font-bold text-slate-900">Rp {{ number_format($report->opening_cash, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Saldo kas penutupan sebelumnya</div>
        </div>

        <!-- Total Setoran -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Setoran Tunai</div>
            <div class="text-xl font-bold text-slate-900">Rp {{ number_format($report->total_deposit, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Total dana masuk hari ini</div>
        </div>

        <!-- Total Penarikan -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Penarikan Tunai</div>
            <div class="text-xl font-bold text-slate-900">Rp {{ number_format($report->total_withdrawal, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Total dana keluar hari ini</div>
        </div>

        <!-- Kas Akhir Sistem -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Saldo Kas Akhir Sistem</div>
            <div class="text-xl font-bold text-blue-600">Rp {{ number_format($report->closing_cash, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Perhitungan saldo kas fisik</div>
        </div>

    </div>

    <!-- Transactions List Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Rincian Transaksi Teller</h2>
                <p class="text-xs text-slate-500">Daftar seluruh transaksi setoran & penarikan yang diproses teller pada tanggal laporan ini.</p>
            </div>
            <span class="text-xs font-medium text-slate-500">Total Transaksi: {{ $transactions->count() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Waktu</th>
                        <th class="px-6 py-3.5">ID Transaksi</th>
                        <th class="px-6 py-3.5">No. Rekening</th>
                        <th class="px-6 py-3.5">Nama Nasabah</th>
                        <th class="px-6 py-3.5">Jenis Transaksi</th>
                        <th class="px-6 py-3.5 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                {{ $trx->created_at ? $trx->created_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-mono font-medium text-slate-900">
                                #{{ $trx->id }}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs text-blue-600">
                                {{ $trx->bankAccount?->account_number ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">
                                {{ $trx->bankAccount?->customer?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5 capitalize">
                                @if($trx->type === 'deposit')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Setoran</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-700">Penarikan</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-900">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-sm">
                                Tidak ada catatan transaksi pada tanggal laporan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
