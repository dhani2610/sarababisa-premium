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
            border: solid 1px #000; /* Perbaikan border untuk tabel detail */
            word-wrap: break-word;
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
            Laporan Transaksi Servis Belum Lunas
        </h4>
        <p style="margin-top: 0">{{ $customerName }}</p>
    </div>

    <h4 style="margin-bottom: 6px; text-decoration: underline;">
        Ringkasan
    </h4>

    <table id="ringkasan">
        <tbody>
            <tr>
                <th>Total Item Servis</th>
                <th>: {{ count($services) }} Item</th>

                <th style="padding-left: 20px;">Total Biaya Servis</th>
                <th>: Rp. {{ number_format($total_biaya) }}</th>
            </tr>
        </tbody>
    </table>

    <h4 style="margin-top: 15px; margin-bottom: 6px; text-decoration: underline;">
        Detail Transaksi
    </h4>

    <table id="detail" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 30px;">No.</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 80px;">No Servis</th>
                <th style="width: 100px;">Tipe</th>
                <th style="width: 80px;">Imei</th>
                <th>Tindakan</th>
                <th style="width: 100px;">Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($services as $item)
                <tr>
                    {{-- No --}}
                    <td>{{ $loop->iteration }}</td>

                    {{-- Tanggal --}}
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>

                    <td class="text-center" style="width: 60px;" >
                        {{ $item->nomor_servis }}
                    </td>

                    {{-- Tipe (Model) --}}
                    <td>{{ $item->modelserie->name ?? '-' }}</td>

                    {{-- Imei --}}
                    <td>{{ $item->imei ?? '-' }}</td>

                    {{-- Tindakan --}}
                    <td style="text-align: left;">
                        @php
                            $tindakan = json_decode($item->tindakan_servis);
                            // Cek apakah data tindakan adalah array atau string biasa
                            if (is_array($tindakan)) {
                                echo implode(', ', $tindakan);
                            } else {
                                echo $item->tindakan_servis ?? '-';
                            }
                        @endphp
                    </td>

                    {{-- Biaya --}}
                    <td style="text-align: right;">
                        Rp. {{ number_format($item->biaya) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TAMBAHAN: Informasi Pembayaran / Bank -->
    <div style="margin-top: 20px; font-size: 12px; font-style: italic;">
        <h4 style="margin-bottom: 6px; font-style: normal; text-decoration: underline;">
            Informasi Pembayaran (Transfer)
        </h4>
        @php
            $banks = old('banks', json_decode($users->banks ?? '[]', true));
        @endphp

        <strong>No. Rekening : {{ $users->rekening }} {{ $users->bank }} An. {{ $users->pemilik_rekening }} </strong>

        @if(!empty($banks))
            @foreach ($banks as $index => $bank)
                <br><strong>No. Rekening : {{ $bank['rekening'] }}, {{ $bank['bank'] }} An. {{ $bank['pemilik'] }} </strong>
            @endforeach
        @endif
    </div>

</body>

</html>
