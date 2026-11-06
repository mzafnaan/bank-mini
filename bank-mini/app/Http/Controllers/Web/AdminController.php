<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\User;
use App\Notifications\VerifyCustomerEmailNotification;
use App\Services\AuthService;
use App\Services\BankAccountService;
use App\Services\CustomerService;
use App\Services\JournalService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected CustomerService $customerService,
        protected BankAccountService $bankAccountService,
        protected JournalService $journalService,
        protected AuthService $authService
    ) {}

    /**
     * Admin Dashboard Overview.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));

        $stats = [
            'total_users' => User::count(),
            'total_customers' => Customer::count(),
            'total_accounts' => BankAccount::where('status', 'active')->count(),
            'total_all_accounts' => BankAccount::count(),
            'total_balance' => BankAccount::sum('balance'),
        ];

        $searchResults = null;
        if ($search !== '') {
            $searchResults = [
                'customers' => $this->customerService->getAllCustomers($search),
                'users' => $this->userService->getAllUsers($search),
                'accounts' => $this->bankAccountService->getAllAccounts($search),
                'journals' => $this->journalService->getAllJournals($search),
            ];
        }

        $recentJournals = JournalEntry::with(['transaction.bankAccount.customer', 'transaction.teller'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentJournals', 'search', 'searchResults'));
    }

    /**
     * User Management.
     */
    public function users(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $users = $this->userService->getAllUsers($search);
        return view('admin.users', compact('users', 'search'));
    }

    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());
        return redirect()->route('admin.users')->with('success', 'Pengguna internal berhasil ditambahkan.');
    }

    public function updateUser(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if ($user->id === auth()->id() && ($data['status'] ?? null) === 'inactive') {
            return back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $this->userService->updateUser($user, $data);
        return redirect()->route('admin.users')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleUserStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $this->userService->toggleStatus($user);
        return redirect()->route('admin.users')->with('success', 'Status akun pengguna berhasil diperbarui.');
    }

    /**
     * Admin-initiated password reset for internal users (staff).
     * Generates a secure random temporary password and revokes any active tokens.
     */
    public function resetUserPassword(User $user): RedirectResponse
    {
        try {
            $temporaryPassword = $this->authService->adminResetUserPassword($user);

            return redirect()->route('admin.users')->with([
                'success' => "Password pengguna '{$user->name}' berhasil direset.",
                'reset_password_credentials' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role,
                    'password' => $temporaryPassword,
                ],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer Management & Onboarding.
     */
    public function customers(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $customers = $this->customerService->getAllCustomers($search);
        $tellers = User::where('role', 'teller')->where('status', 'active')->orderBy('name')->get();

        return view('admin.customers', compact('customers', 'search', 'tellers'));
    }

    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());
            $emailWarning = null;
            if ($customer->pending_email) {
                try {
                    $this->sendCustomerEmailVerification($customer);
                } catch (\Throwable) {
                    $emailWarning = 'Nasabah berhasil didaftarkan, tetapi email verifikasi gagal dikirim. Periksa konfigurasi email, lalu minta nasabah mengirim ulang tautan dari dashboard.';
                }
            }

            $response = redirect()->route('admin.customers')->with([
                'success' => $customer->pending_email
                    ? "Nasabah '{$customer->name}' berhasil didaftarkan. Email pemulihan baru aktif setelah diverifikasi."
                    : "Nasabah '{$customer->name}' berhasil didaftarkan.",
                'new_customer_credentials' => [
                    'name' => $customer->name,
                    'account_number' => $customer->bankAccount->account_number ?? '-',
                    'balance' => $customer->bankAccount->balance ?? 0,
                    'username' => $customer->customerAccount->username ?? strtolower($customer->nis),
                    'password' => $customer->plain_password ?? ('Bk' . $customer->nis . '!'),
                    'pin' => $customer->plain_pin ?? substr(str_pad((string) abs(crc32($customer->nis)), 6, '0', STR_PAD_LEFT), 0, 6),
                ],
            ]);
            return $emailWarning ? $response->with('error', $emailWarning) : $response;
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal mendaftarkan nasabah: ' . $e->getMessage()]);
        }
    }

    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->updateCustomer($customer, $request->validated());
        $emailChangedToPending = $customer->wasChanged('pending_email') && $customer->pending_email;
        $emailWarning = null;
        if ($emailChangedToPending) {
            try {
                $this->sendCustomerEmailVerification($customer);
            } catch (\Throwable) {
                $emailWarning = 'Data nasabah diperbarui, tetapi email verifikasi gagal dikirim. Periksa konfigurasi email, lalu minta nasabah mengirim ulang tautan dari dashboard.';
            }
        }

        $message = $emailChangedToPending
            ? 'Data nasabah diperbarui. Email pemulihan baru aktif setelah diverifikasi.'
            : 'Data nasabah berhasil diperbarui.';
        $response = redirect()->route('admin.customers')->with('success', $message);
        return $emailWarning ? $response->with('error', $emailWarning) : $response;
    }

    private function sendCustomerEmailVerification(Customer $customer): void
    {
        $email = $customer->pending_email;
        $verificationUrl = URL::temporarySignedRoute(
            'customer.email.verify',
            now()->addMinutes(60),
            ['customer' => $customer->id, 'email' => $email]
        );

        Notification::route('mail', $email)
            ->notify(new VerifyCustomerEmailNotification($verificationUrl, $customer->name));
    }

    /**
     * Admin-initiated password reset for customer accounts.
     * Generates a secure temporary password and forces the customer to change it on next login.
     */
    public function resetCustomerPassword(Customer $customer): RedirectResponse
    {
        $customerAccount = $customer->customerAccount;

        if (! $customerAccount) {
            return back()->with('error', 'Nasabah ini belum memiliki akun login.');
        }

        try {
            $temporaryPassword = $this->authService->adminResetCustomerPassword($customerAccount);

            return redirect()->route('admin.customers')->with([
                'success' => "Password akun nasabah '{$customer->name}' berhasil direset.",
                'reset_password_credentials' => [
                    'name' => $customer->name,
                    'nis' => $customer->nis,
                    'username' => $customerAccount->username,
                    'password' => $temporaryPassword,
                ],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Account Management List.
     */
    public function accounts(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $accounts = $this->bankAccountService->getAllAccounts($search);
        return view('admin.accounts', compact('accounts', 'search'));
    }

    public function toggleAccountStatus(BankAccount $bankAccount): RedirectResponse
    {
        if (auth()->user()?->role !== 'administrator') {
            abort(403, 'Hanya Administrator yang dapat mengubah status rekening.');
        }

        $this->bankAccountService->toggleStatus($bankAccount);

        $statusText = $bankAccount->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.accounts')
            ->with('success', "Rekening nasabah {$bankAccount->account_number} berhasil {$statusText}.");
    }

    /**
     * Accounting Audit.
     */
    public function journals(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $journals = $this->journalService->getAllJournals($search);
        return view('admin.journals', compact('journals', 'search'));
    }
}
