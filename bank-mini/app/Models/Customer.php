<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory;

    public ?string $plain_password = null;
    public ?string $plain_pin = null;

    protected $fillable = [
        'nis',
        'name',
        'class',
        'class_group',
        'academic_year_id',
        'status',
        'phone',
        'email',
        'email_verified_at',
        'pending_email',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    public function customerAccount(): HasOne
    {
        return $this->hasOne(CustomerAccount::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(CustomerClassHistory::class)->orderBy('academic_year_id', 'desc');
    }

    public function isActive(): bool
    {
        return in_array(strtolower((string) $this->status), ['active', 'aktif'], true);
    }

    public function isGraduated(): bool
    {
        return in_array(strtolower((string) $this->status), ['inactive', 'nonaktif', 'lulus', 'graduated'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->isActive() ? 'Aktif' : 'Nonaktif (Lulus)';
    }

    public function getFullClassAttribute(): string
    {
        return trim($this->class . ' ' . ($this->class_group ?? ''));
    }
}
