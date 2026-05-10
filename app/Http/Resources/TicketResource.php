<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            // Menampilkan nama dari relasi (bukan ID mentah) sesuai spek
            'priority' => $this->priority?->name,
            'category' => $this->category?->name,
            'created_by' => $this->creator?->name,
            'assigned_agent' => $this->assignedAgent?->name,
            'due_at' => $this->due_at,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'labels' => $this->labels->pluck('name'),
        ];
    }
}