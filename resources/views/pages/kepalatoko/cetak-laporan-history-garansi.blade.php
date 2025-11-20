<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan History Garansi</title>
    <style>
        @page {
            margin: 3mm 4mm 10mm 3mm;
            /* Atur margin atas, kanan, bawah, dan kiri */
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

        #detail td,
        #detail th,
        #detail tr,
        #detail table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            padding: 4px;
            text-align: center;
            border: solid;
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

        /* ✅ Fokus di sini: perkecil font hanya untuk tabel Detail Servis */
        #detail td,
        #detail th {
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 9px;
            /* kecilkan font */
            line-height: 0.9em;
            /* rapatkan jarak antar baris */
            padding: 2px 3px;
            /* kecilkan padding */
            text-align: center;
            word-wrap: break-word;
            /* pecah teks panjang biar tidak keluar */
            white-space: normal;
            /* biar bisa turun ke baris baru */
        }

        #detail {
            width: 100%;
            table-layout: fixed;
            /* pastikan tabel menyesuaikan lebar halaman */
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
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt=""
                height="70">
        @endif
        <h4 style="margin-top: 5px; margin-bottom: 0">{{ $users->nama_toko }}</h4>
        <p style="margin-top: 3px; margin-bottom: 5px;">{{ $users->alamat_toko }}</p>
    </div>

    <hr style="border-top: 1px dashed; margin-bottom: 0;">

    <div class="text-center">
        <h4 style="margin-bottom: 6px; margin-top: 5px;">
            LAPORAN RIWAYAT GARANSI
        </h4>
        <p style="margin-top: 0">Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d
            {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
    </div>

    <br>

    <h4 style="margin-bottom: 6px; text-decoration: underline;">
        Ringkasan
    </h4>


    <table id="ringkasan" >
        <tbody>
            <tr>
                <th>Total Data</th>
                <th>:{{ $totalData }}</th>
                <th>Total Selesai</th>
                <th>:{{ $totalSelesai }}</th>
               
            </tr>
            <tr>
                <th>Total Menunggu Konfirmasi</th>
                <th>:{{ $totalProses }}</th>
                <th>Total Batal</th>
                <th>:{{ $totalBatal }}</th>
               
            </tr>
            <tr>
                <th>Total Modal</th>
                <th>: Rp{{ number_format($totalModal, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

    <h4 style="margin-top: 15px; margin-bottom: 6px; text-decoration: underline;">
        Detail Riwayat
    </h4>
    <br>
    <table class="table-auto w-full" id="detail">

        <thead id="detail"
            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
            <tr>
                <th style="width:30%">No.</th>
                <th class="">Tanggal</th>
                <th class="">Tgl Selesai</th>
                <th class="">Nomor Servis</th>
                <th class="">Pelanggan</th>
                <th class="">Penerima</th>
                <th class="">Teknisi</th>
                <th class="">Tindakan</th>
                <th class="">Sparepart</th>
                <th class="">Total Modal</th>
                <th class="">Keluhan</th>
                <th class="">Catatan</th>
                <th class="">Status</th>
            </tr>
        </thead>
        <tbody class="text-sm divide-y divide-slate-200">
            @php $i = 1; @endphp
            @foreach ($data as $item)
                <tr>
                    <td style="width:30%">{{ $i++ }}</td>
                    <td class="">{{ $item->date }}</td>
                    <td class="">{{ $item->tgl_selesai ?? '-' }}</td>
                    <td class="">{{ $item->service->nomor_servis ?? $item->service_id }}</td>
                    <td class="">{{ $item->pelanggan->nama ?? '-' }}</td>
                    <td class="">{{ $item->penerima->name ?? '-' }}</td>
                    <td class="">{{ $item->teknisi->name ?? '-' }}</td>

                    {{-- Tindakan --}}
                    <td style="text-align:left">
                        @if (!empty($item->tindakan))
                        @php
                            $tindakans = json_decode($item->tindakan, true) ?? [];
                        @endphp

                        {{-- <ul class="list-disc ml-4"> --}}
                            @foreach ($tindakans as $t)
                                @php
                                    $action = \App\Models\ServiceAction::find($t['id']);
                                @endphp
                                {{-- <li> --}}
                                    - {{ $action ? $action->nama_tindakan : $t['id_manual'] }}
                                    - Rp{{ number_format($t['harga'], 0, ',', '.') }}
                                {{-- </li> --}}
                                <br>
                                <br>
                            @endforeach
                        @else
                        -
                        @endif
                        {{-- </ul> --}}
                    </td>

                    {{-- Sparepart --}}
                    <td style="text-align:left">
                        @if (!empty($item->sparepart))
                            @php $spareparts = json_decode($item->sparepart, true); @endphp
                            @if ($spareparts)
                                {{-- <ul class=""> --}}
                                    @foreach ($spareparts as $sp)
                                        @php $prd = \App\Models\Product::find($sp['id']); @endphp
                                        {{-- <li> --}}
                                            - {{ $prd->product_name ?? 'Produk ID ' . $sp['id'] }}
                                            (x{{ $sp['qty'] }})
                                            - Rp{{ number_format($sp['harga'], 0, ',', '.') }}
                                        {{-- </li> --}}
                                        <br>
                                        <br>
                                    @endforeach
                                {{-- </ul> --}}
                            @else
                                -
                            @endif
                        @else
                        -
                        @endif
                    </td>

                    <td class="">Rp{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                    <td class="">{{ $item->keluhan }}</td>
                    <td class="">{{ $item->catatan }}</td>
                    <td class="">
                            @if ($item->status == 1)
                                Diproses
                            @elseif ($item->status == 2)
                                Sudah Selesai
                            @elseif ($item->status == 3)
                                Dibatalkan / Refund
                            @endif
                    </td>
                </tr>
            @endforeach
        </tbody>mak
    </table>


</body>

</html>
