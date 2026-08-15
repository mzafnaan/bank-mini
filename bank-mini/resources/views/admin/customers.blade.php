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
                        <th class="px-6 py-3">Kelas</th>
                        <th class="px-6 py-3">Telepon</th>
                        <th class="px-6 py-3">Nomor Rekening</th>
                        <th class="px-6 py-3">Status Akun Login</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono text-slate-700 font-semibold">{{ $customer->nis }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $customer->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->class }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($customer->bankAccount)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-bold rounded-md bg-blue-50 text-blue-700">
                                        {{ $customer->bankAccount->account_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($customer->customerAccount)
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif ({{ $customer->customerAccount->username }})
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="editCustomer({{ json_encode($customer) }})"
                                    class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-slate-400">Belum ada data nasabah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Add Customer (Onboarding) -->
    <div id="modalAddCustomer" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-lg w-full p-6 my-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900">Onboarding Nasabah Baru</h3>
                <button onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            
            <div class="mb-5 p-3.5 bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-md leading-relaxed">
                Sistem akan otomatis membuat Rekening Bank (Saldo Rp 0) & Akun Login Nasabah secara otomatis dalam satu transaksi database.
            </div>

            <form action="{{ route('admin.customers.store') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Section 1 -->
                <div class="border-b border-slate-100 pb-4">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">1. IDENTITAS NASABAH</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">NIS <span class="text-red-500">*</span></label>
                            <input type="text" name="nis" required placeholder="Contoh: 2026001" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:border-blue-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Nama siswa" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:border-blue-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="class" required placeholder="Contoh: XII RPL 1" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:border-blue-600 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. Telepon</label>
                            <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:border-blue-600 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-2 text-xs text-slate-600">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">2. AKUN LOGIN NASABAH (OTOMATIS)</h4>
                    <ul class="space-y-1.5 list-disc list-inside text-slate-600">
                        <li><strong>Akun Otentikasi:</strong> Dibuat otomatis untuk akses <strong>Customer Web</strong> & <strong>Customer Mobile API</strong>.</li>
                        <li><strong>Username:</strong> Otomatis menggunakan NIS nasabah.</li>
                        <li><strong>Kredensial Awal:</strong> Diberikan secara aman (`first_login = true`).</li>
                        <li><strong>Rekening Bank:</strong> Otomatis dibuat dengan Saldo Rp 0 dan QR Code siap pakai.</li>
                    </ul>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalAddCustomer').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md transition">Simpan & Proses Onboarding</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Customer -->
    <div id="modalEditCustomer" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900">Edit Data Nasabah</h3>
                <button onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <form id="formEditCustomer" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">NIS</label>
                    <input type="text" id="edit_nis" name="nis" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Kelas</label>
                    <input type="text" id="edit_class" name="class" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">No. Telepon</label>
                    <input type="text" id="edit_phone" name="phone" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalEditCustomer').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editCustomer(customer) {
            document.getElementById('formEditCustomer').action = '/admin/customers/' + customer.id;
            document.getElementById('edit_nis').value = customer.nis;
            document.getElementById('edit_name').value = customer.name;
            document.getElementById('edit_class').value = customer.class;
            document.getElementById('edit_phone').value = customer.phone || '';
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
    </script>

    <!-- Modal Credentials Nasabah Baru -->
    @if(session('new_customer_credentials'))
        <div id="modalCustomerCredentials" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full overflow-hidden">
                <!-- Header -->
                <div class="bg-blue-600 px-6 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-white/20 rounded-md flex items-center justify-center text-white flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white leading-tight">Nasabah Berhasil Didaftarkan</h3>
                            <p class="text-xs text-blue-100 mt-0.5">Informasi akun login & PIN transaksi nasabah</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('modalCustomerCredentials').remove()" class="text-blue-200 hover:text-white text-2xl font-bold leading-none">&times;</button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 rounded-md p-4 border border-slate-200">
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama Nasabah</div>
                        <div class="text-base font-bold text-slate-900 mt-0.5">{{ session('new_customer_credentials')['name'] }}</div>
                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-3">Nomor Rekening</div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-bold rounded-md bg-blue-50 text-blue-700 border border-blue-200">
                                {{ session('new_customer_credentials')['account_number'] }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">KREDENSIAL AKSES NASABAH</h4>
                        
                        <!-- Username -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-md border border-slate-200">
                            <div>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Username (NIS)</span>
                                <span class="text-sm font-mono font-bold text-slate-900 mt-0.5 block" id="cred-username">{{ session('new_customer_credentials')['username'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('cred-username', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition shadow-sm">
                                Salin
                            </button>
                        </div>

                        <!-- Password -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-md border border-slate-200">
                            <div>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Password Default</span>
                                <span class="text-sm font-mono font-bold text-slate-900 mt-0.5 block" id="cred-password">{{ session('new_customer_credentials')['password'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('cred-password', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition shadow-sm">
                                Salin
                            </button>
                        </div>

                        <!-- PIN -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-md border border-slate-200">
                            <div>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">PIN Transaksi (6 Digit)</span>
                                <span class="text-sm font-mono font-bold text-slate-900 tracking-widest mt-0.5 block" id="cred-pin">{{ session('new_customer_credentials')['pin'] }}</span>
                            </div>
                            <button type="button" onclick="copyCred('cred-pin', this)" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition shadow-sm">
                                Salin
                            </button>
                        </div>
                    </div>

                    <div class="p-3.5 bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-md flex items-start gap-2.5 leading-relaxed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Berikan kredensial ini kepada nasabah. Nasabah dapat mengubah Password & PIN setelah login pertama kali.</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-200 flex justify-end">
                    <button type="button" onclick="document.getElementById('modalCustomerCredentials').remove()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition">
                        Tutup & Selesai
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection
