<?php

use Illuminate\Support\Facades\Auth;
use App\Models\StoreSetting;
use App\Models\Cabang;
use App\Models\TeknisiServis;
use App\Models\ServiceTransaction;

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
        if (Auth::check()) {
            if (Auth::user()->id == 1) {
                $data = Cabang::orderBy('id','asc')->get();
            }else{
                $data = Cabang::orderBy('id','asc')->where('id',Auth::user()->cabang_id)->get();
            }
        }

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
            $data = $cbg->nama_cabang ?? null;
        }else{
            $data = null;
        }
        return $data;
    }
}
if (!function_exists('expiredDateCabang')) {
    function expiredDateCabang()
    {
        $cbg = Cabang::find(getCabangId());
        if (!empty($cbg)) {
            $data = $cbg->expired_date ?? null;
        }else{
            $data = null;
        }
        return $data;
    }
}
if (!function_exists('allowTransaksiCabang')) {
    function allowTransaksiCabang()
    {
        $cbg = Cabang::find(getCabangId());
        if (!empty($cbg)) {
            $data = $cbg->allow_transaksi ?? 1;
        }else{
            $data = 1;
        }
        return $data;
    }
}
if (!function_exists('servisIdMultiTeknisi')) {
    function servisIdMultiTeknisi($id = null)
    {
        if ($id == null) {
            $userId = Auth::user()->id;
        }else{
            $userId = $id;
        }

        $idsFromDetail = TeknisiServis::where('users_id', $userId)
            ->pluck('service_transactions_id');

        $idsFromParent = ServiceTransaction::where('users_id', $userId)
            ->pluck('id');

        $mergedIds = $idsFromDetail->merge($idsFromParent)
            ->unique()
            ->values()
            ->toArray();

        return $mergedIds;
    }
}
if (!function_exists('bonusTeknisiMultiInterface')) {
    function bonusTeknisiMultiInterface()
    {
        $userId = Auth::user()->id;
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $teknisiServisInterface = TeknisiServis::where('users_id', Auth::user()->id)
            ->where('tipe', 'Interface')
            ->whereHas('transaction', function ($query) use ($currentYear, $currentMonth) {
                $query->where('is_approve', 'Setuju') // Pastikan status sudah disetujui
                    ->whereYear('tgl_disetujui', $currentYear)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
            ->sum('bonus_interface');

        return $teknisiServisInterface;
    }
}
if (!function_exists('bonusTeknisiMultiHardware')) {
    function bonusTeknisiMultiHardware()
    {
        $userId = Auth::user()->id;
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $teknisiServisHardware = TeknisiServis::where('users_id', Auth::user()->id)
            ->where('tipe', 'Hardware')
            ->whereHas('transaction', function ($query) use ($currentYear, $currentMonth) {
                $query->where('is_approve', 'Setuju') // Pastikan status sudah disetujui
                    ->whereYear('tgl_disetujui', $currentYear)
                    ->whereMonth('tgl_disetujui', $currentMonth);
            })
             ->sum(function ($item) {
                return $item->profit * ($item->persen_teknisi / 100);
            });

        return $teknisiServisHardware;
    }
}
if (!function_exists('bonusTeknisiMultiInterfaceByTransactionId')) {
    function bonusTeknisiMultiInterfaceByTransactionId($transactionId = null,$id_user = null)
    {
        if ($id_user == null) {
            $userId = Auth::user()->id;
        }else{
            $userId = $id_user;
        }
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $teknisiServisInterface = TeknisiServis::where('service_transactions_id', $transactionId)->where('users_id', $userId)
            ->where('tipe', 'Interface')

            ->sum('bonus_interface');

        return $teknisiServisInterface;
    }
}
if (!function_exists('bonusTeknisiMultiHardwareByTransactionId')) {
    function bonusTeknisiMultiHardwareByTransactionId($transactionId = null,$id_user = null)
    {
        if ($id_user == null) {
            $userId = Auth::user()->id;
        }else{
            $userId = $id_user;
        }
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $teknisiServisHardware = TeknisiServis::where('service_transactions_id', $transactionId)->where('users_id', $userId)
            ->where('tipe', 'Hardware')
            ->get()
            ->sum(function ($item) {
                return $item->profit * ($item->persen_teknisi / 100);
            });

        return $teknisiServisHardware;
    }
}
if (!function_exists('getTypeTeknisiMultiTransaksi')) {
    function getTypeTeknisiMultiTransaksi($transactionId = null,$id_user = null)
    {
        if ($id_user == null) {
            $userId = Auth::user()->id;
        }else{
            $userId = $id_user;
        }
        $data = TeknisiServis::where('service_transactions_id', $transactionId)->where('users_id', $userId)->first();

        return $data;
    }
}

