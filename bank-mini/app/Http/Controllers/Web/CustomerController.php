<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ChangePinRequest;
use App\Http\Requests\Customer\AuthorizeWithdrawalRequest;
use App\Http\Requests\Customer\UpdateEmailRequest;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Notifications\VerifyCustomerEmailNotification;
use App\Services\AuthService;
use App\Services\CustomerWithdrawalService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected CustomerWithdrawalService $withdrawalService
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

        // Active pending withdrawal request waiting for customer PIN authorization
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
     * Handle Customer Password Update via AuthService.
     */
    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->authService->changePassword(
                $customerAccount,
                $request->validated('current_password'),
                $request->validated('new_password')
            );

            return back()->with('success', 'Password akun Anda berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
    }

    /**
     * Handle Customer PIN Update via AuthService.
     */
    public function updatePin(ChangePinRequest $request): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->authService->changePin(
                $customerAccount,
                $request->validated('current_pin'),
                $request->validated('new_pin')
            );

            return back()->with('success', 'PIN otorisasi transaksi Anda berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withErrors(['current_pin' => $e->getMessage()]);
        }
    }

    /**
     * Authorize a pending Withdrawal Request using CustomerWithdrawalService.
     */
    public function authorizeWithdrawal(AuthorizeWithdrawalRequest $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->withdrawalService->authorize(
                $customerAccount,
                $withdrawalRequest,
                $request->validated('pin')
            );

            return back()->with('success', 'Penarikan tunai sebesar Rp ' . number_format($withdrawalRequest->amount, 0, ',', '.') . ' berhasil disetujui. Teller dapat menyerahkan uang fisik kepada Anda.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a pending Withdrawal Request using CustomerWithdrawalService.
     */
    public function rejectWithdrawal(WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->withdrawalService->reject($customerAccount, $withdrawalRequest);

            return back()->with('success', 'Permintaan penarikan tunai telah Anda tolak.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Request email verification / change for customer account.
     * Keeps current email active as recovery email until the new pending email is verified.
     */
    public function requestEmailVerification(UpdateEmailRequest $request): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $newEmail = strtolower(trim($request->validated('email')));

        $customer->update([
            'pending_email' => $newEmail,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'customer.email.verify',
            now()->addMinutes(60),
            ['customer' => $customer->id, 'email' => $newEmail]
        );

        Notification::route('mail', $newEmail)
            ->notify(new VerifyCustomerEmailNotification($verificationUrl, $customer->name));

        return back()->with('success', "Tautan verifikasi telah dikirim ke {$newEmail}. Silakan periksa inbox email Anda untuk mengaktifkannya.");
    }

    /**
     * Resend verification email for pending email address.
     */
    public function resendEmailVerification(): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;

        if (! $customer->pending_email) {
            return back()->withErrors(['email' => 'Tidak ada permintaan verifikasi email yang sedang tertunda.']);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'customer.email.verify',
            now()->addMinutes(60),
            ['customer' => $customer->id, 'email' => $customer->pending_email]
        );

        Notification::route('mail', $customer->pending_email)
            ->notify(new VerifyCustomerEmailNotification($verificationUrl, $customer->name));

        return back()->with('success', "Tautan verifikasi baru telah dikirimkan ulang ke {$customer->pending_email}.");
    }

    /**
     * Cancel pending email change request.
     */
    public function cancelPendingEmail(): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;

        $customer->update(['pending_email' => null]);

        return back()->with('success', 'Permintaan verifikasi email baru telah dibatalkan. Email aktif Anda tetap tidak berubah.');
    }

    /**
     * Verify email via signed URL and promote pending_email to active recovery email.
     */
    public function verifyEmail(Request $request, Customer $customer): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            if (auth()->guard('customer_web')->check()) {
                return redirect()->route('customer.security')->with('error', 'Tautan verifikasi tidak valid atau sudah kedaluwarsa.');
            }
            return redirect()->route('login')->with('error', 'Tautan verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        $email = $request->query('email');

        if (! $customer->pending_email || $customer->pending_email !== $email) {
            if (auth()->guard('customer_web')->check()) {
                return redirect()->route('customer.security')->with('error', 'Permintaan verifikasi email ini sudah tidak berlaku atau sudah diverifikasi.');
            }
            return redirect()->route('login')->with('error', 'Permintaan verifikasi email ini sudah tidak berlaku atau sudah diverifikasi.');
        }

        // Promote pending_email to active verified email
        $customer->update([
            'email' => $customer->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
        ]);

        if (auth()->guard('customer_web')->check()) {
            return redirect()->route('customer.security')
                ->with('success', "Alamat email {$customer->email} berhasil diverifikasi dan sekarang aktif sebagai email pemulihan akun Anda.");
        }

        return redirect()->route('login')
            ->with('success', "Alamat email {$customer->email} berhasil diverifikasi! Sekarang Anda dapat menggunakannya untuk pemulihan akun jika lupa password.");
    }
}
