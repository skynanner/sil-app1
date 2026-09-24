<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardCost extends Model
{
    use HasFactory;

    public const TYPE_TRANSPORT = 'TRANSPORT';
    public const TYPE_HOTEL = 'HOTEL';
    public const TYPE_AIRFARE = 'AIRFARE';
    public const TYPE_DAILY_ALLOWANCE = 'DAILY_ALLOWANCE';
    public const TYPE_OTHER = 'OTHER';

    protected $fillable = [
        'cost_type',
        'name',
        'region_origin',
        'region_destination',
        'rank_group',
        'unit',
        'unit_amount',
        'fiscal_year',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'unit_amount' => 'decimal:2',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function costItems(): HasMany
    {
        return $this->hasMany(TravelCostItem::class);
    }
}
