<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /*
    Sebagai langkah pendahuluan sebelum merakit antarmukanya, jika Anda diminta untuk membuat metode index di dalam sebuah berkas app/Http/Controllers/ActivityLogController.php baru, bagaimana Anda akan menyusun kueri menggunakan ActivityLog::with(['ticket', 'user']) yang secara otomatis memfilter log berdasarkan team_id khusus jika pengguna yang sedang login adalah seorang penyelia?
    */
    public function index()
    {
        if (Auth::user()->role->slug == 'administrator') {
            $logs = ActivityLog::with(['ticket', 'user'])->orderBy('created_at', 'desc')->get();
        }

        if (Auth::user()->role->slug == 'supervisor') {
            // 1. Kumpulkan daftar ID agen dalam satu tim penyelia
            $agentIds = User::where('team_id', Auth::user()->team_id)
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
                        ->orderBy('created_at', 'desc')->get();
        }
        return view('activity_logs.index', compact('logs'));
    }
}
