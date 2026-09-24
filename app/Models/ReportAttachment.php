<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAttachment extends Model
{
    use HasFactory;

    public const TYPE_BOARDING_PASS = 'BOARDING_PASS';
    public const TYPE_TICKET = 'TICKET';
    public const TYPE_TRANSPORT_PROOF = 'TRANSPORT_PROOF';
    public const TYPE_HOTEL_INVOICE = 'HOTEL_INVOICE';
    public const TYPE_OTHER = 'OTHER';

    protected $fillable = [
        'travel_report_id',
        'attachment_type',
        'file_name',
        'file_path',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function travelReport(): BelongsTo
    {
        return $this->belongsTo(TravelReport::class);
    }
}
