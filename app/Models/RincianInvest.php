<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RincianInvest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipe',
        'id_investor',
        'tanggal',
        'nominal',
        'upload_bukti_tf',
        'keterangan',
        'cabang_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function investor()
    {
        return $this->belongsTo(User::class, 'id_investor');
    }

    public function getTipeLabelAttribute()
    {
        return match ($this->tipe) {
            1 => 'Masuk',
            2 => 'Pembagian',
            3 => 'Lain-lain',
            default => '-',
        };
    }
}
