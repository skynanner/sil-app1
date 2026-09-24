<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Verification extends Model
{
    use HasFactory;

    public const STAGE_PPK = 'PPK';
    public const STAGE_PPSPM = 'PPSPM';

    public const DECISION_ACCEPTED = 'ACCEPTED';
    public const DECISION_REJECTED = 'REJECTED';

    protected $fillable = [
        'travel_request_id',
        'verifier_id',
        'stage',
        'decision',
        'notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function travelRequest(): BelongsTo
    {
        return $this->belongsTo(TravelRequest::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
