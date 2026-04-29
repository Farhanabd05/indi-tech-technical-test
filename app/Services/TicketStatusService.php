<?php
namespace App\Services;

use App\Enums\TicketStatus;

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
}
