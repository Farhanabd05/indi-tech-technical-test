<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTicketRequest;
use App\Services\TicketService;
use App\Enums\TicketStatus;
use App\Services\ActivityLogService;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Notification;

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

        if (Auth::user()->role->slug === 'agent') {
            $query->where('assigned_agent_id', Auth::user()->id);
        } elseif (Auth::user()->role->slug === 'customer') {
            $query->where('created_by', Auth::user()->id);
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
                    'file_path' => $path,
                    'file_name' => $file->hashName(),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
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
}
