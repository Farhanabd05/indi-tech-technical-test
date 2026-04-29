<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\ActivityLog; // Asumsi model log
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Mencatat aktivitas tiket.
     *
     * @param Ticket $ticket
     * @param mixed $user Entitas pengguna
     * @param string $action Nama aksi
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public static function log(Ticket $ticket, ?User $user, string $action, $oldValue, $newValue): void
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

        try {
            // Struktur penyimpanan log
            ActivityLog::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $user ? $user->id : null,
                'action'      => $action,
                'old_value'   => $oldValue,
                'new_value'   => $newValue,
            ]);
        } catch (\Exception $e) {
            // Mekanisme Pertahanan: Log gagal, tapi aplikasi lanjut berjalan
            Log::error('Gagal menyimpan Activity Log: ' . $e->getMessage());
            
            // Opsional: Kirim notifikasi ke sistem pemantauan/admin
        }
    }
}
