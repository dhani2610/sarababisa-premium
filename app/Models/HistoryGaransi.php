<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryGaransi extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'service_id',
        'penerima_id',
        'teknisi_id',
        'tindakan',
        'sparepart',
        'total_biaya',
        'keluhan',
        'tgl_selesai',
        'status',
        'cabang_id',
        'id_customer',
        'estimasi_pengerjaan',
        'fungsi_masuk',
        'fungsi_keluar',
    ];

    protected $casts = [
        'tindakan' => 'array',
        'sparepart' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(ServiceTransaction::class, 'service_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }
}
