@extends('layouts.teller')

@section('title', 'Penarikan Tunai')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Form Penarikan Tunai (Withdrawal)</h1>
        <p class="text-sm text-slate-500">Lakukan penarikan simpanan nasabah dengan otorisasi PIN melalui aplikasi mobile atau langsung di teller.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->has('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ $errors->first('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Form -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm p-6">
            <form id="withdrawalForm" action="{{ route('teller.withdrawal.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Account Identification -->
                <div>
                    <label for="account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        1. Nomor Rekening Nasabah <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $account?->account_number ?? $accountNumber) }}" required placeholder="Contoh: RPL-001"
                               class="flex-1 px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition">
                        <button type="button" onclick="lookupAccount()" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md transition flex-shrink-0">
                            Cek Rekening
                        </button>
                    </div>
                </div>

                <!-- Verified Account Summary Box -->
                @if($account)
                    <div class="p-3.5 bg-white border border-slate-200 rounded-md flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-slate-100 text-slate-700 font-bold rounded-md flex items-center justify-center text-xs border border-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 4a3.25 3.25 0 1 1 0 6.5A3.25 3.25 0 0 1 12 6Zm0 13a7 7 0 0 1-5.45-2.6 6.2 6.2 0 0 1 10.9 0A7 7 0 0 1 12 19Z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 text-sm block" id="customerNameDisplay">{{ $account->customer?->name }}</span>
                                <span class="text-xs text-slate-500 block">NIS: {{ $account->customer?->nis }} | Kelas: {{ $account->customer?->class }}</span>
                                <div class="mt-1">
                                    @if($account->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Status Rekening: Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded bg-rose-50 text-rose-700 border border-rose-200">Status Rekening: Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-semibold text-slate-500 block">Saldo Saat Ini</span>
                            <span class="text-sm font-bold text-blue-700" id="currentBalanceDisplay">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($account->status === 'inactive')
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-md text-xs text-rose-700 flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span><strong>Peringatan:</strong> Rekening nasabah sedang tidak aktif dan tidak dapat digunakan untuk transaksi.</span>
                        </div>
                    @endif
                @endif

                <!-- Withdrawal Amount -->
                <div>
                    <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        2. Nominal Penarikan Tunai (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="1000" step="1000" required placeholder="0"
                               class="w-full pl-10 pr-4 py-2 text-lg font-bold bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition text-slate-900">
                    </div>

                    <!-- Quick Amount Presets -->
                    <div class="flex flex-wrap gap-2 mt-2.5">
                        <button type="button" onclick="setAmount(10000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            10.000
                        </button>
                        <button type="button" onclick="setAmount(20000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            20.000
                        </button>
                        <button type="button" onclick="setAmount(50000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            50.000
                        </button>
                        <button type="button" onclick="setAmount(100000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            100.000
                        </button>
                        <button type="button" onclick="setAmount(200000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            200.000
                        </button>
                        <button type="button" onclick="setAmount(500000)" class="px-3 py-1 bg-slate-100 hover:bg-blue-100 hover:text-blue-800 text-slate-700 text-xs font-medium rounded border border-slate-200">
                            500.000
                        </button>
                    </div>
                </div>

                <!-- 3. Metode Otorisasi PIN -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        3. Metode Otorisasi PIN <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Option 1: Mobile App -->
                        <label id="optCardMobile" class="flex items-center justify-between p-3 border rounded-md cursor-pointer transition select-none border-blue-600 bg-blue-50/30">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-semibold text-slate-800">Aplikasi Mobile</span>
                            </div>
                            <input type="radio" name="auth_method" value="mobile" checked onchange="toggleAuthMethod('mobile')" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                        </label>

                        <!-- Option 2: Teller Direct PIN -->
                        <label id="optCardTeller" class="flex items-center justify-between p-3 border rounded-md cursor-pointer transition select-none border-slate-200 bg-white hover:bg-slate-50">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span class="text-xs font-semibold text-slate-800">Input di Teller</span>
                            </div>
                            <input type="radio" name="auth_method" value="teller" onchange="toggleAuthMethod('teller')" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                        </label>
                    </div>
                </div>

                <!-- Customer PIN Input (Visible only for Option 2: Teller) -->
                <div id="pinInputContainer" class="hidden">
                    <label for="pin" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        PIN 6-Digit Nasabah <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="pin" name="pin" maxlength="6" pattern="[0-9]{6}" placeholder="••••••" autocomplete="off"
                           class="w-full px-3.5 py-2 text-base font-bold tracking-widest text-center bg-slate-50 border border-slate-300 rounded-md focus:outline-none focus:border-blue-600 focus:bg-white transition text-slate-900 placeholder-slate-400">
                </div>

                <!-- Submit Action -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('teller.dashboard') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-md transition">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitWithdrawal" @if($account && $account->status === 'inactive') disabled class="px-5 py-2.5 bg-slate-300 text-slate-500 font-medium text-xs rounded-md cursor-not-allowed flex items-center gap-1.5" @else class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs rounded-md shadow-sm transition flex items-center gap-1.5" @endif>
                        <span id="btnSubmitText">Kirim Permintaan ke Aplikasi Nasabah</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- Guidance & Active Request Side Panel -->
        <div class="space-y-6">
            @if(isset($activeRequest) && $activeRequest)
                <div class="bg-amber-50 rounded-lg border border-amber-200 shadow-sm p-5 text-amber-900">
                    <div class="flex items-center gap-2 mb-2 font-bold text-xs uppercase tracking-wider text-amber-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                        Permintaan Menunggu Otorisasi
                    </div>
                    <p class="text-xs text-amber-700 mb-3">
                        Ada permintaan penarikan yang sedang menunggu otorisasi nasabah:
                    </p>
                    <div class="bg-white p-3 rounded border border-amber-200 text-xs space-y-1 mb-3">
                        <div class="flex justify-between">
                            <span class="text-slate-500">ID Permintaan:</span>
                            <span class="font-bold text-slate-800">#{{ $activeRequest->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nominal:</span>
                            <span class="font-bold text-emerald-700">Rp {{ number_format($activeRequest->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <button type="button" onclick="openWaitingModal({{ $activeRequest->id }}, '{{ $account->account_number }}', '{{ $account->customer?->name }}', {{ $activeRequest->amount }})"
                            class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium text-xs rounded-md shadow transition">
                        Buka Layar Tunggu
                    </button>
                </div>
            @endif

            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-5">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider mb-2">Aturan Penarikan Tunai</h3>
                <ul class="space-y-1.5 text-xs text-slate-600 list-disc list-inside">
                    <li>Otorisasi menggunakan PIN 6 digit nasabah.</li>
                    <li>Saldo sisa setelah penarikan minimal Rp 10.000.</li>
                </ul>
            </div>
        </div>

    </div>

    <!-- Modal Menunggu Otorisasi Nasabah Mobile -->
    <div id="waitingModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all p-6 text-center">
            
            <!-- Animated Icon Area -->
            <div id="modalIconWaiting" class="mx-auto w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4 relative">
                <span class="absolute w-full h-full rounded-full bg-blue-400 opacity-20 animate-ping"></span>
                <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>

            <div id="modalIconSuccess" class="hidden mx-auto w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <div id="modalIconRejected" class="hidden mx-auto w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>

            <h3 id="modalTitle" class="text-lg font-bold text-slate-900 mb-1">Menunggu Otorisasi Nasabah</h3>
            <p id="modalDescription" class="text-xs text-slate-500 mb-5">
                Minta nasabah membuka aplikasi <strong>M-Bank</strong> di smartphone mereka dan memasukkan PIN untuk menyetujui penarikan ini.
            </p>

            <!-- Transaction Detail Box -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-left text-xs space-y-2 mb-5">
                <div class="flex justify-between items-center pb-2 border-b border-slate-200">
                    <span class="text-slate-500">Nominal Penarikan:</span>
                    <span id="modalAmount" class="font-extrabold text-base text-blue-700">Rp 0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Rekening:</span>
                    <span id="modalAccount" class="font-bold text-slate-800">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nama Nasabah:</span>
                    <span id="modalCustomer" class="font-bold text-slate-800">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Status Otorisasi:</span>
                    <span id="modalStatusBadge" class="inline-flex items-center px-2 py-0.5 rounded font-semibold text-[11px] bg-amber-100 text-amber-800">
                        Menunggu PIN...
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div id="modalActionsWaiting" class="flex gap-2 justify-center">
                <button type="button" onclick="cancelActiveRequest()" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold rounded-lg transition">
                    Batalkan Permintaan
                </button>
            </div>

            <div id="modalActionsFinished" class="hidden flex justify-center">
                <button type="button" onclick="closeWaitingModal()" class="px-6 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <script>
        let currentAuthMethod = 'mobile';
        let activePollingInterval = null;
        let activeRequestId = null;

        function setAmount(val) {
            document.getElementById('amount').value = val;
        }

        function lookupAccount() {
            const acc = document.getElementById('account_number').value.trim();
            if (acc) {
                window.location.href = "{{ route('teller.withdrawal') }}?account_number=" + encodeURIComponent(acc);
            }
        }

        function toggleAuthMethod(method) {
            currentAuthMethod = method;
            const pinContainer = document.getElementById('pinInputContainer');
            const pinInput = document.getElementById('pin');
            const btnSubmitText = document.getElementById('btnSubmitText');
            const cardMobile = document.getElementById('optCardMobile');
            const cardTeller = document.getElementById('optCardTeller');

            if (method === 'mobile') {
                pinContainer.classList.add('hidden');
                pinInput.removeAttribute('required');
                pinInput.value = '';
                btnSubmitText.textContent = 'Kirim Permintaan ke Aplikasi Nasabah';

                cardMobile.classList.add('border-blue-600', 'bg-blue-50/20');
                cardMobile.classList.remove('border-slate-200');
                cardTeller.classList.remove('border-blue-600', 'bg-blue-50/20');
                cardTeller.classList.add('border-slate-200');
            } else {
                pinContainer.classList.remove('hidden');
                pinInput.setAttribute('required', 'required');
                btnSubmitText.textContent = 'Proses Transaksi Penarikan';

                cardTeller.classList.add('border-blue-600', 'bg-blue-50/20');
                cardTeller.classList.remove('border-slate-200');
                cardMobile.classList.remove('border-blue-600', 'bg-blue-50/20');
                cardMobile.classList.add('border-slate-200');
            }
        }

        // Initialize state on load
        toggleAuthMethod('mobile');

        // Handle form submission
        document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
            const accountNumber = document.getElementById('account_number').value.trim();
            const amount = document.getElementById('amount').value.trim();
            const customerName = document.getElementById('customerNameDisplay')?.textContent.trim() || 'Nasabah';

            if (!accountNumber || !amount) {
                alert('Nomor rekening dan nominal penarikan wajib diisi.');
                e.preventDefault();
                return;
            }

            const formattedAmount = new Intl.NumberFormat('id-ID').format(amount);

            if (currentAuthMethod === 'mobile') {
                e.preventDefault();

                if (!confirm(`Apakah Anda yakin ingin mengirim permintaan otorisasi penarikan tunai sebesar Rp ${formattedAmount} untuk rekening ${accountNumber} (${customerName}) ke smartphone nasabah?`)) {
                    return;
                }

                const btnSubmit = document.getElementById('btnSubmitWithdrawal');
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-70');

                fetch("{{ route('teller.withdrawal.request') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        account_number: accountNumber,
                        amount: amount
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-70');

                    if (data.success && data.data) {
                        openWaitingModal(data.data.id, accountNumber, customerName, data.data.amount);
                    } else {
                        alert(data.message || 'Gagal mengirim permintaan penarikan.');
                    }
                })
                .catch(err => {
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-70');
                    alert('Terjadi kesalahan jaringan atau server.');
                });
            } else {
                // If teller method, confirm before letting the form submit normally
                if (!confirm(`Apakah Anda yakin ingin memproses transaksi penarikan tunai sebesar Rp ${formattedAmount} dari rekening ${accountNumber} (${customerName})?`)) {
                    e.preventDefault();
                    return;
                }
            }
        });

        function openWaitingModal(requestId, accountNumber, customerName, amount) {
            activeRequestId = requestId;

            document.getElementById('modalAmount').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');
            document.getElementById('modalAccount').textContent = accountNumber;
            document.getElementById('modalCustomer').textContent = customerName;

            document.getElementById('modalIconWaiting').classList.remove('hidden');
            document.getElementById('modalIconSuccess').classList.add('hidden');
            document.getElementById('modalIconRejected').classList.add('hidden');

            document.getElementById('modalTitle').textContent = 'Menunggu Otorisasi Nasabah';
            document.getElementById('modalDescription').innerHTML = 'Minta nasabah membuka aplikasi <strong>M-Bank</strong> di smartphone mereka dan memasukkan PIN untuk menyetujui penarikan ini.';

            document.getElementById('modalStatusBadge').className = 'inline-flex items-center px-2 py-0.5 rounded font-semibold text-[11px] bg-amber-100 text-amber-800';
            document.getElementById('modalStatusBadge').textContent = 'Menunggu PIN...';

            document.getElementById('modalActionsWaiting').classList.remove('hidden');
            document.getElementById('modalActionsFinished').classList.add('hidden');

            document.getElementById('waitingModal').classList.remove('hidden');

            // Start polling every 2 seconds
            if (activePollingInterval) clearInterval(activePollingInterval);
            activePollingInterval = setInterval(() => pollRequestStatus(requestId), 2000);
        }

        function pollRequestStatus(requestId) {
            fetch(`/teller/withdrawal-requests/${requestId}/status`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'approved') {
                    clearInterval(activePollingInterval);

                    document.getElementById('modalIconWaiting').classList.add('hidden');
                    document.getElementById('modalIconSuccess').classList.remove('hidden');

                    document.getElementById('modalTitle').textContent = 'Transaksi Berhasil Disetujui!';
                    document.getElementById('modalDescription').textContent = 'Nasabah telah memasukkan PIN dengan benar. Mengalihkan ke struk bukti penarikan...';

                    document.getElementById('modalStatusBadge').className = 'inline-flex items-center px-2 py-0.5 rounded font-semibold text-[11px] bg-emerald-100 text-emerald-800';
                    document.getElementById('modalStatusBadge').textContent = 'Disetujui (Approved)';

                    document.getElementById('modalActionsWaiting').classList.add('hidden');

                    setTimeout(() => {
                        if (data.receipt_url) {
                            window.location.href = data.receipt_url;
                        } else {
                            window.location.reload();
                        }
                    }, 1200);

                } else if (data.status === 'rejected') {
                    clearInterval(activePollingInterval);

                    document.getElementById('modalIconWaiting').classList.add('hidden');
                    document.getElementById('modalIconRejected').classList.remove('hidden');

                    document.getElementById('modalTitle').textContent = 'Penarikan Ditolak Nasabah';
                    document.getElementById('modalDescription').textContent = 'Nasabah menolak permintaan otorisasi penarikan ini melalui aplikasi mobile.';

                    document.getElementById('modalStatusBadge').className = 'inline-flex items-center px-2 py-0.5 rounded font-semibold text-[11px] bg-rose-100 text-rose-800';
                    document.getElementById('modalStatusBadge').textContent = 'Ditolak (Rejected)';

                    document.getElementById('modalActionsWaiting').classList.add('hidden');
                    document.getElementById('modalActionsFinished').classList.remove('hidden');

                } else if (data.status === 'expired') {
                    clearInterval(activePollingInterval);

                    document.getElementById('modalIconWaiting').classList.add('hidden');
                    document.getElementById('modalIconRejected').classList.remove('hidden');

                    document.getElementById('modalTitle').textContent = 'Permintaan Kedaluwarsa';
                    document.getElementById('modalDescription').textContent = 'Batas waktu 15 menit otorisasi telah berakhir.';

                    document.getElementById('modalStatusBadge').className = 'inline-flex items-center px-2 py-0.5 rounded font-semibold text-[11px] bg-slate-100 text-slate-800';
                    document.getElementById('modalStatusBadge').textContent = 'Kedaluwarsa (Expired)';

                    document.getElementById('modalActionsWaiting').classList.add('hidden');
                    document.getElementById('modalActionsFinished').classList.remove('hidden');
                }
            })
            .catch(err => {
                console.error('Polling error:', err);
            });
        }

        function cancelActiveRequest() {
            if (!activeRequestId) return;

            if (!confirm('Apakah Anda yakin ingin membatalkan permintaan penarikan ini?')) return;

            fetch(`/teller/withdrawal-requests/${activeRequestId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(activePollingInterval);
                closeWaitingModal();
                window.location.reload();
            })
            .catch(err => {
                alert('Gagal membatalkan permintaan.');
            });
        }

        function closeWaitingModal() {
            if (activePollingInterval) clearInterval(activePollingInterval);
            document.getElementById('waitingModal').classList.add('hidden');
            activeRequestId = null;
        }
    </script>
@endsection
