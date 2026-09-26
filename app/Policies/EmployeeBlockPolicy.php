<?php

namespace App\Policies;

use App\Models\EmployeeBlock;
use App\Models\User;
use App\Traits\ChecksRole;

class EmployeeBlockPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function view(User $user, EmployeeBlock $block): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user) || $user->employee_id === $block->employee_id;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, EmployeeBlock $block): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, EmployeeBlock $block): bool
    {
        return $this->isAdmin($user);
    }
}
