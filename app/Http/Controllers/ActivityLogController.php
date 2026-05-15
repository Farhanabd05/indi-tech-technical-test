<?php

namespace App\Http\Controllers;

use App\Enums\ActivityLogAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('administrator')) {
            $logs = ActivityLog::with(['ticket', 'user'])->orderBy('created_at', 'desc')->paginate(10);
        }

        if ($user->hasRole('supervisor')) {
            // 1. Kumpulkan daftar ID agen dalam satu tim penyelia
            $agentIds = User::where('team_id', $user->team_id)
                            ->whereHas('role', function ($q) {
                                $q->where('slug', 'agent');
                            })
                            ->pluck('id');

            // 2. Saring log aktivitas dengan menembus relasi 'ticket' menggunakan whereHas
            $logs = ActivityLog::with(['ticket', 'user'])
                        ->whereHas('ticket', function ($query) use ($agentIds) {
                            // Berikan kondisi agar assigned_agent_id termasuk dalam kumpulan $agentIds
                            $query->whereIn('assigned_agent_id', $agentIds);
                        })
                        ->orderBy('created_at', 'desc')->paginate(10);
        }

        $this->loadTargetUsersForAssignmentLogs($logs);

        return view('activity_logs.index', compact('logs'));
    }

    private function loadTargetUsersForAssignmentLogs($logs): void
    {
        $assignmentActions = [
            ActivityLogAction::ASSIGN_TICKET->value,
            ActivityLogAction::REASSIGN_TICKET->value,
        ];

        $targetUserIds = $logs->getCollection()
            ->filter(fn (ActivityLog $log) => in_array($log->action, $assignmentActions, true) && ctype_digit((string) $log->new_value))
            ->pluck('new_value')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $targetUsers = $targetUserIds->isEmpty()
            ? collect()
            : User::whereIn('id', $targetUserIds)->get()->keyBy('id');

        $logs->getCollection()->each(function (ActivityLog $log) use ($assignmentActions, $targetUsers) {
            $targetUser = null;

            if (in_array($log->action, $assignmentActions, true) && ctype_digit((string) $log->new_value)) {
                $targetUser = $targetUsers->get((int) $log->new_value);
            }

            $log->setRelation('targetUser', $targetUser);
        });
    }
}
