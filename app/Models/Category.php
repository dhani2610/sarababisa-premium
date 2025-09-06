<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'category_name',
        'show_portal',
    ];

    public function product()
    {
        $currentMonth = now()->month;

        return $this->hasMany(Product::class, 'categories_id', 'id')
            ->whereMonth('created_at', $currentMonth);
    }

    public function relasiProduct()
    {
        return $this->hasMany(Product::class, 'categories_id', 'id');
    }

    public function subCategory()
    {
        return $this->hasMany(SubCategory::class, 'categories_id', 'id');
    }
}
