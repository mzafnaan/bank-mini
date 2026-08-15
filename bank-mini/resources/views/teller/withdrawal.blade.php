@extends('layouts.teller')

@section('title', 'Penarikan Tunai')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Form Penarikan Tunai (Withdrawal)</h1>
        <p class="text-sm text-slate-500">Lakukan penarikan simpanan nasabah dengan otorisasi PIN (BR-036 ~ BR-047).</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Form -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <form action="{{ route('teller.withdrawal.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Account Identification -->
                <div>
                    <label for="account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        1. Nomor Rekening Nasabah <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $account?->account_number ?? $accountNumber) }}" required placeholder="Contoh: 1000000001"
                               class="flex-1 px-3.5 py-2 text-sm font-mono font-bold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        <button type="button" onclick="lookupAccount()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition flex-shrink-0">
                            Cek Rekening
                        </button>
                    </div>
                </div>

                <!-- Verified Account Summary Box -->
                @if($account)
                    <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-md flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-blue-600 text-white font-bold rounded-md flex items-center justify-center text-xs shadow-sm">
                                {{ strtoupper(substr($account->customer?->name ?? 'N', 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 text-sm block">{{ $account->customer?->name }}</span>
                                <span class="text-xs text-slate-500 block">NIS: {{ $account->customer?->nis }} | Kelas: {{ $account->customer?->class }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-semibold text-blue-800 block">Saldo Saat Ini</span>
                            <span class="text-sm font-bold text-blue-700 font-mono">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Withdrawal Amount -->
                <div>
                    <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        2. Nominal Penarikan Tunai (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="1000" step="1000" required placeholder="0"
                               class="w-full pl-10 pr-4 py-2 text-lg font-bold font-mono bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition text-slate-900">
                    </div>

                    <!-- Quick Amount Presets -->
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        <button type="button" onclick="setAmount(10000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            + 10.000
                        </button>
                        <button type="button" onclick="setAmount(20000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            + 20.000
                        </button>
                        <button type="button" onclick="setAmount(50000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            + 50.000
                        </button>
                        <button type="button" onclick="setAmount(100000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            + 100.000
                        </button>
                        <button type="button" onclick="setAmount(200000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            + 200.000
                        </button>
                        <button type="button" onclick="setAmount(500000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            + 500.000
                        </button>
                    </div>
                </div>

                <!-- Customer PIN Authorization Input (Light Consistent Theme) -->
                <div class="p-4 bg-blue-50/70 text-slate-900 rounded-md shadow-sm border border-blue-200">
                    <label for="pin" class="block text-xs font-semibold uppercase tracking-wider text-blue-900 mb-1">
                        3. Otorisasi PIN Nasabah (6 Digit) <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-slate-600 mb-2.5">Nasabah memasukkan 6 digit PIN untuk menyetujui penarikan (Default seeder: 123456).</p>
                    <input type="password" id="pin" name="pin" maxlength="6" pattern="[0-9]{6}" required placeholder="••••••••" autocomplete="off"
                           class="w-full px-4 py-2.5 text-xl font-mono font-bold tracking-widest text-center bg-white border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 transition text-slate-900 placeholder-slate-400">
                </div>

                <!-- Submit Action -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('teller.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-md transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-1.5">
                        <span>Proses Transaksi Penarikan</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- Guidance Side Panel -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-2">Aturan Penarikan Tunai</h3>
                <ul class="space-y-1.5 text-xs text-slate-600 list-disc list-inside">
                    <li>Otorisasi menggunakan PIN 6 digit nasabah.</li>
                    <li>Saldo sisa setelah penarikan minimal Rp 10.000 (BR-044).</li>
                    <li>Jurnal Akuntansi Otomatis:
                        <div class="mt-1 font-mono text-[11px] text-slate-700 bg-slate-50 p-2 rounded border border-slate-200">
                            (Debit) 201 Tabungan Nasabah<br>
                            (Kredit) 101 Kas
                        </div>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    <script>
        function setAmount(val) {
            document.getElementById('amount').value = val;
        }

        function lookupAccount() {
            const acc = document.getElementById('account_number').value.trim();
            if (acc) {
                window.location.href = "{{ route('teller.withdrawal') }}?account_number=" + encodeURIComponent(acc);
            }
        }
    </script>
@endsection
