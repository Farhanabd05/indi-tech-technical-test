<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Http\Requests\Ticket\AssignTicketRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\TicketService;
use Illuminate\Support\Facades\Log;
class TicketAssignController extends Controller
{
    /**
     * Menangani penugasan agen ke tiket (Invokable).
     */
    public function __invoke(AssignTicketRequest $request, Ticket $ticket, TicketService $ticketService)
    {
        try {
            $ticketService->assignTicket($ticket, $request->validated()['assigned_agent_id'] ?? null, Auth::user());
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('An error occurred while assigning a ticket: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while assigning a ticket. Please try again later.'], 500);
        }
    }
}