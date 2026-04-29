<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
Silakan buka atau buat berkas model app/Models/Comment.php. Mengacu pada susunan data yang Anda kirimkan di pengontrol, atribut apa saja yang wajib Anda tuliskan di dalam susunan properti $fillable agar pangkalan data menerima permintaan penyimpanan Anda?
*/
class Comment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'is_internal',
    ];

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
