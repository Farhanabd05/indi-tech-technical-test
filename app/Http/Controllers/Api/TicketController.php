<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketStatusRequest;
use App\Services\TicketStatusService;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use App\Services\ActivityLogService;
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Ticket\TicketFilterRequest;

class TicketController extends Controller
{
    public function index(TicketFilterRequest $request): AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ticket::visibleTo($user)
            ->with(['priority', 'category', 'creator', 'assignedAgent', 'labels']);

        // Apply the filter from the request
        $query->filter($request->only(['status', 'priority_id', 'category_id', 'assigned_agent_id', 'label_id', 'created_from', 'created_to', 'due_from', 'due_to', 'overdue', 'search', 'sort_by', 'sort_direction']));

        $tickets = $query->latest()->paginate(10);

        return TicketResource::collection($tickets);
    }

    public function show(Ticket $ticket): TicketResource
    {
        Gate::authorize('view', $ticket);
        
        $ticket->load(['priority', 'category', 'creator', 'assignedAgent', 'labels', 'attachments', 'comments']);
        
        return new TicketResource($ticket);
    }

    public function store(StoreTicketRequest $request, TicketService $ticketService): JsonResponse
    {
        Gate::authorize('create', Ticket::class);

        $ticket = $ticketService->createTicket($request->validated(), Auth::user());

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    public function changeStatus(UpdateTicketStatusRequest $request, Ticket $ticket, TicketStatusService $statusService): JsonResponse
    {
        Gate::authorize('changeStatus', $ticket);
        try {
            $statusService->changeStatus($ticket, TicketStatus::from($request->validated()['status']), Auth::user());
            return (new TicketResource($ticket))->response()->setStatusCode(200);
        } catch (\Exception $e) {
            Log::error("Error changing ticket status: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}