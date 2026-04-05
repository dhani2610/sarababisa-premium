<?php

use Illuminate\Support\Facades\Auth;
use App\Models\StoreSetting;
use App\Models\Cabang;
use App\Models\Type;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\ModelSerie;
use App\Models\TeknisiServis;
use App\Models\ServiceTransaction;
use Carbon\Carbon;
use App\Models\User;

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
            ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
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
            ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])

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
        $user = User::find($userId);
        $teknisiServisHardware = TeknisiServis::where('service_transactions_id', $transactionId)->where('users_id', $userId)
            ->where('tipe', 'Hardware')
            ->get();

        $total_profit_hardware_main = ServiceTransaction::where('id', $transactionId)
            ->where('tipe', 'Hardware')
            ->sum('profit');
        $bonus_hardware_main = ($total_profit_hardware_main / 100) * $user->persen;

        $total_bonus_hardware_detail = TeknisiServis::where('service_transactions_id', $transactionId)
                ->where('tipe', 'Hardware')
                ->get()
                ->sum(function ($item) {
                    return $item->profit * ($item->persen_teknisi / 100);
                });
        // $bonus = $bonus_hardware_main + $total_bonus_hardware_detail;
        $bonus = $total_bonus_hardware_detail;
        return $bonus;
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
if (!function_exists('insertManualPelanggan')) {
    function insertManualPelanggan($data,$tlp,$kategori,$alamat){
        try {
            $new = new Customer();
            $new->nama = $data;
            $new->kategori = $kategori;
            $new->nomor_hp = $tlp;
            $new->alamat = $alamat;
            $new->cabang_id = getCabangId();

            if ($new->save()) {
                return $new->id;
            }

            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
if (!function_exists('insertManualKategori')) {
    function insertManualKategori($data){
        try {
            $new = new Type();
            $new->name = $data;
            $new->cabang_id = getCabangId();

            if ($new->save()) {
                return $new->id;
            }

            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
if (!function_exists('insertManualBrand')) {
    function insertManualBrand($data){
        try {
            $new = new Brand();
            $new->name = $data;
            $new->cabang_id = getCabangId();

            if ($new->save()) {
                return $new->id;
            }

            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
if (!function_exists('insertManualModelSerie')) {
    function insertManualModelSerie($data,$brand_id){
        try {
            $new = new ModelSerie();
            $new->name = $data;
            $new->brands_id = $brand_id ?? 0;
            $new->id_tipe_os = null;
            $new->nominal_bonus = 0;
            $new->cabang_id = getCabangId();

            if ($new->save()) {
                return $new->id;
            }

            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}
if (!function_exists('calculateBonus')) {
    function calculateBonus($id)
    {
        $start_date = Carbon::now()->startOfMonth()->toDateString();
        $end_date = Carbon::now()->endOfMonth()->toDateString();
        $user = User::find($id);
        $bonus = 0;

        if ($user->role === 'Teknisi') {
            $total_bonus_interface_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('bonus_interface');

            $total_profit_hardware_main = ServiceTransaction::where('users_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', getCabangId())
                ->where('tipe', 'Hardware')
                ->whereDate('tgl_ambil', '>=', $start_date)
                ->whereDate('tgl_ambil', '<=', $end_date)
                ->sum('profit');

            $bonus_hardware_main = ($total_profit_hardware_main / 100) * $user->persen;

            $total_bonus_interface_detail = TeknisiServis::where('users_id', $user->id)
                ->whereIn('tipe', ['Interface','Interface Leveling','Interface Persentase'])
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->sum('bonus_interface');

            $total_bonus_hardware_detail = TeknisiServis::where('users_id', $user->id)
                ->where('tipe', 'Hardware')
                ->whereHas('transaction', function ($query) use ($start_date, $end_date) {
                    $query->where('is_approve', 'Setuju')
                        ->where('status_servis', 'Sudah Diambil')
                        ->whereDate('tgl_ambil', '>=', $start_date)
                        ->whereDate('tgl_ambil', '<=', $end_date);
                })
                ->get()
                ->sum(function ($item) {
                    return $item->profit * ($item->persen_teknisi / 100);
                });

            // $bonus = $total_bonus_interface_main + $bonus_hardware_main + $total_bonus_interface_detail + $total_bonus_hardware_detail;
            $bonus = $total_bonus_interface_detail + $total_bonus_hardware_detail;

        } elseif ($user->role === 'Sales') {
            $bonus = $user->sale->sum('profit') / 100;
            $bonus *= $user->persen;

        } else {
            $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen'; // default biar aman
            $persen = $user->persen ?? 0;
            $nominalBonus = $user->nominal_bonus_admin ?? 0;

            $totalProfitService = $user->adminservice->sum('profit');
            $totalProfitSale = $user->adminsale->sum('profit');
            $totalNotaService = $user->adminservice->count();
            $totalNotaSale = $user->adminsale->count();

            if ($tipeBonusNota === 'Persen') {
                $bonus = (($totalProfitService + $totalProfitSale) / 100) * $persen;
            } elseif ($tipeBonusNota === 'Tetap') {
                $totalNota = $totalNotaService + $totalNotaSale;
                $bonus = $totalNota * $nominalBonus;
            } else {
                $bonus = 0;
            }
        }

        return $bonus;
    }
}

