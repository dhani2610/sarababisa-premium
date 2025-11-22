<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $fillable = [
        'purchases_id',
        'suppliers_id',
        'supplier_name',
        'product_name',
        'keterangan',
        'status',
        'retur_quantity',
        'retur_credit',
        'date',
        'cabang_id',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchases_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'suppliers_id', 'id');
    }
}
