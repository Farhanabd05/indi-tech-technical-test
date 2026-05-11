<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use App\Enums\TicketStatus;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SupervisorDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // Mendapatkan daftar ID agen yang berada dalam tim yang dipimpin oleh penyelia
        $agentIds = User::where('team_id', Auth::user()->team_id)
            ->where('role_id', Role::where('slug', 'agent')->first()->id)
            ->pluck('id')
            ->toArray();

        // Kueri dasar untuk tiket yang ditugaskan kepada agen dalam tim penyelia
        $queryBase = Ticket::whereIn('assigned_agent_id', $agentIds);

        $totalTickets = (clone $queryBase)->count();

        // Diubah menjadi camelCase agar standar
        $openTickets = (clone $queryBase) 
            ->where('status', TicketStatus::OPEN)
            ->count();

        $overdueTickets = (clone $queryBase)
            ->overdue()
            ->count();

        $escalatedTickets = (clone $queryBase)
            ->where('status', TicketStatus::ESCALATED)
            ->count();

        $agentWorkload = (clone $queryBase)
            ->selectRaw('assigned_agent_id, count(*) as total')
            ->whereNotIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])
            ->groupBy('assigned_agent_id')
            ->with('assignedAgent') // Eager loading the assignedAgent relationship
            ->get();

        // Menarik tiket terselesaikan tanpa groupBy di SQL agar semua tiket terambil
        $resolvedTickets = (clone $queryBase)
            ->whereIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])
            ->whereNotNull('resolved_at')
            ->get();

        // Menghitung waktu resolusi rata-rata PER AGEN menggunakan Laravel Collection
        $averageResolutionTime = $resolvedTickets->groupBy('assigned_agent_id')
            ->map(function ($group) {
                $totalDuration = $group->sum(function ($ticket) {
                    return Carbon::parse($ticket->resolved_at)->diffInSeconds(Carbon::parse($ticket->created_at));
                });
                $count = $group->count();
                return $count > 0 ? $totalDuration / $count : 0;
            })
            ->toArray();

        // Melempar data ke antarmuka pengguna
        return view('dashboard.supervisor', compact(
            'totalTickets',
            'openTickets',
            'overdueTickets',
            'escalatedTickets',
            'agentWorkload',
            'averageResolutionTime'
        ));
    }
}