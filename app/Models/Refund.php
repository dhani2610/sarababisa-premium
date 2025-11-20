<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'servis_transaction_id',
        'nominal',
        'nominal_servis',
        'teknisi_id',
        'period',
        'cabang_id',
    ];

    protected $dates = ['period'];

    public function serviceTransaction()
    {
        return $this->belongsTo(ServiceTransaction::class, 'servis_transaction_id');
    }

    public function teknisi()
    {
        return $this->belongsTo(\App\Models\User::class, 'teknisi_id');
    }
}
