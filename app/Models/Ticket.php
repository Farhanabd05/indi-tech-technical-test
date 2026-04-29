<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TicketStatus; // 1. Import Enum

/*
Silakan buka model Ticket dan buat model Label jika belum ada. Bagaimana Anda merancang fungsi relasi pada kedua model tersebut (misalnya metode labels() di dalam Ticket, dan metode tickets() di dalam Label) menggunakan fungsi Eloquent $this->belongsToMany(...)
*/ 
class Ticket extends Model
{
    
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
        'created_by'
    ];

    protected function casts(): array // 2. Gunakan metode casts()
    {
        return [
            // 3. Petakan status ke class Enum
            'status' => TicketStatus::class, 
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
}
