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
    public function getAllCustomers(): Collection
    {
        return Customer::with(['bankAccount', 'customerAccount'])->orderBy('name', 'asc')->get();
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

            // 2. Auto-generate Bank Account (Balance strictly 0)
            $year = date('Y');
            $count = BankAccount::count() + 1;
            $accountNumber = sprintf('REK-%s%04d', $year, $count);
            $qrCode = 'QR-' . $accountNumber;

            BankAccount::create([
                'customer_id' => $customer->id,
                'account_number' => $accountNumber,
                'balance' => 0,
                'qr_code' => $qrCode,
            ]);

            // 3. Auto-create Customer Account for Web & Mobile API Login
            $username = strtolower(trim($customer->nis));
            
            // Secure temporary password & PIN unique per customer (hashed)
            $temporaryPassword = 'Bk' . $customer->nis . '!';
            $temporaryPin = str_pad((string) abs(crc32($customer->nis)), 6, '0', STR_PAD_LEFT);

            CustomerAccount::create([
                'customer_id' => $customer->id,
                'username' => $username,
                'password' => Hash::make($temporaryPassword),
                'pin' => Hash::make(substr($temporaryPin, 0, 6)),
                'first_login' => true,
                'status' => 'active',
            ]);

            return $customer->load(['bankAccount', 'customerAccount']);
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
