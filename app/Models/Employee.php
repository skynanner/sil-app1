<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'nik',
        'full_name',
        'rank_grade',
        'position',
        'work_unit_id',
        'is_active',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'imported_at' => 'datetime',
        ];
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function treasuryOfficials(): HasMany
    {
        return $this->hasMany(TreasuryOfficial::class);
    }

    public function travelRequestPersonnel(): HasMany
    {
        return $this->hasMany(TravelRequestPersonnel::class);
    }

    public function travelRequests(): HasManyThrough
    {
        return $this->hasManyThrough(
            TravelRequest::class,
            TravelRequestPersonnel::class,
            'employee_id',
            'id',
            'id',
            'travel_request_id'
        );
    }

    public function travelMonitoring(): HasMany
    {
        return $this->hasMany(TravelMonitoring::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(EmployeeBlock::class);
    }

    public function activeBlock(): HasOne
    {
        return $this->hasOne(EmployeeBlock::class)->whereIn('status', [EmployeeBlock::STATUS_ACTIVE, EmployeeBlock::STATUS_TGR_PROCESS]);
    }

    public function isBlocked(): bool
    {
        return $this->blocks()
            ->whereIn('status', [EmployeeBlock::STATUS_ACTIVE, EmployeeBlock::STATUS_TGR_PROCESS])
            ->exists();
    }
}
