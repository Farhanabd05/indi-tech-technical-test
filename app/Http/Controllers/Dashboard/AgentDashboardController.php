<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Enums\TicketStatus;

/*
- **Agent dashboard**
  - Base query: `Ticket::where('assigned_agent_id', auth()->id())`.
  - Cards: assigned count, overdue assigned, in progress, waiting for customer.
  - Recently updated: assigned tickets ordered by `updated_at`.
  - Status distribution grouped by status.
  - Overdue query must exclude resolved/closed.

*/
class AgentDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // Implementasi logika untuk dasbor agen akan ditempatkan di sini
        $agentId = Auth::id(); // Mendapatkan ID agen yang sedang masuk

        // Kueri dasar untuk tiket yang ditugaskan kepada agen
        $queryBase = Ticket::where('assigned_agent_id', $agentId);

        // Menghitung total tiket yang ditugaskan kepada agen
        $totalTickets = (clone $queryBase)->count();

        // My overdue tickets, Overdue query must exclude resolved/closed.
        $overdueTickets = (clone $queryBase)
            ->overdue()
            ->whereNotIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])
            ->count();
        
        // in progress
        $inProgressTickets = (clone $queryBase)
            ->where('status', TicketStatus::IN_PROGRESS)
            ->count();

        // waiting for customer
        $waitingForCustomerTickets = (clone $queryBase)
            ->where('status', TicketStatus::WAITING_FOR_CUSTOMER)
            ->count();

        // Recently updated tickets
        $recentUpdates = (clone $queryBase)
            ->latest('updated_at')
            ->limit(5)
            ->get();
            
        // Status distribution grouped by status
        $ticketsByStatus = (clone $queryBase)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();
        
        // Melempar data ke antarmuka pengguna
        return view('dashboard.agent', compact(
            'totalTickets',
            'overdueTickets',
            'inProgressTickets',
            'waitingForCustomerTickets',
            'ticketsByStatus',
            'recentUpdates'
        ));
    }
}
