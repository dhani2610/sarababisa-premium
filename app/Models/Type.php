<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = [
        'name',
        'cabang_id',
    ];

    public function service()
    {
        return $this->hasMany(ServiceTransaction::class, 'types_id', 'id')
            ->where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', now()->month)
            ->where('is_approve', 'Setuju');
    }
    public function filteredService()
    {
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(ServiceTransaction::class, 'types_id', 'id')
            ->where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', $date->year)
            ->whereMonth('tgl_disetujui', $date->month)
            ->where('is_approve', 'Setuju');
    }

    public function relasiService()
    {
        return $this->hasMany(ServiceTransaction::class, 'types_id', 'id');
    }
}
