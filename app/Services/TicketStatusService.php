<?php
namespace App\Services;

use App\Enums\ActivityLogAction;
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
            $now = now();
            $oldStatus = $ticket->status;
            $ticket->status = $newStatus;

            if ($oldStatus === TicketStatus::WAITING_FOR_CUSTOMER && $newStatus !== TicketStatus::WAITING_FOR_CUSTOMER) {
                $this->resumeSla($ticket, $now);
            }

            if ($newStatus === TicketStatus::WAITING_FOR_CUSTOMER) {
                $this->pauseSla($ticket, $now);
            }

            if ($newStatus === TicketStatus::REOPENED) {
                $this->restartSla($ticket);
            }
 
            // Update timestamps
            if ($newStatus === TicketStatus::RESOLVED) $ticket->resolved_at = $now;
            elseif ($newStatus === TicketStatus::CLOSED) $ticket->closed_at = $now;
            else { $ticket->resolved_at = null; $ticket->closed_at = null; }
            
            $ticket->save();
            ActivityLogService::log($ticket, $user, ActivityLogAction::UPDATE_STATUS, $oldStatus->value, $newStatus->value);
 
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

    private function pauseSla(Ticket $ticket, $now): void
    {
        if ($ticket->sla_paused_at === null) {
            $ticket->sla_paused_at = $now;
        }
    }

    private function resumeSla(Ticket $ticket, $now): void
    {
        if ($ticket->sla_paused_at === null) {
            return;
        }

        $pausedMinutes = (int) $ticket->sla_paused_at->diffInMinutes($now);

        $ticket->total_paused_duration_minutes = ((int) $ticket->total_paused_duration_minutes) + $pausedMinutes;
        $ticket->sla_paused_at = null;

        if ($ticket->due_at !== null && $pausedMinutes > 0) {
            $ticket->due_at = $ticket->due_at->copy()->addMinutes($pausedMinutes);
        }
    }

    private function restartSla(Ticket $ticket): void
    {
        $ticket->due_at = app(TicketService::class)->calculateDueDate($ticket->priority_id);
        $ticket->sla_paused_at = null;
        $ticket->total_paused_duration_minutes = 0;
        $ticket->overdue_notified_at = null;
    }
}
