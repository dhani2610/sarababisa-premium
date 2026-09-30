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
use App\Models\BonusLeveling;
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
        if (is_object($cabangId)) {
            $cabangId = $cabangId->id ?? null;
            if (empty($cabangId)) return '';
        }
        if (is_array($cabangId)) return '';
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
        if (!Auth::check() || !isset(Auth::user()->cabang_id)) {
            return '-';
        }
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

        $relasi = TeknisiServis::where('service_transactions_id', $transactionId)
            ->where('users_id', $userId)
            ->first();

        $tx = ServiceTransaction::find($transactionId);
        if (!$tx) return 0;

        $tipe = $relasi ? $relasi->tipe : $tx->tipe;
        $bonusInterface = $relasi ? (float)$relasi->bonus_interface : (float)$tx->bonus_interface;
        $profit = $relasi ? (float)$relasi->profit : (float)$tx->profit;
        $persen = $relasi ? (float)$relasi->persen_teknisi : (float)$tx->persen_teknisi;
        if ($persen <= 0) $persen = (float)($user->persen ?? 0);

        // 1. Tipe Interface Leveling
        if ($tipe === 'Interface Leveling' || ($user->bagian_teknisi === 'Teknisi Interface' && in_array($tipe, ['Interface Leveling', null, '']))) {
            if ($bonusInterface > 0) {
                return $bonusInterface;
            }
            // Fallback dynamic lookup leveling
            $model = ModelSerie::find($tx->model_series_id);
            $tipe_os_id = $model ? $model->id_tipe_os : null;
            $jenis_barang_id = $tx->types_id;
            $biaya = (int)($relasi && $relasi->biaya ? $relasi->biaya : $tx->biaya);

            $leveling = BonusLeveling::where('id_user', $userId)
                ->where('id_jenis_barang', $jenis_barang_id)
                ->where('id_tipe_os', $tipe_os_id)
                ->where('start_rate', '<=', $biaya)
                ->where('end_rate', '>=', $biaya)
                ->first();

            if ($leveling && !empty($leveling->nominal_bonus)) {
                return (float)$leveling->nominal_bonus;
            }
            return (float)($model ? ($model->nominal_bonus ?? 0) : 0);
        }

        // 2. Tipe Interface (Flat)
        if ($tipe === 'Interface') {
            if ($bonusInterface > 0) return $bonusInterface;
            $model = ModelSerie::find($tx->model_series_id);
            return (float)($model ? ($model->nominal_bonus ?? 0) : 0);
        }

        // 3. Tipe Interface Persentase
        if ($tipe === 'Interface Persentase' || $user->bagian_teknisi === 'Teknisi Persentase Interface') {
            $pInterface = (float)($user->persen_bonus_interface ?? 0);
            return ($profit / 100) * $pInterface;
        }

        // 4. Default: Hardware (Persentase dari profit)
        if ($profit > 0 && $persen > 0) {
            return ($profit / 100) * $persen;
        }

        if ($relasi && !empty($relasi->profittoko) && $profit > (float)$relasi->profittoko) {
            return $profit - (float)$relasi->profittoko;
        }

        return 0;
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
                ->where(function($q) use ($start_date, $end_date) {
                    $q->where(function($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('tgl_ambil')
                            ->whereDate('tgl_ambil', '>=', $start_date)
                            ->whereDate('tgl_ambil', '<=', $end_date);
                    })->orWhere(function($sub) use ($start_date, $end_date) {
                        $sub->whereNull('tgl_ambil')
                            ->whereDate('tgl_disetujui', '>=', $start_date)
                            ->whereDate('tgl_disetujui', '<=', $end_date);
                    });
                })
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

