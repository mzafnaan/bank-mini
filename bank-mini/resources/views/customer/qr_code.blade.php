@extends('layouts.customer')

@section('title', 'QR Code Rekening')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">QR Code & Kartu Digital Rekening</h1>
            <p class="text-sm text-slate-500">Identitas resmi rekening nasabah untuk transaksi cepat di loket Teller Bank Mini.</p>
        </div>
        <div class="flex items-center gap-2 no-print">
            <button onclick="window.print()" type="button" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs rounded-md shadow-xs transition flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Kartu Digital</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
        
        <!-- Main Digital Card (Print Container) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sm:p-8 flex flex-col items-center print-container">
                
                <!-- School & Bank Header -->
                <div class="w-full pb-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-900 leading-tight block">BANK MINI SEKOLAH</span>
                            <span class="text-xs text-slate-500 font-medium block">KARTU DIGITAL IDENTITAS NASABAH</span>
                        </div>
                    </div>
                    <div>
                        @if($bankAccount?->status === 'active')
                            <span class="px-2.5 py-1 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Rekening Aktif
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                Rekening Nonaktif
                            </span>
                        @endif
                    </div>
                </div>

                <!-- QR Code Box -->
                <div class="my-6 p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-blue-200 flex flex-col items-center justify-center shadow-inner">
                    <div class="w-48 h-48 sm:w-56 sm:h-56 bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center justify-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($bankAccount?->qr_code ?? $bankAccount?->account_number ?? '') }}" 
                             alt="QR Code {{ $bankAccount?->account_number }}"
                             class="w-full h-full object-contain">
                    </div>
                    <span class="text-[11px] text-slate-500 mt-2 font-medium">Scan QR Code ini pada scanner loket teller</span>
                </div>

                <!-- Account Number Big Badge with Copy Button -->
                <div class="w-full max-w-sm flex items-center justify-between gap-2 p-3 bg-slate-100 rounded-lg border border-slate-200 mb-6">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Nomor Rekening</span>
                        <span id="accountNumberText" class="text-lg font-mono font-bold text-blue-700 block tracking-wider">{{ $bankAccount?->account_number }}</span>
                    </div>
                    <button onclick="copyAccountNumber()" type="button" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded border border-slate-300 shadow-2xs transition flex items-center gap-1.5 no-print" id="copyBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span id="copyBtnText">Salin</span>
                    </button>
                </div>

                <!-- Student Information Grid on Card -->
                <div class="w-full pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Nama Nasabah</span>
                        <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate">{{ $customer?->name }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">NIS</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 block">{{ $customer?->nis }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Kelas / Rombel</span>
                        <span class="text-xs sm:text-sm font-semibold text-slate-800 block">{{ $customer?->class }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Jurusan</span>
                        <span class="text-xs sm:text-sm font-semibold text-blue-700 block">{{ $bankAccount?->major_code ?? '-' }}</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Instructions & Usage Guidelines (No-Print) -->
        <div class="space-y-6 no-print">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Petunjuk Penggunaan</span>
                </h2>
                
                <ol class="space-y-4 text-xs text-slate-600 list-decimal list-inside">
                    <li class="leading-relaxed">
                        <strong class="text-slate-800">Buka di Smartphone:</strong> Buka halaman ini pada smartphone Anda saat Anda hendak bertransaksi di loket Bank Mini Sekolah.
                    </li>
                    <li class="leading-relaxed">
                        <strong class="text-slate-800">Scan di Loket:</strong> Tunjukkan tampilan QR Code kepada petugas Teller. Kamera scanner loket akan otomatis mengenali rekening Anda.
                    </li>
                    <li class="leading-relaxed">
                        <strong class="text-slate-800">Otorisasi Transaksi:</strong> Untuk setoran tunai, verifikasi akan otomatis. Untuk penarikan tunai, Anda akan diminta memasukkan PIN otorisasi transaksi.
                    </li>
                    <li class="leading-relaxed">
                        <strong class="text-slate-800">Cetak Kartu:</strong> Anda juga dapat mencetak kartu digital ini melalui tombol Cetak Kartu untuk disimpan di dompet Anda.
                    </li>
                </ol>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-xs text-blue-900 shadow-xs">
                <div class="flex items-start gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div>
                        <span class="font-bold block mb-1">Keamanan Data QR Code</span>
                        <p class="text-[11px] text-blue-800 leading-normal">
                            QR Code ini hanya memuat nomor rekening Anda sebagai media identifikasi nasabah dan tidak mengandung password maupun PIN Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function copyAccountNumber() {
            const acc = document.getElementById('accountNumberText').innerText.trim();
            navigator.clipboard.writeText(acc).then(() => {
                const btnText = document.getElementById('copyBtnText');
                btnText.innerText = 'Tersalin!';
                setTimeout(() => {
                    btnText.innerText = 'Salin';
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        }
    </script>
    @endpush
@endsection
