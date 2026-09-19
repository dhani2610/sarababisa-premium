<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Models\DataAuditLog;
use App\Services\DataBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DataAuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeKepalaToko()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['Kepala Toko', 'Super Admin'])) {
            abort(403, 'Akses khusus Kepala Toko.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeKepalaToko();
        $cabangId = getCabangId();

        // Summary counts
        $baseQuery = DataAuditLog::query();
        if ($cabangId) {
            $baseQuery->where(function ($q) use ($cabangId) {
                $q->where('cabang_id', $cabangId)->orWhereNull('cabang_id');
            });
        }

        $totalAktivitas = (clone $baseQuery)->count();
        $totalBackup = (clone $baseQuery)->where('action', 'BACKUP')->count();
        $totalHapus = (clone $baseQuery)->where('action', 'HAPUS')->count();
        $totalRestore = (clone $baseQuery)->where('action', 'RESTORE')->count();

        $modules = DataBackupService::modules();

        return view('pages.kepalatoko.audit-log.index', compact(
            'modules',
            'totalAktivitas',
            'totalBackup',
            'totalHapus',
            'totalRestore'
        ));
    }

    /**
     * Get Audit Log Data for Yajra DataTables
     */
    public function getData(Request $request)
    {
        $this->authorizeKepalaToko();
        $cabangId = getCabangId();

        $query = DataAuditLog::query();

        if ($cabangId) {
            $query->where(function ($q) use ($cabangId) {
                $q->where('cabang_id', $cabangId)->orWhereNull('cabang_id');
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module_key', $request->module);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhere('file_name', 'like', $search)
                  ->orWhere('ip_address', 'like', $search);
            });
        }

        $query->latest('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('waktu', function ($row) {
                $createdAt = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : now();
                return '<div class="font-semibold text-slate-800">' . $createdAt->format('d M Y') . '</div>' .
                       '<div class="text-xs text-indigo-600 font-mono font-medium">' . $createdAt->format('H:i:s') . ' WIB</div>' .
                       '<div class="text-[10px] text-slate-400">' . $createdAt->diffForHumans() . '</div>';
            })
            ->addColumn('pengguna', function ($row) {
                return '<div class="font-medium text-slate-800 text-xs">' . e($row->user_name ?? 'Sistem') . '</div>' .
                       '<span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">' . e($row->user_role ?? 'User') . '</span>';
            })
            ->editColumn('action', function ($row) {
                if ($row->action === 'BACKUP') {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">BACKUP</span>';
                } elseif ($row->action === 'HAPUS') {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">HAPUS</span>';
                } elseif ($row->action === 'RESTORE') {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">RESTORE</span>';
                } elseif ($row->action === 'DOWNLOAD_ARSIP' || $row->action === 'DOWNLOAD_LANGSUNG') {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">UNDUH</span>';
                } elseif ($row->action === 'HAPUS_ARSIP') {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">HAPUS ARSIP</span>';
                }
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">' . e($row->action) . '</span>';
            })
            ->editColumn('module_name', function ($row) {
                return '<span class="font-medium text-slate-800 text-xs">' . e($row->module_name ?? ($row->module_key ?: '-')) . '</span>';
            })
            ->addColumn('periode', function ($row) {
                if ($row->start_date && $row->end_date) {
                    $start = \Carbon\Carbon::parse($row->start_date)->format('d/m/Y');
                    $end = \Carbon\Carbon::parse($row->end_date)->format('d/m/Y');
                    return '<span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-200">' . $start . ' s/d ' . $end . '</span>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            ->editColumn('record_count', function ($row) {
                if ($row->record_count > 0) {
                    return '<span class="font-semibold text-slate-800 font-mono">' . number_format($row->record_count) . '</span> <span class="text-[10px] text-slate-400">baris</span>';
                }
                return '<span class="text-slate-400">-</span>';
            })
            ->editColumn('description', function ($row) {
                $desc = '<div class="text-xs leading-relaxed text-slate-700">' . e($row->description) . '</div>';
                if ($row->file_name) {
                    $desc .= '<div class="mt-0.5 text-[10px] text-slate-500 font-mono bg-slate-50 px-1.5 py-0.5 rounded border border-slate-200 inline-block truncate max-w-xs">' . e($row->file_name) . '</div>';
                }
                return $desc;
            })
            ->addColumn('ip_perangkat', function ($row) {
                $ip = '<span class="font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-slate-700">' . e($row->ip_address ?: '127.0.0.1') . '</span>';
                if ($row->user_agent) {
                    $ip .= '<div class="text-[10px] text-slate-400 truncate max-w-[130px] mt-0.5" title="' . e($row->user_agent) . '">' . e(\Illuminate\Support\Str::limit($row->user_agent, 20)) . '</div>';
                }
                return $ip;
            })
            ->rawColumns(['waktu', 'pengguna', 'action', 'module_name', 'periode', 'record_count', 'description', 'ip_perangkat'])
            ->make(true);
    }
}
