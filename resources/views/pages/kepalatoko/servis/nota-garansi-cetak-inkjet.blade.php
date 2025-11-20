<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Pengambilan Servis #{{ $items->nomor_servis }}</title>
    <style>
        @page {
            size: A4;
            margin: 3mm;
            /* Atur margin atas, kanan, bawah, dan kiri */
        }

        body {
            margin: 0;
        }

        .pt-5 {
            padding-top: 20px;
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

        .text-justify {
            text-align: justify;
        }

        .capital {
            text-transform: uppercase;
        }

        .w-100 {
            width: 100%;
        }

        .w-50 {
            width: 50%;
        }

        .w-75 {
            width: 75%;
        }

        .w-25 {
            width: 25%;
        }

        td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            padding: 4px;
        }

        #data {
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <table class="w-100">
        <tr>
            @php
                $phones = old('phones', json_decode($users->phones ?? '[]', true));
            @endphp
            @if ($users->profile_photo_path != null)
                <td class="text-center" style="width: 30%">
                    <img src="data:image/png;base64,{{ Storage::disk('public')->exists($users->profile_photo_path) ? base64_encode(file_get_contents($imagePath)) : '' }}"
                        alt="" height="70">
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
    
    @if ($history->status == 1)
    <h4 class="text-center" style="margin-bottom: 6px; margin-top: 6px;">NOTA TANDA TERIMA KLAIM GARANSI</h4>
    @else
    <h4 class="text-center" style="margin-bottom: 6px; margin-top: 6px;">NOTA PENGAMBILAN KLAIM GARANSI</h4>
    @endif

    <table class="w-100">
        <tr>
            <td class="text-left"><strong>No. Servis</strong> : {{ $items->nomor_servis }}</td>
            <td class="text-right"><strong>Tanggal</strong> :
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</td>
            {{-- <td class="text-right"><strong>Dicetak oleh</strong> : {{ Auth::user()->name }}</td> --}}
            <td class="text-right"><strong>Dicetak oleh</strong> : Admin</td>
        </tr>
    </table>

    <table class="w-100">
        <thead>
            <tr style="border-top-style: solid; border-right-style: solid;">
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Data Pelanggan
                </th>
                <th id="data" colspan="4" class="text-left" style="border-left-style: solid;">Data Barang</th>
            </tr>
        </thead>
        <tbody>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Nama</th>
                <td id="data" class="capital">: {{ $history->pelanggan->nama }}</td>
                <td id="data" scope="row" style="border-left-style: solid;">Jenis Barang</th>
                <td id="data" class="capital">: {{ $items->type->name }}</td>
                <td id="data" scope="row">IMEI/SN</th>
                <td id="data">: {{ $items->imei }}</td>
            </tr>
            <tr style="border-right-style: solid;">
                <td id="data" scope="row" style="border-left-style: solid;">Nomor HP</th>
                <td id="data">: {{ $history->pelanggan->nomor_hp }}</td>
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
                <td class="capital">: {{ $history->pelanggan->alamat }}</td>
                <td scope="row" style="border-left-style: solid;">Model Seri</th>
                <td class="capital">: {{ $items->modelserie->name }}</td>
                <td scope="row">Warna/Kapasitas</th>
                <td class="capital">: {{ $items->warna }} / {{ $items->capacity->name }}</td>
            </tr>
        </tbody>
    </table>
    <table class="w-100"  style="padding-top: 0px;">
        <thead>
            <tr style="border-top-style: solid; border-right-style: solid;">
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Klaim Garansi</th>
                <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Pengecekan
                    (Tombol, Kamera, dll)</th>
                    <th id="data" colspan="2" class="text-left" style="border-left-style: solid;">Status klaim garansi</th>
            </tr>
        </thead>
        <tbody>
            <tbody>

         {{-- ROW 1 --}}
        @if ($history->status != 2)
        <tr style="border-right-style: solid;">
            <td id="data" scope="row" style="border-left-style: solid;">Keluhan</td>
            <td id="data">: {{ $history->keluhan }}</td>

            <td id="data" scope="row" style="border-left-style: solid;">Fungsi (Masuk)</td>
            <td id="data">: {{ $history->fungsi_masuk }}</td>

            <td id="data" style="border-left-style: solid;">
                @if ($history->status == 1)
                    Diproses
                @elseif ($history->status == 2)
                    Sudah Selesai
                @elseif ($history->status == 3)
                    Dibatalkan / Refund
                @endif
            </td>
        </tr>
        @else
        <tr style="border-right-style: solid;">
            <td id="data" scope="row" style="border-left-style: solid;">Keluhan</td>
            <td id="data">: {{ $history->keluhan }}</td>

            <td id="data" scope="row" style="border-left-style: solid;">Fungsi (Keluar)</td>
            <td id="data">: {{ $history->fungsi_keluar }}</td>

            <td id="data" style="border-left-style: solid;border-right-style: solid;border-bottom-style: solid">
                @if ($history->status == 1)
                    Diproses
                @elseif ($history->status == 2)
                    Sudah Selesai
                @elseif ($history->status == 3)
                    Dibatalkan / Refund
                @endif
            </td>
        </tr>
        @endif


        </tbody>
        @if ($history->status == 1)
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
                @if ($items->pin != null)
                <td>
                      <th class="text-center">{{ $items->pin }}</th>
                </td>
                @else
                <td></td>
                <td>
                    <hr style="border-top: 1px dashed;">
                </td>
                @endif
                <td class="text-center"><img src="{{ $items->pola != null ? $items->pola : asset('images/pola.png') }}" alt=""
                        style="height: 40"></td>
                <td class="text-center capital" style="padding-top: 36px;">{{ $history->pelanggan->nama }}</td>
                <td class="text-center capital" style="padding-top: 36px;">{{ $items->penerima }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
        </tbody>
    </table>
    @if ($history->status == 2)
    <table class="w-100">
        <tbody>

            <tr>
                @if ($items->catatan != null)
                    <td colspan="4">
                        <strong>Catatan</strong> : {{ $items->catatan }}
                    </td>
                @endif
            </tr>

            <tr>
            <tr>
                <th class="text-left w-75">Syarat & Ketentuan</th>
                @if ($items->exp_garansi === null)
                    <th colspan="3" class="w-25 text-right">
                        {{-- (Tidak ada garansi) --}}
                    </th>
                @else
                    <th colspan="3" class="w-25 text-right">
                        {{-- (Garansi <strong>{{ $items->exp_garansi }}</strong>) --}}
                    </th>
                @endif
            </tr>
            <tr>
                @php
                    $banks = old('banks', json_decode($users->banks ?? '[]', true));
                @endphp
                <td rowspan="2" class="text-justify" style="font-style: italic; padding-right: 30px;">
                    {!! $terms->description !!}
                    @foreach ($banks as $index => $bank)
                        <br><strong>No. Rekening : {{ $bank['rekening'] }}, {{ $bank['bank'] }} An.
                            {{ $bank['pemilik'] }} </strong>
                    @endforeach
                </td>
                <th class="text-center" style="vertical-align: top;">Pengambil</th>
                <th class="text-center" style="vertical-align: top;">Penyerah</th>
                <th class="text-center" style="vertical-align: top;">Teknisi</th>
            </tr>
            <tr>
                <td class="pt-5 text-center capital">{{ $items->pengambil }}</td>
                @if ($items->penyerah != null)
                    <td class="pt-5 text-center capital">{{ $items->penyerah }}</td>
                @else
                    <td class="pt-5 text-center capital">-</td>
                @endif
                @if ($items->user != null)
                    <td class="pt-5 text-center capital">{{ $items->user->name }}</td>
                @else
                    <td class="pt-5 text-center capital">-</td>
                @endif
            </tr>
        </tbody>
    </table>
    @endif
</body>

</html>
