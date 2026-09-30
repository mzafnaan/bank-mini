<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Display the main Customer Dashboard Overview.
     */
    public function dashboard(Request $request): View
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        $balance = $bankAccount?->balance ?? 0;

        // Statistics
        $totalDeposit = Transaction::where('bank_account_id', $bankAccount?->id)
            ->where('type', 'deposit')
            ->sum('amount');

        $totalWithdrawal = Transaction::where('bank_account_id', $bankAccount?->id)
            ->where('type', 'withdrawal')
            ->sum('amount');

        $transactionCount = Transaction::where('bank_account_id', $bankAccount?->id)
            ->count();

        // 5 Most recent transactions
        $recentTransactions = Transaction::with('teller')
            ->where('bank_account_id', $bankAccount?->id)
            ->latest('created_at')
            ->take(5)
            ->get();

        // Active pending withdrawal request waiting for customer PIN authorization (BR-039, BR-040, DD-006)
        $pendingWithdrawal = WithdrawalRequest::with('teller')
            ->where('bank_account_id', $bankAccount?->id)
            ->where('status', 'waiting')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('created_at')
            ->first();

        return view('customer.dashboard', compact(
            'customerAccount',
            'customer',
            'bankAccount',
            'balance',
            'totalDeposit',
            'totalWithdrawal',
            'transactionCount',
            'recentTransactions',
            'pendingWithdrawal'
        ));
    }

    /**
     * Display the Customer's Transaction History (Mutasi Rekening).
     */
    public function transactions(Request $request): View
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        $type = $request->query('type');
        $search = trim((string) $request->query('search', ''));
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Transaction::with('teller')
            ->where('bank_account_id', $bankAccount?->id)
            ->latest('created_at');

        if (! empty($type) && in_array($type, ['deposit', 'withdrawal'], true)) {
            $query->where('type', $type);
        }

        if (! empty($search)) {
            $digitsOnly = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $digitsOnly) {
                if (! empty($digitsOnly)) {
                    $q->where('id', 'like', "%{$digitsOnly}%");
                }
                $q->orWhereHas('teller', function ($t) use ($search) {
                    $t->where('name', 'like', "%{$search}%");
                });
            });
        }

        if (! empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->paginate(10)->withQueryString();

        return view('customer.transactions', compact(
            'customer',
            'bankAccount',
            'transactions',
            'type',
            'search',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display the Digital Bank Card & QR Code for Teller Identification.
     */
    public function qrCode(): View
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        return view('customer.qr_code', compact(
            'customerAccount',
            'customer',
            'bankAccount'
        ));
    }

    /**
     * Display the Account Security & Credential Management View.
     */
    public function security(): View
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        return view('customer.security', compact(
            'customerAccount',
            'customer',
            'bankAccount'
        ));
    }

    /**
     * Handle Customer Password Update.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->authService->changePassword(
                $customerAccount,
                $request->input('current_password'),
                $request->input('new_password')
            );

            return back()->with('success', 'Password akun Anda berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
    }

    /**
     * Handle Customer PIN Update.
     */
    public function updatePin(Request $request): RedirectResponse
    {
        $request->validate([
            'current_pin' => ['required', 'string', 'digits:6'],
            'new_pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ], [
            'current_pin.required' => 'PIN saat ini wajib diisi.',
            'current_pin.digits' => 'PIN saat ini harus 6 digit angka.',
            'new_pin.required' => 'PIN baru wajib diisi.',
            'new_pin.digits' => 'PIN baru harus 6 digit angka.',
            'new_pin.confirmed' => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->authService->changePin(
                $customerAccount,
                $request->input('current_pin'),
                $request->input('new_pin')
            );

            return back()->with('success', 'PIN otorisasi transaksi Anda berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['current_pin' => $e->getMessage()]);
        }
    }

    /**
     * Authorize a pending Withdrawal Request via Customer's own device with PIN (BR-039, BR-040, DD-006).
     */
    public function authorizeWithdrawal(Request $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'digits:6'],
        ], [
            'pin.required' => 'PIN otorisasi 6 digit wajib dimasukkan.',
            'pin.digits' => 'PIN otorisasi harus terdiri dari 6 digit angka.',
        ]);

        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $bankAccount = $customerAccount->customer->bankAccount;

        // Verify account ownership
        if (! $bankAccount || $withdrawalRequest->bank_account_id !== $bankAccount->id) {
            return back()->with('error', 'Permintaan penarikan ini bukan milik rekening Anda.');
        }

        // Verify status
        if ($withdrawalRequest->status !== 'waiting') {
            return back()->with('error', 'Permintaan penarikan ini sudah tidak dalam status menunggu otorisasi.');
        }

        // Verify expiration
        if ($withdrawalRequest->expires_at && $withdrawalRequest->expires_at->isPast()) {
            $withdrawalRequest->update(['status' => 'expired']);
            return back()->with('error', 'Permintaan penarikan ini telah kedaluwarsa.');
        }

        // Verify Bank Account Active
        if ($bankAccount->status === 'inactive') {
            return back()->with('error', 'Rekening Anda sedang tidak aktif dan tidak dapat digunakan untuk transaksi.');
        }

        // Validate PIN
        if (! Hash::check($request->input('pin'), $customerAccount->pin)) {
            return back()->withErrors(['pin' => 'PIN otorisasi salah. Silakan coba kembali.']);
        }

        // Validate Balance and Minimum balance rule (BR-044)
        $minimumBalance = 10000;
        if (($bankAccount->balance - $withdrawalRequest->amount) < $minimumBalance) {
            return back()->with('error', 'Saldo tidak mencukupi untuk penarikan ini. Sisa saldo mengendap minimal Rp 10.000.');
        }

        try {
            DB::transaction(function () use ($bankAccount, $withdrawalRequest) {
                // 1. Decrement balance
                $bankAccount->decrement('balance', $withdrawalRequest->amount);

                // 2. Create transaction record
                $transaction = Transaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'teller_id' => $withdrawalRequest->teller_id,
                    'type' => 'withdrawal',
                    'amount' => $withdrawalRequest->amount,
                ]);

                // 3. Create accounting journal entries
                // Debit: Tabungan Nasabah (201)
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => '201',
                    'type' => 'debit',
                    'amount' => $withdrawalRequest->amount,
                ]);

                // Credit: Kas (101)
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => '101',
                    'type' => 'credit',
                    'amount' => $withdrawalRequest->amount,
                ]);

                // 4. Update withdrawal request status
                $withdrawalRequest->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            });

            return back()->with('success', 'Penarikan tunai sebesar Rp ' . number_format($withdrawalRequest->amount, 0, ',', '.') . ' berhasil disetujui. Teller dapat menyerahkan uang fisik kepada Anda.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses otorisasi penarikan: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending Withdrawal Request by the customer.
     */
    public function rejectWithdrawal(WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $bankAccount = $customerAccount->customer->bankAccount;

        // Verify account ownership
        if (! $bankAccount || $withdrawalRequest->bank_account_id !== $bankAccount->id) {
            return back()->with('error', 'Permintaan penarikan ini bukan milik rekening Anda.');
        }

        if ($withdrawalRequest->status !== 'waiting') {
            return back()->with('error', 'Permintaan penarikan ini sudah tidak dalam status menunggu otorisasi.');
        }

        $withdrawalRequest->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Permintaan penarikan tunai telah Anda tolak.');
    }
}
