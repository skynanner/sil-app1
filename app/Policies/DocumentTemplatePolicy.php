<?php

namespace App\Policies;

use App\Models\DocumentTemplate;
use App\Models\User;
use App\Traits\ChecksRole;

class DocumentTemplatePolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view/download templates for reports
    }

    public function view(User $user, DocumentTemplate $template): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, DocumentTemplate $template): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, DocumentTemplate $template): bool
    {
        return $this->isAdmin($user);
    }
}
