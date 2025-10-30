<?php

namespace App\Http\Controllers\KepalaToko;

use App\Models\Type;
use App\Models\User;
use App\Models\Brand;
use App\Models\Worker;
use App\Models\Capacity;
use App\Models\Customer;
use App\Models\ModelSerie;
use Illuminate\Http\Request;
use App\Models\ServiceAction;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ServiceTransaction;
use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TipeOs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TransaksiServisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $processes = ServiceTransaction::with('customer')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->orderByDesc('updated_at')->paginate(10);
        $processes_count = ServiceTransaction::whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])->count();
        $bisadiambil = ServiceTransaction::with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->paginate(10);
        $jumlahbisadiambil = ServiceTransaction::with('customer', 'serviceaction')->where('status_servis', 'Bisa Diambil')->count();
        $jumlah_bisa_diambil = ServiceTransaction::where('status_servis', 'Bisa Diambil')->count();
        $jumlah_sudah_diambil = ServiceTransaction::where('status_servis', 'Sudah Diambil')->count();
        $jumlah_semua = ServiceTransaction::all()->count();
        return view('pages/kepalatoko/servis/transaksi-servis', compact(
            'processes',
            'processes_count',
            'jumlah_bisa_diambil',
            'jumlah_sudah_diambil',
            'jumlah_semua',
            'bisadiambil',
            'jumlahbisadiambil'
        ));
    }

    public function updatePinPola(Request $request, $id)
    {
        $request->validate([
            'pin' => 'nullable|string|max:10',
            'pola' => 'nullable|string',
        ]);

        $service = ServiceTransaction::findOrFail($id);
        $service->pin = $request->pin;
        $service->pola = $request->pola;
        $service->save();

        return response()->json([
            'success' => true,
            'message' => 'PIN & Pola berhasil disimpan.'
        ]);
    }


    public function deleteSelected(Request $request)
    {
        $selectedIds  = $request->input('selectedIds');
        ServiceTransaction::whereIn('id', $selectedIds)->delete();
        return response()->json(['message' => 'Data transaksi servis berhasil dihapus.']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $nomor_servis = '' . mt_rand(date('Ymd00'), date('Ymd99'));
        $nama_pelanggan = Customer::find($request->customers_id);
        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

        // Transaction create
        $transaksi = ServiceTransaction::create([
            'nomor_servis' => $nomor_servis,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'nama_barang' => $nama_barang,
            'imei' => $request->imei,
            'warna' => $request->warna,
            'capacities_id' => $request->capacities_id,
            'kelengkapan' => $request->kelengkapan,
            'kerusakan' => $request->kerusakan,
            'qc_masuk' => $request->qc_masuk,
            'estimasi_pengerjaan' => $request->estimasi_pengerjaan,
            'estimasi_biaya' => $request->estimasi_biaya,
            'uang_muka' => $request->uang_muka,
            'status_servis' => $request->status_servis,
            'penerima' => $request->penerima
        ]);

        try {
            $tglMasuk = $transaksi->created_at
                ? Carbon::parse($transaksi->created_at)->locale('id')->translatedFormat('d F Y')
                : '-';

            $pesan = "📦 *TRANSAKSI BARU*\n\n"
                . "🧾 *PROSES DITINGGAL*\n"
                . "🧾 *Status Servis:* {$transaksi->status_servis}\n"
                . "🧾 *Nomor Servis:* {$transaksi->nomor_servis}\n"
                . "👤 *Pelanggan:* {$nama_pelanggan->nama}\n"
                . "📱 *Barang:* {$nama_barang}\n"
                // . "⚙️ *Tindakan Servis:*\n{$tindakanText}\n\n"
                . "🧾 *Estimasi Pengerjaan:* {$transaksi->estimasi_pengerjaan}\n"
                . "💰 *Estimasi Biaya:* Rp " . number_format($transaksi->estimasi_biaya, 0, ',', '.') . "\n"
                . "💰 *Uang Muka:* Rp " . number_format($transaksi->uang_muka, 0, ',', '.') . "\n\n"
                . "📅 *Tanggal Masuk:* {$tglMasuk}\n"
                . "🧍‍♂️ *penerima:* " . Auth::user()->name;

            $this->sendMessage($pesan);

        } catch (\Exception $e) {
            \Log::error("Gagal kirim Telegram: " . $e->getMessage());
        }


        return redirect()->route('transaksi-servis.index');
    }
    public function sendMessage($message)
    {
        $storeSetting = \App\Models\StoreSetting::find(1);
        if ($storeSetting && $storeSetting->token_bot && $storeSetting->chat_id) {
            $botToken = $storeSetting->token_bot;
            $chatId   = $storeSetting->chat_id;

            if (!$botToken || !$chatId) {
                \Log::warning('Telegram bot token atau chat_id belum diset di pengaturan toko.');
                return;
            }

            try {
                Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal kirim pesan Telegram: ' . $e->getMessage());
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function cetak(Request $request)
    {
        // Mengambil logo dan nama toko
        $users = User::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Filter tanggal
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // Mengambil data servis
        $services = ServiceTransaction::with('brand', 'modelserie', 'user')->whereNotIn('status_servis', ['Bisa Diambil', 'Sudah Diambil'])
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)
            ->orderBy('created_at', 'asc')
            ->get();

        $pdf = PDF::loadView('pages.kepalatoko.cetak-laporan-proses', [
            'users' => $users,
            'imagePath' => $imagePath,
            'services' => $services,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);

        $filename = 'Laporan Transaksi Servis' . ' ' . $start_date . ' ' . 'sd' . ' ' . $end_date . '.pdf';

        return $pdf->stream($filename);
    }

    public function cetaktermal($id)
    {
        $items = ServiceTransaction::with('customer')->findOrFail($id);
        $users = User::find(1);
        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.notaterima-cetak-termal', [
        // return view('pages.kepalatoko.servis.notaterima-cetak-termal', [
            'users' => $users,
            'items' => $items,
            'imagePath' => $imagePath,
        ]);

        $filename = 'Nota Terima ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    public function cetakinkjet($id)
    {
        $items = ServiceTransaction::with('customer')->findOrFail($id);
        $users = User::find(1);
        $terms = Term::find(1);

        $logo = $users->profile_photo_path;
        $imagePath = public_path('storage/' . $logo);

        // Ambil nomor invoice dari database
        $invoiceNumber = $items->nomor_servis;
        $namaPelanggan = $items->customer->nama;

        $pdf = PDF::loadView('pages.kepalatoko.servis.notaterima-cetak-inkjet', [
        // return view('pages.kepalatoko.servis.notaterima-cetak-inkjet', [
            'users' => $users,
            'items' => $items,
            'terms' => $terms,
            'imagePath' => $imagePath,
        ]);

        $filename = 'Nota Terima ' . $invoiceNumber . ' ' . '(' . $namaPelanggan . ')' . '.pdf';

        return $pdf->setOption('isRemoteEnabled', true)->stream($filename);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        $item = ServiceTransaction::findOrFail($id);
        $customers = Customer::all();
        $types = Type::all();
        $brands = Brand::all();
        $model_series = ModelSerie::all();
        $service_actions = ServiceAction::all();
        $capacities = Capacity::all();
        $users = User::where('role', 'Teknisi')->get();
        $workers = Worker::where('jabatan', 'like', '%' . 'teknisi')->get();

        return view('pages.kepalatoko.servis.transaksi-servis-edit', [
            'item' => $item,
            'types' => $types,
            'customers' => $customers,
            'brands' => $brands,
            'model_series' => $model_series,
            'service_actions' => $service_actions,
            'capacities' => $capacities,
            'users' => $users,
            'workers' => $workers
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = ServiceTransaction::findOrFail($id);
        $nama_pelanggan = Customer::find($request->customers_id);
        $nama_tipe = Type::find($request->types_id);
        $nama_merek = Brand::find($request->brands_id);
        $nama_model = ModelSerie::find($request->model_series_id);
        $nama_barang = '' . $nama_tipe->name . ' ' . $nama_merek->name . ' ' . $nama_model->name;

        // Transaction update
        $item->update([
            'created_at' => $request->created_at,
            'customers_id' => $request->customers_id,
            'nama_pelanggan' => $nama_pelanggan->nama,
            'types_id' => $request->types_id,
            'brands_id' => $request->brands_id,
            'model_series_id' => $request->model_series_id,
            'nama_barang' => $nama_barang,
            'imei' => $request->imei,
            'warna' => $request->warna,
            'capacities_id' => $request->capacities_id,
            'kelengkapan' => $request->kelengkapan,
            'kerusakan' => $request->kerusakan,
            'qc_masuk' => $request->qc_masuk,
            'estimasi_pengerjaan' => $request->estimasi_pengerjaan,
            'estimasi_biaya' => $request->estimasi_biaya,
            'uang_muka' => $request->uang_muka,
            'penerima' => $request->penerima
        ]);

        return redirect()->route('transaksi-servis.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = ServiceTransaction::findOrFail($id);

        $item->delete();

        return redirect()->route('transaksi-servis.index');
    }
}
