<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataArchive extends Model
{
    protected $table = 'data_archives';

    protected $fillable = [
        'cabang_id',
        'module_key',
        'module_name',
        'start_date',
        'end_date',
        'file_name',
        'file_path',
        'file_size',
        'record_count',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
