<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'ADMIN';
    public const PPK_VERIFIER = 'PPK_VERIFIER';
    public const PPSPM_VERIFIER = 'PPSPM_VERIFIER';
    public const USER = 'USER';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
