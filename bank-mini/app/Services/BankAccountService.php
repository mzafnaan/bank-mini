<?php

namespace App\Services;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection;

class BankAccountService
{
    public function getAllAccounts(): Collection
    {
        return BankAccount::with(['customer.customerAccount'])->orderBy('account_number', 'asc')->get();
    }
}
