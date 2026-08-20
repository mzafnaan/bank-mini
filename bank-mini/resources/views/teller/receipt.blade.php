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
            <a href="{{ route('teller.dashboard') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-md transition">
                &larr; Ke Dashboard
            </a>
            <button onclick="window.print()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-1.5 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- Realistic Receipt Container -->
    <div class="flex justify-center">
        <div class="w-[340px] print-container print:shadow-none print:border-none print:p-0">

            <!-- Receipt Top Zigzag Edge -->
            <div class="w-full overflow-hidden no-print" style="height: 16px;">
                <svg width="100%" height="16" viewBox="0 0 340 16" preserveAspectRatio="none">
                    <defs>
                        <pattern id="zigzag-top" x="0" y="0" width="20" height="16" patternUnits="userSpaceOnUse">
                            <polygon points="0,16 10,0 20,16" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="340" height="16" fill="url(#zigzag-top)"/>
                </svg>
            </div>

            <!-- Receipt Body -->
            <div class="bg-white px-7 pt-6 pb-2 shadow-lg print:shadow-none" style="box-shadow: 0 4px 24px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05);">

                <!-- Success Icon -->
                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-md" style="box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Receipt Header -->
                <div class="text-center mb-5">
                    <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-widest">Transaksi Berhasil</h2>
                    <p class="text-[10px] text-slate-400 font-medium mt-1 tracking-wide">BANK MINI SEKOLAH</p>
                </div>

                <!-- Separator -->
                <div class="border-t border-dashed border-slate-300 my-4"></div>

                <!-- Detail Section 1: Waktu & Referensi -->
                <div class="space-y-3 text-xs mb-1">
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium w-28 flex-shrink-0">Tanggal</span>
                        <span class="font-semibold text-slate-800 text-right font-mono">{{ $transaction->created_at ? $transaction->created_at->format('d M Y | H:i:s') : date('d M Y | H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium w-28 flex-shrink-0">No. Referensi</span>
                        <span class="font-bold text-slate-900 text-right tracking-wide">TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <!-- Separator -->
                <div class="border-t border-dashed border-slate-300 my-4"></div>

                <!-- Detail Section 2: Info Nasabah -->
                <div class="space-y-3 text-xs mb-1">
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium w-28 flex-shrink-0">Nama Nasabah</span>
                        <span class="font-bold text-slate-900 text-right">{{ $transaction->bankAccount?->customer?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium w-28 flex-shrink-0">NIS</span>
                        <span class="font-semibold text-slate-700 text-right">{{ $transaction->bankAccount?->customer?->nis ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium w-28 flex-shrink-0">No. Rekening</span>
                        <span class="font-bold text-slate-900 text-right font-mono">{{ $transaction->bankAccount?->account_number ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-slate-400 font-medium w-28 flex-shrink-0">Jenis Transaksi</span>
                        <span class="font-extrabold uppercase text-right {{ $transaction->type === 'deposit' ? 'text-blue-600' : 'text-orange-600' }}">
                            {{ $transaction->type === 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}
                        </span>
                    </div>
                </div>

                <!-- Separator -->
                <div class="border-t border-dashed border-slate-300 my-4"></div>

                <!-- Detail Section 3: Nominal -->
                <div class="space-y-3 text-xs mb-1">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Nominal</span>
                        <span class="text-lg font-extrabold text-slate-900">
                            Rp{{ number_format($transaction->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Separator -->
                <div class="border-t border-dashed border-slate-300 my-4"></div>

                <!-- Total Section -->
                <div class="flex justify-between items-center py-3 px-4 bg-slate-50 rounded-lg mb-2">
                    <span class="text-sm font-bold text-slate-800">Total</span>
                    <span class="text-xl font-extrabold {{ $transaction->type === 'deposit' ? 'text-blue-600' : 'text-orange-600' }}">
                        Rp{{ number_format($transaction->amount, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Saldo Info -->
                <div class="flex justify-between items-center text-xs px-1 mt-3 mb-2">
                    <span class="text-slate-400 font-medium">Saldo Akhir Rekening</span>
                    <span class="font-extrabold text-slate-900">
                        Rp{{ number_format($transaction->bankAccount?->balance ?? 0, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Separator -->
                <div class="border-t border-dashed border-slate-300 my-4"></div>

                <!-- Teller Info -->
                <div class="flex justify-between items-center text-[11px] text-slate-400 mb-4">
                    <span>Dilayani oleh</span>
                    <span class="font-semibold text-slate-600">{{ $transaction->teller?->name ?? auth()->user()->name }}</span>
                </div>

                <!-- Footer Message -->
                <div class="text-center py-4 mt-1">
                    <p class="text-[11px] text-blue-600 font-medium mb-1">Transaksi berhasil diproses.</p>
                    <p class="text-[10px] text-slate-400 leading-relaxed">
                        Harap simpan bukti transaksi ini.<br>
                        Terima kasih telah menggunakan layanan<br>
                    </p>
                </div>

                <!-- Timestamp watermark -->
                <div class="text-center text-[9px] text-slate-300 pb-4">
                    {{ now()->format('d/m/Y H:i:s') }} — VALID
                </div>

            </div>

            <!-- Receipt Bottom Zigzag Edge -->
            <div class="w-full overflow-hidden no-print" style="height: 16px;">
                <svg width="100%" height="16" viewBox="0 0 340 16" preserveAspectRatio="none">
                    <defs>
                        <pattern id="zigzag-bottom" x="0" y="0" width="20" height="16" patternUnits="userSpaceOnUse">
                            <polygon points="0,0 10,16 20,0" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="340" height="16" fill="url(#zigzag-bottom)"/>
                </svg>
            </div>

        </div>
    </div>

    <!-- Bottom Actions (below receipt) -->
    <div class="flex justify-center mt-6 gap-3 no-print">
        <a href="{{ route('teller.dashboard') }}" class="w-[340px] flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs rounded-md transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Kembali ke Dashboard
        </a>
    </div>

@endsection
