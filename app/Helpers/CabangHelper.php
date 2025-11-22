<?php

use Illuminate\Support\Facades\Auth;
use App\Models\StoreSetting;
use App\Models\Cabang;

if (!function_exists('getCabangId')) {
    function getCabangId()
    {
        // Jika ada user login dan punya cabang, pakai itu
        if (Auth::check() && isset(Auth::user()->cabang_id)) {
            return Auth::user()->cabang_id;
        }

        // Default fallback ke 1
        return 1;
    }
}
if (!function_exists('getCabang')) {
    function getCabang()
    {
        $data = Cabang::orderBy('nama_cabang','asc')->get();

        // Default fallback ke 1
        return $data;
    }
}

if (!function_exists('getStoreSettingByCabang')) {
    function getStoreSettingByCabang($key = null)
    {
        $cabangId = getCabangId();

        $setting = StoreSetting::where('cabang_id', $cabangId)->first();

        if (!$setting) {
            return null;
        }

        // Kalau mau ambil single field
        if ($key) {
            return $setting->{$key} ?? null;
        }

        // Balikkan seluruh row
        return $setting;
    }
}

if (!function_exists('getCabangName')) {
    function getCabangName($id)
    {
        $cbg = Cabang::find($id);
        if (!empty($cbg)) {
            $data = $cbg->nama_cabang ?? '-';
        }else{
            $data = '-';
        }
        return $data;
    }
}

if (!function_exists('getCabangNameUser')) {
    function getCabangNameUser()
    {
        $cbg = Cabang::find(getCabangId());
        if (!empty($cbg)) {
            $data = $cbg->nama_cabang ?? '-';
        }else{
            $data = '-';
        }
        return $data;
    }
}

