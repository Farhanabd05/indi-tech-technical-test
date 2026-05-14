<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Notifications\SlaOverdueNotification;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Enums\ActivityLogAction;
// db buat transaction
use Illuminate\Support\Facades\DB;

#[Signature('tickets:check-overdue')]
#[Description('Command description')]
class CheckOverdueTicketsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Kueri untuk mencari tiket terlambat
        $overdueTickets = Ticket::overdue()
            ->whereNull('overdue_notified_at')
            ->get();

        // 2. Kueri untuk mengumpulkan pengguna dengan peran supervisor atau administrator
        $usersToNotify = User::whereHas('role', function ($query) {
            $query->whereIn('slug', ['supervisor', 'administrator']);
        })->get();

        foreach ($overdueTickets as $ticket) {
            DB::transaction(function () use ($ticket, $usersToNotify) {
                Notification::send($usersToNotify, new SlaOverdueNotification($ticket));
                $ticket->update(['overdue_notified_at' => now(), 'status' => TicketStatus::ESCALATED->value]);
                ActivityLogService::log($ticket, null, ActivityLogAction::SLA_OVERDUE);
            });
        }

        $this->info('Overdue tickets have been checked and notifications sent if necessary.');
    }
}
