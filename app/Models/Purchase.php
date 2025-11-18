<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'reference_number',
        'customers_id',
        'suppliers_id',
        'suppliers_name',
        'keterangan',
        'products_id',
        'product_name',
        'quantity',
        'product_price',
        'total_price',
        'date',
        'cabang_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'suppliers_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');
    }
}
