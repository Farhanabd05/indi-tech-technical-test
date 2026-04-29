<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/*
Di dalam model Attachment, bagaimana Anda akan membuat sebuah metode (misalnya attachable()) yang memanggil dan mengembalikan nilai dari fungsi bawaan $this->morphTo()?
*/
class Attachment extends Model
{
    protected $fillable = ['file_path', 'file_name', 'original_name', 'mime_type', 'file_size'];

    public function attachable()
    {
        return $this->morphTo();
    }
}

