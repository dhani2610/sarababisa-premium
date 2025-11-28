<?php

namespace App\Models;

use App\Models\Worker;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'alamat',
        'role',
        'types_id',
        'nomor_hp',
        'nik',
        'persen',
        'owner',
        'kota',
        'nama_toko',
        'deskripsi_toko',
        'alamat_toko',
        'nomor_hp_toko',
        'bank',
        'rekening',
        'pemilik_rekening',
        'profile_photo_path',
        'workers_id',
        'exp_date',
        'phones',
        'banks',
        'foto_portal',
        'color_portal',
        'ig',
        'fb',
        'tiktok',
        'bagian_teknisi',
        'pdf_investor',
        'foto_login',
        'tipe_bonus_admin',
        'nominal_bonus_admin',
        'shift_id',
        'cabang_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = [
    //     'profile_photo_url',
    // ];

    public function type()
    {
        return $this->belongsTo(
            Type::class,
            'types_id',
            'id'
        );
    }

    public function servicetransaction()
    {
        $currentMonth = now()->month;

        return $this->hasMany(ServiceTransaction::class, 'users_id', 'id')
            ->where('cabang_id', getCabangId())
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', $currentMonth);
    }
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    public function prevservicetransaction()
    {
        $lastMonth = now()->subMonth(); // Mendapatkan tanggal bulan sebelumnya

        return $this->hasMany(ServiceTransaction::class, 'users_id', 'id')
            ->where('is_approve', 'Setuju')
            ->where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', $lastMonth);
    }

    public function relasiService()
    {
        return $this->hasMany(ServiceTransaction::class, 'users_id', 'id');
    }

    public function sale()
    {
        $currentMonth = now()->month;

        return $this->hasMany(OrderDetail::class, 'users_id', 'id')
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', getCabangId())
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            });
    }

    public function prevsale()
    {
        $lastMonth = now()->subMonth(); // Mendapatkan tanggal bulan sebelumnya

        return $this->hasMany(OrderDetail::class, 'users_id', 'id')
            ->whereHas('order', function ($query) use ($lastMonth) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', getCabangId())
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $lastMonth);
            });
    }

    public function relasiSale()
    {
        return $this->hasMany(OrderDetail::class, 'users_id', 'id');
    }

    public function expense()
    {
        return $this->hasMany(Expense::class, 'users_id', 'id');
    }

    public function salary()
    {
        return $this->hasMany(Salary::class, 'users_id', 'id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'workers_id', 'id');
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function adminservice()
    {
        $currentMonth = now()->month;

        return $this->hasMany(ServiceTransaction::class, 'admin_id', 'id')
            ->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', $currentMonth);
    }

    public function prevadminservice()
    {
        $lastMonth = now()->subMonth(); // Mendapatkan tanggal bulan sebelumnya

        return $this->hasMany(ServiceTransaction::class, 'admin_id', 'id')
            ->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', now()->year)
            ->whereMonth('tgl_disetujui', $lastMonth);
    }

    public function adminsale()
    {
        $currentMonth = now()->month;

        return $this->hasMany(OrderDetail::class, 'admin_id', 'id')
            ->whereHas('order', function ($query) use ($currentMonth) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', getCabangId())
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            });
    }

    public function prevadminsale()
    {
        $lastMonth = now()->subMonth(); // Mendapatkan tanggal bulan sebelumnya

        return $this->hasMany(OrderDetail::class, 'admin_id', 'id')
            ->whereHas('order', function ($query) use ($lastMonth) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', getCabangId())
                    ->whereYear('tgl_disetujui', now()->year)
                    ->whereMonth('tgl_disetujui', $lastMonth);
            });
    }

    public function targetServis()
    {
        $currentMonth = now()->month;

        return $this->hasMany(TeknisiTarget::class, 'users_id', 'id')
            ->whereYear('created_at', now()->year)
            ->where('cabang_id', getCabangId())
            ->whereMonth('created_at', $currentMonth);
    }

    public function targetSale()
    {
        $currentMonth = now()->month;

        return $this->hasMany(SalesTarget::class, 'users_id', 'id')
            ->whereYear('created_at', now()->year)
            ->where('cabang_id', getCabangId())
            ->whereMonth('created_at', $currentMonth);
    }
}
