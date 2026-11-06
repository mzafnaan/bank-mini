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
        $query = Customer::with(['bankAccount', 'customerAccount'])->orderBy('name', 'asc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('class', 'like', "%{$search}%")
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
            // 1. Create Customer identity record
            $customer = Customer::create([
                'nis' => trim($data['nis']),
                'name' => trim($data['name']),
                'class' => trim($data['class']),
                'phone' => ! empty($data['phone']) ? trim($data['phone']) : null,
                'email' => null,
                'email_verified_at' => null,
                'pending_email' => ! empty($data['email']) ? strtolower(trim($data['email'])) : null,
            ]);

            // Determine Major Code (AK, BR, BD, MP, DKV, RPL, TKJ)
            $major = ! empty($data['major']) ? strtoupper(trim($data['major'])) : null;
            $validMajors = ['AK', 'BR', 'BD', 'MP', 'DKV', 'RPL', 'TKJ'];

            if (! $major || ! in_array($major, $validMajors, true)) {
                if (preg_match('/\b(AK|BR|BD|MP|DKV|RPL|TKJ)\b/i', $customer->class, $matches)) {
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
        $updateData = [
            'nis' => trim($data['nis']),
            'name' => trim($data['name']),
            'class' => trim($data['class']),
            'phone' => isset($data['phone']) ? trim($data['phone']) : $customer->phone,
        ];

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

        return $customer->update($updateData);
    }
}
