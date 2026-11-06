@extends('layouts.admin')

@section('title', 'Kelola Pengguna Internal')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengguna Internal</h1>
            <p class="text-sm text-slate-500">Kelola akun staf internal (Administrator, Teller, Supervisor).</p>
        </div>
        <button onclick="document.getElementById('modalAddUser').classList.remove('hidden')"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md shadow-sm transition">
            + Tambah Pengguna
        </button>
    </div>

    @if(request('search'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-blue-900">
            <span>Filter pencarian: <strong>"{{ request('search') }}"</strong> (Ditemukan {{ $users->count() }} data)</span>
            <a href="{{ route('admin.users') }}" class="font-semibold text-blue-700 hover:underline">Reset Filter</a>
        </div>
    @endif

    <!-- Users Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Nama</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->username }}</td>
                            <td class="px-6 py-4 capitalize">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-700">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->status === 'active')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="editUser({{ json_encode($user) }})"
                                        class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition whitespace-nowrap">
                                        Edit
                                    </button>

                                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline-flex" onsubmit="return confirm('Apakah Anda yakin ingin mereset password akun pengguna {{ $user->name }}? Password baru acak akan dibuat.')">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded transition whitespace-nowrap"
                                            title="Reset password pengguna ke password acak sementara">
                                            Reset PW
                                        </button>
                                    </form>
                                    
                                    @if($user->id === auth()->id())
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium text-slate-400 bg-slate-50 border border-slate-200/60 rounded cursor-not-allowed select-none" title="Anda tidak dapat menonaktifkan akun sendiri">
                                            Akun Sendiri
                                        </span>
                                    @elseif($user->status === 'active')
                                        <button type="button"
                                            onclick="confirmToggleStatus('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}', 'inactive')"
                                            class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded hover:bg-slate-200 transition whitespace-nowrap">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="button"
                                            onclick="confirmToggleStatus('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}', 'active')"
                                            class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium text-emerald-600 hover:text-emerald-800 bg-emerald-50 rounded hover:bg-emerald-100 transition whitespace-nowrap">
                                            Aktifkan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-slate-400">Belum ada pengguna internal terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Status Pengguna -->
    <div id="modalConfirmStatus" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="confirm_modal_title" class="text-lg font-bold text-slate-900">Konfirmasi Status Pengguna</h3>
                <button onclick="closeConfirmModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <p id="confirm_modal_message" class="text-sm text-slate-600 mb-6 leading-relaxed"></p>

            <form id="formConfirmStatus" method="POST" class="flex justify-end gap-2">
                @csrf
                @method('PATCH')
                <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md transition">
                    Batal
                </button>
                <button type="submit" id="confirm_modal_btn" class="px-4 py-2 font-medium text-sm rounded-md transition">
                    Konfirmasi
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Add User -->
    <div id="modalAddUser" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900">Tambah Pengguna Internal</h3>
                <button onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Role</label>
                    <select name="role" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="teller">Teller</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="modalEditUser" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-900">Edit Pengguna Internal</h3>
                <button onclick="document.getElementById('modalEditUser').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form id="formEditUser" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Role</label>
                    <select id="edit_role" name="role" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="teller">Teller</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="administrator">Administrator</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Status</label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                    <p id="edit_status_notice" class="hidden text-[11px] text-amber-600 mt-1 font-medium">Akun Anda yang sedang aktif tidak dapat dinonaktifkan.</p>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalEditUser').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let initialUserStatus = '';

        function editUser(user) {
            document.getElementById('formEditUser').action = '/admin/users/' + user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            initialUserStatus = user.status;

            if (user.id === {{ auth()->id() ?? 'null' }}) {
                document.getElementById('edit_status').querySelector('option[value="inactive"]').disabled = true;
                document.getElementById('edit_status_notice').classList.remove('hidden');
            } else {
                document.getElementById('edit_status').querySelector('option[value="inactive"]').disabled = false;
                document.getElementById('edit_status_notice').classList.add('hidden');
            }

            document.getElementById('modalEditUser').classList.remove('hidden');
        }

        document.getElementById('formEditUser').addEventListener('submit', function(e) {
            const status = document.getElementById('edit_status').value;
            if (status === 'inactive' && initialUserStatus === 'active') {
                if (!confirm('Apakah Anda yakin ingin mengubah status pengguna ini menjadi Nonaktif?')) {
                    e.preventDefault();
                }
            }
        });

        function confirmToggleStatus(userId, userName, role, targetAction) {
            const form = document.getElementById('formConfirmStatus');
            form.action = '/admin/users/' + userId + '/status';

            const title = document.getElementById('confirm_modal_title');
            const msg = document.getElementById('confirm_modal_message');
            const btn = document.getElementById('confirm_modal_btn');

            if (targetAction === 'inactive') {
                title.innerText = 'Nonaktifkan Pengguna';
                msg.innerHTML = 'Apakah Anda yakin ingin menonaktifkan pengguna <strong>' + userName + '</strong> (Role: <span class="capitalize">' + role + '</span>)?<br><br><span class="text-xs text-slate-500">Catatan: Pengguna yang dinonaktifkan tidak akan dapat masuk ke dalam sistem.</span>';
                btn.innerText = 'Ya, Nonaktifkan';
                btn.className = 'px-4 py-2 font-medium text-sm rounded-md text-white bg-red-600 hover:bg-red-700 transition shadow-sm';
            } else {
                title.innerText = 'Aktifkan Pengguna';
                msg.innerHTML = 'Apakah Anda yakin ingin mengaktifkan kembali pengguna <strong>' + userName + '</strong> (Role: <span class="capitalize">' + role + '</span>)?<br><br><span class="text-xs text-slate-500">Catatan: Pengguna akan dapat masuk kembali ke dalam sistem sesuai hak aksesnya.</span>';
                btn.innerText = 'Ya, Aktifkan';
                btn.className = 'px-4 py-2 font-medium text-sm rounded-md text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm';
            }

            document.getElementById('modalConfirmStatus').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('modalConfirmStatus').classList.add('hidden');
        }

        function copyCred(elementId, btn) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerText;
                btn.innerText = 'Tersalin! ✓';
                btn.classList.remove('bg-blue-50', 'text-blue-600', 'hover:bg-blue-100', 'bg-amber-100', 'text-amber-800', 'hover:bg-amber-200');
                btn.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                    if (elementId.includes('password')) {
                        btn.classList.add('bg-amber-100', 'text-amber-800', 'hover:bg-amber-200');
                    } else {
                        btn.classList.add('bg-blue-50', 'text-blue-600', 'hover:bg-blue-100');
                    }
                }, 1500);
            });
        }
    </script>

    <!-- Modal Credentials Reset Password Pengguna Internal -->
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
                            <p class="text-xs text-slate-500 mt-0.5">Password baru pengguna internal</p>
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
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nama Staf</div>
                        <div class="text-base font-bold text-slate-900 mt-0.5">{{ session('reset_password_credentials')['name'] }}</div>
                        <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mt-2.5">Role</div>
                        <div class="text-sm font-semibold text-slate-700 mt-0.5 capitalize">{{ session('reset_password_credentials')['role'] }}</div>
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
                                <span class="text-[10px] font-semibold text-amber-800 uppercase tracking-wider block">Password Baru</span>
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
                        <span>Berikan password baru ini kepada staf terkait. Demi keamanan, password ini hanya ditampilkan satu kali pada dialog ini.</span>
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
