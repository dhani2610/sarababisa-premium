<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\QcProduk;

class QcProduk extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function picMasuk()
    {
        return $this->belongsTo(User::class, 'pic_masuk', 'id');
    }

    /**
     * Relasi ke User sebagai PIC Keluar
     */
    public function picKeluar()
    {
        return $this->belongsTo(User::class, 'pic_keluar', 'id');
    }
}
