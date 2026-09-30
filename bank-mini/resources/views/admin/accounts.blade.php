@extends('layouts.admin')

@section('title', 'Daftar Rekening Nasabah')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Rekening Nasabah</h1>
        <p class="text-sm text-slate-500">Daftar seluruh rekening bank mini nasabah beserta status operasional rekening.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-blue-900">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700 font-bold">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-rose-50 border border-rose-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-rose-900">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    @if(request('search'))
        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center justify-between text-xs text-blue-900">
            <span>Filter pencarian: <strong>"{{ request('search') }}"</strong> (Ditemukan {{ $accounts->count() }} data)</span>
            <a href="{{ route('admin.accounts') }}" class="font-semibold text-blue-700 hover:underline">Reset Filter</a>
        </div>
    @endif

    <!-- Accounts Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3">No. Rekening</th>
                    <th class="px-6 py-3">Nama Nasabah</th>
                    <th class="px-6 py-3">Kelas</th>
                    <th class="px-6 py-3">Saldo</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($accounts as $account)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-semibold text-slate-900">
                            {{ $account->account_number }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900">
                            {{ $account->customer?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $account->customer?->class ?? '-' }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-900">
                            Rp {{ number_format($account->balance, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($account->status === 'active')
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button onclick="showAccountDetail({{ json_encode($account->load('customer')) }})"
                                class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition">
                                Detail & QR
                            </button>

                            @if($account->status === 'active')
                                <button type="button"
                                    onclick="confirmToggleStatus('{{ $account->id }}', '{{ $account->account_number }}', '{{ addslashes($account->customer?->name ?? '-') }}', 'inactive')"
                                    class="px-2.5 py-1 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded hover:bg-slate-200 transition">
                                    Nonaktifkan
                                </button>
                            @else
                                <button type="button"
                                    onclick="confirmToggleStatus('{{ $account->id }}', '{{ $account->account_number }}', '{{ addslashes($account->customer?->name ?? '-') }}', 'active')"
                                    class="px-2.5 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition">
                                    Aktifkan
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-slate-400">Belum ada data rekening nasabah.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Modal Konfirmasi Status Rekening -->
    <div id="modalConfirmStatus" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="confirm_modal_title" class="text-lg font-bold text-slate-900">Konfirmasi Status Rekening</h3>
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

    <!-- Modal Detail Rekening & QR Code -->
    <div id="modalAccountDetail" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Detail Rekening Nasabah</h3>
                <button onclick="document.getElementById('modalAccountDetail').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            
            <div class="space-y-4">
                <!-- QR Code Dynamic Renderer -->
                <div class="text-center py-2 bg-slate-50 rounded-lg border border-slate-100">
                    <img id="detail_qr_img" src="" alt="QR Code Rekening" class="w-36 h-36 mx-auto mb-2 border border-slate-200 rounded p-1 bg-white">
                    <div id="detail_qr_code" class="text-xs text-slate-600 font-semibold"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">No. Rekening</span>
                        <span id="detail_account_number" class="font-semibold text-slate-900"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Saldo Saat Ini</span>
                        <span id="detail_balance" class="font-bold text-slate-900"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Nama Nasabah</span>
                        <span id="detail_customer_name" class="font-semibold text-slate-800"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">NIS</span>
                        <span id="detail_customer_nis" class="text-slate-700 font-medium"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Kelas</span>
                        <span id="detail_customer_class" class="text-slate-700"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Kode Jurusan</span>
                        <span id="detail_customer_major" class="text-slate-700 font-medium"></span>
                    </div>
                    <div class="col-span-2">
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Status Rekening</span>
                        <span id="detail_status_badge" class="mt-1 inline-block"></span>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="document.getElementById('modalAccountDetail').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function confirmToggleStatus(accountId, accountNumber, customerName, targetAction) {
            const form = document.getElementById('formConfirmStatus');
            form.action = '/admin/accounts/' + accountId + '/status';

            const title = document.getElementById('confirm_modal_title');
            const msg = document.getElementById('confirm_modal_message');
            const btn = document.getElementById('confirm_modal_btn');

            if (targetAction === 'inactive') {
                title.innerText = 'Nonaktifkan Rekening';
                msg.innerHTML = 'Apakah Anda yakin ingin menonaktifkan rekening <strong>' + accountNumber + '</strong> atas nama <strong>' + customerName + '</strong>?<br><br><span class="text-xs text-slate-500">Catatan: Rekening yang dinonaktifkan tidak dapat digunakan untuk transaksi setoran maupun penarikan tunai. Akun login nasabah tetap aktif.</span>';
                btn.innerText = 'Ya, Nonaktifkan';
                btn.className = 'px-4 py-2 font-medium text-sm rounded-md text-white bg-red-600 hover:bg-red-700 transition shadow-sm';
            } else {
                title.innerText = 'Aktifkan Rekening';
                msg.innerHTML = 'Apakah Anda yakin ingin mengaktifkan kembali rekening <strong>' + accountNumber + '</strong> atas nama <strong>' + customerName + '</strong>?<br><br><span class="text-xs text-slate-500">Catatan: Rekening akan dapat digunakan kembali untuk memproses transaksi setoran dan penarikan tunai.</span>';
                btn.innerText = 'Ya, Aktifkan';
                btn.className = 'px-4 py-2 font-medium text-sm rounded-md text-white bg-blue-600 hover:bg-blue-700 transition shadow-sm';
            }

            document.getElementById('modalConfirmStatus').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('modalConfirmStatus').classList.add('hidden');
        }

        function showAccountDetail(account) {
            document.getElementById('detail_account_number').innerText = account.account_number;
            document.getElementById('detail_balance').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(account.balance);
            document.getElementById('detail_customer_name').innerText = account.customer ? account.customer.name : '-';
            document.getElementById('detail_customer_nis').innerText = account.customer ? account.customer.nis : '-';
            document.getElementById('detail_customer_class').innerText = account.customer ? account.customer.class : '-';
            
            // Extract major code from account number prefix
            let major = '-';
            if (account.account_number && account.account_number.includes('-')) {
                const parts = account.account_number.split('-');
                const validCodes = ['AK', 'BR', 'BD', 'MP', 'DKV', 'RPL', 'TKJ'];
                if (validCodes.includes(parts[0].toUpperCase())) {
                    major = parts[0].toUpperCase();
                }
            }
            if (major === '-' && account.customer && account.customer.class) {
                const match = account.customer.class.match(/\b(AK|BR|BD|MP|DKV|RPL|TKJ)\b/i);
                if (match) major = match[1].toUpperCase();
            }
            document.getElementById('detail_customer_major').innerText = major;

            const badgeContainer = document.getElementById('detail_status_badge');
            if (account.status === 'active') {
                badgeContainer.innerHTML = '<span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Aktif</span>';
            } else {
                badgeContainer.innerHTML = '<span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-500">Nonaktif</span>';
            }
            
            const qrText = account.qr_code || account.account_number;
            document.getElementById('detail_qr_code').innerText = qrText;
            document.getElementById('detail_qr_img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(qrText);
            
            document.getElementById('modalAccountDetail').classList.remove('hidden');
        }
    </script>
@endsection
