<?php

namespace App\Policies;

use App\Models\TravelRequest;
use App\Models\User;
use App\Traits\ChecksRole;

class TravelRequestPolicy
{
    use ChecksRole;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TravelRequest $travelRequest): bool
    {
        if ($this->isAdmin($user) || $this->isVerifier($user)) {
            return true;
        }

        if ($travelRequest->submitted_by === $user->id) {
            return true;
        }

        if ($user->employee_id && $travelRequest->requestPersonnel()->where('employee_id', $user->employee_id)->exists()) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TravelRequest $travelRequest): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($travelRequest->submitted_by === $user->id) {
            return in_array($travelRequest->status, [
                TravelRequest::STATUS_WAITING_VERIFICATION,
                TravelRequest::STATUS_PROBLEM,
            ]);
        }

        return false;
    }

    public function delete(User $user, TravelRequest $travelRequest): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $travelRequest->submitted_by === $user->id && $travelRequest->status === TravelRequest::STATUS_WAITING_VERIFICATION;
    }
}
