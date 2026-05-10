<?php

namespace App\Services;

use App\Models\Priority;
use App\Models\SlaRule;
use App\Models\Ticket;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Notifications\TicketAssignedNotification;

class TicketService
{
    protected array $nextTicketNumbers = [];

    public function generateTicketNumber(): string
    {
        $prefix = 'TCK';
        $year = date('Y');
        
        // Mulai transaksi untuk memastikan konsistensi data
        return DB::transaction(function () use ($prefix, $year) {
            if (! isset($this->nextTicketNumbers[$year])) {
                // Ambil nomor urut terakhir untuk tahun ini dengan kunci eksklusif
                $lastTicket = Ticket::whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->orderBy('ticket_number', 'desc')
                    ->first();

                $this->nextTicketNumbers[$year] = $lastTicket
                    ? ((int) Str::afterLast($lastTicket->ticket_number, '-')) + 1
                    : 1;
            }

            $nextNumber = $this->nextTicketNumbers[$year]++;

            // Format nomor tiket sesuai spesifikasi
            return sprintf('%s-%s-%06d', $prefix, $year, $nextNumber);
        });
    }

    public function calculateDueDate(int $priorityId): \Illuminate\Support\Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            $priority = Priority::find($priorityId);
            $resolutionHours = match ($priority?->name) {
                'Low' => 120,
                'Medium' => 72,
                'High' => 24,
                'Critical' => 8,
                default => null,
            };

            if ($resolutionHours === null) {
                throw new Exception('SLA rule not found for the given priority.');
            }

            return now()->addHours($resolutionHours);
        }

        return now()->addHours($slaRule->resolution_hours);
    }

    public function createTicket(array $data, User $user): Ticket
    {
        // saya gunain transaksi agar jika log atau label gagal, tiket tidak tanggung tersimpan
        return DB::transaction(function () use ($data, $user) {
            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'title'         => $data['title'],
                'description'   => $data['description'],
                'category_id'   => $data['category_id'],
                'priority_id'   => $data['priority_id'],
                'status'        => \App\Enums\TicketStatus::OPEN,
                'created_by'    => $user->id,
                'due_at'        => $this->calculateDueDate($data['priority_id'])
            ]);

            // Sinkronisasi Label
            if (isset($data['label_ids'])) {
                $ticket->labels()->sync($data['label_ids']);
            }

            // Handle lampiran
            if (isset($data['attachments'])) {
                $this->handleAttachments($ticket, $data['attachments'], $user);
            }

            // Pindahkan pencatatan log ke sini agar konsisten di Web & API
            ActivityLogService::log($ticket, $user, 'create_ticket');
            $adminUsers = User::whereHas('role', fn($q) => $q->where('slug', 'administrator'))->get();
            Notification::send($adminUsers, new TicketCreatedNotification($ticket));

            return $ticket;
        });
    }

    private function handleAttachments(Ticket $ticket, array $attachments, User $user)
    {
        foreach ($attachments as $attachment) {
            $path = $attachment->store('tickets', 'public');
            $ticket->attachments()->create([
                'path' => $path,
                'stored_name' => $attachment->hashName(),
                'original_name' => $attachment->getClientOriginalName(),
                'mime_type' => $attachment->getMimeType(),
                'size' => $attachment->getSize(),
                'uploaded_by' => $user->id
            ]);
        }
    }

    public function assignTicket(Ticket $ticket, ?int $agentId, User $user): Ticket
    {
        return DB::transaction(function () use ($ticket, $agentId, $user) {
            $oldAgentId = $ticket->assigned_agent_id;
            $ticket->update([
                'assigned_agent_id' => $agentId,
                'status' => $agentId ? TicketStatus::ASSIGNED : TicketStatus::OPEN,
            ]);
            ActivityLogService::log($ticket, $user, $oldAgentId ? 'reassign_ticket' : 'assign_ticket', $oldAgentId, $agentId);
            if ($agentId && $ticket->assignedAgent) {
                $ticket->assignedAgent->notify(new TicketAssignedNotification($ticket));
            }
            return $ticket;
        });
    }
}
