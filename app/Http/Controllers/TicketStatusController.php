<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\TicketResolvedNotification;
use App\Notifications\TicketEscalatedNotification;
use App\Enums\TicketStatus;

class TicketStatusController extends Controller
{
    public function update(UpdateTicketStatusRequest $request, Ticket $ticket)
    {
        // 1. Amankan data status lama ke dalam variabel sementara
        $oldStatus = $ticket->status;

        // 2. Ambil status baru dari request yang sudah divalidasi
        $newStatus = $request->validated()['status'];

        // 3. Ubah atribut tiket
        $ticket->status = $newStatus;

        // 4. Eksekusi penyimpanan ke pangkalan data
        $ticket->save();

        // 5. Panggil layanan pencatatan riwayat (Log)
        ActivityLogService::log(
            $ticket,
            Auth::user(),
            'update_status',
            $oldStatus,
            $newStatus
        );

        $kumpulanSupervisorAdmin = User::whereHas('role', function ($query) {
            $query->whereIn('slug', ['administrator', 'supervisor']);
        })->get();
        match ($ticket->status) {
            TicketStatus::RESOLVED => $ticket->creator->notify(new TicketResolvedNotification($ticket)),
            TicketStatus::ESCALATED => Notification::send($kumpulanSupervisorAdmin, new TicketEscalatedNotification($ticket)),
            default => null,
        };
        return response()->json(['message' => 'Status tiket berhasil diperbarui']);
    }
}

