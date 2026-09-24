<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    use HasFactory;

    public const TYPE_TRAVEL_REPORT = 'TRAVEL_REPORT';
    public const TYPE_ACCOUNTABILITY = 'ACCOUNTABILITY';

    protected $fillable = [
        'name',
        'document_type',
        'file_path',
        'version',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function travelReports(): HasMany
    {
        return $this->hasMany(TravelReport::class, 'template_id');
    }
}
