<?php

namespace App\Policies;

use App\Models\StandardCost;
use App\Models\User;
use App\Traits\ChecksRole;

class StandardCostPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function view(User $user, StandardCost $standardCost): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, StandardCost $standardCost): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, StandardCost $standardCost): bool
    {
        return $this->isAdmin($user);
    }
}
