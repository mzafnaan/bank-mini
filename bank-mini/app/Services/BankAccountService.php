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
}
