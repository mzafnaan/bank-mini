<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerClassHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'academic_year_id',
        'class',
        'class_group',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function getFullClassAttribute(): string
    {
        return trim($this->class . ' ' . ($this->class_group ?? ''));
    }
}

