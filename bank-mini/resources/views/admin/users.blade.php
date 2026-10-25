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
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->username }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $user->email ?? '-' }}</td>
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
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editUser({{ json_encode($user) }})"
                                    class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition">
                                    Edit
                                </button>
                                
                                @if($user->status === 'active')
                                    <button type="button"
                                        onclick="confirmToggleStatus('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}', 'inactive')"
                                        class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded hover:bg-slate-200 transition">
                                        Nonaktifkan
                                    </button>
                                @else
                                    <button type="button"
                                        onclick="confirmToggleStatus('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}', 'active')"
                                        class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition">
                                        Aktifkan
                                    </button>
                                @endif
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
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Email (untuk Reset Password)</label>
                    <input type="email" name="email" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm" placeholder="staf@sekolah.sch.id">
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
                    <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Email (untuk Reset Password)</label>
                    <input type="email" id="edit_email" name="email" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm" placeholder="staf@sekolah.sch.id">
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
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            initialUserStatus = user.status;
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
    </script>
@endsection
