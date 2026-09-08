<?php

use Illuminate\Support\Facades\Auth;
use App\Models\StoreSetting;
use App\Models\Cabang;
use App\Models\Type;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\ModelSerie;
use App\Models\MetodePembayaran;
use App\Models\User;
use App\Models\TeknisiServis;
use App\Models\OrderDetail;
use App\Models\ServiceTransaction;
use Carbon\Carbon;
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
        $data = collect();
        if (Auth::check()) {
            if (Auth::user()->id == 1) {
                $data = Cabang::orderBy('id','asc')->get();
            }else{
                $data = Auth::user()->assigned_cabangs;
                if ($data->isEmpty()) {
                    $data = Cabang::orderBy('id','asc')->where('id', Auth::user()->cabang_id)->get();
                }
            }
        }

        // Default fallback ke 1
        return $data;
    }
}

if (!function_exists('getCabangName')) {
    function getCabangName($cabangId)
    {
        static $cabangCache = [];
        if (empty($cabangId)) return '';
        if (!isset($cabangCache[$cabangId])) {
            $c = Cabang::find($cabangId);
            $cabangCache[$cabangId] = $c ? $c->nama_cabang : '';
        }
        return $cabangCache[$cabangId];
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

if (!function_exists('getCabangNameUser')) {
    function getCabangNameUser()
    {
        $cbg = Cabang::find(Auth::user()->cabang_id);
        if (!empty($cbg)) {
            $data = $cbg->nama_cabang ?? '-';
        }else{
            $data = '-';
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
if (!function_exists('getBonusTeknisiByTransaction')) {
    function getBonusTeknisiByTransaction($transactionId = null, $userId = null)
    {
        if (empty($transactionId)) return 0;
        if (empty($userId)) {
            $userId = Auth::id();
        }
        $user = User::find($userId);
        if (!$user) return 0;

        // 1. Cek dari relasi TeknisiServis terlebih dahulu (multi-teknisi / detail tindakan servis)
        $relasi = TeknisiServis::where('service_transactions_id', $transactionId)
            ->where('users_id', $userId)
            ->first();

        if ($relasi) {
            // Jika ada bonus_interface nominal tetap yang diset dan > 0
            if (!empty($relasi->bonus_interface) && (float)$relasi->bonus_interface > 0) {
                return (float)$relasi->bonus_interface;
            }

            // Hitung berdasarkan persentase
            $persen = !empty($relasi->persen_teknisi) ? (float)$relasi->persen_teknisi : (!empty($user->persen) ? (float)$user->persen : 0);
            $profit = (float)($relasi->profit ?? 0);

            if ($profit > 0 && $persen > 0) {
                return ($profit / 100) * $persen;
            }

            // Fallback jika ada selisih profit dengan profittoko
            if (!empty($relasi->profittoko) && $profit > (float)$relasi->profittoko) {
                return $profit - (float)$relasi->profittoko;
            }

            return 0;
        }

        // 2. Fallback jika tidak ada record di TeknisiServis (transaksi single langsung di ServiceTransaction)
        $tx = ServiceTransaction::find($transactionId);
        if (!$tx) return 0;

        if (!empty($tx->bonus_interface) && (float)$tx->bonus_interface > 0) {
            return (float)$tx->bonus_interface;
        }

        $persen = !empty($tx->persen_teknisi) ? (float)$tx->persen_teknisi : (!empty($user->persen) ? (float)$user->persen : 0);
        $profit = (float)($tx->profit ?? 0);
        return ($profit / 100) * $persen;
    }
}

if (!function_exists('bonusTeknisiMultiInterfaceByTransactionId')) {
    function bonusTeknisiMultiInterfaceByTransactionId($transactionId = null, $id_user = null)
    {
        return getBonusTeknisiByTransaction($transactionId, $id_user);
    }
}

if (!function_exists('bonusTeknisiMultiHardwareByTransactionId')) {
    function bonusTeknisiMultiHardwareByTransactionId($transactionId = null, $id_user = null)
    {
        return getBonusTeknisiByTransaction($transactionId, $id_user);
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
if (!function_exists('calculateBonusForCabang')) {
    function calculateBonusForCabang($user, $cabangId, $start_date, $end_date)
    {
        if (is_numeric($user)) {
            $user = User::find($user);
        }
        if (!$user) return 0;

        $bonus = 0;

        if ($user->role === 'Teknisi') {
            // 1. Ambil transaksi menggunakan helper multi-teknisi
            $transactions = ServiceTransaction::whereIn('id', servisIdMultiTeknisi($user->id))
                ->whereDate('tgl_disetujui', '>=', $start_date)
                ->whereDate('tgl_disetujui', '<=', $end_date)
                ->where('cabang_id', $cabangId)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->get();

            // 2. Hitung bonus per transaksi menggunakan fungsi seragam getBonusTeknisiByTransaction
            $bonus = 0;
            foreach ($transactions as $service) {
                $bonus += getBonusTeknisiByTransaction($service->id, $user->id);
            }

        } elseif ($user->role === 'Sales') {
            $data = OrderDetail::where('users_id', $user->id)
            ->whereHas('order', function ($query) use ($start_date, $end_date, $cabangId) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', $cabangId)
                    ->whereDate('tgl_disetujui', '>=', $start_date)
                    ->whereDate('tgl_disetujui', '<=', $end_date);
            })->get();

            $bonus = $data->sum('profit') / 100;
            $bonus *= ($user->persen ?? 0);

        } else {
            $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen';
            $persen = $user->persen ?? 0;
            $nominalBonus = $user->nominal_bonus_admin ?? 0;

            $service = ServiceTransaction::where('admin_id', $user->id)
            ->where('status_servis', 'Sudah Diambil')
            ->where('is_approve', 'Setuju')
            ->where('cabang_id', $cabangId)
            ->whereDate('tgl_disetujui', '>=', $start_date)
            ->whereDate('tgl_disetujui', '<=', $end_date)
            ->get();

            $sale = OrderDetail::where('admin_id', $user->id)
            ->whereHas('order', function ($query) use ($start_date, $end_date, $cabangId) {
                $query->where('is_approve', 'Setuju')
                    ->where('cabang_id', $cabangId)
                    ->whereDate('tgl_disetujui', '>=', $start_date)
                    ->whereDate('tgl_disetujui', '<=', $end_date);
            })->get();

            $totalProfitService = $service->sum('profit');
            $totalProfitSale = $sale->sum('profit');
            $totalNotaService = $service->count();
            $totalNotaSale = $sale->count();

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

if (!function_exists('calculateBonus')) {
    function calculateBonus($id, $start_date = null, $end_date = null)
    {
        $user = User::find($id);
        if (!$user) return 0;

        if (empty($start_date) && empty($end_date)) {
            if (request('filter_month')) {
                $date = \Carbon\Carbon::parse(request('filter_month'));
            } else {
                $date = \Carbon\Carbon::now();
            }
            $start_date = $date->copy()->startOfMonth()->toDateString();
            $end_date = $date->copy()->endOfMonth()->toDateString();
        }

        $cabangIds = $user->assigned_cabang_ids;
        $totalBonus = 0;

        foreach ($cabangIds as $cabangId) {
            $totalBonus += calculateBonusForCabang($user, $cabangId, $start_date, $end_date);
        }

        return $totalBonus;
    }
}

if (!function_exists('getBonusBreakdownByCabang')) {
    function getBonusBreakdownByCabang($user, $start_date = null, $end_date = null)
    {
        if (is_numeric($user)) {
            $user = User::find($user);
        }
        if (!$user) return [];

        if (empty($start_date) && empty($end_date)) {
            if (request('filter_month')) {
                $date = \Carbon\Carbon::parse(request('filter_month'));
            } else {
                $date = \Carbon\Carbon::now();
            }
            $start_date = $date->copy()->startOfMonth()->toDateString();
            $end_date = $date->copy()->endOfMonth()->toDateString();
        }

        $breakdown = [];
        $cabangs = $user->assigned_cabangs;

        foreach ($cabangs as $cabang) {
            $b = calculateBonusForCabang($user, $cabang->id, $start_date, $end_date);
            $breakdown[] = [
                'cabang_id' => $cabang->id,
                'cabang_name' => $cabang->nama_cabang,
                'bonus' => $b,
            ];
        }

        return $breakdown;
    }
}

    if (!function_exists('getMetodePembayaran')) {
        function getMetodePembayaran(){
            try {
                $data = MetodePembayaran::where('cabang_id',getCabangId())->get();
                return $data;
            } catch (\Throwable $th) {
                return 0;
            }
        }
    }

