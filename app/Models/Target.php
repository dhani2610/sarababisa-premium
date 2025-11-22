<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    protected $fillable = [
        'target',
        'persen',
        'nilai',
        'created_at',
        'updated_at',
        'cabang_id',
    ];
}
