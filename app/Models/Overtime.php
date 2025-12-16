<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'nominal_overtime',
        'keterangan',
        'tanggal',
        'waktu_start',
        'waktu_end',
        'approve_by',
        'status',
        'cabang_id',
    ];

    protected $dates = ['tanggal'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approve_by');
    }
}
