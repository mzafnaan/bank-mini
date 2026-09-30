@extends('layouts.customer')

@section('title', 'Dashboard Nasabah')

@section('content')
    <!-- Dashboard Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Nasabah</h1>
            <p class="text-sm text-slate-500">Ringkasan informasi rekening, saldo simpanan, dan aktivitas transaksi Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customer.transactions') }}" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs rounded-md shadow-xs transition flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Lihat Mutasi</span>
            </a>
            <a href="{{ route('customer.qr-code') }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-xs transition flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <span>Kartu Digital</span>
            </a>
        </div>
    </div>

    <!-- Inactive Account Banner -->
    @if($bankAccount && $bankAccount->status === 'inactive')
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-sm flex items-start gap-3 shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold block">Perhatian: Status Rekening Sedang Nonaktif</span>
                <span class="text-xs text-rose-700">Rekening Anda dinonaktifkan sementara oleh Administrator. Anda tetap dapat memantau mutasi, namun tidak dapat melakukan setoran ataupun penarikan di loket teller sampai rekening diaktifkan kembali.</span>
            </div>
        </div>
    @endif

    <!-- Pending Withdrawal Authorization Card (BR-039, BR-040, DD-006) -->
    @if($pendingWithdrawal)
        <div class="mb-6 bg-amber-50 border-2 border-amber-300 rounded-xl p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-amber-200">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-200 text-amber-900 mb-1">
                            Menunggu Otorisasi Anda
                        </span>
                        <h2 class="text-base font-bold text-slate-900">Permintaan Penarikan Tunai di Loket Teller</h2>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Petugas Teller <strong class="text-slate-800">{{ $pendingWithdrawal->teller?->name ?? 'Teller Loket' }}</strong> mengajukan penarikan tunai sebesar <strong class="text-blue-700 text-sm">Rp {{ number_format($pendingWithdrawal->amount, 0, ',', '.') }}</strong>.
                        </p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-xs text-slate-500 block">Waktu Permintaan:</span>
                    <span class="text-xs font-semibold text-slate-800 block">{{ $pendingWithdrawal->created_at->format('d/m/Y H:i') }}</span>
                    @if($pendingWithdrawal->expires_at)
                        <span class="text-[11px] text-amber-700 font-medium">Batas: {{ $pendingWithdrawal->expires_at->format('H:i') }}</span>
                    @endif
                </div>
            </div>

            <!-- Authorization Form with PIN -->
            <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <form action="{{ route('customer.withdrawal.authorize', $pendingWithdrawal->id) }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                    @csrf
                    <div class="flex-1 max-w-xs">
                        <label for="withdrawal_pin" class="block text-xs font-semibold text-slate-700 mb-1">Masukkan 6 Digit PIN Rekening Anda:</label>
                        <input type="password" id="withdrawal_pin" name="pin" maxlength="6" pattern="[0-9]{6}" required placeholder="••••••" autocomplete="off"
                               class="w-full px-3.5 py-2 text-center tracking-widest text-lg font-bold bg-white border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 transition">
                    </div>
                    <div class="flex items-center gap-2 pt-0 sm:pt-5">
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded-md shadow-xs transition flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setujui Penarikan</span>
                        </button>
                    </div>
                </form>

                <form action="{{ route('customer.withdrawal.reject', $pendingWithdrawal->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak transaksi penarikan ini?');" class="pt-0 sm:pt-5">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 font-medium text-xs rounded-md shadow-xs transition flex items-center justify-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Tolak</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Top Grid: Digital Passbook Card & Quick QR -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Digital Passbook / Bank Card (2 Cols) -->
        <div class="lg:col-span-2 relative overflow-hidden rounded-xl bg-gradient-to-tr from-slate-900 via-blue-900 to-indigo-900 text-white p-6 sm:p-8 shadow-md border border-slate-800">
            <!-- Decorative Background Patterns -->
            <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-48 h-48 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col justify-between h-full min-h-[200px] gap-6">
                <!-- Card Header -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-md bg-white/10 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold tracking-wide uppercase text-white block">Bank Mini Sekolah</span>
                            <span class="text-[10px] tracking-wider text-blue-200 uppercase block">Kartu Simpanan Siswa</span>
                        </div>
                    </div>
                    <div>
                        @if($bankAccount?->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/40 backdrop-blur-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Nonaktif
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Balance Display -->
                <div>
                    <span class="text-xs font-medium text-slate-300 block mb-1">Saldo Simpanan Saat Ini</span>
                    <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white flex items-baseline gap-1">
                        <span class="text-xl sm:text-2xl font-bold text-blue-300">Rp</span>
                        <span>{{ number_format($balance, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Card Footer Info -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between pt-4 border-t border-white/10 gap-3">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Nama Nasabah</span>
                        <span class="text-sm font-bold text-white tracking-wide block truncate max-w-[240px]">{{ $customer?->name }}</span>
                    </div>
                    <div class="sm:text-center">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Nomor Rekening</span>
                        <span class="text-sm font-mono font-bold text-blue-200 tracking-wider block">{{ $bankAccount?->account_number }}</span>
                    </div>
                    <div class="sm:text-right">
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 block">NIS & Kelas</span>
                        <span class="text-xs font-semibold text-slate-200 block">{{ $customer?->nis }} • {{ $customer?->class }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick QR Card (1 Col) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col items-center justify-between text-center">
            <div class="w-full">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 block mb-3">QR Code Rekening Anda</span>
                <div class="w-36 h-36 mx-auto bg-slate-50 p-2.5 rounded-lg border border-slate-200 flex flex-col items-center justify-center shadow-inner mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($bankAccount?->qr_code ?? $bankAccount?->account_number ?? '') }}" 
                         alt="QR Code {{ $bankAccount?->account_number }}" 
                         class="w-full h-full object-contain rounded">
                </div>
                <div class="text-xs font-mono font-bold text-slate-800 bg-slate-100 px-3 py-1 rounded inline-block border border-slate-200 mb-2">
                    {{ $bankAccount?->account_number }}
                </div>
                <p class="text-[11px] text-slate-500 leading-tight">
                    Tunjukkan QR Code ini kepada Teller saat melakukan setoran atau penarikan di loket.
                </p>
            </div>
            
            <a href="{{ route('customer.qr-code') }}" class="w-full mt-4 py-2 bg-slate-50 hover:bg-slate-100 text-blue-600 font-semibold text-xs rounded-md border border-slate-200 transition text-center">
                Buka & Cetak Kartu Digital &rarr;
            </a>
        </div>

    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <!-- Saldo Tabungan -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo Tabungan</span>
                <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($balance, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Saldo aktif yang dapat ditarik</div>
        </div>

        <!-- Total Setoran -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Setoran</span>
                <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600 mt-2">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Akumulasi seluruh simpanan masuk</div>
        </div>

        <!-- Total Penarikan -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Penarikan</span>
                <div class="w-8 h-8 rounded bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-rose-600 mt-2">Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Akumulasi dana yang ditarik</div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mutasi Transaksi</span>
                <div class="w-8 h-8 rounded bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ $transactionCount }} <span class="text-sm font-normal text-slate-500">kali</span></div>
            <div class="text-xs text-slate-400 mt-1">Total riwayat mutasi tercatat</div>
        </div>

    </div>

    <!-- Account Details & Profile Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">Informasi Identitas & Rekening Nasabah</h2>
            <span class="text-xs text-slate-500">Terdaftar sejak {{ $customer?->created_at ? $customer->created_at->format('d M Y') : '-' }}</span>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Lengkap</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">{{ $customer?->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Induk Siswa (NIS)</dt>
                    <dd class="text-sm font-semibold text-slate-900 mt-1">{{ $customer?->nis }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kelas / Rombel</dt>
                    <dd class="text-sm font-semibold text-slate-900 mt-1">{{ $customer?->class }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Telepon</dt>
                    <dd class="text-sm font-semibold text-slate-900 mt-1">{{ $customer?->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Rekening Bank</dt>
                    <dd class="text-sm font-mono font-bold text-blue-700 mt-1">{{ $bankAccount?->account_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Jurusan</dt>
                    <dd class="text-sm font-semibold text-slate-900 mt-1">{{ $bankAccount?->major_code ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Rekening</dt>
                    <dd class="mt-1">
                        @if($bankAccount?->status === 'active')
                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Nonaktif</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">Username Login</dt>
                    <dd class="text-sm font-mono font-medium text-slate-700 mt-1">{{ $customerAccount?->username }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Aktivitas Transaksi Terakhir</h2>
                <p class="text-xs text-slate-500">5 transaksi simpanan terbaru pada rekening Anda.</p>
            </div>
            <a href="{{ route('customer.transactions') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                <span>Lihat Seluruh Mutasi</span>
                <span>&rarr;</span>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu & Tanggal</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Jenis Transaksi</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                        <th class="px-6 py-3">Petugas Teller</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-sm">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs font-semibold text-slate-800">
                                {{ $trx->code }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($trx->type === 'deposit')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        + Setoran Tunai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        - Penarikan Tunai
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-semibold {{ $trx->type === 'deposit' ? 'text-emerald-600' : 'text-slate-900' }}">
                                {{ $trx->type === 'deposit' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600">
                                {{ $trx->teller?->name ?? 'Loket Teller' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Belum ada aktivitas transaksi pada rekening ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
