@extends('layouts.customer')

@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Mutasi Rekening</h1>
            <p class="text-sm text-slate-500">Catatan seluruh transaksi setoran dan penarikan tunai</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm mb-6">
        <form action="{{ route('customer.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

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

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition shadow-sm">
                    Filter
                </button>
                @if(!empty($search) || !empty($type) || !empty($startDate) || !empty($endDate))
                    <a href="{{ route('customer.transactions') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition">
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
                            <td class="px-6 py-3.5 capitalize">
                                @if($trx->type === 'deposit')
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded bg-blue-50 text-blue-700">
                                        + Setoran
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded bg-slate-100 text-slate-700">
                                        - Penarikan
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
