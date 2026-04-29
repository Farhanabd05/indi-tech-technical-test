<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function generateTicketNumber(): string
    {
        $prefix = 'TCK';
        $year = date('Y');
        
        // Mulai transaksi untuk memastikan konsistensi data
        return DB::transaction(function () use ($prefix, $year) {
            // Ambil nomor urut terakhir untuk tahun ini dengan kunci eksklusif
            $lastTicket = Ticket::whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderBy('created_at', 'desc')
                ->first();

            // Hitung nomor urut berikutnya
            $nextNumber = $lastTicket ? ((int) Str::afterLast($lastTicket->ticket_number, '-')) + 1 : 1;

            // Format nomor tiket sesuai spesifikasi
            return sprintf('%s-%s-%06d', $prefix, $year, $nextNumber);
        });
    }

    public function calculateDueDate(int $priorityId): \Illuminate\Support\Carbon
    {
        return match ($priorityId) {
            1 => now()->addDays(5), // Low
            2 => now()->addDays(3), // Medium
            3 => now()->addDay(), // High
            4 => now()->addHours(8), // Critical
            default => now()->addDays(14), // Default untuk prioritas tidak dikenal
        };

    }
}