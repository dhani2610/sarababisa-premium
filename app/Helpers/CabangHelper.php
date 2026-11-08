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
        static $cachedUserId = null;
        static $cachedCabangId = null;
        static $cachedData = null;

        if (!Auth::check()) {
            return collect();
        }

        $uid = Auth::user()->id;
        $cid = Auth::user()->cabang_id;

        if ($cachedData !== null && $cachedUserId === $uid && $cachedCabangId === $cid) {
            return $cachedData;
        }

        if ($uid == 1) {
            $data = Cabang::orderBy('id', 'asc')->get();
        } else {
            $data = Auth::user()->assigned_cabangs;
            if ($data->isEmpty()) {
                $data = Cabang::orderBy('id', 'asc')->where('id', $cid)->get();
            }
        }

        $cachedUserId = $uid;
        $cachedCabangId = $cid;
        $cachedData = $data;

        return $data;
    }
}

if (!function_exists('getCabangByIdCached')) {
    function getCabangByIdCached($cabangId)
    {
        static $cabangModelCache = [];
        if (empty($cabangId)) {
            return null;
        }
        if (is_object($cabangId)) {
            $cabangId = $cabangId->id ?? null;
            if (empty($cabangId)) {
                return null;
            }
        }
        if (is_array($cabangId)) {
            return null;
        }
        $cabangId = (int) $cabangId;
        if (!array_key_exists($cabangId, $cabangModelCache)) {
            $cabangModelCache[$cabangId] = Cabang::find($cabangId);
        }
        return $cabangModelCache[$cabangId];
    }
}

if (!function_exists('getCabangName')) {
    function getCabangName($cabangId)
    {
        $c = getCabangByIdCached($cabangId);
        return $c ? ($c->nama_cabang ?? '') : '';
    }
}

