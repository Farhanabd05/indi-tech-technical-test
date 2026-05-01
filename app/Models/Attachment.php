<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/*
Di dalam model Attachment, bagaimana Anda akan membuat sebuah metode (misalnya attachable()) yang memanggil dan mengembalikan nilai dari fungsi bawaan $this->morphTo()?
*/
class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size'
    ];

    public function getUrlAttribute()
    {
        return \Illuminate\Support\Facades\Storage::url($this->path);
    }

    public function attachable()
    {
        return $this->morphTo();
    }
}
