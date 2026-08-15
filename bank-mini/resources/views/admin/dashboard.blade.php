@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard Administrator</h1>
        <p class="text-sm text-slate-500">Ringkasan statistik operasional Bank Mini Sekolah.</p>
    </div>

    <!-- Stat Cards (Simple Clean Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Total Users -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pengguna Internal</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_users']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Admin, Teller, Supervisor</div>
        </div>

        <!-- Total Customers -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Nasabah</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_customers']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Siswa terdaftar</div>
        </div>

        <!-- Total Accounts -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Rekening Aktif</div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_accounts']) }}</div>
            <div class="text-xs text-slate-400 mt-1">Rekening bank mini</div>
        </div>

        <!-- Total Balance -->
        <div class="bg-white p-5 rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Saldo Bank</div>
            <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($stats['total_balance'], 0, ',', '.') }}</div>
            <div class="text-xs text-slate-400 mt-1">Total dana tersimpan</div>
        </div>

    </div>

    <!-- Recent Audit Journals Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-base font-semibold text-slate-900">Audit Jurnal Terbaru</h2>
            <a href="{{ route('admin.journals') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat Semua Jurnal</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">ID Transaksi</th>
                        <th class="px-6 py-3">Nasabah</th>
                        <th class="px-6 py-3">Kode Akun</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentJournals as $journal)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 text-xs">
                                {{ $journal->created_at ? $journal->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                #{{ $journal->transaction_id }}
                            </td>
                            <td class="px-6 py-3.5">
                                {{ $journal->transaction?->bankAccount?->customer?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $journal->account_code }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 capitalize">
                                <span class="px-2 py-0.5 text-xs font-medium rounded {{ $journal->type === 'debit' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $journal->type }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-900">
                                Rp {{ number_format($journal->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-slate-400 text-sm">Belum ada catatan jurnal akuntansi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
