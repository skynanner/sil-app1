<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Traits\ChecksRole;

class RolePolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return false; // Roles are fixed
    }

    public function update(User $user, Role $role): bool
    {
        return false;
    }

    public function delete(User $user, Role $role): bool
    {
        return false;
    }
}
