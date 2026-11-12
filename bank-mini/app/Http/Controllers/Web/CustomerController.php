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
use App\Services\SimpleXlsxExportService;
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

        $transactions = $query->paginate(25)->withQueryString();

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
     * Handle Customer PIN Reset via Account Password Verification.
     */
    public function resetPinWithPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ], [
            'password.required' => 'Password akun wajib dimasukkan.',
            'new_pin.required' => 'PIN baru 6 digit wajib dimasukkan.',
            'new_pin.digits' => 'PIN baru harus terdiri dari 6 digit angka.',
            'new_pin.confirmed' => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();

        try {
            $this->authService->resetPinWithPassword(
                $customerAccount,
                $request->input('password'),
                $request->input('new_pin')
            );

            return back()->with('success', 'PIN otorisasi transaksi berhasil diperbarui menggunakan verifikasi password akun.');
        } catch (Exception $e) {
            return back()->withErrors(['pin_password' => $e->getMessage()]);
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

    /**
     * Cetak Laporan Mutasi Rekening Nasabah (Minimalist Print View).
     */
    public function printTransactions(Request $request): View
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Hitung Saldo Awal sebelum tanggal mulai
        $openingBalance = 0;
        if (!empty($startDate)) {
            $priorDeposits = Transaction::where('bank_account_id', $bankAccount?->id)
                ->where('type', 'deposit')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $priorWithdrawals = Transaction::where('bank_account_id', $bankAccount?->id)
                ->where('type', 'withdrawal')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $openingBalance = $priorDeposits - $priorWithdrawals;
        }

        $query = Transaction::with('teller')
            ->where('bank_account_id', $bankAccount?->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($type) && in_array($type, ['deposit', 'withdrawal'], true)) {
            $query->where('type', $type);
        }
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        $runningBalance = $openingBalance;
        $totalDeposit = 0;
        $totalWithdrawal = 0;

        $items = $transactions->map(function ($t) use (&$runningBalance, &$totalDeposit, &$totalWithdrawal) {
            if ($t->type === 'deposit') {
                $runningBalance += $t->amount;
                $totalDeposit += $t->amount;
            } else {
                $runningBalance -= $t->amount;
                $totalWithdrawal += $t->amount;
            }
            $t->running_balance = $runningBalance;
            return $t;
        });

        $closingBalance = $runningBalance;
        $periodLabel = (!empty($startDate) && !empty($endDate))
            ? date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate))
            : (!empty($startDate) ? 'Mulai ' . date('d/m/Y', strtotime($startDate)) : (!empty($endDate) ? 'Sampai ' . date('d/m/Y', strtotime($endDate)) : 'Semua Periode'));

        return response()
            ->view('customer.print_transactions', compact(
                'customer',
                'bankAccount',
                'items',
                'openingBalance',
                'closingBalance',
                'totalDeposit',
                'totalWithdrawal',
                'startDate',
                'endDate',
                'periodLabel',
                'type'
            ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
    }

    /**
     * Export Mutasi Rekening Nasabah ke File Excel (.xlsx OpenXML format).
     */
    public function exportTransactionsExcel(Request $request)
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;
        $bankAccount = $customer->bankAccount;

        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $openingBalance = 0;
        if (!empty($startDate)) {
            $priorDeposits = Transaction::where('bank_account_id', $bankAccount?->id)
                ->where('type', 'deposit')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $priorWithdrawals = Transaction::where('bank_account_id', $bankAccount?->id)
                ->where('type', 'withdrawal')
                ->whereDate('created_at', '<', $startDate)
                ->sum('amount');
            $openingBalance = $priorDeposits - $priorWithdrawals;
        }

        $query = Transaction::with('teller')
            ->where('bank_account_id', $bankAccount?->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($type) && in_array($type, ['deposit', 'withdrawal'], true)) {
            $query->where('type', $type);
        }
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        $accountNo = $bankAccount?->account_number ?? 'Rekening';
        $filename = 'Mutasi_' . $accountNo . '_' . date('Ymd_His') . '.xlsx';

        $periodLabel = (!empty($startDate) && !empty($endDate))
            ? date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate))
            : (!empty($startDate) ? 'Mulai ' . date('d/m/Y', strtotime($startDate)) : (!empty($endDate) ? 'Sampai ' . date('d/m/Y', strtotime($endDate)) : 'Semua Periode'));

        $metaPairs = [
            [
                'L_label' => 'Nama Nasabah',
                'L_val' => $customer->name,
                'R_label' => 'No. Rekening',
                'R_val' => $bankAccount?->account_number ?? '-',
                'R_is_acc' => true,
            ],
            [
                'L_label' => 'NIS / Kelas',
                'L_val' => ($customer->nis ?? '-') . ' / ' . ($customer->class ?? '') . ' ' . ($customer->class_group ?? ''),
                'R_label' => 'Periode Mutasi',
                'R_val' => $periodLabel,
            ],
            [
                'L_label' => 'Tanggal Export',
                'L_val' => date('d/m/Y H:i:s'),
                'R_label' => 'Status Rekening',
                'R_val' => $bankAccount?->status === 'active' ? 'Aktif' : 'Nonaktif',
            ],
        ];

        $headers = ['No', 'Tanggal & Waktu', 'ID Transaksi', 'Tipe Transaksi', 'Setoran (Rp)', 'Penarikan (Rp)', 'Saldo Akhir (Rp)', 'Petugas Teller'];

        $rows = [];
        $rows[] = [
            ['v' => '-', 's' => 8],
            ['v' => $startDate ? date('d/m/Y 00:00', strtotime($startDate)) : '-', 's' => 8],
            ['v' => 'SALDO-AWAL', 's' => 7],
            ['v' => 'Saldo Awal Periode', 's' => 7],
            ['v' => 0, 's' => 9],
            ['v' => 0, 's' => 9],
            ['v' => $openingBalance, 's' => 10],
            ['v' => 'Sistem', 's' => 8],
        ];

        $runningBalance = $openingBalance;
        $totalDeposit = 0;
        $totalWithdrawal = 0;
        $no = 1;

        foreach ($transactions as $t) {
            $isDeposit = ($t->type === 'deposit');
            if ($isDeposit) {
                $runningBalance += $t->amount;
                $totalDeposit += $t->amount;
                $creditVal = (int) $t->amount;
                $debitVal = 0;
            } else {
                $runningBalance -= $t->amount;
                $totalWithdrawal += $t->amount;
                $creditVal = 0;
                $debitVal = (int) $t->amount;
            }

            $rows[] = [
                ['v' => $no++, 's' => 8],
                ['v' => $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-', 's' => 8],
                ['v' => $t->code, 's' => 7],
                ['v' => $isDeposit ? 'Setoran Tunai' : 'Penarikan Tunai', 's' => 7],
                ['v' => $creditVal, 's' => 9],
                ['v' => $debitVal, 's' => 9],
                ['v' => $runningBalance, 's' => 10],
                ['v' => $t->teller?->name ?? '-', 's' => 8],
            ];
        }

        $summary = [
            ['v' => 'TOTAL PERIODE', 's' => 11],
            ['v' => '', 's' => 11],
            ['v' => '', 's' => 11],
            ['v' => '', 's' => 11],
            ['v' => $totalDeposit, 's' => 12],
            ['v' => $totalWithdrawal, 's' => 12],
            ['v' => $runningBalance, 's' => 12],
            ['v' => '-', 's' => 11],
        ];

        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        SimpleXlsxExportService::create($tempPath, $metaPairs, $headers, $rows, $summary);

        $downloadHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ];

        return response()->download($tempPath, $filename, $downloadHeaders)->deleteFileAfterSend(true);
    }

    /**
     * Nasabah menyetujui (Approve) pengajuan penonaktifan rekening oleh Admin.
     */
    public function approveDeactivation(Request $request): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;

        if (!$customer->deactivation_requested_at) {
            return redirect()->route('customer.dashboard')->with('error', 'Tidak ada permintaan penonaktifan yang aktif.');
        }

        app(\App\Services\CustomerService::class)->approveDeactivation($customer);

        // Logout customer session
        auth()->guard('customer_web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Rekening Anda telah resmi dinonaktifkan sesuai persetujuan Anda.');
    }

    /**
     * Nasabah menolak pengajuan penonaktifan rekening oleh Admin.
     */
    public function rejectDeactivation(Request $request): RedirectResponse
    {
        /** @var \App\Models\CustomerAccount $customerAccount */
        $customerAccount = auth()->guard('customer_web')->user();
        $customer = $customerAccount->customer;

        if (!$customer->deactivation_requested_at) {
            return redirect()->route('customer.dashboard')->with('error', 'Tidak ada permintaan penonaktifan yang aktif.');
        }

        app(\App\Services\CustomerService::class)->rejectDeactivation($customer);

        return redirect()->route('customer.dashboard')->with('success', 'Permintaan penonaktifan rekening berhasil ditolak. Rekening Anda tetap aktif.');
    }
}
