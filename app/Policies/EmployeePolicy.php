<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Traits\ChecksRole;

class EmployeePolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->isAdmin($user);
    }
}
