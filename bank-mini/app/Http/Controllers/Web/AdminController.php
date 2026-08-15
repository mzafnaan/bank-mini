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
use App\Services\BankAccountService;
use App\Services\CustomerService;
use App\Services\JournalService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected CustomerService $customerService,
        protected BankAccountService $bankAccountService,
        protected JournalService $journalService
    ) {}

    /**
     * Admin Dashboard Overview.
     */
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => Customer::count(),
            'total_accounts' => BankAccount::count(),
            'total_balance' => BankAccount::sum('balance'),
        ];

        $recentJournals = JournalEntry::with(['transaction.bankAccount.customer', 'transaction.teller'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentJournals'));
    }

    /**
     * User Management.
     */
    public function users(): View
    {
        $users = $this->userService->getAllUsers();
        return view('admin.users', compact('users'));
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
    public function customers(): View
    {
        $customers = $this->customerService->getAllCustomers();
        return view('admin.customers', compact('customers'));
    }

    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());
            return redirect()->route('admin.customers')->with(
                'success',
                "Nasabah '{$customer->name}' berhasil didaftarkan. Rekening ({$customer->bankAccount->account_number}) dan Akun Mobile siap digunakan."
            );
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
     * Account Management List.
     */
    public function accounts(): View
    {
        $accounts = $this->bankAccountService->getAllAccounts();
        return view('admin.accounts', compact('accounts'));
    }

    /**
     * Accounting Audit.
     */
    public function journals(): View
    {
        $journals = $this->journalService->getAllJournals();
        return view('admin.journals', compact('journals'));
    }
}
