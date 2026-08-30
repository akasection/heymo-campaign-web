<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageBackoffice();
    }

    public function view(User $user, Brand $brand): bool
    {
        return $this->canManageOwnedBrand($user, $brand);
    }

    public function create(User $user): bool
    {
        return $user->canManageBackoffice() && $user->organization_id !== null;
    }

    public function update(User $user, Brand $brand): bool
    {
        return $this->canManageOwnedBrand($user, $brand);
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $this->canManageOwnedBrand($user, $brand);
    }

    public function restore(User $user, Brand $brand): bool
    {
        return $this->canManageOwnedBrand($user, $brand);
    }

    private function canManageOwnedBrand(User $user, Brand $brand): bool
    {
        return $user->canManageBackoffice()
            && $user->organization_id !== null
            && (int) $user->organization_id === (int) $brand->organization_id;
    }
}
