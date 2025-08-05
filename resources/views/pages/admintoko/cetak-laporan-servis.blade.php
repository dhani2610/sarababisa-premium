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
            font-size: 10px;
            line-height: 1em;
            table-layout: fixed;
            padding: 4px;
            text-align: center;
            border: solid;
            word-wrap: break-word;
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
                <th>Total Profit</th>
                <th>: Rp. {{ number_format($total_profit) }}</th>
            </tr>
            <tr>
                <th>Total DP</th>
                <th>: Rp. {{ number_format($total_dp) }}</th>
            </tr>
        </tbody>
    </table>

    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
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
                @if ($toko->is_bonus === 1)
                    <th>Modal Sparepart</th>
                @endif
                <th>Biaya Servis</th>
                <th>Diskon</th>
                @if ($toko->is_bonus === 1)
                    <th>Profit</th>
                @endif
                <th>Pembayaran</th>

            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach ($services as $item)
                @if (json_decode($item->tindakan_servis))
                    @php
                        $tindakan_servis = json_decode($item->tindakan_servis);
                        $biaya_j = json_decode($item->biaya_j);
                        $modal_j = json_decode($item->modal_j);
                    @endphp
                    <tr>
                        <td style="width: 10px;" rowspan="{{ count($tindakan_servis) }}">{{ $no++ }}</td>
                        <td class="text-center" style="width: 60px;" rowspan="{{ count($tindakan_servis) }}">
                            {{ $item->nomor_servis }}</td>
                        <td style="text-align: left; width: 70px;" class="capital"
                            rowspan="{{ count($tindakan_servis) }}">{{ $item->nama_pelanggan }}</td>
                        @if ($item->user)
                            <td style="text-align: left; width: 70px;" rowspan="{{ count($tindakan_servis) }}">
                                {{ $item->user->name }}
                            </td>
                        @elseif ($item->user()->withTrashed()->first())
                            <td style="text-align: left; width: 70px;" rowspan="{{ count($tindakan_servis) }}">
                                {{ $item->user()->withTrashed()->first()->name }}
                            </td>
                        @else
                            <td style="text-align: center; width: 70px;" rowspan="{{ count($tindakan_servis) }}">
                                -
                            </td>
                        @endif
                        <td style="text-align: left; width: 70px;" rowspan="{{ count($tindakan_servis) }}">
                            @if ($item->modelserie)
                                {{ $item->modelserie->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="" style="text-align: left; width: 80px;">
                            @if ($item->kondisi_servis != 'Sudah jadi')
                                {{ $item->kondisi_servis }}
                            @else
                                {{ $tindakan_servis[0] }}
                            @endif
                        </td>

                        @if ($toko->is_bonus === 1)
                            <td style="width: 60px; text-align: right;">Rp.
                                {{ number_format($modal_j[0]) }}
                            </td>
                        @endif
                        {{-- <td style="width: 60px; text-align: right;">Rp.
                            {{ number_format($item->omzet) }}</td> --}}
                        <td style="width: 60px; text-align: right;">Rp.
                            {{ number_format($biaya_j[0]) }}</td>
                        <td style="width: 50px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td>
                        @if ($toko->is_bonus === 1)
                            <td style="width: 60px; text-align: right;">Rp.
                                {{ number_format($biaya_j[0] - $modal_j[0]) }}
                            </td>
                            {{-- <td style="width: 60px; text-align: right;">Rp.
                                {{ number_format($item->profit) }}
                            </td> --}}
                        @endif
                        <td class="" rowspan="{{ count($tindakan_servis) }}"
                            style="text-align: left; width: 80px;">
                            @php
                                $metode = [];
                                if ($item->tunai > 0) {
                                    $metode[] =
                                        '<div>
                        <strong>Tunai:</strong><br>
                        Rp.' .
                                        number_format($item->tunai, 0, ',', '.') .
                                        '
                     </div>';
                                }
                                if ($item->transfer > 0) {
                                    $metode[] =
                                        '<div>
                        <strong>Transfer:</strong><br>
                        Rp.' .
                                        number_format($item->transfer, 0, ',', '.') .
                                        '
                     </div>';
                                }
                            @endphp
                            {!! implode('<hr style="margin: 4px 0;">', $metode) !!}
                        </td>

                    </tr>
                    @for ($i = 1; $i < count($tindakan_servis); $i++)
                        <tr>
                            <td class="" style="text-align: left; width: 80px;">
                                @if ($item->kondisi_servis != 'Sudah jadi')
                                    {{ $item->kondisi_servis }}
                                @else
                                    {{ $tindakan_servis[$i] }}
                                @endif
                            </td>

                            @if ($toko->is_bonus === 1)
                                <td style="width: 60px; text-align: right;">Rp.
                                    {{ number_format($modal_j[$i]) }}
                                </td>
                            @endif
                            <td style="width: 60px; text-align: right;">Rp.
                                {{ number_format($biaya_j[$i]) }}</td>
                            <td style="width: 50px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td>

                            @if ($toko->is_bonus === 1)
                                <td style="width: 60px; text-align: right;">Rp.
                                    {{ number_format($biaya_j[$i] - $modal_j[$i]) }}
                                </td>
                            @endif
                        </tr>
                    @endfor
                @else
                    <tr>
                        <td style="width: 10px;">{{ $no++ }}</td>
                        <td class="text-center" style="width: 60px;">{{ $item->nomor_servis }}</td>
                        <td style="text-align: left; width: 70px;" class="capital">{{ $item->nama_pelanggan }}</td>
                        @if ($item->user)
                            <td style="text-align: left; width: 70px;">
                                {{ $item->user->name }}
                            </td>
                        @elseif ($item->user()->withTrashed()->first())
                            <td style="text-align: left; width: 70px;">
                                {{ $item->user()->withTrashed()->first()->name }}
                            </td>
                        @else
                            <td style="text-align: center; width: 70px;">
                                -
                            </td>
                        @endif
                        <td style="text-align: left; width: 70px;">
                            @if ($item->modelserie)
                                {{ $item->modelserie->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="" style="text-align: left; width: 80px;">
                            @if ($item->kondisi_servis != 'Sudah jadi')
                                {{ $item->kondisi_servis }}
                            @else
                                {{ json_decode($item->tindakan_servis) ? implode(', ', json_decode($item->tindakan_servis)) : $item->tindakan_servis }}
                            @endif
                        </td>




                        @if ($toko->is_bonus === 1)
                            <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->modal_sparepart) }}
                            </td>
                        @endif
                        <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->biaya) }}</td>
                        <td style="width: 50px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td>
                        @if ($toko->is_bonus === 1)
                            <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>
                        @endif
                        <td class="" style="text-align: left; width: 80px;">
                            @php
                                $metode = [];
                                if ($item->tunai > 0) {
                                    $metode[] =
                                        '<div>
                        <strong>Tunai:</strong><br>
                        Rp.' .
                                        number_format($item->tunai, 0, ',', '.') .
                                        '
                     </div>';
                                }
                                if ($item->transfer > 0) {
                                    $metode[] =
                                        '<div>
                        <strong>Transfer:</strong><br>
                        Rp.' .
                                        number_format($item->transfer, 0, ',', '.') .
                                        '
                     </div>';
                                }
                            @endphp
                            {!! implode('<hr style="margin: 4px 0;">', $metode) !!}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <hr>
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
                <th>Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
            @endphp
            @foreach ($services as $item)
                <tr>
                    <td style="width: 10px;">{{ $loop->iteration }}</td>
                    <td>{{ $item->nomor_servis }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->penerima }}</td>
                    <td>{{ $item->modelserie->name ?? '-' }}</td>
                    <td>{{ $item->kerusakan }}</td>
                    <td>Rp. {{ number_format($item->estimasi_biaya) }}</td>
                    <td>Rp. {{ number_format($item->uang_muka) }}</td>
                    <td class="" style="text-align: left; width: 80px;">
                        @php
                            $metode = [];
                            if ($item->tunai > 0) {
                                $metode[] =
                                    '<div>
                        <strong>Tunai:</strong><br>
                        Rp.' .
                                    number_format($item->tunai, 0, ',', '.') .
                                    '
                     </div>';
                            }
                            if ($item->transfer > 0) {
                                $metode[] =
                                    '<div>
                        <strong>Transfer:</strong><br>
                        Rp.' .
                                    number_format($item->transfer, 0, ',', '.') .
                                    '
                     </div>';
                            }
                        @endphp
                        {!! implode('<hr style="margin: 4px 0;">', $metode) !!}

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
