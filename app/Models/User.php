<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'role_id',
        'username',
        'password_hash',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function travelRequests(): HasMany
    {
        return $this->hasMany(TravelRequest::class, 'submitted_by');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'verifier_id');
    }

    public function requestStatusHistories(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class, 'changed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function travelReports(): HasMany
    {
        return $this->hasMany(TravelReport::class, 'submitted_by');
    }

    public function documentTemplates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'created_by');
    }

    public function approvedCalculations(): HasMany
    {
        return $this->hasMany(TravelCostCalculation::class, 'approved_by');
    }

    public function resolvedBlocks(): HasMany
    {
        return $this->hasMany(EmployeeBlock::class, 'resolved_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->code === Role::ADMIN;
    }

    public function isPPKVerifier(): bool
    {
        return $this->role?->code === Role::PPK_VERIFIER;
    }

    public function isPPSPMVerifier(): bool
    {
        return $this->role?->code === Role::PPSPM_VERIFIER;
    }

    public function isUser(): bool
    {
        return $this->role?->code === Role::USER;
    }
}
