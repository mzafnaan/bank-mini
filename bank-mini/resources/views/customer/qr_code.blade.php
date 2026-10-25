@extends('layouts.customer')

@section('title', 'QR Code Rekening')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">QR Code Rekening</h1>
        <p class="text-sm text-slate-500">Tunjukkan QR Code ini kepada Teller saat melakukan setoran atau penarikan tunai di loket.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- QR Code Card -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 flex flex-col items-center">
            <div class="mb-4 p-4 bg-white border border-slate-200 rounded-lg inline-block">
                @if($bankAccount)
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($bankAccount->account_number) }}"
                         alt="QR Code {{ $bankAccount->account_number }}"
                         class="w-48 h-48">
                @else
                    <div class="w-48 h-48 bg-slate-100 flex items-center justify-center text-xs text-slate-400 rounded">
                        QR Tidak Tersedia
                    </div>
                @endif
            </div>

            <span class="inline-flex items-center px-3 py-1 text-sm font-bold rounded bg-blue-50 text-blue-700 mb-3">
                {{ $bankAccount?->account_number ?? '-' }}
            </span>

            <p class="text-xs text-slate-400 text-center mb-4">Scan kode ini untuk identifikasi rekening Anda secara cepat.</p>

            @if($bankAccount)
                <button onclick="window.print()" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                    Cetak Kartu Digital Rekening
                </button>
            @endif
        </div>

        <!-- Account Info -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Detail Rekening</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Nasabah</dt>
                        <dd class="text-sm font-bold text-slate-900 mt-1">{{ $customer?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">NIS</dt>
                        <dd class="text-sm font-semibold text-slate-900 mt-1">{{ $customer?->nis }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas</dt>
                        <dd class="text-sm font-semibold text-slate-900 mt-1">{{ $customer?->class }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Rekening</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded bg-blue-50 text-blue-700">
                                {{ $bankAccount?->account_number ?? '-' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Rekening</dt>
                        <dd class="mt-1">
                            @if($bankAccount?->status === 'active')
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded bg-blue-50 text-blue-700">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo</dt>
                        <dd class="text-sm font-bold text-blue-600 mt-1">Rp {{ number_format($bankAccount?->balance ?? 0, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

    </div>

    <!-- Print Style -->
    <style>
        @media print {
            nav, .sidebar, header, button, a, .no-print { display: none !important; }
            body { background: white !important; }
            .print-only { display: block !important; }
        }
    </style>
@endsection
