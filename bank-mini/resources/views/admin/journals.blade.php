@extends('layouts.admin')

@section('title', 'Audit Jurnal Akuntansi')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Audit Jurnal Akuntansi</h1>
        <p class="text-sm text-slate-500">Pencatatan pembukuan ganda (double-entry bookkeeping) seluruh transaksi keuangan sistem.</p>
    </div>

    @if(request('search'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-blue-900">
            <span>Filter pencarian: <strong>"{{ request('search') }}"</strong> (Ditemukan {{ $journals->count() }} data)</span>
            <a href="{{ route('admin.journals') }}" class="font-semibold text-blue-700 hover:underline">Reset Filter</a>
        </div>
    @endif

    <!-- Journals Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Tanggal & Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">No. Rekening & Nasabah</th>
                        <th class="px-6 py-3">Teller / Petugas</th>
                        <th class="px-6 py-3">Kode Akun</th>
                        <th class="px-6 py-3">Posisi Tipe</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($journals as $journal)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                                {{ $journal->created_at ? $journal->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-900">
                                #{{ $journal->transaction_id }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $journal->transaction?->bankAccount?->customer?->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-blue-600 font-mono">
                                    {{ $journal->transaction?->bankAccount?->account_number ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $journal->transaction?->teller?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-mono font-semibold rounded bg-slate-100 text-slate-800">
                                    {{ $journal->account_code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 capitalize">
                                @if($journal->type === 'debit')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded bg-blue-50 text-blue-700">DEBIT</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded bg-slate-100 text-slate-700">KREDIT</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
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
    </div>
@endsection
