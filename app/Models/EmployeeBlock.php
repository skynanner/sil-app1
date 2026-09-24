<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBlock extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_TGR_PROCESS = 'TGR_PROCESS';
    public const STATUS_RESOLVED = 'RESOLVED';

    protected $fillable = [
        'employee_id',
        'travel_request_id',
        'reason',
        'status',
        'tgr_reference',
        'blocked_at',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'blocked_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
