<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class LogServisController extends Controller
{
    public function index()
    {
        return view('pages/kepalatoko/log-servis');
    }

    public function getData(Request $request)
    {
        // Eager load semua relasi yang dibutuhkan untuk kolom "Nama Barang"
        $query = Activity::where('subject_type', 'App\Models\ServiceTransaction')
            ->with(['subject.type', 'subject.brand', 'subject.modelserie', 'causer'])
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn() // No.

            // Kolom Waktu
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i');
            })

            // Kolom Akun
            ->addColumn('causer_name', function ($row) {
                if ($row->causer) {
                    return '<div class="font-medium">' . $row->causer->name . '</div>';
                }
                return '<div class="font-medium text-rose-600">Data akun telah dihapus</div>';
            })

            // Kolom Aktivitas
            ->editColumn('description', function ($row) {
                return '<div class="font-medium">' . $row->description . '</div>';
            })

            // Kolom Nomor Servis
            ->addColumn('nomor_servis', function ($row) {
                if ($row->subject) {
                    return '<div class="font-medium">#' . $row->subject->nomor_servis . '</div>';
                }
                return '<div class="font-medium text-rose-600">Data servis telah dihapus</div>';
            })

            // Kolom Pelanggan
            ->addColumn('pelanggan', function ($row) {
                if ($row->subject) {
                    return '<div class="font-medium">' . $row->subject->nama_pelanggan . '</div>';
                }
                return '<div class="font-medium text-rose-600">Data servis telah dihapus</div>';
            })

            // Kolom Nama Barang
            ->addColumn('nama_barang', function ($row) {
                if ($row->subject) {
                    // Safety check jika relasi di dalam subject ada yang null
                    $type = $row->subject->type->name ?? '';
                    $brand = $row->subject->brand->name ?? '';
                    $model = $row->subject->modelserie->name ?? '-';

                    return '<div class="font-medium">' . $type . ' ' . $brand . ' ' . $model . '</div>';
                }
                return '<div class="font-medium text-rose-600">Data servis telah dihapus</div>';
            })

            // Kolom Sebelum (Old Values)
            ->addColumn('sebelum', function ($row) {
                if ($row->description === 'deleted' || $row->description === 'created') {
                    return '<div class="font-medium text-orange-600">-</div>';
                }

                $content = '';
                // Mengambil data changes['old']
                $old = $row->changes['old'] ?? [];

                if (is_array($old)) {
                    foreach ($old as $key => $val) {
                        $content .= $key . ' : ' . $val . '<br>';
                    }
                }

                return '<div class="font-medium text-orange-600">' . $content . '</div>';
            })

            // Kolom Sesudah (New Values / Attributes)
            ->addColumn('sesudah', function ($row) {
                if ($row->description === 'deleted' || $row->description === 'created') {
                    return '<div class="font-medium text-blue-600">-</div>';
                }

                $content = '';
                // Mengambil data changes['attributes']
                $attributes = $row->changes['attributes'] ?? [];

                if (is_array($attributes)) {
                    foreach ($attributes as $key => $val) {
                        $content .= $key . ' : ' . $val . '<br>';
                    }
                }

                return '<div class="font-medium text-blue-600">' . $content . '</div>';
            })

            // Agar HTML tag (div, br, span) terbaca oleh browser
            ->rawColumns(['causer_name', 'description', 'nomor_servis', 'pelanggan', 'nama_barang', 'sebelum', 'sesudah'])

            // Logic Searching Global (Opsional, agar search bar berfungsi ke relasi)
            ->filterColumn('nomor_servis', function($query, $keyword) {
                $query->whereHas('subject', function($q) use($keyword) {
                    $q->where('nomor_servis', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('pelanggan', function($query, $keyword) {
                $query->whereHas('subject', function($q) use($keyword) {
                    $q->where('nama_pelanggan', 'like', "%{$keyword}%");
                });
            })

            ->make(true);
    }

    public function destroy($model)
    {
        Activity::where('subject_type', 'App\Models\ServiceTransaction')->truncate();
        return redirect()->route('log-servis')->with('success', 'Semua log aktivitas servis berhasil dihapus.');
    }
}
