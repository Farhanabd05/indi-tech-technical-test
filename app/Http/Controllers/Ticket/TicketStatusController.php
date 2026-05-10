<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\UpdateTicketStatusRequest;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use App\Enums\TicketStatus;
use App\Services\TicketStatusService;
use Illuminate\Support\Facades\Gate;

class TicketStatusController extends Controller
{
    public function update(UpdateTicketStatusRequest $request, Ticket $ticket, TicketStatusService $statusService)
    {
        Gate::authorize('changeStatus', $ticket);
        try {
            $statusService->changeStatus($ticket, TicketStatus::from($request->validated()['status']), Auth::user());
            return redirect()->back()->with('success', 'Status berhasil diubah.');
        } catch (\Exception $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }
}
