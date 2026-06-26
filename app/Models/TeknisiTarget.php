<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeknisiTarget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'users_id',
        'item',
        'created_at',
        'teknisi_name',
        'cabang_id',
        'tipe',
        'nominal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}
