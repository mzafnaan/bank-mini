<?php

namespace App\Services;

use App\Models\CustomerAccount;
use App\Models\JournalEntry;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerWithdrawalService
{
    /**
     * Authorize a pending withdrawal request using customer PIN.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function authorize(CustomerAccount $customerAccount, WithdrawalRequest $withdrawalRequest, string $pin): Transaction
    {
        $bankAccount = $customerAccount->customer?->bankAccount;

        // 1. Verify account ownership
        if (! $bankAccount || $withdrawalRequest->bank_account_id !== $bankAccount->id) {
            throw new Exception('Permintaan penarikan ini bukan milik rekening Anda.');
        }

        // 2. Verify status
        if ($withdrawalRequest->status !== 'waiting') {
            throw new Exception('Permintaan penarikan ini sudah tidak dalam status menunggu otorisasi.');
        }

        // 3. Verify expiration
        if ($withdrawalRequest->expires_at && $withdrawalRequest->expires_at->isPast()) {
            $withdrawalRequest->update(['status' => 'expired']);
            throw new Exception('Permintaan penarikan ini telah kedaluwarsa.');
        }

        // 4. Verify Bank Account is Active
        if ($bankAccount->status === 'inactive') {
            throw new Exception('Rekening Anda sedang tidak aktif dan tidak dapat digunakan untuk transaksi.');
        }

        // 5. Validate PIN
        if (! Hash::check($pin, $customerAccount->pin)) {
            throw ValidationException::withMessages([
                'pin' => 'PIN otorisasi salah. Silakan coba kembali.',
            ]);
        }

        // 6. Validate balance and minimum balance rule (Rp 10.000, BR-044)
        $minimumBalance = 10000;
        if (($bankAccount->balance - $withdrawalRequest->amount) < $minimumBalance) {
            throw new Exception('Saldo tidak mencukupi untuk penarikan ini. Sisa saldo mengendap minimal Rp 10.000.');
        }

        // 7. Execute Database Transaction
        return DB::transaction(function () use ($bankAccount, $withdrawalRequest) {
            // Decrement bank account balance
            $bankAccount->decrement('balance', $withdrawalRequest->amount);

            // Create withdrawal transaction
            $transaction = Transaction::create([
                'bank_account_id' => $bankAccount->id,
                'teller_id' => $withdrawalRequest->teller_id,
                'type' => 'withdrawal',
                'amount' => $withdrawalRequest->amount,
            ]);

            // Create accounting journal entries:
            // Debit: Tabungan Nasabah (201)
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => '201',
                'type' => 'debit',
                'amount' => $withdrawalRequest->amount,
            ]);

            // Credit: Kas Teller (101)
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_code' => '101',
                'type' => 'credit',
                'amount' => $withdrawalRequest->amount,
            ]);

            // Update withdrawal request status to approved
            $withdrawalRequest->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Reject a pending withdrawal request by the customer.
     *
     * @throws Exception
     */
    public function reject(CustomerAccount $customerAccount, WithdrawalRequest $withdrawalRequest): bool
    {
        $bankAccount = $customerAccount->customer?->bankAccount;

        // Verify account ownership
        if (! $bankAccount || $withdrawalRequest->bank_account_id !== $bankAccount->id) {
            throw new Exception('Permintaan penarikan ini bukan milik rekening Anda.');
        }

        // Verify status
        if ($withdrawalRequest->status !== 'waiting') {
            throw new Exception('Permintaan penarikan ini sudah tidak dalam status menunggu otorisasi.');
        }

        return $withdrawalRequest->update([
            'status' => 'rejected',
        ]);
    }
}
