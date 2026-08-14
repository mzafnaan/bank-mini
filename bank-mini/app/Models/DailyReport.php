<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'teller_id',
        'supervisor_id',
        'report_date',
        'opening_cash',
        'total_deposit',
        'total_withdrawal',
        'closing_cash',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function teller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
}