if (!function_exists('getStoreSettingByCabang')) {
    function getStoreSettingByCabang($key = null)
    {
        static $settingCache = [];
        $cabangId = (int) getCabangId();

        if (!array_key_exists($cabangId, $settingCache)) {
            $settingCache[$cabangId] = StoreSetting::where('cabang_id', $cabangId)->first();
        }

        $setting = $settingCache[$cabangId];

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
        $cbg = getCabangByIdCached(Auth::user()->cabang_id);
        if (!empty($cbg)) {
            $data = $cbg->nama_cabang ?? '-';
        } else {
            $data = '-';
        }
        return $data;
    }
}
if (!function_exists('expiredDateCabang')) {
    function expiredDateCabang()
    {
        $cbg = getCabangByIdCached(getCabangId());
        if (!empty($cbg)) {
            $data = $cbg->expired_date ?? null;
        } else {
            $data = null;
        }
        return $data;
    }
}
if (!function_exists('allowTransaksiCabang')) {
    function allowTransaksiCabang()
    {
        $cbg = getCabangByIdCached(getCabangId());
        if (!empty($cbg)) {
            $data = $cbg->allow_transaksi ?? 1;
        } else {
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
if (!function_exists('getBonusTeknisiFromData')) {
    function getBonusTeknisiFromData($tx, $relasi, $user, $modelMap = null, $levelingMap = null)
    {
        if (!$tx || !$user) {
            return 0;
        }

        $userId = $user->id;
        $tipe = $relasi ? $relasi->tipe : $tx->tipe;
        $bonusInterface = $relasi ? (float) $relasi->bonus_interface : (float) $tx->bonus_interface;
        $profit = $relasi ? (float) $relasi->profit : (float) $tx->profit;
        $persen = $relasi ? (float) $relasi->persen_teknisi : (float) $tx->persen_teknisi;
        if ($persen <= 0) {
            $persen = (float) ($user->persen ?? 0);
        }

        $bagian = $user->bagian_teknisi ?? '';

        // 1. Tipe Interface Leveling (Khusus Teknisi Interface)
        if ($bagian === 'Teknisi Interface' && ($tipe === 'Interface Leveling' || empty($tipe))) {
            if ($bonusInterface > 0) {
                return $bonusInterface;
            }
            // Fallback dynamic lookup leveling
            $model = ($modelMap !== null && is_array($modelMap))
                ? ($modelMap[$tx->model_series_id] ?? null)
                : ModelSerie::find($tx->model_series_id);
            $tipe_os_id = $model ? $model->id_tipe_os : null;
            $jenis_barang_id = $tx->types_id;
            $biaya = (int) ($relasi && $relasi->biaya ? $relasi->biaya : $tx->biaya);

            $leveling = null;
            if ($levelingMap !== null && is_array($levelingMap)) {
                $userLevels = $levelingMap[$userId] ?? [];
                foreach ($userLevels as $lvl) {
                    if (
                        $lvl->id_jenis_barang == $jenis_barang_id &&
                        $lvl->id_tipe_os == $tipe_os_id &&
                        $lvl->start_rate <= $biaya &&
                        $lvl->end_rate >= $biaya
                    ) {
                        $leveling = $lvl;
                        break;
                    }
                }
            } else {
                $leveling = BonusLeveling::where('id_user', $userId)
                    ->where('id_jenis_barang', $jenis_barang_id)
                    ->where('id_tipe_os', $tipe_os_id)
                    ->where('start_rate', '<=', $biaya)
                    ->where('end_rate', '>=', $biaya)
                    ->first();
            }

            if ($leveling && !empty($leveling->nominal_bonus)) {
                return (float) $leveling->nominal_bonus;
            }
            if ($model && !empty($model->nominal_bonus)) {
                return (float) $model->nominal_bonus;
            }
        }

        // 2. Tipe Interface Tetap (Khusus Teknisi Interface)
        if ($bagian === 'Teknisi Interface' && $tipe === 'Interface') {
            if ($bonusInterface > 0) {
                return $bonusInterface;
            }
            $model = ($modelMap !== null && is_array($modelMap))
                ? ($modelMap[$tx->model_series_id] ?? null)
                : ModelSerie::find($tx->model_series_id);
            if ($model && !empty($model->nominal_bonus)) {
                return (float) $model->nominal_bonus;
            }
        }

        // 3. Tipe Interface Persentase
        if ($bagian === 'Teknisi Persentase Interface' || $tipe === 'Interface Persentase') {
            $pInterface = (float) ($user->persen_bonus_interface ?? 0);
            if ($pInterface <= 0) {
                $pInterface = $persen;
            }
            if ($pInterface > 0 && $profit > 0) {
                return ($profit / 100) * $pInterface;
            }
        }

        // 4. Jika ada bonus_interface nominal khusus tersimpan > 0
        if ($bonusInterface > 0) {
            return $bonusInterface;
        }

        // 5. Default / Teknisi Hardware (Persentase dari profit)
        if ($profit > 0 && $persen > 0) {
            return ($profit / 100) * $persen;
        }

        // 6. Fallback jika ada selisih profit dengan profittoko
        if ($relasi && !empty($relasi->profittoko) && $profit > (float) $relasi->profittoko) {
            return $profit - (float) $relasi->profittoko;
        }

        return 0;
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

        return getBonusTeknisiFromData($tx, $relasi, $user);
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
            $cabangId = getCabangId();

            // Cek apakah customer sudah ada di cabang ini berdasarkan nomor HP (atau nama jika tanpa nomor HP)
            $cleanTlp = preg_replace('/[^0-9]/', '', $tlp ?? '');
            if (str_starts_with($cleanTlp, '62')) $cleanTlp = substr($cleanTlp, 2);
            if (str_starts_with($cleanTlp, '0')) $cleanTlp = substr($cleanTlp, 1);

            if (!empty($cleanTlp)) {
                $existing = Customer::where('cabang_id', $cabangId)
                    ->get()
                    ->first(function($c) use ($cleanTlp) {
                        $p = preg_replace('/[^0-9]/', '', $c->nomor_hp ?? '');
                        if (str_starts_with($p, '62')) $p = substr($p, 2);
                        if (str_starts_with($p, '0')) $p = substr($p, 1);
                        return !empty($p) && $p === $cleanTlp;
                    });
                if ($existing) {
                    if (empty($existing->alamat) && !empty($alamat)) {
                        $existing->alamat = $alamat;
                        $existing->save();
                    }
                    return $existing->id;
                }
            } elseif (!empty($data)) {
                $existing = Customer::where('cabang_id', $cabangId)
                    ->where('nama', $data)
                    ->first();
                if ($existing) {
                    return $existing->id;
                }
            }

            $new = new Customer();
            $new->nama = $data;
            $new->kategori = $kategori;
            $new->nomor_hp = $tlp;
            $new->alamat = $alamat;
            $new->cabang_id = $cabangId;

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
            $transactions = ServiceTransaction::where('cabang_id', $cabangId)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->where(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('tgl_ambil')
                            ->whereDate('tgl_ambil', '>=', $start_date)
                            ->whereDate('tgl_ambil', '<=', $end_date);
                    })->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNull('tgl_ambil')
                            ->whereDate('tgl_disetujui', '>=', $start_date)
                            ->whereDate('tgl_disetujui', '<=', $end_date);
                    });
                })
                ->where(function ($q) use ($user) {
                    $q->where('users_id', $user->id)
                      ->orWhereIn('id', function ($sub) use ($user) {
                          $sub->select('service_transactions_id')
                              ->from('teknisi_servis')
                              ->where('users_id', $user->id);
                      });
                })
                ->get();

            if ($transactions->isEmpty()) {
                return 0;
            }

            $txIds = $transactions->pluck('id')->all();
            $relasiMap = TeknisiServis::whereIn('service_transactions_id', $txIds)
                ->where('users_id', $user->id)
                ->get()
                ->keyBy('service_transactions_id');

            $modelIds = $transactions->pluck('model_series_id')->filter()->unique()->values()->all();
            $modelMap = !empty($modelIds)
                ? ModelSerie::whereIn('id', $modelIds)->get()->keyBy('id')->all()
                : [];

            $levelingMap = [
                $user->id => BonusLeveling::where('id_user', $user->id)->get()->all(),
            ];

            $bonus = 0;
            foreach ($transactions as $service) {
                $relasi = $relasiMap->get($service->id);
                $bonus += getBonusTeknisiFromData($service, $relasi, $user, $modelMap, $levelingMap);
            }

        } elseif ($user->role === 'Sales') {
            $totalProfit = \Illuminate\Support\Facades\DB::table('order_details')
                ->join('orders', 'order_details.orders_id', '=', 'orders.id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->where('order_details.users_id', $user->id)
                ->where('orders.is_approve', 'Setuju')
                ->where('orders.cabang_id', $cabangId)
                ->whereDate('orders.tgl_disetujui', '>=', $start_date)
                ->whereDate('orders.tgl_disetujui', '<=', $end_date)
                ->sum('order_details.profit');

            $bonus = ($totalProfit / 100) * ($user->persen ?? 0);

        } else {
            $tipeBonusNota = $user->tipe_bonus_admin ?? 'Persen';
            $persen = $user->persen ?? 0;
            $nominalBonus = $user->nominal_bonus_admin ?? 0;

            $serviceAgg = \Illuminate\Support\Facades\DB::table('service_transactions')
                ->whereNull('deleted_at')
                ->where('admin_id', $user->id)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', $cabangId)
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->where(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('tgl_ambil')
                            ->whereDate('tgl_ambil', '>=', $start_date)
                            ->whereDate('tgl_ambil', '<=', $end_date);
                    })->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNull('tgl_ambil')
                            ->whereDate('tgl_disetujui', '>=', $start_date)
                            ->whereDate('tgl_disetujui', '<=', $end_date);
                    });
                })
                ->selectRaw('COALESCE(SUM(profit), 0) as total_profit, COUNT(*) as total_nota')
                ->first();

            $saleAgg = \Illuminate\Support\Facades\DB::table('order_details')
                ->join('orders', 'order_details.orders_id', '=', 'orders.id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->where('order_details.admin_id', $user->id)
                ->where('orders.is_approve', 'Setuju')
                ->where('orders.cabang_id', $cabangId)
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->where(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('orders.tgl_disetujui')
                            ->whereDate('orders.tgl_disetujui', '>=', $start_date)
                            ->whereDate('orders.tgl_disetujui', '<=', $end_date);
                    })->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNull('orders.tgl_disetujui')
                            ->whereDate('orders.created_at', '>=', $start_date)
                            ->whereDate('orders.created_at', '<=', $end_date);
                    });
                })
                ->selectRaw('COALESCE(SUM(order_details.profit), 0) as total_profit, COUNT(*) as total_nota')
                ->first();

            $totalProfitService = (float) ($serviceAgg->total_profit ?? 0);
            $totalProfitSale = (float) ($saleAgg->total_profit ?? 0);
            $totalNotaService = (int) ($serviceAgg->total_nota ?? 0);
            $totalNotaSale = (int) ($saleAgg->total_nota ?? 0);

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

