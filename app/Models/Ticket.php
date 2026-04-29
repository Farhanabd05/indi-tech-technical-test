<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TicketStatus; // 1. Import Enum
class Ticket extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array // 2. Gunakan metode casts()
    {
        return [
            // 3. Petakan status ke class Enum
            'status' => TicketStatus::class, 
        ];
    }
}
