<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerAccount;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public function __construct(
        protected ?BankAccountService $bankAccountService = null
    ) {
        $this->bankAccountService = $bankAccountService ?? app(BankAccountService::class);
    }

    public function getAllCustomers(?string $search = null, int $perPage = 25): LengthAwarePaginator
    {
        $query = Customer::with(['bankAccount', 'customerAccount', 'academicYear'])->orderBy('name', 'asc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('class', 'like', "%{$search}%")
                  ->orWhere('class_group', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('bankAccount', fn($bq) => $bq->where('account_number', 'like', "%{$search}%"))
                  ->orWhereHas('customerAccount', fn($cq) => $cq->where('username', 'like', "%{$search}%"));
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Complete Customer Onboarding within a single Database Transaction.
     * Creates: Customer + BankAccount (balance=0) + CustomerAccount (Web & Mobile login).
     */
    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            // Determine active academic year
            $activeAcademicYearId = $data['academic_year_id'] ?? \App\Models\AcademicYear::where('is_active', true)->value('id');

            // Parse class and class_group
            $rawClass = trim($data['class']);
            $class = $rawClass;
            $classGroup = ! empty($data['class_group']) ? trim($data['class_group']) : null;
            if (! $classGroup && preg_match('/^(XII|XI|X)\s+(.+)$/i', $rawClass, $matches)) {
                $class = strtoupper($matches[1]);
                $classGroup = trim($matches[2]);
            }

            // 1. Create Customer identity record
            $customer = Customer::create([
                'nis' => trim($data['nis']),
                'name' => trim($data['name']),
                'class' => $class,
                'class_group' => $classGroup,
                'academic_year_id' => $activeAcademicYearId,
                'status' => $data['status'] ?? 'active',
                'phone' => ! empty($data['phone']) ? trim($data['phone']) : null,
                'email' => null,
                'email_verified_at' => null,
                'pending_email' => ! empty($data['email']) ? strtolower(trim($data['email'])) : null,
            ]);

            // Create initial customer class history if academic year is set
            if ($customer->academic_year_id) {
                \App\Models\CustomerClassHistory::firstOrCreate([
                    'customer_id' => $customer->id,
                    'academic_year_id' => $customer->academic_year_id,
                ], [
                    'class' => $customer->class,
                    'class_group' => $customer->class_group,
                ]);
            }

            // Determine Major Code (AK, BR, BD, MP, DKV, RPL, TKJ)
            $major = ! empty($data['major']) ? strtoupper(trim($data['major'])) : null;
            $validMajors = ['AK', 'BR', 'BD', 'MP', 'DKV', 'RPL', 'TKJ'];

            if (! $major || ! in_array($major, $validMajors, true)) {
                $combinedClass = trim($customer->class . ' ' . ($customer->class_group ?? ''));
                if (preg_match('/\b(AK|BR|BD|MP|DKV|RPL|TKJ)\b/i', $combinedClass, $matches)) {
                    $major = strtoupper($matches[1]);
                } else {
                    $major = 'RPL';
                }
            }

            // 2. Auto-generate Bank Account [KODE JURUSAN]-[NOMOR URUT 3 DIGIT]
            $accountNumber = $this->bankAccountService->generateAccountNumber($major);
            $qrCode = $accountNumber;
            $initialDeposit = ! empty($data['initial_deposit']) ? (float) $data['initial_deposit'] : 0;

            $bankAccount = BankAccount::create([
                'customer_id' => $customer->id,
                'account_number' => $accountNumber,
                'balance' => $initialDeposit,
                'status' => 'active',
                'qr_code' => $qrCode,
            ]);

            // If initial deposit is provided, record deposit transaction and journal entries
            if ($initialDeposit > 0) {
                // Cash deposit must be attributed to an operational Teller cash drawer (BR-011, BR-048)
                $tellerId = ! empty($data['teller_id'])
                    ? (int) $data['teller_id']
                    : (User::where('role', 'teller')->where('status', 'active')->orderBy('id')->value('id')
                        ?? User::where('role', 'teller')->orderBy('id')->value('id')
                        ?? (auth()->id() ?? User::where('role', 'administrator')->value('id') ?? 1));

                $transaction = Transaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'teller_id' => $tellerId,
                    'type' => 'deposit',
                    'amount' => $initialDeposit,
                ]);

                // Journal: Debit Kas (101), Credit Tabungan Nasabah (201)
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => '101',
                    'type' => 'debit',
                    'amount' => $initialDeposit,
                ]);

                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => '201',
                    'type' => 'credit',
                    'amount' => $initialDeposit,
                ]);
            }

            // 3. Auto-create Customer Account for Web & Mobile API Login
            $username = strtolower(trim($customer->nis));
            
            // Secure temporary password & PIN unique per customer (hashed)
            $temporaryPassword = 'Bk' . $customer->nis . '!';
            $temporaryPin = substr(str_pad((string) abs(crc32($customer->nis)), 6, '0', STR_PAD_LEFT), 0, 6);

            CustomerAccount::create([
                'customer_id' => $customer->id,
                'username' => $username,
                'password' => Hash::make($temporaryPassword),
                'pin' => Hash::make($temporaryPin),
                'first_login' => true,
                'status' => 'active',
            ]);

            $customer->load(['bankAccount', 'customerAccount']);
            $customer->plain_password = $temporaryPassword;
            $customer->plain_pin = $temporaryPin;

            return $customer;
        });
    }

    public function updateCustomer(Customer $customer, array $data): bool
    {
        $rawClass = trim($data['class']);
        $class = $rawClass;
        $classGroup = array_key_exists('class_group', $data) ? (! empty($data['class_group']) ? trim($data['class_group']) : null) : $customer->class_group;
        if (! $classGroup && preg_match('/^(XII|XI|X)\s+(.+)$/i', $rawClass, $matches)) {
            $class = strtoupper($matches[1]);
            $classGroup = trim($matches[2]);
        }

        $updateData = [
            'nis' => trim($data['nis']),
            'name' => trim($data['name']),
            'class' => $class,
            'class_group' => $classGroup,
            'phone' => isset($data['phone']) ? trim($data['phone']) : $customer->phone,
        ];

        if (array_key_exists('academic_year_id', $data)) {
            $updateData['academic_year_id'] = $data['academic_year_id'] ?: null;
        }

        if (array_key_exists('status', $data) && ! empty($data['status'])) {
            // Business Rule: Jika admin ingin menonaktifkan nasabah yang sedang aktif,
            // perlu persetujuan (approve) dari nasabah terlebih dahulu (tidak langsung nonaktif).
            if ($data['status'] === 'inactive' && $customer->status === 'active') {
                $updateData['deactivation_requested_at'] = now();
                $updateData['deactivation_reason'] = !empty($data['deactivation_reason'])
                    ? trim($data['deactivation_reason'])
                    : 'Pengajuan penonaktifan rekening oleh Administrator.';
            } elseif ($data['status'] === 'active') {
                $updateData['status'] = 'active';
                $updateData['deactivation_requested_at'] = null;
                $updateData['deactivation_reason'] = null;
                $customer->bankAccount?->update(['status' => 'active']);
                $customer->customerAccount?->update(['status' => 'active']);
            } else {
                $updateData['status'] = $data['status'];
            }
        }

        if (array_key_exists('email', $data)) {
            $newEmail = ! empty($data['email']) ? strtolower(trim($data['email'])) : null;

            if (! $newEmail) {
                $updateData['email'] = null;
                $updateData['email_verified_at'] = null;
                $updateData['pending_email'] = null;
            } elseif (
                $newEmail !== strtolower((string) $customer->email)
                || ! $customer->email_verified_at
            ) {
                $updateData['pending_email'] = $newEmail;
            }
        }

        $updated = $customer->update($updateData);

        if ($updated && $customer->academic_year_id) {
            \App\Models\CustomerClassHistory::updateOrCreate([
                'customer_id' => $customer->id,
                'academic_year_id' => $customer->academic_year_id,
            ], [
                'class' => $customer->class,
                'class_group' => $customer->class_group,
            ]);
        }

        return $updated;
    }

    /**
     * Nasabah menyetujui permintaan penonaktifan akun oleh Admin.
     */
    public function approveDeactivation(Customer $customer): bool
    {
        return DB::transaction(function () use ($customer) {
            $customer->update([
                'status' => 'inactive',
                'deactivation_requested_at' => null,
            ]);
            $customer->bankAccount?->update(['status' => 'inactive']);
            $customer->customerAccount?->update(['status' => 'inactive']);
            return true;
        });
    }

    /**
     * Nasabah menolak permintaan penonaktifan akun oleh Admin.
     */
    public function rejectDeactivation(Customer $customer): bool
    {
        return $customer->update([
            'deactivation_requested_at' => null,
            'deactivation_reason' => null,
        ]);
    }

    /**
     * Admin membatalkan pengajuan penonaktifan akun nasabah.
     */
    public function cancelDeactivationRequest(Customer $customer): bool
    {
        return $customer->update([
            'deactivation_requested_at' => null,
            'deactivation_reason' => null,
        ]);
    }

    /**
     * Buka Rekening & Buat Akun Login untuk nasabah yang sudah terdaftar (misal hasil import massal).
     * Nasabah yang di-import belum memiliki rekening dan akun login.
     * Saat admin membuka rekening:
     * - Dibuat BankAccount dengan saldo awal (default 0 atau sesuai input)
     * - Dibuat CustomerAccount (username = NIS, password = Bk{NIS}!, PIN = crc32 hash)
     * - Jika saldo awal > 0, dicatat Transaksi Deposit + Jurnal Debit Kas (101) & Credit Tabungan (201).
     */
    public function openAccountForCustomer(Customer $customer, array $data = []): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            // Check if customer already has a bank account
            if ($customer->bankAccount) {
                return $customer;
            }

            // Determine Major Code (AK, BR, BD, MP, DKV, RPL, TKJ)
            $major = ! empty($data['major']) ? strtoupper(trim($data['major'])) : null;
            $validMajors = ['AK', 'BR', 'BD', 'MP', 'DKV', 'RPL', 'TKJ'];

            if (! $major || ! in_array($major, $validMajors, true)) {
                $combinedClass = trim($customer->class . ' ' . ($customer->class_group ?? ''));
                if (preg_match('/\b(AK|BR|BD|MP|DKV|RPL|TKJ)\b/i', $combinedClass, $matches)) {
                    $major = strtoupper($matches[1]);
                } else {
                    $major = 'RPL';
                }
            }

            // 1. Auto-generate Bank Account [KODE JURUSAN]-[NOMOR URUT 3 DIGIT]
            $accountNumber = $this->bankAccountService->generateAccountNumber($major);
            $qrCode = $accountNumber;
            $initialDeposit = ! empty($data['initial_deposit']) ? (float) $data['initial_deposit'] : 0;

            $bankAccount = BankAccount::create([
                'customer_id' => $customer->id,
                'account_number' => $accountNumber,
                'balance' => $initialDeposit,
                'status' => 'active',
                'qr_code' => $qrCode,
            ]);

            // If initial deposit is provided, record deposit transaction and journal entries
            if ($initialDeposit > 0) {
                $tellerId = ! empty($data['teller_id'])
                    ? (int) $data['teller_id']
                    : (User::where('role', 'teller')->where('status', 'active')->orderBy('id')->value('id')
                        ?? User::where('role', 'teller')->orderBy('id')->value('id')
                        ?? (auth()->id() ?? User::where('role', 'administrator')->value('id') ?? 1));

                $transaction = Transaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'teller_id' => $tellerId,
                    'type' => 'deposit',
                    'amount' => $initialDeposit,
                ]);

                // Journal: Debit Kas (101), Credit Tabungan Nasabah (201)
                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => '101',
                    'type' => 'debit',
                    'amount' => $initialDeposit,
                ]);

                JournalEntry::create([
                    'transaction_id' => $transaction->id,
                    'account_code' => '201',
                    'type' => 'credit',
                    'amount' => $initialDeposit,
                ]);
            }

            // 2. Auto-create Customer Account for Web & Mobile API Login if not exists
            $temporaryPassword = 'Bk' . $customer->nis . '!';
            $temporaryPin = substr(str_pad((string) abs(crc32($customer->nis)), 6, '0', STR_PAD_LEFT), 0, 6);

            if (! $customer->customerAccount) {
                $username = strtolower(trim($customer->nis));
                CustomerAccount::create([
                    'customer_id' => $customer->id,
                    'username' => $username,
                    'password' => Hash::make($temporaryPassword),
                    'pin' => Hash::make($temporaryPin),
                    'first_login' => true,
                    'status' => 'active',
                ]);
            }

            // Update customer phone / pending_email if provided during account opening
            $customerUpdates = [];
            if (! empty($data['phone'])) {
                $customerUpdates['phone'] = trim($data['phone']);
            }
            if (! empty($data['email'])) {
                $customerUpdates['pending_email'] = strtolower(trim($data['email']));
            }
            if (! empty($customerUpdates)) {
                $customer->update($customerUpdates);
            }

            $customer->load(['bankAccount', 'customerAccount']);
            $customer->plain_password = $temporaryPassword;
            $customer->plain_pin = $temporaryPin;

            return $customer;
        });
    }
}
