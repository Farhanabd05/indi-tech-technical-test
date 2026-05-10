<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Http\Requests\Ticket\AssignTicketRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\TicketService;

class TicketAssignController extends Controller
{
    /**
     * Menangani penugasan agen ke tiket (Invokable).
     */
    public function __invoke(AssignTicketRequest $request, Ticket $ticket, TicketService $ticketService)
    {
        $ticketService->assignTicket($ticket, $request->validated()['assigned_agent_id'] ?? null, Auth::user());
        return redirect()->back();
    }
}
