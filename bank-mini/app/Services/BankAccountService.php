<?php

namespace App\Services;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection;

class BankAccountService
{
    public function getAllAccounts(?string $search = null): Collection
    {
        $query = BankAccount::with(['customer.customerAccount'])->orderBy('account_number', 'asc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => 
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                  );
            });
        }

        return $query->get();
    }

    public function generateAccountNumber(string $major): string
    {
        $validMajors = ['AK', 'BR', 'BD', 'MP', 'DKV', 'RPL', 'TKJ'];
        $cleanMajor = strtoupper(trim($major));
        if (! in_array($cleanMajor, $validMajors, true)) {
            $cleanMajor = 'RPL';
        }

        // 1. Get next sequence based on DB auto-increment ID
        $maxId = BankAccount::lockForUpdate()->max('id') ?? 0;
        $nextNumber = $maxId + 1;

        // 2. Also inspect existing accounts matching standard code pattern to guarantee sequence continuity
        $allNumbers = BankAccount::pluck('account_number');
        foreach ($allNumbers as $accNum) {
            if (preg_match('/^(AK|BR|BD|MP|DKV|RPL|TKJ)-(\d+)$/i', $accNum, $m)) {
                $suffix = (int) $m[2];
                if ($suffix >= $nextNumber) {
                    $nextNumber = $suffix + 1;
                }
            }
        }

        // 3. Guarantee strictly unique account_number
        while (BankAccount::where('account_number', sprintf('%s-%03d', $cleanMajor, $nextNumber))->exists()) {
            $nextNumber++;
        }

        return sprintf('%s-%03d', $cleanMajor, $nextNumber);
    }

    public function toggleStatus(BankAccount $account): BankAccount
    {
        $newStatus = ($account->status === 'active') ? 'inactive' : 'active';
        $account->update(['status' => $newStatus]);
        return $account->fresh();
    }

    public function updateStatus(BankAccount $account, string $status): BankAccount
    {
        if (! in_array($status, ['active', 'inactive'], true)) {
            throw new \InvalidArgumentException('Status rekening tidak valid.');
        }

        $account->update(['status' => $status]);
        return $account->fresh();
    }
}
