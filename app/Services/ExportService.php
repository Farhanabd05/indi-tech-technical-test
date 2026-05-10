<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class ExportService
{
    public function exportTicketsToCsv(Collection $tickets)
    {
        return response()->streamDownload(function () use ($tickets) {
            $file = fopen('php://output', 'w');

            // Header CSV (Ditambahkan kolom yang kurang dari audit: Nomor Tiket, Tenggat Waktu, Diselesaikan Pada)
            fputcsv($file, [
                'ID',
                'Nomor Tiket',
                'Judul',
                'Kategori',
                'Prioritas',
                'Agen',
                'Status',
                'Pembuat',
                'Tanggal Dibuat',
                'Tenggat Waktu',
                'Diselesaikan Pada',
            ]);
            
            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->id,
                    $ticket->ticket_number,
                    $ticket->title,
                    $ticket->category->name,
                    $ticket->priority->name,
                    $ticket->assignedAgent ? $ticket->assignedAgent->name : 'No Agent',
                    $ticket->status->label(),
                    $ticket->creator->name,
                    $ticket->created_at,
                    $ticket->due_at,
                    $ticket->resolved_at,
                ]);
            }
            fclose($file);
        }, 'tickets.csv', ['Content-Type' => 'text/csv'], 'attachment');
    }
}