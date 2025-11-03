<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tipe', 'keterangan', 'tanggal', 'nominal_potongan','tanggal_mulai','tanggal_selesai','dokumen'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
