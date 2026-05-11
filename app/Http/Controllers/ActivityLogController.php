<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        return view('activity_logs.index', compact('logs'));
    }
}
