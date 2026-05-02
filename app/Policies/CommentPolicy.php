<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewInternal(User $user, Comment $comment): bool
    {
        return $user->hasRole(['administrator', 'supervisor', 'agent']);
    }
}
