<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workers_id',
        'item',
        'total',
        'is_approve',
        'tgl_disetujui',
        'cabang_id',
        'created_at',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workers_id', 'id');
    }
}