if (!function_exists('calculateTotalWorkerBonusForCabang')) {
    function calculateTotalWorkerBonusForCabang($workers, $cabangId, $start_date, $end_date)
    {
        if ($workers->isEmpty()) {
            return 0;
        }

        // Resolve user accounts for all workers in bulk
        $workerIds = $workers->pluck('id')->all();
        $workerNames = $workers->pluck('name')->filter()->unique()->values()->all();

        $usersByWorkerId = User::whereIn('workers_id', $workerIds)->get()->keyBy('workers_id');
        $usersByName = !empty($workerNames)
            ? User::with('cabangs')->whereIn('name', $workerNames)->get()->groupBy('name')
            : collect();

        $resolvedUsers = [];
        foreach ($workers as $w) {
            $u = $usersByWorkerId->get($w->id);
            if (!$u && $usersByName->has($w->name)) {
                $candidates = $usersByName->get($w->name);
                $u = $candidates->firstWhere('cabang_id', $w->cabang_id)
                    ?? $candidates->first(function ($cand) use ($w) {
                        return $cand->cabang_id == $w->cabang_id || $cand->cabangs->contains('id', $w->cabang_id);
                    })
                    ?? $candidates->first();
            }
            if ($u) {
                $resolvedUsers[] = $u;
            }
        }

        if (empty($resolvedUsers)) {
            return 0;
        }

        $teknisiUsers = [];
        $salesUsers = [];
        $adminUsers = [];
        foreach ($resolvedUsers as $u) {
            if ($u->role === 'Teknisi') {
                $teknisiUsers[$u->id] = $u;
            } elseif ($u->role === 'Sales') {
                $salesUsers[$u->id] = $u;
            } else {
                $adminUsers[$u->id] = $u;
            }
        }

        $totalBonus = 0;

        // 1. Bulk Teknisi Bonus
        if (!empty($teknisiUsers)) {
            $tekIds = array_keys($teknisiUsers);
            $txs = ServiceTransaction::where('cabang_id', $cabangId)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->where(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('tgl_ambil')
                            ->whereDate('tgl_ambil', '>=', $start_date)
                            ->whereDate('tgl_ambil', '<=', $end_date);
                    })->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNull('tgl_ambil')
                            ->whereDate('tgl_disetujui', '>=', $start_date)
                            ->whereDate('tgl_disetujui', '<=', $end_date);
                    });
                })
                ->where(function ($q) use ($tekIds) {
                    $q->whereIn('users_id', $tekIds)
                      ->orWhereIn('id', function ($sub) use ($tekIds) {
                          $sub->select('service_transactions_id')
                              ->from('teknisi_servis')
                              ->whereIn('users_id', $tekIds);
                      });
                })
                ->get();

            if ($txs->isNotEmpty()) {
                $txIds = $txs->pluck('id')->all();
                $relasis = TeknisiServis::whereIn('service_transactions_id', $txIds)
                    ->whereIn('users_id', $tekIds)
                    ->get();

                $relasiByUserAndTx = [];
                foreach ($relasis as $r) {
                    if (!isset($relasiByUserAndTx[$r->users_id][$r->service_transactions_id])) {
                        $relasiByUserAndTx[$r->users_id][$r->service_transactions_id] = $r;
                    }
                }

                $modelIds = $txs->pluck('model_series_id')->filter()->unique()->values()->all();
                $modelMap = !empty($modelIds)
                    ? ModelSerie::whereIn('id', $modelIds)->get()->keyBy('id')->all()
                    : [];

                $levelings = BonusLeveling::whereIn('id_user', $tekIds)->get();
                $levelingMap = [];
                foreach ($levelings as $lvl) {
                    $levelingMap[$lvl->id_user][] = $lvl;
                }

                foreach ($resolvedUsers as $u) {
                    if ($u->role !== 'Teknisi') {
                        continue;
                    }
                    $uid = $u->id;
                    foreach ($txs as $tx) {
                        $relasi = $relasiByUserAndTx[$uid][$tx->id] ?? null;
                        if ($tx->users_id == $uid || $relasi !== null) {
                            $totalBonus += getBonusTeknisiFromData($tx, $relasi, $u, $modelMap, $levelingMap);
                        }
                    }
                }
            }
        }

        // 2. Bulk Sales Bonus
        if (!empty($salesUsers)) {
            $salesIds = array_keys($salesUsers);
            $salesProfits = \Illuminate\Support\Facades\DB::table('order_details')
                ->join('orders', 'order_details.orders_id', '=', 'orders.id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->whereIn('order_details.users_id', $salesIds)
                ->where('orders.is_approve', 'Setuju')
                ->where('orders.cabang_id', $cabangId)
                ->whereDate('orders.tgl_disetujui', '>=', $start_date)
                ->whereDate('orders.tgl_disetujui', '<=', $end_date)
                ->groupBy('order_details.users_id')
                ->selectRaw('order_details.users_id, COALESCE(SUM(order_details.profit), 0) as total_profit')
                ->pluck('total_profit', 'users_id');

            foreach ($resolvedUsers as $u) {
                if ($u->role !== 'Sales') {
                    continue;
                }
                $p = (float) ($salesProfits[$u->id] ?? 0);
                $totalBonus += ($p / 100) * ($u->persen ?? 0);
            }
        }

        // 3. Bulk Admin Bonus
        if (!empty($adminUsers)) {
            $adminIds = array_keys($adminUsers);

            $adminServiceAgg = \Illuminate\Support\Facades\DB::table('service_transactions')
                ->whereNull('deleted_at')
                ->whereIn('admin_id', $adminIds)
                ->where('status_servis', 'Sudah Diambil')
                ->where('is_approve', 'Setuju')
                ->where('cabang_id', $cabangId)
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->where(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('tgl_ambil')
                            ->whereDate('tgl_ambil', '>=', $start_date)
                            ->whereDate('tgl_ambil', '<=', $end_date);
                    })->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNull('tgl_ambil')
                            ->whereDate('tgl_disetujui', '>=', $start_date)
                            ->whereDate('tgl_disetujui', '<=', $end_date);
                    });
                })
                ->groupBy('admin_id')
                ->selectRaw('admin_id, COALESCE(SUM(profit), 0) as total_profit, COUNT(*) as total_nota')
                ->get()
                ->keyBy('admin_id');

            $adminSaleAgg = \Illuminate\Support\Facades\DB::table('order_details')
                ->join('orders', 'order_details.orders_id', '=', 'orders.id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->whereIn('order_details.admin_id', $adminIds)
                ->where('orders.is_approve', 'Setuju')
                ->where('orders.cabang_id', $cabangId)
                ->where(function ($q) use ($start_date, $end_date) {
                    $q->where(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNotNull('orders.tgl_disetujui')
                            ->whereDate('orders.tgl_disetujui', '>=', $start_date)
                            ->whereDate('orders.tgl_disetujui', '<=', $end_date);
                    })->orWhere(function ($sub) use ($start_date, $end_date) {
                        $sub->whereNull('orders.tgl_disetujui')
                            ->whereDate('orders.created_at', '>=', $start_date)
                            ->whereDate('orders.created_at', '<=', $end_date);
                    });
                })
                ->groupBy('order_details.admin_id')
                ->selectRaw('order_details.admin_id as admin_id, COALESCE(SUM(order_details.profit), 0) as total_profit, COUNT(*) as total_nota')
                ->get()
                ->keyBy('admin_id');

            foreach ($resolvedUsers as $u) {
                if ($u->role === 'Teknisi' || $u->role === 'Sales') {
                    continue;
                }
                $tipeBonusNota = $u->tipe_bonus_admin ?? 'Persen';
                $persen = $u->persen ?? 0;
                $nominalBonus = $u->nominal_bonus_admin ?? 0;

                $srv = $adminServiceAgg->get($u->id);
                $sl = $adminSaleAgg->get($u->id);

                $totalProfitService = (float) ($srv->total_profit ?? 0);
                $totalProfitSale = (float) ($sl->total_profit ?? 0);
                $totalNotaService = (int) ($srv->total_nota ?? 0);
                $totalNotaSale = (int) ($sl->total_nota ?? 0);

                if ($tipeBonusNota === 'Persen') {
                    $totalBonus += (($totalProfitService + $totalProfitSale) / 100) * $persen;
                } elseif ($tipeBonusNota === 'Tetap') {
                    $totalNota = $totalNotaService + $totalNotaSale;
                    $totalBonus += $totalNota * $nominalBonus;
                }
            }
        }

        return $totalBonus;
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

