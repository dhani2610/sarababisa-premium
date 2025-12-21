<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\KepalaToko\UserRequest;
use App\Models\Shift;
use App\Models\Cabang;

class AkunController extends Controller
{
    public function getDataTotalCabang(Request $request)
    {
        try {
            $data = User::select('total_cabang')->first();

            return response()->json(['msg'=>'berhasil','data'=>$data->total_cabang ?? 0]);
        } catch (\Throwable $th) {
            return response()->json(['msg'=>'gagal','error'=> $th->getMessage()]);
        }
    }
    public function updateTotalCabang(Request $request)
    {
        try {
            $allUser = User::get();
            foreach ($allUser as $user) {
                $user->total_cabang = $request->total_cabang;
                $user->save();
            }

            return response()->json(['msg'=>'berhasil','data']);
        } catch (\Throwable $th) {
            return response()->json(['msg'=>'gagal','error'=> $th->getMessage()]);
        }
    }

    public function getCabangWithExpiredDate(Request $request)
    {
        $cabang = Cabang::get();
        return response()->json($cabang);
    }

    public function updateExpiredDateCabang(Request $request)
{
    try {
        $request->validate([
            'id' => 'required',
            // 'expired_date' => 'required|date' // Hapus validasi required agar bisa update parsial
        ]);

        $id = $request->id;
        $cabang = Cabang::find($id);

        if (!$cabang) {
            return response()->json(['status' => 'gagal', 'msg' => 'Cabang tidak ditemukan'], 404);
        }

        // Update Expired Date jika dikirim
        if ($request->has('expired_date')) {
            $cabang->expired_date = $request->expired_date;
        }

        // Update Allow Transaksi jika dikirim
        if ($request->has('allow_transaksi')) {
            // Pastikan nilai jadi integer (1 atau 0)
            $cabang->allow_transaksi = (int) $request->allow_transaksi;
        }

        $cabang->save();

        return response()->json([
            'status' => 'success',
            'msg' => 'Data cabang ' . $cabang->nama_cabang . ' berhasil diperbarui',
            'data' => $cabang
        ]);

    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'gagal',
            'msg' => $th->getMessage(),
        ], 500);
    }
}

}
