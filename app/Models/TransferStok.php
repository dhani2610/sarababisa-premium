<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferStok extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function dariCabang() {
        return $this->belongsTo(Cabang::class, 'dari_cabang_id');
    }

    public function keCabang() {
        return $this->belongsTo(Cabang::class, 'ke_cabang_id');
    }

    public function dariProduk() {
        return $this->belongsTo(Product::class, 'dari_produk_id');
    }

    public function keProduk() {
        return $this->belongsTo(Product::class, 'ke_produk_id');
    }


}
