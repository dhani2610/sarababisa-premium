<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tanda Terima Servis #{{ $items->nomor_servis }}</title>
    <style>
        @page {
            size: A4;
            margin: 3mm;
            /* Atur margin atas, kanan, bawah, dan kiri */
        }

        body {
            margin: 0;
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

        .w-50 {
            width: 50%;
        }

        td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            width: 100%;
            padding: 4px;
            color: #000000;
        }

        #data {
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            @php
                $phones = old('phones', json_decode($users->phones ?? '[]', true));
            @endphp
            @if ($users->profile_photo_path != null)
                <td class="w-50 text-center">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt=""
                        height="70">
                </td>
                <td style="height: 50px; vertical-align: middle; text-align: left; line-height: 1.5em;">
                    <strong>{{ $users->nama_toko }} ({{ $users->deskripsi_toko }})</strong> <br>
                    {{ $users->alamat_toko }} - {{ $users->nomor_hp_toko }}
                    @foreach ($phones as $index => $phone)
                        | {{ $phone['title'] }} : {{ $phone['nomor'] }} <br>
                    @endforeach
                </td>
            @else
                <td style="text-align: left; line-height: 1.5em;"><strong>{{ $users->nama_toko }}
                        ({{ $users->deskripsi_toko }})</strong> <br>
                    {{ $users->alamat_toko }} - {{ $users->nomor_hp_toko }}
                    @foreach ($phones as $index => $phone)
                        | {{ $phone['title'] }} : {{ $phone['nomor'] }} <br>
                    @endforeach
                </td>
            @endif
        </tr>
    </table>

    <hr style="border-top: 1px dashed;">

    <h4 class="text-center" style="margin-bottom: 6px; margin-top: 6px;">
        NOTA TERIMA SERVIS
    </h4>

    <table>
        <tr>
            <td class="text-left"><strong>No. Servis</strong> : {{ $items->nomor_servis }}</td>
            <td class="text-center"><strong>Tanggal</strong> :
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</td>
            <td class="text-right"><strong>Dicetak oleh</strong> : {{ Auth::user()->name }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr style="border-top-style: solid; border-right-style: solid;">
                <th id="data" colspan="2" style="border-left-style: solid;" class="text-left">Data Pelanggan
                </th>
                <th id="data" colspan="4" style="border-left-style: solid;" class="text-left">Data Barang</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Nama</th>
                <td id="data" class="capital">: {{ $items->customer->nama }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Jenis Barang</th>
                <td id="data" class="capital">: {{ $items->type->name }}</td>
                <td id="data" scope="row">IMEI/SN</th>
                <td id="data">: {{ $items->imei }}</td>
            </tr>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Nomor HP</th>
                <td id="data">: {{ $items->customer->nomor_hp }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Merek</th>
                <td id="data" class="capital">: {{ $items->brand->name }}</td>
                <td id="data" scope="row">Kelengkapan</th>
                    @if ($items->kelengkapan != null)
                <td id="data" class="capital">: {{ $items->kelengkapan }}</td>
            @else
                <td id="data" class="capital">: Hanya Unit</td>
                @endif
            </tr>
            <tr style="border-bottom-style: solid; border-right-style: solid;">
                <td scope="row" style="border-left-style: solid;">Alamat</th>
                <td class="capital">: {{ $items->customer->alamat }}</td>
                <td scope="row" style="border-left-style: solid;">Model Seri</th>
                <td class="capital">: {{ $items->modelserie->name }}</td>
                <td scope="row">Warna/Kapasitas</th>
                <td class="capital">: {{ $items->warna }} / {{ $items->capacity->name }}</td>
            </tr>
        </tbody>
    </table>
    <table style="padding-top: 0px;">
        <thead>
            <tr style="border-top-style: solid; border-right-style: solid;">
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Pengecekan</th>
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Pembayaran</th>
                <th id="data" colspan="2" class="text-center" style="border-left-style: solid;">Cek status
                    servis via web</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Kerusakan</th>
                <td id="data" class="capital">: {{ $items->kerusakan }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Estimasi Biaya Servis</th>
                    @if ($items->estimasi_biaya != null)
                <td id="data">: Rp. {{ number_format($items->estimasi_biaya) }}</td>
            @else
                <td id="data">: -</td>
                @endif
                <td id="data" colspan="2" style="border-left-style: solid;" class="text-center">
                    <p>{{ env('APP_URL') . '/tracking') }} </p>
                </td>
            </tr>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Pengecekan Fungsi</th>
                <td id="data" class="capital">: {{ $items->qc_masuk }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Uang Muka</th>
                    @if ($items->uang_muka != null)
                <td id="data">: Rp. {{ number_format($items->uang_muka) }}</td>
            @else
                <td id="data">: Tidak ada</td>
                @endif
                <td id="data" colspan="2" style="border-left-style: solid;"></td>
            </tr>
            <tr style="border-bottom-style: solid; border-right-style: solid;">
                <td scope="row" style="border-left-style: solid;">Estimasi Pengerjaan</td>
                @if ($items->estimasi_pengerjaan != null)
                    <td class="capital">: {{ $items->estimasi_pengerjaan }}</td>
                @else
                    <td>: -</td>
                @endif
                <th scope="row" style="border-left-style: solid;"></th>
                <td></td>
                <td colspan="2" style="border-left-style: solid;"></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="padding-bottom: 0;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th colspan="6" style="text-align: left">Syarat & Ketentuan</th>
            </tr>
            <tr>
                <td colspan="6" style="text-align: justify">
                    {!! $terms->description !!}
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 4px;"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th></th>
                <th>PIN</th>
                <th class="text-center">Pola</th>
                <th class="text-center">Pelanggan</th>
                <th class="text-center">Diterima</th>
                <th></th>
            </tr>
            <tr>
                <td></td>
                <td>
                    <hr style="border-top: 1px dashed;">
                </td>
                <td class="text-center"><img src="{{ asset('images/pola.png') }}" alt=""
                        style="height: 40"></td>
                <td class="text-center capital" style="padding-top: 36px;">{{ $items->customer->nama }}</td>
                <td class="text-center capital" style="padding-top: 36px;">{{ $items->penerima }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
