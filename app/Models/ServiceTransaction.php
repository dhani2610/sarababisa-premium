<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceTransaction extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'users_id',
        'penerima',
        'nama_pelanggan',
        'nomor_servis',
        'customers_id',
        'types_id',
        'brands_id',
        'model_series_id',
        'nama_barang',
        'imei',
        'warna',
        'capacities_id',
        'kerusakan',
        'qc_masuk',
        'qc_keluar',
        'garansi',
        'exp_garansi',
        'estimasi_biaya',
        'estimasi_pengerjaan',
        'uang_muka',
        'status_servis',
        'kelengkapan',
        'kondisi_servis',
        'catatan',
        'cara_pembayaran',
        'biaya',
        'tindakan_servis',
        'service_actions_id',
        'products_id',
        'modal_sparepart',
        'is_approve',
        'diskon',
        'created_at',
        'tgl_ambil',
        'pengambil',
        'tgl_selesai',
        'tgl_disetujui',
        'is_admin_toko',
        'admin_id',
        'omzet',
        'profit',
        'profittoko',
        'persen_admin',
        'persen_teknisi',
        'penyerah',
        'pay',
        'due',
        'tempo',
        'tunai',
        'transfer',
        'service_actions',
        'products',
        'exp_garansi_j',
        'biaya_j',
        "modal_j",
        "pin",
        "pola",
        "ppn",
        "bonus_interface",
        "tipe",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Transaksi Servis')
            ->logOnly(['modal_sparepart', 'biaya', 'uang_muka','payment_method','tunai','transfer','ppn','diskon', 'imei', 'kerusakan', 'qc_masuk', 'tindakan_servis', 'kelengkapan', 'created_at', 'penerima', 'nama_pelanggan', 'status_servis', 'kondisi_servis', 'users_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
        // Chain fluent methods for configuration options
    }


    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'types_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brands_id', 'id');
    }

    public function modelserie()
    {
        return $this->belongsTo(ModelSerie::class, 'model_series_id', 'id');
    }

    public function capacity()
    {
        return $this->belongsTo(Capacity::class, 'capacities_id', 'id');
    }

    public function serviceaction()
    {
        return $this->belongsTo(ServiceAction::class, 'service_actions_id', 'id');
    }
}
