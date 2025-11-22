<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'created_at',
        'price',
        'supplier',
        'masa_penggantian',
        'cabang_id',
    ];
}
