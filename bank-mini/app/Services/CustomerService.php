<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\CustomerAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    public function __construct(
        protected ?BankAccountService $bankAccountService = null
    ) {
        $this->bankAccountService = $bankAccountService ?? app(BankAccountService::class);
    }

    public function getAllCustomers(?string $search = null): Collection
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

        return $query->get();
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

            // 2. Auto-generate Bank Account [KODE JURUSAN]-[NOMOR URUT 3 DIGIT] (Balance strictly 0, status active)
            $accountNumber = $this->bankAccountService->generateAccountNumber($major);
            $qrCode = $accountNumber;

            BankAccount::create([
                'customer_id' => $customer->id,
                'account_number' => $accountNumber,
                'balance' => 0,
                'status' => 'active',
                'qr_code' => $qrCode,
            ]);

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
        return $customer->update([
            'nis' => trim($data['nis']),
            'name' => trim($data['name']),
            'class' => trim($data['class']),
            'phone' => isset($data['phone']) ? trim($data['phone']) : $customer->phone,
        ]);
    }
}
