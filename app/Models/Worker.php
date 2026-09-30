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
        'status_shift',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'workers_id');
    }

    public function getUserAccountAttribute()
    {
        return User::where('workers_id', $this->id)->first()
            ?? User::where('name', $this->name)->where('cabang_id', $this->cabang_id)->first()
            ?? User::forCabang($this->cabang_id)->where('name', $this->name)->first()
            ?? User::where('name', $this->name)->first();
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
