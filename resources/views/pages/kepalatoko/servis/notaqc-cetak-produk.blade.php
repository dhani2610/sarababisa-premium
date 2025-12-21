<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QC List #{{ $items->nomor_servis }}</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .info-table td {
            padding: 3px;
            border: none;
        }

        .qc-table {
            margin-top: 10px;
            width: 100%;
        }

        .qc-table th {
            background-color: #BDD7EE;
            /* Warna Biru Excel */
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        .qc-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .col-no {
            width: 30px;
            text-align: center;
        }

        .col-item {
            width: 40%;
        }

        .col-remark {
            width: 25%;
            text-align: center;
        }

        .footer-box {
            height: 30px;
        }

        /* Utility */
        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <div class="header-title">
        {{ $users->nama_toko }}<br>
        QUALITY CONTROL PRODUCT
    </div>

    <br>
    <br>

    <table class="info-table">
        <tr>
            <td>Type/Capacity/Color/IMEI</td>
            <td>:</td>
            <td class="uppercase">
                {{ $prod->product_name ?? '-' }} /
                {{ $prod->capacity->name ?? '-' }} /
                {{ $prod->warna ?? '-' }} /
                {{ $prod->nomor_seri ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>Date</td>
            <td>:</td>
            <td>
                {{ \Carbon\Carbon::parse($items->created_at)->format('d-m-Y') }}
            </td>
        </tr>
    </table>
    @if (!empty($qcIndexes))
        <table class="qc-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-no">No.</th>
                    <th rowspan="2" class="col-item">ITEM</th>
                    <th colspan="2">REMARK</th>
                </tr>
                <tr>
                    <th>IN (Masuk)</th>
                    <th>OUT (Keluar)</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp

                {{-- Loop berdasarkan Index angka (0, 1, 2...) --}}
                @forelse($qcIndexes as $index)
                    @php
                        // Ambil array data utuh berdasarkan index
                        $dataMasuk = $qcMasuk[$index] ?? [];
                        $dataKeluar = $qcKeluar[$index] ?? [];

                        // Ambil Nama Item (Prioritas dari Masuk, kalau kosong ambil dari Keluar)
                        $namaItem = $dataMasuk['item'] ?? $dataKeluar['item'] ?? '-';

                        // Ambil Value (OK/Rusak/dll)
                        $valMasuk = $dataMasuk['value'] ?? '-';
                        $valKeluar = $dataKeluar['value'] ?? '-';
                    @endphp

                    {{-- Hanya tampilkan jika Nama Item ada --}}
                    @if($valMasuk != '-')
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>

                            {{-- Tampilkan Nama Item (String) --}}
                            <td class="uppercase">
                                <strong>{{ $namaItem }}</strong>
                            </td>

                            {{-- KOLOM MASUK (Ambil value-nya saja) --}}
                            <td class="text-center">
                                {{ $valMasuk }}
                            </td>

                            {{-- KOLOM KELUAR (Ambil value-nya saja) --}}
                            <td class="text-center">
                                {{ $valKeluar }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data QC yang tersimpan.</td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2" style="border:1px solid #000; padding: 10px;">
                        <strong>PIC QC IN:</strong> <br><br><br>
                        <span class="uppercase">{{ $items->picMasuk->name}} </span>
                    </td>
                    <td colspan="2" style="border:1px solid #000; padding: 10px;">
                        <strong>PIC QC OUT:</strong> <br><br><br>
                        <span class="uppercase">{{ $items->picKeluar->name }}</span>
                    </td>
                </tr>
            </tbody>
            </table>
    @else
    @endif


</body>

</html>
