<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case WAITING_FOR_CUSTOMER = 'waiting_for_customer';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
    case ESCALATED = 'escalated';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::ASSIGNED => 'Assigned',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING_FOR_CUSTOMER => 'Waiting for Customer',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
            self::REOPENED => 'Reopened',
            self::ESCALATED => 'Escalated',
        };
    }

    /**
     * Opsional: Warna untuk komponen UI (Badge/Tag)
     */
    public function color(): string
    {
        return match($this) {
            self::OPEN => 'gray',
            self::ASSIGNED => 'blue',
            self::IN_PROGRESS => 'orange',
            self::WAITING_FOR_CUSTOMER => 'purple',
            self::RESOLVED => 'green',
            self::CLOSED => 'red',
            self::REOPENED => 'indigo',
            self::ESCALATED => 'red',
        };
    }

}

    

?>