<?php

namespace App\Policies;

use App\Models\TreasuryOfficial;
use App\Models\User;
use App\Traits\ChecksRole;

class TreasuryOfficialPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function view(User $user, TreasuryOfficial $official): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, TreasuryOfficial $official): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, TreasuryOfficial $official): bool
    {
        return $this->isAdmin($user);
    }
}
