<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAccount extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'username',
        'password',
        'pin',
        'first_login',
        'status',
    ];

    protected $hidden = [
        'password',
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'pin' => 'hashed',
            'first_login' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
