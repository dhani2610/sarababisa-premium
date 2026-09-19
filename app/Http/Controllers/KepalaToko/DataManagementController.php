<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Mail\BackupDataMail;
use App\Models\DataArchive;
use App\Models\DataAuditLog;
use App\Models\StoreSetting;
use App\Services\DataBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class DataManagementController extends Controller
{
    protected $backupService;

    public function __construct(DataBackupService $backupService)
    {
        $this->middleware('auth');
        $this->backupService = $backupService;
    }

    /**
     * Check if user is Kepala Toko
     */
    protected function authorizeKepalaToko()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['Kepala Toko', 'Super Admin'])) {
            abort(403, 'Akses khusus Kepala Toko.');
        }
    }

    /**
     * View Arsip Data Page
     */
    public function indexArsip(Request $request)
    {
        $this->authorizeKepalaToko();
        $modules = DataBackupService::modules();

        return view('pages.kepalatoko.arsip-data.index', compact('modules'));
    }

    /**
     * Get Arsip Data for Yajra DataTables
     */
    public function getArsipData(Request $request)
    {
        $this->authorizeKepalaToko();
        $cabangId = getCabangId();

        $query = DataArchive::query();
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        if ($request->filled('module')) {
            $query->where('module_key', $request->module);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            });
        }

        $query->latest('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('created_at_formatted', function ($row) {
                $createdAt = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : now();
                return '<div class="font-semibold text-slate-800">' . $createdAt->format('d M Y') . '</div>' .
                       '<div class="text-[11px] text-slate-400 font-mono">' . $createdAt->format('H:i:s') . ' WIB</div>';
            })
            ->addColumn('module_formatted', function ($row) {
                return '<div class="font-medium text-slate-800 text-xs">' . e($row->module_name) . '</div>' .
                       '<div class="text-[10px] text-slate-400 font-mono">' . e($row->module_key) . '</div>';
            })
            ->addColumn('periode', function ($row) {
                $start = $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') : '-';
                $end = $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d/m/Y') : '-';
                return '<span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-200">' . $start . ' s/d ' . $end . '</span>';
            })
            ->addColumn('record_count_formatted', function ($row) {
                return '<span class="font-semibold text-slate-800 font-mono text-xs">' . number_format($row->record_count) . '</span> <span class="text-[10px] text-slate-400">baris</span>';
            })
            ->addColumn('file_size_formatted', function ($row) {
                return '<span class="inline-flex px-1.5 py-0.5 rounded text-[11px] font-mono bg-slate-100 text-slate-700">' . ($row->file_size ?: '0 KB') . '</span>';
            })
            ->addColumn('created_by_formatted', function ($row) {
                return '<div class="text-xs font-medium text-slate-700">' . e($row->created_by ?: 'Kepala Toko') . '</div>';
            })
            ->addColumn('aksi', function ($row) {
                $downloadUrl = route('arsip-data.download', $row->id);
                $deleteUrl = route('arsip-data.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                <div class="flex items-center justify-center gap-1.5">
                    <a href="' . $downloadUrl . '" class="btn bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs px-2.5 py-1.5 rounded-lg border border-indigo-200 inline-flex items-center gap-1 shadow-sm" title="Unduh File SQL">
                        <svg style="width: 12px; height: 12px; min-width: 12px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h2.5V2h3v6H12L8 12zM2 14v-2h12v2H2z"/>
                        </svg>
                        <span>Unduh</span>
                    </a>
                    <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirmDeleteArchive(event, this)" class="inline">
                        ' . $csrf . '
                        ' . $method . '
                        <button type="submit" class="btn bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs px-2.5 py-1.5 rounded-lg border border-rose-200 inline-flex items-center gap-1 shadow-sm" title="Hapus Arsip">
                            <svg style="width: 12px; height: 12px; min-width: 12px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                                <path d="M5 2V1h6v1h4v2H1V2h4zm1 3h2v8H6V5zm4 0h2v8h-2V5z"/>
                            </svg>
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>';
            })
            ->rawColumns(['created_at_formatted', 'module_formatted', 'periode', 'record_count_formatted', 'file_size_formatted', 'created_by_formatted', 'aksi'])
            ->make(true);
    }

    /**
     * Download an archive by ID
     */
    public function downloadArsip($id)
    {
        $this->authorizeKepalaToko();
        $cabangId = getCabangId();

        $archive = DataArchive::findOrFail($id);
        if ($cabangId && $archive->cabang_id && $archive->cabang_id != $cabangId) {
            abort(403, 'Tidak memiliki akses ke arsip cabang ini.');
        }

        if (!File::exists($archive->file_path)) {
            abort(404, 'File backup fisik tidak ditemukan di server.');
        }

        DataAuditLog::record([
            'action' => 'DOWNLOAD_ARSIP',
            'module_key' => $archive->module_key,
            'module_name' => $archive->module_name,
            'start_date' => $archive->start_date,
            'end_date' => $archive->end_date,
            'file_name' => $archive->file_name,
            'record_count' => $archive->record_count,
            'description' => "Mengunduh file arsip {$archive->file_name} (Modul {$archive->module_name}, Periode " . ($archive->start_date ? $archive->start_date->format('Y-m-d') : '-') . " s/d " . ($archive->end_date ? $archive->end_date->format('Y-m-d') : '-') . ").",
        ]);

        return response()->download($archive->file_path, $archive->file_name, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Delete an archive record
     */
    public function deleteArsip($id)
    {
        $this->authorizeKepalaToko();
        $cabangId = getCabangId();

        $archive = DataArchive::findOrFail($id);
        if ($cabangId && $archive->cabang_id && $archive->cabang_id != $cabangId) {
            abort(403, 'Tidak memiliki akses ke arsip cabang ini.');
        }

        if (File::exists($archive->file_path)) {
            File::delete($archive->file_path);
        }

        DataAuditLog::record([
            'action' => 'HAPUS_ARSIP',
            'module_key' => $archive->module_key,
            'module_name' => $archive->module_name,
            'start_date' => $archive->start_date,
            'end_date' => $archive->end_date,
            'file_name' => $archive->file_name,
            'record_count' => $archive->record_count,
            'description' => "Menghapus file arsip {$archive->file_name} (Modul {$archive->module_name}, Periode " . ($archive->start_date ? $archive->start_date->format('Y-m-d') : '-') . " s/d " . ($archive->end_date ? $archive->end_date->format('Y-m-d') : '-') . ") dari server VPS.",
        ]);

        $archive->delete();

        return redirect()->back()->with('success', 'Arsip backup berhasil dihapus.');
    }

    /**
     * Download generated backup directly
     */
    public function downloadDirect($fileName)
    {
        $this->authorizeKepalaToko();
        $safeName = basename($fileName);
        $path = storage_path("app/backups/{$safeName}");

        if (!File::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        DataAuditLog::record([
            'action' => 'DOWNLOAD_LANGSUNG',
            'file_name' => $safeName,
            'description' => "Mengunduh langsung file backup {$safeName} dari modal browser.",
        ]);

        return response()->download($path, $safeName, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Execute Backup:
     * 1. Export SQL
     * 2. Save in DataArchive (VPS storage)
     * 3. Send email to configured backup_email if available
     * 4. Return direct download link & notification info
     */
    public function backup(Request $request)
    {
        $this->authorizeKepalaToko();

        $request->validate([
            'module' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $moduleKey = $request->module;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $cabangId = getCabangId();
        $cabangName = getCabangName($cabangId) ?: 'Cabang Utama';

        try {
            // 1. Export SQL
            $result = $this->backupService->exportSql($moduleKey, $startDate, $endDate, $cabangId);

            $fileSize = File::exists($result['file_path']) ? File::size($result['file_path']) : 0;
            $formattedSize = $this->formatFileSize($fileSize);

            // 2. Save to DataArchive (VPS archive record)
            $archive = DataArchive::create([
                'cabang_id' => $cabangId,
                'module_key' => $moduleKey,
                'module_name' => $result['module_name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'file_name' => $result['file_name'],
                'file_path' => $result['file_path'],
                'file_size' => $formattedSize,
                'record_count' => $result['record_count'],
                'created_by' => Auth::user()->name ?? 'Kepala Toko',
            ]);

            // 3. Send Email
            $setting = StoreSetting::where('cabang_id', $cabangId)->first();
            $backupEmail = $setting ? $setting->backup_email : null;
            $emailSent = false;
            $emailError = null;

            if (!empty($backupEmail)) {
                try {
                    Mail::to($backupEmail)->send(new BackupDataMail(
                        $result['module_name'],
                        $startDate,
                        $endDate,
                        $cabangName,
                        $result['record_count'],
                        $result['file_path'],
                        $result['file_name']
                    ));
                    $emailSent = true;
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim email backup ke {$backupEmail}: " . $e->getMessage());
                    $emailError = $e->getMessage();
                }
            }

            // Record Audit Log
            DataAuditLog::record([
                'action' => 'BACKUP',
                'module_key' => $moduleKey,
                'module_name' => $result['module_name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'record_count' => $result['record_count'],
                'file_name' => $result['file_name'],
                'description' => "Melakukan backup data modul {$result['module_name']} periode {$startDate} s/d {$endDate} ({$result['record_count']} data). File disimpan ke arsip VPS" . ($emailSent ? " & dikirim ke email {$backupEmail}." : "."),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Backup data berhasil dibuat dan diarsipkan.',
                'file_name' => $result['file_name'],
                'download_url' => route('data-management.download', ['fileName' => $result['file_name']]),
                'record_count' => $result['record_count'],
                'module_name' => $result['module_name'],
                'email_configured' => !empty($backupEmail),
                'email_recipient' => $backupEmail,
                'email_sent' => $emailSent,
                'email_error' => $emailError,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if archive exists for given module and date range before deletion
     */
    public function checkArchiveStatus(Request $request)
    {
        $this->authorizeKepalaToko();

        $request->validate([
            'module' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $moduleKey = $request->module;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $cabangId = getCabangId();

        $archive = DataArchive::where('module_key', $moduleKey)
            ->where('cabang_id', $cabangId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sub) use ($startDate, $endDate) {
                    $sub->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                })->orWhere(function ($sub) use ($startDate, $endDate) {
                    $sub->where('start_date', $startDate)
                        ->where('end_date', $endDate);
                });
            })
            ->latest()
            ->first();

        if (!$archive) {
            return response()->json([
                'has_archive' => false,
                'message' => 'Peringatan: Belum ada arsip backup untuk modul dan periode ini! Anda WAJIB melakukan Backup terlebih dahulu sebelum menghapus data.',
            ]);
        }

        return response()->json([
            'has_archive' => true,
            'archive' => [
                'file_name' => $archive->file_name,
                'record_count' => $archive->record_count,
                'file_size' => $archive->file_size,
                'created_at' => $archive->created_at->format('d/m/Y H:i'),
            ],
            'message' => "Arsip backup ditemukan: {$archive->file_name} ({$archive->record_count} data, ukuran {$archive->file_size}). Pastikan file backup sudah terisi data yang sesuai sebelum menghapus data dari database.",
        ]);
    }

    /**
     * Verify uploaded SQL backup file and preview before deletion
     */
    public function verifyFileForDelete(Request $request)
    {
        $this->authorizeKepalaToko();

        $request->validate([
            'module' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $moduleKey = $request->module;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $cabangId = getCabangId();

        try {
            $sqlContent = File::get($request->file('file')->getRealPath());
            $fileName = $request->file('file')->getClientOriginalName();

            $result = $this->backupService->verifyFileMatch($sqlContent, $moduleKey, $startDate, $endDate, $cabangId);

            if (!$result['matched']) {
                return response()->json([
                    'status' => 'error',
                    'matched' => false,
                    'message' => $result['message'],
                ], 422);
            }

            // Check if archive also recorded in database
            $archiveExists = DataArchive::where('module_key', $moduleKey)
                ->where('cabang_id', $cabangId)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('start_date', $startDate)
                            ->where('end_date', $endDate);
                    });
                })
                ->exists();

            return response()->json([
                'status' => 'success',
                'matched' => true,
                'file_name' => $fileName,
                'archive_in_db' => $archiveExists,
                'table_name' => $result['table_name'],
                'total_records' => $result['total_records'],
                'file_period' => $result['file_period'],
                'columns' => $result['columns'],
                'preview_rows' => $result['preview_rows'],
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'matched' => false,
                'message' => 'Gagal memverifikasi file: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete data for selected module & date range after archive verification
     */
    public function deleteData(Request $request)
    {
        $this->authorizeKepalaToko();

        $request->validate([
            'module' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $moduleKey = $request->module;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $cabangId = getCabangId();

        // If user uploaded a verification file, verify it
        if ($request->hasFile('file')) {
            $sqlContent = File::get($request->file('file')->getRealPath());
            $verify = $this->backupService->verifyFileMatch($sqlContent, $moduleKey, $startDate, $endDate, $cabangId);
            if (!$verify['matched']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Penghapusan ditolak! ' . $verify['message'],
                ], 422);
            }
        } else {
            // Check if archive exists for this module & period
            $archive = DataArchive::where('module_key', $moduleKey)
                ->where('cabang_id', $cabangId)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($sub) use ($startDate, $endDate) {
                        $sub->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    })->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('start_date', $startDate)
                            ->where('end_date', $endDate);
                    });
                })
                ->latest()
                ->first();

            if (!$archive) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Penghapusan ditolak! Tidak ditemukan data arsip backup untuk modul dan periode ini. Silakan lakukan Backup dan unggah file verifikasi terlebih dahulu.',
                ], 422);
            }
        }

        try {
            $deletedCount = $this->backupService->deleteData($moduleKey, $startDate, $endDate, $cabangId);

            $config = DataBackupService::getModuleConfig($moduleKey);
            $moduleName = $config['name'] ?? $moduleKey;

            // Record Audit Log
            DataAuditLog::record([
                'action' => 'HAPUS',
                'module_key' => $moduleKey,
                'module_name' => $moduleName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'record_count' => $deletedCount,
                'description' => "Menghapus data modul {$moduleName} periode {$startDate} s/d {$endDate} ({$deletedCount} data berhasil dihapus dari database).",
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menghapus {$deletedCount} data pada periode tersebut.",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview SQL content before restore
     */
    public function preview(Request $request)
    {
        $this->authorizeKepalaToko();

        try {
            $sqlContent = '';

            if ($request->has('archive_id')) {
                $archive = DataArchive::findOrFail($request->archive_id);
                if (!File::exists($archive->file_path)) {
                    return response()->json(['status' => 'error', 'message' => 'File arsip tidak ditemukan di server.'], 404);
                }
                $sqlContent = File::get($archive->file_path);
            } elseif ($request->hasFile('file')) {
                $request->validate([
                    'file' => 'required|file|max:51200', // max 50MB
                ]);
                $sqlContent = File::get($request->file('file')->getRealPath());
            } else {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada file SQL yang diunggah.'], 400);
            }

            $parsed = $this->backupService->parseSqlForPreview($sqlContent);

            return response()->json([
                'status' => 'success',
                'primary_table' => $parsed['primary_table'],
                'all_tables' => $parsed['all_tables'],
                'columns' => $parsed['columns'],
                'total_records' => $parsed['total_records'],
                'preview_rows' => $parsed['preview_rows'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membaca format file SQL: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Restore chunk of rows
     */
    public function restoreChunk(Request $request)
    {
        $this->authorizeKepalaToko();

        $request->validate([
            'table_name' => 'required|string',
            'rows' => 'required|array',
        ]);

        $tableName = $request->table_name;
        $rows = $request->rows;
        $cabangId = getCabangId();

        try {
            $result = $this->backupService->restoreChunk($tableName, $rows, $cabangId);

            return response()->json([
                'status' => 'success',
                'restored' => $result['restored'],
                'skipped' => $result['skipped'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal saat memulihkan chunk data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log completed restore operation
     */
    public function logRestore(Request $request)
    {
        $this->authorizeKepalaToko();

        $moduleKey = $request->module_key;
        $config = DataBackupService::getModuleConfig($moduleKey);
        $moduleName = $config['name'] ?? ($moduleKey ?: 'Data');
        $totalRestored = (int) $request->total_restored;
        $totalSkipped = (int) $request->total_skipped;
        $totalRows = (int) $request->total_rows;
        $fileName = $request->file_name;

        DataAuditLog::record([
            'action' => 'RESTORE',
            'module_key' => $moduleKey,
            'module_name' => $moduleName,
            'file_name' => $fileName,
            'record_count' => $totalRestored,
            'description' => "Memulihkan data modul {$moduleName}: {$totalRestored} data baru berhasil disimpan, {$totalSkipped} data duplikat dilewati (total {$totalRows} data)." . ($fileName ? " File: {$fileName}" : ""),
        ]);

        return response()->json(['status' => 'success']);
    }

    protected function formatFileSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
    }
}
