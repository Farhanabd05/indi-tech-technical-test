<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ticket;
use App\Models\User;

// <!-- Pertanyaan reflektif untuk Anda: Silakan buat berkas model app/Models/ActivityLog.php. Mengacu pada parameter yang dikirim oleh layanan Anda tadi, apa saja isi dari susunan (array) properti $fillable yang akan Anda tuliskan, dan bagaimana Anda merancang dua fungsi relasi Eloquent (belongsTo) yang menghubungkan model ini dengan model Ticket dan User? -->
class ActivityLog extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getAssignedAgentNameAttribute()
    {
        return User::find($this->new_value)?->name ?? 'Agent Sudah Dihapus';
    }
}