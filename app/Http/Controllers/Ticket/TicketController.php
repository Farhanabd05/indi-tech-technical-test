<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Services\TicketService;
use App\Enums\TicketStatus;
use App\Services\ActivityLogService;
use App\Models\User;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Label;
use App\Models\Category;
use App\Models\Priority;
use App\Services\TicketStatusService;
use App\Services\ExportService;
use App\Enums\ActivityLogAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Ticket\TicketFilterRequest;

class TicketController extends Controller
{   
    public function show(Ticket $ticket)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        Gate::authorize('view', $ticket);
        $statuses = (new TicketStatusService())->allowedNextStatuses($ticket->status);

        $ticket->load([
            'labels',
            'category',
            'priority',
            'attachments',
            'comments.user',
            'comments.attachments',
            'creator',
            'assignedAgent',
        ]);
        $agentsQuery = User::whereHas('role', function ($q) {
            $q->where('slug', 'agent');
        });

        if ($user->hasRole('supervisor')) {
            $agentsQuery->where('team_id', $user->team_id);
        }

        $agents = $agentsQuery->get();
        return view('tickets.show', compact('ticket', 'statuses', 'agents'));
    }

    public function index(TicketFilterRequest $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        Gate::authorize('viewAny', Ticket::class);
        $query = Ticket::query();
        $query->filter($request->only(['status', 'priority_id', 'category_id', 'assigned_agent_id', 'label_id', 'created_from', 'created_to', 'due_from', 'due_to', 'overdue', 'search', 'sort_by', 'sort_direction']));

        $query->visibleTo($user);

        $tickets = $query->paginate(10);
        $statuses = TicketStatus::cases();
        $priorities = Priority::all();
        $categories = Category::all();
        $labels = Label::all();

        return view('tickets.index', compact('tickets', 'statuses', 'priorities', 'categories', 'labels'));
    }


    public function store(StoreTicketRequest $request, TicketService $ticket_service)
    {
        try {
            /** @var \App\Models\User */
            $user = Auth::user();
            $validated = $request->validated();
            $ticket = $ticket_service->createTicket($validated, $user);
            
            return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dibuat.');
        } catch (\Exception $e) {
            Log::error('An error occurred while storing a ticket: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while storing a ticket. Please try again later.'], 500);
        }
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $validated = $request->validated();
        $ticket_service = new TicketService();
        $ticket_service->updateTicket($ticket, $validated, $user);
        return redirect()->route('tickets.show', $ticket)->with('success', 'Tiket berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($ticket, $user) {
            ActivityLogService::log($ticket, $user, ActivityLogAction::DELETE_TICKET);
            $ticket->delete();
        });

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dihapus.');
    }

    // creata
    public function create()
    {
        $categories = Category::all();
        $priorities = Priority::all();
        $labels = Label::all();
        return view('tickets.create', compact('categories', 'priorities', 'labels'));
    }

    public function export(TicketFilterRequest $request)
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $query = Ticket::query();

        // 1. Terapkan filter yang sama persis dengan halaman antarmuka
        $query->filter($request->only(['status', 'priority_id', 'category_id', 'assigned_agent_id', 'label_id', 'created_from', 'created_to', 'due_from', 'due_to', 'overdue', 'search', 'sort_by', 'sort_direction']));

        // 2. Muat relasi yang valid untuk mencegah N+1 Query
        $query->with(['category', 'priority', 'labels', 'assignedAgent', 'creator']);

        $query->visibleTo($user);

        // 3. Count the number of tickets to check if it exceeds the limit
        $ticketCount = $query->count();
        if ($ticketCount > 1000) {
            // Redirect back with an error message
            return back()->withErrors(['TooManyTickets' => 'Data terlalu besar, maksimal 1000 tiket']);
        }

        // 4. Tarik seluruh data (tanpa pagination)
        $tickets = $query->get();

        // 5. Serahkan data ke layanan ekspor
        return (new ExportService())->exportTicketsToCsv($tickets);
    }
}
