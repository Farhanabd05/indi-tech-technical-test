<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\User;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        $induk = $attachment->attachable;

        if ($induk instanceof Ticket) {
            return $user->can('view', $induk);
        }

    if ($induk instanceof Comment) {
        // jika ini catatan internal, gunakan gerbang viewInternalNotes
        if ($induk->is_internal) {
            return $user->can('viewInternalNotes', $induk->ticket);
        }
        // jika ini komentar publik biasa, cukup cek hak akses tiket
        return $user->can('view', $induk->ticket);
    }
 
        return false;
    }
}