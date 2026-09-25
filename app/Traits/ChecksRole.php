<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\User;

trait ChecksRole
{
    protected function isAdmin(User $user): bool
    {
        return $user->role?->code === Role::ADMIN;
    }

    protected function isPPK(User $user): bool
    {
        return $user->role?->code === Role::PPK_VERIFIER;
    }

    protected function isPPSPM(User $user): bool
    {
        return $user->role?->code === Role::PPSPM_VERIFIER;
    }

    protected function isVerifier(User $user): bool
    {
        return in_array($user->role?->code, [Role::PPK_VERIFIER, Role::PPSPM_VERIFIER], true);
    }

    protected function isUser(User $user): bool
    {
        return $user->role?->code === Role::USER;
    }
}
