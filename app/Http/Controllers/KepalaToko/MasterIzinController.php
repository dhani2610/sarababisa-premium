<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\IzinRequest;
use App\Models\Izin;
use App\Models\User;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class MasterIzinController extends Controller
{
    public function index()
    {
        return view('pages.kepalatoko.master.izin');
    }

    public function store(IzinRequest $request)
    {
        // dd('MASUK KE STORE', $request->all());

        try {
            $data = $request->validated();

            if ($request->hasFile('dokumen')) {
                $file = $request->file('dokumen');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/izin'), $filename);
                $data['dokumen'] = 'uploads/izin/' . $filename;
            }

            // Ambil setting potongan dari store_settings (id = 1)
            $store = StoreSetting::find(1);

            // Jika nominal_potongan tidak diisi manual, ambil dari store_settings sesuai tipe
            if (empty($data['nominal_potongan'])) {
                switch ($data['tipe']) {
                    case 'izin':
                        $data['nominal_potongan'] = $store->nominal_potongan_izin ?? 0;
                        break;
                    case 'sakit':
                        $data['nominal_potongan'] = $store->nominal_potongan_sakit ?? 0;
                        break;
                    case 'alfa':
                        $data['nominal_potongan'] = $store->nominal_potongan_alfa ?? 0;
                        break;
                    default:
                        $data['nominal_potongan'] = 0;
                        break;
                }
            } else {
                // Jika user isi manual, pastikan format angka bersih dari titik
                $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
            }
            // dd($data);

            $izin = Izin::create($data);

            try {
                $user = User::find($izin->user_id);
                $userName = $user ? $user->name : '-';
                $tanggal = \Carbon\Carbon::parse($izin->tanggal)
                    ->locale('id')
                    ->translatedFormat('d F Y');

                $linkDokumen = $izin->dokumen
                    ? '[' . basename($izin->dokumen) . '](' . asset($izin->dokumen) . ')'
                    : '-';

                $pesan = "📢 *IZIN BARU DIBUAT*\n\n"
                    . "👤 *Karyawan:* {$userName}\n"
                    . "📅 *Tanggal:* {$tanggal}\n"
                    . "📝 *Tipe Izin:* " . ucfirst($izin->tipe) . "\n"
                    . "💬 *Keterangan:* " . ($izin->keterangan ?? '-') . "\n"
                    . "📎 *Dokumen:* {$linkDokumen}\n"
                    . "🧍‍♂️ *Dibuat oleh:* " . Auth::user()->name;

                $this->sendMessage($pesan);
            } catch (\Exception $e) {
                \Log::error("Gagal kirim Telegram (izin): " . $e->getMessage());
            }

            toast('Data Izin berhasil disimpan.', 'success');
            return redirect()->route('master-izin.index');
        } catch (\Throwable $e) {

            toast('Data Izin gagal disimpan.', 'error');
            return redirect()->route('master-izin.index');
        }
    }

    public function update(IzinRequest $request, $id)
    {
        $data = $request->validated();
        // dd($data);
        $data['nominal_potongan'] = str_replace('.', '', $data['nominal_potongan']);
        $izin = Izin::findOrFail($id);
        if ($request->hasFile('dokumen')) {
            if ($izin->dokumen && file_exists(public_path($izin->dokumen))) {
                unlink(public_path($izin->dokumen));
            }
            $file = $request->file('dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/izin'), $filename);
            $data['dokumen'] = 'uploads/izin/' . $filename;
        }

        $izin->update($data);

        try {
            $user = User::find($izin->user_id);
            $userName = $user ? $user->name : '-';
            $tanggal = \Carbon\Carbon::parse($izin->tanggal)
                ->locale('id')
                ->translatedFormat('d F Y');

            $linkDokumen = $izin->dokumen
                ? '[' . basename($izin->dokumen) . '](' . asset($izin->dokumen) . ')'
                : '-';

            if ($izin->status  == 1){
                $status = 'DiSetujui';
            }else if ($izin->status == 0){
                $status = 'Pending';
            }else if ($izin->status == 2){
                $status = 'Ditolak';
            }

            $pesan = "📢 *IZIN DI EDIT*\n\n"
                . "👤 *Status:* {$status}\n"
                . "👤 *Karyawan:* {$userName}\n"
                . "📅 *Tanggal:* {$tanggal}\n"
                . "📝 *Tipe Izin:* " . ucfirst($izin->tipe) . "\n"
                . "💬 *Keterangan:* " . ($izin->keterangan ?? '-') . "\n"
                . "📎 *Dokumen:* {$linkDokumen}\n"
                . "🧍‍♂️ *Di Edit oleh:* " . Auth::user()->name;

            $this->sendMessage($pesan);
        } catch (\Exception $e) {
            \Log::error("Gagal kirim Telegram (izin): " . $e->getMessage());
        }


        toast('Data Izin berhasil diperbarui.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function destroy($id)
    {
        $item = Izin::findOrFail($id);
        $item->delete();

        toast('Data Izin berhasil dihapus.', 'success');
        return redirect()->route('master-izin.index');
    }

    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        Izin::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data izin berhasil dihapus.']);
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
}
