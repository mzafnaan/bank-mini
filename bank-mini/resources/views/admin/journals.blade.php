@extends('layouts.admin')

@section('title', 'Audit Jurnal Akuntansi')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Audit Jurnal Akuntansi</h1>
        <p class="text-sm text-slate-500">Pencatatan pembukuan ganda (double-entry) seluruh transaksi keuangan sistem.</p>
    </div>

    @if(request('search'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-blue-900">
            <span>Filter pencarian: <strong>"{{ request('search') }}"</strong> (Ditemukan {{ $journals->total() ?? $journals->count() }} data)</span>
            <a href="{{ route('admin.journals') }}" class="font-semibold text-blue-700 hover:underline">Reset Filter</a>
        </div>
    @endif

    <!-- Journals Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 whitespace-nowrap">Tanggal & Waktu</th>
                        <th class="px-6 py-3 whitespace-nowrap">ID Transaksi</th>
                        <th class="px-6 py-3 whitespace-nowrap">No. Rekening & Nasabah</th>
                        <th class="px-6 py-3 whitespace-nowrap">Akun Pembukuan</th>
                        <th class="px-6 py-3 whitespace-nowrap">Teller</th>
                        <th class="px-6 py-3 whitespace-nowrap">Posisi</th>
                        <th class="px-6 py-3 text-right whitespace-nowrap">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($journals as $journal)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-sm text-slate-500 whitespace-nowrap">
                                {{ $journal->created_at ? $journal->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                TRX-{{ str_pad($journal->transaction_id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $journal->transaction?->bankAccount?->customer?->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-blue-600 font-medium whitespace-nowrap">
                                    {{ $journal->transaction?->bankAccount?->account_number ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($journal->account_code == '101')
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-800 border border-slate-200">101</span>
                                        <span class="text-xs font-semibold text-slate-900">Kas Tunai (Teller)</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Aset Kas Fisik Loket</div>
                                @elseif($journal->account_code == '201')
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-800 border border-slate-200">201</span>
                                        <span class="text-xs font-semibold text-slate-900">Simpanan Nasabah</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Liabilitas / Tabungan Siswa</div>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-800 border border-slate-200">{{ $journal->account_code }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-700 whitespace-nowrap">
                                {{ $journal->transaction?->teller?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($journal->type === 'debit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded bg-blue-50 text-blue-700 border border-blue-200">
                                        DEBIT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded bg-amber-50 text-amber-700 border border-amber-200">
                                        KREDIT
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($journal->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-slate-400">Belum ada data audit jurnal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($journals->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $journals->links() }}
            </div>
        @endif
    </div>
@endsection
