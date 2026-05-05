<?php

namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    // Base query: all tickets.
    public function __invoke()
    {
        $queryBase = Ticket::query();

        // Menghitung total tiket
        $totalTickets = (clone $queryBase)->count();

        // My overdue tickets
        $overdueTickets = (clone $queryBase)
            ->overdue()
            ->count();

        // Unassigned tickets
        $unassignedTickets = (clone $queryBase)
            ->whereNull('assigned_agent_id')
            ->count();

        // Tickets created this week
        $createdThisWeek = (clone $queryBase)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        
        // Groupings: status, priority, category.
        $ticketsByStatus = (clone $queryBase)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $ticketsByPriority = (clone $queryBase)
            ->with('priority')
            ->selectRaw('priority_id, count(*) as total')
            ->groupBy('priority_id')
            ->get();
        
        $ticketsByCategory = (clone $queryBase)
            ->with('category')
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->get();
        
        // Top agents
        $topAgents = (clone $queryBase)
            ->with('assignedAgent')
            ->selectRaw('assigned_agent_id, count(*) as total')
            ->whereNotNull('assigned_agent_id')
            ->where('status', TicketStatus::RESOLVED)
            ->groupBy('assigned_agent_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Average resolution: only tickets with `resolved_at`.
        $averageResolution = (clone $queryBase)
            ->whereNotNull('resolved_at')
            ->get()
            ->avg(function ($ticket) {
                $resolvedAt = Carbon::parse($ticket->resolved_at);
                return $ticket->created_at->diffInHours($resolvedAt);
            });

        
        return view('dashboard.admin', compact(
            'totalTickets',
            'overdueTickets',
            'unassignedTickets',
            'createdThisWeek',
            'ticketsByStatus',
            'ticketsByPriority',
            'ticketsByCategory',
            'topAgents',
            'averageResolution'
        ));
    }
}
