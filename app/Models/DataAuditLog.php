<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataAuditLog extends Model
{
    use HasFactory;

    protected $table = 'data_audit_logs';

    protected $fillable = [
        'cabang_id',
        'user_id',
        'user_name',
        'user_role',
        'action',
        'module_key',
        'module_name',
        'start_date',
        'end_date',
        'record_count',
        'file_name',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    /**
     * Helper to log an audit event quickly
     */
    public static function record(array $data)
    {
        try {
            return self::create(array_merge([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Kepala Toko',
                'user_role' => auth()->user()->role ?? 'Kepala Toko',
                'cabang_id' => function_exists('getCabangId') ? getCabangId() : (auth()->user()->cabang_id ?? null),
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            ], $data));
        } catch (\Throwable $e) {
            // Non-blocking fallback so audit log failure never breaks core operations
            \Log::error('DataAuditLog recording failed: ' . $e->getMessage());
            return null;
        }
    }
}
