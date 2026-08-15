<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Collection;

class JournalService
{
    public function getAllJournals(): Collection
    {
        return JournalEntry::with(['transaction.bankAccount.customer', 'transaction.teller'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
