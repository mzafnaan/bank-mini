@extends('layouts.admin')

@section('title', 'Kelola Data Nasabah')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Data Nasabah</h1>
            <p class="text-sm text-slate-500">Proses pendaftaran nasabah baru otomatis membuat Rekening Bank dan Akun Login Nasabah.</p>
        </div>
        <button onclick="document.getElementById('modalAddCustomer').classList.remove('hidden')"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition">
            + Tambah Nasabah
        </button>
    </div>

    @if(request('search'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-blue-900">
            <span>Filter pencarian: <strong>"{{ request('search') }}"</strong> (Ditemukan {{ $customers->count() }} data)</span>
            <a href="{{ route('admin.customers') }}" class="font-semibold text-blue-700 hover:underline">Reset Filter</a>
        </div>
    @endif

    <!-- Customers Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">NIS</th>
                        <th class="px-6 py-3">Nama Nasabah</th>
                        <th class="px-6 py-3">Kelas & Rombel</th>
                        <th class="px-6 py-3">Tahun Ajaran</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Telepon</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Nomor Rekening</th>
                        <th class="px-6 py-3 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-700 font-semibold">{{ $customer->nis }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $customer->name }}</td>
                            <td class="px-6 py-4 text-slate-700 font-medium">{{ $customer->full_class }}</td>
                            <td class="px-6 py-4 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $customer->academicYear?->name ?? 'Belum Diatur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($customer->status === 'active' || $customer->status === 'aktif')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">
                                        Aktif
                                    </span>
                                @elseif($customer->status === 'graduated')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-purple-50 text-purple-700">
                                        Lulus
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                @if($customer->pending_email)
                                    {{ $customer->pending_email }}
                                    <span class="block text-xs text-amber-600">Menunggu verifikasi</span>
                                @else
                                    {{ $customer->email ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($customer->bankAccount)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs rounded-md bg-blue-50 text-blue-700">
                                        {{ $customer->bankAccount->account_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="editCustomer({{ json_encode($customer) }})"
                                        class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition whitespace-nowrap">
                                        Edit
                                    </button>
                                    @if($customer->customerAccount)
                                        <form action="{{ route('admin.customers.reset-password', $customer) }}" method="POST" class="inline-flex" onsubmit="return confirm('Apakah Anda yakin ingin mereset password akun {{ $customer->name }}? Password acak sementara akan dibuat dan nasabah wajib mengubahnya saat login.')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded transition whitespace-nowrap"
                                                title="Reset password nasabah ke password sementara acak">
                                                Reset PW
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-6 text-center text-slate-400">Belum ada data nasabah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Add Customer -->
    <div id="modalAddCustomer" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6 my-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900">Tambah Nasabah Baru</h3>
                <button onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
            </div>

            <form action="{{ route('admin.customers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">NIS</label>
                    <input type="text" name="nis" required placeholder="Contoh: 2026001" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama siswa" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tingkat Kelas *</label>
                        <select id="add_customer_class" name="class" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Rombel *</label>
                        <input type="text" id="add_customer_class_group" name="class_group" required placeholder="Contoh: RPL 1" 
                            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="">Otomatis (Tahun Ajaran Aktif)</option>
                        @foreach($academicYears ?? [] as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Jurusan</label>
                    <select name="major" id="add_customer_major" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="">Auto Deteksi dari Rombel</option>
                        <option value="RPL">RPL - Rekayasa Perangkat Lunak</option>
                        <option value="TKJ">TKJ - Teknik Komputer Jaringan</option>
                        <option value="DKV">DKV - Desain Komunikasi Visual</option>
                        <option value="AK">AK - Akuntansi</option>
                        <option value="MP">MP - Manajemen Perkantoran</option>
                        <option value="BD">BD - Bisnis Digital</option>
                        <option value="BR">BR - Bisnis Retail</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">No. Telepon</label>
                    <input type="tel" name="phone" placeholder="08xxxxxxxxxx" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="15"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Email</label>
                    <input type="email" name="email" placeholder="siswa@sekolah.sch.id" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Setoran Awal (Minimal Rp 10.000) *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-sm font-semibold text-slate-500">Rp</span>
                        <input type="number" name="initial_deposit" id="add_customer_deposit" required min="10000" step="1000"
                            placeholder="Contoh: 10000" value="{{ old('initial_deposit') }}"
                            class="w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Sesuai aturan saldo minimum, setoran awal minimal Rp 10.000 untuk dapat membuka rekening.</p>
                </div>
                
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md transition">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Customer -->
    <div id="modalEditCustomer" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6 my-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900">Edit Data Nasabah</h3>
                <button onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
            </div>
            <form id="formEditCustomer" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">NIS</label>
                    <input type="text" id="edit_nis" name="nis" required 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_name" name="name" required 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tingkat Kelas *</label>
                        <select id="edit_class" name="class" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Rombel *</label>
                        <input type="text" id="edit_class_group" name="class_group" required placeholder="Contoh: RPL 1" 
                            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Status Nasabah *</label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="graduated">Lulus</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                    <select id="edit_academic_year_id" name="academic_year_id" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears ?? [] as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">No. Telepon (Opsional, Hanya Angka)</label>
                    <input type="tel" id="edit_phone" name="phone" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="15"
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Email</label>
                    <input type="email" id="edit_email" name="email" placeholder="siswa@sekolah.sch.id" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md transition">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md transition">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCustomer(customer) {
            document.getElementById('formEditCustomer').action = '/admin/customers/' + customer.id;
            document.getElementById('edit_nis').value = customer.nis;
            document.getElementById('edit_name').value = customer.name;
            document.getElementById('edit_class').value = customer.class || 'X';
            document.getElementById('edit_class_group').value = customer.class_group || '';
            document.getElementById('edit_status').value = customer.status || 'active';
            document.getElementById('edit_academic_year_id').value = customer.academic_year_id || '';
            document.getElementById('edit_phone').value = customer.phone || '';
            document.getElementById('edit_email').value = customer.pending_email || customer.email || '';
            document.getElementById('modalEditCustomer').classList.remove('hidden');
        }

        function copyCred(elementId, btn) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerText;
                btn.innerText = 'Tersalin! ✓';
                btn.classList.remove('bg-blue-50', 'text-blue-600', 'hover:bg-blue-100');
                btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                    btn.classList.add('bg-blue-50', 'text-blue-600', 'hover:bg-blue-100');
                }, 1500);
            });
        }

        // Auto-detect jurusan from class group input
        document.getElementById('add_customer_class_group')?.addEventListener('input', function() {
            const val = this.value;
            const match = val.match(/\b(AK|BR|BD|MP|DKV|RPL|TKJ)\b/i);
            const select = document.getElementById('add_customer_major');
            if (match && select) {
                select.value = match[1].toUpperCase();
            }
        });
    </script>
        });
    </script>

    <!-- Modal Credentials Nasabah Baru -->
    @if(session('new_customer_credentials'))
        <div id="modalCustomerCredentials" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full overflow-hidden my-8">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-900 leading-tight">Nasabah Berhasil Didaftarkan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Informasi akun login & PIN transaksi nasabah</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('modalCustomerCredentials').remove()" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-100 rounded-lg transition leading-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nama Nasabah</div>
                        <div class="text-base font-bold text-slate-900 mt-0.5">{{ session('new_customer_credentials')['name'] }}</div>
                        <div class="grid grid-cols-2 gap-3 mt-3">
                            <div>
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nomor Rekening</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-mono">
                                        {{ session('new_customer_credentials')['account_number'] }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Setoran Awal</div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">
                                        Rp {{ number_format(session('new_customer_credentials')['balance'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kredensial Akses Nasabah</h4>
                        
                        <!-- Username -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Username (NIS)</span>
                                <span class="text-sm font-bold text-slate-900 mt-0.5 block font-mono" id="cred-username">{{ session('new_customer_credentials')['username'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('cred-username', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition shadow-sm">
                                Salin
                            </button>
                        </div>

                        <!-- Password -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Password Default</span>
                                <span class="text-sm font-bold text-slate-900 mt-0.5 block font-mono" id="cred-password">{{ session('new_customer_credentials')['password'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('cred-password', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition shadow-sm">
                                Salin
                            </button>
                        </div>

                        <!-- PIN -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">PIN Transaksi (6 Digit)</span>
                                <span class="text-sm font-bold text-slate-900 tracking-widest mt-0.5 block font-mono" id="cred-pin">{{ session('new_customer_credentials')['pin'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('cred-pin', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition shadow-sm">
                                Salin
                            </button>
                        </div>
                    </div>

                    <div class="p-3 bg-blue-50/80 border border-blue-100 text-blue-800 text-xs rounded-xl flex items-start gap-2.5 leading-relaxed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Berikan kredensial ini kepada nasabah. Nasabah dapat mengubah Password & PIN setelah login pertama kali.</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50/80 px-6 py-3.5 border-t border-slate-100 flex justify-end">
                    <button type="button" onclick="document.getElementById('modalCustomerCredentials').remove()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition">
                        Tutup & Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Credentials Reset Password -->
    @if(session('reset_password_credentials'))
        <div id="modalResetPasswordCredentials" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full overflow-hidden my-8">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-50 text-amber-600 border border-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-slate-900 leading-tight">Password Berhasil Direset</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Password sementara akun nasabah</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('modalResetPasswordCredentials').remove()" class="text-slate-400 hover:text-slate-600 p-1.5 hover:bg-slate-100 rounded-lg transition leading-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nama Nasabah</div>
                        <div class="text-base font-bold text-slate-900 mt-0.5">{{ session('reset_password_credentials')['name'] }}</div>
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mt-2.5">NIS</div>
                        <div class="text-sm font-semibold text-slate-700 mt-0.5 font-mono">{{ session('reset_password_credentials')['nis'] }}</div>
                    </div>

                    <div class="space-y-2.5">
                        <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kredensial Login Baru</h4>
                        
                        <!-- Username -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                            <div>
                                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Username</span>
                                <span class="text-sm font-bold text-slate-900 mt-0.5 block font-mono" id="reset-cred-username">{{ session('reset_password_credentials')['username'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('reset-cred-username', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition shadow-sm">
                                Salin
                            </button>
                        </div>

                        <!-- Temporary Password -->
                        <div class="flex items-center justify-between p-3 bg-amber-50/60 rounded-xl border border-amber-200">
                            <div>
                                <span class="text-[10px] font-semibold text-amber-800 uppercase tracking-wider block">Password Sementara</span>
                                <span class="text-sm font-bold text-amber-950 mt-0.5 block font-mono" id="reset-cred-password">{{ session('reset_password_credentials')['password'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('reset-cred-password', this)" class="px-3 py-1.5 text-xs font-medium text-amber-800 hover:text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-lg transition shadow-sm">
                                Salin
                            </button>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50/80 border border-amber-200 text-amber-900 text-xs rounded-xl flex items-start gap-2.5 leading-relaxed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Password sementara ini hanya ditampilkan satu kali demi keamanan. Nasabah wajib mengganti password saat login pertama kali. Semua sesi dan token mobile nasabah telah dinonaktifkan.</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50/80 px-6 py-3.5 border-t border-slate-100 flex justify-end">
                    <button type="button" onclick="document.getElementById('modalResetPasswordCredentials').remove()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-sm rounded-md shadow-sm transition">
                        Tutup & Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection
