<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Label extends Model
{
    protected $fillable = ['name', 'color'];
    use SoftDeletes;

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class);
    }
}
