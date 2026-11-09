@extends('layouts.admin')

@section('title', 'Manajemen Tahun Ajaran')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Tahun Ajaran</h1>
            <p class="text-sm text-slate-500">Kelola periode tahun ajaran aktif dan riwayat tahun ajaran Bank Mini.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.class-promotions.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md shadow-sm transition">
                Proses Kenaikan Kelas
            </a>
            <button onclick="document.getElementById('modalAddAcademicYear').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition">
                + Tambah Tahun Ajaran
            </button>
        </div>
    </div>

    <!-- Academic Year Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <span class="text-xs uppercase tracking-wider font-semibold text-slate-500">Tahun Ajaran Aktif</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $activeYear?->name ?? 'Belum Ada' }}
            </div>
            <p class="text-xs text-slate-400 mt-1">
                {{ $activeYear ? $activeYear->formatted_period : 'Tidak ada tahun ajaran yang aktif' }}
            </p>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <span class="text-xs uppercase tracking-wider font-semibold text-slate-500">Siswa Aktif Saat Ini</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $activeYear ? $activeYear->customers()->where('status', 'active')->count() : 0 }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Terdaftar pada tahun ajaran aktif</p>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <span class="text-xs uppercase tracking-wider font-semibold text-slate-500">Total Periode Tercatat</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $academicYears->total() }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Riwayat seluruh tahun ajaran</p>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm mb-4">
        <form method="GET" action="{{ route('admin.academic-years.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama tahun ajaran (contoh: 2026/2027)..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-sm rounded-md transition">
                Cari
            </button>
            @if(!empty($search))
                <a href="{{ route('admin.academic-years.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium text-sm rounded-md transition flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Academic Years Table -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama Tahun Ajaran</th>
                        <th class="px-6 py-3.5">Tanggal Mulai</th>
                        <th class="px-6 py-3.5">Tanggal Selesai</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Nasabah</th>
                        <th class="px-6 py-3.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($academicYears as $year)
                        <tr class="hover:bg-slate-50 transition {{ $year->is_active ? 'bg-slate-50/70' : '' }}">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $year->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $year->start_date ? $year->start_date->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $year->end_date ? $year->end_date->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($year->is_active)
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-600">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-medium text-slate-700">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $year->customers_count ?? $year->customers()->count() }} Siswa
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$year->is_active)
                                        <form action="{{ route('admin.academic-years.activate', $year) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan tahun ajaran {{ $year->name }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded transition">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tahun Ajaran Utama</span>
                                    @endif

                                    <a href="{{ route('admin.class-promotions.index', ['source_academic_year_id' => $year->id]) }}"
                                        class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded transition">
                                        Kenaikan Kelas &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                Belum ada data tahun ajaran. Silakan klik tombol <strong>+ Tambah Tahun Ajaran</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($academicYears->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $academicYears->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Add Academic Year -->
    <div id="modalAddAcademicYear" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6 my-8">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Tambah Tahun Ajaran Baru</h3>
                <button type="button" onclick="document.getElementById('modalAddAcademicYear').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-2xl font-bold leading-none">&times;</button>
            </div>

            <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Tahun Ajaran *</label>
                    <input type="text" name="name" required placeholder="Contoh: 2026/2027" value="{{ old('name') }}"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                    <p class="text-[11px] text-slate-400 mt-1">Format: YYYY/YYYY (contoh: 2026/2027).</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required value="{{ old('start_date') }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required value="{{ old('end_date') }}"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-md border border-slate-200">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                            class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <div class="text-xs">
                            <span class="font-semibold text-slate-800">Jadikan sebagai Tahun Ajaran Aktif</span>
                            <p class="text-slate-500 mt-0.5">Tahun ajaran aktif sebelumnya otomatis dinonaktifkan.</p>
                        </div>
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalAddAcademicYear').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md transition shadow-sm">
                        Simpan Tahun Ajaran
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
