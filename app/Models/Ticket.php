<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\TicketStatus; // 1. Import Enum

class Ticket extends Model
{
    use SoftDeletes;
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'category_id',
        'priority_id',
        'status',
        'created_by',
        'assigned_agent_id',
        'due_at',
        'sla_paused_at',
        'total_paused_duration_minutes',
    ];

    protected function casts(): array // 2. Gunakan metode casts()
    {
        return [
            // 3. Petakan status ke class Enum
            'status' => TicketStatus::class, 
            'due_at' => 'datetime',
            'sla_paused_at' => 'datetime',
            'total_paused_duration_minutes' => 'integer',
        ];
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
            ->whereNull('sla_paused_at')
            ->whereNotIn('status', [
                TicketStatus::RESOLVED->value,
                TicketStatus::CLOSED->value,
                TicketStatus::WAITING_FOR_CUSTOMER->value,
            ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && $this->sla_paused_at === null
            && ! in_array($this->status, [
                TicketStatus::RESOLVED,
                TicketStatus::CLOSED,
                TicketStatus::WAITING_FOR_CUSTOMER,
            ], true);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_agent_id');
    }

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority_id'])) {
            $query->where('priority_id', $filters['priority_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['assigned_agent_id'])) {
            $query->where('assigned_agent_id', $filters['assigned_agent_id']);
        }   


        if (isset($filters['label_id'])) {
            $query->whereHas('labels', function ($q) use ($filters) {
                $q->where('labels.id', $filters['label_id']);
            });
        }

        // created date range filter
        if (isset($filters['created_from']) && isset($filters['created_to'])) {
            $query->whereBetween('created_at', [$filters['created_from'], $filters['created_to']]);
        }

        // due date range filter
        if (isset($filters['due_from']) && isset($filters['due_to'])) {
            $query->whereBetween('due_at', [$filters['due_from'], $filters['due_to']]);
        }

        // overdue filter
        if (isset($filters['overdue']) && $filters['overdue'] == true) {
            $query->overdue();
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%$search%")
                    ->orWhere('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    // customer name and email search
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('email', 'like', "%$search%");
                    });
            });
        }

        $query->when(isset($filters['sort_by']) && in_array($filters['sort_by'], ['created_at', 'updated_at', 'priority_id', 'due_at', 'status']), function ($q) use ($filters) {
            $sortBy = $filters['sort_by'];
            $direction = $filters['sort_direction'] ?? 'asc';
            $validDirection = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'asc';
            $q->orderBy($sortBy, $validDirection);
        });
    }

    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasRole('administrator')) {
            return $query;
        }

        if ($user->hasRole('supervisor')) {
            // Membatasi akses Penyelia hanya untuk agen dalam timnya
            return $query->whereHas('assignedAgent', function ($q) use ($user) {
                $q->where('team_id', $user->team_id);
            });
        }

        if ($user->hasRole('agent')) {
            return $query->where('assigned_agent_id', $user->id);
        }

        if ($user->hasRole('customer')) {
            return $query->where('created_by', $user->id);
        }

        // Tutup semua akses jika peran tidak dikenali
        return $query->whereRaw('1 = 0');
    }
}
