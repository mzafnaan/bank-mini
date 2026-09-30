@extends('layouts.customer')

@section('title', 'Keamanan Akun')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Keamanan Akun & Kredensial</h1>
        <p class="text-sm text-slate-500">Kelola kata sandi login dan PIN otorisasi penarikan tunai Anda.</p>
    </div>

    @if($customerAccount->first_login)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-sm flex items-start gap-3 shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold block">Pemberitahuan Login Pertama</span>
                <span class="text-xs text-amber-800">
                    Akun Anda saat ini masih menggunakan password atau PIN default yang dibuat sistem saat pendaftaran awal. Demi keamanan data dan simpanan Anda, segera perbarui Password dan PIN transaksi Anda di bawah ini.
                </span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Form Ganti Password -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ubah Password Login</h2>
                        <p class="text-xs text-slate-500">Digunakan untuk login ke Portal Web dan Aplikasi Mobile.</p>
                    </div>
                </div>

                <form action="{{ route('customer.security.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            Password Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" required placeholder="Masukkan password saat ini"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('current_password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('new_password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required minlength="6" placeholder="Ulangi password baru"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-md shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>Perbarui Password</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700 block mb-0.5">Ketentuan Password:</span>
                <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                    <li>Minimal 6 karakter kombinasi huruf dan angka.</li>
                    <li>Jangan berikan password kepada siapapun.</li>
                </ul>
            </div>
        </div>

        <!-- Form Ganti PIN -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ubah PIN Transaksi</h2>
                        <p class="text-xs text-slate-500">Digunakan untuk otorisasi penarikan uang di loket Teller.</p>
                    </div>
                </div>

                <form action="{{ route('customer.security.pin') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            PIN Saat Ini (6 Digit Angka) <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_pin" name="current_pin" required maxlength="6" pattern="[0-9]{6}" placeholder="••••••" autocomplete="off"
                               class="w-full px-3.5 py-2 text-center text-base tracking-widest font-bold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('current_pin')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            PIN Baru (6 Digit Angka) <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="new_pin" name="new_pin" required maxlength="6" pattern="[0-9]{6}" placeholder="••••••" autocomplete="off"
                               class="w-full px-3.5 py-2 text-center text-base tracking-widest font-bold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('new_pin')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="new_pin_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            Konfirmasi PIN Baru (6 Digit Angka) <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="new_pin_confirmation" name="new_pin_confirmation" required maxlength="6" pattern="[0-9]{6}" placeholder="••••••" autocomplete="off"
                               class="w-full px-3.5 py-2 text-center text-base tracking-widest font-bold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-md shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>Perbarui PIN Transaksi</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500">
                <span class="font-semibold text-slate-700 block mb-0.5">Ketentuan PIN:</span>
                <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                    <li>Harus tepat 6 digit angka numerik (0-9).</li>
                    <li>Hindari angka mudah ditebak seperti tanggal lahir atau angka berurutan.</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- Security Information Guide -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Informasi Keamanan Akun Nasabah</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-600">
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <strong class="text-slate-800 block mb-1">Perbedaan Password & PIN</strong>
                <p class="leading-relaxed text-[11px]">
                    <strong>Password</strong> digunakan saat Anda masuk ke portal web atau aplikasi mobile nasabah. Sedangkan <strong>PIN</strong> adalah kode rahasia 6 digit yang wajib Anda masukkan untuk menyetujui transaksi penarikan saldo di loket Teller.
                </p>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <strong class="text-slate-800 block mb-1">Kerahasiaan Otorisasi (Sesuai Aturan Sistem)</strong>
                <p class="leading-relaxed text-[11px]">
                    Petugas Teller <em>tidak diperbolehkan</em> mengetahui atau meminta PIN Anda. Otorisasi penarikan tunai dilakukan langsung oleh nasabah secara mandiri melalui tombol otorisasi pada dashboard ini atau perangkat nasabah sendiri.
                </p>
            </div>
        </div>
    </div>
@endsection
