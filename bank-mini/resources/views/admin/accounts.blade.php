@extends('layouts.admin')

@section('title', 'Daftar Rekening Nasabah')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Rekening Nasabah</h1>
        <p class="text-sm text-slate-500">Daftar seluruh rekening bank mini nasabah yang dibuat otomatis saat proses onboarding.</p>
    </div>

    <!-- Accounts Table Card -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">No. Rekening</th>
                        <th class="px-6 py-3">Nama Nasabah</th>
                        <th class="px-6 py-3">Saldo Saat Ini</th>
                        <th class="px-6 py-3">Status Rekening</th>
                        <th class="px-6 py-3 text-right">Aksi / Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-blue-600">
                                {{ $account->account_number }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $account->customer?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">
                                Rp {{ number_format($account->balance, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($account->customer?->customerAccount?->status === 'active')
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-blue-50 text-blue-700">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-slate-100 text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="showAccountDetail({{ json_encode($account->load('customer.customerAccount')) }})"
                                    class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 rounded hover:bg-blue-100 transition">
                                    Lihat Detail & QR
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-slate-400">Belum ada data rekening nasabah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Rekening & QR Code -->
    <div id="modalAccountDetail" class="fixed inset-0 bg-slate-900/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-lg border border-slate-200 shadow-md max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <h3 class="text-lg font-bold text-slate-900">Detail Rekening Nasabah</h3>
                <button onclick="document.getElementById('modalAccountDetail').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            
            <div class="space-y-4">
                <!-- QR Code Dynamic Renderer -->
                <div class="text-center py-2 bg-slate-50 rounded-lg border border-slate-100">
                    <img id="detail_qr_img" src="" alt="QR Code Rekening" class="w-36 h-36 mx-auto mb-2 border border-slate-200 rounded p-1 bg-white">
                    <div id="detail_qr_code" class="font-mono text-xs text-slate-500 font-semibold"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">No. Rekening</span>
                        <span id="detail_account_number" class="font-mono font-bold text-blue-600"></span>
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
                        <span id="detail_customer_nis" class="font-mono text-slate-700"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Kelas</span>
                        <span id="detail_customer_class" class="text-slate-700"></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-slate-400 uppercase">Status Rekening</span>
                        <span id="detail_status" class="font-medium text-slate-700"></span>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="document.getElementById('modalAccountDetail').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-md">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function showAccountDetail(account) {
            document.getElementById('detail_account_number').innerText = account.account_number;
            document.getElementById('detail_balance').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(account.balance);
            document.getElementById('detail_customer_name').innerText = account.customer ? account.customer.name : '-';
            document.getElementById('detail_customer_nis').innerText = account.customer ? account.customer.nis : '-';
            document.getElementById('detail_customer_class').innerText = account.customer ? account.customer.class : '-';
            
            const status = (account.customer && account.customer.customer_account) ? account.customer.customer_account.status : 'active';
            document.getElementById('detail_status').innerText = status === 'active' ? 'Aktif' : 'Nonaktif';
            
            const qrText = account.qr_code || account.account_number;
            document.getElementById('detail_qr_code').innerText = qrText;
            document.getElementById('detail_qr_img').src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(qrText);
            
            document.getElementById('modalAccountDetail').classList.remove('hidden');
        }
    </script>
@endsection
