<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'cabang_id',
    ];

    public function modelserie()
    {
        return $this->hasMany(ModelSerie::class, 'brands_id', 'id');
    }

    public function relasiService()
    {
        return $this->hasMany(ServiceTransaction::class, 'brands_id', 'id');
    }

    public function relasiModelSerie()
    {
        return $this->hasMany(ModelSerie::class, 'brands_id', 'id');
    }
}
