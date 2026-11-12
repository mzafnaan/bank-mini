@extends('layouts.admin')

@section('title', 'Kenaikan Kelas & Kelulusan')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Kenaikan Kelas & Kelulusan</h1>
            <p class="text-sm text-slate-500">Kelola kenaikan tingkat kelas dan kelulusan siswa per tahun ajaran.</p>
        </div>
        <div>
            <a href="{{ route('admin.academic-years.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md shadow-sm transition inline-block">
                Kelola Tahun Ajaran
            </a>
        </div>
    </div>

    <!-- Filter & Configuration Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('admin.class-promotions.index') }}" id="filterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Source Academic Year -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Ajaran Asal</label>
                    <select name="source_academic_year_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 bg-white">
                        <option value="">Semua Siswa Aktif</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (string)$sourceYearId === (string)$year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Academic Year -->
                <div>
                    <label class="block text-xs font-semibold text-slate-900 mb-1">Tahun Ajaran Tujuan *</label>
                    <select name="target_academic_year_id" id="filterTargetYear" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-blue-500 rounded-md text-sm text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 bg-blue-50/20">
                        <option value="">-- Pilih Tahun Ajaran Tujuan --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ (string)$targetYearId === (string)$year->id ? 'selected' : '' }}>
                                {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Level Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tingkat Kelas</label>
                    <select name="class_level" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 bg-white">
                        <option value="">Semua Tingkat</option>
                        <option value="X" {{ $classLevel === 'X' ? 'selected' : '' }}>Kelas X</option>
                        <option value="XI" {{ $classLevel === 'XI' ? 'selected' : '' }}>Kelas XI</option>
                        <option value="XII" {{ $classLevel === 'XII' ? 'selected' : '' }}>Kelas XII</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Siswa</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="NIS, nama, atau rombel..."
                            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-sm rounded-md transition">
                            Cari
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Compact Summary Strip -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center sm:text-left">
            <div class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-2 sm:pb-0 sm:pr-3">
                <span class="text-xs text-slate-500 font-medium">Total Kandidat</span>
                <div class="text-xl font-bold text-slate-900 mt-0.5" id="statTotalCandidates">{{ $candidates->total() }}</div>
            </div>
            <div class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-2 sm:pb-0 sm:pr-3">
                <span class="text-xs text-emerald-700 font-semibold">Naik Kelas</span>
                <div class="text-xl font-bold text-emerald-700 mt-0.5" id="statPromoteCount">0</div>
            </div>
            <div class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-2 sm:pb-0 sm:pr-3">
                <span class="text-xs text-amber-700 font-semibold">Tinggal Kelas</span>
                <div class="text-xl font-bold text-amber-700 mt-0.5" id="statStayCount">0</div>
            </div>
            <div>
                <span class="text-xs text-purple-700 font-semibold">Lulus</span>
                <div class="text-xl font-bold text-purple-700 mt-0.5" id="statGraduateCount">0</div>
            </div>
        </div>
    </div>

    @if(!$targetYearId)
        <div class="bg-white rounded-lg border border-slate-200 p-8 text-center text-slate-500 mb-6">
            <p class="text-base font-semibold text-slate-800">Silakan pilih Tahun Ajaran Tujuan</p>
            <p class="text-xs text-slate-400 mt-1">Pilih tahun ajaran tujuan di atas untuk memulai penentuan kenaikan kelas.</p>
        </div>
    @elseif($candidates->isEmpty())
        <div class="bg-white rounded-lg border border-slate-200 p-8 text-center text-slate-500 mb-6">
            <p class="text-base font-semibold text-slate-800">Tidak ada data siswa ditemukan</p>
            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan pilihan filter atau kata kunci pencarian Anda.</p>
        </div>
    @else
        <!-- Promotion Form -->
        <form action="{{ route('admin.class-promotions.process') }}" method="POST" id="promotionForm">
            @csrf
            <input type="hidden" name="source_academic_year_id" value="{{ $sourceYearId }}">
            <input type="hidden" name="target_academic_year_id" value="{{ $targetYearId }}">

            <!-- Clean Action Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3 bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-semibold text-slate-500 mr-1">Atur Cepat:</span>
                    <button type="button" onclick="setAllDecisions('recommend')"
                        class="px-2.5 py-1 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition">
                        Rekomendasi Sistem
                    </button>
                    <button type="button" onclick="setAllDecisions('promote')"
                        class="px-2.5 py-1 text-xs font-medium text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded transition border border-emerald-200">
                        Semua Naik
                    </button>
                    <button type="button" onclick="setAllDecisions('stay')"
                        class="px-2.5 py-1 text-xs font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 rounded transition border border-amber-200">
                        Semua Tinggal
                    </button>
                </div>

                <div>
                    <button type="button" onclick="openConfirmationModal()"
                        class="w-full sm:w-auto px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-md shadow-sm transition">
                        Simpan Kenaikan Kelas
                    </button>
                </div>
            </div>

            <!-- Promotion Table -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">NIS</th>
                                <th class="px-4 py-3">Nama Siswa</th>
                                <th class="px-4 py-3">Kelas Asal</th>
                                <th class="px-4 py-3">Keputusan</th>
                                <th class="px-4 py-3">Rombel Tujuan</th>
                                <th class="px-4 py-3">Hasil</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($candidates as $index => $customer)
                                @php
                                    $currentClass = $customer->class ?? 'X';
                                    $defaultDecision = ($currentClass === 'XII') ? 'graduate' : 'promote';
                                    $defaultTargetClass = match($currentClass) {
                                        'X' => 'XI',
                                        'XI' => 'XII',
                                        'XII' => 'Lulus',
                                        default => $currentClass
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50 transition student-row"
                                    data-customer-id="{{ $customer->id }}"
                                    data-current-class="{{ $currentClass }}"
                                    data-current-group="{{ $customer->class_group ?? '' }}"
                                    data-recommendation="{{ $defaultDecision }}">
                                    <td class="px-4 py-3 text-center text-xs text-slate-400">
                                        {{ $candidates->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-semibold text-xs">
                                        {{ $customer->nis }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">
                                        {{ $customer->name }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 text-xs">
                                        {{ $customer->full_class }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="decisions[{{ $customer->id }}][action]"
                                            class="decision-select px-2.5 py-1.5 border border-slate-300 rounded text-xs bg-white focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                                            onchange="updateRowPreview({{ $customer->id }})">
                                            @if($currentClass === 'XII')
                                                <option value="graduate" {{ $defaultDecision === 'graduate' ? 'selected' : '' }}>
                                                    Lulus Sekolah
                                                </option>
                                                <option value="stay">
                                                    Tinggal Kelas (Tetap XII)
                                                </option>
                                            @else
                                                <option value="promote" selected>
                                                    Naik Kelas (ke {{ $defaultTargetClass }})
                                                </option>
                                                <option value="stay">
                                                    Tinggal Kelas (Tetap {{ $currentClass }})
                                                </option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="decisions[{{ $customer->id }}][target_class_group]"
                                            id="group_{{ $customer->id }}"
                                            value="{{ $customer->class_group ?? '' }}"
                                            placeholder="Contoh: RPL 1"
                                            class="group-input px-2.5 py-1.5 border border-slate-300 rounded text-xs w-28 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                                            oninput="updateRowPreview({{ $customer->id }})">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div id="preview_{{ $customer->id }}" class="text-xs">
                                            <!-- Dynamically filled by JS -->
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($candidates->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            Menampilkan {{ $candidates->firstItem() }} - {{ $candidates->lastItem() }} dari {{ $candidates->total() }} siswa
                        </div>
                        <div>
                            {{ $candidates->appends(request()->query())->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </form>
    @endif

    <!-- Confirmation Modal Dialog -->
    <div id="modalConfirmPromotion" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-lg max-w-md w-full p-6 my-8">
            <h3 class="text-lg font-bold text-slate-900 pb-2 border-b border-slate-100">
                Konfirmasi Kenaikan Kelas
            </h3>

            <div class="py-4 space-y-3">
                <div class="p-3 bg-slate-50 rounded-md border border-slate-200 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Tahun Tujuan:</span>
                        <span class="font-bold text-slate-900">{{ $targetYear?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Siswa:</span>
                        <span class="font-bold text-slate-900" id="modalTotalStudents">0</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2 bg-emerald-50 border border-emerald-200 rounded">
                        <span class="text-emerald-700 block">Naik Kelas</span>
                        <span class="text-base font-bold text-emerald-800" id="modalPromoteCount">0</span>
                    </div>
                    <div class="p-2 bg-amber-50 border border-amber-200 rounded">
                        <span class="text-amber-700 block">Tinggal</span>
                        <span class="text-base font-bold text-amber-800" id="modalStayCount">0</span>
                    </div>
                    <div class="p-2 bg-purple-50 border border-purple-200 rounded">
                        <span class="text-purple-700 block">Lulus</span>
                        <span class="text-base font-bold text-purple-800" id="modalGraduateCount">0</span>
                    </div>
                </div>

                <p class="text-[11px] text-slate-500 leading-normal">
                    * Siswa yang dinyatakan lulus akan dinonaktifkan status akunnya. Rekening bank dan saldo tetap aman dan tidak dihapus.
                </p>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalConfirmPromotion').classList.add('hidden')"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded transition">
                    Batal
                </button>
                <button type="button" onclick="submitPromotionForm()"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs rounded transition shadow-sm">
                    Ya, Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <script>
        function updateRowPreview(customerId) {
            const row = document.querySelector(`.student-row[data-customer-id="${customerId}"]`);
            if (!row) return;

            const currentClass = row.dataset.currentClass;
            const select = row.querySelector('.decision-select');
            const groupInput = row.querySelector('.group-input');
            const previewEl = document.getElementById(`preview_${customerId}`);
            const decision = select.value;
            const group = groupInput.value.trim();

            if (decision === 'promote') {
                const targetClass = currentClass === 'X' ? 'XI' : (currentClass === 'XI' ? 'XII' : 'XII');
                const fullTarget = group ? `${targetClass} ${group}` : targetClass;
                previewEl.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">Naik ke ${fullTarget}</span>`;
                groupInput.disabled = false;
                groupInput.classList.remove('bg-slate-100', 'text-slate-400');
            } else if (decision === 'stay') {
                const fullTarget = group ? `${currentClass} ${group}` : currentClass;
                previewEl.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800">Tinggal di ${fullTarget}</span>`;
                groupInput.disabled = false;
                groupInput.classList.remove('bg-slate-100', 'text-slate-400');
            } else if (decision === 'graduate') {
                previewEl.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 text-purple-800">Lulus</span>`;
                groupInput.disabled = true;
                groupInput.classList.add('bg-slate-100', 'text-slate-400');
            }

            recalculateLiveStats();
        }

        function recalculateLiveStats() {
            let promote = 0;
            let stay = 0;
            let graduate = 0;

            document.querySelectorAll('.student-row').forEach(row => {
                const sel = row.querySelector('.decision-select');
                if (!sel) return;
                if (sel.value === 'promote') promote++;
                else if (sel.value === 'stay') stay++;
                else if (sel.value === 'graduate') graduate++;
            });

            document.getElementById('statPromoteCount').innerText = promote;
            document.getElementById('statStayCount').innerText = stay;
            document.getElementById('statGraduateCount').innerText = graduate;
        }

        function setAllDecisions(mode) {
            document.querySelectorAll('.student-row').forEach(row => {
                const currentClass = row.dataset.currentClass;
                const select = row.querySelector('.decision-select');
                const customerId = row.dataset.customerId;

                if (mode === 'recommend') {
                    select.value = row.dataset.recommendation;
                } else if (mode === 'promote') {
                    if (currentClass !== 'XII') {
                        select.value = 'promote';
                    }
                } else if (mode === 'stay') {
                    select.value = 'stay';
                }

                updateRowPreview(customerId);
            });
        }

        function openConfirmationModal() {
            const form = document.getElementById('promotionForm');
            if (!form) return;

            let promote = 0;
            let stay = 0;
            let graduate = 0;

            document.querySelectorAll('.student-row').forEach(row => {
                const sel = row.querySelector('.decision-select');
                if (!sel) return;
                if (sel.value === 'promote') promote++;
                else if (sel.value === 'stay') stay++;
                else if (sel.value === 'graduate') graduate++;
            });

            const total = promote + stay + graduate;

            document.getElementById('modalTotalStudents').innerText = `${total} Siswa`;
            document.getElementById('modalPromoteCount').innerText = promote;
            document.getElementById('modalStayCount').innerText = stay;
            document.getElementById('modalGraduateCount').innerText = graduate;

            document.getElementById('modalConfirmPromotion').classList.remove('hidden');
        }

        function submitPromotionForm() {
            const form = document.getElementById('promotionForm');
            if (form) {
                form.querySelectorAll('.group-input:disabled').forEach(input => {
                    input.disabled = false;
                });
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.student-row').forEach(row => {
                const customerId = row.dataset.customerId;
                updateRowPreview(customerId);
            });
        });
    </script>
@endsection
