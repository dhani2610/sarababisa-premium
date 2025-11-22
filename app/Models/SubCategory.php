<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        'name',
        'categories_id',
        'cabang_id'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'categories_id', 'id');
    }

    public function relasiProduct()
    {
        return $this->hasMany(Product::class, 'sub_categories_id', 'id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'sub_categories_id', 'id');
    }
}
