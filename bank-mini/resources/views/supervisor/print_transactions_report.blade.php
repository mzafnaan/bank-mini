<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Kas {{ $period_label }} - Bank Mini SMK N 1 Bantul</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .kop-double-border {
            border-bottom: 2px solid #0f172a;
            position: relative;
            padding-bottom: 12px;
        }
        .kop-double-border::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -5px;
            border-bottom: 1px solid #0f172a;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #0f172a !important;
                font-size: 10pt;
            }
            .print-sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.2cm 1.5cm;
            }
            tr {
                page-break-inside: avoid;
            }
            .break-inside-avoid {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 text-xs">

    <!-- Top Action Bar (Screen only) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('supervisor.transactions.report', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium rounded-lg border border-slate-300 shadow-sm transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Rekap</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 mr-2 hidden sm:inline">Siap cetak atau simpan sebagai PDF</span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Sheet -->
    <div class="print-sheet max-w-4xl mx-auto bg-white p-10 sm:p-12 rounded-xl border border-slate-200 shadow-sm">
        
        <!-- School & Bank Mini Header (KOP Surat) -->
        <div class="kop-double-border flex items-center gap-5 mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" class="w-20 h-20 object-contain flex-shrink-0">
            <div class="flex-1 text-center pr-8">
                <h3 class="text-[11px] font-medium tracking-widest text-slate-500 uppercase">PEMERINTAH DAERAH ISTIMEWA YOGYAKARTA</h3>
                <h1 class="text-lg font-bold text-slate-900 uppercase tracking-tight mt-0.5">SMK NEGERI 1 BANTUL</h1>
                <h2 class="text-xs font-semibold text-blue-900 uppercase tracking-wider mt-0.5">UNIT LAYANAN BANK MINI SEKOLAH</h2>
                <p class="text-[11px] text-slate-500 mt-1">Jl. Bantul No. 1, Sabdodadi, Bantul, D.I. Yogyakarta 55715 | Telp: (0274) 367156</p>
            </div>
        </div>

        <!-- Document Title & Meta -->
        <div class="text-center mb-7">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                LAPORAN TRANSAKSI MUTASI KAS {{ strtoupper($period) }}
            </h2>
            <div class="flex items-center justify-center gap-2 mt-1 text-xs text-slate-600">
                <span>Periode: <strong class="font-medium text-slate-800">{{ $period_label }}</strong></span>
                @if($type !== 'all')
                    <span class="text-slate-300">•</span>
                    <span>Filter: <strong class="font-medium text-slate-800">{{ $type === 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}</strong></span>
                @endif
                <span class="text-slate-300">•</span>
                <span>Dicetak: <span class="font-mono text-slate-600">{{ now()->format('d/m/Y H:i') }} WIB</span></span>
            </div>
        </div>

        <!-- Professional Summary Recap Cards -->
        <div class="grid grid-cols-3 gap-4 mb-7">
            <div class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-lg">
                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-500 block">Total Setoran</span>
                <span class="text-sm font-semibold text-slate-900 font-mono block mt-1">Rp {{ number_format($total_deposit, 0, ',', '.') }}</span>
                <span class="text-[10.5px] text-slate-500 mt-0.5 block">{{ $count_deposit }} transaksi tercatat</span>
            </div>
            <div class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-lg">
                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-500 block">Total Penarikan</span>
                <span class="text-sm font-semibold text-slate-900 font-mono block mt-1">Rp {{ number_format($total_withdrawal, 0, ',', '.') }}</span>
                <span class="text-[10.5px] text-slate-500 mt-0.5 block">{{ $count_withdrawal }} transaksi tercatat</span>
            </div>
            <div class="p-3.5 bg-slate-50/70 border border-slate-200 rounded-lg">
                <span class="text-[10px] font-medium uppercase tracking-wider text-slate-500 block">Arus Kas Bersih (Net)</span>
                <span class="text-sm font-semibold font-mono block mt-1 {{ $net_cash >= 0 ? 'text-slate-900' : 'text-rose-700' }}">
                    Rp {{ number_format($net_cash, 0, ',', '.') }}
                </span>
                <span class="text-[10.5px] text-slate-500 mt-0.5 block">Total {{ $total_count }} mutasi kas</span>
            </div>
        </div>

        <!-- Transactions Table -->
        <table class="w-full text-left text-xs border border-slate-200 border-collapse mb-8">
            <thead>
                <tr class="bg-slate-50 text-slate-600 font-semibold text-[11px] border-b border-slate-200">
                    <th class="border-r border-slate-200 px-3 py-2.5 text-center w-10">No</th>
                    <th class="border-r border-slate-200 px-3 py-2.5 whitespace-nowrap">Waktu</th>
                    <th class="border-r border-slate-200 px-3 py-2.5">No. TRX</th>
                    <th class="border-r border-slate-200 px-3 py-2.5">No. Rekening</th>
                    <th class="border-r border-slate-200 px-3 py-2.5">Nasabah</th>
                    <th class="border-r border-slate-200 px-3 py-2.5">Kelas</th>
                    <th class="border-r border-slate-200 px-3 py-2.5">Teller</th>
                    <th class="border-r border-slate-200 px-3 py-2.5 text-center">Jenis</th>
                    <th class="px-3 py-2.5 text-right">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($transactions as $index => $trx)
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/40' : 'bg-white' }} hover:bg-slate-50/80 transition-colors">
                        <td class="border-r border-slate-200 px-3 py-2 text-center text-slate-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                        <td class="border-r border-slate-200 px-3 py-2 whitespace-nowrap font-mono text-[11px] text-slate-600">
                            {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="border-r border-slate-200 px-3 py-2 font-mono text-[11px] text-slate-700">
                            TRX-{{ str_pad($trx->id, 6, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="border-r border-slate-200 px-3 py-2 font-mono text-[11px] text-slate-700">
                            {{ $trx->bankAccount?->account_number ?? '-' }}
                        </td>
                        <td class="border-r border-slate-200 px-3 py-2 font-medium text-slate-800">
                            {{ $trx->bankAccount?->customer?->name ?? 'N/A' }}
                        </td>
                        <td class="border-r border-slate-200 px-3 py-2 text-slate-600">
                            {{ $trx->bankAccount?->customer?->class ?? '-' }}
                        </td>
                        <td class="border-r border-slate-200 px-3 py-2 text-slate-600">
                            {{ $trx->teller?->name ?? 'System' }}
                        </td>
                        <td class="border-r border-slate-200 px-3 py-2 text-center">
                            @if($trx->type === 'deposit')
                                <span class="inline-block px-2 py-0.5 text-[10.5px] font-medium text-emerald-800 bg-emerald-50 border border-emerald-200/60 rounded">
                                    Setoran
                                </span>
                            @else
                                <span class="inline-block px-2 py-0.5 text-[10.5px] font-medium text-rose-800 bg-rose-50 border border-rose-200/60 rounded">
                                    Penarikan
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-mono font-medium text-slate-900 text-xs">
                            {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-400 italic">
                            Tidak ada transaksi mutasi kas tercatat pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-50/80 font-semibold border-t-2 border-slate-300">
                    <td colspan="8" class="border-r border-slate-200 px-3 py-2.5 text-right text-slate-700 uppercase tracking-wider text-[11px]">
                        TOTAL KAS BERSIH (NET FLOW):
                    </td>
                    <td class="px-3 py-2.5 text-right font-mono text-xs font-semibold text-slate-900">
                        Rp {{ number_format($net_cash, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Formal Signature / Pengesahan Supervisor -->
        <div class="flex justify-end mt-10 pt-4 text-xs text-slate-800 break-inside-avoid">
            <div class="text-center w-64">
                <p class="text-slate-600">Bantul, {{ date('d F Y') }}</p>
                <p class="font-medium text-slate-800 mt-1">Supervisor Bank Mini Sekolah</p>
                
                <div class="h-20 flex items-center justify-center">
                    <!-- Space for physical stamp & signature -->
                </div>
                
                <p class="font-semibold text-slate-900 underline underline-offset-4">( {{ auth()->user()->name }} )</p>
                <p class="text-slate-500 text-[11px] mt-0.5 font-mono">ID / NIP: {{ auth()->user()->username }}</p>
            </div>
        </div>

    </div>

</body>
</html>
