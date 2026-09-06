<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDeleteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabang_id',
        'transaksi_type',
        'transaksi_id',
        'transaksi_nomor',
        'keterangan',
        'alasan',
        'requested_by',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
