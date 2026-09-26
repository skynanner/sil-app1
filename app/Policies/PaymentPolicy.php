<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Traits\ChecksRole;

class PaymentPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user) || $this->isVerifier($user) || $payment->travelRequest?->submitted_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user) || $this->isPPSPM($user);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->isAdmin($user);
    }
}
