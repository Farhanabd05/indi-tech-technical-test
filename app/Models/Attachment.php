<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
