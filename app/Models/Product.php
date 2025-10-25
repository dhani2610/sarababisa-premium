<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'product_name',
        'product_code',
        'sub_categories_id',
        'category_name',
        'warna',
        'kondisi',
        'stok',
        'stok_minimal',
        'harga_modal',
        'harga_jual',
        'harga_jual_toko',
        'keterangan',
        'nomor_seri',
        'garansi',
        'garansi_imei',
        'ppn',
        'categories_id',
        'brands_id',
        'model_series_id',
        'ram',
        'capacities_id',
        'created_at'
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_categories_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categories_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brands_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(ModelSerie::class, 'model_series_id', 'id');
    }

    public function capacity()
    {
        return $this->belongsTo(Capacity::class, 'capacities_id', 'id');
    }

    public function relasiOrder()
    {
        return $this->hasMany(OrderDetail::class, 'products_id', 'id');
    }
}
