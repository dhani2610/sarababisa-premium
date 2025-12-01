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


}
