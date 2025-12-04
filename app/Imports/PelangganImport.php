<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class PelangganImport implements ToModel, WithHeadingRow, WithBatchInserts
{
    public function model(array $row)
    {
        // Abaikan jika tidak ada nama pelanggan DAN tidak ada nomor HP
        if (empty($row['Nama Pelanggan']) && empty($row['Nomor HP'])) {
            return null;
        }

        $cabangId = getCabangId();
        $hpAsli = $row['Nomor HP'];

        // ---------------------------------------------------------
        // 1. LOGIKA UPDATE (Prioritas Utama - Cek Cabang Sendiri)
        // ---------------------------------------------------------
        // Cari apakah Customer dengan HP ini sudah ada di cabang ini?
        $existingCustomer = Customer::where('cabang_id', $cabangId)
                            ->where('nomor_hp', $hpAsli)
                            ->first();

        if ($existingCustomer) {
            // == KONDISI UPDATE ==
            // Data sudah ada di cabang ini, update detailnya.
            $existingCustomer->update([
                'nama'     => $row['Nama Pelanggan'],
                'kategori' => $row['Kategori Pelanggan'],
                'alamat'   => $row['Alamat'],
                // 'nomor_hp' tidak perlu diupdate karena sudah kunci pencarian
            ]);

            return null; // Stop, jangan lanjut ke create
        }

        // ---------------------------------------------------------
        // 2. LOGIKA CREATE (Jika belum ada di cabang ini)
        // ---------------------------------------------------------

        $finalHp = $hpAsli;
        $counter = 2;

        // Cek apakah HP ini sudah dipakai secara GLOBAL (di cabang lain)?
        // Jika ya, kita rename HP-nya agar tetap bisa masuk (unik).
        // PENTING: Ini akan membuat nomor HP jadi string seperti "0812345 (2)"
        while (Customer::where('nomor_hp', $finalHp)->exists()) {
            $finalHp = $hpAsli . ' (' . $counter . ')';
            $counter++;
        }

        // Simpan Data Baru
        return new Customer([
            'nama'        => $row['Nama Pelanggan'],
            'nomor_hp'    => $finalHp, // Nomor HP yang sudah diamankan (unik)
            'kategori'    => $row['Kategori Pelanggan'],
            'alamat'      => $row['Alamat'],
            'cabang_id'   => $cabangId,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
