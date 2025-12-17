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
            // Validasi input
            $request->validate([
                'id' => 'required',
                'expired_date' => 'required|date'
            ]);

            $id = $request->id;
            $expired_date = $request->expired_date;

            $cabang = Cabang::find($id);
            if (!$cabang) {
                return response()->json(['status' => 'gagal', 'msg' => 'Cabang tidak ditemukan'], 404);
            }

            $cabang->expired_date = $expired_date;
            $cabang->save();

            return response()->json([
                'status' => 'success',
                'msg' => 'Berhasil update expired cabang ' . $cabang->nama_cabang,
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
