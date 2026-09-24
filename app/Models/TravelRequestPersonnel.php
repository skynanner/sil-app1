<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelRequestPersonnel extends Model
{
    use HasFactory;

    protected $table = 'travel_request_personnel';

    protected $fillable = [
        'travel_request_id',
        'employee_id',
        'activity_start_date',
        'activity_end_date',
    ];

    protected function casts(): array
    {
        return [
            'activity_start_date' => 'date',
            'activity_end_date' => 'date',
        ];
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function costItems(): HasMany
    {
        return $this->hasMany(TravelCostItem::class, 'personnel_id');
    }
}
