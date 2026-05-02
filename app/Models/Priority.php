<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Priority extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('level', 'asc');
    }
}
