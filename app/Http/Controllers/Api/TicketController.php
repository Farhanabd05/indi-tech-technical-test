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

class TicketController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $tickets = Ticket::with(['priority', 'category', 'creator', 'assignedAgent', 'labels'])
            ->when($user->isCustomer(), fn($q) => $q->where('created_by', $user->id))
            ->when($user->isAgent(), fn($q) => $q->where('assigned_agent_id', $user->id))
            ->latest()
            ->paginate(10);

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
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}