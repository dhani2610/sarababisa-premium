<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_tindakan',
        'modal_sparepart',
        'harga_toko',
        'harga_pelanggan',
        'garansi',
        'cabang_id',
    ];

    public function servicetransaction()
    {
        return $this->hasMany(ServiceTransaction::class, 'service_actions_id');
    }
}
