<!-- Modal Ubah Password Staf (Teller / Supervisor / Admin) -->
<div id="modalStaffChangePassword" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-6 my-8">
        <div class="flex justify-between items-center pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Ubah Password Akun</h3>
                    <p class="text-xs text-slate-500">Perbarui kata sandi akun {{ auth()->user()->name ?? 'staf' }}.</p>
                </div>
            </div>
            <button type="button" onclick="closeStaffChangePasswordModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none p-1">&times;</button>
        </div>

        <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4 pt-4">
            @csrf

            <div>
                <label for="staff_current_password" class="block text-xs font-semibold text-slate-600 uppercase mb-1">
                    Password Saat Ini <span class="text-red-500">*</span>
                </label>
                <input type="password" id="staff_current_password" name="current_password" required
                    placeholder="Masukkan password saat ini atau password acak dari Admin..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
            </div>

            <div>
                <label for="staff_new_password" class="block text-xs font-semibold text-slate-600 uppercase mb-1">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <input type="password" id="staff_new_password" name="password" required minlength="6"
                    placeholder="Minimal 6 karakter..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
            </div>

            <div>
                <label for="staff_password_confirmation" class="block text-xs font-semibold text-slate-600 uppercase mb-1">
                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                </label>
                <input type="password" id="staff_password_confirmation" name="password_confirmation" required minlength="6"
                    placeholder="Ketik ulang password baru..."
                    class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeStaffChangePasswordModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-md transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStaffChangePasswordModal() {
        const modal = document.getElementById('modalStaffChangePassword');
        if (modal) {
            modal.classList.remove('hidden');
            const input = document.getElementById('staff_current_password');
            if (input) {
                input.value = '';
                input.focus();
            }
            const p1 = document.getElementById('staff_new_password');
            if (p1) p1.value = '';
            const p2 = document.getElementById('staff_password_confirmation');
            if (p2) p2.value = '';
        }
    }

    function closeStaffChangePasswordModal() {
        const modal = document.getElementById('modalStaffChangePassword');
        if (modal) modal.classList.add('hidden');
    }
</script>

