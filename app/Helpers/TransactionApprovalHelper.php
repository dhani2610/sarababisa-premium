<?php

namespace App\Helpers;

use App\Models\StoreSetting;
use App\Models\TransactionDeleteRequest;
use Illuminate\Support\Facades\Auth;

class TransactionApprovalHelper
{
    public static function requiresApproval()
    {
        $setting = StoreSetting::where('cabang_id', getCabangId())->first();
        $isToggleOn = $setting && (int) ($setting->approval_hapus_transaksi ?? 0) === 1;
        $isNotKepalaToko = Auth::check() && Auth::user()->role !== 'Kepala Toko';

        return $isToggleOn && $isNotKepalaToko;
    }

    public static function createRequest($type, $transaction, $reason = null)
    {
        $nomor = $type === 'servis' ? ($transaction->nomor_servis ?? null) : ($transaction->invoice_no ?? null);
        $keterangan = '';
        if ($type === 'servis') {
            $pelanggan = $transaction->customer->nama ?? $transaction->nama_pelanggan ?? '-';
            $barang = $transaction->nama_barang ?? '-';
            $keterangan = "Servis: {$barang} | Pelanggan: {$pelanggan}";
        } else {
            $pelanggan = $transaction->customer->nama ?? '-';
            $total = number_format($transaction->total ?? 0, 0, ',', '.');
            $keterangan = "POS Invoice: {$nomor} | Pelanggan: {$pelanggan} | Total: Rp {$total}";
        }

        // Cek apakah sudah pernah request yang pending
        $existing = TransactionDeleteRequest::where('transaksi_type', $type)
            ->where('transaksi_id', $transaction->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        return TransactionDeleteRequest::create([
            'cabang_id' => $transaction->cabang_id ?? getCabangId(),
            'transaksi_type' => $type,
            'transaksi_id' => $transaction->id,
            'transaksi_nomor' => $nomor,
            'keterangan' => $keterangan,
            'alasan' => $reason ?? 'Permintaan hapus dari ' . (Auth::user()->name ?? 'user') . ' (' . (Auth::user()->role ?? '') . ')',
            'requested_by' => Auth::id(),
            'status' => 'pending',
        ]);
    }
}
