<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Transaksi {{ $period_label }} - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8">

    <!-- Top Action Bar (Screen only) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('supervisor.transactions.report', request()->query()) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-md border border-slate-300 shadow-sm transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Laporan</span>
        </a>

        <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-md shadow transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Dokumen Sekarang</span>
        </button>
    </div>

    <!-- Main Printable Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-lg border border-slate-200 shadow-sm print:shadow-none print:border-none print:p-0">
        
        <!-- School & Bank Mini Header (KOP Surat) -->
        <div class="flex items-center gap-5 border-b-2 border-slate-900 pb-5 mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK" class="w-20 h-20 object-contain flex-shrink-0">
            <div class="flex-1 text-center pr-10">
                <h2 class="text-xs font-semibold tracking-widest text-slate-500 uppercase">PEMERINTAH DAERAH ISTIMEWA YOGYAKARTA</h2>
                <h1 class="text-xl font-extrabold text-slate-900 uppercase tracking-tight">SMK NEGERI 1 BANTUL</h1>
                <h3 class="text-sm font-bold text-blue-700 uppercase tracking-wider">UNIT LAYANAN BANK MINI SEKOLAH</h3>
                <p class="text-xs text-slate-600 mt-1">Jl. Bantul No. 1, Sabdodadi, Bantul, D.I. Yogyakarta 55715 | Telp: (0274) 367156</p>
            </div>
        </div>

        <!-- Document Title -->
        <div class="text-center mb-6">
            <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider underline">
                LAPORAN TRANSAKSI MUTASI KAS {{ strtoupper($period) }}
            </h2>
            <p class="text-xs text-slate-600 mt-1">
                Periode: <strong class="text-slate-900">{{ $period_label }}</strong>
                @if($type !== 'all')
                    | Tipe: <strong class="capitalize text-slate-900">{{ $type === 'deposit' ? 'Hanya Setoran' : 'Hanya Penarikan' }}</strong>
                @endif
            </p>
        </div>

        <!-- Summary Recap Box -->
        <div class="grid grid-cols-3 gap-3 mb-6 p-4 bg-slate-50 border border-slate-200 rounded-lg text-xs">
            <div>
                <span class="text-slate-500 block">Total Setoran Tunai</span>
                <span class="text-sm font-bold text-emerald-700 block mt-0.5">Rp {{ number_format($total_deposit, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400">({{ $count_deposit }} transaksi)</span>
            </div>
            <div>
                <span class="text-slate-500 block">Total Penarikan Tunai</span>
                <span class="text-sm font-bold text-rose-700 block mt-0.5">Rp {{ number_format($total_withdrawal, 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400">({{ $count_withdrawal }} transaksi)</span>
            </div>
            <div>
                <span class="text-slate-500 block">Kas Bersih (Net Flow)</span>
                <span class="text-sm font-bold {{ $net_cash >= 0 ? 'text-blue-700' : 'text-red-700' }} block mt-0.5">
                    Rp {{ number_format($net_cash, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-slate-400">Total: {{ $total_count }} transaksi</span>
            </div>
        </div>

        <!-- Transactions Table -->
        <table class="w-full text-left text-xs border border-slate-300 border-collapse mb-8">
            <thead>
                <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300">
                    <th class="border border-slate-300 px-3 py-2 text-center w-10">No</th>
                    <th class="border border-slate-300 px-3 py-2 whitespace-nowrap">Waktu</th>
                    <th class="border border-slate-300 px-3 py-2">No. TRX</th>
                    <th class="border border-slate-300 px-3 py-2">No. Rekening</th>
                    <th class="border border-slate-300 px-3 py-2">Nasabah</th>
                    <th class="border border-slate-300 px-3 py-2">Kelas</th>
                    <th class="border border-slate-300 px-3 py-2">Teller</th>
                    <th class="border border-slate-300 px-3 py-2 text-center">Jenis</th>
                    <th class="border border-slate-300 px-3 py-2 text-right">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $index => $trx)
                    <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/60' : 'bg-white' }}">
                        <td class="border border-slate-300 px-3 py-1.5 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-300 px-3 py-1.5 whitespace-nowrap font-mono text-[11px]">
                            {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 font-mono text-[11px]">
                            TRX-{{ str_pad($trx->id, 6, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 font-mono text-[11px]">
                            {{ $trx->bankAccount?->account_number ?? '-' }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 font-medium text-slate-900">
                            {{ $trx->bankAccount?->customer?->name ?? 'N/A' }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 text-slate-600">
                            {{ $trx->bankAccount?->customer?->class ?? '-' }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 text-slate-600">
                            {{ $trx->teller?->name ?? 'System' }}
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 text-center font-medium">
                            @if($trx->type === 'deposit')
                                <span class="text-emerald-700">Setoran</span>
                            @else
                                <span class="text-rose-700">Penarikan</span>
                            @endif
                        </td>
                        <td class="border border-slate-300 px-3 py-1.5 text-right font-mono font-semibold">
                            {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="border border-slate-300 px-4 py-8 text-center text-slate-400 italic">
                            Tidak ada data transaksi pada periode laporan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-100 font-bold border-t-2 border-slate-400">
                    <td colspan="8" class="border border-slate-300 px-3 py-2 text-right">TOTAL KAS BERSIH:</td>
                    <td class="border border-slate-300 px-3 py-2 text-right font-mono text-sm text-slate-900">
                        Rp {{ number_format($net_cash, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature / Pengesahan Supervisor -->
        <div class="flex justify-end mt-8 pt-4 text-xs text-slate-800 break-inside-avoid" style="page-break-inside: avoid;">
            <div class="text-center w-64">
                <p>Bantul, {{ date('d F Y') }}</p>
                <p class="font-semibold text-slate-900 mt-0.5">Supervisor Bank Mini</p>
                <div class="h-16"></div>
                <p class="font-bold underline text-slate-900">( {{ auth()->user()->name }} )</p>
                <p class="text-slate-500 text-[10px] mt-0.5">NIP/ID: {{ auth()->user()->username }}</p>
            </div>
        </div>
    </div>

</body>
</html>

