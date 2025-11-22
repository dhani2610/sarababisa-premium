<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacity extends Model
{
    protected $fillable = [
        'name',
        'cabang_id',
    ];

    public function relasiService()
    {
        return $this->hasMany(ServiceTransaction::class, 'capacities_id', 'id');
    }
}
