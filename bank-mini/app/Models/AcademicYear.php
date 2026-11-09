<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(CustomerClassHistory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPeriodAttribute(): string
    {
        $start = $this->start_date ? $this->start_date->format('d M Y') : '-';
        $end = $this->end_date ? $this->end_date->format('d M Y') : '-';
        return "{$start} s/d {$end}";
    }
}

