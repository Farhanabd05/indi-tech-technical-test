<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Enums\TicketStatus;

class CustomerDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $customerId = Auth::id(); // Mendapatkan ID pelanggan yang sedang masuk

        // Kueri dasar untuk tiket pelanggan sesuai spesifikasi
        $queryBase = Ticket::where('created_by', $customerId); 

        // Menghitung total tiket milik pelanggan dengan kloning
        $totalTickets = (clone $queryBase)->count();

        // Open tickets
        $openTickets = (clone $queryBase)
            ->where('status', TicketStatus::OPEN)
            ->count();
        
        // Resolved tickets
        $resolvedTickets = (clone $queryBase)
            ->where('status', TicketStatus::RESOLVED)
            ->count();
        // overdue own tickets
        $overdueTickets = (clone $queryBase)
            ->overdue()
            ->count();
        

        // Recently updated tickets
        $recentUpdates = (clone $queryBase)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // Status chart: `selectRaw('status, count(*) as total')->groupBy('status')`.
        $ticketsByStatus = (clone $queryBase)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        // Melempar data ke antarmuka pengguna
        return view('dashboard.customer', compact(
            'totalTickets',
            'openTickets',
            'resolvedTickets',
            'overdueTickets',
            'recentUpdates',
            'ticketsByStatus'
        ));
    }
}