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
                    <h2 class="text-base font-semibold text-slate-900">Email Pemulihan Akun</h2>
                    <p class="text-xs text-slate-500">Digunakan untuk menerima tautan atur ulang password jika Anda lupa kata sandi.</p>
                </div>
            </div>

            @if($customer->email && $customer->email_verified_at)
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Terverifikasi
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600">
                    Belum Diatur
                </span>
            @endif
        </div>

        <div class="p-6 space-y-6">
            <!-- Current Active Email Info -->
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Email Pemulihan Aktif Saat Ini</span>
                    @if($customer->email && $customer->email_verified_at)
                        <span class="text-base font-bold text-slate-900 mt-0.5 block font-mono">{{ $customer->email }}</span>
                        <span class="text-xs text-slate-500 mt-1 block">Diverifikasi pada {{ $customer->email_verified_at->translatedFormat('d F Y, H:i') }}</span>
                    @else
                        <span class="text-sm font-semibold text-amber-700 mt-0.5 block">Belum ada email yang terverifikasi</span>
                        <span class="text-xs text-slate-500 mt-1 block">Tambahkan email Anda di bawah agar dapat melakukan pemulihan password secara mandiri.</span>
                    @endif
                </div>

                @if($customer->email && $customer->email_verified_at)
                    <div class="text-xs text-slate-400 bg-white px-3 py-1.5 rounded border border-slate-200 self-start sm:self-auto">
                        Status: Siap digunakan untuk Lupa Password
                    </div>
                @endif
            </div>

            <!-- Pending Verification Alert Box -->
            @if($customer->pending_email)
                <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-amber-900">Verifikasi Email Baru Sedang Tertunda</h3>
                            <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                                Tautan verifikasi telah dikirimkan ke <strong class="font-mono text-slate-900">{{ $customer->pending_email }}</strong>. 
                                @if($customer->email)
                                    Email pemulihan aktif Anda tetap <strong class="font-mono text-slate-900">{{ $customer->email }}</strong> sampai email baru diverifikasi.
                                @else
                                    Buka tautan di inbox email tersebut untuk mengaktifkannya sebagai email pemulihan akun Anda.
                                @endif
                            </p>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <form action="{{ route('customer.security.email.resend') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs rounded shadow-sm transition">
                                        Kirim Ulang Tautan Verifikasi
                                    </button>
                                </form>

                                <form action="{{ route('customer.security.email.cancel') }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan verifikasi email baru ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 font-medium text-xs rounded transition">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Add / Change Email -->
            <form action="{{ route('customer.security.email') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                        {{ $customer->email ? 'Ganti dengan Alamat Email Baru' : 'Masukkan Alamat Email Anda' }}
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <div class="flex-1">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   placeholder="contoh: nama.nasabah@gmail.com"
                                   class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition @error('email') border-red-500 @enderror">
                            @error('email')
                                <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex-shrink-0 flex items-center justify-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Kirim Tautan Verifikasi
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">
                        Kami akan mengirimkan email konfirmasi. Jika Anda mengganti email lama, email lama Anda tidak akan diganti sampai email baru berhasil diverifikasi.
                    </p>
                </div>
            </form>
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
                        <label for="current_pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">PIN Saat Ini</label>
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
@endsection
