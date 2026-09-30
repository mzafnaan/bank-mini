@extends('layouts.teller')

@section('title', 'Bukti Transaksi - TRX-' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT))

@section('content')
    <!-- Top Action Bar -->
    <div class="mb-6 flex items-center justify-between no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bukti Transaksi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Cetak bukti transaksi fisik untuk Nasabah dan Arsip Bank Mini</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teller.dashboard') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-md transition">
                Ke Dashboard
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-1.5 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- Clean Receipt Card -->
    <div class="flex justify-center">
        <div class="w-[350px] print-container print:shadow-none print:border-none print:p-0">

            <!-- Receipt Body -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 print:border-none print:shadow-none print:p-0">

                <!-- Success Icon -->
                <div class="flex justify-center mb-3">
                    <div class="w-12 h-12 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Receipt Header -->
                <div class="text-center mb-4">
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Transaksi Berhasil</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Bank Mini Sekolah</p>
                </div>

                <!-- Separator -->
                <div class="border-t border-slate-100 my-3.5"></div>

                <!-- Section: Waktu & Referensi -->
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Tanggal</span>
                        <span class="font-medium text-slate-700">{{ $transaction->created_at ? $transaction->created_at->format('d M Y | H:i:s') : date('d M Y | H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">No. Referensi</span>
                        <span class="font-semibold text-slate-900">TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <!-- Separator -->
                <div class="border-t border-slate-100 my-3.5"></div>

                <!-- Section: Info Nasabah -->
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Nama Nasabah</span>
                        <span class="font-semibold text-slate-900">{{ $transaction->bankAccount?->customer?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">NIS</span>
                        <span class="text-slate-600">{{ $transaction->bankAccount?->customer?->nis ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">No. Rekening</span>
                        <span class="font-semibold text-slate-900">{{ $transaction->bankAccount?->account_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Jenis Transaksi</span>
                        @if($transaction->type === 'deposit')
                            <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Setoran Tunai</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-slate-100 text-slate-700">Penarikan Tunai</span>
                        @endif
                    </div>
                </div>

                <!-- Separator -->
                <div class="border-t border-slate-100 my-3.5"></div>

                <!-- Section: Nominal & Total -->
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Nominal</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2.5 px-3.5 bg-slate-50 border border-slate-100 rounded-lg">
                        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</span>
                        <span class="text-base font-bold text-slate-900">
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center pt-1">
                        <span class="text-slate-400">Saldo Akhir Rekening</span>
                        <span class="font-semibold text-slate-800">
                            Rp {{ number_format($transaction->bankAccount?->balance ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Dilayani oleh</span>
                        <span class="text-slate-600">{{ $transaction->teller?->name ?? auth()->user()->name }}</span>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="text-center pt-3 mt-3.5 border-t border-slate-100">
                    <p class="text-xs text-slate-600 font-medium">Transaksi berhasil diproses.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                        Harap simpan bukti transaksi ini.<br>
                        Terima kasih telah menggunakan layanan Bank Mini.
                    </p>
                    <p class="text-[10px] text-slate-300 mt-2">
                        {{ now()->format('d/m/Y H:i:s') }}
                    </p>
                </div>

            </div>

        </div>
    </div>

    <!-- Bottom Action Button -->
    <div class="flex justify-center mt-4 no-print">
        <a href="{{ route('teller.dashboard') }}" class="w-[350px] flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium text-xs rounded-lg transition shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Kembali ke Dashboard
        </a>
    </div>

@endsection
