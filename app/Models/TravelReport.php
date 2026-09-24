<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_OVERDUE = 'OVERDUE';

    protected $fillable = [
        'travel_request_id',
        'template_id',
        'submitted_by',
        'report_file_path',
        'due_date',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }
}
