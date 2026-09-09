<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'account_number',
        'balance',
        'status',
        'qr_code',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getMajorCodeAttribute(): ?string
    {
        $parts = explode('-', (string) $this->account_number);
        $validMajors = ['AK', 'BR', 'BD', 'MP', 'DKV', 'RPL', 'TKJ'];

        if (count($parts) >= 2 && in_array(strtoupper($parts[0]), $validMajors, true)) {
            return strtoupper($parts[0]);
        }

        if ($this->customer && $this->customer->class) {
            if (preg_match('/\b(AK|BR|BD|MP|DKV|RPL|TKJ)\b/i', $this->customer->class, $matches)) {
                return strtoupper($matches[1]);
            }
        }

        return null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }
}
