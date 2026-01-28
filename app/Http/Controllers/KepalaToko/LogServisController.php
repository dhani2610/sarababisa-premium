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

    public function destroyPenjualan()
    {
        Activity::where('log_name', 'Transaksi Penjualan')->truncate();
        return redirect()->route('log-penjualan')->with('success', 'Semua log aktivitas penjualan berhasil dihapus.');
    }

    public function indexPenjualan()
    {
        return view('pages/kepalatoko/log-penjualan');
    }

    public function getDataPenjualan(Request $request)
    {
        // Ambil Log Transaksi Penjualan
        // Load relasi: subject (Order) -> detailOrders (Barang yang dibeli)
        $query = Activity::where('log_name', 'Transaksi Penjualan')
            ->with(['causer', 'subject.detailOrders'])
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()

            // 1. Kolom Tanggal
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i');
            })

            // 2. Kolom Nomor Invoice
            ->addColumn('nomor_invoice', function ($row) {
                if ($row->subject) {
                    return '<div class="font-medium">#' . $row->subject->invoice_no . '</div>';
                }
                return '<div class="font-medium text-rose-600">Data Order Terhapus</div>';
            })

            // 3. Kolom Pelanggan
            ->addColumn('pelanggan', function ($row) {
                if ($row->subject) {
                    return '<div class="font-medium">' . ($row->subject->nama_pelanggan ?? '-') . '</div>';
                }
                return '-';
            })

            // 4. Kolom Pembuat (User yang melakukan aksi)
            ->addColumn('pembuat', function ($row) {
                if ($row->causer) {
                    return '<div class="font-medium">' . $row->causer->name . '</div>';
                }
                return '<div class="font-medium text-rose-600">System / Akun Terhapus</div>';
            })

            // 5. Kolom Nama Barang (Relasi ke OrderDetail)
            ->addColumn('nama_barang', function ($row) {
                // Cek apakah subject (Order) masih ada
                if ($row->subject) {
                    // Cek apakah ada detail order
                    if ($row->subject->detailOrders->isNotEmpty()) {
                        // Ambil semua product_name dan gabungkan dengan koma
                        $products = $row->subject->detailOrders->pluck('product_name')->toArray();

                        // Tampilkan sebagai list bullet agar rapi
                        $html = '<ul class="list-disc list-inside text-sm">';
                        foreach($products as $p){
                            $html .= '<li>'.$p.'</li>';
                        }
                        $html .= '</ul>';

                        return $html;
                    }
                    return '<span class="text-slate-400 italic">Tidak ada item</span>';
                }
                return '-';
            })

            // 6. Kolom Sebelum (Old Values)
            ->addColumn('sebelum', function ($row) {
                if ($row->description === 'created') {
                    return '<div class="font-medium text-blue-600">Data Baru</div>';
                }

                $content = '';
                $old = $row->changes['old'] ?? [];

                if (is_array($old) && count($old) > 0) {
                    foreach ($old as $key => $val) {
                        // Skip kolom timestamps biar ga penuh
                        if(in_array($key, ['created_at', 'updated_at'])) continue;
                        $content .= '<strong>'.$key . '</strong> : ' . $val . '<br>';
                    }
                } else {
                    return '<div class="text-slate-400">-</div>';
                }

                return '<div class="font-medium text-orange-600 text-sm">' . $content . '</div>';
            })

            // 7. Kolom Sesudah (New Values)
            ->addColumn('sesudah', function ($row) {
                if ($row->description === 'deleted') {
                    return '<div class="font-medium text-rose-600">Data Dihapus</div>';
                }

                $content = '';
                $attributes = $row->changes['attributes'] ?? [];

                if (is_array($attributes) && count($attributes) > 0) {
                    foreach ($attributes as $key => $val) {
                         // Skip kolom timestamps
                        if(in_array($key, ['created_at', 'updated_at'])) continue;
                        $content .= '<strong>'.$key . '</strong> : ' . $val . '<br>';
                    }
                } else {
                     // Jika created, tampilkan semua attributes
                     if($row->description === 'created'){
                         return '<div class="text-blue-600">Detail Invoice dibuat</div>';
                     }
                     return '<div class="text-slate-400">-</div>';
                }

                return '<div class="font-medium text-blue-600 text-sm">' . $content . '</div>';
            })

            ->rawColumns(['nomor_invoice', 'pelanggan', 'pembuat', 'nama_barang', 'sebelum', 'sesudah'])
            ->make(true);
    }
}
