<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    /**
     * Hanya administrator yang diizinkan mengelola data master.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    /**
     * Staf (Admin, Supervisor, Agent) perlu melihat kategori untuk memproses tiket.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['administrator', 'supervisor', 'agent']);
    }
}