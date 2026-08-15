<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Collection;

class JournalService
{
    public function getAllJournals(?string $search = null): Collection
    {
        $query = JournalEntry::with(['transaction.bankAccount.customer', 'transaction.teller'])
            ->orderBy('created_at', 'desc');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('transaction.bankAccount.customer', fn($cq) =>
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                  );
            });
        }

        return $query->get();
    }
}
