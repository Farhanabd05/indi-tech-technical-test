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
        $overdueTickets = Ticket::where('due_at', '<', now())
            ->whereNotIn('status', [TicketStatus::RESOLVED->value, TicketStatus::CLOSED->value])
            ->whereNull('overdue_notified_at')
            ->get();

        // 2. Kueri untuk mengumpulkan pengguna dengan peran supervisor atau administrator
        $usersToNotify = User::whereHas('role', function ($query) {
            $query->whereIn('slug', ['supervisor', 'administrator']);
        })->get();

        // 3. Perulangan untuk setiap tiket bermasalah
        foreach ($overdueTickets as $ticket) {
            // Kirim notifikasi ke pengguna yang relevan
            Notification::send($usersToNotify, new SlaOverdueNotification($ticket));

            // Tandai tiket dengan waktu saat ini untuk mencegah pengiriman berulang
            $ticket->update(['overdue_notified_at' => now()]);
        }

        $this->info('Overdue tickets have been checked and notifications sent if necessary.');
    }
}
