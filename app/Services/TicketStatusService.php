<?php
namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketResolvedNotification;
use App\Notifications\TicketEscalatedNotification;
use App\Services\ActivityLogService;

class TicketStatusService
{
    private const TRANSITION_MAP = [
        TicketStatus::OPEN->value                 => [TicketStatus::ASSIGNED->value, TicketStatus::CLOSED->value],
        TicketStatus::ASSIGNED->value             => [TicketStatus::IN_PROGRESS->value, TicketStatus::ESCALATED->value],
        TicketStatus::IN_PROGRESS->value          => [TicketStatus::WAITING_FOR_CUSTOMER->value, TicketStatus::RESOLVED->value, TicketStatus::ESCALATED->value],
        TicketStatus::WAITING_FOR_CUSTOMER->value => [TicketStatus::IN_PROGRESS->value, TicketStatus::RESOLVED->value],
        TicketStatus::RESOLVED->value             => [TicketStatus::CLOSED->value, TicketStatus::REOPENED->value],
        TicketStatus::CLOSED->value               => [TicketStatus::REOPENED->value],
        TicketStatus::REOPENED->value             => [TicketStatus::ASSIGNED->value, TicketStatus::IN_PROGRESS->value],
        TicketStatus::ESCALATED->value            => [TicketStatus::IN_PROGRESS->value, TicketStatus::RESOLVED->value],
    ];

    public function isValidTransition(TicketStatus $from, TicketStatus $to): bool
    {
        return in_array($to->value, $this->allowedNextStatuses($from));
    }

    public function allowedNextStatuses(TicketStatus $current): array
    {
        return self::TRANSITION_MAP[$current->value] ?? [];
    }

    public function changeStatus(Ticket $ticket, TicketStatus $newStatus, User $user): Ticket
    {
        // Validasi Bisnis: Tiket operasional wajib punya agen
        if (!$ticket->assigned_agent_id && in_array($newStatus, [
            TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS, 
            TicketStatus::ESCALATED, TicketStatus::RESOLVED
        ])) {
            throw new \Exception('Tiket harus memiliki agen sebelum status diubah.');
        }
 
        return DB::transaction(function () use ($ticket, $newStatus, $user) {
            $oldStatus = $ticket->status;
            $ticket->status = $newStatus;
 
            // Update timestamps
            if ($newStatus === TicketStatus::RESOLVED) $ticket->resolved_at = now();
            elseif ($newStatus === TicketStatus::CLOSED) $ticket->closed_at = now();
            else { $ticket->resolved_at = null; $ticket->closed_at = null; }
            
            $ticket->save();
            ActivityLogService::log($ticket, $user, 'update_status', $oldStatus->value, $newStatus->value);
 
            // Notifikasi
            if ($newStatus === TicketStatus::RESOLVED) {
                $ticket->creator->notify(new TicketResolvedNotification($ticket));
            } elseif ($newStatus === TicketStatus::ESCALATED) {
                $admins = User::whereHas('role', fn($q) => $q->whereIn('slug', ['administrator', 'supervisor']))->get();
                Notification::send($admins, new TicketEscalatedNotification($ticket));
            }
            return $ticket;
        });
    }
}
