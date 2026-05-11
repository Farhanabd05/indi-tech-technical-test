<?php

namespace App\Policies;

use App\Models\User;

class LabelPolicy // Lakukan hal yang sama untuk PriorityPolicy dan SlaRulePolicy
{
    /**
     * Hanya administrator yang bisa mengelola (CRUD) data master.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    /**
     * Staf perlu melihat data ini untuk memproses tiket.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['administrator', 'supervisor', 'agent']);
    }
}