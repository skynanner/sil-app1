<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TravelRequest extends Model
{
    use HasFactory;

    public const STATUS_WAITING_VERIFICATION = 'WAITING_VERIFICATION';
    public const STATUS_IN_PROCESS = 'IN_PROCESS';
    public const STATUS_PROBLEM = 'PROBLEM';
    public const STATUS_SP2D = 'SP2D';

    protected $fillable = [
        'sprint_number',
        'sprint_date',
        'activity_name',
        'activity_location',
        'activity_start_date',
        'activity_end_date',
        'budget_allocation_id',
        'submitted_by',
        'sprint_file_path',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'sprint_date' => 'date',
            'activity_start_date' => 'date',
            'activity_end_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function requestPersonnel(): HasMany
    {
        return $this->hasMany(TravelRequestPersonnel::class);
    }

    public function personnel()
    {
        return $this->belongsToMany(Employee::class, 'travel_request_personnel')
            ->withPivot(['activity_start_date', 'activity_end_date'])
            ->withTimestamps();
    }

    public function costCalculation(): HasOne
    {
        return $this->hasOne(TravelCostCalculation::class);
    }

    public function costItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            TravelCostItem::class,
            TravelCostCalculation::class,
            'travel_request_id',
            'calculation_id'
        );
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class)->orderByDesc('changed_at');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function travelReports(): HasMany
    {
        return $this->hasMany(TravelReport::class);
    }

    public function monitoringRecords(): HasMany
    {
        return $this->hasMany(TravelMonitoring::class);
    }

    public function employeeBlocks(): HasMany
    {
        return $this->hasMany(EmployeeBlock::class);
    }
}
