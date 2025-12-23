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
    <br>

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
                <th>Total Uang Muka</th>
                <th>: Rp. {{ number_format($total_dp) }}</th>
                <th>Total Pengeluaran</th>
                <th>: Rp. {{ number_format($total_pengeluaran) }}</th>
            </tr>
            <tr>
                <th>Total Insiden</th>
                <th>: Rp. {{ number_format($total_insiden) }}</th>
                <th>Saldo Akhir</th>
                <th>: Rp. {{ number_format($saldo_akhir) }}</th>
            </tr>
        </tbody>
    </table>
    <br>

    <h4 style="margin-top: 15px; margin-bottom: 6px; text-decoration: underline;">
        Detail Transaksi
    </h4>
    <br>

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
                $i = 1;
            @endphp
            @foreach ($services as $item)
                @php
                    $teknisiServis = $item->teknisi_tambahan;

                    // $tindakan_servis = json_decode($item->tindakan_servis);

                    $tindakan_servis = [];
                    $biaya_j = [];
                    $modal_j = [];
                    $teknisi_display_arr = [];

                    if ($teknisiServis->isNotEmpty()) {
                        foreach ($teknisiServis as $teknisi) {
                            $list = json_decode($teknisi->tindakan_servis);

                            if (is_array($list)) {
                                $tindakan_servis = array_merge($tindakan_servis, $list);
                            }

                            $biaya_multi = json_decode(str_replace(['“','”'], '"', $teknisi->biaya_j), true);

                            if (is_array($biaya_multi)) {
                                $biaya_j = array_merge($biaya_j, $biaya_multi);
                            }
                            $modal_multi = json_decode(str_replace(['“','”'], '"', $teknisi->modal_j), true);

                            if (is_array($modal_multi)) {
                                $modal_j = array_merge($modal_j, $modal_multi);
                            }

                            $teknisi_display_arr[] = $teknisi->teknisi->name ?? '-';

                        }

                        $teknisi_display = implode(', ', $teknisi_display_arr);
                    } else {
                        $list = json_decode($item->tindakan_servis);

                        if (is_array($list)) {
                            $tindakan_servis = $list;
                        }

                        $biaya_j = json_decode(str_replace(['“','”'], '"', $item->biaya_j), true);
                        $modal_j = json_decode(str_replace(['“','”'], '"', $item->modal_j), true);

                        if ($item->user){
                            $teknisi_display = $item->user->name;
                        }elseif ($item->user()->withTrashed()->first()){
                            $teknisi_display = $item->user()->withTrashed()->first()->name;
                        } else{
                            $teknisi_display = '-';
                        }
                    }
                    // dd($teknisi_display);


                    // $biaya_j = json_decode($item->biaya_j);
                    // $biaya_j = json_decode(str_replace(['“','”'], '"', $item->biaya_j), true);
                    // $modal_j = json_decode(str_replace(['“','”'], '"', $item->modal_j), true);
                    // $modal_j = json_decode($item->modal_j);
                @endphp
                @if ($tindakan_servis)
                    <tr>
                        <td style="width: 10px;" rowspan="{{ count($tindakan_servis) }}">{{ $i++ }}</td>
                        <td class="text-center" style="width: 60px;" rowspan="{{ count($tindakan_servis) }}">
                            {{ $item->nomor_servis }}</td>
                        <td style="text-align: left; width: 70px;" class="capital"
                            rowspan="{{ count($tindakan_servis) }}">{{ $item->nama_pelanggan }}</td>
                        {{-- @if ($item->user)
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
                        @endif --}}
                        <td style="text-align: center; width: 70px;" rowspan="{{ count($tindakan_servis) }}">
                                {{  $teknisi_display  }}
                        </td>
                        <td style="text-align: left; width: 70px;" rowspan="{{ count($tindakan_servis) }}">
                            @if ($item->modelserie)
                                {{ $item->modelserie->name ?? '-' }}
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

                        @php
                            $biayaj_convert = is_array($biaya_j) ? ($biaya_j[0] ?? 0) : $biaya_j;
                            $modal_convert = is_array($modal_j) ? ($modal_j[0] ?? 0) : $modal_j;
                        @endphp
                        <td style="width: 60px; text-align: right;">
                            Rp. {{ number_format($modal_convert) }}
                        </td>
                        {{-- <td style="width: 60px; text-align: right;">Rp. {{ number_format($modal_j[0]) }}
                        </td> --}}
                        <td style="width: 60px; text-align: right;">Rp. {{ number_format($biayaj_convert) }}</td>
                        {{-- <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->biaya) }}</td> --}}
                        <td style="width: 50px; text-align: right;" rowspan="{{ count($tindakan_servis) }}">Rp. {{ number_format($item->diskon) }}</td>
                        <td style="width: 60px; text-align: right;">Rp.
                            {{ number_format($biayaj_convert - $modal_convert - $item->diskon) }}</td>

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
                            @if ($item->kondisi_servis == 'Dibatalkan')
                                @php
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
                                    } else {
                                        $label = '';
                                        $nilai = 0;
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
                    </tr>
                    @for ($k = 1; $k < count($tindakan_servis); $k++)
                        <tr>
                            <td class="" style="text-align: left; width: 80px;">
                                @if ($item->kondisi_servis != 'Sudah jadi')
                                    {{ $item->kondisi_servis }}
                                @else
                                    {{ $tindakan_servis[$k] }}
                                @endif
                            </td>
                            {{-- <td class="" style="text-align: left; width: 80px;">
                                @php
                                    $metode = [];
                                    if ($item->tunai > 0) {
                                        $metode[] = 'Tunai: Rp ' . number_format($item->tunai, 0, ',', '.');
                                    }
                                    if ($item->transfer > 0) {
                                        $metode[] = 'Transfer: Rp ' . number_format($item->transfer, 0, ',', '.');
                                    }
                                @endphp
                                {!! implode('<br><hr style="margin: 2px 0;">', $metode) !!}
                            </td> --}}
                            <td style="width: 60px; text-align: right;">Rp.
                                {{ number_format($modal_j[$k]) }}
                            </td>
                            <td style="width: 60px; text-align: right;">Rp. {{ number_format($biaya_j[$k]) }}</td>
                            {{-- <td style="width: 50px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td> --}}
                            {{-- <td style="width: 50px; text-align: right;">Rp. {{ number_format($item->profit) }}</td> --}}
                            <td style="width: 60px; text-align: right;">Rp.
                                {{ number_format($biaya_j[$k] - $modal_j[$k]) }}</td>
                        </tr>
                    @endfor
                @else
                    <tr>
                        <td style="width: 10px;">{{ $i++ }}</td>
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
                                {{ $item->modelserie->name ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="" style="text-align: left; width: 80px;">
                            @if ($item->kondisi_servis != 'Sudah jadi')
                                {{ $item->kondisi_servis }}
                            @else
                                {{-- {{ json_decode($tindakan_servis) ? implode(', ', json_decode($tindakan_servis)) : $tindakan_servis }} --}}
                                @foreach ($tindakan_servis as $t)
                                    {{ $t }},
                                @endforeach
                            @endif
                        </td>

                        <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->modal_sparepart) }}
                        </td>
                        <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->biaya) }}</td>
                        <td style="width: 50px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td>
                        <td style="width: 60px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>

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
                            @if ($item->kondisi_servis == 'Dibatalkan')
                                @php
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
                                    } else {
                                        $label = '';
                                        $nilai = 0;
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
                    </tr>
                @endif
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
                {{-- <th>Pembayaran</th> --}}
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
            @endphp
            @foreach ($servicesDP as $item)
                <tr>
                    <td style="width: 10px;">{{ $loop->iteration }}</td>
                    <td>{{ $item->nomor_servis }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->penerima }}</td>
                    <td>{{ $item->modelserie->name ?? '-' ?? '-' }}</td>
                    <td>{{ $item->kerusakan }}</td>
                    <td>Rp. {{ number_format($item->estimasi_biaya) }}</td>
                    <td>Rp. {{ number_format($item->uang_muka) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
        Pengeluaran
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
            @php
                $i = 1;
            @endphp
            @foreach ($pengeluaran_data as $item)
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
				<td style="text-align: right;">Rp. {{ number_format($total_pengeluaran) }}</td>
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
                <!-- Row -->
            @php
                $i = 1
            @endphp
            @foreach($insiden as $itemsiden)
                <tr>
                    <td class="">
                        {{ $i++ }}
                    </td>
                    <td class="">
                        {{ \Carbon\Carbon::parse($itemsiden->created_at)->translatedFormat('d F Y') }}
                    </td>
                    <td class="">
                        @if ($itemsiden->worker)
                            @if ($itemsiden->worker->exists())
                                {{ $itemsiden->worker->name }}
                            @else
                                Data karyawan telah dihapus
                            @endif
                        @else
                            Data karyawan telah dihapus
                        @endif
                    </td>
                    <td class="">
                        {{ $itemsiden->name }}
                    </td>
                    <td class="">
                        {{ number_format($itemsiden->price) }}
                    </td>
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
