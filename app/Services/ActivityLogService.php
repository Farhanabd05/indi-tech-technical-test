<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\ActivityLog;
use App\Enums\ActivityLogAction;

class ActivityLogService
{
    /**
     * Mencatat aktivitas tiket.
     *
     * @param Ticket $ticket
     * @param mixed $user Entitas pengguna
     * @param ActivityLogAction $action
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public static function log(Ticket $ticket, ?User $user, ActivityLogAction $action, $oldValue = null, $newValue = null): void
    {
        // Buat sebuah logika sebelum baris ActivityLog::create untuk mengubah data mixed tersebut menjadi teks biasa. (Petunjuk: Anda bisa memeriksa apakah data tersebut adalah objek tipe BackedEnum menggunakan instanceof \BackedEnum untuk mengambil nilai ->value-nya, atau menggunakan json_encode jika datanya berbentuk array).
        if ($oldValue instanceof \BackedEnum) {
            $oldValue = $oldValue->value;
        } elseif (is_array($oldValue)) {
            $oldValue = json_encode($oldValue);
        }

        if ($newValue instanceof \BackedEnum) {
            $newValue = $newValue->value;
        } elseif (is_array($newValue)) {
            $newValue = json_encode($newValue);
        }

        ActivityLog::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user ? $user->id : null,
            'action'      => $action->value,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
        ]);
    }
}
