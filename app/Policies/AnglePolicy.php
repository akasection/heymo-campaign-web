<?php

namespace App\Policies;

use App\Models\Angle;
use App\Models\Brand;
use App\Models\User;

class AnglePolicy
{
    public function viewAny(User $user, Brand $brand): bool
    {
        return $this->canManageOwnedBrand($user, $brand);
    }

    public function view(User $user, Angle $angle): bool
    {
        return $this->canManageOwnedBrand($user, $angle->brand);
    }

    public function create(User $user, Brand $brand): bool
    {
        return $this->canManageOwnedBrand($user, $brand);
    }

    public function update(User $user, Angle $angle): bool
    {
        return $this->canManageOwnedBrand($user, $angle->brand);
    }

    public function delete(User $user, Angle $angle): bool
    {
        return $this->canManageOwnedBrand($user, $angle->brand);
    }

    public function restore(User $user, Angle $angle): bool
    {
        return $this->canManageOwnedBrand($user, $angle->brand);
    }

    private function canManageOwnedBrand(User $user, Brand $brand): bool
    {
        return $user->canManageBackoffice()
            && $user->organization_id !== null
            && (int) $user->organization_id === (int) $brand->organization_id;
    }
}
