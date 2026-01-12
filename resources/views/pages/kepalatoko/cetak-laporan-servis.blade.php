<!DOCTYPE html>
<html lang="en">

@php
    use App\Models\Product;
@endphp

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Transaksi Servis</title>
    <style>
        @page {
            margin: 3mm 4mm 10mm 3mm;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .capital {
            text-transform: uppercase;
        }

        #ringkasan td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            padding: 4px 0 4px 0;
            text-align: left;
        }

            /* Cari bagian ini di <style> */
        #detail td,
        #detail th,
        #detail tr,
        #detail table {
            border-collapse: collapse;
            font-size: 10px;
            line-height: 1em;
            table-layout: fixed;
            padding: 4px;
            text-align: center;
            border: solid;
            word-wrap: break-word;

            /* UBAH DARI 'top' MENJADI 'middle' */
            vertical-align: middle;
        }

        #analisis td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 14px;
            line-height: 1em;
            width: 100%;
            padding: 4px 0 4px 0;
            text-align: left;
        }

        #data {
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="text-center">
        @if ($users->profile_photo_path != null)
            <img src="data:image/png;base64,{{ Storage::disk('public')->exists($users->profile_photo_path) ? base64_encode(file_get_contents($imagePath)) : '' }}"
                alt="" height="70">
        @endif
        <h4 style="margin-top: 5px; margin-bottom: 0">{{ $users->nama_toko }}</h4>
        <p style="margin-top: 3px; margin-bottom: 5px;">{{ $users->alamat_toko }}</p>
    </div>

    <hr style="border-top: 1px dashed; margin-bottom: 0;">

    <div class="text-center">
        <h4 style="margin-bottom: 6px; margin-top: 5px;">
            Laporan Transaksi Servis
        </h4>
        <p style="margin-top: 0">Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d
            {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
    </div>

    <h4 style="margin-bottom: 6px; text-decoration: underline;">
        Ringkasan
    </h4>

    <table id="ringkasan">
        <tbody>
            <tr>
                <th>Total Item Servis</th>
                <th>: {{ $total_servis }} Item</th>
                <th>Total Pembayaran Tunai</th>
                <th>: Rp. {{ number_format($total_tunai) }}</th>
                <th>Saldo Akhir</th>
                <th>: Rp. {{ number_format($saldo_akhir) }}</th>
            </tr>
            <tr>
                <th>Total Biaya Servis</th>
                <th>: Rp. {{ number_format($total_biaya) }}</th>
                <th>Total Pembayaran Transfer</th>
                <th>: Rp. {{ number_format($total_transfer) }}</th>

            </tr>
            <tr>
                <th>Total Diskon</th>
                <th>: Rp. {{ number_format($total_diskon) }}</th>
                <th>Total Pembayaran Tempo</th>
                <th>: Rp. {{ number_format($total_kredit) }}</th>
            </tr>
            <tr>
                <th>Total Modal Sparepart</th>
                <th>: Rp. {{ number_format($total_modal) }}</th>
                <th>Total Pengeluaran Toko</th>
                <th>: Rp. {{ number_format($total_pengeluaran_toko) }}</th>

            </tr>
            <tr>
                <th>Total Uang Muka</th>
                <th>: Rp. {{ number_format($total_dp) }}</th>
                <th>Total Pengeluaran Servis</th>
                <th>: Rp. {{ number_format($total_pengeluaran_servis) }}</th>

            </tr>
            <tr>
                <th>Total Insiden</th>
                <th>: Rp. {{ number_format($total_insiden) }}</th>
                 <th>Total Profit</th>
                <th>: Rp. {{ number_format($total_profit) }}</th>


            </tr>
        </tbody>
    </table>

    <h4 style="margin-top: 15px; margin-bottom: 6px; text-decoration: underline;">
        Detail Transaksi
    </h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No.</th>
                <th>No. Servis</th>
                <th>Pelanggan</th>
                <th>Teknisi</th>
                <th>Model Seri</th>
                <th>Tindakan</th>
                <th>Modal Sparepart</th>
                <th>Biaya Servis</th>
                <th>Diskon</th>
                <th>Profit</th>
                <th>Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($services as $item)
                @php
                    // --- PREPARE DATA STRUCTURE (GROUPING) ---
                    // Kita buat array yang terstruktur: Transaksi -> Teknisi -> Actions

                    $grouped_data = [];
                    $total_rows_transaksi = 0; // Untuk Rowspan Utama

                    // Ambil relasi teknisi (sesuaikan dengan nama relasi di model Anda, contoh: teknisi_tambahan)
                    $teknisiCollection = $item->teknisi_tambahan;

                    if ($teknisiCollection && $teknisiCollection->isNotEmpty()) {
                        // KASUS 1: Ada data di tabel teknisi_servis
                        foreach ($teknisiCollection as $tek) {
                            $actions = json_decode($tek->tindakan_servis);
                            if (!is_array($actions) || empty($actions)) {
                                $actions = ['-']; // Fallback jika array kosong
                            }

                            // Decode Biaya & Modal per teknisi
                            $biayas = json_decode(str_replace(['“','”'], '"', $tek->biaya_j), true);
                            $modals = json_decode(str_replace(['“','”'], '"', $tek->modal_j), true);

                            $grouped_data[] = [
                                'nama_teknisi' => $tek->teknisi->name ?? '-',
                                'actions'      => $actions,
                                'biayas'       => is_array($biayas) ? $biayas : [],
                                'modals'       => is_array($modals) ? $modals : [],
                            ];

                            $total_rows_transaksi += count($actions);
                        }
                    } else {
                        // KASUS 2: Tidak ada teknisi spesifik (Data Legacy/Lama/Single)
                        // Ambil dari table service_transactions langsung
                        $actions = json_decode($item->tindakan_servis);
                        if (!is_array($actions)) {
                             // Jika tindakan bukan array, cek kondisi servis
                             $act_str = ($item->kondisi_servis != 'Sudah jadi') ? $item->kondisi_servis : ($item->tindakan_servis ?? '-');
                             $actions = [$act_str];
                        }
                        if (empty($actions)) $actions = ['-'];

                        $biayas = json_decode(str_replace(['“','”'], '"', $item->biaya_j), true);
                        if(!is_array($biayas)) $biayas = [$item->biaya]; // fallback single value

                        $modals = json_decode(str_replace(['“','”'], '"', $item->modal_j), true);
                        if(!is_array($modals)) $modals = [$item->modal_sparepart]; // fallback single value

                        // Tentukan nama teknisi fallback
                        $nama_tek = '-';
                        if ($item->user) {
                            $nama_tek = $item->user->name;
                        } elseif ($item->user()->withTrashed()->first()) {
                            $nama_tek = $item->user()->withTrashed()->first()->name;
                        }

                        $grouped_data[] = [
                            'nama_teknisi' => $nama_tek,
                            'actions'      => $actions,
                            'biayas'       => $biayas,
                            'modals'       => $modals,
                        ];
                        $total_rows_transaksi += count($actions);
                    }
                @endphp

                {{-- START LOOPING TAMPILAN --}}
                @php
                    $isFirstRowTransaction = true;
                @endphp

                @foreach ($grouped_data as $group)
                    @php
                        $tek_rowspan = count($group['actions']);
                        $isFirstRowTeknisi = true;
                    @endphp

                    @foreach ($group['actions'] as $index => $action)
                        <tr>
                            {{-- KOLOM UTAMA (Hanya muncul di baris pertama transaksi) --}}
                            @if ($isFirstRowTransaction)
                                <td style="width: 10px;" rowspan="{{ $total_rows_transaksi }}">{{ $no++ }}</td>
                                <td class="text-center" style="width: 60px;" rowspan="{{ $total_rows_transaksi }}">
                                    {{ $item->nomor_servis }}
                                </td>
                                <td style="text-align: left; width: 70px;" class="capital" rowspan="{{ $total_rows_transaksi }}">
                                    {{ $item->nama_pelanggan }}
                                </td>
                            @endif

                            {{-- KOLOM TEKNISI (Hanya muncul di baris pertama tiap teknisi) --}}
                            @if ($isFirstRowTeknisi)
                                <td style="text-align: center; width: 70px;" rowspan="{{ $tek_rowspan }}">
                                    {{ $group['nama_teknisi'] }}
                                </td>
                            @endif

                            {{-- KOLOM MODEL SERI (Hanya muncul di baris pertama transaksi - sesuai design asli user) --}}
                            {{-- Tapi karena layout teknisi memecah baris, model seri sebaiknya ikut rowspan UTAMA agar rapi --}}
                            @if ($isFirstRowTransaction)
                                <td style="text-align: left; width: 70px;" rowspan="{{ $total_rows_transaksi }}">
                                    {{ $item->modelserie->name ?? '-' }}
                                </td>
                            @endif

                            {{-- KOLOM TINDAKAN (Selalu muncul per baris) --}}
                            <td class="" style="text-align: left; width: 80px;">
                                @if ($item->kondisi_servis != 'Sudah jadi' && $isFirstRowTransaction && count($grouped_data) == 1 && count($group['actions']) == 1)
                                    {{-- Handle jika status bukan sudah jadi --}}
                                    {{ $item->kondisi_servis }}
                                @else
                                    {{ $action }}
                                @endif
                            </td>

                            {{-- PREPARE VALUE HARGA --}}
                            @php
                                $val_modal = $group['modals'][$index] ?? 0;
                                $val_biaya = $group['biayas'][$index] ?? 0;
                            @endphp

                            {{-- KOLOM MODAL & BIAYA (Selalu muncul per baris) --}}
                            <td style="width: 60px; text-align: right;">
                                Rp. {{ number_format($val_modal) }}
                            </td>
                            <td style="width: 60px; text-align: right;">
                                Rp. {{ number_format($val_biaya) }}
                            </td>

                            {{-- KOLOM DISKON (Rowspan Utama - Diskon biasanya per transaksi) --}}
                            @if ($isFirstRowTransaction)
                                <td style="width: 50px; text-align: right;" rowspan="{{ $total_rows_transaksi }}">
                                    Rp. {{ number_format($item->diskon) }}
                                </td>
                            @endif

                            {{-- KOLOM PROFIT (Per Baris) --}}
                            {{-- Note: Rumus asli user dikurangi diskon per item. Hati-hati jika diskon global --}}
                            <td style="width: 60px; text-align: right;">
                                Rp. {{ number_format($val_biaya - $val_modal - ($isFirstRowTransaction ? $item->diskon : 0)) }}
                                {{-- Logic Profit diatas: Diskon hanya mengurangi baris pertama agar tidak double counting pengurangan profit, atau sesuaikan dengan logika bisnis Anda --}}
                            </td>

                            {{-- KOLOM PEMBAYARAN (Rowspan Utama) --}}
                            @if ($isFirstRowTransaction)
                                <td class="" rowspan="{{ $total_rows_transaksi }}" style="text-align: left; width: 80px;">
                                    @php
                                        $metode = [];
                                        if ($item->tunai > 0) {
                                            $metode[] = '<div><strong>Tunai:</strong><br>Rp.' . number_format($item->tunai, 0, ',', '.') . '</div>';
                                        }
                                        if ($item->transfer > 0) {
                                            $metode[] = '<div><strong>Transfer:</strong><br>Rp.' . number_format($item->transfer, 0, ',', '.') . '</div>';
                                        }
                                    @endphp

                                    @if ($item->kondisi_servis == 'Dibatalkan')
                                        @php
                                            $label = ''; $nilai = 0;
                                            if ($item->uang_muka > 0 && $item->modal_sparepart > 0) {
                                                $label = 'Modal - DP:';
                                                $nilai = $item->uang_muka - $item->modal_sparepart;
                                            } elseif ($item->uang_muka > 0 && $item->biaya > 0) {
                                                $label = 'Modal - DP:';
                                                $nilai = $item->uang_muka - $item->biaya;
                                            } elseif ($item->uang_muka > 0) {
                                                $label = 'Uang Muka:';
                                                $nilai = $item->uang_muka;
                                            } elseif ($item->modal_sparepart > 0) {
                                                $label = 'Modal:';
                                                $nilai = $item->modal_sparepart;
                                            }
                                        @endphp
                                        <div>
                                            <strong>{{ $label }}</strong><br>
                                            Rp.-{{ number_format($nilai, 0, ',', '.') }}
                                        </div>
                                    @else
                                        {!! implode('<hr style="margin: 4px 0;">', $metode) !!}
                                    @endif
                                </td>
                            @endif
                        </tr>

                        @php
                            $isFirstRowTransaction = false; // Baris selanjutnya bukan baris pertama transaksi
                            $isFirstRowTeknisi = false;     // Baris selanjutnya bukan baris pertama teknisi ini
                        @endphp
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

    <hr>
    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
        Uang Muka
    </h4>
    <table id="detail">
        <thead>
            <tr>
                <th>No.</th>
                <th>No. Servis</th>
                <th>Pelanggan</th>
                <th>Penerima</th>
                <th>Model Seri</th>
                <th>Kerusakan</th>
                <th>Estimasi Biaya</th>
                <th>Uang Muka</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($servicesDP as $item)
                <tr>
                    <td style="width: 10px;">{{ $loop->iteration }}</td>
                    <td>{{ $item->nomor_servis }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->penerima }}</td>
                    <td>{{ $item->modelserie->name ?? '-' }}</td>
                    <td>{{ $item->kerusakan }}</td>
                    <td>Rp. {{ number_format($item->estimasi_biaya) }}</td>
                    <td>Rp. {{ number_format($item->uang_muka) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
        Pengeluaran Toko
    </h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tgl Pengeluaran</th>
                <th>Nama</th>
                <th>Item Pengeluaran</th>
                <th>Status</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengeluaran_data_toko as $item)
                <tr>
                    <td style="width: 10px;">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td>
                        @if ($item->user)
                            {{ $item->user->name }}
                        @else
                            Akun sudah dihapus
                        @endif
                    </td>
                    <td>{{ $item->name }}</td>
                    <td>
                        @if ($item->is_approve === null)
                            Belum Disetujui
                        @elseif ($item->is_approve === 'Setuju')
                            Sudah Disetujui
                        @else
                            Ditolak
                        @endif
                    </td>
                    <td>Rp. {{ number_format($item->price) }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="5">Total Biaya</th>
                <td style="text-align: right;">Rp. {{ number_format($total_pengeluaran_toko) }}</td>
            </tr>
        </tbody>
    </table>
    <hr>
    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
        Pengeluaran Servis
    </h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tgl Pengeluaran</th>
                <th>Nama</th>
                <th>Item Pengeluaran</th>
                <th>Status</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengeluaran_data_servis as $item)
                <tr>
                    <td style="width: 10px;">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td>
                        @if ($item->user)
                            {{ $item->user->name }}
                        @else
                            Akun sudah dihapus
                        @endif
                    </td>
                    <td>{{ $item->name }}</td>
                    <td>
                        @if ($item->is_approve === null)
                            Belum Disetujui
                        @elseif ($item->is_approve === 'Setuju')
                            Sudah Disetujui
                        @else
                            Ditolak
                        @endif
                    </td>
                    <td>Rp. {{ number_format($item->price) }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="5">Total Biaya</th>
                <td style="text-align: right;">Rp. {{ number_format($total_pengeluaran_servis) }}</td>
            </tr>
        </tbody>
    </table>
    <hr>
  
    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
        Insiden
    </h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal</th>
                <th>Teknisi</th>
                <th>Nama Insiden</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insiden as $itemsiden)
                <tr>
                    <td class="">{{ $loop->iteration }}</td>
                    <td class="">{{ \Carbon\Carbon::parse($itemsiden->created_at)->translatedFormat('d F Y') }}</td>
                    <td class="">
                        @if ($itemsiden->worker && $itemsiden->worker->exists())
                            {{ $itemsiden->worker->name }}
                        @else
                            Data karyawan telah dihapus
                        @endif
                    </td>
                    <td class="">{{ $itemsiden->name }}</td>
                    <td class="">{{ number_format($itemsiden->price) }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="4">Total Biaya</th>
                <td style="text-align: right;">Rp. {{ number_format($total_insiden) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
