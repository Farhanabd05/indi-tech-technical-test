<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import Trait

class Category extends Model
{
    use SoftDeletes;
    
    // Tambahkan properti pelindung ini
    protected $guarded = ['id'];
}