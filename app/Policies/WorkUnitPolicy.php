<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkUnit;
use App\Traits\ChecksRole;

class WorkUnitPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, WorkUnit $workUnit): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, WorkUnit $workUnit): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, WorkUnit $workUnit): bool
    {
        return $this->isAdmin($user);
    }
}
