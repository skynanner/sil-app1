<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelCostItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculation_id',
        'personnel_id',
        'standard_cost_id',
        'description',
        'quantity',
        'unit_amount',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(TravelCostCalculation::class, 'calculation_id');
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(TravelRequestPersonnel::class, 'personnel_id');
    }

    public function standardCost(): BelongsTo
    {
        return $this->belongsTo(StandardCost::class);
    }
}
