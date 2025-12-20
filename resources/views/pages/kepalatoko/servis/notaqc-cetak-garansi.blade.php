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
        QUALITY CONTROL SERVICE
    </div>

    <br>
    <br>

    <table class="info-table">
        <tr>
            <td width="20%">Customer</td>
            <td width="2%">:</td>
            <td class="uppercase"><strong>{{ $items->pelanggan->nama }}</strong> ({{ $items->pelanggan->nomor_hp }})</td>
        </tr>
        <tr>
            <td>Type/Capacity/Color/IMEI</td>
            <td>:</td>
            <td class="uppercase">
                {{ $items->service->type->name ?? '-' }} /
                {{ $items->service->capacity->name ?? '-' }} /
                {{ $items->service->warna ?? '-' }} /
                {{ $items->service->imei ?? '-' }}
            </td>
        </tr>
        <tr>
            <td>Date / Case</td>
            <td>:</td>
            <td>
                {{ \Carbon\Carbon::parse($items->created_at)->format('d-m-Y') }} /
                <strong>{{ $items->keluhan }}</strong>
            </td>
        </tr>
    </table>
    @if (!empty($qcItems))
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
                @forelse($qcItems as $index => $itemKey)
                    @php
                        $statusMasuk = $qcMasuk[$itemKey] ?? null;
                        $statusKeluar = $qcKeluar[$itemKey] ?? null;
                    @endphp

                    @if (!empty($statusMasuk) && $statusMasuk != '-')
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>

                            {{-- Tampilkan Nama Item --}}
                            <td class="uppercase"><strong>{{ $itemKey }}</strong></td>

                            {{-- KOLOM MASUK --}}
                            <td class="text-center">
                                {{ $statusMasuk ?? '-' }}
                            </td>

                            {{-- KOLOM KELUAR --}}
                            <td class="text-center">
                                {{ $statusKeluar ?? '-' }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data QC yang tersimpan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="border:1px solid #000; padding: 10px;">
                        <strong>CASHIER/TECHNICIAN:</strong> <br><br><br>
                        <span class="uppercase">{{ $items->penerima->name ?? $items->penerima->name }}</span>
                    </td>
                    <td colspan="2" style="border:1px solid #000; padding: 10px;">
                        <strong>CUSTOMER:</strong> <br><br><br>
                        <span class="uppercase">{{ $items->pelanggan->nama }}</span>
                    </td>
                </tr>

            </tfoot>
        </table>
    @else
        <table class="qc-table">
            <thead>
                <tr>
                    <th>IN (Masuk)</th>
                    <th>OUT (Keluar)</th>
                </tr>
            </thead>
            <tbody>
                <td>
                    {{ $items->qc_masuk }}

                </td>
                <td>
                    {{ $items->qc_keluar ?? '-' }}
                </td>
            </tbody>
            <tfoot>
                <tr>
                    <td  style="border:1px solid #000; padding: 10px;">
                        <strong>CASHIER/TECHNICIAN:</strong> <br><br><br>
                        <span class="uppercase">{{ $items->admin->name ?? $items->penerima }}</span>
                    </td>
                    <td  style="border:1px solid #000; padding: 10px;">
                        <strong>CUSTOMER:</strong> <br><br><br>
                        <span class="uppercase">{{ $items->pelanggan->nama }}</span>
                    </td>
                </tr>

            </tfoot>
        </table>
    @endif


</body>

</html>
