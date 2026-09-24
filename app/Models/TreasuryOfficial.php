<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreasuryOfficial extends Model
{
    use HasFactory;

    public const TYPE_PPK = 'PPK';
    public const TYPE_PPSPM = 'PPSPM';
    public const TYPE_TREASURER = 'TREASURER';

    protected $fillable = [
        'employee_id',
        'official_type',
        'decree_number',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
