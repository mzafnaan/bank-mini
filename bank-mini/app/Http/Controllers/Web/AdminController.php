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
use App\Services\AuthService;
use App\Services\BankAccountService;
use App\Services\CustomerService;
use App\Services\JournalService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'total_accounts' => BankAccount::count(),
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
        $this->userService->updateUser($user, $request->validated());
        return redirect()->route('admin.users')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleUserStatus(User $user): RedirectResponse
    {
        $this->userService->toggleStatus($user);
        return redirect()->route('admin.users')->with('success', 'Status akun pengguna berhasil diperbarui.');
    }

    /**
     * Customer Management & Onboarding.
     */
    public function customers(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $customers = $this->customerService->getAllCustomers($search);
        return view('admin.customers', compact('customers', 'search'));
    }

    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());
            return redirect()->route('admin.customers')->with([
                'success' => "Nasabah '{$customer->name}' berhasil didaftarkan.",
                'new_customer_credentials' => [
                    'name' => $customer->name,
                    'account_number' => $customer->bankAccount->account_number ?? '-',
                    'username' => $customer->customerAccount->username ?? strtolower($customer->nis),
                    'password' => $customer->plain_password ?? ('Bk' . $customer->nis . '!'),
                    'pin' => $customer->plain_pin ?? substr(str_pad((string) abs(crc32($customer->nis)), 6, '0', STR_PAD_LEFT), 0, 6),
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal mendaftarkan nasabah: ' . $e->getMessage()]);
        }
    }

    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->updateCustomer($customer, $request->validated());
        return redirect()->route('admin.customers')->with('success', 'Data nasabah berhasil diperbarui.');
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
