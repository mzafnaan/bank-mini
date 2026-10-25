@extends('layouts.customer')

@section('title', 'Dashboard Nasabah')

@section('content')
    <!-- Dashboard Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Nasabah</h1>
            <p class="text-sm text-slate-500">Ringkasan informasi rekening dan aktivitas transaksi Anda.</p>
        </div>
    </div>

    <!-- Inactive Account Banner -->
    @if($bankAccount && $bankAccount->status === 'inactive')
        <div class="mb-6 p-3.5 bg-red-50 border border-red-200 text-red-800 text-sm rounded-md flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Rekening Anda sedang nonaktif. Anda tetap dapat memantau mutasi, namun tidak dapat melakukan transaksi sampai
                rekening diaktifkan kembali oleh Administrator.</span>
        </div>
    @endif

    <!-- Pending Withdrawal Authorization (BR-039, BR-040, DD-006) -->
    @if($pendingWithdrawal)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-blue-200">
                <div>
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 mb-1.5">
                        Menunggu Otorisasi Anda
                    </span>
                    <h2 class="text-base font-semibold text-slate-900">Permintaan Penarikan Tunai di Loket Teller</h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Teller <strong class="text-slate-800">{{ $pendingWithdrawal->teller?->name ?? 'Teller Loket' }}</strong>
                        mengajukan penarikan sebesar
                        <strong class="text-slate-900">Rp {{ number_format($pendingWithdrawal->amount, 0, ',', '.') }}</strong>
                        pada {{ $pendingWithdrawal->created_at->format('d/m/Y H:i') }}.
                        @if($pendingWithdrawal->expires_at)
                            <span class="text-slate-500">Batas waktu: {{ $pendingWithdrawal->expires_at->format('H:i') }}.</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                <form action="{{ route('customer.withdrawal.authorize', $pendingWithdrawal->id) }}" method="POST"
                    class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 flex-1">
                    @csrf
                    <div class="flex-1 max-w-xs">
                        <label for="withdrawal_pin"
                            class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Masukkan PIN 6
                            Digit</label>
                        <input type="password" id="withdrawal_pin" name="pin" maxlength="6" pattern="[0-9]{6}" required
                            placeholder="••••••" autocomplete="off"
                            class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 transition">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                        Setujui Penarikan
                    </button>
                </form>

                <form action="{{ route('customer.withdrawal.reject', $pendingWithdrawal->id) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menolak transaksi penarikan ini?');">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs rounded-md transition">
                        Tolak
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Saldo Tabungan</div>
            <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($balance, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Saldo simpanan saat ini</div>
        </div>

        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Setoran</div>
            <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Akumulasi simpanan masuk</div>
        </div>

        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Penarikan</div>
            <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Akumulasi dana ditarik</div>
        </div>

        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Transaksi</div>
            <div class="text-2xl font-bold text-slate-900">{{ $transactionCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Riwayat mutasi tercatat</div>
        </div>

    </div>

    <!-- Account Information -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm mb-8">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900">Informasi Rekening</h2>
            <a href="{{ route('customer.qr-code') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat QR
                Code</a>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-y-6 gap-x-4">
                <div>
                    <div class="text-sm text-slate-500 mb-1">Nama Nasabah</div>
                    <div class="font-medium text-slate-900">{{ $customer?->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">NIS</div>
                    <div class="font-medium text-slate-900">{{ $customer?->nis }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">Kelas</div>
                    <div class="font-medium text-slate-900">{{ $customer?->class }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">No. Rekening</div>
                    <div class="font-medium text-slate-900">{{ $bankAccount?->account_number }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">Status Rekening</div>
                    <div>
                        @if($bankAccount?->status === 'active')
                            <span class="text-blue-600 font-medium">Aktif</span>
                        @else
                            <span class="text-slate-500 font-medium">Nonaktif</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">Telepon</div>
                    <div class="font-medium text-slate-900">{{ $customer?->phone ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">Username</div>
                    <div class="font-medium text-slate-900">{{ $customerAccount?->username }}</div>
                </div>
                <div>
                    <div class="text-sm text-slate-500 mb-1">Terdaftar Sejak</div>
                    <div class="font-medium text-slate-900">{{ $customer?->created_at ? $customer->created_at->format('d M Y') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-slate-900">Transaksi Terakhir</h2>
            <a href="{{ route('customer.transactions') }}"
                class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua Mutasi</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                        <th class="px-6 py-3">Teller</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-sm">
                                {{ $trx->created_at ? $trx->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                {{ $trx->code }}
                            </td>
                            <td class="px-6 py-3.5 capitalize">
                                @if($trx->type === 'deposit')
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded bg-blue-50 text-blue-700">
                                        + Setoran
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-medium rounded bg-slate-100 text-slate-700">
                                        - Penarikan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-900">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3.5 text-sm text-slate-600">
                                {{ $trx->teller?->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-slate-400 text-sm">
                                Belum ada aktivitas transaksi pada rekening ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection