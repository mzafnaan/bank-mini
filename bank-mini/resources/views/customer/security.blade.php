@extends('layouts.customer')

@section('title', 'Keamanan Akun & Email')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Pengaturan Akun & Keamanan</h1>
        <p class="text-sm text-slate-500">Kelola email pemulihan untuk reset password, kata sandi login, dan PIN transaksi Anda.</p>
    </div>

    <!-- Recovery Email Section -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Email Pemulihan Akun</h2>
                    <p class="text-xs text-slate-500">Menerima tautan reset password jika Anda lupa kata sandi.</p>
                </div>
            </div>

            @if($customer->pending_email)
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                    Menunggu Verifikasi
                </span>
            @elseif($customer->email && $customer->email_verified_at)
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Terverifikasi
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-slate-100 text-slate-600">
                    Belum Diatur
                </span>
            @endif
        </div>

        <div class="p-6">
            @if($customer->pending_email)
                <!-- Tampilan Simpel Saat Verifikasi Tertunda -->
                <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="text-xs font-semibold text-amber-800 uppercase tracking-wider">Verifikasi Email Baru</span>
                            </div>
                            <div class="text-base font-bold text-slate-900 font-mono mt-1">
                                {{ $customer->pending_email }}
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Tautan verifikasi telah dikirim. Buka kotak masuk atau spam email Anda untuk mengonfirmasi.
                                @if($customer->email)
                                    <span class="block text-slate-400 mt-0.5">Email aktif saat ini ({{ $customer->email }}) tetap berlaku hingga email baru diverifikasi.</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <form action="{{ route('customer.security.email.resend') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                                    Kirim Ulang
                                </button>
                            </form>
                            <form action="{{ route('customer.security.email.cancel') }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan verifikasi email ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3.5 py-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-medium text-xs rounded-md transition">
                                    Batalkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- Tampilan Simpel Normal -->
                <div class="space-y-4">
                    @if($customer->email && $customer->email_verified_at)
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-lg border border-slate-200">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Email Aktif</span>
                                <span class="text-sm font-bold text-slate-800 font-mono mt-0.5 block">{{ $customer->email }}</span>
                            </div>
                            <span class="text-xs text-slate-400">
                                Diverifikasi {{ $customer->email_verified_at->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    @endif

                    <form action="{{ route('customer.security.email') }}" method="POST">
                        @csrf
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                            {{ $customer->email ? 'Ganti Alamat Email' : 'Daftarkan Email Pemulihan' }}
                        </label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                placeholder="contoh: nama.nasabah@gmail.com"
                                class="flex-1 px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition @error('email') border-red-500 @enderror">
                            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition whitespace-nowrap">
                                Kirim Tautan Verifikasi
                            </button>
                        </div>
                        @error('email')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Password and PIN Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Change Password -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900">Ubah Password Login</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('customer.security.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Password Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('current_password')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Password Baru</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('new_password')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required minlength="6"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change PIN -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                <div class="w-6 h-6 rounded bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900">Ubah PIN Transaksi</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('customer.security.pin') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="current_pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">PIN Saat Ini</label>
                            <button type="button" onclick="document.getElementById('modalResetPin').classList.remove('hidden')" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                Lupa PIN?
                            </button>
                        </div>
                        <input type="password" id="current_pin" name="current_pin" required maxlength="6" pattern="[0-9]{6}" placeholder="6 digit angka"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('current_pin')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="new_pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">PIN Baru</label>
                        <input type="password" id="new_pin" name="new_pin" required maxlength="6" pattern="[0-9]{6}" placeholder="6 digit angka"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        @error('new_pin')
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="new_pin_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Konfirmasi PIN Baru</label>
                        <input type="password" id="new_pin_confirmation" name="new_pin_confirmation" required maxlength="6" pattern="[0-9]{6}" placeholder="6 digit angka"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition">
                            Perbarui PIN
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Modal Reset PIN via Password Akun -->
    <div id="modalResetPin" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Atur Ulang PIN Transaksi</h3>
                        <p class="text-xs text-slate-500">Gunakan password akun Anda untuk mereset PIN.</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modalResetPin').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
            </div>

            <form action="{{ route('customer.security.reset-pin') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Password Akun Login *</label>
                    <input type="password" name="password" required placeholder="Masukkan password login Anda"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:border-blue-600">
                    @error('pin_password')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">PIN Baru (6 Digit) *</label>
                    <input type="password" name="new_pin" required maxlength="6" pattern="[0-9]{6}" placeholder="Contoh: 123456"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:border-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Konfirmasi PIN Baru *</label>
                    <input type="password" name="new_pin_confirmation" required maxlength="6" pattern="[0-9]{6}" placeholder="Ulangi 6 digit PIN baru"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:border-blue-600">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalResetPin').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-md transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md shadow-sm transition">
                        Simpan PIN Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
