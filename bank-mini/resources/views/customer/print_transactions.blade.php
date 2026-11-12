<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Mutasi Rekening {{ $bankAccount?->account_number }} - {{ $customer->name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <style>
        * { box-sizing: border-box; }
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
            bottom: -4px;
            border-bottom: 1px solid #94a3b8;
        }
        @media print {
            .no-print { display: none !important; }
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
            .print-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
            .print-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-4 sm:py-8 print:py-0 print:bg-white text-slate-800">

    <!-- Action Toolbar (Hidden in Print) -->
    @php
        $isApi = request()->is('api/*') || request()->filled('token');
        $excelExportUrl = $isApi
            ? url('/api/v1/nasabah/mutasi/export-excel?' . http_build_query(request()->query()))
            : route('customer.transactions.export-excel', request()->query());
    @endphp
    <div class="no-print max-w-4xl mx-auto mb-4 px-3 sm:px-4 flex flex-wrap items-center justify-between gap-2.5">
        <div class="flex items-center gap-2">
            @if($isApi)
                <button type="button" onclick="if(window.history.length > 1){window.history.back();}else{window.close();}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition shadow-sm cursor-pointer">
                    &larr; Tutup / Kembali
                </button>
            @else
                <a href="{{ route('customer.transactions') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition shadow-sm">
                    &larr; Kembali
                </a>
            @endif
            <span class="text-xs text-slate-500 hidden sm:inline">Laporan Mutasi Transaksi</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $excelExportUrl }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md hover:bg-emerald-100 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xlsx)
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md transition shadow-sm cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Print Sheet Container -->
    <div class="print-sheet max-w-4xl mx-auto bg-white border border-slate-200 shadow-md p-4 sm:p-8 md:p-10 rounded-lg">
        
        <!-- Header & Kop Rapi & Proporsional -->
        <div class="kop-double-border flex items-center justify-between gap-3 sm:gap-4 mb-6">
            <div class="flex items-center gap-3 sm:gap-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-12 h-12 sm:w-16 sm:h-16 object-contain shrink-0" onerror="this.style.display='none'">
                <div>
                    <h1 class="text-base sm:text-xl font-extrabold text-slate-900 tracking-tight uppercase leading-tight">Bank Mini Sekolah</h1>
                    <p class="text-[11px] sm:text-xs text-slate-600 font-semibold mt-0.5">SMK Negeri 1 Bantul &bull; Laboratorium Perbankan Syariah / Mini</p>
                    <p class="text-[10px] sm:text-[11px] text-slate-500 mt-0.5">Jl. Parangtritis No.KM.11, Sabdodadi, Bantul, D.I. Yogyakarta</p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[10px] sm:text-xs text-slate-400 block font-medium">Tanggal Cetak</span>
                <span class="text-xs sm:text-sm font-bold text-slate-800">{{ date('d F Y') }}</span>
            </div>
        </div>

        <!-- Customer & Account Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 print-cols-2 gap-2 sm:gap-3 p-3.5 sm:p-4 bg-slate-50/80 border border-slate-200 rounded-lg mb-6 text-xs">
            <div class="space-y-1.5">
                <div class="flex items-center justify-between sm:justify-start"><span class="w-28 sm:w-32 text-slate-500 font-medium">Nama Nasabah:</span><span class="font-bold text-slate-900">{{ $customer->name }}</span></div>
                <div class="flex items-center justify-between sm:justify-start"><span class="w-28 sm:w-32 text-slate-500 font-medium">NIS:</span><span class="font-mono text-slate-800">{{ $customer->nis }}</span></div>
                <div class="flex items-center justify-between sm:justify-start"><span class="w-28 sm:w-32 text-slate-500 font-medium">Kelas / Rombel:</span><span class="text-slate-800">{{ $customer->class }} {{ $customer->class_group ?? '' }}</span></div>
            </div>
            <div class="space-y-1.5 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200">
                <div class="flex items-center justify-between sm:justify-start">
                    <span class="w-28 sm:w-32 text-slate-500 font-medium">No. Rekening:</span>
                    <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">{{ $bankAccount?->account_number }}</span>
                </div>
                <div class="flex items-center justify-between sm:justify-start"><span class="w-28 sm:w-32 text-slate-500 font-medium">Periode Mutasi:</span><span class="font-semibold text-slate-900">{{ $periodLabel }}</span></div>
                <div class="flex items-center justify-between sm:justify-start">
                    <span class="w-28 sm:w-32 text-slate-500 font-medium">Status Rekening:</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold {{ $bankAccount?->status === 'active' ? 'text-emerald-700' : 'text-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $bankAccount?->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $bankAccount?->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Financial Summary Ribbon (Grid 2x2 on Mobile, 4 Cols on Desktop/Print) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 print-cols-4 gap-2.5 sm:gap-3 mb-6">
            <div class="p-2.5 sm:p-3 bg-slate-50 border border-slate-200 rounded-lg text-center">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 block mb-0.5">Saldo Awal</span>
                <span class="font-mono font-bold text-xs sm:text-sm text-slate-800">Rp {{ number_format($openingBalance, 0, ',', '.') }}</span>
            </div>
            <div class="p-2.5 sm:p-3 bg-emerald-50/70 border border-emerald-200 rounded-lg text-center">
                <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-800 block mb-0.5">Total Setoran (+)</span>
                <span class="font-mono font-bold text-xs sm:text-sm text-emerald-700">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</span>
            </div>
            <div class="p-2.5 sm:p-3 bg-rose-50/70 border border-rose-200 rounded-lg text-center">
                <span class="text-[10px] uppercase font-bold tracking-wider text-rose-800 block mb-0.5">Total Penarikan (-)</span>
                <span class="font-mono font-bold text-xs sm:text-sm text-rose-700">Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}</span>
            </div>
            <div class="p-2.5 sm:p-3 bg-blue-50/80 border border-blue-200 rounded-lg text-center">
                <span class="text-[10px] uppercase font-bold tracking-wider text-blue-900 block mb-0.5">Saldo Akhir</span>
                <span class="font-mono font-bold text-xs sm:text-sm text-blue-700">Rp {{ number_format($closingBalance, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Transactions Table (Horizontally Scrollable on Mobile) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 min-w-[620px] sm:min-w-full">
                <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 w-8 text-center border-r border-slate-200">No</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Tanggal &amp; Waktu</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">No. Transaksi</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Keterangan</th>
                        <th class="py-2.5 px-3 text-right border-r border-slate-200 text-emerald-700">Setoran (Kredit)</th>
                        <th class="py-2.5 px-3 text-right border-r border-slate-200 text-rose-700">Penarikan (Debit)</th>
                        <th class="py-2.5 px-3 text-right font-bold text-slate-900">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    <tr class="bg-slate-50/50">
                        <td class="py-2 px-3 text-center border-r border-slate-200 text-slate-400 font-sans">-</td>
                        <td class="py-2 px-3 border-r border-slate-200 text-slate-500 font-sans">{{ $startDate ? date('d/m/Y 00:00', strtotime($startDate)) : '-' }}</td>
                        <td class="py-2 px-3 border-r border-slate-200 text-slate-400 italic">SALDO-AWAL</td>
                        <td class="py-2 px-3 border-r border-slate-200 text-slate-500 font-sans">Saldo Awal Periode</td>
                        <td class="py-2 px-3 text-right border-r border-slate-200 text-slate-400">-</td>
                        <td class="py-2 px-3 text-right border-r border-slate-200 text-slate-400">-</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-800">Rp {{ number_format($openingBalance, 0, ',', '.') }}</td>
                    </tr>
                    @forelse($items as $idx => $t)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-2 px-3 text-center border-r border-slate-200 font-sans text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 font-sans text-slate-600">{{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 font-semibold text-slate-800">{{ $t->code }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 font-sans text-slate-700">
                                {{ $t->type === 'deposit' ? 'Setoran Tunai' : 'Penarikan Tunai' }}
                                @if($t->teller)
                                    <span class="text-slate-400 text-[10px]">({{ $t->teller->name }})</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right border-r border-slate-200 text-emerald-700 font-medium">
                                {{ $t->type === 'deposit' ? 'Rp ' . number_format($t->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-2 px-3 text-right border-r border-slate-200 text-rose-700 font-medium">
                                {{ $t->type === 'withdrawal' ? 'Rp ' . number_format($t->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-2 px-3 text-right font-bold text-slate-900">
                                Rp {{ number_format($t->running_balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400 font-sans text-xs">
                                Tidak ada aktivitas transaksi pada rentang periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer sepenuhnya dihilangkan sesuai permintaan -->

    </div>

</body>
</html>
