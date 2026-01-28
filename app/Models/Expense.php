<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'users_id',
        'is_approve',
        'tgl_disetujui',
        'created_at',
        'cabang_id',
        'tipe',
        'foto',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
