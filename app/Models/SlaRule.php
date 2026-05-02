<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    protected $guarded = ['id'];

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }
}
