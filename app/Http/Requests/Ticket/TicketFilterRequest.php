<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TicketStatus;

class TicketFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi sudah ditangani di level controller, jadi kita buka saja
        return true; 
    }

    public function rules(): array
    {
        return [
            // Status menjadi opsional dan divalidasi ke Enum
            'status' => ['nullable', Rule::enum(TicketStatus::class)],
            
            'priority_id' => ['nullable', 'exists:priorities,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            
            // Agen divalidasi ke tabel users
            'assigned_agent_id' => ['nullable', 'exists:users,id'],
            'label_id' => ['nullable', 'exists:labels,id'],
            
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date', 'after_or_equal:due_from'],
            'overdue' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'string', Rule::in(['created_at', 'updated_at', 'priority_id', 'due_at', 'status'])],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
