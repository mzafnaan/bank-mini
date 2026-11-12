<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessClassPromotionRequest;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\User;
use App\Notifications\VerifyCustomerEmailNotification;
use App\Services\AcademicYearService;
use App\Services\AuthService;
use App\Services\BankAccountService;
use App\Services\ClassPromotionService;
use App\Services\CustomerImportService;
use App\Services\CustomerService;
use App\Services\JournalService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        protected AuthService $authService,
        protected AcademicYearService $academicYearService,
        protected ClassPromotionService $classPromotionService,
        protected CustomerImportService $customerImportService
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
        $perPage = (int) $request->input('per_page', 10);
        $users = $this->userService->getAllUsers($search, $perPage);
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
        $perPage = (int) $request->input('per_page', 10);
        $customers = $this->customerService->getAllCustomers($search, $perPage);
        $tellers = User::where('role', 'teller')->where('status', 'active')->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.customers', compact('customers', 'search', 'tellers', 'academicYears'));
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
        $deactivationRequested = $customer->wasChanged('deactivation_requested_at') && $customer->deactivation_requested_at;
        $emailWarning = null;
        if ($emailChangedToPending) {
            try {
                $this->sendCustomerEmailVerification($customer);
            } catch (\Throwable) {
                $emailWarning = 'Data nasabah diperbarui, tetapi email verifikasi gagal dikirim. Periksa konfigurasi email, lalu minta nasabah mengirim ulang tautan dari dashboard.';
            }
        }

        if ($deactivationRequested) {
            $message = "Pengajuan penonaktifan akun nasabah '{$customer->name}' telah dikirim ke nasabah untuk disetujui (Approve).";
        } elseif ($emailChangedToPending) {
            $message = 'Data nasabah diperbarui. Email pemulihan baru aktif setelah diverifikasi.';
        } else {
            $message = 'Data nasabah berhasil diperbarui.';
        }

        $response = redirect()->route('admin.customers')->with('success', $message);
        return $emailWarning ? $response->with('error', $emailWarning) : $response;
    }

    public function cancelCustomerDeactivation(Customer $customer): RedirectResponse
    {
        $this->customerService->cancelDeactivationRequest($customer);
        return redirect()->route('admin.customers')->with('success', "Pengajuan penonaktifan nasabah '{$customer->name}' berhasil dibatalkan.");
    }

    /**
     * Download template file CSV untuk format import nasabah.
     */
    public function downloadImportTemplate(): Response
    {
        $csvContent = $this->customerImportService->generateTemplateCsv();
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_nasabah.csv"',
        ]);
    }

    /**
     * Import massal data siswa/nasabah dari file Excel (.xlsx) atau CSV (.csv).
     */
    public function importCustomers(Request $request): RedirectResponse
    {
        if (! $request->hasFile('file')) {
            $rawError = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($rawError === UPLOAD_ERR_INI_SIZE || $rawError === UPLOAD_ERR_FORM_SIZE) {
                return back()->withErrors(['file' => 'Ukuran file terlalu besar melebihi batas upload server.']);
            }
            return back()->withErrors(['file' => 'Silakan pilih file Excel (.xlsx) atau CSV (.csv) untuk diunggah.']);
        }

        $uploadedFile = $request->file('file');
        if (! $uploadedFile->isValid()) {
            $errorCode = $uploadedFile->getError();
            $errorMap = [
                UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas upload server (upload_max_filesize).',
                UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas form.',
                UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian. Silakan coba unggah kembali.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary server tidak ditemukan.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file sementara ke disk.',
                UPLOAD_ERR_EXTENSION => 'Upload file dihentikan oleh ekstensi PHP.',
            ];
            return back()->withErrors(['file' => $errorMap[$errorCode] ?? 'File gagal diunggah ke server (Error: ' . $errorCode . ').']);
        }

        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'auto_activate' => 'nullable',
        ]);

        $extension = $uploadedFile->getClientOriginalExtension();

        if (!in_array(strtolower($extension), ['csv', 'txt', 'xlsx', 'xls'])) {
            return back()->withErrors(['file' => 'Format file harus berupa Excel (.xlsx) atau CSV (.csv).']);
        }

        try {
            $parsedStudents = $this->customerImportService->parseFile($uploadedFile->getRealPath(), $extension);

            if (empty($parsedStudents)) {
                return back()->withErrors(['file' => 'Tidak ada data siswa/nasabah yang valid ditemukan dalam file. Pastikan format kolom sesuai.']);
            }

            $autoActivate = $request->boolean('auto_activate', false);
            $academicYearId = $request->filled('academic_year_id') ? (int) $request->academic_year_id : null;

            $result = $this->customerImportService->import($parsedStudents, $autoActivate, $academicYearId);

            $message = "Import berhasil: {$result['imported']} nasabah baru ditambahkan, {$result['updated']} diperbarui.";
            if ($result['skipped'] > 0) {
                $message .= " ({$result['skipped']} baris dilewati/kosong)";
            }

            if (!empty($result['errors'])) {
                return redirect()->route('admin.customers')->with('error', implode(' ', $result['errors']));
            }

            return redirect()->route('admin.customers')->with('success', $message);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal memproses file import: ' . $e->getMessage()]);
        }
    }

    /**
     * Buka Rekening & Aktifkan Login untuk nasabah yang di-import tanpa rekening.
     */
    public function openAccountForCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'major' => 'nullable|string|max:10',
            'initial_deposit' => 'nullable|numeric|min:0',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $customer = $this->customerService->openAccountForCustomer($customer, [
                'major' => $request->input('major'),
                'initial_deposit' => $request->input('initial_deposit', 0),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
            ]);

            if ($customer->pending_email) {
                try {
                    $this->sendCustomerEmailVerification($customer);
                } catch (\Throwable) {
                    // Abaikan jika SMTP belum dikonfigurasi
                }
            }

            return redirect()->route('admin.customers')->with([
                'success' => "Rekening untuk nasabah '{$customer->name}' berhasil dibuka dan akun login telah aktif.",
                'new_customer_credentials' => [
                    'name' => $customer->name,
                    'account_number' => $customer->bankAccount->account_number ?? '-',
                    'balance' => $customer->bankAccount->balance ?? 0,
                    'username' => $customer->customerAccount->username ?? strtolower($customer->nis),
                    'password' => $customer->plain_password ?? ('Bk' . $customer->nis . '!'),
                    'pin' => $customer->plain_pin ?? substr(str_pad((string) abs(crc32($customer->nis)), 6, '0', STR_PAD_LEFT), 0, 6),
                ],
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuka rekening nasabah: ' . $e->getMessage());
        }
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

    public function toggleAccountStatus(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        if (auth()->user()?->role !== 'administrator') {
            abort(403, 'Hanya Administrator yang dapat mengubah status rekening.');
        }

        // Jika diakses via GET (misal refresh browser), arahkan kembali dengan aman ke daftar rekening
        if ($request->isMethod('GET')) {
            return redirect()->route('admin.accounts');
        }

        // Jika rekening sedang aktif dan ingin dinonaktifkan, ajukan persetujuan ke nasabah
        if ($bankAccount->status === 'active') {
            $customer = $bankAccount->customer;
            if ($customer) {
                $customer->update([
                    'deactivation_requested_at' => now(),
                    'deactivation_reason' => 'Pengajuan penonaktifan rekening oleh Administrator.'
                ]);
                return redirect()->route('admin.accounts')
                    ->with('success', "Permintaan penonaktifan rekening {$bankAccount->account_number} telah dikirim ke dashboard nasabah untuk disetujui (Approve).");
            }
        }

        // Jika sedang nonaktif, aktifkan kembali langsung
        $this->bankAccountService->toggleStatus($bankAccount);
        $customer = $bankAccount->customer;
        if ($customer) {
            $customer->update([
                'status' => 'active',
                'deactivation_requested_at' => null,
                'deactivation_reason' => null,
            ]);
            $customer->customerAccount?->update(['status' => 'active']);
        }

        return redirect()->route('admin.accounts')
            ->with('success', "Rekening nasabah {$bankAccount->account_number} berhasil diaktifkan kembali.");
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

    /**
     * Academic Years Management (Manajemen Tahun Ajaran).
     */
    public function academicYears(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $academicYears = $this->academicYearService->getAllAcademicYears($search);
        $activeYear = $this->academicYearService->getActiveAcademicYear();
        $previousYears = $this->academicYearService->getPreviousAcademicYears();

        return view('admin.academic_years', compact('academicYears', 'activeYear', 'previousYears', 'search'));
    }

    public function storeAcademicYear(StoreAcademicYearRequest $request): RedirectResponse
    {
        $year = $this->academicYearService->createAcademicYear($request->validated());

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Tahun ajaran {$year->name} berhasil ditambahkan.");
    }

    public function activateAcademicYear(AcademicYear $academicYear): RedirectResponse
    {
        $this->academicYearService->activateAcademicYear($academicYear);

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Tahun ajaran {$academicYear->name} sekarang berstatus Aktif.");
    }

    /**
     * Class Promotion & Graduation (Kenaikan Kelas dan Kelulusan).
     */
    public function classPromotions(Request $request): View
    {
        $academicYears = $this->academicYearService->getAcademicYearList();
        $activeYear = $this->academicYearService->getActiveAcademicYear();

        $sourceYearId = $request->filled('source_academic_year_id')
            ? (int) $request->input('source_academic_year_id')
            : $activeYear?->id;

        $targetYearId = $request->filled('target_academic_year_id')
            ? (int) $request->input('target_academic_year_id')
            : null;

        $classLevel = $request->input('class_level');
        $search = trim($request->input('search', ''));
        $perPage = $request->input('per_page', 25) === 'all' ? 1000 : (int) $request->input('per_page', 25);

        $candidates = $this->classPromotionService->getPromotionCandidates([
            'source_academic_year_id' => $sourceYearId,
            'class_level' => $classLevel,
            'search' => $search,
        ], $perPage);

        $summary = $this->classPromotionService->getCandidateSummary($sourceYearId);
        $sourceYear = $sourceYearId ? AcademicYear::find($sourceYearId) : null;
        $targetYear = $targetYearId ? AcademicYear::find($targetYearId) : null;

        return view('admin.class_promotions', compact(
            'academicYears',
            'activeYear',
            'sourceYearId',
            'targetYearId',
            'sourceYear',
            'targetYear',
            'classLevel',
            'search',
            'candidates',
            'summary'
        ));
    }

    public function processClassPromotion(ProcessClassPromotionRequest $request): RedirectResponse
    {
        $targetYear = AcademicYear::findOrFail($request->input('target_academic_year_id'));
        $sourceYearId = $request->input('source_academic_year_id');
        $decisions = $request->input('decisions', []);

        try {
            $result = $this->classPromotionService->processPromotion($targetYear, $decisions, $sourceYearId ? (int) $sourceYearId : null);

            return redirect()->route('admin.class-promotions.index', [
                'source_academic_year_id' => $sourceYearId,
                'target_academic_year_id' => $targetYear->id,
            ])->with(
                'success',
                "Kenaikan kelas dan kelulusan berhasil diproses! Total {$result['total']} siswa ({$result['promoted']} naik kelas, {$result['stayed']} tinggal kelas, {$result['graduated']} lulus)."
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses kenaikan kelas: ' . $e->getMessage());
        }
    }
}
