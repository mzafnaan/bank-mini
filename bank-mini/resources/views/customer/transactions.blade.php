@extends('layouts.customer')

@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Mutasi Rekening</h1>
            <p class="text-sm text-slate-500">Catatan lengkap seluruh transaksi setoran simpanan dan penarikan tunai Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" type="button" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs rounded-md shadow-xs transition flex items-center gap-1.5 no-print">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Rekening Koran</span>
            </button>
        </div>
    </div>

    <!-- Printable Header (Print Only) -->
    <div class="hidden print-only mb-6 border-b-2 border-slate-800 pb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">BANK MINI SEKOLAH</h1>
                <p class="text-xs text-slate-600">Laporan Rekening Koran Nasabah</p>
            </div>
            <div class="text-right text-xs text-slate-600">
                <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
                <p class="font-bold text-slate-900">No. Rekening: {{ $bankAccount?->account_number }}</p>
            </div>
        </div>
        <div class="mt-3 text-xs grid grid-cols-2 gap-2 text-slate-700">
            <div>Nama: <strong>{{ $customer?->name }}</strong> (NIS: {{ $customer?->nis }})</div>
            <div class="text-right">Kelas: <strong>{{ $customer?->class }}</strong> | Saldo: <strong>Rp {{ number_format($bankAccount?->balance ?? 0, 0, ',', '.') }}</strong></div>
        </div>
    </div>

    <!-- Filter Bar (No-Print) -->
    <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm mb-6 no-print">
        <form action="{{ route('customer.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Cari ID Transaksi / Teller</label>
                <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Ketik nomor transaksi..."
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            <!-- Transaction Type -->
            <div>
                <label for="type" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Jenis Mutasi</label>
                <select id="type" name="type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    <option value="">Semua Transaksi</option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>+ Setoran Tunai</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>- Penarikan Tunai</option>
                </select>
            </div>

            <!-- Date Range: Start -->
            <div>
                <label for="start_date" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            <!-- Submit and Reset Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition shadow-sm">
                    Terapkan
                </button>
                @if(!empty($search) || !empty($type) || !empty($startDate) || !empty($endDate))
                    <a href="{{ route('customer.transactions') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Waktu Transaksi</th>
                        <th class="px-6 py-3.5">ID Transaksi</th>
                        <th class="px-6 py-3.5">Tipe Mutasi</th>
                        <th class="px-6 py-3.5 text-right">Nominal</th>
                        <th class="px-6 py-3.5">Petugas Teller</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-600 text-xs font-medium">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-800">
                                {{ $trx->code }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($trx->type === 'deposit')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                        </svg>
                                        <span>Setoran</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                        </svg>
                                        <span>Penarikan</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-sm {{ $trx->type === 'deposit' ? 'text-emerald-600' : 'text-slate-900' }}">
                                {{ $trx->type === 'deposit' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $trx->teller?->name ?? 'Teller Loket' }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Sukses
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                <div class="max-w-xs mx-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <p class="font-semibold text-slate-700 text-sm">Tidak Ada Transaksi Ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada data mutasi yang sesuai dengan kriteria pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 no-print">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
