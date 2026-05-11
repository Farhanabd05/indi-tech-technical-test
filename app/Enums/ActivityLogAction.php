<?php

namespace App\Enums;

enum ActivityLogAction: string
{
    case CREATE_TICKET = 'create_ticket';
    case UPDATE_TICKET = 'update_ticket';
    case UPDATE_STATUS = 'update_status';
    case ASSIGN_TICKET = 'assign_ticket';
    case REASSIGN_TICKET = 'reassign_ticket';
    case ADD_COMMENT = 'add_comment';
    case UPLOAD_ATTACHMENT = 'upload_attachment';
    case SLA_OVERDUE = 'sla_overdue';
}