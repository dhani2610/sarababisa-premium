<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeknisiServis extends Model
{
    use HasFactory;

    protected $table = 'teknisi_servis';

    protected $fillable = [
        'service_transactions_id',
        'users_id',
        'tipe',
        'modal_sparepart',
        'biaya',
        'profit',
        'profittoko',
        'persen_teknisi',
        'bonus_interface',
        'tindakan_servis',
        'service_actions',
        'products',
        'biaya_j',
        'modal_j',
    ];

    // Relasi balik ke Transaksi Utama
    public function transaction()
    {
        return $this->belongsTo(ServiceTransaction::class, 'service_transactions_id');
    }

    // Relasi ke User (Teknisi)
    public function teknisi()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
