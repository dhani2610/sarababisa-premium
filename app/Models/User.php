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
use NotificationChannels\WebPush\HasPushSubscriptions;
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable,HasPushSubscriptions;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $guarded = [];

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

    // ... import yang mungkin dibutuhkan
    // use Carbon\Carbon;

    // 1. Fungsi Filter Transaksi Service
    public function filteredServiceTransaction()
    {
        // Ambil bulan dari request, jika tidak ada pakai bulan sekarang
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(ServiceTransaction::class, 'users_id', 'id')
            ->where('cabang_id', getCabangId())
            ->where('is_approve', 'Setuju')
            ->whereYear('tgl_disetujui', $date->year)
            ->whereMonth('tgl_disetujui', $date->month);
    }

    // 2. Fungsi Filter Penjualan (Sale)
    public function filteredSale()
    {
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(OrderDetail::class, 'users_id', 'id')
            ->whereHas('order', function ($query) use ($date) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', getCabangId())
                    ->whereYear('tgl_disetujui', $date->year)
                    ->whereMonth('tgl_disetujui', $date->month);
            });
    }

    // 3. Fungsi Filter Admin Service (Opsional, jika admin juga ditampilkan)
    public function filteredAdminService()
    {
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(ServiceTransaction::class, 'admin_id', 'id')
            ->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->where('cabang_id', getCabangId())
            ->whereYear('tgl_disetujui', $date->year)
            ->whereMonth('tgl_disetujui', $date->month);
    }

    // 4. Fungsi Filter Admin Sale (Opsional)
    public function filteredAdminSale()
    {
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(OrderDetail::class, 'admin_id', 'id')
        ->where('total', '>',0)
            ->whereHas('order', function ($query) use ($date) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', getCabangId())
                    ->whereYear('tgl_disetujui', $date->year)
                    ->whereMonth('tgl_disetujui', $date->month);
            });
    }

    public function filteredTargetServis()
    {
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(TeknisiTarget::class, 'users_id', 'id')
            ->whereYear('created_at', $date->year)
            ->where('cabang_id', getCabangId())
            ->whereMonth('created_at', $date->month);
    }

    // public function filteredTargetSale()
    // {
    //     $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

    //     return $this->hasMany(SalesTarget::class, 'users_id', 'id')
    //         ->whereYear('created_at', $date->year)
    //         ->where('cabang_id', getCabangId())
    //         ->whereMonth('created_at', $date->month);
    // }

    // ... fungsi lainnya di Model User

    public function filteredTargetSale()
    {
        $date = request('filter_month') ? \Carbon\Carbon::parse(request('filter_month')) : now();

        return $this->hasMany(SalesTarget::class, 'users_id', 'id')
            ->whereYear('created_at', $date->year)
            ->where('cabang_id', getCabangId())
            ->whereMonth('created_at', $date->month);
    }

    /**
     * Fungsi baru untuk menghitung progres dan bonus target teknisi
     */
    public function getTargetTeknisiStats($totalBonus)
{
    $targets = $this->filteredTargetServis;
    $services = $this->filteredServiceTransaction;

    // Jika teknisi tidak punya target bulan ini
    if ($targets->isEmpty()) {
        return [
            'target_text'   => '-',
            'progres_text'  => '-',
            'reward'        => 0,
            'achieved_text' => '-' // <-- TAMBAHAN: Default kosong jika tidak ada target
        ];
    }

    $tipeTarget = $targets->first()->tipe;

    if ($tipeTarget == 'nominal') {
        $achieved = $services->sum('profit');
        $targetValue = $targets->sum('nominal');
        $targetText = 'Rp ' . number_format($targetValue, 0, ',', '.');

        // <-- TAMBAHAN: Format teks pencapaian nominal
        $achievedText = 'Rp ' . number_format($achieved, 0, ',', '.');
    } else {
        $achieved = $services->count();
        $targetValue = $targets->sum('item');
        $targetText = $targetValue . ' Item';

        // <-- TAMBAHAN: Format teks pencapaian item
        $achievedText = $achieved . ' Item';
    }

    // Hitung persentase progres
    $progres = $targetValue > 0 ? ($achieved / $targetValue) * 100 : 0;

    // Batasi progres maksimal di 100% jika diinginkan
    $progresLimit = $progres > 100 ? 100 : $progres;

    // Hitung nominal bonus pencapaian
    if ($achieved < $targetValue) {
        $reward = $totalBonus * ($progresLimit / 100);
    } else {
        $reward = $totalBonus;
    }

    return [
        'target_text'   => $targetText,
        'progres_text'  => number_format($progresLimit, 1, ',', '.') . '%',
        'reward'        => $reward,
        'achieved_text' => $achievedText // <-- TAMBAHAN: Lempar ke view
    ];
}
}
