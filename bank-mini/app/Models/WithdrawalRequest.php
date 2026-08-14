<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'bank_account_id',
        'teller_id',
        'amount',
        'status',
        'expires_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function teller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teller_id');
    }
}
