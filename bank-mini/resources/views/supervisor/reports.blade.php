@extends('layouts.supervisor')

@section('title', 'Laporan Harian Teller')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laporan Harian Teller</h1>
            <p class="text-sm text-slate-500">Daftar verifikasi dan persetujuan (approval) laporan penutupan kas harian teller.</p>
        </div>
    </div>

    <!-- Filter & Search Bar with Status Dropdown -->
    <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form action="{{ route('supervisor.reports') }}" method="GET" class="w-full flex flex-col sm:flex-row sm:items-center gap-3">
            
            <div class="flex items-center gap-2">
                <label for="status" class="text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">Filter Status:</label>
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
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Nama Teller</th>
                        <th class="px-6 py-3.5 text-right">Kas Awal</th>
                        <th class="px-6 py-3.5 text-right">Total Setoran</th>
                        <th class="px-6 py-3.5 text-right">Total Penarikan</th>
                        <th class="px-6 py-3.5 text-right">Kas Akhir</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Petugas Verifikasi</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                                <a href="{{ route('supervisor.reports.show', $report) }}" class="text-blue-600 hover:underline">
                                    {{ $report->report_date ? $report->report_date->format('d/m/Y') : '-' }}
                                </a>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $report->teller?->name ?? 'N/A' }}
                                <span class="block text-xs font-normal text-slate-400">@ {{ $report->teller?->username }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-600">
                                Rp {{ number_format($report->opening_cash, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-700 font-medium">
                                Rp {{ number_format($report->total_deposit, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-700 font-medium">
                                Rp {{ number_format($report->total_withdrawal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-xs font-bold text-slate-900">
                                Rp {{ number_format($report->closing_cash, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($report->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">
                                        Disetujui
                                    </span>
                                @elseif($report->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md bg-red-50 text-red-700">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md bg-amber-50 text-amber-700">
                                        Menunggu Approval
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600">
                                @if($report->supervisor)
                                    <span class="font-medium text-slate-900">{{ $report->supervisor->name }}</span>
                                    <span class="block text-slate-400 text-[11px]">{{ $report->approved_at ? $report->approved_at->format('d/m/Y H:i') : '' }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap space-x-1.5">
                                <a href="{{ route('supervisor.reports.show', $report) }}"
                                   class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition inline-block">
                                    Detail
                                </a>

                                @if($report->status === 'draft')
                                    <form action="{{ route('supervisor.reports.approve', $report) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition shadow-sm">
                                            Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('supervisor.reports.reject', $report) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menolak laporan harian ini?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition shadow-sm">
                                            Tolak
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada data laporan harian yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
@endsection
