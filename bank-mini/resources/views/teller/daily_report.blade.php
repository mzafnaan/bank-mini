@extends('layouts.teller')

@section('title', 'Penutupan Kas Harian')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Penutupan Kas Harian</h1>
        <p class="text-sm text-slate-500">Proses rekonsiliasi saldo kas operasional harian Teller dan pengiriman laporan ke
            Supervisor.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Daily Closing Form & Calculation Card -->
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Rekapitulasi Kas Hari Ini</h2>
                        <span class="text-xs text-slate-500">Tanggal:
                            {{ date('d F Y', strtotime($reportData['report_date'])) }}</span>
                    </div>
                    @if($reportData['existing_report'])
                        @if($reportData['existing_report']->status === 'approved')
                            <span
                                class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold text-xs rounded">
                                Approved Supervisor
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 font-semibold text-xs rounded">
                                Status: Draft menunggu Supervisor
                            </span>
                        @endif
                    @else
                        <span
                            class="px-2.5 py-1 bg-slate-100 text-slate-600 border border-slate-200 font-semibold text-xs rounded">
                            Belum Disubmit
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 bg-slate-50 rounded-md border border-slate-200">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">1. Saldo Kas
                            Awal</span>
                        <span class="text-xl font-bold text-slate-800 block mt-1">Rp
                            {{ number_format($reportData['opening_cash'], 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 bg-blue-50/60 rounded-md border border-blue-200">
                        <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider block">2. Total Setoran
                            (+)</span>
                        <span class="text-xl font-bold text-blue-700 block mt-1">Rp
                            {{ number_format($reportData['total_deposit'], 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-md border border-slate-200">
                        <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider block">3. Total Penarikan
                            (-)</span>
                        <span class="text-xl font-bold text-slate-800 block mt-1">Rp
                            {{ number_format($reportData['total_withdrawal'], 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-md shadow-sm border border-blue-200">
                        <span class="text-xs font-semibold text-blue-900 uppercase tracking-wider block">4. Saldo Kas
                            Sistem</span>
                        <span class="text-xl font-bold text-blue-700 block mt-1" id="system_balance"
                            data-value="{{ $reportData['expected_closing_cash'] }}">
                            Rp {{ number_format($reportData['expected_closing_cash'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- Physical Cash Verification Form -->
                <form action="{{ route('teller.daily-report.store') }}" method="POST"
                    class="space-y-4 pt-4 border-t border-slate-100">
                    @csrf

                    <div>
                        <label for="physical_cash"
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jumlah Uang Fisik Di Laci Kas (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                            <input type="number" id="physical_cash" name="physical_cash"
                                value="{{ old('physical_cash', $reportData['existing_report']?->closing_cash ?? $reportData['expected_closing_cash']) }}"
                                min="0" step="1000" required placeholder="Masukkan hasil hitungan fisik..."
                                oninput="checkBalanceMatch()"
                                class="w-full pl-10 pr-4 py-2 text-lg font-bold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition text-slate-900">
                        </div>
                    </div>

                    <!-- Live Reconciliation Status Indicator -->
                    <div id="match_status_box"
                        class="p-3.5 rounded-md text-xs font-medium transition flex items-center gap-2">
                        <!-- JS injected status -->
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="submit" id="submit_btn"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-1.5">
                            <span>Kirim Laporan Kas Ke Supervisor</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>

        <!-- Guidance Side Panel -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-2">Aturan Penutupan Kas</h3>
                <ul class="space-y-1.5 text-xs text-slate-600 list-disc list-inside">
                    <li>Penutupan kas dilakukan akhir hari jam kerja operasional.</li>
                    <li>Laporan harian hanya dapat diproses apabila uang fisik sesuai dengan kas sistem.</li>
                    <li>Status laporan awal adalah <strong>Draft</strong> untuk diverifikasi Supervisor.</li>
                </ul>
            </div>
        </div>

    </div>

    <script>
        function checkBalanceMatch() {
            const systemVal = parseFloat(document.getElementById('system_balance').getAttribute('data-value')) || 0;
            const inputVal = parseFloat(document.getElementById('physical_cash').value) || 0;
            const box = document.getElementById('match_status_box');
            const btn = document.getElementById('submit_btn');

            if (inputVal === systemVal) {
                box.className = "p-3.5 bg-blue-50 text-blue-900 border border-blue-200 rounded-md text-xs font-medium flex items-center gap-2";
                box.innerHTML = `<svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                     <div><strong>Sesuai (Match)!</strong> Uang fisik cocok dengan perhitungan kas sistem.</div>`;
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                const diff = inputVal - systemVal;
                const formattedDiff = new Intl.NumberFormat('id-ID').format(Math.abs(diff));
                const diffText = diff > 0 ? `Kelebihan Rp ${formattedDiff}` : `Kekurangan Rp ${formattedDiff}`;

                box.className = "p-3.5 bg-red-50 text-red-900 border border-red-200 rounded-md text-xs font-medium flex items-center gap-2";
                box.innerHTML = `<svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                     <div><strong>Tidak Sesuai (Selisih: ${diffText})!</strong> Hasil perhitungan uang fisik belum sesuai (BR-050).</div>`;
            }
        }

        document.addEventListener('DOMContentLoaded', checkBalanceMatch);
    </script>
@endsection