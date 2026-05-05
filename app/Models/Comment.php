<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Comment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'is_internal',
    ];

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function setContentAttribute($value): void
    {
        $this->attributes['body'] = $value;
    }

    public function getContentAttribute(): ?string
    {
        return $this->body;
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /// Relasi dengan model Ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /// Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
