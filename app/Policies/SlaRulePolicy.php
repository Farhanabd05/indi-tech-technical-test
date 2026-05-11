<?php

namespace App\Policies;

use App\Models\User;

class SlaRulePolicy 
{
    public function manage(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['administrator', 'supervisor', 'agent']);
    }
}