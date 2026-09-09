@extends('layouts.supervisor')

@section('title', 'Laporan Harian Teller')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Laporan Harian Teller</h1>
        <p class="text-sm text-slate-500">Daftar verifikasi dan persetujuan (approval) laporan penutupan kas harian teller.</p>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm mb-6">
        <form action="{{ route('supervisor.reports') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="status" class="text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Status:</label>
                <select id="status" name="status" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="flex-1 flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama teller / tanggal (YYYY-MM-DD)..."
                       class="w-full sm:w-64 px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md shadow-sm transition">
                    Filter
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('supervisor.reports') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-md transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Nama Teller</th>
                    <th class="px-6 py-3 text-right">Kas Awal</th>
                    <th class="px-6 py-3 text-right">Setoran</th>
                    <th class="px-6 py-3 text-right">Penarikan</th>
                    <th class="px-6 py-3 text-right">Kas Akhir</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($reports as $report)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $report->report_date ? $report->report_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ $report->teller?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right text-slate-600">
                            Rp {{ number_format($report->opening_cash, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-slate-700 font-medium">
                            Rp {{ number_format($report->total_deposit, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-slate-700 font-medium">
                            Rp {{ number_format($report->total_withdrawal, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-900">
                            Rp {{ number_format($report->closing_cash, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($report->status === 'approved')
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Disetujui</span>
                            @elseif($report->status === 'rejected')
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-red-50 text-red-700">Ditolak</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-amber-50 text-amber-700">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <a href="{{ route('supervisor.reports.show', $report) }}"
                                   title="Lihat Detail"
                                   class="p-1.5 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-md transition"
                                   aria-label="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                @if($report->status === 'draft')
                                    <form action="{{ route('supervisor.reports.approve', $report) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan harian ini?')">
                                        @csrf
                                        <button type="submit"
                                                title="Setujui Laporan"
                                                class="p-1.5 text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 rounded-md transition"
                                                aria-label="Setujui">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('supervisor.reports.reject', $report) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menolak laporan harian ini?')">
                                        @csrf
                                        <button type="submit"
                                                title="Tolak Laporan"
                                                class="p-1.5 text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-md transition"
                                                aria-label="Tolak">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-sm">
                            Tidak ada data laporan harian yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($reports->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection

