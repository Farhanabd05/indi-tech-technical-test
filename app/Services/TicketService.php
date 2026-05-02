<?php

namespace App\Services;

use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\Ticket;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TicketService
{
    protected array $nextTicketNumbers = [];

    public function generateTicketNumber(): string
    {
        $prefix = 'TCK';
        $year = date('Y');
        
        // Mulai transaksi untuk memastikan konsistensi data
        return DB::transaction(function () use ($prefix, $year) {
            if (! isset($this->nextTicketNumbers[$year])) {
                // Ambil nomor urut terakhir untuk tahun ini dengan kunci eksklusif
                $lastTicket = Ticket::whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->orderBy('ticket_number', 'desc')
                    ->first();

                $this->nextTicketNumbers[$year] = $lastTicket
                    ? ((int) Str::afterLast($lastTicket->ticket_number, '-')) + 1
                    : 1;
            }

            $nextNumber = $this->nextTicketNumbers[$year]++;

            // Format nomor tiket sesuai spesifikasi
            return sprintf('%s-%s-%06d', $prefix, $year, $nextNumber);
        });
    }

    public function calculateDueDate(int $priorityId): \Illuminate\Support\Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            $priority = Priority::find($priorityId);
            $resolutionHours = match ($priority?->name) {
                'Low' => 120,
                'Medium' => 72,
                'High' => 24,
                'Critical' => 8,
                default => null,
            };

            if ($resolutionHours === null) {
                throw new Exception('SLA rule not found for the given priority.');
            }

            return now()->addHours($resolutionHours);
        }

        return now()->addHours($slaRule->resolution_hours);
    }
}
