<?php

namespace App\Policies;

use App\Models\BudgetAllocation;
use App\Models\User;
use App\Traits\ChecksRole;

class BudgetAllocationPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function view(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $this->isAdmin($user);
    }
}
