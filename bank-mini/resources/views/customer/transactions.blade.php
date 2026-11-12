@extends('layouts.customer')

@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Mutasi Rekening</h1>
            <p class="text-sm text-slate-500">Catatan seluruh transaksi setoran dan penarikan tunai</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customer.transactions.export-excel', request()->query()) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 rounded-md hover:bg-emerald-50 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('customer.transactions.print', request()->query()) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md transition shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm mb-6">
        <form action="{{ route('customer.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

            <div class="lg:col-span-2">
                <label for="search" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Cari ID Transaksi</label>
                <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Ketik nomor transaksi..."
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            <div>
                <label for="type" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Tipe Transaksi</label>
                <select id="type" name="type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    <option value="">Semua Tipe</option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Setoran Tunai</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai</option>
                </select>
            </div>

            <div>
                <label for="start_date" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            <div>
                <label for="end_date" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition shadow-sm">
                    Filter
                </button>
                @if(!empty($search) || !empty($type) || !empty($startDate) || !empty($endDate))
                    <a href="{{ route('customer.transactions') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition" title="Reset Filter">
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
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                        <th class="px-6 py-3">Teller</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-sm">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                {{ $trx->code }}
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
                            <td class="px-6 py-3.5 text-right font-medium text-slate-900">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-sm text-slate-600">
                                {{ $trx->teller?->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-slate-400 text-sm">
                                Belum ada data mutasi yang sesuai dengan filter Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
