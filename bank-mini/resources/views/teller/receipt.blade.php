@extends('layouts.teller')

@section('title', 'Bukti Transaksi - TRX-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT))

@section('content')
    <!-- Top Action Bar -->
    <div class="mb-6 flex items-center justify-between no-print">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Bukti Transaksi (Struk)</h1>
            <p class="text-xs text-slate-500 mt-1">Cetak bukti transaksi fisik untuk Nasabah dan Arsip Bank Mini (BR-035 & BR-047).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('teller.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                &larr; Ke Dashboard
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- Printable Receipt Container -->
    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl border border-slate-300 shadow-lg print-container print:shadow-none print:border-none print:p-0">
        
        <!-- Receipt Header -->
        <div class="text-center border-b border-dashed border-slate-300 pb-4 mb-5">
            <h2 class="text-lg font-extrabold text-slate-900 uppercase tracking-wide">BANK MINI SEKOLAH</h2>
            <p class="text-[11px] text-slate-500 font-medium">Slip Transaksi Operasional Teller</p>
            <div class="mt-2.5 inline-block px-3 py-1 bg-slate-100 text-slate-800 font-mono text-xs font-bold rounded-md">
                TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <!-- Receipt Details Table -->
        <div class="space-y-3 text-xs border-b border-dashed border-slate-300 pb-5 mb-5">
            
            <div class="flex justify-between">
                <span class="text-slate-500">Waktu / Tanggal:</span>
                <span class="font-mono font-semibold text-slate-800">{{ $transaction->created_at ? $transaction->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s') }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-slate-500">Nama Nasabah:</span>
                <span class="font-bold text-slate-900">{{ $transaction->bankAccount?->customer?->name ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-slate-500">NIS / Kelas:</span>
                <span class="font-semibold text-slate-800">{{ $transaction->bankAccount?->customer?->nis ?? '-' }} ({{ $transaction->bankAccount?->customer?->class ?? '-' }})</span>
            </div>

            <div class="flex justify-between">
                <span class="text-slate-500">Nomor Rekening:</span>
                <span class="font-mono font-bold text-slate-900">{{ $transaction->bankAccount?->account_number ?? '-' }}</span>
            </div>

            <div class="flex justify-between pt-2 border-t border-slate-100">
                <span class="text-slate-500">Jenis Transaksi:</span>
                <span class="font-extrabold uppercase {{ $transaction->type === 'deposit' ? 'text-blue-700' : 'text-slate-800' }}">
                    {{ $transaction->type === 'deposit' ? 'SETORAN TUNAI' : 'PENARIKAN TUNAI' }}
                </span>
            </div>

            <div class="flex justify-between items-center py-2 bg-slate-50 px-3 rounded-lg my-1">
                <span class="font-bold text-slate-700">Nominal Transaksi:</span>
                <span class="text-base font-extrabold font-mono text-slate-900">
                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between pt-1">
                <span class="text-slate-500">Saldo Akhir Rekening:</span>
                <span class="font-mono font-extrabold text-blue-700">
                    Rp {{ number_format($transaction->bankAccount?->balance ?? 0, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between text-[11px] text-slate-500 pt-1">
                <span>Teller:</span>
                <span class="font-medium text-slate-700">{{ $transaction->teller?->name ?? auth()->user()->name }}</span>
            </div>

        </div>

        <!-- Receipt Footer Note -->
        <div class="text-center text-[11px] text-slate-500 leading-relaxed">
            Harap simpan bukti transaksi ini sebagai bukti transaksi.<br>
            Terima kasih telah menggunakan layanan Bank Mini Sekolah.
        </div>

    </div>
@endsection
