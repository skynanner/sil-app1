<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelMonitoring extends Model
{
    use HasFactory;

    protected $table = 'travel_monitoring';

    public const REPORT_STATUS_PENDING = 'PENDING';
    public const REPORT_STATUS_SUBMITTED = 'SUBMITTED';
    public const REPORT_STATUS_OVERDUE = 'OVERDUE';

    protected $fillable = [
        'employee_id',
        'travel_request_id',
        'period_month',
        'period_year',
        'activity_start_date',
        'activity_end_date',
        'approved_amount',
        'report_status',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'activity_start_date' => 'date',
            'activity_end_date' => 'date',
            'approved_amount' => 'decimal:2',
            'recorded_at' => 'datetime',
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
}
