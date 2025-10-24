<?php

namespace App\Http\Controllers\KepalaToko;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaToko\RefundRequest;
use App\Models\Refund;
use App\Models\ServiceTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $refunds = Refund::with(['ServiceTransaction','teknisi'])->orderBy('created_at','desc')->paginate($perPage);

        // untuk dropdown servis - hanya contoh, bisa ditambah filter
        $servis = ServiceTransaction::with('user')->orderBy('id','desc')->get();

        return view('pages.kepalatoko.master.refund', [
            'refunds' => $refunds,
            'servis' => $servis,
        ]);
    }
    public function show($id)
    {
        return response()->json(['message' => 'Not implemented'], 404);
    }


    // endpoint untuk bulk delete via AJAX
    public function deleteSelected(Request $request)
    {
        $selectedIds = $request->input('selectedIds', []);
        if (!is_array($selectedIds) || empty($selectedIds)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih.'], 422);
        }

        Refund::whereIn('id', $selectedIds)->delete();

        return response()->json(['message' => 'Data refund berhasil dihapus.']);
    }

    public function store(RefundRequest $request)
    {
        $data = $request->validated();

        // convert period from "YYYY-MM" to YYYY-MM-01 (date)
        if (!empty($data['period'])) {
            $data['period'] = $data['period'] . '-01';
        }

        // Ambil teknisi dari servis transaction (dari field users_id)
        $servis = ServiceTransaction::find($data['servis_transaction_id']);
        if ($servis) {
            // jika field di servis bernama users_id
            $data['teknisi_id'] = $servis->users_id ?? $servis->user_id ?? null;
        }

        Refund::create($data);

        toast('Refund berhasil ditambahkan.', 'success');

        return redirect()->route('refund.index');
    }

    public function edit($id)
    {
        $item = Refund::findOrFail($id);
        $servis = ServiceTransaction::with('user')->orderBy('id','desc')->get();

        return view('pages.kepalatoko.master.refund-edit', [
            'item' => $item,
            'servis' => $servis,
        ]);
    }

    public function update(RefundRequest $request, $id)
    {
        $data = $request->validated();

        if (!empty($data['period'])) {
            $data['period'] = $data['period'] . '-01';
        }

        // set teknisi from servis
        $servis = ServiceTransaction::find($data['servis_transaction_id']);
        if ($servis) {
            $data['teknisi_id'] = $servis->users_id ?? $servis->user_id ?? null;
        }

        $item = Refund::findOrFail($id);
        $item->update($data);

        toast('Refund berhasil diupdate.', 'success');

        return redirect()->route('refund.index');
    }

    public function destroy($id)
    {
        $item = Refund::findOrFail($id);
        $item->delete();

        toast('Refund berhasil dihapus.', 'success');

        return redirect()->route('refund.index');
    }

    // helper route: ambil data servis by id (ajax)
    public function serviceDetail($id)
    {
        $servis = ServiceTransaction::with('user')->find($id);
        if (!$servis) {
            return response()->json(['message' => 'Servis tidak ditemukan'], 404);
        }

        if ($servis->tipe == 'Interface') {
            $bonus = $servis->bonus_interface;
        }else{
            $bonus = $servis->profit/100;
            $bonus *= $servis->persen_teknisi;
        }

        return response()->json([
            'id' => $servis->id,
            'teknisi_id' => $servis->users_id ?? $servis->user_id ?? null,
            'teknisi_name' => optional($servis->user)->name ?? null,
            'nominal' => $bonus,
        ]);
    }
}
