<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'name',
        'class',
        'phone',
    ];

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    public function customerAccount(): HasOne
    {
        return $this->hasOne(CustomerAccount::class);
    }
}
