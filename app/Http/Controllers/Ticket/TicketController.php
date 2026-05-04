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
use App\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Label;
use App\Models\Team;
use App\Models\Category;
use App\Models\Priority;

class TicketController extends Controller
{   
    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        return view('tickets.show', compact('ticket'));
    }

    public function index()
    {
        Gate::authorize('viewAny', Ticket::class);

        $query = Ticket::query();

        $query->filter(request(['status', 'priority_id', 'category_id', 'assigned_agent_id', 'label_id', 'created_from', 'created_to', 'due_from', 'due_to', 'overdue', 'search', 'sort_by', 'sort_direction']));

        if (Auth::user()->role->slug === 'agent') {
            $query->where('assigned_agent_id', Auth::user()->id);
        } elseif (Auth::user()->role->slug === 'customer') {
            $query->where('created_by', Auth::user()->id);
        } elseif (Auth::user()->role->slug === 'supervisor') {
            $agentIds = User::where('team_id', Auth::user()->team_id)
                        ->whereHas('role', function ($q) {
                            $q->where('slug', 'agent');
                        })->pluck('id');

            $query->whereIn('assigned_agent_id', $agentIds);
        }

        $tickets = $query->paginate(10);

        return view('tickets.index', compact('tickets'));
    }


    public function store(StoreTicketRequest $request, TicketService $ticket_service)
    {
        // Logika pembuatan nomor tiket akan dipindahkan ke dalam TicketService
        $ticketNumber = $ticket_service->generateTicketNumber();

        $validated = $request->validated();
        // Proses penyimpanan tiket menggunakan data dari request dan nomor tiket yang dihasilkan
        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'priority_id' => $validated['priority_id'],
            'status' => TicketStatus::OPEN,
            'created_by' => Auth::id(),
            'due_at' => $ticket_service->calculateDueDate($validated['priority_id'])
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('tickets', 'public');
                $ticket->attachments()->create([
                    'path' => $path,
                    'stored_name' => $file->hashName(),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id()
                ]);
            }
        }

        if (isset($validated['label_ids'])) {
            $ticket->labels()->sync($validated['label_ids']);
        }
        ActivityLogService::log(
            $ticket,
            Auth::user(),
            'create_ticket',
            null,
            'Tiket dibuat dengan nomor: ' . $ticketNumber
        );

        $adminUsers = User::whereHas('role', function ($query) {
            $query->where('slug', 'administrator');
        })->get();
        Notification::send($adminUsers, new TicketCreatedNotification($ticket));
        
        return response()->json([
            'message' => 'Tiket berhasil dibuat.',
            'ticket' => $ticket
        ], 201);
    }
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $validated = $request->validated();
        $ticket->update($validated);

        ActivityLogService::log(
            $ticket,
            Auth::user(),
            'update_ticket',
            null,
            'Tiket diperbarui dengan data: ' . json_encode($validated)
        );

        return response()->json([
            'message' => 'Tiket berhasil diperbarui.',
            'ticket' => $ticket
        ], 200);
    }

    // creata
    public function create()
    {
        $categories = Category::all();
        $priorities = Priority::all();
        $labels = Label::all();
        return view('tickets.create', compact('categories', 'priorities', 'labels'));
    }
}
