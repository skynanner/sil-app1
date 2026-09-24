<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_unit_id',
        'fiscal_year',
        'account_code',
        'description',
        'total_amount',
        'committed_amount',
        'realized_amount',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'total_amount' => 'decimal:2',
            'committed_amount' => 'decimal:2',
            'realized_amount' => 'decimal:2',
        ];
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function travelRequests(): HasMany
    {
        return $this->hasMany(TravelRequest::class);
    }

    /**
     * Remaining balance = total_amount - committed_amount - realized_amount
     */
    protected function remainingBalance(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->total_amount - (float) $this->committed_amount - (float) $this->realized_amount,
        );
    }
}
