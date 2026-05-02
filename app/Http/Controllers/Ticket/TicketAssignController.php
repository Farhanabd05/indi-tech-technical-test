<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogService;
use App\Notifications\TicketAssignedNotification;
use App\Models\User;

class TicketAssignController extends Controller
{
    /**
     * Menangani penugasan agen ke tiket (Invokable).
     */
    /*
    Jangan lupa, buat juga kelas notifikasi baru bernama TicketAssignedNotification melalui Artisan, lalu sisipkan pemanggilan ActivityLogService dan pengiriman notifikasi di dalam TicketAssignController.
    */
    public function __invoke(AssignTicketRequest $request, Ticket $ticket)
    {
        // Data sudah tervalidasi dan user sudah terotorisasi
        $validated = $request->validated();

        $agentId = $validated['assigned_agent_id'] ?? null;

        // Update identitas agen penanggung jawab
        $ticket->update([
            'assigned_agent_id' => $agentId,
            'status' => $agentId ? TicketStatus::ASSIGNED : TicketStatus::OPEN,
        ]);
        // Panggil layanan pencatatan riwayat (Log)
        ActivityLogService::log(
            $ticket,
            Auth::user(),
            'assign_agent',
            null,
            $agentId
        );
        /*
        Pertanyaan reflektif untuk Anda: Tepat di bawah pemanggilan ActivityLogService pada pengontrol Anda, bagaimana Anda akan menuliskan satu baris perintah untuk memicu pengiriman notifikasi tersebut kepada agen? (Petunjuk: Anda bisa menarik data pengguna agen melalui metode relasi $ticket->assignedAgent—jika relasi tersebut sudah ada di model Ticket—atau menggunakan kueri User::find($validated['agent_id']), lalu menyambungkannya dengan metode ->notify(...)).
        */
        // pengiriman notifikasi ke agen yang ditugaskan
        $assignedAgent = $ticket->assignedAgent;
        if ($assignedAgent) {
            $assignedAgent->notify(new TicketAssignedNotification($ticket));
        }

        return redirect()->back();
    }
}
