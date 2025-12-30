<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'jabatan',
        'status',
        'bulankerja',
        'gaji',
        'absen',
        'bpjs',
        'cabang_id',
    ];

    // many to many
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // one to many
    public function salary()
    {
        $currentMonth = now()->month;
        return $this->hasMany(Salary::class, 'workers_id')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', $currentMonth);
    }

    public function debt()
    {
        $currentMonth = now()->month;
        return $this->hasMany(Debt::class, 'workers_id')
            ->where('is_approve', 'Setuju')
            ->whereYear('created_at', now()->year)
            ->whereMonth('tgl_disetujui', $currentMonth);
    }

    public function incident()
    {
        $currentMonth = now()->month;
        return $this->hasMany(Incident::class, 'workers_id')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', $currentMonth);
    }

    public function relasiDebt()
    {
        return $this->hasMany(Debt::class, 'workers_id');
    }

    public function relasiSalary()
    {
        return $this->hasMany(Salary::class, 'workers_id');
    }
}
