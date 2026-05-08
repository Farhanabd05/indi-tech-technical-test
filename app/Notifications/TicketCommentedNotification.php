<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class TicketCommentedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Comment on Ticket #' . $this->ticket->ticket_number)
            ->line('A new comment has been added to your ticket.')
            ->action('View Ticket', url('/tickets/' . $this->ticket->id))
            ->line('Thank you for using our support application!');
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title' => $this->ticket->title,
            'message' => 'A new comment has been added to your ticket.',
        ];
    }
}