<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\HistoryGaransi;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use App\Models\Product;
use App\Models\ServiceAction;
use App\Models\Refund;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
class HistoryGaransiController extends Controller
{
    public function index()
    {
        $users = User::where('id','!=',1)->where('cabang_id',getCabangId())->get();
        return view('pages.kepalatoko.history.garansi', compact(
            'users'
        ));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        $cabang = getCabangId();

        $query = HistoryGaransi::with(['service', 'teknisi', 'penerima', 'pelanggan'])
            ->where('cabang_id', $cabang)
            ->latest();

        // Filter Role Teknisi
        if ($user->role === 'Teknisi') {
            $query->where('teknisi_id', $user->id);
        }

        // Filter Status dari Tab
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="table-item form-checkbox" value="' . $row->id . '" />';
            })
            ->addIndexColumn() // No.
            ->addColumn('nomor_servis', function($row){
                return $row->service->nomor_servis ?? '';
            })
            ->addColumn('pelanggan_nama', function($row){
                return $row->pelanggan->nama ?? '-';
            })
            ->addColumn('penerima_nama', function($row){
                return $row->penerima->name ?? '-';
            })
            ->addColumn('teknisi_nama', function($row){
                return $row->teknisi->name ?? '-';
            })
            ->addColumn('qc_button', function($row){
                return '<a href="'.route('kepalatoko-cetak-qc-garansi',$row->id).'" target="_blank" class="btn bg-indigo-500 hover:bg-indigo-600 text-white btn-sm">Lihat QC</a>';
            })
            ->addColumn('tindakan_list', function($row){
                if (empty($row->tindakan)) return '-';

                $tindakans = json_decode($row->tindakan, true) ?? [];
                if(empty($tindakans)) return '-';

                $html = '<ul class="list-disc ml-4">';
                foreach ($tindakans as $t) {
                    $action = ServiceAction::find($t['id']);
                    $nama = $action ? $action->nama_tindakan : ($t['id_manual'] ?? '-');
                    $html .= '<li>'.$nama.' - Rp'.number_format($t['harga'], 0, ',', '.').'</li>';
                }
                $html .= '</ul>';
                return $html;
            })
            ->addColumn('sparepart_list', function($row){
                if (empty($row->sparepart)) return '-';

                $spareparts = json_decode($row->sparepart, true);
                if (!$spareparts) return '-';

                $html = '<ul class="list-disc ml-4">';
                foreach ($spareparts as $sp) {
                    $prd = Product::find($sp['id']);
                    $nama = $prd->product_name ?? 'Produk ID ' . $sp['id'];
                    $html .= '<li>'.$nama.' (x'.$sp['qty'].') - Rp'.number_format($sp['harga'], 0, ',', '.').'</li>';
                }
                $html .= '</ul>';
                return $html;
            })
            ->editColumn('total_biaya', function($row){
                return 'Rp' . number_format($row->total_biaya, 0, ',', '.');
            })
            ->addColumn('status_badge', function($row){
                $class = $row->status == 1 ? 'status-menunggu' : ($row->status == 2 ? 'status-selesai' : 'status-batal');
                $label = $row->status == 1 ? 'Diproses' : ($row->status == 2 ? 'Sudah Selesai' : 'Dibatalkan');

                // Button ini akan ditangkap oleh JS toggle-status yang sudah ada
                return '<center><button class="btn-status '.$class.' toggle-status" data-id="'.$row->id.'">'.$label.'</button></center>';
            })
            ->addColumn('aksi', function ($row) {
                // Edit Button
                $editUrl = route('history-garansi.edit', $row->id);
                $editBtn = '
                    <a href="' . $editUrl . '">
                        <button class="text-slate-400 hover:text-slate-500 rounded-full" title="Ubah">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                <rect x="9" y="3" width="6" height="4" rx="2" />
                                <path d="M9 14l2 2l4 -4" />
                            </svg>
                        </button>
                    </a>';

                // Print Button
                $printBtn = '';
                if (!empty($row->service)) {
                    $printUrl = route('history-garansi.cetak-inject', $row->id);
                    $printBtn = '
                    <a href="' . $printUrl . '" target="_blank">
                        <button class="text-slate-400 hover:text-slate-500 rounded-full" title="Cetak">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                <rect x="7" y="13" width="10" height="8" rx="2" />
                            </svg>
                        </button>
                    </a>';
                }

                // Delete Button (Trigger Custom Modal)
                $deleteBtn = '
                    <button class="text-rose-500 hover:text-rose-600 rounded-full" onclick="confirmDelete('.$row->id.')">
                        <span class="sr-only">Delete</span>
                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                            <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                            <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                        </svg>
                    </button>';

                return '<div class="space-x-1 flex items-center">' . $editBtn . $printBtn . $deleteBtn . '</div>';
            })
            // Filter Global Search (Manual override agar mencari ke relasi)
            ->filterColumn('nomor_servis', function($query, $keyword) {
                 $query->whereHas('service', function($q) use($keyword) {
                    $q->where('nomor_servis', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('pelanggan_nama', function($query, $keyword) {
                 $query->whereHas('pelanggan', function($q) use($keyword) {
                    $q->where('nama', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['checkbox', 'qc_button', 'tindakan_list', 'sparepart_list', 'status_badge', 'aksi'])
            ->make(true);
    }

    public function cetak(Request $request)
    {
        // Ambil info toko
        $users = User::find(1);
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Ambil data history garansi berdasarkan periode
        $data = HistoryGaransi::where('cabang_id',getCabangId())->with(['service', 'teknisi', 'penerima','pelanggan'])
            ->whereBetween('date', [$start_date, $end_date])
            ->orderBy('date', 'desc')
            ->get();

        // Hitung ringkasan
        $totalData = $data->count();
        $totalSelesai = $data->where('status', 2)->count();
        $totalProses = $data->where('status', 1)->count();
        $totalBatal = $data->where('status', 3)->count();
        $totalModal = $data->sum('total_biaya');

        // Buat PDF
        // return View('pages.kepalatoko.cetak-laporan-history-garansi', [
        $pdf = Pdf::loadView('pages.kepalatoko.cetak-laporan-history-garansi', [
            'users' => $users,
            'imagePath' => $imagePath,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'data' => $data,
            'totalData' => $totalData,
            'totalSelesai' => $totalSelesai,
            'totalProses' => $totalProses,
            'totalBatal' => $totalBatal,
            'totalModal' => $totalModal,
        ]);

        $filename = 'Laporan Riwayat Garansi ' . $start_date . ' sd ' . $end_date . '.pdf';
        return $pdf->stream($filename);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data yang dipilih']);
        }

        try {
            HistoryGaransi::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data']);
        }
    }

    public function cetakinkjet($id)
    {
        $history = HistoryGaransi::where('id',$id)->with(['pelanggan'])->first();
        $items = ServiceTransaction::with('customer')->findOrFail($history->service_id);
        $users = User::find(1);

        if ($items->cabang_id == 1) {
            $users = User::find(1);
        }else{
            $users = User::where('cabang_id',$history->cabang_id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
        }
        if ($history->status == 1) {
            $terms = Term::find(1);
        }else{
            $terms = Term::find(2);

        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.nota-garansi-cetak-inkjet', [
        // return View('pages.kepalatoko.servis.nota-garansi-cetak-inkjet', [
            'users' => $users,
            'items' => $items,
            'terms' => $terms,
            'imagePath' => $imagePath,
            'history' => $history,
        ]);

        $filename = 'Nota Riwayat Garansi.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'date'          => 'required|date',
            'service_id'    => 'required|exists:service_transactions,id',
            'penerima_id'   => 'required|exists:users,id',
            'keluhan'    => 'required|string',
        ]);

         $qc_data = $request->qc_masuk ?? [];

        // Gabungkan dengan baris Custom (jika ada input manual)
        if ($request->has('custom_item_name')) {
            foreach ($request->custom_item_name as $key => $name) {
                // Hanya proses jika nama item tidak kosong
                if (!empty($name)) {
                    // Ambil value statusnya (OK/Rusak/dll), default '-' jika kosong
                    $val = $request->custom_qc_masuk[$key] ?? '-';

                    // Masukkan ke array utama
                    $qc_data[$name] = $val;
                }
            }
        }

        // Ubah array menjadi JSON agar bisa disimpan di database text/longtext
        $qc_masuk_final = json_encode($qc_data);

        $data = new HistoryGaransi();
        $data->date        = $request->date;
        $data->service_id  = $request->service_id;
        $data->penerima_id = $request->penerima_id;
        $data->id_customer = $request->id_customer;
        $data->estimasi_pengerjaan = $request->estimasi_pengerjaan;
        $data->fungsi_masuk = $qc_masuk_final;
        $data->teknisi_id  = 0;
        $data->keluhan  = $request->keluhan;
        $data->tindakan    = [];
        $data->sparepart   =  [];
        $data->modal_sparepart = 0;
        $data->total_biaya_tindakan = 0;
        $data->total_biaya = 0;
        $data->catatan     = '-';
        $data->status     = 1;
        $data->cabang_id     = getCabangId();
        $data->save();

        toast('Data berhasil disimpan.', 'success');
        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil disimpan');
    }

    function cleanRupiah($value)
    {
        return (int) str_replace('.', '', $value ?? 0);
    }

    public function update(Request $request,$id)
    {
        // dd($request->all());

        $request->merge([
            'total_biaya_tindakan' => str_replace('.', '', $request->total_biaya_tindakan),
            'modal_sparepart' => str_replace('.', '', $request->modal_sparepart),
            'total_biaya' => str_replace('.', '', $request->total_biaya),
        ]);

        $tindakan = $request->tindakan ?? [];

        foreach ($tindakan as $key => $row) {
            if (isset($row['harga'])) {
                $tindakan[$key]['harga'] = $this->cleanRupiah($row['harga']);
            }
        }

        $sparepart = $request->sparepart ?? [];

        foreach ($sparepart as $key => $row) {
            if (isset($row['harga'])) {
                $sparepart[$key]['harga'] = $this->cleanRupiah($row['harga']);
            }

            if (isset($row['qty'])) {
                $sparepart[$key]['qty'] = (int) $row['qty'];
            }
        }



        $qc_masuk_data = $request->qc_masuk ?? [];
        $qc_keluar_data = $request->qc_keluar ?? [];

        if ($request->has('custom_item_name')) {
            foreach ($request->custom_item_name as $key => $name) {
                if (!empty($name)) {
                    $val_in = $request->custom_qc_masuk[$key] ?? '-';
                    $val_out = $request->custom_qc_keluar[$key] ?? '-';

                    $qc_masuk_data[$name] = $val_in;
                    $qc_keluar_data[$name] = $val_out;
                }
            }
        }

        $qc_masuk_final = json_encode($qc_masuk_data);
        $qc_keluar_final = json_encode($qc_keluar_data);


        $data = HistoryGaransi::find($id);
        if ($request->status == 2 || $request->status == 3) {
            $data->tgl_selesai = date('Y-m-d');
        }else{
            $data->tgl_selesai = null;
        }
        $data->id_customer  = $request->id_customer;
        $data->teknisi_id  = $request->teknisi_id;
        $data->tindakan   =  !empty($tindakan) ? json_encode($tindakan) : [];
        $data->sparepart   =  !empty($sparepart) ? json_encode($sparepart) : [];
        $data->modal_sparepart = $request->modal_sparepart ?? 0;
        $data->total_biaya_tindakan = $request->total_biaya_tindakan;
        $data->total_biaya = $request->total_biaya;
        $data->catatan     = $request->catatan;
        $data->status     = $request->status;
        $data->fungsi_masuk = $qc_masuk_data;
        $data->fungsi_keluar = $qc_keluar_data;
        $data->save();

        if ($data->status == 3) {
            $servis = ServiceTransaction::with('user')->find($data->service_id);

            // if (!$servis) {

                if ($servis->tipe == 'Interface') {
                    $bonus = $servis->bonus_interface;
                } else {
                    $bonus = $servis->profit / 100;
                    $bonus *= $servis->persen_teknisi;
                }

                // dd($servis,$bonus);

                $tambahrefund = new Refund();
                $tambahrefund->servis_transaction_id = $servis->id;
                $tambahrefund->nominal = $bonus;
                $tambahrefund->nominal_servis = $servis->biaya ?? $servis->pay;
                $tambahrefund->teknisi_id = $servis->users_id ?? 0;
                $tambahrefund->cabang_id = getCabangId();
                $tambahrefund->save();

                if ($servis->biaya > 0) {
                    Expense::create([
                        'tipe' => 1, // tipe servis
                        'name' => 'Refund #'. $servis->nomor_servis,
                        'price' => $servis->biaya,
                        'users_id' => auth()->user()->id,
                        'cabang_id' => getCabangId()
                    ]);
                }
            // }
        }

        if (!empty($sparepart)) {
            foreach ($sparepart as $row) {

                // Pastikan ada id & qty
                if (empty($row['id']) || empty($row['qty'])) {
                    continue; // skip jika data tidak valid
                }

                // Cek produk
                $spareparts = Product::find($row['id']);
                if (!$spareparts) {
                    continue; // skip kalau produk tidak ada
                }

                // Cek service
                $service = ServiceTransaction::find($request->service_id);
                if (!$service) {
                    continue; // skip kalau service tidak ditemukan
                }

                // Cek customer
                $customer = Customer::find($service->customers_id);
                if (!$customer) {
                    continue; // skip kalau customer tidak ada
                }

                // Update stok
                if ($spareparts->stok >= (int)$row['qty']) {
                    $spareparts->stok -= (int)$row['qty'];
                    $spareparts->save();
                } else {
                    continue; // skip kalau stok tidak cukup
                }

            }
        }

        $servis2 = ServiceTransaction::with('user')->find($data->service_id);

        if ($request->total_biaya > 0) {
            Expense::create([
                'tipe' => 1, // tipe servis
                'name' => 'Klaim Garansi #'. $servis2->nomor_servis,
                'price' => $request->total_biaya,
                'users_id' => auth()->user()->id,
                'cabang_id' => getCabangId()
            ]);
        }
        toast('Data berhasil disimpan.', 'success');

        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil disimpan');
    }

    public function toggleStatus($id)
    {
        $item = HistoryGaransi::findOrFail($id);

        // Logic toggle (misalnya siklus 1 -> 2 -> 3 -> balik ke 1)
        if ($item->status == 1) {
            $item->status = 2; // dari Diproses ke Selesai
        } elseif ($item->status == 2) {
            $item->status = 3; // dari Selesai ke Dibatalkan
        } else {
            $item->status = 1; // dari Dibatalkan ke Diproses
        }

        $item->save();

        return response()->json([
            'success' => true,
            'status' => $item->status,
            'label' => $item->status == 1 ? 'Diproses' : ($item->status == 2 ? 'Selesai' : 'Dibatalkan')
        ]);
    }


    public function destroy($id)
    {
        $history = HistoryGaransi::findOrFail($id);

        // balikin stok sparepart
        if (!empty($history->sparepart)) {
            $spareparts = json_decode($history->sparepart, true);

            foreach ($spareparts as $row) {
                $product = Product::find($row['id']);
                if ($product) {
                    $product->stok += (int)$row['qty']; // balikin stok
                    $product->save();
                }
            }
        }

        // hapus data history garansi
        $history->delete();
        toast('Data berhasil dihapus & stok sparepart dikembalikan.', 'success');

        return redirect()->route('history-garansi.index')->with('success', 'Data berhasil dihapus & stok sparepart dikembalikan');
    }


    public function edit($id)
    {
        $historyGaransi = HistoryGaransi::findOrFail($id);
        $serviceTransactions = ServiceTransaction::where('cabang_id',getCabangId())->orderBy('created_at', 'desc')->get();
        $products = Product::where('cabang_id',getCabangId())->whereHas('subCategory.category', function ($q) {
            $q->where('category_name', 'Sparepart');
        })->where('stok', '>=', 1)->get();

        $customer = Customer::get();

        $serviceActions = ServiceAction::where('cabang_id',getCabangId())->get();
        $users = User::where('id','!=',1)->where('cabang_id',getCabangId())->where('role','!=','Investor')->get();
        return view('pages.kepalatoko.history.edit', compact(
            'users','historyGaransi','serviceTransactions','products','serviceActions','customer'
        ));
    }

    public function getDetailHistory($id)
    {
        try {
            $data = HistoryGaransi::where('service_id', $id)
                ->with(['service', 'teknisi', 'penerima'])
                ->get();
            // return response()->json($data);

            // Tambahkan nama tindakan langsung di sini
            $data->transform(function ($item) {

                if (!empty($item->tindakan)) {
                    $tindakans = json_decode($item->tindakan, true) ?? [];

                    $listTindakan = [];
                    foreach ($tindakans as $t) {

                        $action = \App\Models\ServiceAction::find($t['id']);

                        $listTindakan[] = [
                            'nama' => $action->nama_tindakan ?? $t['id_manual'],
                            'harga' => $t['harga'],
                        ];
                    }

                    $item->tindakan_list = $listTindakan;
                    return $item;
                }else{
                    $item->tindakan_list = [];
                }
                return $item;
            });

            return response()->json($data);

        } catch (\Throwable $th) {
            return response()->json([]);
        }
    }

    public function cetakQcGaransi($id)
    {
        $items = HistoryGaransi::with(['pelanggan','penerima','service'])->findOrFail($id);
        // dd($items);
        // 1. Decode JSON ke Array
        $qcMasuk = $items->fungsi_masuk ? json_decode($items->fungsi_masuk, true) : [];
        $qcKeluar = $items->fungsi_keluar ? json_decode($items->fungsi_keluar, true) : [];

        $qcItems = $qcMasuk != null ? array_keys($qcMasuk) : [];

        if (empty($qcItems) && !empty($qcKeluar)) {
            $qcItems = array_keys($qcKeluar);
        }

        if (empty($qcItems)) {
            $qcItems = [];
        }

        if ($items->cabang_id == 1) {
            $users = User::find(1);
        } else {
            $users = User::where('cabang_id', $items->cabang_id)->where('id', '!=', 1)->where('role', 'Kepala Toko')->orderBy('id', 'asc')->first();
        }

        if (empty($users)) {
            toast('Silahkan bikin akun kepala toko terlebih dahulu...', 'error');
            return redirect()->back();
        }

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->pelanggan->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.notaqc-cetak-garansi', [
        // return View('pages.kepalatoko.servis.notaqc-cetak', [
            'users' => $users,
            'items' => $items,
            'imagePath' => $imagePath,
            'qcMasuk' => $qcMasuk,   // Data Status Masuk (OK/Rusak/Null)
            'qcKeluar' => $qcKeluar, // Data Status Keluar
            'qcItems' => $qcItems    // Daftar Nama Item (Dinamis dari DB)
        ]);

        $filename = 'QC Check ' . $invoiceNumber . ' - ' . $namaPelanggan . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }


}
