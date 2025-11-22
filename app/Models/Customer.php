<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama',
        'kategori',
        'nomor_hp',
        'alamat',
        'cabang_id',
    ];

    // one to many
    public function servicetransaction()
    {
        return $this->hasMany(ServiceTransaction::class, 'customers_id');
    }

    public function sale()
    {
        return $this->hasMany(Order::class, 'customers_id');
    }
}
