<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->organization_id !== null
            && (int) $user->organization_id === (int) $campaign->brand?->organization_id;
    }
}
