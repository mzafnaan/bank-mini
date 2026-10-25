<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'teller_id');
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class, 'teller_id');
    }

    public function dailyReportsAsTeller(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'teller_id');
    }

    public function dailyReportsAsSupervisor(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'supervisor_id');
    }

    /**
     * Send the password reset notification with custom template.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
