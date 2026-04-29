<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import Trait

class Category extends Model
{
    use SoftDeletes; // 2. Gunakan Trait di dalam kelas

    // ... sisa kode model
}