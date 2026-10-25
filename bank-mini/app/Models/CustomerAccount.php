<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;

class CustomerAccount extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    /**
     * Get the email address used for password reset.
     * Email is stored on the related customers table, not on customer_accounts.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->customer->email ?? '';
    }

    /**
     * Route notifications for the mail channel.
     * Directs password reset emails to the customer's verified email.
     */
    public function routeNotificationForMail($notification = null): ?string
    {
        return $this->customer->email;
    }

    /**
     * Send the password reset notification with custom template.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}

