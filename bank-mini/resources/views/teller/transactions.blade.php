@extends('layouts.teller')

@section('title', 'Riwayat Transaksi Teller')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Transaksi Teller</h1>
            <p class="text-sm text-slate-500">Daftar transaksi setoran & penarikan yang diproses pada loket Anda.</p>
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm mb-6">
        <form action="{{ route('teller.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            
            <div class="sm:col-span-2">
                <label for="search" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Pencarian Nasabah / Rekening</label>
                <input type="text" id="search" name="search" value="{{ $search }}" placeholder="No. Rekening, NIS, atau Nama..."
                       class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            <div>
                <label for="type" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-500 mb-1">Tipe Transaksi</label>
                <select id="type" name="type" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    <option value=""> Semua Tipe </option>
                    <option value="deposit" {{ $type === 'deposit' ? 'selected' : '' }}>Setoran Tunai</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition shadow-sm">
                    Filter
                </button>
                @if(!empty($search) || !empty($type) || !empty($date))
                    <a href="{{ route('teller.transactions') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Transactions Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
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
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                TRX-{{ str_pad($trx->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="font-semibold text-slate-900 block">{{ $trx->bankAccount?->customer?->name ?? 'N/A' }}</span>
                                <span class="text-xs text-slate-500 block">NIS: {{ $trx->bankAccount?->customer?->nis ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-xs font-semibold text-slate-700">
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
                            <td class="px-6 py-3.5 text-right font-semibold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                <a href="{{ route('teller.transactions.receipt', $trx->id) }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded transition inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-slate-400 text-sm">
                                Tidak ada data transaksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $transactions->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
