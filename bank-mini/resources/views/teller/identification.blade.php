@extends('layouts.teller')

@section('title', 'Identifikasi Nasabah')

@section('content')
    <!-- HTML5 QR Code Scanner CDN -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Identifikasi Rekening & Nasabah</h1>
        <p class="text-sm text-slate-500">Lakukan identifikasi nasabah menggunakan Nomor Rekening, NIS, Nama, atau Scan QR Code via Kamera.</p>
    </div>

    <!-- Search & QR Camera Scanner Input Card -->
    <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm mb-6">
        <form id="identification-form" action="{{ route('teller.identification') }}" method="GET" class="space-y-4">
            <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">Pencarian & Identifikasi Rekening</label>
            
            <div class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-1">
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Ketik No. Rekening, NIS, Nama, atau Scan QR..." autofocus
                           class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <!-- QR Code Camera Toggle Button -->
                <button type="button" onclick="toggleCameraScanner()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center justify-center gap-1.5 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="camera-btn-text">Scan QR</span>
                </button>

                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center justify-center gap-1.5 flex-shrink-0">
                    <span>Identifikasi</span>
                </button>
            </div>
        </form>

        <!-- Live Camera Scanner Box (Hidden by default, toggles on click) -->
        <div id="camera-scanner-container" class="hidden mt-4 pt-4 border-t border-slate-100 bg-slate-50 p-4 rounded-md border border-slate-200">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span>Kamera Laptop Aktif - Arahkan QR Code ke Kamera</span>
                </div>
                <button type="button" onclick="stopCameraScanner()" class="text-xs text-red-600 hover:underline font-medium">&times; Tutup Kamera</button>
            </div>
            
            <div id="qr-reader" class="w-full max-w-sm mx-auto rounded-md overflow-hidden bg-white border border-slate-300 shadow-sm"></div>
            <div id="qr-reader-results" class="text-center text-xs text-slate-500 mt-2"></div>
        </div>
    </div>

    <!-- Account Details Result -->
    @if($account)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <!-- Customer Card Profile -->
            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between border-b border-slate-100 pb-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 font-bold text-lg rounded-md flex items-center justify-center border border-blue-200">
                                {{ strtoupper(substr($account->customer?->name ?? 'N', 0, 2)) }}
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ $account->customer?->name }}</h2>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    NIS: <strong class="text-slate-700">{{ $account->customer?->nis }}</strong> • Kelas: <strong class="text-slate-700">{{ $account->customer?->class }}</strong>
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-semibold rounded">
                            Rekening Aktif
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="p-4 bg-slate-50 rounded-md border border-slate-200">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Nomor Rekening</span>
                            <span class="text-base font-mono font-bold text-slate-900 block mt-1">{{ $account->account_number }}</span>
                        </div>
                        <div class="p-4 bg-blue-50/60 rounded-md border border-blue-200">
                            <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider block">Saldo Saat Ini</span>
                            <span class="text-xl font-bold text-blue-600 block mt-1">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
                    <a href="{{ route('teller.deposit', ['account_number' => $account->account_number]) }}" class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition text-center">
                        Setor Tunai Ke Rekening Ini
                    </a>
                    <a href="{{ route('teller.withdrawal', ['account_number' => $account->account_number]) }}" class="flex-1 px-4 py-2.5 bg-slate-100 border border-slate-300 hover:bg-slate-200 text-slate-800 font-medium text-xs rounded-md shadow-sm transition text-center">
                        Tarik Tunai Dari Rekening Ini
                    </a>
                </div>
            </div>

            <!-- QR Code Card (Consistent Light Theme) -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 flex flex-col items-center justify-center text-center">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">QR Code Rekening</span>
                <div class="w-36 h-36 bg-slate-50 p-3 rounded-lg border border-slate-200 flex flex-col items-center justify-center text-slate-800 shadow-inner mb-3">
                    <div class="w-full h-full border border-dashed border-blue-400 rounded flex flex-col items-center justify-center p-2 bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-blue-600 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span class="text-[10px] font-mono text-slate-600 truncate w-full">{{ $account->qr_code }}</span>
                    </div>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded border border-slate-200">
                    {{ $account->account_number }}
                </span>
            </div>

        </div>

        <!-- Account Transactions History -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900 text-sm">Riwayat Transaksi Rekening Ini</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Tipe</th>
                            <th class="px-6 py-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($account->transactions as $trx)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 text-xs text-slate-500 font-mono">{{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="px-6 py-3">
                                    @if($trx->type === 'deposit')
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-blue-50 text-blue-700">+ Setoran</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-slate-100 text-slate-700">- Penarikan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-6 text-center text-xs text-slate-400 italic">Belum ada riwayat transaksi pada rekening ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($search !== '')
        <div class="bg-white p-8 rounded-lg border border-slate-200 text-center text-slate-500">
            <h3 class="font-bold text-slate-800 text-sm">Nasabah Tidak Ditemukan</h3>
            <p class="text-xs text-slate-400 mt-1">Tidak ada data rekening yang cocok dengan kata kunci "{{ $search }}".</p>
        </div>
    @else
        <div class="bg-white p-8 rounded-lg border border-slate-200 text-center text-slate-400 text-xs">
            Masukkan Nomor Rekening, NIS, atau klik <strong>"Scan QR (Kamera)"</strong> untuk mengidentifikasi nasabah via kamera laptop.
        </div>
    @endif

    <!-- QR Code Scanner Script -->
    <script>
        let html5QrcodeScanner = null;
        let isCameraActive = false;

        function toggleCameraScanner() {
            if (isCameraActive) {
                stopCameraScanner();
            } else {
                startCameraScanner();
            }
        }

        function startCameraScanner() {
            const container = document.getElementById('camera-scanner-container');
            container.classList.remove('hidden');
            document.getElementById('camera-btn-text').innerText = "Tutup Kamera";
            isCameraActive = true;

            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader",
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    /* verbose= */ false
                );

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function stopCameraScanner() {
            const container = document.getElementById('camera-scanner-container');
            container.classList.add('hidden');
            document.getElementById('camera-btn-text').innerText = "Scan QR (Kamera)";
            isCameraActive = false;

            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error("Failed to clear html5QrcodeScanner: ", error);
                });
                html5QrcodeScanner = null;
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan successful: ${decodedText}`, decodedResult);
            document.getElementById('search').value = decodedText;
            
            // Play a quick beep audio if supported or submit form
            stopCameraScanner();
            document.getElementById('identification-form').submit();
        }

        function onScanFailure(error) {
            // Quietly ignore scan frame failures while scanning
        }
    </script>
@endsection
