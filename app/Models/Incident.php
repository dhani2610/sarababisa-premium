<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'workers_id',
        'persen_teknisi',
        'biaya_teknisi',
        'biaya_toko',
        'created_at',
        'cabang_id',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workers_id', 'id');
    }
}
