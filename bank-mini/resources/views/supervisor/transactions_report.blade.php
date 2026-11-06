@extends('layouts.supervisor')

@section('title', 'Laporan Transaksi - Supervisor')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laporan Transaksi Kas</h1>
            <p class="text-sm text-slate-500">Cetak dan pantau rekap transaksi kas teller secara Harian, Mingguan, dan Bulanan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('supervisor.transactions.report.print', request()->query()) }}" target="_blank"
               class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5 mb-6">
        <form action="{{ route('supervisor.transactions.report') }}" method="GET" class="space-y-4">
            <!-- Period Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider mr-2">Periode:</span>
                <label class="cursor-pointer">
                    <input type="radio" name="period" value="daily" class="sr-only peer" {{ $period === 'daily' ? 'checked' : '' }} onchange="this.form.submit()">
                    <span class="px-3 py-1.5 text-xs font-medium rounded-md peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-200 border border-transparent text-slate-600 hover:bg-slate-50 transition">
                        Harian
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="period" value="weekly" class="sr-only peer" {{ $period === 'weekly' ? 'checked' : '' }} onchange="this.form.submit()">
                    <span class="px-3 py-1.5 text-xs font-medium rounded-md peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-200 border border-transparent text-slate-600 hover:bg-slate-50 transition">
                        Mingguan
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="period" value="monthly" class="sr-only peer" {{ $period === 'monthly' ? 'checked' : '' }} onchange="this.form.submit()">
                    <span class="px-3 py-1.5 text-xs font-medium rounded-md peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:border-blue-200 border border-transparent text-slate-600 hover:bg-slate-50 transition">
                        Bulanan
                    </span>
                </label>
            </div>

            <!-- Date / Month Pickers -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                @if($period === 'daily')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>
                @elseif($period === 'weekly')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $start_date }}" max="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $end_date }}" max="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>
                @elseif($period === 'monthly')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Bulan</label>
                        <select name="month" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                            @foreach([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ] as $num => $name)
                                <option value="{{ $num }}" {{ (int)$month === $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tahun</label>
                        <select name="year" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                            @for($y = (int)date('Y'); $y >= (int)date('Y') - 3; $y--)
                                <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tipe Transaksi</label>
                    <select name="type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Setoran Saja</option>
                        <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Penarikan Saja</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs rounded-md shadow-sm transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Period Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Setoran -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Total Setoran</span>
            <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($total_deposit, 0, ',', '.') }}</div>
            <span class="text-xs text-emerald-600 font-medium mt-1 inline-block">{{ $count_deposit }} transaksi setoran</span>
        </div>

        <!-- Total Penarikan -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Total Penarikan</span>
            <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($total_withdrawal, 0, ',', '.') }}</div>
            <span class="text-xs text-rose-600 font-medium mt-1 inline-block">{{ $count_withdrawal }} transaksi penarikan</span>
        </div>

        <!-- Kas Bersih -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Kas Bersih (Net)</span>
            <div class="text-2xl font-bold {{ $net_cash >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                Rp {{ number_format($net_cash, 0, ',', '.') }}
            </div>
            <span class="text-xs text-slate-400 mt-1 inline-block">Setoran dikurangi penarikan</span>
        </div>

        <!-- Total Frekuensi -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Frekuensi Transaksi</span>
            <div class="text-2xl font-bold text-slate-900">{{ $total_count }}</div>
            <span class="text-xs text-slate-400 mt-1 inline-block">Periode {{ $period_label }}</span>
        </div>
    </div>

    <!-- Transactions Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Rincian Transaksi {{ $period_label }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar mutasi kas tabungan nasabah yang tercatat pada sistem.</p>
            </div>
            <span class="text-xs font-medium text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded-md">
                Ditemukan {{ $transactions->count() }} data
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 whitespace-nowrap">Waktu</th>
                        <th class="px-6 py-3 whitespace-nowrap">ID Transaksi</th>
                        <th class="px-6 py-3 whitespace-nowrap">No. Rekening</th>
                        <th class="px-6 py-3 whitespace-nowrap">Nama Nasabah</th>
                        <th class="px-6 py-3 whitespace-nowrap">Kelas</th>
                        <th class="px-6 py-3 whitespace-nowrap">Teller</th>
                        <th class="px-6 py-3 whitespace-nowrap">Jenis</th>
                        <th class="px-6 py-3 text-right whitespace-nowrap">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 text-sm text-slate-500 whitespace-nowrap">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-sm font-medium text-slate-900 whitespace-nowrap">
                                TRX-{{ str_pad($trx->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-3.5 text-sm font-medium text-slate-900 whitespace-nowrap">
                                {{ $trx->bankAccount?->account_number ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="font-semibold text-slate-900 block">{{ $trx->bankAccount?->customer?->name ?? 'N/A' }}</span>
                                @if($trx->bankAccount?->customer?->nis)
                                    <span class="text-xs text-slate-500 block">NIS: {{ $trx->bankAccount->customer->nis }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-sm text-slate-600 whitespace-nowrap">
                                {{ $trx->bankAccount?->customer?->class ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-sm text-slate-600 whitespace-nowrap">
                                {{ $trx->teller?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                @if($trx->type === 'deposit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                        Setoran
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">
                                        Penarikan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400 text-sm">
                                Tidak ada transaksi yang tercatat untuk periode {{ $period_label }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

