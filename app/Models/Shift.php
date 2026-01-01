<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_pulang',
        'nominal_gaji',
        'potongan_terlambat',
        'potongan_tidak_masuk',
        'potongan_izin',
        'potongan_cuti',
        'potongan_sakit',
        'cabang_id',
        'worker_id',
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
}
