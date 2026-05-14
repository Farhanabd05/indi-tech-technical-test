<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;

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

        $averageResolutionTime = (clone $queryBase)
            ->selectRaw('assigned_agent_id, AVG(' . $this->resolutionMinutesExpression() . ') as average_resolution_minutes')
            ->whereIn('status', [TicketStatus::RESOLVED, TicketStatus::CLOSED])
            ->whereNotNull('resolved_at')
            ->groupBy('assigned_agent_id')
            ->pluck('average_resolution_minutes', 'assigned_agent_id')
            ->map(fn ($minutes) => (float) $minutes)
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

    private function resolutionMinutesExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'mysql', 'mariadb' => '(TIMESTAMPDIFF(MINUTE, created_at, resolved_at) - COALESCE(total_paused_duration_minutes, 0))',
            'pgsql' => '((EXTRACT(EPOCH FROM (resolved_at - created_at)) / 60) - COALESCE(total_paused_duration_minutes, 0))',
            'sqlsrv' => '(DATEDIFF(minute, created_at, resolved_at) - COALESCE(total_paused_duration_minutes, 0))',
            default => "(((strftime('%s', resolved_at) - strftime('%s', created_at)) / 60.0) - COALESCE(total_paused_duration_minutes, 0))",
        };
    }
}
