@extends('layouts.teller')

@section('title', 'Setoran Tunai')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Form Setoran Tunai (Deposit)</h1>
        <p class="text-sm text-slate-500">Lakukan transaksi setoran tunai simpanan nasabah.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Form -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <form action="{{ route('teller.deposit.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Account Identification -->
                <div>
                    <label for="account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        1. Nomor Rekening Nasabah <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $account?->account_number ?? $accountNumber) }}" required placeholder="Contoh: 1000000001"
                               class="flex-1 px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        <button type="button" onclick="lookupAccount()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex-shrink-0">
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

                <!-- Deposit Amount -->
                <div>
                    <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        2. Nominal Setoran Tunai (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="1000" step="1000" required placeholder="0"
                               class="w-full pl-10 pr-4 py-2 text-lg font-semibold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition text-slate-900">
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

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        3. Catatan Setoran (Opsional)
                    </label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Misal: Setoran simpanan sukarela"
                           class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                </div>

                <!-- Submit Action -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('teller.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-md transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-1.5">
                        <span>Proses Transaksi Setoran</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- Guidance Side Panel -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-2">Ketentuan Setoran</h3>
                <ul class="space-y-1.5 text-xs text-slate-600 list-disc list-inside">
                    <li>Teller wajib melakukan identifikasi rekening sebelum transaksi diproses.</li>
                    <li>Hitung uang fisik secara teliti dan pastikan jumlah sesuai nominal input.</li>
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
                window.location.href = "{{ route('teller.deposit') }}?account_number=" + encodeURIComponent(acc);
            }
        }
    </script>
@endsection
