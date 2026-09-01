<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

class VisitorPolicy
{
    public function view(User $user, Visitor $visitor): bool
    {
        return $user->organization_id !== null
            && (int) $user->organization_id === (int) $visitor->brand?->organization_id;
    }
}
